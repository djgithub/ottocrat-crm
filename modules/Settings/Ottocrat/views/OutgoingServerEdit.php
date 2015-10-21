<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_OutgoingServerEdit_View extends Settings_Ottocrat_Index_View {
    
    public function process(Ottocrat_Request $request) {
        $systemDetailsModel = Settings_Ottocrat_Systems_Model::getInstanceFromServerType('email', 'OutgoingServer');
        $viewer = $this->getViewer($request);
        $qualifiedName = $request->getModule(false);
		
        $viewer->assign('MODEL',$systemDetailsModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->view('OutgoingServerEdit.tpl',$qualifiedName);
    }
		
	function getPageTitle(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_OUTGOING_SERVER',$qualifiedModuleName);
	}
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Ottocrat_Request $request
	 * @return <Array> - List of Ottocrat_JsScript_Model instances
	 */
	function getHeaderScripts(Ottocrat_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.OutgoingServer"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}