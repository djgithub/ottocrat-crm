<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Login_View extends Ottocrat_View_Controller {

	function loginRequired() {
		return false;
	}
	
	function checkPermission(Ottocrat_Request $request) {
		return true;
	}
	
	function process (Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_VERSION', vglobal('ottocrat_current_version'));
		$viewer->view('Login.tpl', 'Users');
}
}
