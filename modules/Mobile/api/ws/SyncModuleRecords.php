<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/SaveRecord.php';

include_once 'include/Webservices/Query.php';

class Mobile_WS_SyncModuleRecords extends Mobile_WS_SaveRecord {

	static $SYNC_MODE_PUBLIC  = "PUBLIC";
	static $SYNC_MODE_PRIVATE = "PRIVATE";

	function isModePrivate(Mobile_API_Request $request, $defmode="PRIVATE") {
		return (strcasecmp($request->get('mode', $defmode), self::$SYNC_MODE_PRIVATE) === 0);
	}

	function process(Mobile_API_Request $request) {
		$current_user = $this->getActiveUser();
		$current_user_wsid = sprintf("%sx%s", Mobile_WS_Utils::getEntityModuleWSId("Users"), $current_user->id);

		$module = $request->get('module');
		$lastSyncTime = $request->get('syncToken', 0);
		$currentPage = intval($request->get('page', 0));
		$isPrivateMode = $this->isModePrivate($request);

		$FETCH_LIMIT = Mobile::config('API_RECORD_FETCH_LIMIT');
		$startLimit = $currentPage * $FETCH_LIMIT;

		// Keep track of sync-token for futher reference
		$maxSyncTime = $lastSyncTime;

		$describeInfo = vtws_describe($module, $current_user);
		$this->cacheDescribeInfo($describeInfo);

		$hasAssignedToField = false;
		foreach ($describeInfo['fields'] as $fieldinfo) {
			if ($fieldinfo['name'] == 'assigned_user_id') {
				$hasAssignedToField = true;
				break;
			}
		}

		/////////////////////////////
		// MODIFIED RECORDS TRACKING
		/////////////////////////////
		if (empty($lastSyncTime)) {
			// No previous state information available? Lookup records recently modified
			if ($hasAssignedToField && $isPrivateMode) {
				$queryActive = sprintf("SELECT * FROM %s WHERE assigned_user_id = '%s' ORDER BY modifiedtime DESC", $module, $current_user_wsid);
			} else {
				$queryActive = sprintf("SELECT * FROM %s ORDER BY modifiedtime DESC", $module);
			}

		} else {
			// Attempt to lookup records from previous state
			if ($hasAssignedToField && $isPrivateMode) {
				$queryActive = sprintf("SELECT * FROM %s WHERE assigned_user_id = '%s' AND modifiedtime > '%s'", $module, $current_user_wsid, date("Y-m-d H:i:s", $lastSyncTime));
			} else {
				$queryActive = sprintf("SELECT * FROM %s WHERE modifiedtime > '%s'", $module, date("Y-m-d H:i:s", $lastSyncTime));
			}
		}

		// Try to fetch record with paging (one extra record fetch is attempted to determine presence of next page)
		$activeQuery = sprintf("%s LIMIT %u,%u;", $queryActive, $startLimit, ($FETCH_LIMIT+1));
		$activeResult = vtws_query( $activeQuery, $current_user );

		 // Determine paging
        $hasNextPage = (count($activeResult) > $FETCH_LIMIT);

        // Special case handling merge Events records
        if ($module == 'Calendar') {
            $activeResult2 = vtws_query(str_replace('Calendar', 'Events', $activeQuery), $current_user);
            if (!empty($activeResult2)) {
                $activeResult = array_merge($activeResult, $activeResult2);
                if (!$hasNextPage) {
                    // If there was not Calendar next-page of records - check with Events
                    $hasNextPage = (count($activeResult) > $FETCH_LIMIT);
                }
            }
            // Indicator that we fetched both Calendar+Events
            $FETCH_LIMIT *= 2;
        }

        $nextPage = 0;
        if ($hasNextPage) {
            array_pop($activeResult); // Avoid sending next page record now
            $nextPage = $currentPage + 1;
        }

		// Resolved record details
		$resolvedModifiedRecords = array();
		$resolvedDeletedRecords = array();

		if (!empty($activeResult)) {

			foreach($activeResult as $recordValues) {
				$this->resolveRecordValues($recordValues, $current_user);
				$transformedRecord = $this->transformRecordWithGrouping($recordValues, $module);
				// Update entity fieldnames
				$transformedRecord['labelFields'] = $this->cachedEntityFieldnames($module);
				$resolvedModifiedRecords[] = $transformedRecord;

				$modifiedTimeInSeconds = strtotime($recordValues['modifiedtime']);
				if ($maxSyncTime < $modifiedTimeInSeconds) {
					$maxSyncTime = $modifiedTimeInSeconds;
				}
			}
		}

		////////////////////////////
		// DELETED RECORDS TRACKING
		////////////////////////////
		// Only when there is previous state information and is first page
		if (!empty($lastSyncTime) && $currentPage === 0) {
			global $adb;

			$queryDeletedParameters = array($module, date('Y-m-d H:i:s', $lastSyncTime));
			$andsmowneridequal = "";

			if ($hasAssignedToField) {
				if ($isPrivateMode) {
					$queryDeletedParameters[] = $current_user->id;
					$andsmowneridequal = " AND ottocrat_crmentity.smownerid=?";
				} else {
					$andsmowneridequal = Mobile_WS_Utils::querySecurityFromSuffix($module, $current_user);
				}
			}

			// Since Calendar and Events are merged
			if ($module == 'Calendar') {
				$queryDeleted = $adb->pquery("SELECT activityid as crmid, activitytype as setype FROM ottocrat_activity
					INNER JOIN ottocrat_crmentity ON ottocrat_activity.activityid=ottocrat_crmentity.crmid
					AND ottocrat_crmentity.deleted=1 AND ottocrat_crmentity.setype=? AND ottocrat_crmentity.modifiedtime > ?
					LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
					LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid WHERE 1=1	$andsmowneridequal ",
				$queryDeletedParameters);
			} else if ($module == 'Leads') {
				$queryDeleted = $adb->pquery("SELECT crmid, modifiedtime, setype FROM ottocrat_crmentity
				INNER JOIN ottocrat_leaddetails ON ottocrat_leaddetails.leadid=ottocrat_crmentity.crmid
				LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				WHERE (ottocrat_crmentity.deleted=1 OR (ottocrat_crmentity.deleted=0 AND ottocrat_leaddetails.converted=1)) AND ottocrat_crmentity.setype=? AND ottocrat_crmentity.modifiedtime > ? $andsmowneridequal", $queryDeletedParameters);
			} else {
				$queryDeleted = $adb->pquery("SELECT crmid, modifiedtime, setype FROM ottocrat_crmentity
				LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				WHERE ottocrat_crmentity.deleted=1 AND ottocrat_crmentity.setype=? AND ottocrat_crmentity.modifiedtime > ? $andsmowneridequal", $queryDeletedParameters);
			}

			while($row = $adb->fetch_array($queryDeleted)) {
				$recordModule = $row['setype'];
				if ($module == 'Calendar') {
					if ($row['setype'] != 'Task' && $row['setype'] != 'Emails') {
						$recordModule = 'Events';
					} else {
						$recordModule = $module;
					}
				}

				$resolvedDeletedRecords[] = sprintf("%sx%s", Mobile_WS_Utils::getEntityModuleWSId($recordModule), $row['crmid']);
				$modifiedTimeInSeconds = strtotime($row['modifiedtime']);
				if ($maxSyncTime < $modifiedTimeInSeconds) {
					$maxSyncTime = $modifiedTimeInSeconds;
				}
			}
		}

		$result = array(
			'nextSyncToken' => $maxSyncTime,
			'deleted' => $resolvedDeletedRecords,
			'updated' => $resolvedModifiedRecords,
			'nextPage'=> $nextPage, // Applies only to retrieve updated record details
		);

		$response = new Mobile_API_Response();
		$response->setResult( array( 'sync' => $result) );

		return $response;
	}
}