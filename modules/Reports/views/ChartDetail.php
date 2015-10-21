<?php
/*************************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Ondemand Commercial
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_ChartDetail_View extends Ottocrat_Index_View {

	public function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$record = $request->get('record');
		$reportModel = Reports_Record_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId()) && !$reportModel->isEditable()) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
	
	function preProcess(Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$this->record = $detailViewModel = Reports_DetailView_Model::getInstance($moduleName, $recordId);

		parent::preProcess($request);

		$reportModel = $detailViewModel->getRecord();
		$reportModel->setModule('Reports');

		$primaryModule = $reportModel->getPrimaryModule();
		$secondaryModules = $reportModel->getSecondaryModules();
		$primaryModuleModel = Ottocrat_Module_Model::getInstance($primaryModule);

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
		$permission = $userPrivilegesModel->hasModulePermission($primaryModuleModel->getId());

		if(!$permission) {
			$viewer->assign('MODULE', $primaryModule);
			$viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
			$viewer->view('OperationNotPermitted.tpl', $primaryModule);
			exit;
		}

		// Advanced filter conditions
		$viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $reportModel->transformToNewAdvancedFilter());
		$viewer->assign('PRIMARY_MODULE', $primaryModule);

		$recordStructureInstance = Ottocrat_RecordStructure_Model::getInstanceFromRecordModel($reportModel);
		$primaryModuleRecordStructure = $recordStructureInstance->getPrimaryModuleRecordStructure();
		$secondaryModuleRecordStructures = $recordStructureInstance->getSecondaryModuleRecordStructure();

		$viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $primaryModuleRecordStructure);
		$viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $secondaryModuleRecordStructures);

		$secondaryModuleIsCalendar = strpos($secondaryModules, 'Calendar');
		if(($primaryModule == 'Calendar') || ($secondaryModuleIsCalendar !== FALSE)){
			$advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
		} else{
			$advanceFilterOpsByFieldType = Ottocrat_Field_Model::getAdvancedFilterOpsByFieldType();
		}
		$viewer->assign('ADVANCED_FILTER_OPTIONS', Ottocrat_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
        $dateFilters = Ottocrat_Field_Model::getDateFilterTypes();
        foreach($dateFilters as $comparatorKey => $comparatorInfo) {
            $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
            $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
            $comparatorInfo['label'] = vtranslate($comparatorInfo['label'],$moduleName);
            $dateFilters[$comparatorKey] = $comparatorInfo;
        }

		$reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);

		$viewer->assign('PRIMARY_MODULE_FIELDS', $reportModel->getPrimaryModuleFieldsForAdvancedReporting());
		$viewer->assign('SECONDARY_MODULE_FIELDS', $reportModel->getSecondaryModuleFieldsForAdvancedReporting());
		$viewer->assign('CALCULATION_FIELDS', $reportModel->getModuleCalculationFieldsForReport());

        $viewer->assign('DATE_FILTERS', $dateFilters);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('RECORD', $recordId);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CHART_MODEL', $reportChartModel);

		$viewer->view('ChartReportHeader.tpl', $moduleName);
	}

	function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		echo $this->getReport($request);
	}

	function getReport(Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$record = $request->get('record');

		$reportModel = Reports_Record_Model::getInstanceById($record);
		$reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);
		$secondaryModules = $reportModel->getSecondaryModules();
		if(empty($secondaryModules)) {
			$viewer->assign('CLICK_THROUGH', true);
		}

		$viewer->assign('ADVANCED_FILTERS', $request->get('advanced_filter'));
		$viewer->assign('PRIMARY_MODULE_FIELDS', $reportModel->getPrimaryModuleFields());
		$viewer->assign('SECONDARY_MODULE_FIELDS', $reportModel->getSecondaryModuleFields());
		$viewer->assign('CALCULATION_FIELDS', $reportModel->getModuleCalculationFieldsForReport());
        
        $data = $reportChartModel->getData();
		$viewer->assign('CHART_TYPE', $reportChartModel->getChartType());
		$viewer->assign('DATA', json_encode($data, JSON_HEX_APOS));
		$viewer->assign('REPORT_MODEL', $reportModel);


		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('SECONDARY_MODULES',$secondaryModules);
		$viewer->assign('MODULE', $moduleName);

		$viewer->view('ChartReportContents.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Ottocrat_Request $request
	 * @return <Array> - List of Ottocrat_JsScript_Model instances
	 */
	function getHeaderScripts(Ottocrat_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Ottocrat.resources.Detail',
			"modules.Ottocrat.resources.dashboards.Widget",
			"modules.$moduleName.resources.Detail",
			"modules.$moduleName.resources.Edit",
			"modules.$moduleName.resources.Edit3",
			"modules.$moduleName.resources.ChartEdit",
			"modules.$moduleName.resources.ChartEdit2",
			"modules.$moduleName.resources.ChartEdit3",
			"modules.$moduleName.resources.ChartDetail",

			'~/libraries/jquery/jqplot/jquery.jqplot.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.barRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.canvasTextRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.categoryAxisRenderer.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.pointLabels.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.highlighter.min.js',
			'~/libraries/jquery/jqplot/plugins/jqplot.pieRenderer.min.js'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param Ottocrat_Request $request
	 * @return <Array> - List of Ottocrat_CssScript_Model instances
	 */
	public function getHeaderCss(Ottocrat_Request $request) {
		$parentHeaderCssScriptInstances = parent::getHeaderCss($request);

		$headerCss = array(
			'~/libraries/jquery/jqplot/jquery.jqplot.min.css',
		);
		$cssScripts = $this->checkAndConvertCssStyles($headerCss);
		$headerCssScriptInstances = array_merge($parentHeaderCssScriptInstances , $cssScripts);
		return $headerCssScriptInstances;
	}

}
