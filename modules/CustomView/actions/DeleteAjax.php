<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class CustomView_DeleteAjax_Action extends Ottocrat_Action_Controller {

	function preProcess(Ottocrat_Request $request) {
		return true;
	}

	function postProcess(Ottocrat_Request $request) {
		return true;
	}

	public function process(Ottocrat_Request $request) {
		$customViewModel = CustomView_Record_Model::getInstanceById($request->get('record'));

		$customViewModel->delete();
	}
    
    public function validateRequest(Ottocrat_Request $request) {
        $request->validateWriteAccess();
    }
}
