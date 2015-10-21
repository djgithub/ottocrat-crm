<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_CronTasks_SaveAjax_Action extends Settings_Ottocrat_Index_Action {

	public function checkPermission(Ottocrat_Request $request) {
		parent::checkPermission($request);

		$recordId = $request->get('record');
		if(!$recordId) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Ottocrat_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_CronTasks_Record_Model::getInstanceById($recordId, $qualifiedModuleName);

		$fieldsList = $recordModel->getModule()->getEditableFieldsList();
		foreach ($fieldsList as $fieldName) {
			$fieldValue = $request->get($fieldName);
			if (isset ($fieldValue)) {
				$recordModel->set($fieldName, $fieldValue);
			}
		}

		$recordModel->save();

		$response = new Ottocrat_Response();
		$response->setResult(array(true));
		$response->emit();
	}
        
        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        }
}