<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Users_BasicAjax_Action extends Ottocrat_BasicAjax_Action {

	function checkPermission(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if(!$currentUser->isAdminUser()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Ottocrat'));
		}
	}

	public function process(Ottocrat_Request $request) {
		$searchValue = $request->get('search_value');
		$searchModule = $request->get('search_module');

		$parentRecordId = $request->get('parent_id');
		$parentModuleName = $request->get('parent_module');

		$searchModuleModel = Users_Module_Model::getInstance($searchModule);
		$records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName);

		$result = array();
		if(is_array($records)){
			foreach($records as $moduleName=>$recordModels) {
				foreach($recordModels as $recordModel) {
					$result[] = array('label'=>decode_html($recordModel->getName()), 'value'=>decode_html($recordModel->getName()), 'id'=>$recordModel->getId());
				}
			}
		}

		$response = new Ottocrat_Response();
		$response->setResult($result);
		$response->emit();
	}
}
