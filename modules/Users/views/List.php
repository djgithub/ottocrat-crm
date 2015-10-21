<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Users_List_View extends Settings_Ottocrat_List_View {

	function checkPermission(Ottocrat_Request $request) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if(!$currentUserModel->isAdminUser()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Ottocrat'));
		}
	}
    
	function getHeaderScripts(Ottocrat_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		
		$jsFileNames = array(
			'modules.Users.resources.List',
            "modules.Emails.resources.MassEdit",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	public function process(Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		$viewer->view('ListViewContents.tpl', $request->getModule(false));
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Ottocrat_Request $request, Ottocrat_Viewer $viewer) {
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');

		if($sortOrder == "ASC"){
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		}else{
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}
		$username=$_SESSION['username'];
		$add_user_flg		=	0;

		if($username!='') {
			global $adb, $WP_USER,$WP_DB,$WP_PASSWORD,$OT_USER,$OT_DB,$OT_PASSWORD;


				$adb->disconnect();
				$adb->resetSettings('mysqli', 'localhost', $WP_DB, $WP_USER, $WP_PASSWORD);
			$Query = "SELECT users FROM logicint_tlrsite.tbl_user u inner join logicint_tlrsite.tbl_packagemaster p on u
		.package_id=p.package_id  WHERE username='$username'";

			$adb->checkConnection();
			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$Result = $adb->pquery($Query, array());
			$maxuser_cnt = $adb->query_result($Result, 0, "users");

			$adb->disconnect();
			$adb->resetSettings('mysqli', 'localhost', $OT_DB, $OT_USER, $OT_PASSWORD);

			$uQuery = "SELECT count(*) as usercnt FROM ottocrat_users";
			$adb->checkConnection();
			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$uResult = $adb->pquery($uQuery, array());
			$user_cnt = $adb->query_result($uResult, 0, "usercnt");


			if ($maxuser_cnt > $user_cnt || $maxuser_cnt == 0) $add_user_flg = 1;

		}else
		{
			$add_user_flg 		= 1;
		}

		$viewer->assign('ADD_USER_FLAG', $add_user_flg);

		if(empty ($pageNumber)){
			$pageNumber = '1';
		}
                
        $status = $request->get('status');
        if(empty($status))
            $status = 'Active';

		$listViewModel = Ottocrat_ListView_Model::getInstance($moduleName, $cvId);

		$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'), 'CVID'=>$cvId);
		$linkModels = $listViewModel->getListViewMassActions($linkParams);
                $listViewModel->set('status', $status);

		$pagingModel = new Ottocrat_Paging_Model();
		$pagingModel->set('page', $pageNumber);

		if(!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder',$sortOrder);
		}

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
		if(!empty($operator)) {
			$listViewModel->set('operator', $operator);
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
		}
		if(!empty($searchKey) && !empty($searchValue)) {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
		}
		if(!$this->listViewHeaders){
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if(!$this->listViewEntries){
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		$noOfEntries = count($this->listViewEntries);

		$viewer->assign('MODULE', $moduleName);

		if(!$this->listViewLinks){
			$this->listViewLinks = $listViewModel->getListViewLinks($linkParams);
		}

		$viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);

		$viewer->assign('LISTVIEW_MASSACTIONS', $linkModels['LISTVIEWMASSACTION']);

		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER',$pageNumber);

		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);
		$viewer->assign('QUALIFIED_MODULE', $moduleName);
		
		$viewer->assign('LISTVIEW_ENTRIES_COUNT',$noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);

		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			if(!$this->listViewCount){
				$this->listViewCount = $listViewModel->getListViewCount();
			}
			$totalCount = $this->listViewCount;
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if($pageCount == 0){
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('LISTVIEW_COUNT', $totalCount);
		}
		$viewer->assign('MODULE_MODEL', $listViewModel->getModule());
		$viewer->assign('IS_MODULE_EDITABLE', $listViewModel->getModule()->isPermitted('EditView'));
		$viewer->assign('IS_MODULE_DELETABLE', $listViewModel->getModule()->isPermitted('Delete'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
	}
	
	/**
	 * Function returns the number of records for the current filter
	 * @param Ottocrat_Request $request
	 */
	function getRecordsCount(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$count = $this->getListViewCount($request);

		$result = array();
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Ottocrat_Response();
		$response->setEmitType(Ottocrat_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get listView count
	 * @param Ottocrat_Request $request
	 */
	function getListViewCount(Ottocrat_Request $request){
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		if(empty($cvId)) {
			$cvId = '0';
		}

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');

		$listViewModel = Ottocrat_ListView_Model::getInstance($moduleName, $cvId);
		$listViewModel->set('search_key', $searchKey);
		$listViewModel->set('search_value', $searchValue);
		$listViewModel->set('operator', $request->get('operator'));

		$count = $listViewModel->getListViewCount();

		return $count;
	}



	/**
	 * Function to get the page count for list
	 * @return total number of pages
	 */
	function getPageCount(Ottocrat_Request $request){
		$listViewCount = $this->getListViewCount($request);
		$pagingModel = new Ottocrat_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $listViewCount;
		$response = new Ottocrat_Response();
		$response->setResult($result);
		$response->emit();
	}
}