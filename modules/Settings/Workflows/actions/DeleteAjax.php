<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Workflows_DeleteAjax_Action extends Settings_Ottocrat_Index_Action {

	public function process(Ottocrat_Request $request) {
		$qualifiedModule = $request->getModule(false);
		$recordId = $request->get('record');

		$response = new Ottocrat_Response();
		$recordModel = Settings_Workflows_Record_Model::getInstance($recordId);
		if($recordModel->isDefault()) {
			$response->setError('LBL_DEFAULT_WORKFLOW', vtranslate('LBL_CANNOT_DELETE_DEFAULT_WORKFLOW', $qualifiedModule));
		} else {
			$recordModel->delete();
			$response->setResult(array('success'=>'ok'));
		}
		$response->emit();
	}
        
        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
