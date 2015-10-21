<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Users_SystemSetup_View extends Ottocrat_Index_View {
	
	public function preProcess(Ottocrat_Request $request) {

		return true;
	}
	
	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$userModel = Users_Record_Model::getCurrentUserModel();
		$isFirstUser = Users_CRMSetup::isFirstUser($userModel);

		if($isFirstUser) {
			$viewer->assign('IS_FIRST_USER', $isFirstUser);
			$viewer->assign('PACKAGES_LIST', Users_CRMSetup::getPackagesList());
			$viewer->view('SystemSetup.tpl', $moduleName);
		} else {
			header ('Location: '.Ottocrat_Request:: encryptLink('index.php?module=Users&parent=Settings&view=UserSetup'));
			exit();
		}
	}
	
	function postProcess(Ottocrat_Request $request) {
		return true;
	}
	
}