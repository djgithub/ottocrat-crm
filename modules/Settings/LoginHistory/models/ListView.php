<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
class Settings_LoginHistory_ListView_Model extends Settings_Ottocrat_ListView_Model {

	/**
	 * Funtion to get the Login history basic query
	 * @return type
	 */
    public function getBasicListQuery() {
        $module = $this->getModule();
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		
		$query = "SELECT login_id, $userNameSql AS user_name, user_ip, logout_time, login_time, ottocrat_loginhistory.status FROM $module->baseTable 
				INNER JOIN ottocrat_users ON ottocrat_users.user_name = $module->baseTable.user_name";
		
		$search_key = $this->get('search_key');
		$value = $this->get('search_value');
		
		if(!empty($search_key) && !empty($value)) {
			$query .= " WHERE $module->baseTable.$search_key = '$value'";
		}
		//Fix for http://trac.ottocrat.com/cgi-bin/trac.cgi/ticket/7996
         $query .= " ORDER BY login_time DESC"; 
 	 return $query; 
    }

	public function getListViewLinks() {
		return array();
	}
	
	/** 
	 * Function which will get the list view count  
	 * @return - number of records 
	 */

	public function getListViewCount() {
		$db = PearDatabase::getInstance();

		$module = $this->getModule();
		$listQuery = "SELECT count(*) AS count FROM $module->baseTable INNER JOIN ottocrat_users ON ottocrat_users.user_name = $module->baseTable.user_name";

		$search_key = $this->get('search_key');
		$value = $this->get('search_value');
		
		if(!empty($search_key) && !empty($value)) {
			$listQuery .= " WHERE $module->baseTable.$search_key = '$value'";
		}

		$listResult = $db->pquery($listQuery, array());
		return $db->query_result($listResult, 0, 'count');
	}
}
