<?php
/*********************************************************************************
** The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
*
 ********************************************************************************/
require_once('include/utils/CommonUtils.php');
require_once('include/database/PearDatabase.php');
/** Function to  returns the combo field values in array format
  * @param $combofieldNames -- combofieldNames:: Type string array
  * @returns $comboFieldArray -- comboFieldArray:: Type string array
 */
function getComboArray($combofieldNames)
{
	global $log,$mod_strings;
        $log->debug("Entering getComboArray(".$combofieldNames.") method ...");
	global $adb,$current_user;
        $roleid=$current_user->roleid;
	$comboFieldArray = Array();
	foreach ($combofieldNames as $tableName => $arrayName)
	{
		$fldArrName= $arrayName;
		$arrayName = Array();
		
		$sql = "select $tableName from ottocrat_$tableName";
		$params = array();
		if(!is_admin($current_user))
		{
			$subrole = getRoleSubordinates($roleid);
			if(count($subrole)> 0)
			{
				$roleids = $subrole;
				array_push($roleids, $roleid);
			}
			else
			{
				$roleids = $roleid;
			}
			$sql = "select distinct $tableName from ottocrat_$tableName  inner join ottocrat_role2picklist on ottocrat_role2picklist.picklistvalueid = ottocrat_$tableName.picklist_valueid where roleid in(". generateQuestionMarks($roleids) .") order by sortid";
			$params = array($roleids);
		}
		$result = $adb->pquery($sql, $params);	
		while($row = $adb->fetch_array($result))
		{
			$val = $row[$tableName];
			$arrayName[$val] = getTranslatedString($val);
		}
		$comboFieldArray[$fldArrName] = $arrayName;
	}
	$log->debug("Exiting getComboArray method ...");
	return $comboFieldArray;	
}
function getUniquePicklistID()
{
	global $adb;
	/*$sql="select id from ottocrat_picklistvalues_seq";
	$picklistvalue_id = $adb->query_result($adb->pquery($sql, array()),0,'id');

	$qry = "update ottocrat_picklistvalues_seq set id =?";
	$adb->pquery($qry, array(++$picklistvalue_id));*/
	return $adb->getUniqueID('ottocrat_picklistvalues');
}

?>
