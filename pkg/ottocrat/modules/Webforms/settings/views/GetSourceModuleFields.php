<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Webforms_GetSourceModuleFields_View extends Settings_Ottocrat_IndexAjax_View {

	public function checkPermission(Ottocrat_Request $request) {
		parent::checkPermission($request);

		$moduleModel = Ottocrat_Module_Model::getInstance($request->getModule());
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Ottocrat_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$sourceModule = $request->get('sourceModule');
		$viewer = $this->getViewer($request);
		$mode = '';
		$selectedFieldsList = array();

		if ($recordId) {
			$recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
			$mode = 'edit';
			if ($sourceModule === $recordModel->get('targetmodule')) {
				$selectedFieldsList = $recordModel->getSelectedFieldsList();
			}
		} else {
			$recordModel = Settings_Webforms_Record_Model::getCleanInstance($qualifiedModuleName);
		}

		$viewer->assign('MODE', $mode);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('SELECTED_FIELD_MODELS_LIST', $selectedFieldsList);
		$viewer->assign('ALL_FIELD_MODELS_LIST', $recordModel->getAllFieldsList($sourceModule));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->view('FieldsEditView.tpl', $qualifiedModuleName);
	}
}