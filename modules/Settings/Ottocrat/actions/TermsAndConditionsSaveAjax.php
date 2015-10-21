<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Ottocrat_TermsAndConditionsSaveAjax_Action extends Settings_Ottocrat_Basic_Action {
    
    public function process(Ottocrat_Request $request) {
        $model = Settings_Ottocrat_TermsAndConditions_Model::getInstance();
        $model->setText($request->get('tandc'));
        $model->save();
        
        $response = new Ottocrat_Response();
        $response->emit();
    }
    
    public function validateRequest(Ottocrat_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}