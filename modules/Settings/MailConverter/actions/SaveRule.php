<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_SaveRule_Action extends Settings_Ottocrat_Index_Action {

	public function checkPermission(Ottocrat_Request $request) {
		parent::checkPermission($request);
		$recordId = $request->get('record');
		$scannerId = $request->get('scannerId');

		if (!$scannerId) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
		}
	}

	public function process(Ottocrat_Request $request) {
		$recordId = $request->get('record');
		$scannerId = $request->get('scannerId');
		$action = $request->get('action1');
		$request->set('action', $action);
		$qualifiedModuleName = $request->getModule(false);

		if ($recordId) {
			$recordModel = Settings_MailConverter_RuleRecord_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_MailConverter_RuleRecord_Model::getCleanInstance($scannerId);
		}

		$recordModel->assignedTo = $request->get('assignedTo');
		$recordModel->cc = $request->get('cc');
		$recordModel->bcc = $request->get('bcc');
		$fieldsList = $recordModel->getFields();
		foreach ($fieldsList as $fieldName) {
			$recordModel->set($fieldName, $request->get($fieldName));
		}
		$recordModel->set('newAction', $request->get('action'));

		$ruleId = $recordModel->save();

		$response = new Ottocrat_Response();
		$response->setResult(array('message' => vtranslate('LBL_SAVED_SUCCESSFULLY', $qualifiedModuleName), 'id' => $ruleId, 'scannerId' => $scannerId));
		$response->emit();
	}
        
        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        }
}