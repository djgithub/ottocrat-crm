<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Products_SubProducts_Action extends Ottocrat_Action_Controller {

	function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function process(Ottocrat_Request $request) {
		$productId = $request->get('record');
		$productModel = Ottocrat_Record_Model::getInstanceById($productId, 'Products');
		$subProducts = $productModel->getSubProducts();
		$values = array();
		foreach($subProducts as $subProduct) {
			$values[$subProduct->getId()] = $subProduct->getName();
		}

		$response = new Ottocrat_Response();
		$response->setResult($values);
		$response->emit();
	}
}
