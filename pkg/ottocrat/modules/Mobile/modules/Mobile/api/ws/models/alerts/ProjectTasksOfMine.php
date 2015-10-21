<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/../Alert.php';
class Mobile_WS_AlertModel_ProjectTasksOfMine extends Mobile_WS_AlertModel {
	function __construct() {
		parent::__construct();
		$this->name = 'My Project Task';
		$this->moduleName = 'ProjectTask';
		$this->refreshRate= 1 * (24* 60 * 60); // 1 day
		$this->description='Project Task Assigned To Me';
	}

	function query() {
		$sql = "SELECT crmid FROM ottocrat_crmentity INNER JOIN ottocrat_projecttask ON 
                    ottocrat_projecttask.projecttaskid=ottocrat_crmentity.crmid WHERE ottocrat_crmentity.deleted=0 AND ottocrat_crmentity.smownerid=? AND
                    ottocrat_projecttask.projecttaskprogress <> '100%';";
		return $sql;
	}
        function queryParameters() {
		return array($this->getUser()->id);
	}

	
}

