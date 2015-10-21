<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
chdir(dirname(__FILE__) . '/../../../');
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Ottocrat/Module.php';
include_once 'includes/main/WebUI.php';
vimport('includes.http.Request');

class PBXManager_PBXManager_Callbacks {
    
    function validateRequest($ottocratsecretkey,$request) {
        if($ottocratsecretkey == $request->get('ottocratsignature')){
            return true;
        }
        return false;
    }

    function process($request){
	$pbxmanagerController = new PBXManager_PBXManager_Controller();
        $connector = $pbxmanagerController->getConnector();
        if($this->validateRequest($connector->getOttocratSecretKey(),$request)) {
            $pbxmanagerController->process($request);
        }else {
            $response = $connector->getXmlResponse();
            echo $response;
        }
    }
}
$pbxmanager = new PBXManager_PBXManager_Callbacks();
$pbxmanager->process(new Ottocrat_Request($_REQUEST));
?>