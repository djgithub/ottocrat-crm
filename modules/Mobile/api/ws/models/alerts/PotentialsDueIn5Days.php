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

/** Upcoming Opportunity */
class Mobile_WS_AlertModel_PotentialsDueIn5Days extends Mobile_WS_AlertModel {
	function __construct() {
		parent::__construct();
		$this->name = 'Upcoming Opportunity';
		$this->moduleName = 'Potentials';
		$this->refreshRate= 1 * (24 * 60 * 60); // 1 day
		$this->description='Alert sent when Potential Close Date is due before 5 days or less';
	}
	
	function query() {
		$sql = Mobile_WS_Utils::getModuleListQuery('Potentials', 
					"ottocrat_potential.sales_stage not like 'Closed%' AND
					DATEDIFF(ottocrat_potential.closingdate, CURDATE()) <= 5"
				);
		return preg_replace("/^SELECT count\(\*\) as count(.*)/i", "SELECT crmid $1", Ottocrat_Functions::mkCountQuery($sql));
	}
	
	function queryParameters() {
		return array();
	}
}