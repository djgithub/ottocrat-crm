<?php

/*+********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *********************************************************************************/

function vtws_history($element, $user) {
	$MAXLIMIT = 100;

	$adb = PearDatabase::getInstance();

	// Mandatory input validation
	if (empty($element['module']) && empty($element['record'])) {
		throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING, "Missing mandatory input values.");
	}

	if (!CRMEntity::getInstance('ModTracker') || !vtlib_isModuleActive('ModTracker')) {
		throw new WebServiceException("TRACKING_MODULE_NOT_ACTIVE", "Tracking module not active.");
	}

	$idComponents = NULL;

	$moduleName = $element['module'];
	$record = $element['record'];
	$mode = empty($element['mode'])? 'Private' : $element['mode']; // Private or All
	$page = empty($element['page'])? 0 : intval($element['page']); // Page to start

	$acrossAllModule = false;
	if ($moduleName == 'Home') $acrossAllModule = true;

	// Pre-condition check
	if (empty($moduleName)) {
		$moduleName = Mobile_WS_Utils::detectModulenameFromRecordId($record);
		$idComponents = vtws_getIdComponents($record); // We have it - as the input is validated.
	}

	if (!$acrossAllModule && !ModTracker::isTrackingEnabledForModule($moduleName)) {
		throw new WebServiceException("Module_NOT_TRACKED", "Module not tracked for changes.");
	}

	// Per-condition has been met, perform the operation
	$sql = '';
	$params = array();

	// REFER: modules/ModTracker/ModTracker.php

	// Two split phases for data extraction - so we can apply limit of retrieveal at record level.
	$sql = 'SELECT ottocrat_modtracker_basic.* FROM ottocrat_modtracker_basic
		INNER JOIN ottocrat_crmentity ON ottocrat_modtracker_basic.crmid = ottocrat_crmentity.crmid
		AND ottocrat_crmentity.deleted = 0';

	if ($mode == 'Private') {
		$sql .= ' WHERE ottocrat_modtracker_basic.whodid = ?';
		$params[] = $user->getId();
	} else if ($mode == 'All') {
		if ($acrossAllModule) {
			// TODO collate only active (or enabled) modules for tracking.
		} else if($moduleName) {
            $sql .= ' WHERE ottocrat_modtracker_basic.module = ?';
            $params[] = $moduleName;
        } else {
            $sql .= ' WHERE ottocrat_modtracker_basic.crmid = ?';
            $params[] = $idComponents[1];
        }
	}

	// Get most recently tracked changes with limit
	$start = $page*$MAXLIMIT; if ($start > 0) $start = $start + 1; // Adjust the start range
	$sql .= sprintf(' ORDER BY ottocrat_modtracker_basic.id DESC LIMIT %s,%s', $start, $MAXLIMIT);

	$result = $adb->pquery($sql, $params);

	$recordValuesMap = array();
	$orderedIds = array();

	while ($row = $adb->fetch_array($result)) {
		$orderedIds[] = $row['id'];

		$whodid = vtws_history_entityIdHelper('Users', $row['whodid']);
		$crmid = vtws_history_entityIdHelper($acrossAllModule? '' : $moduleName, $row['crmid']);
		$status = $row['status'];
		$statuslabel = '';
		switch ($status) {
			case ModTracker::$UPDATED: $statuslabel = 'updated'; break;
			case ModTracker::$DELETED: $statuslabel = 'deleted'; break;
			case ModTracker::$CREATED: $statuslabel = 'created'; break;
			case ModTracker::$RESTORED: $statuslabel = 'restored'; break;
			case ModTracker::$LINK: $statuslabel = 'link'; break;
			case ModTracker::$UNLINK: $statuslabel = 'unlink'; break;
		}
		$item['modifieduser'] = $whodid;
		$item['id'] = $crmid;
		$item['modifiedtime'] = $row['changedon'];
		$item['status'] = $status;
		$item['statuslabel'] = $statuslabel;
		$item['values'] = array();

		$recordValuesMap[$row['id']] = $item;
	}

	$historyItems = array();

	// Minor optimizatin to avoid 2nd query run when there is nothing to expect.
	if (!empty($orderedIds)) {
		$sql = 'SELECT ottocrat_modtracker_detail.* FROM ottocrat_modtracker_detail';
		$sql .= ' WHERE ottocrat_modtracker_detail.id IN (' . generateQuestionMarks($orderedIds) . ')';

		// LIMIT here is not required as $ids extracted is with limit at record level earlier.
		$params = $orderedIds;

		$result = $adb->pquery($sql, $params);
		while ($row = $adb->fetch_array($result)) {
			$item = $recordValuesMap[$row['id']];

			// NOTE: For reference field values transform them to webservice id.
			$item['values'][$row['fieldname']] = array(
				'previous' => $row['prevalue'],
				'current'  => $row['postvalue']
			);
			$recordValuesMap[$row['id']] = $item;
		}

		// Group the values per basic-transaction
		foreach ($orderedIds as $id) {
			$historyItems[] = $recordValuesMap[$id];
		}
	}

	return $historyItems;
}

// vtws_getWebserviceEntityId - seem to be missing the optimization
// which could pose performance challenge while gathering the changes made
// this helper function targets to cache and optimize the transformed values.
function vtws_history_entityIdHelper($moduleName, $id) {
	static $wsEntityIdCache = NULL;
	if ($wsEntityIdCache === NULL) {
		$wsEntityIdCache = array('users' => array(), 'records' => array());
	}

	if (!isset($wsEntityIdCache[$moduleName][$id])) {
		// Determine moduleName based on $id
		if (empty($moduleName)) {
			$moduleName = getSalesEntityType($id);
		}
		if($moduleName == 'Calendar') {
			$moduleName = vtws_getCalendarEntityType($id);
		}

		$wsEntityIdCache[$moduleName][$id] = vtws_getWebserviceEntityId($moduleName, $id);
	}
	return $wsEntityIdCache[$moduleName][$id];
}