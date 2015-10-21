<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_OutgoingServerSaveAjax_Action extends Settings_Ottocrat_Basic_Action {
    
    public function process(Ottocrat_Request $request) {
        $outgoingServerSettingsModel = Settings_Ottocrat_Systems_Model::getInstanceFromServerType('email', 'OutgoingServer');
        $loadDefaultSettings = $request->get('default');
        if($loadDefaultSettings == "true") {
            $outgoingServerSettingsModel->loadDefaultValues();
        }else{
            $outgoingServerSettingsModel->setData($request->getAll());
        }
        $response = new Ottocrat_Response();
        try{
            $id = $outgoingServerSettingsModel->save($request);
            $data = $outgoingServerSettingsModel->getData();
            $response->setResult($data);
        }catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Ottocrat_Request $request) { 
        $request->validateWriteAccess(); 
    }
}