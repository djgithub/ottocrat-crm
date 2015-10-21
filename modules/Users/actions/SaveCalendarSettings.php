<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Users_SaveCalendarSettings_Action extends Users_Save_Action {


	public function process(Ottocrat_Request $request) {
		$recordModel = $this->getRecordModelFromRequest($request);
		
		$recordModel->save();
		$this->saveCalendarSharing($request);
		header("Location: ".Ottocrat_Request:: encryptLink("index.php?module=Calendar&view=Calendar"));
	}

	/**
	 * Function to update Calendar Sharing information
	 * @params - Ottocrat_Request $request
	 */
	public function saveCalendarSharing(Ottocrat_Request $request){
		
		$sharedIds = $request->get('sharedIds');
		$sharedType = $request->get('sharedtype');

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$calendarModuleModel = Ottocrat_Module_Model::getInstance('Calendar');
		$accessibleUsers = $currentUserModel->getAccessibleUsersForModule('Calendar');

		if($sharedType == 'private'){
			$calendarModuleModel->deleteSharedUsers($currentUserModel->id);
		}else if($sharedType == 'public'){
            $allUsers = $currentUserModel->getAll(true);
			$accessibleUsers = array();
			foreach ($allUsers as $id => $userModel) {
				$accessibleUsers[$id] = $id;
			}
			$calendarModuleModel->deleteSharedUsers($currentUserModel->id);
			$calendarModuleModel->insertSharedUsers($currentUserModel->id, array_keys($accessibleUsers));
		}else{
			if(!empty($sharedIds)){
				$calendarModuleModel->deleteSharedUsers($currentUserModel->id);
				$calendarModuleModel->insertSharedUsers($currentUserModel->id, $sharedIds);
			}else{
				$calendarModuleModel->deleteSharedUsers($currentUserModel->id);
			}
		}
	}
}
