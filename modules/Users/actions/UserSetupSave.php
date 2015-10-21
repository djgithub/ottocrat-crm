<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Users_UserSetupSave_Action extends Users_Save_Action {
	
	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$userModuleModel = Users_Module_Model::getInstance($moduleName);
		$userRecordModel = Users_Record_Model::getCurrentUserModel();
		
		//Handling the user preferences 
                $userRecordModel->set('mode','edit'); 
                $userRecordModel->set('language', $request->get('lang_name')); 
                $userRecordModel->set('time_zone', $request->get('time_zone')); 
                $userRecordModel->set('date_format', $request->get('date_format')); 
                $userRecordModel->save(); 
                //End 
		
		//Handling the System Setup
		$currencyName = $request->get('currency_name');
		if(!empty($currencyName)) $userModuleModel->updateBaseCurrency($currencyName);
		$userModuleModel->insertEntryIntoCRMSetup($userRecordModel->getId());
		//End
		
		header("Location: index.php");
		//End
	}
}