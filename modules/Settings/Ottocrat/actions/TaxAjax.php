<?php

/* +**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Ottocrat_TaxAjax_Action extends Settings_Ottocrat_Basic_Action {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('checkDuplicateName');
	}

	public function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}

		$taxId = $request->get('taxid');
		$type = $request->get('type');
		if (empty($taxId)) {
			$taxRecordModel = new Settings_Ottocrat_TaxRecord_Model();
		} else {
			$taxRecordModel = Settings_Ottocrat_TaxRecord_Model::getInstanceById($taxId, $type);
		}
		
		$fields = array('taxlabel','percentage','deleted');
		foreach($fields as $fieldName) {
			if($request->has($fieldName)) {
				$taxRecordModel->set($fieldName,$request->get($fieldName));
			}
		}
		
		$taxRecordModel->setType($type);

		$response = new Ottocrat_Response();
		try {
			$taxId = $taxRecordModel->save();
			$recordModel = Settings_Ottocrat_TaxRecord_Model::getInstanceById($taxId, $type);
			$response->setResult(array_merge(array('_editurl' => $recordModel->getEditTaxUrl(), 'type' => $recordModel->getType(), 'row_type' => $currentUser->get('rowheight')), $recordModel->getData()));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function checkDuplicateName(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$taxId = $request->get('taxid');
		$taxLabel = $request->get('taxlabel');
		$type = $request->get('type');

		$exists = Settings_Ottocrat_TaxRecord_Model::checkDuplicate($taxLabel, $taxId, $type);

		if (!$exists) {
			$result = array('success' => false);
		} else {
			$result = array('success' => true, 'message' => vtranslate('LBL_TAX_NAME_EXIST', $qualifiedModuleName));
		}
		
		$response = new Ottocrat_Response();
		$response->setResult($result);
		$response->emit();
	}
        
        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}