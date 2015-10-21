<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_ChartSave_Action extends Reports_Save_Action {

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();

		$record = $request->get('record');
		$reportModel = new Reports_Record_Model();
		$reportModel->setModule('Reports');
		if(!empty($record) && !$request->get('isDuplicate')) {
			$reportModel->setId($record);
		}

		$reportModel->set('reportname', $request->get('reportname'));
		$reportModel->set('folderid', $request->get('folderid'));
		$reportModel->set('description', $request->get('reports_description'));

		$reportModel->setPrimaryModule($request->get('primary_module'));

		$secondaryModules = $request->get('secondary_modules');
		$secondaryModules = implode(':', $secondaryModules);
		$reportModel->setSecondaryModule($secondaryModules);

		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$reportModel->set('reporttype', 'chart');


		$dataFields = $request->get('datafields', 'count(*)');
		if(is_string($dataFields)) $dataFields = array($dataFields);

		$reportModel->set('reporttypedata', Zend_Json::encode(array(
																'type'=>$request->get('charttype', 'pieChart'),
																'groupbyfield'=>$request->get('groupbyfield'),
																'datafields'=>$dataFields)
															));
		$reportModel->save();

		$loadUrl = $reportModel->getDetailViewUrl();
		header("Location: $loadUrl");
	}
}
