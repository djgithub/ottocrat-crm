<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Leads_ConvertLead_View extends Ottocrat_Index_View {

	function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'ConvertLead')) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	function process(Ottocrat_Request $request) {
		$currentUserPriviligeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$viewer = $this->getViewer($request);
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Ottocrat_Record_Model::getInstanceById($recordId);
		$moduleModel = $recordModel->getModule();
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('CURRENT_USER_PRIVILEGE', $currentUserPriviligeModel);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('CONVERT_LEAD_FIELDS', $recordModel->getConvertLeadFields());

		$assignedToFieldModel = $moduleModel->getField('assigned_user_id');
		$assignedToFieldModel->set('fieldvalue', $recordModel->get('assigned_user_id'));
		$viewer->assign('ASSIGN_TO', $assignedToFieldModel);

		$potentialModuleModel = Ottocrat_Module_Model::getInstance('Potentials');
		$accountField = Ottocrat_Field_Model::getInstance('related_to', $potentialModuleModel);
		$contactField = Ottocrat_Field_Model::getInstance('contact_id', $potentialModuleModel);
		$viewer->assign('ACCOUNT_FIELD_MODEL', $accountField);
		$viewer->assign('CONTACT_FIELD_MODEL', $contactField);
		
		$contactsModuleModel = Ottocrat_Module_Model::getInstance('Contacts');
		$accountField = Ottocrat_Field_Model::getInstance('account_id', $contactsModuleModel);
		$viewer->assign('CONTACT_ACCOUNT_FIELD_MODEL', $accountField);
		
		$viewer->view('ConvertLead.tpl', $moduleName);
	}
}