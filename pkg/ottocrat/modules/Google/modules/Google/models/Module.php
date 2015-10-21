<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */
class Google_Module_Model extends Ottocrat_Module_Model {
    
    public static function removeSync($module, $id) {
        $db = PearDatabase::getInstance();
        $query = "DELETE FROM ottocrat_google_oauth WHERE service = ? AND userid = ?";
        $db->pquery($query, array($module, $id));
    }
    
    /**
     * Function to delete google synchronization completely. Deletes all mapping information stored.
     * @param <string> $module - Module Name
     * @param <integer> $user - User Id
     */
    public function deleteSync($module, $user) {
        if($module == 'Contacts' || $module == 'Calendar') {
            $name = 'Ottocrat_Google'.$module;
        }
        else {
            return;
        }
        $db = PearDatabase::getInstance();
        $db->pquery("DELETE FROM ottocrat_google_oauth2 WHERE service = ? AND userid = ?", array('Google'.$module, $user));
        $db->pquery("DELETE FROM ottocrat_google_sync WHERE googlemodule = ? AND user = ?", array($module, $user));
        
        $result = $db->pquery("SELECT stateencodedvalues FROM ottocrat_wsapp_sync_state WHERE name = ? AND userid = ?", array($name, $user));
        $stateValuesJson = $db->query_result($result, 0, 'stateencodedvalues');
        $stateValues = Zend_Json::decode(decode_html($stateValuesJson));
        $appKey = $stateValues['synctrackerid'];
        
        $result = $db->pquery("SELECT appid FROM ottocrat_wsapp WHERE appkey = ?", array($appKey));
        $appId = $db->query_result($result, 0, 'appid');
        
        $db->pquery("DELETE FROM ottocrat_wsapp_recordmapping WHERE appid = ?", array($appId));
        $db->pquery("DELETE FROM ottocrat_wsapp WHERE appid = ?", array($appId));
        $db->pquery("DELETE FROM ottocrat_wsapp_sync_state WHERE name = ? AND userid = ?", array($name, $user));
        if($module == 'Contacts') {
            $db->pquery("DELETE FROM ottocrat_google_sync_settings WHERE user = ?", array($user));
            $db->pquery("DELETE FROM ottocrat_google_sync_fieldmapping WHERE user = ?", array($user));
        }
        Google_Utils_Helper::errorLog();
        
        return;
    }
    
    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames() {
        return array();
    }
}

?>