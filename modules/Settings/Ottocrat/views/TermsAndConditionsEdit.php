<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_TermsAndConditionsEdit_View extends Settings_Ottocrat_Index_View {
    
    public function process(Ottocrat_Request $request) {
        $model = Settings_Ottocrat_TermsAndConditions_Model::getInstance();
        $conditionText = $model->getText();
        
        $viewer = $this->getViewer($request);
        $qualifiedName = $request->getModule(false);
        
        $viewer->assign('CONDITION_TEXT',$conditionText);
        $viewer->assign('MODEL',$model);
        $viewer->view('TermsAndConditions.tpl',$qualifiedName);
    }
	
	function getPageTitle(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_TERMS_AND_CONDITIONS',$qualifiedModuleName);
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
			"modules.Settings.$moduleName.resources.TermsAndConditions"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
    