<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_ActivityReminder_Action extends Ottocrat_Action_Controller{

	function __construct() {
		$this->exposeMethod('getReminders');
		$this->exposeMethod('postpone');
	}

	public function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if(!$permission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

	}

	function getReminders(Ottocrat_Request $request) {
		$recordModels = Calendar_Module_Model::getCalendarReminder();
		foreach($recordModels as $record) {
			$records[] = $record->getDisplayableValues();
			$record->updateReminderStatus();
		}

		$response = new Ottocrat_Response();
		$response->setResult($records);
		$response->emit();
	}

	function postpone(Ottocrat_Request $request) {
		$recordId = $request->get('record');
		$module = $request->getModule();
		$recordModel = Ottocrat_Record_Model::getInstanceById($recordId, $module);
		$recordModel->updateReminderStatus(0);
	}
}