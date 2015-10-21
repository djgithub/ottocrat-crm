<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Currency_EditAjax_View extends Settings_Ottocrat_IndexAjax_View{
    
    public function process(Ottocrat_Request $request) {
        $record = $request->get('record');
        if(!empty($record)) {
            $recordModel = Settings_Currency_Record_Model::getInstance($record);
        }else {
		   $recordModel = new Settings_Currency_Record_Model(); 
        }
        
        $allCurrencies = Settings_Currency_Record_Model::getAllNonMapped($record);
        $otherExistingCurrencies = Settings_Currency_Record_Model::getAll($record);
		
		foreach ($otherExistingCurrencies as $currencyModel) {
			if($currencyModel->isBaseCurrency()) {
				$baseCurrencyModel = $currencyModel;
				break;
			}
		}
        $viewer = $this->getViewer($request);
        
        $qualifiedName = $request->getModule(false);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedName);
        $viewer->assign('RECORD_MODEL',$recordModel);
        $viewer->assign('ALL_CURRENCIES',$allCurrencies);
        $viewer->assign('OTHER_EXISTING_CURRENCIES',$otherExistingCurrencies);
        $viewer->assign('BASE_CURRENCY_MODEL', $baseCurrencyModel);
		
        $viewer->view('EditAjax.tpl',$qualifiedName);
    }
}