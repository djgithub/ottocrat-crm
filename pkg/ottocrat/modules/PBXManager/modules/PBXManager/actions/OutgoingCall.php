<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class PBXManager_OutgoingCall_Action extends Ottocrat_Action_Controller{
    
    public function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if(!$permission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
    
    public function process(Ottocrat_Request $request) {
        $serverModel = PBXManager_Server_Model::getInstance();
        $gateway = $serverModel->get("gateway");
        $response = new Ottocrat_Response();
        $user = Users_Record_Model::getCurrentUserModel();
	$userNumber = $user->phone_crm_extension;
        
        if($gateway && $userNumber){
            try{
                $number = $request->get('number');
                $recordId = $request->get('record');
                $connector = $serverModel->getConnector();
                $result = $connector->call($number, $recordId);
                $response->setResult($result);
            }catch(Exception $e){
                throw new Exception($e);
            }
        }else{
            $response->setResult(false);
        }
        $response->emit();
    }
    
}