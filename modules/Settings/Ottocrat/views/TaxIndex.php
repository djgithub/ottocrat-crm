<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_TaxIndex_View extends Settings_Ottocrat_Index_View {
    
    public function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		
		$taxRecordModel = new Settings_Ottocrat_TaxRecord_Model();
        $productAndServicesTaxList = Settings_Ottocrat_TaxRecord_Model::getProductTaxes();
        $shippingAndHandlingTaxList = Settings_Ottocrat_TaxRecord_Model::getShippingTaxes();
        
        $qualifiedModuleName = $request->getModule(false);
        
        $viewer = $this->getViewer($request);
		$viewer->assign('TAX_RECORD_MODEL', $taxRecordModel);
        $viewer->assign('PRODUCT_AND_SERVICES_TAXES',$productAndServicesTaxList);
        $viewer->assign('SHIPPING_AND_HANDLING_TAXES',$shippingAndHandlingTaxList);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->view('TaxIndex.tpl',$qualifiedModuleName);
    }
	
	
		
	
	function getPageTitle(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_TAX_CALCULATIONS',$qualifiedModuleName);
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
			"modules.Settings.$moduleName.resources.Tax"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}