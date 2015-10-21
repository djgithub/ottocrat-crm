<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Users_SystemSetupSave_Action extends Users_Save_Action {
    
        function checkPermission(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if(!$currentUser->isAdminUser() && !$currentUser->isAccountOwner()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Ottocrat'));
		}
	}
	
	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$packages = $request->get(packages);
		$userModuleModel = Users_Module_Model::getInstance($moduleName);
		$userModuleModel::savePackagesInfo($packages);
		header ('Location: '.Ottocrat_Request:: encryptLink('index.php?module=Users&parent=Settings&view=UserSetup'));
		exit();
	}
}