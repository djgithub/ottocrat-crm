<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

//Overrides GetRelatedList : used to get related query
//TODO : Eliminate below hacking solution
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Ottocrat/Module.php';
include_once 'includes/main/WebUI.php';

$webUI = new Ottocrat_WebUI();
$webUI->process(new Ottocrat_Request($_REQUEST, $_REQUEST));
//$webUI->process(new Ottocrat_Request($_SERVER['QUERY_STRING'], $_REQUEST));
