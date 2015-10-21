<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PBXManager_SaveAjax_Action extends Ottocrat_SaveAjax_Action {

    // To save Mapping of user from mapping popup
    public function process(Ottocrat_Request $request) {
        $id = $request->get('id');
        $qualifiedModuleName = 'PBXManager';
        
        $recordModel = Settings_PBXManager_Record_Model::getCleanInstance();
        $recordModel->set('gateway',$qualifiedModuleName);
        if($id) {
            $recordModel->set('id',$id);
        }
        
        $connector = new PBXManager_PBXManager_Connector;
        foreach ($connector->getSettingsParameters() as $field => $type) {
                $recordModel->set($field, $request->get($field));
        }
        
        $response = new Ottocrat_Response();
        try {
                $recordModel->save();
                $response->setResult(true);
        } catch (Exception $e) {
                $response->setError($e->getMessage());
        }
        $response->emit();
    }
}
