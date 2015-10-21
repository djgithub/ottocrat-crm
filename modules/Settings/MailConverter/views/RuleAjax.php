<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MailConverter_RuleAjax_View extends Settings_Ottocrat_IndexAjax_View {
	
	public function process(Ottocrat_Request $request) {
		$scannerId = $request->get('scannerId');
		$ruleId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);

		$viewer->assign('SCANNER_ID', $scannerId);
		$viewer->assign('SCANNER_MODEL', Settings_MailConverter_Record_Model::getInstanceById($scannerId));
		$viewer->assign('RULE_MODEL', Settings_MailConverter_RuleRecord_Model::getRule($scannerId,$ruleId));

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

		$viewer->view('Rule.tpl', $qualifiedModuleName);
	}
}
?>
