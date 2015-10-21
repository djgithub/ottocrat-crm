<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Portal_MassDelete_Action extends Ottocrat_MassDelete_Action {

    function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

    public function process(Ottocrat_Request $request) {
        $module = $request->getModule();
        
        Portal_Module_Model::deleteRecords($request);
        
        $response = new Ottocrat_Response();
        $result = array('message' => vtranslate('LBL_BOOKMARKS_DELETED_SUCCESSFULLY', $module));
        $response->setResult($result);
        $response->emit();
    }
}