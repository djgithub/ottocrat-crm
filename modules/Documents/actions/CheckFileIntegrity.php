<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_CheckFileIntegrity_Action extends Ottocrat_Action_Controller {

	public function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();

		if(!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $request->get('record'))) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$documentRecordModel = Ottocrat_Record_Model::getInstanceById($recordId, $moduleName);
		$resultVal = $documentRecordModel->checkFileIntegrity();

		$result = array('success'=>$resultVal);
		if ($resultVal) {
                        $documentRecordModel->updateFileStatus(true);
			$result['message'] = vtranslate('LBL_FILE_AVAILABLE', $moduleName);
		} else {
                        $documentRecordModel->updateFileStatus(false);
			$result['message'] = vtranslate('LBL_FILE_NOT_AVAILABLE', $moduleName);
		}

		$response = new Ottocrat_Response();
		$response->setResult($result);
		$response->emit();
	}
}
