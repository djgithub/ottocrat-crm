<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PickListDependency_AddDependency_View extends Settings_Ottocrat_IndexAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('GetPickListFields');
	}

	function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && method_exists($this, $mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$qualifiedModule = $request->getModule(true);
		$viewer = $this->getViewer($request);
		$moduleModels = Ottocrat_Module_Model::getEntityModules();

		$viewer->assign('MODULES', $moduleModels);
		echo $viewer->view('AddDependency.tpl', $qualifiedModule);
	}

	/**
	 * Function returns the picklist field for a module
	 * @param Ottocrat_Request $request
	 */
	function GetPickListFields(Ottocrat_Request $request) {
		$module = $request->get('sourceModule');

		$fieldList = Settings_PickListDependency_Module_Model::getAvailablePicklists($module);

		$response = new Ottocrat_Response();
		$response->setResult($fieldList);
		$response->emit();
	}

	function CheckCyclicDependency() {

	}
}