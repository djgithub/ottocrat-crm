<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class SMSNotifier_CheckStatus_View extends Ottocrat_IndexAjax_View {

	function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();

		if(!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $request->get('record'))) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function process(Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$notifierRecordModel = Ottocrat_Record_Model::getInstanceById($request->get('record'), $moduleName);
		$notifierRecordModel->checkStatus();

		$viewer->assign('RECORD', $notifierRecordModel);
		$viewer->view('StatusWidget.tpl', $moduleName);
	}
}