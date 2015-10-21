<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_PickListDependency_SaveAjax_Action extends Settings_Ottocrat_Index_Action {
    
    public function process(Ottocrat_Request $request) {
        $sourceModule = $request->get('sourceModule');
        $sourceField = $request->get('sourceField');
        $targetField = $request->get('targetField');
        $recordModel = Settings_PickListDependency_Record_Model::getInstance($sourceModule, $sourceField, $targetField);
        
        $response = new Ottocrat_Response();
        try{
            $result = $recordModel->save($request->get('mapping'));
            $response->setResult(array('success'=>$result));
        } catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Ottocrat_Request $request) { 
        $request->validateWriteAccess(); 
    }
}