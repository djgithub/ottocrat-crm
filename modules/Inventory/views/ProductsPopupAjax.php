<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Inventory_ProductsPopupAjax_View extends Inventory_ProductsPopup_View {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('getListViewCount');
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getPageCount');
	}
	
	/**
	 * Function returns module name for which Popup will be initialized
	 * @param type $request
	 */
	public function getModule($request) {
		return 'Products';
	}
	
	function preProcess(Ottocrat_Request $request) {
		return true;
	}

	function postProcess(Ottocrat_Request $request) {
		return true;
	}

	function process (Ottocrat_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$viewer = $this->getViewer ($request);

		$this->initializeListViewContents($request, $viewer);
		$moduleName = 'Inventory';
		$viewer->assign('MODULE_NAME',$moduleName);
		echo $viewer->view('PopupContents.tpl', $moduleName, true);
	}
}