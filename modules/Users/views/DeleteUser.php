<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Users_DeleteUser_View extends Ottocrat_Index_View {
	
	public function checkPermission(Ottocrat_Request $request){
		parent::checkPermission($request);
	}

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$userid = $request->get('record');
		
		$userRecordModel = Users_Record_Model::getInstanceById($userid, $moduleName);
		$viewer = $this->getViewer($request);
		$usersList = $userRecordModel->getAll(true);
		
		if(array_key_exists($userid, $usersList)){
			unset($usersList[$userid]);
		}
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USERID', $userid);
		$viewer->assign('DELETE_USER_NAME', $userRecordModel->getName());
		$viewer->assign('USER_LIST', $usersList);
        if($request->get('mode') == 'permanent')
        	$viewer->assign('PERMANENT', true);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		
		$viewer->view('DeleteUser.tpl', $moduleName);
	}
}
