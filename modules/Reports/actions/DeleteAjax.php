<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_DeleteAjax_Action extends Ottocrat_DeleteAjax_Action {

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$response = new Ottocrat_Response();

		$recordModel = Reports_Record_Model::getInstanceById($recordId, $moduleName);

		if (!$recordModel->isDefault() && $recordModel->isEditable()) {
			$recordModel->delete();
			$response->setResult(array(vtranslate('LBL_REPORTS_DELETED_SUCCESSFULLY', $parentModule)));
		} else {
			$response->setError(vtranslate('LBL_REPORT_DELETE_DENIED', $moduleName));
		}
		$response->emit();
	}
}
