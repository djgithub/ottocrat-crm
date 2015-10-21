<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
class Settings_Ottocrat_Basic_Action extends Settings_Ottocrat_IndexAjax_View {
    
    function __construct() {
		parent::__construct();
		$this->exposeMethod('updateFieldPinnedStatus');
	}
    
    function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    public function updateFieldPinnedStatus(Ottocrat_Request $request) {
        $fieldId = $request->get('fieldid');
        $menuItemModel = Settings_Ottocrat_MenuItem_Model::getInstanceById($fieldId);
        
        $pin = $request->get('pin');
        if($pin == 'true') {
            $menuItemModel->markPinned();
        }else{
            $menuItemModel->unMarkPinned();
        }
        
	$response = new Ottocrat_Response();
	$response->setResult(array('SUCCESS'=>'OK'));
	$response->emit();
    }
    
    public function validateRequest(Ottocrat_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}