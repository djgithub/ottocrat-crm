<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ************************************************************************************/

class Potentials_Module_Model extends Ottocrat_Module_Model {

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
	 * Function returns number of Open Potentials in each of the sales stage
	 * @param <Integer> $owner - userid
	 * @return <Array>
	 */
	public function getPotentialsCountBySalesStage($owner, $dateFilter) {
		$db = PearDatabase::getInstance();

		if (!$owner) {
			$currenUserModel = Users_Record_Model::getCurrentUserModel();
			$owner = $currenUserModel->getId();
		} else if ($owner === 'all') {
			$owner = '';
		}

		$params = array();
		if(!empty($owner)) {
			$ownerSql =  ' AND smownerid = ? ';
			$params[] = $owner;
		}
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND closingdate BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}

		$result = $db->pquery('SELECT COUNT(*) count, sales_stage FROM ottocrat_potential
						INNER JOIN ottocrat_crmentity ON ottocrat_potential.potentialid = ottocrat_crmentity.crmid
						AND deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql . $dateFilterSql . ' AND sales_stage NOT IN ("Closed Won", "Closed Lost")
							GROUP BY sales_stage ORDER BY count desc', $params);
		
		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$saleStage = $db->query_result($result, $i, 'sales_stage');
			$response[$i][0] = $saleStage;
			$response[$i][1] = $db->query_result($result, $i, 'count');
			$response[$i][2] = vtranslate($saleStage, $this->getName());
		}
		return $response;
	}

	/**
	 * Function returns number of Open Potentials for each of the sales person
	 * @param <Integer> $owner - userid
	 * @return <Array>
	 */
	public function getPotentialsCountBySalesPerson() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
		$result = $db->pquery('SELECT COUNT(*) AS count, concat(first_name," ",last_name) as last_name, ottocrat_potential.sales_stage FROM ottocrat_potential
						INNER JOIN ottocrat_crmentity ON ottocrat_potential.potentialid = ottocrat_crmentity.crmid
						INNER JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid AND ottocrat_users.status="ACTIVE"
						AND ottocrat_crmentity.deleted = 0'.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).'
						INNER JOIN ottocrat_sales_stage ON ottocrat_potential.sales_stage =  ottocrat_sales_stage.sales_stage 
						GROUP BY smownerid, sales_stage ORDER BY ottocrat_sales_stage.sortorderid', $params);

		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i]['count'] = $row['count'];
			$response[$i]['last_name'] = decode_html($row['last_name']);
			$response[$i]['sales_stage'] = $row['sales_stage'];
			//$response[$i][2] = $row['']
 		}
		return $response;
	}

	/**
	 * Function returns Potentials Amount for each Sales Person
	 * @return <Array>
	 */
	function getPotentialsPipelinedAmountPerSalesPerson() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
		$result = $db->pquery('SELECT sum(amount) AS amount, concat(first_name," ",last_name) as last_name, ottocrat_potential.sales_stage FROM ottocrat_potential
						INNER JOIN ottocrat_crmentity ON ottocrat_potential.potentialid = ottocrat_crmentity.crmid
						INNER JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid AND ottocrat_users.status="ACTIVE"
						AND ottocrat_crmentity.deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).
						'INNER JOIN ottocrat_sales_stage ON ottocrat_potential.sales_stage =  ottocrat_sales_stage.sales_stage 
						WHERE ottocrat_potential.sales_stage NOT IN ("Closed Won", "Closed Lost")
						GROUP BY smownerid, sales_stage ORDER BY ottocrat_sales_stage.sortorderid', $params);
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
                        $row['last_name'] = decode_html($row['last_name']);
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Function returns Total Revenue for each Sales Person
	 * @return <Array>
	 */
	function getTotalRevenuePerSalesPerson($dateFilter) {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
		$params[] = 'Closed Won';
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}
		
		$result = $db->pquery('SELECT sum(amount) amount, concat(first_name," ",last_name) as last_name,ottocrat_users.id as id,DATE_FORMAT(closingdate, "%d-%m-%Y") AS closingdate  FROM ottocrat_potential
						INNER JOIN ottocrat_crmentity ON ottocrat_potential.potentialid = ottocrat_crmentity.crmid
						INNER JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid AND ottocrat_users.status="ACTIVE"
						AND ottocrat_crmentity.deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).'WHERE sales_stage = ? '.' '.$dateFilterSql.' GROUP BY smownerid', $params);
		$data = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
                        $row['last_name'] = decode_html($row['last_name']);
			$data[] = $row;
		}
		return $data;
	}

	 /**
	 * Function returns Top Potentials Header
	 * 
	 */
    
    function getTopPotentialsHeader() {
        
         $headerArray = array('potentialname' => 'Potential Name');
         $fieldsToDisplay=  array("amount","related_to"); 
         $moduleModel = Ottocrat_Module_Model::getInstance('Potentials');
          foreach ($fieldsToDisplay as $value) {
                     $fieldInstance = Ottocrat_Field_Model::getInstance($value,$moduleModel); 
                          if($fieldInstance->isViewable()){
                                $headerArray = array_merge($headerArray,array($value =>$fieldInstance->label));
                               }
           }
        return $headerArray;
    }
    
	/**
	 * Function returns Top Potentials
	 * @return <Array of Ottocrat_Record_Model>
	 */
	function getTopPotentials($pagingModel) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
   
        $moduleModel = Ottocrat_Module_Model::getInstance('Potentials');
        $fieldsToDisplay=  array("amount","related_to");
         
        $query = "SELECT crmid , potentialname " ; 
        foreach ($fieldsToDisplay as $value) {
                      $fieldInstance = Ottocrat_Field_Model::getInstance($value,$moduleModel); 

                            if($fieldInstance->isViewable()){
                                $query= $query. ', ' .$value;
                          }
           }
        
           $query = $query . " FROM ottocrat_potential
						INNER JOIN ottocrat_crmentity ON ottocrat_potential.potentialid = ottocrat_crmentity.crmid
							AND deleted = 0 ".Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName())."
						WHERE sales_stage NOT IN ('Closed Won', 'Closed Lost') AND amount > 0
						ORDER BY amount DESC LIMIT ".$pagingModel->getStartIndex().", ".$pagingModel->getPageLimit()."";
     	$result = $db->pquery($query, array());

		$models = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$modelInstance = Ottocrat_Record_Model::getCleanInstance('Potentials');
			$modelInstance->setId($db->query_result($result, $i, 'crmid'));
			$modelInstance->set('amount', $db->query_result($result, $i, 'amount'));
			$modelInstance->set('potentialname', $db->query_result($result, $i, 'potentialname'));
			$modelInstance->set('related_to', $db->query_result($result, $i, 'related_to'));
			$models[] = $modelInstance;
		}
		return $models;
	}

	/**
	 * Function returns Potentials Forecast Amount
	 * @return <Array>
	 */
	function getForecast($closingdateFilter,$dateFilter) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$params = array();
		$params[] = $currentUser->getId();
		if(!empty($closingdateFilter)) {
			$closingdateFilterSql = ' AND closingdate BETWEEN ? AND ? ';
			$params[] = $closingdateFilter['start'];
			$params[] = $closingdateFilter['end'];
		}
		
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}
		
		$result = $db->pquery('SELECT forecast_amount, DATE_FORMAT(closingdate, "%m-%d-%Y") AS closingdate FROM ottocrat_potential
					INNER JOIN ottocrat_crmentity ON ottocrat_potential.potentialid = ottocrat_crmentity.crmid
					AND deleted = 0 AND smownerid = ? WHERE closingdate >= CURDATE() AND sales_stage NOT IN ("Closed Won", "Closed Lost")'.
					' '.$closingdateFilterSql.$dateFilterSql,
					$params);

		$forecast = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$forecast[] = $row;
		}
		return $forecast;

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
	 * Function returns Potentials Amount for each Sales Stage
	 * @return <Array>
	 */
	function getPotentialTotalAmountBySalesStage() {
		//$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$picklistValues = Ottocrat_Util_Helper::getPickListValues('sales_stage');
		$data = array();
		foreach ($picklistValues as $key => $picklistValue) {
			$result = $db->pquery('SELECT SUM(amount) AS amount FROM ottocrat_potential
								   INNER JOIN ottocrat_crmentity ON ottocrat_potential.potentialid = ottocrat_crmentity.crmid
								   AND deleted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).' WHERE sales_stage = ?', array($picklistValue));
			$num_rows = $db->num_rows($result);
			for($i=0; $i<$num_rows; $i++) {
				$values = array();
				$amount = $db->query_result($result, $i, 'amount');
				if(!empty($amount)){
					$values[0] = $db->query_result($result, $i, 'amount');
					$values[1] = vtranslate($picklistValue, $this->getName());
					$data[] = $values;
				}
				
			}
		}
		return $data;
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
		if (in_array($sourceModule, array('Products', 'Services'))) {
			if ($sourceModule === 'Products') {
				$condition = " ottocrat_potential.potentialid NOT IN (SELECT crmid FROM ottocrat_seproductsrel WHERE productid = '$record')";
			} elseif ($sourceModule === 'Services') {
				$condition = " ottocrat_potential.potentialid NOT IN (SELECT relcrmid FROM ottocrat_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM ottocrat_crmentityrel WHERE relcrmid = '$record') ";
			}

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

	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	public function getSearchRecordsQuery($searchValue, $parentId=false, $parentModule=false) {
		if($parentId && in_array($parentModule, array('Accounts', 'Contacts'))) {
			$query = "SELECT * FROM ottocrat_crmentity
						INNER JOIN ottocrat_potential ON ottocrat_potential.potentialid = ottocrat_crmentity.crmid
						WHERE deleted = 0 AND ottocrat_potential.related_to = $parentId AND label like '%$searchValue%'";
			return $query;
		}
		return parent::getSearchRecordsQuery($parentId, $parentModule);
	}
}