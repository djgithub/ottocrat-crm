<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/PendingTicketsOfMine.php';

/** Idle Ticket Alert */
class Mobile_WS_AlertModel_IdleTicketsOfMine extends Mobile_WS_AlertModel_PendingTicketsOfMine {
	function __construct() {
		parent::__construct();
		$this->name = 'Idle Ticket Alert';
		$this->moduleName = 'HelpDesk';
		$this->refreshRate= 1 * (60 * 60); // 1 hour
		$this->description='Alert sent when ticket has not been updated in 24 hours';
	}
	
	function query() {
		$sql = parent::query();
		$sql .= " AND DATEDIFF(CURDATE(), ottocrat_crmentity.modifiedtime) > 1";
		return $sql;
	}
}