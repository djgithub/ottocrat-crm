<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Users_DeleteImage_Action extends Ottocrat_Action_Controller {

	public function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('id');

		if (!(Users_Privileges_Model::isPermitted($moduleName, 'EditView', $record) && Users_Privileges_Model::isPermitted($moduleName, 'Delete', $record))) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$imageId = $request->get('imageid');

		$response = new Ottocrat_Response();
		if ($recordId) {
			$recordModel = Users_Record_Model::getInstanceById($recordId, $moduleName);
			$status = $recordModel->deleteImage($imageId);
			if ($status) {
				$response->setResult(array(vtranslate('LBL_IMAGE_DELETED_SUCCESSFULLY', $moduleName)));
			}
		} else {
			$response->setError(vtranslate('LBL_IMAGE_NOT_DELETED', $moduleName));
		}

		$response->emit();
	}
}
