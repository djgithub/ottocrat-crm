<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Emails_BasicAjax_Action extends Ottocrat_Action_Controller {

	public function checkPermission(Ottocrat_Request $request) {
		return;
	}

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->get('module');
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$searchValue = $request->get('searchValue');

		$emailsResult = array();
		if ($searchValue) {
			$emailsResult = $moduleModel->searchEmails($request->get('searchValue'));
		}

		$response = new Ottocrat_Response();
		$response->setResult($emailsResult);
		$response->emit();
	}
}

?>
