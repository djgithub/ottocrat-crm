<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
class Settings_ModuleManager_Basic_Action extends Settings_Ottocrat_IndexAjax_View {
    function __construct() {
		parent::__construct();
		$this->exposeMethod('updateModuleStatus');
                $this->exposeMethod('importUserModuleStep3');
                $this->exposeMethod('updateUserModuleStep3');
	}
    
    function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    public function updateModuleStatus(Ottocrat_Request $request) {
        $moduleName = $request->get('forModule');
        $updateStatus = $request->get('updateStatus');
        
        $moduleManagerModel = new Settings_ModuleManager_Module_Model();
        
        if($updateStatus == 'true') {
            $moduleManagerModel->enableModule($moduleName);
        }else{
            $moduleManagerModel->disableModule($moduleName);
        }
        
        $response = new Ottocrat_Response();
		$response->emit();
    }
    
    public function importUserModuleStep3(Ottocrat_Request $request) {
        $importModuleName = $request->get('module_import_name');
        $uploadFile = $request->get('module_import_file');
        $uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
        $uploadFileName = "$uploadDir/$uploadFile";
        checkFileAccess($uploadFileName);

        $importType = $request->get('module_import_type');
        if(strtolower($importType) == 'language') {
                $package = new Ottocrat_Language();
        } else {
                $package = new Ottocrat_Package();
        }

        $package->import($uploadFileName);
        checkFileAccessForDeletion($uploadFileName);
        unlink($uploadFileName);
        
        $result = array('success'=>true, 'importModuleName'=> $importModuleName);
        $response = new Ottocrat_Response();
        $response->setResult($result);
        $response->emit();
    }
    
    public function updateUserModuleStep3(Ottocrat_Request $request){
        $importModuleName = $request->get('module_import_name');
        $uploadFile = $request->get('module_import_file');
        $uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
        $uploadFileName = "$uploadDir/$uploadFile";
        checkFileAccess($uploadFileName);

        $importType = $request->get('module_import_type');
        if(strtolower($importType) == 'language') {
                $package = new Ottocrat_Language();
        } else {
                $package = new Ottocrat_Package();
        }

        if (strtolower($importType) == 'language') {
                $package->import($uploadFileName);
        } else {
                $package->update(Ottocrat_Module::getInstance($importModuleName), $uploadFileName);
        }

        checkFileAccessForDeletion($uploadFileName);
        unlink($uploadFileName);
        
        $result = array('success'=>true, 'importModuleName'=> $importModuleName);
        $response = new Ottocrat_Response();
        $response->setResult($result);
        $response->emit();
    }

	 public function validateRequest(Ottocrat_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}
