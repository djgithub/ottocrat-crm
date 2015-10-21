<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Users_IndexAjax_Action extends Ottocrat_BasicAjax_Action {
    
    function __construct() {
		parent::__construct();
		$this->exposeMethod('toggleLeftPanel');
	}
    
    function process(Ottocrat_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    public function toggleLeftPanel (Ottocrat_Request $request) {        
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentUser->set('leftpanelhide',$request->get('showPanel'));
        $currentUser->leftpanelhide = $request->get('showPanel');
        $currentUser->set('mode','edit');
        $response = new Ottocrat_Response();
        try{
            $currentUser->save();
            $response->setResult(array('success'=>true));
        }catch(Exception $e){
            $response->setError($e->getCode(),$e->getMessage());
        }
        $response->emit();
    }
}