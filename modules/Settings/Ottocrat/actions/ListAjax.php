<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_ListAjax_Action extends Settings_Ottocrat_ListAjax_View{
    
    public function __construct() {
        parent::__construct();
        $this->exposeMethod('getPageCount');
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
    
    public function getListViewCount(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$sourceModule = $request->get('sourceModule');

		$listViewModel = Settings_Ottocrat_ListView_Model::getInstance($qualifiedModuleName);
		
		if(!empty($sourceModule)) {
			$listViewModel->set('sourceModule', $sourceModule);
		}

		return $listViewModel->getListViewCount();
    }
    
    public function getPageCount(Ottocrat_Request $request) {
        $numOfRecords = $this->getListViewCount($request);
        $pagingModel = new Ottocrat_Paging_Model();
        $pageCount = ceil((int) $numOfRecords/(int)($pagingModel->getPageLimit()));
        
		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $numOfRecords;
		$response = new Ottocrat_Response();
		$response->setResult($result);
		$response->emit();
    }
}