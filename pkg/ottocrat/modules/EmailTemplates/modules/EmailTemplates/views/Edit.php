<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

Class EmailTemplates_Edit_View extends Ottocrat_Edit_View {
	
	/**
	 * Function to check module Edit Permission
	 * @param Ottocrat_Request $request
	 * @return boolean
	 */
	public function checkPermission(Ottocrat_Request $request) {
		return true;
	}
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Ottocrat_Request $request
	 * @return <Array> - List of Ottocrat_JsScript_Model instances
	 */
	function getHeaderScripts(Ottocrat_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$jsFileNames = array(
				"libraries.jquery.ckeditor.ckeditor",
				"libraries.jquery.ckeditor.adapters.jquery",
				'modules.Ottocrat.resources.CkEditor',
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	/**
	 * Funtioin to process the Edit view
	 * @param Ottocrat_Request $request
	 */
	public function process(Ottocrat_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');
		
		if(!empty($record)) {
			$recordModel = EmailTemplates_Record_Model::getInstanceById($record);
            $viewer->assign('RECORD_ID', $record);
            $viewer->assign('MODE', 'edit');
        } else {
			$recordModel = new EmailTemplates_Record_Model();
            $viewer->assign('MODE', '');
			$recordModel->set('templatename','');
			$recordModel->set('description','');
			$recordModel->set('subject','');
			$recordModel->set('body','');
        }
		$recordModel->setModule('EmailTemplates');
        if(!$this->record){
            $this->record = $recordModel;
        }
		$allFiledsOptions = $this->record->getEmailTemplateFields();

		$viewer->assign('RECORD', $this->record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('ALL_FIELDS', $allFiledsOptions);
		$viewer->view('EditView.tpl', $moduleName);
	}
	
}
