<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Logout_Action extends Ottocrat_Action_Controller {

	function checkPermission(Ottocrat_Request $request) {
		return true;
	}

	function process(Ottocrat_Request $request) {
		session_regenerate_id(true); // to overcome session id reuse.
		Ottocrat_Session::destroy();
		
		//Track the logout History
		$moduleName = $request->getModule();
		$moduleModel = Users_Module_Model::getInstance($moduleName);
		$moduleModel->saveLogoutHistory();
		//End
		
		header ('Location: index.php');
	}
}
