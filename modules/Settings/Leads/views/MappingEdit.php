<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Leads_MappingEdit_View extends Settings_Ottocrat_Index_View {

	public function process(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE_MODEL', Settings_Leads_Mapping_Model::getInstance(TRUE));
		$viewer->assign('LEADS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Leads'));
		$viewer->assign('ACCOUNTS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Accounts'));
		$viewer->assign('CONTACTS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Contacts'));
		$viewer->assign('POTENTIALS_MODULE_MODEL', Settings_Leads_Module_Model::getInstance('Potentials'));

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('LeadMappingEdit.tpl', $qualifiedModuleName);
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
			"modules.Settings.$moduleName.resources.LeadMapping"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}