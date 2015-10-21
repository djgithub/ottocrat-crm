<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ************************************************************************************/

class Accounts_Module_Model extends Ottocrat_Module_Model {

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
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (($sourceModule == 'Accounts' && $field == 'account_id' && $record)
				|| in_array($sourceModule, array('Campaigns', 'Products', 'Services', 'Emails'))) {

			if ($sourceModule === 'Campaigns') {
				$condition = " ottocrat_account.accountid NOT IN (SELECT accountid FROM ottocrat_campaignaccountrel WHERE campaignid = '$record')";
			} elseif ($sourceModule === 'Products') {
				$condition = " ottocrat_account.accountid NOT IN (SELECT crmid FROM ottocrat_seproductsrel WHERE productid = '$record')";
			} elseif ($sourceModule === 'Services') {
				$condition = " ottocrat_account.accountid NOT IN (SELECT relcrmid FROM ottocrat_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM ottocrat_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' ottocrat_account.emailoptout = 0';
			} else {
				$condition = " ottocrat_account.accountid != '$record'";
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

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Ottocrat_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		if ($functionName === 'get_activities') {
			$focus = CRMEntity::getInstance($this->getName());
			$focus->id = $recordId;
			$entityIds = $focus->getRelatedContactsIds();
			$entityIds = implode(',', $entityIds);

			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (ottocrat_users.user_name not like '') THEN $userNameSql ELSE ottocrat_groups.groupname END AS user_name,
						ottocrat_crmentity.*, ottocrat_activity.activitytype, ottocrat_activity.subject, ottocrat_activity.date_start, ottocrat_activity.time_start,
						ottocrat_activity.recurringtype, ottocrat_activity.due_date, ottocrat_activity.time_end, ottocrat_activity.visibility, ottocrat_seactivityrel.crmid AS parent_id,
						CASE WHEN (ottocrat_activity.activitytype = 'Task') THEN (ottocrat_activity.status) ELSE (ottocrat_activity.eventstatus) END AS status
						FROM ottocrat_activity
						INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_activity.activityid
						LEFT JOIN ottocrat_seactivityrel ON ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
						LEFT JOIN ottocrat_cntactivityrel ON ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid
						LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
						LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
							WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_activity.activitytype <> 'Emails'
								AND (ottocrat_seactivityrel.crmid = ".$recordId;
			if($entityIds) {
				$query .= " OR ottocrat_cntactivityrel.contactid IN (".$entityIds."))";
			} else {
				$query .= ")";
			}

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}

			// There could be more than one contact for an activity.
			$query .= ' GROUP BY ottocrat_activity.activityid';
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
		}

		return $query;
	}
}
