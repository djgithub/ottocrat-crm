<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class PurchaseOrder_CompanyDetails_Action extends Ottocrat_Action_Controller {

	function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function process(Ottocrat_Request $request) {
		$companyModel = Ottocrat_CompanyDetails_Model::getInstanceById();
        $companyDetails = array(
            'street' => $companyModel->get('organizationname') .' '.$companyModel->get('address'),
            'city' => $companyModel->get('city'),
            'state' => $companyModel->get('state'),
            'code' => $companyModel->get('code'),
            'country' =>  $companyModel->get('country'),
            );
		$response = new Ottocrat_Response();
		$response->setResult($companyDetails);
		$response->emit();
	}
}

?>