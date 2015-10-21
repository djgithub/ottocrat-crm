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

/** Events for today alert */
class Mobile_WS_AlertModel_EventsOfMineToday extends Mobile_WS_AlertModel {
	function __construct() {
		parent::__construct();
		$this->name = 'Your events for the day';
		$this->moduleName = 'Calendar';
		$this->refreshRate= 1 * (24* 60 * 60); // 1 day
		$this->description='Alert sent when events are scheduled for the day';
	}
	
	function query() {
		$today = date('Y-m-d');
		$sql = "SELECT crmid, activitytype FROM ottocrat_activity INNER JOIN 
				ottocrat_crmentity ON ottocrat_crmentity.crmid=ottocrat_activity.activityid
				WHERE ottocrat_crmentity.deleted=0 AND ottocrat_crmentity.smownerid=? AND 
				ottocrat_activity.activitytype <> 'Emails' AND 
				(ottocrat_activity.date_start = '{$today}' OR ottocrat_activity.due_date = '{$today}')";
		return $sql;
	}
	
	function queryParameters() {
		return array($this->getUser()->id);
	}
}
