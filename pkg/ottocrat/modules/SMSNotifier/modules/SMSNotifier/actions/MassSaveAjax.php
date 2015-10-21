<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class SMSNotifier_MassSaveAjax_Action extends Ottocrat_Mass_Action {

	function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	/**
	 * Function that saves SMS records
	 * @param Ottocrat_Request $request
	 */
	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordIds = $this->getRecordsListFromRequest($request);
		$phoneFieldList = $request->get('fields');
		$message = $request->get('message');

		foreach($recordIds as $recordId) {
			$recordModel = Ottocrat_Record_Model::getInstanceById($recordId);
			$numberSelected = false;
			foreach($phoneFieldList as $fieldname) {
				$fieldValue = $recordModel->get($fieldname);
				if(!empty($fieldValue)) {
					$toNumbers[] = $fieldValue;
					$numberSelected = true;
				}
			}
			if($numberSelected) {
				$recordIds[] = $recordId;
			}
		}

		$response = new Ottocrat_Response();
        
		if(!empty($toNumbers)) {
			SMSNotifier_Record_Model::SendSMS($message, $toNumbers, $currentUserModel->getId(), $recordIds, $moduleName);
			$response->setResult(true);
		} else {
			$response->setResult(false);
		}
		return $response;
	}
}
