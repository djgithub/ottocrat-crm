<?php

/* +**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ********************************************************************************** */
/*
 * Class for reloading the Reports headers
 */

class Reports_ListAjax_View extends Reports_List_View {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('getListViewCount');
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getPageCount');
	}

	function preProcess(Ottocrat_Request $request) {
		
	}

	function process(Ottocrat_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$folders = $moduleModel->getFolders();
		$listViewModel = new Reports_ListView_Model();
		$listViewModel->set('module', $moduleModel);

		$linkModels = $listViewModel->getListViewLinks();

		$viewer->assign('LISTVIEW_LINKS', $linkModels);
		$viewer->assign('FOLDERS', $folders);

		$viewer->view('ListViewFolders.tpl', $moduleName);
	}

}