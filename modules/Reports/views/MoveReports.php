<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_MoveReports_View extends Ottocrat_Index_View {

	public function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$folderList = $moduleModel->getFolders();
		$viewer = $this->getViewer($request);
		$viewer->assign('FOLDERS', $folderList);
		$viewer->assign('SELECTED_IDS', $request->get('selected_ids'));
		$viewer->assign('EXCLUDED_IDS', $request->get('excluded_ids'));
		$viewer->assign('VIEWNAME',$request->get('viewname'));
		$viewer->assign('MODULE',$moduleName);
		$viewer->view('MoveReports.tpl', $moduleName);
	}
}