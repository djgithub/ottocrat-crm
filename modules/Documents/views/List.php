<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Documents_List_View extends Ottocrat_List_View {
	function __construct() {
		parent::__construct();
	}
	
	function preProcess (Ottocrat_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();

		$documentModuleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$defaultCustomFilter = $documentModuleModel->getDefaultCustomFilter();
		$folderList = Documents_Module_Model::getAllFolders();

		$viewer->assign('DEFAULT_CUSTOM_FILTER_ID', $defaultCustomFilter);
		$viewer->assign('FOLDERS', $folderList);

		parent::preProcess($request);
	}
    
    
    /*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Ottocrat_Request $request, Ottocrat_Viewer $viewer) {
		$moduleName = $request->getModule();

		$username=$_SESSION['username'];
		$add_file_flg		=	0;

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

		if(empty ($pageNumber)){
			$pageNumber = '1';
		}

		$listViewModel = Ottocrat_ListView_Model::getInstance($moduleName, $cvId);

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


		if($username!='') {
			global $adb, $WP_USER,$WP_DB,$WP_PASSWORD,$OT_USER,$OT_DB,$OT_PASSWORD;


			$adb->disconnect();
			$adb->resetSettings('mysqli', 'localhost', $WP_DB, $WP_USER, $WP_PASSWORD);

			$Query = "SELECT file_storage,file_storage_type FROM logicint_tlrsite.tbl_user u inner join logicint_tlrsite.tbl_packagemaster p on u
		.package_id=p.package_id  WHERE username='$username'";
			$adb->checkConnection();
			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$Result = $adb->pquery($Query, array());
			$file_size = $adb->query_result($Result, 0, "file_storage");
			$storage_type = $adb->query_result($Result, 0, "file_storage_type");
			if($storage_type=='MB')
				$maxfile_size=$file_size*1024;
			if($storage_type=='GB')
				$maxfile_size=$file_size*1024*1024;

			$adb->disconnect();
			$adb->resetSettings('mysqli', 'localhost', $OT_DB, $OT_USER, $OT_PASSWORD);
			$adb->checkConnection();
			$fQuery = "SELECT sum(filesize) as filesize  FROM ottocrat_notes";

			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$fResult = $adb->pquery($fQuery, array());
			$ufile_size = $adb->query_result($fResult, 0, "filesize");


			if ($maxfile_size > $ufile_size || $maxfile_size == 0) $add_file_flg = 1;

		}else
		{
			$add_file_flg 		= 1;
		}

		$viewer->assign('ADD_RECORD_FLAG', $add_file_flg);
        
        $listViewModel->set('folder_id',$request->get('folder_id'));
        $listViewModel->set('folder_value',$request->get('folder_value'));
        
		if(!$this->listViewHeaders){
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if(!$this->listViewEntries){
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		$noOfEntries = count($this->listViewEntries);

		$viewer->assign('VIEWID', $cvId);
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
		$viewer->assign('FOLDER_NAME',$request->get('folder_value'));

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

		$viewer->assign('IS_MODULE_EDITABLE', $listViewModel->getModule()->isPermitted('EditView'));
		$viewer->assign('IS_MODULE_DELETABLE', $listViewModel->getModule()->isPermitted('Delete'));
        $viewer->assign('SEARCH_DETAILS', $searchParmams);
	}
}