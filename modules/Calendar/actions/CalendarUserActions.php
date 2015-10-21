<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_CalendarUserActions_Action extends Ottocrat_Action_Controller{
	
	function __construct() {
		$this->exposeMethod('deleteUserCalendar');
		$this->exposeMethod('addUserCalendar');
		$this->exposeMethod('deleteCalendarView');
		$this->exposeMethod('addCalendarView');
	}
	
	public function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if(!Users_Privileges_Model::isPermitted($moduleName, 'Save', $record)) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
	
	public function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	/**
	 * Function to delete the user calendar from shared calendar
	 * @param Ottocrat_Request $request
	 * @return Ottocrat_Response $response
	 */
	function deleteUserCalendar(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$sharedUserId = $request->get('userid');
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT 1 FROM ottocrat_shareduserinfo WHERE userid=? AND shareduserid=?', array($userId, $sharedUserId));
		if($db->num_rows($result) > 0) {
			$db->pquery('UPDATE ottocrat_shareduserinfo SET visible=? WHERE userid=? AND shareduserid=?', array('0', $userId, $sharedUserId));
		} else {
			$db->pquery('INSERT INTO ottocrat_shareduserinfo (userid, shareduserid, visible) VALUES(?, ?, ?)', array($userId, $sharedUserId, '0'));
		}
		
		$result = array('userid' => $userId, 'sharedid' => $sharedUserId, 'username' => getUserFullName($sharedUserId));
		$response = new Ottocrat_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	/**
	 * Function to add other user calendar to shared calendar
	 * @param Ottocrat_Request $request
	 * @return Ottocrat_Response $response
	 */
	function addUserCalendar(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$sharedUserId = $request->get('selectedUser');
		$color = $request->get('selectedColor');
		
		$db = PearDatabase::getInstance();
		
		$queryResult = $db->pquery('SELECT 1 FROM ottocrat_shareduserinfo WHERE userid=? AND shareduserid=?', array($userId, $sharedUserId));
		
		if($db->num_rows($queryResult) > 0) {
			$db->pquery('UPDATE ottocrat_shareduserinfo SET color=?, visible=? WHERE userid=? AND shareduserid=?', array($color, '1', $userId, $sharedUserId));
		} else {
			$db->pquery('INSERT INTO ottocrat_shareduserinfo (userid, shareduserid, color, visible) VALUES(?, ?, ?, ?)', array($userId, $sharedUserId, $color, '1'));
		}
		
		$response = new Ottocrat_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}
	
	/**
	 * Function to delete the calendar view from My Calendar
	 * @param Ottocrat_Request $request
	 * @return Ottocrat_Response $response
	 */
	function deleteCalendarView(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$viewmodule = $request->get('viewmodule');
		$viewfieldname = $request->get('viewfieldname');
		
		
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE ottocrat_calendar_user_activitytypes 
			INNER JOIN ottocrat_calendar_default_activitytypes ON ottocrat_calendar_default_activitytypes.id = ottocrat_calendar_user_activitytypes.defaultid
			SET ottocrat_calendar_user_activitytypes.visible=? WHERE ottocrat_calendar_user_activitytypes.userid=? AND ottocrat_calendar_default_activitytypes.module=? AND ottocrat_calendar_default_activitytypes.fieldname=?', 
				array('0', $userId, $viewmodule, $viewfieldname));
		
		$result = array('viewmodule' => $viewmodule, 'viewfieldname' => $viewfieldname, 'viewfieldlabel' => $request->get('viewfieldlabel'));
		$response = new Ottocrat_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	/**
	 * Function to add calendar views to My calendar
	 * @param Ottocrat_Request $request
	 * @return Ottocrat_Response $response
	 */
	function addCalendarView(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$viewmodule = $request->get('viewmodule');
		$viewfieldname = $request->get('viewfieldname');
		$viewcolor = $request->get('viewColor');
		
		$db = PearDatabase::getInstance();
		
		$db->pquery('UPDATE ottocrat_calendar_user_activitytypes 
					INNER JOIN ottocrat_calendar_default_activitytypes ON ottocrat_calendar_default_activitytypes.id = ottocrat_calendar_user_activitytypes.defaultid
					SET ottocrat_calendar_user_activitytypes.color=?, ottocrat_calendar_user_activitytypes.visible=? 
					WHERE ottocrat_calendar_user_activitytypes.userid=? AND ottocrat_calendar_default_activitytypes.module=? AND ottocrat_calendar_default_activitytypes.fieldname=?',
						array($viewcolor, '1', $userId, $viewmodule, $viewfieldname));
		
		$response = new Ottocrat_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}
	

}