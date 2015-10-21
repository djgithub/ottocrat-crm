<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Portal_DeleteAjax_Action extends Ottocrat_DeleteAjax_Action {
    
    public function process(Ottocrat_Request $request) {
        $recordId = $request->get('record');
        $module = $request->getModule();
        Portal_Module_Model::deleteRecord($recordId);
        
        $response = new Ottocrat_Response();
		$response->setResult(array('message'=>  vtranslate('LBL_RECORD_DELETED_SUCCESSFULLY', $module)));
		$response->emit();
    }
}