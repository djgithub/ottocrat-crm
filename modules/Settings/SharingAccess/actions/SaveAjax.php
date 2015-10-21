<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_SharingAccess_SaveAjax_Action extends Ottocrat_SaveAjax_Action {

         public function checkPermission(Ottocrat_Request $request) { 
            $currentUser = Users_Record_Model::getCurrentUserModel(); 
            if(!$currentUser->isAdminUser()) { 
                    throw new AppException('LBL_PERMISSION_DENIED'); 
            } 
        } 
	public function process(Ottocrat_Request $request) {
		$modulePermissions = $request->get('permissions');
		$modulePermissions[4] = $modulePermissions[6];

		foreach($modulePermissions as $tabId => $permission) {
			$moduleModel = Settings_SharingAccess_Module_Model::getInstance($tabId);
			$moduleModel->set('permission', $permission);

			try {
				$moduleModel->save();
			} catch (AppException $e) {
				
			}
		}
		Settings_SharingAccess_Module_Model::recalculateSharingRules();

		$response = new Ottocrat_Response();
		$response->setEmitType(Ottocrat_Response::$EMIT_JSON);
		$response->emit();
	}
}