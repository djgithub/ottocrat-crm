<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_PickListDependency_Index_Action extends Settings_Ottocrat_Basic_Action {
    
    function __construct() {
        parent::__construct();
        $this->exposeMethod('checkCyclicDependency');
    }
   
    public function checkCyclicDependency(Ottocrat_Request $request) {
        $module = $request->get('sourceModule');
        $sourceField = $request->get('sourcefield');
        $targetField = $request->get('targetfield');
        $result = Ottocrat_DependencyPicklist::checkCyclicDependency($module, $sourceField, $targetField);
        $response = new Ottocrat_Response();
        $response->setResult(array('result'=>$result));
        $response->emit();
    }
}