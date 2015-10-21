<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ************************************************************************************/

class HelpDesk_Module_Model extends Ottocrat_Module_Model {

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
	 * Function to get Settings links for admin user
	 * @return Array
	 */
	public function getSettingLinks() {
		$settingsLinks = parent::getSettingLinks();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if ($currentUserModel->isAdminUser()) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_MAILSCANNER',
				'linkurl' =>Ottocrat_Request:: encryptLink('index.php?parent=Settings&module=MailConverter&view=List'),
				'linkicon' => ''
			);
		}
		return $settingsLinks;
	}


	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getOpenTickets() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$result = $db->pquery('SELECT count(*) AS count, concat(ottocrat_users.first_name, " " ,ottocrat_users.last_name) as name, ottocrat_users.id as id  FROM ottocrat_troubletickets
						INNER JOIN ottocrat_crmentity ON ottocrat_troubletickets.ticketid = ottocrat_crmentity.crmid
						INNER JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid AND ottocrat_users.status="ACTIVE"
						AND ottocrat_crmentity.deleted = 0'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).
						' WHERE ottocrat_troubletickets.status = ? GROUP BY smownerid', array('Open'));

		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getTicketsByStatus($owner, $dateFilter) {
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if(!empty($ownerSql)) {
			$ownerSql = ' AND '.$ownerSql;
		}
		
		$params = array();
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}
		
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN ottocrat_troubletickets.status IS NULL OR ottocrat_troubletickets.status = "" THEN "" ELSE ottocrat_troubletickets.status END AS statusvalue 
							FROM ottocrat_troubletickets INNER JOIN ottocrat_crmentity ON ottocrat_troubletickets.ticketid = ottocrat_crmentity.crmid AND ottocrat_crmentity.deleted=0
							'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql .' '.$dateFilterSql.
							' INNER JOIN ottocrat_ticketstatus ON ottocrat_troubletickets.status = ottocrat_ticketstatus.ticketstatus GROUP BY statusvalue ORDER BY ottocrat_ticketstatus.sortorderid', $params);

		$response = array();

		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$ticketStatusVal = $row['statusvalue'];
			if($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($ticketStatusVal, $this->getName());
			$response[$i][2] = $ticketStatusVal;
		}
		return $response;
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
								AND ottocrat_seactivityrel.crmid = ".$recordId;

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
		if (in_array($sourceModule, array('Assets', 'Project', 'ServiceContracts', 'Services'))) {
			$condition = " ottocrat_troubletickets.ticketid NOT IN (SELECT relcrmid FROM ottocrat_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM ottocrat_crmentityrel WHERE relcrmid = '$record') ";
			$pos = stripos($listQuery, 'where');

			if ($pos) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
}
