<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ************************************************************************************/

class Contacts_Module_Model extends Ottocrat_Module_Model {
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Ottocrat_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);

		$quickLink = array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_DASHBOARD',
				'linkurl' => $this->getDashBoardUrl(),
				'linkicon' => '',
		);

		//Check profile permissions for Dashboards
		$moduleModel = Ottocrat_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			$parentQuickLinks['SIDEBARLINK'][] = Ottocrat_Link_Model::getInstanceFromValues($quickLink);
		}

		return $parentQuickLinks;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <Ottocrat_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Ottocrat_Datetime_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Ottocrat_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$query = "SELECT ottocrat_crmentity.crmid, crmentity2.crmid AS contact_id, ottocrat_crmentity.smownerid, ottocrat_crmentity.setype, ottocrat_activity.* FROM ottocrat_activity
					INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_activity.activityid
					INNER JOIN ottocrat_cntactivityrel ON ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid
					INNER JOIN ottocrat_crmentity AS crmentity2 ON ottocrat_cntactivityrel.contactid = crmentity2.crmid AND crmentity2.deleted = 0 AND crmentity2.setype = ?
					LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

		$query .= " WHERE ottocrat_crmentity.deleted=0
					AND (ottocrat_activity.activitytype NOT IN ('Emails'))
					AND (ottocrat_activity.status is NULL OR ottocrat_activity.status NOT IN ('Completed', 'Deferred'))
					AND (ottocrat_activity.eventstatus is NULL OR ottocrat_activity.eventstatus NOT IN ('Held'))";

		if ($recordId) {
			$query .= " AND ottocrat_cntactivityrel.contactid = ?";
		} elseif ($mode === 'upcoming') {
			$query .= " AND due_date >= '$currentDate'";
		} elseif ($mode === 'overdue') {
			$query .= " AND due_date < '$currentDate'";
		}

		$params = array($this->getName());
		if ($recordId) {
			array_push($params, $recordId);
		}

		if($user != 'all' && $user != '') {
			if($user === $currentUser->id) {
				$query .= " AND ottocrat_crmentity.smownerid = ?";
				array_push($params, $user);
			}
		}

		$query .= " ORDER BY date_start, time_start LIMIT ". $pagingModel->getStartIndex() .", ". ($pagingModel->getPageLimit()+1);

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);
		
		$groupsIds = Ottocrat_Util_Helper::getGroupsIdsForUsers($currentUser->getId());
		$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$newRow = $db->query_result_rowdata($result, $i);
			$model = Ottocrat_Record_Model::getCleanInstance('Calendar');
			$ownerId = $newRow['smownerid'];
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$visibleFields = array('activitytype','date_start','time_start','due_date','time_end','assigned_user_id','visibility','smownerid','crmid');
			$visibility = true;
			if(in_array($ownerId, $groupsIds)) {
				$visibility = false;
			} else if($ownerId == $currentUser->getId()){
				$visibility = false;
			}
			if(!$currentUser->isAdminUser() && $newRow['activitytype'] != 'Task' && $newRow['visibility'] == 'Private' && $ownerId && $visibility) {
				foreach($newRow as $data => $value) {
					if(in_array($data, $visibleFields) != -1) {
						unset($newRow[$data]);
					}
				}
				$newRow['subject'] = vtranslate('Busy','Events').'*';
			}
			if($newRow['activitytype'] == 'Task') {
				unset($newRow['visibility']);
			}
			
			$model->setData($newRow);
			$model->setId($newRow['crmid']);
			$activities[] = $model;
		}
		
		$pagingModel->calculatePageRange($activities);
		if($numOfRows > $pagingModel->getPageLimit()){
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $activities;
	}

	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	function getSearchRecordsQuery($searchValue, $parentId=false, $parentModule=false) {
		if($parentId && $parentModule == 'Accounts') {
			$query = "SELECT * FROM ottocrat_crmentity
						INNER JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_crmentity.crmid
						WHERE deleted = 0 AND ottocrat_contactdetails.accountid = $parentId AND label like '%$searchValue%'";
			return $query;
		} else if($parentId && $parentModule == 'Potentials') {
			$query = "SELECT * FROM ottocrat_crmentity
						INNER JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_crmentity.crmid
						LEFT JOIN ottocrat_contpotentialrel ON ottocrat_contpotentialrel.contactid = ottocrat_contactdetails.contactid
						LEFT JOIN ottocrat_potential ON ottocrat_potential.contact_id = ottocrat_contactdetails.contactid
						WHERE deleted = 0 AND (ottocrat_contpotentialrel.potentialid = $parentId OR ottocrat_potential.potentialid = $parentId)
						AND label like '%$searchValue%'";
			
				return $query;
		} else if ($parentId && $parentModule == 'HelpDesk') {
            $query = "SELECT * FROM ottocrat_crmentity
                        INNER JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_crmentity.crmid
                        INNER JOIN ottocrat_troubletickets ON ottocrat_troubletickets.contact_id = ottocrat_contactdetails.contactid
                        WHERE deleted=0 AND ottocrat_troubletickets.ticketid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if($parentId && $parentModule == 'Campaigns') {
            $query = "SELECT * FROM ottocrat_crmentity
                        INNER JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_crmentity.crmid
                        INNER JOIN ottocrat_campaigncontrel ON ottocrat_campaigncontrel.contactid = ottocrat_contactdetails.contactid
                        WHERE deleted=0 AND ottocrat_campaigncontrel.campaignid = $parentId AND label like '%$searchValue%'";

            return $query;
        } else if($parentId && $parentModule == 'Vendors') {
            $query = "SELECT ottocrat_crmentity.* FROM ottocrat_crmentity
                        INNER JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_crmentity.crmid
                        INNER JOIN ottocrat_vendorcontactrel ON ottocrat_vendorcontactrel.contactid = ottocrat_contactdetails.contactid
                        WHERE deleted=0 AND ottocrat_vendorcontactrel.vendorid = $parentId AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'Quotes') {
            $query = "SELECT * FROM ottocrat_crmentity
                        INNER JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_crmentity.crmid
                        INNER JOIN ottocrat_quotes ON ottocrat_quotes.contactid = ottocrat_contactdetails.contactid
                        WHERE deleted=0 AND ottocrat_quotes.quoteid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'PurchaseOrder') {
            $query = "SELECT * FROM ottocrat_crmentity
                        INNER JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_crmentity.crmid
                        INNER JOIN ottocrat_purchaseorder ON ottocrat_purchaseorder.contactid = ottocrat_contactdetails.contactid
                        WHERE deleted=0 AND ottocrat_purchaseorder.purchaseorderid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'SalesOrder') {
            $query = "SELECT * FROM ottocrat_crmentity
                        INNER JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_crmentity.crmid
                        INNER JOIN ottocrat_salesorder ON ottocrat_salesorder.contactid = ottocrat_contactdetails.contactid
                        WHERE deleted=0 AND ottocrat_salesorder.salesorderid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'Invoice') {
            $query = "SELECT * FROM ottocrat_crmentity
                        INNER JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_crmentity.crmid
                        INNER JOIN ottocrat_invoice ON ottocrat_invoice.contactid = ottocrat_contactdetails.contactid
                        WHERE deleted=0 AND ottocrat_invoice.invoiceid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        }

		return parent::getSearchRecordsQuery($parentId, $parentModule);
	}


	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Ottocrat_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		if ($functionName === 'get_activities') {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (ottocrat_users.user_name not like '') THEN $userNameSql ELSE ottocrat_groups.groupname END AS user_name,
						ottocrat_cntactivityrel.contactid, ottocrat_seactivityrel.crmid AS parent_id,
						ottocrat_crmentity.*, ottocrat_activity.activitytype, ottocrat_activity.subject, ottocrat_activity.date_start, ottocrat_activity.time_start,
						ottocrat_activity.recurringtype, ottocrat_activity.due_date, ottocrat_activity.time_end, ottocrat_activity.visibility,
						CASE WHEN (ottocrat_activity.activitytype = 'Task') THEN (ottocrat_activity.status) ELSE (ottocrat_activity.eventstatus) END AS status
						FROM ottocrat_activity
						INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_activity.activityid
						INNER JOIN ottocrat_cntactivityrel ON ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid
						LEFT JOIN ottocrat_seactivityrel ON ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
						LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
						LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
							WHERE ottocrat_cntactivityrel.contactid = ".$recordId." AND ottocrat_crmentity.deleted = 0
								AND ottocrat_activity.activitytype <> 'Emails'";

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
		}

		return $query;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Campaigns', 'Potentials', 'Vendors', 'Products', 'Services', 'Emails'))
				|| ($sourceModule === 'Contacts' && $field === 'contact_id' && $record)) {
			switch ($sourceModule) {
				case 'Campaigns'	: $tableName = 'ottocrat_campaigncontrel';	$fieldName = 'contactid';	$relatedFieldName ='campaignid';	break;
				case 'Potentials'	: $tableName = 'ottocrat_contpotentialrel';	$fieldName = 'contactid';	$relatedFieldName ='potentialid';	break;
				case 'Vendors'		: $tableName = 'ottocrat_vendorcontactrel';	$fieldName = 'contactid';	$relatedFieldName ='vendorid';		break;
				case 'Products'		: $tableName = 'ottocrat_seproductsrel';		$fieldName = 'crmid';		$relatedFieldName ='productid';		break;
			}

			if ($sourceModule === 'Services') {
				$condition = " ottocrat_contactdetails.contactid NOT IN (SELECT relcrmid FROM ottocrat_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM ottocrat_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' ottocrat_contactdetails.emailoptout = 0';
			} elseif ($sourceModule === 'Contacts' && $field === 'contact_id') {
				$condition = " ottocrat_contactdetails.contactid != '$record'";
			} else {
				$condition = " ottocrat_contactdetails.contactid NOT IN (SELECT $fieldName FROM $tableName WHERE $relatedFieldName = '$record')";
			}

			$position = stripos($listQuery, 'where');
			if($position) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
    
    public function getDefaultSearchField(){
        return "lastname";
    }
    
}