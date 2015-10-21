<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Ottocrat_List_View extends Ottocrat_Index_View {
	protected $listViewEntries = false;
	protected $listViewCount = false;
	protected $listViewLinks = false;
	protected $listViewHeaders = false;
	function __construct() {
		parent::__construct();
	}

	function preProcess(Ottocrat_Request $request, $display=true) {
		parent::preProcess($request, false);

		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();

		$listViewModel = Ottocrat_ListView_Model::getInstance($moduleName);
		$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));
		$this->viewName = $request->get('viewname');
		if(empty($this->viewName)){
			//If not view name exits then get it from custom view
			//This can return default view id or view id present in session
			$customView = new CustomView();
			$this->viewName = $customView->getViewId($moduleName);
		}

		$quickLinkModels = $listViewModel->getSideBarLinks($linkParams);
		$viewer->assign('QUICK_LINKS', $quickLinkModels);
		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('VIEWID', $this->viewName);

		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	function preProcessTplName(Ottocrat_Request $request) {
		return 'ListViewPreProcess.tpl';
	}

	//Note : To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/*function preProcessParentTplName(Ottocrat_Request $request) {
		return parent::preProcessTplName($request);
	}*/

	protected function preProcessDisplay(Ottocrat_Request $request) {
		parent::preProcessDisplay($request);
	}


	function process (Ottocrat_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$this->viewName = $request->get('viewname');

		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('ListViewContents.tpl', $moduleName);
	}

	function postProcess(Ottocrat_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();

		$viewer->view('ListViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
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
			"modules.$moduleName.resources.List",
			'modules.CustomView.resources.CustomView',
			"modules.$moduleName.resources.CustomView",
			"modules.Emails.resources.MassEdit",
			"modules.Ottocrat.resources.CkEditor"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Ottocrat_Request $request, Ottocrat_Viewer $viewer) {
		$moduleName = $request->getModule();
		$cvId = $this->viewName;
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

		if(empty ($pageNumber)){
			$pageNumber = '1';
		}

		$listViewModel = Ottocrat_ListView_Model::getInstance($moduleName, $cvId);
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'), 'CVID'=>$cvId);
		$linkModels = $listViewModel->getListViewMassActions($linkParams);

		$pagingModel = new Ottocrat_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('viewid', $request->get('viewname'));

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

        $searchParmams = $request->get('search_params');
        if(empty($searchParmams)) {
            $searchParmams = array();
        }
        $transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParmams, $listViewModel->getModule());
        $listViewModel->set('search_params',$transformedSearchParams);


        //To make smarty to get the details easily accesible
        foreach($searchParmams as $fieldListGroup){
            foreach($fieldListGroup as $fieldSearchInfo){
                $fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
                $fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
                $searchParmams[$fieldName] = $fieldSearchInfo;
			}
		}
		$username=$_SESSION['username'];

		$add_contact_flg	=	0;
		$add_lead_flg		=	0;
		$add_opp_flg		=	0;
		$add_org_flg		=	0;

		if($username!='') {
			global $adb, $WP_USER,$WP_DB,$WP_PASSWORD,$OT_USER,$OT_DB,$OT_PASSWORD;
			$adb->disconnect();
			$adb->resetSettings('mysqli', 'localhost', $WP_DB, $WP_USER, $WP_PASSWORD);
			$Query = "SELECT p.* FROM logicint_tlrsite.tbl_user u inner join logicint_tlrsite.tbl_packagemaster p on u
		.package_id=p.package_id  WHERE username='$username'";
			$adb->checkConnection();
			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$Result = $adb->pquery($Query, array());
			$package_id = $adb->query_result($Result, 0, "package_id");
			$maxlead_cnt 		= $adb->query_result($Result, 0, "leads");
			$maxcontact_cnt = $adb->query_result($Result, 0, "contacts");
			$maxopp_cnt 		= $adb->query_result($Result, 0, "opportunities");
			$maxorg_cnt 		= $adb->query_result($Result, 0, "organizations");

			$adb->disconnect();
			$adb->resetSettings('mysqli', 'localhost', $OT_DB, $OT_USER, $OT_PASSWORD);

			$cloofQuery = "select (SELECT count(*)  FROM ottocrat_contactdetails) as contactcnt, (SELECT count(*)  FROM ottocrat_leaddetails) as leadcnt,(SELECT count(*)  FROM ottocrat_potential) as oppcnt,(SELECT count(*)  FROM ottocrat_account) as orgcnt";
			$adb->checkConnection();
			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$cloofResult = $adb->pquery($cloofQuery, array());
			$contact_cnt = $adb->query_result($cloofResult, 0, "contactcnt");
			$lead_cnt = $adb->query_result($cloofResult, 0, "leadcnt");
			$opp_cnt = $adb->query_result($cloofResult, 0, "oppcnt");
			$org_cnt = $adb->query_result($cloofResult, 0, "orgcnt");



			if ($maxcontact_cnt > $contact_cnt || $maxcontact_cnt == 0) $add_contact_flg = 1;
			if ($maxlead_cnt > $lead_cnt || $maxlead_cnt == 0) $add_lead_flg = 1;
			if ($maxopp_cnt > $opp_cnt || $maxopp_cnt == 0) $add_opp_flg = 1;
			if ($maxorg_cnt > $org_cnt || $maxorg_cnt == 0) $add_org_flg = 1;


		}else
		{

			$add_contact_flg = 1;
			$add_lead_flg 		= 1;
			$add_opp_flg 		= 1;
			$add_org_flg 		= 1;

		}


		$add_record_flg=0;
		if($moduleName=='Contacts')
		$add_record_flg=$add_contact_flg;
		if($moduleName=='Leads')
		$add_record_flg=$add_lead_flg;
		if($moduleName=='Potentials')
		$add_record_flg=$add_opp_flg;
		if($moduleName=='Accounts')
			$add_record_flg=$add_org_flg;

		$viewer->assign('ADD_RECORD_FLAG', $add_record_flg);
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
		$viewer->assign('LIST_VIEW_MODEL', $listViewModel);
		$viewer->assign('GROUPS_IDS', Ottocrat_Util_Helper::getGroupsIdsForUsers($currentUser->getId()));
		$viewer->assign('IS_MODULE_EDITABLE', $listViewModel->getModule()->isPermitted('EditView'));
		$viewer->assign('IS_MODULE_DELETABLE', $listViewModel->getModule()->isPermitted('Delete'));
        $viewer->assign('SEARCH_DETAILS', $searchParmams);
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

        $searchParmams = $request->get('search_params');
        $listViewModel->set('search_params',$this->transferListSearchParamsToFilterCondition($searchParmams, $listViewModel->getModule()));

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


    public function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel) {
        return Ottocrat_Util_Helper::transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel);
    }
}