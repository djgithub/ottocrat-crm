<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_TaxAjax_View extends Settings_Ottocrat_Index_View {
    
    public function process(Ottocrat_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$taxId = $request->get('taxid');
		$type = $request->get('type');
		
		if(empty($taxId)) {
            $taxRecordModel = new Settings_Ottocrat_TaxRecord_Model();
        }else{
            $taxRecordModel = Settings_Ottocrat_TaxRecord_Model::getInstanceById($taxId,$type);
        }
		
		$viewer->assign('TAX_TYPE', $type);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TAX_RECORD_MODEL', $taxRecordModel);

		echo $viewer->view('EditTax.tpl', $qualifiedModuleName, true);
    }
	
}