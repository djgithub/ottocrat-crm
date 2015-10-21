<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Leads_Module_Model extends Ottocrat_Module_Model {
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Ottocrat_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$links = parent::getSideBarLinks($linkParams);

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
			$links['SIDEBARLINK'][] = Ottocrat_Link_Model::getInstanceFromValues($quickLink);
		}
		
		return $links;
	}

    /**
	 * Function returns Settings Links
	 * @return Array
	 */
	public function getSettingLinks() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = parent::getSettingLinks();
		
		if($currentUserModel->isAdminUser()) {
			$settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_CUSTOM_FIELD_MAPPING',
					'linkurl' =>Ottocrat_Request:: encryptLink('index.php?parent=Settings&module=Leads&view=MappingDetail'),
					'linkicon' => '');
			
		}
		return $settingLinks;
	}

    /**
    * Function returns deleted records condition
    */
    public function getDeletedRecordCondition() {
       return 'ottocrat_crmentity.deleted = 0 AND ottocrat_leaddetails.converted = 0';
    }

    /**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Ottocrat_Record_Model or Module Specific Record Model instances
	 */
	public function getRecentRecords($limit=10) {
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
        $deletedCondition = $this->getDeletedRecordCondition();
		$query = 'SELECT * FROM ottocrat_crmentity '.
            ' INNER JOIN ottocrat_leaddetails ON
                ottocrat_leaddetails.leadid = ottocrat_crmentity.crmid
                WHERE setype=? AND '.$deletedCondition.' AND modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = array($this->get('name'), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$recentRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

	/**
	 * Function returns the Number of Leads created per week
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsCreated($owner, $dateFilter) {
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

		$result = $db->pquery('SELECT COUNT(*) AS count, date(createdtime) AS time FROM ottocrat_leaddetails
						INNER JOIN ottocrat_crmentity ON ottocrat_leaddetails.leadid = ottocrat_crmentity.crmid
						AND deleted=0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).$ownerSql.' '.$dateFilterSql.' AND converted = 0 GROUP BY week(createdtime)',
					$params);

		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$response[$i][1] = $row['time'];
		}
		return $response;
	}

	/**
	 * Function returns Leads grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsByStatus($owner,$dateFilter) {
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

		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN ottocrat_leadstatus.leadstatus IS NULL OR ottocrat_leadstatus.leadstatus = "" THEN "" ELSE 
						ottocrat_leadstatus.leadstatus END AS leadstatusvalue FROM ottocrat_leaddetails 
						INNER JOIN ottocrat_crmentity ON ottocrat_leaddetails.leadid = ottocrat_crmentity.crmid
						AND deleted=0 AND converted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql .' '.$dateFilterSql.
						'INNER JOIN ottocrat_leadstatus ON ottocrat_leaddetails.leadstatus = ottocrat_leadstatus.leadstatus 
						GROUP BY leadstatusvalue ORDER BY ottocrat_leadstatus.sortorderid', $params);

		$response = array();
		
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$leadStatusVal = $row['leadstatusvalue'];
			if($leadStatusVal == '') {
				$leadStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($leadStatusVal, $this->getName());
			$response[$i][2] = $leadStatusVal;
		}
		return $response;
	}

	/**
	 * Function returns Leads grouped by Source
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsBySource($owner,$dateFilter) {
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
		
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN ottocrat_leaddetails.leadsource IS NULL OR ottocrat_leaddetails.leadsource = "" THEN "" 
						ELSE ottocrat_leaddetails.leadsource END AS leadsourcevalue FROM ottocrat_leaddetails 
						INNER JOIN ottocrat_crmentity ON ottocrat_leaddetails.leadid = ottocrat_crmentity.crmid
						AND deleted=0 AND converted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql .' '.$dateFilterSql.
						'INNER JOIN ottocrat_leadsource ON ottocrat_leaddetails.leadsource = ottocrat_leadsource.leadsource 
						GROUP BY leadsourcevalue ORDER BY ottocrat_leadsource.sortorderid', $params);
		
		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$leadSourceVal =  $row['leadsourcevalue'];
			if($leadSourceVal == '') {
				$leadSourceVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($leadSourceVal, $this->getName());
			$response[$i][2] = $leadSourceVal;
		}
		return $response;
	}

	/**
	 * Function returns Leads grouped by Industry
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsByIndustry($owner,$dateFilter) {
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
		
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN ottocrat_leaddetails.industry IS NULL OR ottocrat_leaddetails.industry = "" THEN "" 
						ELSE ottocrat_leaddetails.industry END AS industryvalue FROM ottocrat_leaddetails 
						INNER JOIN ottocrat_crmentity ON ottocrat_leaddetails.leadid = ottocrat_crmentity.crmid
						AND deleted=0 AND converted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql .' '.$dateFilterSql.'
						INNER JOIN ottocrat_industry ON ottocrat_leaddetails.industry = ottocrat_industry.industry 
						GROUP BY industryvalue ORDER BY ottocrat_industry.sortorderid', $params);
		
		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$industyValue = $row['industryvalue'];
			if($industyValue == '') {
				$industyValue = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($industyValue, $this->getName());
			$response[$i][2] = $industyValue;
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
	 * Function to get Converted Information for selected records
	 * @param <array> $recordIdsList
	 * @return <array> converted Info
	 */
	public static function getConvertedInfo($recordIdsList = array()) {
		$convertedInfo = array();
		if ($recordIdsList) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT converted FROM ottocrat_leaddetails WHERE leadid IN (".generateQuestionMarks($recordIdsList).")", $recordIdsList);
			$numOfRows = $db->num_rows($result);

			for ($i=0; $i<$numOfRows; $i++) {
				$convertedInfo[$recordIdsList[$i]] = $db->query_result($result, $i, 'converted');
			}
		}
		return $convertedInfo;
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
		if (in_array($sourceModule, array('Campaigns', 'Products', 'Services', 'Emails'))) {
			switch ($sourceModule) {
				case 'Campaigns'	: $tableName = 'ottocrat_campaignleadrel';	$fieldName = 'leadid';	$relatedFieldName ='campaignid';	break;
				case 'Products'		: $tableName = 'ottocrat_seproductsrel';		$fieldName = 'crmid';		$relatedFieldName ='productid';		break;
			}

			if ($sourceModule === 'Services') {
				$condition = " ottocrat_leaddetails.leadid NOT IN (SELECT relcrmid FROM ottocrat_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM ottocrat_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' ottocrat_leaddetails.emailoptout = 0';
			} else {
				$condition = " ottocrat_leaddetails.leadid NOT IN (SELECT $fieldName FROM $tableName WHERE $relatedFieldName = '$record')";
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