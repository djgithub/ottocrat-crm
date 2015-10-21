<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Ottocrat_CustomRecordNumberingAjax_Action extends Settings_Ottocrat_Index_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('getModuleCustomNumberingData');
		$this->exposeMethod('saveModuleCustomNumberingData');
		$this->exposeMethod('updateRecordsWithSequenceNumber');
	}

	public function checkPermission(Ottocrat_Request $request) {
		parent::checkPermission($request);
		$qualifiedModuleName = $request->getModule(false);
		$sourceModule = $request->get('sourceModule');

		if(!$sourceModule) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $qualifiedModuleName));
		}
	}

	public function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to get Module custom numbering data
	 * @param Ottocrat_Request $request
	 */
	public function getModuleCustomNumberingData(Ottocrat_Request $request) {
		$sourceModule = $request->get('sourceModule');

		$moduleModel = Settings_Ottocrat_CustomRecordNumberingModule_Model::getInstance($sourceModule);
		$moduleData = $moduleModel->getModuleCustomNumberingData();
		
		$response = new Ottocrat_Response();
		$response->setEmitType(Ottocrat_Response::$EMIT_JSON);
		$response->setResult($moduleData);
		$response->emit();
	}

	/**
	 * Function save module custom numbering data
	 * @param Ottocrat_Request $request
	 */
	public function saveModuleCustomNumberingData(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$sourceModule = $request->get('sourceModule');

		$moduleModel = Settings_Ottocrat_CustomRecordNumberingModule_Model::getInstance($sourceModule);
		$moduleModel->set('prefix', $request->get('prefix'));
		$moduleModel->set('sequenceNumber', $request->get('sequenceNumber'));

		$result = $moduleModel->setModuleSequence();

		$response = new Ottocrat_Response();
		if ($result['success']) {
			$response->setResult(vtranslate('LBL_SUCCESSFULLY_UPDATED', $qualifiedModuleName));
		} else {
			$message = vtranslate('LBL_PREFIX_IN_USE', $qualifiedModuleName);
			$response->setError($message);
		}
		$response->emit();
	}

	/**
	 * Function to update record with sequence number
	 * @param Ottocrat_Request $request
	 */
	public function updateRecordsWithSequenceNumber(Ottocrat_Request $request) {
		$sourceModule = $request->get('sourceModule');

		$moduleModel = Settings_Ottocrat_CustomRecordNumberingModule_Model::getInstance($sourceModule);
		$result = $moduleModel->updateRecordsWithSequence();

		$response = new Ottocrat_Response();
		$response->setResult($result);
		$response->emit();
	}
        
        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        }
}