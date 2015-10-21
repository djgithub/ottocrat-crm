<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_ModuleManager_ModuleExport_Action extends Settings_Ottocrat_IndexAjax_View {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('exportModule');
	}
    
    function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    protected function exportModule(Ottocrat_Request $request) {
        $moduleName = $request->get('forModule');
		
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		
		if (!$moduleModel->isExportable()) {
			echo 'Module not exportable!';
			return;
		}

		$package = new Ottocrat_PackageExport();
		$package->export($moduleModel, '', sprintf("%s-%s.zip", $moduleModel->get('name'), $moduleModel->get('version')), true);
    }
	
}