<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class RecycleBin_RecycleBinAjax_Action extends Ottocrat_Mass_Action {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('restoreRecords');
		$this->exposeMethod('emptyRecycleBin');
		$this->exposeMethod('deleteRecords');
	}
	
	function checkPermission(Ottocrat_Request $request) {
        if($request->get('mode') == 'emptyRecycleBin') {
            //we dont check for permissions since recylebin axis will not be there for non admin users
            return true;
        }
		$targetModuleName = $request->get('sourceModule', $request->get('module'));
		$moduleModel = Ottocrat_Module_Model::getInstance($targetModuleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
			throw new AppException(getTranslatedString('LBL_PERMISSION_DENIED'));
		}
	}

	function preProcess(Ottocrat_Request $request) {
		return true;
	}

	function postProcess(Ottocrat_Request $request) {
		return true;
	}

	public function process(Ottocrat_Request $request) {
		$mode = $request->get('mode');
		
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	/**
	 * Function to restore the deleted records.
	 * @param type $sourceModule
	 * @param type $recordIds
	 */
	public function restoreRecords(Ottocrat_Request $request){
		$sourceModule = $request->get('sourceModule');
		$recordIds = $this->getRecordsListFromRequest($request);
		$recycleBinModule = new RecycleBin_Module_Model();
 
		$response = new Ottocrat_Response();	
		if ($recordIds) {
			$recycleBinModule->restore($sourceModule, $recordIds);
			$response->setResult(array(true));
		} 
		
		$response->emit();

	}
	
	/**
	 * Function to delete the records permanently in vitger CRM database
	 */
	public function emptyRecycleBin(Ottocrat_Request $request){
		$recycleBinModule = new RecycleBin_Module_Model();
		
		$status = $recycleBinModule->emptyRecycleBin();
		
		if($status){
			$response = new Ottocrat_Response();
			$response->setResult(array($status));
			$response->emit();
		}
	}
	
	/**
	 * Function to deleted the records permanently in CRM
	 * @param type $reocrdIds
	 */
	public function deleteRecords(Ottocrat_Request $request){
		$recordIds = $this->getRecordsListFromRequest($request);
		$recycleBinModule = new RecycleBin_Module_Model();
 
		$response = new Ottocrat_Response();	
		if ($recordIds) {
			$recycleBinModule->deleteRecords($recordIds);
			$response->setResult(array(true));
			$response->emit();
		} 
	}
	
}
