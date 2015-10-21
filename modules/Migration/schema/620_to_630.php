<?php
/*+********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *********************************************************************************/

if(defined('OTTOCRAT_UPGRADE')) {
    
global $adb;

$query = 'SELECT DISTINCT profileid FROM ottocrat_profile2utility';
$result = $adb->pquery($query, array());

$profileId = $adb->query_result($result,0,'profileid');
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,5,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,6,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,10,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,5,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,6,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,10,0)',array());

for($i=1; $i< $adb->num_rows($result); $i++){

$profileId = $adb->query_result($result,$i,'profileid');

Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,5,1)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,6,1)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,10,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,5,1)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,6,1)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,10,0)',array());

}
}
chdir(dirname(__FILE__) . '/../../../');
require_once 'includes/main/WebUI.php';

$pickListFieldName = 'no_of_currency_decimals'; 
$moduleModel = Settings_Picklist_Module_Model::getInstance('Users'); 
$fieldModel = Ottocrat_Field_Model::getInstance($pickListFieldName, $moduleModel); 

if ($fieldModel) { 
    $moduleModel->addPickListValues($fieldModel, 0); 
    $moduleModel->addPickListValues($fieldModel, 1); 
    
    $pickListValues = Ottocrat_Util_Helper::getPickListValues($pickListFieldName); 
    $moduleModel->updateSequence($pickListFieldName, $pickListValues); 
} 
?>
