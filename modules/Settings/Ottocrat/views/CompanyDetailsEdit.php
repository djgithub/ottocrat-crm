<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_CompanyDetailsEdit_View extends Settings_Ottocrat_Index_View {

	public function process(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Ottocrat_CompanyDetails_Model::getInstance();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('ERROR_MESSAGE', $request->get('error'));

		$viewer->view('CompanyDetailsEdit.tpl', $qualifiedModuleName);//For Open Source
	}
		
	function getPageTitle(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_CONFIG_EDITOR',$qualifiedModuleName);
	}
	
}