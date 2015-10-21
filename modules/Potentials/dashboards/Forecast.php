<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Potentials_Forecast_Dashboard extends Ottocrat_IndexAjax_View {

	/**
	 * Function to get the list of Script models to be included
	 * @param Ottocrat_Request $request
	 * @return <Array> - List of Ottocrat_JsScript_Model instances
	 */
	function getHeaderScripts(Ottocrat_Request $request) {

		$jsFileNames = array(
			'~/libraries/jquery/jqplot/plugins/jqplot.cursor.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.dateAxisRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.logAxisRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.canvasTextRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js'
		);

		$headerScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $headerScriptInstances;
	}

	public function process(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$linkId = $request->get('linkid');
		
		$expectedclosedate = $request->get('expectedclosedate');
		
		//Date conversion from user to database format
		if(!empty($expectedclosedate)) {
			$closingdates['start'] = Ottocrat_Date_UIType::getDBInsertedValue($expectedclosedate['start']);
			$closingdates['end'] = Ottocrat_Date_UIType::getDBInsertedValue($expectedclosedate['end']);
		}
		
		$createdTime = $request->get('createdtime');
		
		//Date conversion from user to database format
		if(!empty($createdTime)) {
			$dates['start'] = Ottocrat_Date_UIType::getDBInsertedValue($createdTime['start']);
			$dates['end'] = Ottocrat_Date_UIType::getDBInsertedValue($createdTime['end']);
		}
		
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getForecast($closingdates,$dates);

		$widget = Ottocrat_Widget_Model::getInstance($linkId, $currentUser->getId());

		//Include special script and css needed for this widget
		$viewer->assign('SCRIPTS',$this->getHeaderScripts($request));
		
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Forecast.tpl', $moduleName);
		}
	}
}
