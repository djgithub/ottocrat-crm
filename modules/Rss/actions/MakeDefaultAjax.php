<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Rss_MakeDefaultAjax_Action extends Ottocrat_Action_Controller {
    
    function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPrivilegesModel->isPermitted($moduleName, 'ListView', $record)) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordModel = Rss_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->makeDefault();

		$response = new Ottocrat_Response();
		$response->setResult(array('message'=>'JS_RSS_MADE_AS_DEFAULT', 'record'=>$recordId, 'module'=>$moduleName));
		$response->emit();
	}
}
