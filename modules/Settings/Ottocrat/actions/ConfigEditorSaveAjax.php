<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_ConfigEditorSaveAjax_Action extends Settings_Ottocrat_Basic_Action {

	public function process(Ottocrat_Request $request) {
		$response = new Ottocrat_Response();
		$qualifiedModuleName = $request->getModule(false);
		$updatedFields = $request->get('updatedFields');
		$moduleModel = Settings_Ottocrat_ConfigModule_Model::getInstance();

		if ($updatedFields) {
			$moduleModel->set('updatedFields', $updatedFields);
			$status = $moduleModel->save();

			if ($status === true) {
				$response->setResult(array($status));
			} else {
				$response->setError(vtranslate($status, $qualifiedModuleName));
			}
		} else {
			$response->setError(vtranslate('LBL_FIELDS_INFO_IS_EMPTY', $qualifiedModuleName));
		}
		$response->emit();
	}
        
        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        }
}