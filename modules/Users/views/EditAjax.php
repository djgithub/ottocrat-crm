<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

Class Users_EditAjax_View extends Ottocrat_IndexAjax_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('changePassword');
	}

	public function process(Ottocrat_Request $request) {
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function changePassword(Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->get('module');
		$userId = $request->get('recordId');

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USERID', $userId);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('ChangePassword.tpl', $moduleName);
	}

}