<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Ottocrat_FindDuplicates_View extends Ottocrat_List_View {

	function preProcess(Ottocrat_Request $request, $display = true) {
		$viewer = $this->getViewer ($request);
		$this->initializeListViewContents($request, $viewer);
		parent::preProcess($request, $display);
	}

	public function preProcessTplName(Ottocrat_Request $request) {
		return 'FindDuplicatePreProcess.tpl';
	}

	function process (Ottocrat_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$this->initializeListViewContents($request, $viewer);

		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('FindDuplicateContents.tpl', $moduleName);
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
			'modules.Ottocrat.resources.List',
			'modules.Ottocrat.resources.FindDuplicates',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Ottocrat_Request $request, Ottocrat_Viewer $viewer) {
		$currentUser = vglobal('current_user');
		$viewer = $this->getViewer ($request);
		$module = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($module);

		$massActionLink = array(
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_DELETE',
			'linkurl' => 'Javascript:Ottocrat_FindDuplicates_Js.massDeleteRecords("'.Ottocrat_Request:: encryptLink('index.php?module='.$module.'&action=MassDelete').'");',
			'linkicon' => ''
		);
		$massActionLinks[] = Ottocrat_Link_Model::getInstanceFromValues($massActionLink);
		$viewer->assign('LISTVIEW_LINKS', $massActionLinks);
		$viewer->assign('MODULE_MODEL', $moduleModel);

		$pageNumber = $request->get('page');
		if(empty($pageNumber)){
			$pageNumber = '1';
		}
		$pagingModel = new Ottocrat_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pageLimit = $pagingModel->getPageLimit();

		$duplicateSearchFields = $request->get('fields');
		$dataModelInstance = Ottocrat_FindDuplicate_Model::getInstance($module);
		$dataModelInstance->set('fields', $duplicateSearchFields);

		$ignoreEmpty = $request->get('ignoreEmpty');
		$ignoreEmptyValue = false;
		if($ignoreEmpty == 'on' || $ignoreEmpty == 'true' || $ignoreEmpty == '1') $ignoreEmptyValue = true;
		$dataModelInstance->set('ignoreEmpty', $ignoreEmptyValue);

		if(!$this->listViewEntries) {
			$this->listViewEntries = $dataModelInstance->getListViewEntries($pagingModel);
		}

		if(!$this->listViewHeaders){
			$this->listViewHeaders = $dataModelInstance->getListViewHeaders();
		}
		if(!$this->rows) {
			$this->rows = $dataModelInstance->getRecordCount();
			$viewer->assign('TOTAL_COUNT', $this->rows);
		}

		$rowCount = 0;
		foreach($this->listViewEntries as $group) {
			foreach($group as $row) {
				$rowCount++;
			}
		}
		//for calculating the page range
		for($i=0; $i<$rowCount; $i++) $dummyListEntries[] = $i;
		$pagingModel->calculatePageRange($dummyListEntries);

		$viewer->assign('IGNORE_EMPTY', $ignoreEmpty);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $rowCount);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER',$pageNumber);
		$viewer->assign('MODULE', $module);
		$viewer->assign('DUPLICATE_SEARCH_FIELDS', $duplicateSearchFields);

		$customViewModel = CustomView_Record_Model::getAllFilterByModule($module);
		$viewer->assign('VIEW_NAME', $customViewModel->getId());
	}

	/**
	 * Function returns the number of records for the current filter
	 * @param Ottocrat_Request $request
	 */
	function getRecordsCount(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$duplicateSearchFields = $request->get('fields');
		$dataModelInstance = Ottocrat_FindDuplicate_Model::getInstance($moduleName);

		$ignoreEmpty = $request->get('ignoreEmpty');
		$ignoreEmptyValue = false;
		if($ignoreEmpty == 'on' || $ignoreEmpty == 'true' || $ignoreEmpty == '1') $ignoreEmptyValue = true;
		$dataModelInstance->set('ignoreEmpty', $ignoreEmptyValue);

		$dataModelInstance->set('fields', $duplicateSearchFields);
		$count = $dataModelInstance->getRecordCount();

		$result = array();
		$result['module'] = $moduleName;
		$result['count'] = $count;

		$response = new Ottocrat_Response();
		$response->setEmitType(Ottocrat_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}