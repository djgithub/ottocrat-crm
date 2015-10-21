<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Currency_TransformEditAjax_View extends Settings_Ottocrat_IndexAjax_View {
    
    public function process(Ottocrat_Request $request) {
        $record = $request->get('record');
        
        $currencyList = Settings_Currency_Record_Model::getAll($record);
        
        $qualifiedName = $request->getModule(false);
        $viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedName);
        $viewer->assign('CURRENCY_LIST',$currencyList);
        $viewer->assign('RECORD_MODEL',  Settings_Currency_Record_Model::getInstance($record));
        echo $viewer->view('TransformEdit.tpl', $qualifiedName, true);
    }
}