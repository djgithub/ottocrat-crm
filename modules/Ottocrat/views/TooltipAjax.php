<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Ottocrat_TooltipAjax_View extends Ottocrat_PopupAjax_View {

	function preProcess(Ottocrat_Request $request) {
		return true;
	}

	function postProcess(Ottocrat_Request $request) {
		return true;
	}

	function process (Ottocrat_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();

		$this->initializeListViewContents($request, $viewer);

		echo $viewer->view('TooltipContents.tpl', $moduleName, true);
	}
	
	public function initializeListViewContents(Ottocrat_Request $request, Ottocrat_Viewer $viewer) {
		$moduleName = $this->getModule($request);
		
		$recordId = $request->get('record');
		$tooltipViewModel = Ottocrat_TooltipView_Model::getInstance($moduleName, $recordId);

		$viewer->assign('MODULE', $moduleName);

		$viewer->assign('MODULE_MODEL', $tooltipViewModel->getRecord()->getModule());
		
		$viewer->assign('TOOLTIP_FIELDS', $tooltipViewModel->getFields());
		$viewer->assign('RECORD', $tooltipViewModel->getRecord());
		$viewer->assign('RECORD_STRUCTURE', $tooltipViewModel->getStructure());

		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
	}

}