<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PBXManager_Gateway_Action extends Settings_Ottocrat_IndexAjax_View{
    
    function __construct() {
        $this->exposeMethod('getSecretKey');
    }
    
    public function process(Ottocrat_Request $request) {
        $this->getSecretKey($request);
    }
    
    public function getSecretKey(Ottocrat_Request $request) {
        $serverModel = PBXManager_Server_Model::getInstance();
        $response = new Ottocrat_Response();
        $ottocratsecretkey = $serverModel->get('ottocratsecretkey');
        if($ottocratsecretkey) {
            $connector = $serverModel->getConnector();
            $ottocratsecretkey = $connector->getOttocratSecretKey();
            $response->setResult($ottocratsecretkey);
        }else {
            $ottocratsecretkey = PBXManager_Server_Model::generateOttocratSecretKey();
            $response->setResult($ottocratsecretkey);
        }
        $response->emit();
    }
}
