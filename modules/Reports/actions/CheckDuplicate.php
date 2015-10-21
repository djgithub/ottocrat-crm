<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_CheckDuplicate_Action extends Ottocrat_Action_Controller {

	function checkPermission(Ottocrat_Request $request) {
		return;
	}

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$reportName = $request->get('reportname');
		$record = $request->get('record');
		
		if ($record) {
			$recordModel = Ottocrat_Record_Model::getInstanceById($record, $moduleName);
		} else {
			$recordModel = Ottocrat_Record_Model::getCleanInstance($moduleName);
		}

		$recordModel->set('reportname', $reportName);
		$recordModel->set('reportid', $record);
		$recordModel->set('isDuplicate', $request->get('isDuplicate'));
		
		if (!$recordModel->checkDuplicate()) {
			$result = array('success'=>false);
		} else {
			$result = array('success'=>true, 'message'=>vtranslate('LBL_DUPLICATES_EXIST', $moduleName));
		}
		$response = new Ottocrat_Response();
		$response->setResult($result);
		$response->emit();
	}
}
