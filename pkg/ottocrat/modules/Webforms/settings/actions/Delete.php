<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Webforms_Delete_Action extends Settings_Ottocrat_Index_Action {

	public function checkPermission(Ottocrat_Request $request) {
		parent::checkPermission($request);

		$recordId = $request->get('record');
		$moduleModel = Ottocrat_Module_Model::getInstance($request->getModule());

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$recordId || !$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Ottocrat_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		$moduleModel = $recordModel->getModule();

		$recordModel->delete();

		$returnUrl = $moduleModel->getListViewUrl();
		$response = new Ottocrat_Response();
		$response->setResult($returnUrl);
		return $response;
	}

        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}