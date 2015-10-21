<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Roles_MoveAjax_Action extends Settings_Ottocrat_Basic_Action {

	public function preProcess(Ottocrat_Request $request) {
		return;
	}

	public function postProcess(Ottocrat_Request $request) {
		return;
	}

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$parentRoleId = $request->get('parent_roleid');

		$parentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
		$recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);

		$response = new Ottocrat_Response();
		$response->setEmitType(Ottocrat_Response::$EMIT_JSON);
		try {
			$recordModel->moveTo($parentRole);
		} catch (AppException $e) {
			$response->setError('Move Role Failed');
		}
		$response->emit();
	}
}
