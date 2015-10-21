<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_PickListDependency_List_View extends Settings_Ottocrat_List_View {
	
	public function preProcess(Ottocrat_Request $request, $display = true) {
		$moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();
		$forModule = $request->get('formodule');
		$viewer = $this->getViewer($request);
		$viewer->assign('PICKLIST_MODULES_LIST',$moduleModelList);
		$viewer->assign('FOR_MODULE',$forModule);
		parent::preProcess($request, $display);
	}
	
	public function process(Ottocrat_Request $request) {
       if($request->isAjax()) {
			$moduleModelList = Settings_PickListDependency_Module_Model::getPicklistSupportedModules();
			$forModule = $request->get('formodule');
			
			$viewer = $this->getViewer($request);
			$viewer->assign('PICKLIST_MODULES_LIST',$moduleModelList);
			$viewer->assign('FOR_MODULE',$forModule);
			
			$viewer = $this->getViewer($request);
			$this->initializeListViewContents($request, $viewer);
			$viewer->view('ListViewHeader.tpl', $request->getModule(false));
       }
       parent::process($request);
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
			'~libraries/jquery/malihu-custom-scrollbar/js/jquery.mCustomScrollbar.concat.min.js',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	public function getHeaderCss(Ottocrat_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = array(
			'~/libraries/jquery/malihu-custom-scrollbar/css/jquery.mCustomScrollbar.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}