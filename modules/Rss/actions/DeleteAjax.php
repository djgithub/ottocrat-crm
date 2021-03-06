<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Rss_DeleteAjax_Action extends Ottocrat_Delete_Action {

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordModel = Rss_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->delete();

		$response = new Ottocrat_Response();
		$response->setResult(array('record'=>$recordId, 'module'=>$moduleName));
		$response->emit();
	}
}
