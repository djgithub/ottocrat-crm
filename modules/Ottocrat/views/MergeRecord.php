<?php
/**************************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 **************************************************************************************/

class Ottocrat_MergeRecord_View extends Ottocrat_Popup_View {
	function process(Ottocrat_Request $request) {
		$records = $request->get('records');
		$records = explode(',', $records);
		$module = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($module);
		$fieldModels =  $moduleModel->getFields();

		foreach($records as $record) {
			$recordModels[] = Ottocrat_Record_Model::getInstanceById($record);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDS', $records);
		$viewer->assign('RECORDMODELS', $recordModels);
		$viewer->assign('FIELDS', $fieldModels);
		$viewer->assign('MODULE', $module);
		$viewer->view('MergeRecords.tpl', $module);
	}
}
