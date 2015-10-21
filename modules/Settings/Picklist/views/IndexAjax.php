<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Picklist_IndexAjax_View extends Settings_Ottocrat_IndexAjax_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('showEditView');
        $this->exposeMethod('showDeleteView');
        $this->exposeMethod('getPickListDetailsForModule');
        $this->exposeMethod('getPickListValueForField');
        $this->exposeMethod('getPickListValueByRole');
        $this->exposeMethod('showAssignValueToRoleView');
    }

    public function process(Ottocrat_Request $request) {
        $mode = $request->get('mode');
        if($this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function showEditView(Ottocrat_Request $request) {
        $module = $request->get('source_module');
        $pickListFieldId = $request->get('pickListFieldId');
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldId);
        $valueToEdit = $request->getRaw('fieldValue');

		$selectedFieldEditablePickListValues = $fieldModel->getEditablePicklistValues($fieldModel->getName());
		$selectedFieldNonEditablePickListValues = $fieldModel->getNonEditablePicklistValues($fieldModel->getName());
	//	$selectedFieldAllPickListValues =  array_map('Ottocrat_Util_Helper::toSafeHTML', $selectedFieldAllPickListValues);
        $qualifiedName = $request->getModule(false);
        $viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
        $viewer->assign('SOURCE_MODULE', $module);
        $viewer->assign('SOURCE_MODULE_NAME',$module);
        $viewer->assign('FIELD_MODEL',$fieldModel);
        $viewer->assign('FIELD_VALUE',$valueToEdit);
		$viewer->assign('SELECTED_PICKLISTFIELD_EDITABLE_VALUES',$selectedFieldEditablePickListValues);
		$viewer->assign('SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES',$selectedFieldNonEditablePickListValues);
		$viewer->assign('MODULE',$moduleName);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedName);
        echo $viewer->view('EditView.tpl', $qualifiedName, true);
    }

    public function showDeleteView(Ottocrat_Request $request) {
        $module = $request->get('source_module');
        $pickListFieldId = $request->get('pickListFieldId');
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldId);
        $valueToDelete = $request->get('fieldValue');

		$selectedFieldEditablePickListValues = $fieldModel->getEditablePicklistValues($fieldModel->getName());
		$selectedFieldNonEditablePickListValues = $fieldModel->getNonEditablePicklistValues($fieldModel->getName());
		$selectedFieldEditablePickListValues =  array_map('Ottocrat_Util_Helper::toSafeHTML', $selectedFieldEditablePickListValues);
		if(!empty($selectedFieldNonEditablePickListValues)) {
			$selectedFieldNonEditablePickListValues =  array_map('Ottocrat_Util_Helper::toSafeHTML', $selectedFieldNonEditablePickListValues);
		}

		// merge the non-editable picklist value also with editable values, so that deleted value can be replaced with either of them.
                if(!empty($selectedFieldNonEditablePickListValues)){
                    $selectedFieldEditablePickListValues = array_merge($selectedFieldEditablePickListValues, $selectedFieldNonEditablePickListValues);
                }

        $qualifiedName = $request->getModule(false);
        $viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
        $viewer->assign('SOURCE_MODULE', $module);
        $viewer->assign('SOURCE_MODULE_NAME',$module);
        $viewer->assign('FIELD_MODEL',$fieldModel);

		$viewer->assign('MODULE',$moduleName);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedName);
		$viewer->assign('SELECTED_PICKLISTFIELD_EDITABLE_VALUES',$selectedFieldEditablePickListValues);
		$viewer->assign('SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES',$selectedFieldNonEditablePickListValues);
		$viewer->assign('FIELD_VALUES',array_map('Ottocrat_Util_Helper::toSafeHTML', $valueToDelete));
        echo $viewer->view('DeleteView.tpl', $qualifiedName, true);
    }

    public function getPickListDetailsForModule(Ottocrat_Request $request) {
        $sourceModule = $request->get('source_module');
        $moduleModel = Settings_Picklist_Module_Model::getInstance($sourceModule);
        //TODO: see if you needs to optimize this , since its will gets all the fields and filter picklist fields
        $pickListFields = $moduleModel->getFieldsByType(array('picklist','multipicklist'));

        $qualifiedName = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('PICKLIST_FIELDS',$pickListFields);
		$viewer->assign('SELECTED_MODULE_NAME',$sourceModule);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedName);
        $viewer->view('ModulePickListDetail.tpl',$qualifiedName);
    }

    public function getPickListValueForField(Ottocrat_Request $request) {
        $sourceModule = $request->get('source_module');
        $pickFieldId = $request->get('pickListFieldId');
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldId);

		$moduleName = $request->getModule();
        $qualifiedName = $request->getModule(false);

        $selectedFieldAllPickListValues = Ottocrat_Util_Helper::getPickListValues($fieldModel->getName());
        $viewer = $this->getViewer($request);
        $viewer->assign('SELECTED_PICKLIST_FIELDMODEL',$fieldModel);
		$viewer->assign('SELECTED_MODULE_NAME',$sourceModule);
		$viewer->assign('MODULE',$moduleName);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedName);
        $viewer->assign('ROLES_LIST', Settings_Roles_Record_Model::getAll());
        $viewer->assign('SELECTED_PICKLISTFIELD_ALL_VALUES',$selectedFieldAllPickListValues);
        $viewer->view('PickListValueDetail.tpl',$qualifiedName);
    }


    public function getPickListValueByRole(Ottocrat_Request $request) {
        $sourceModule = $request->get('source_module');
        $pickFieldId = $request->get('pickListFieldId');
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldId);
        $moduleName = $request->getModule();
        $qualifiedName = $request->getModule(false);

        $userSelectedRoleId = $request->get('rolesSelected');

        $pickListValuesForRole = $fieldModel->getPicklistValues(array($userSelectedRoleId),'CONJUNCTION');
		$pickListValuesForRole = array_map('Ottocrat_Util_Helper::toSafeHTML', $pickListValuesForRole);
        $allPickListValues = Ottocrat_Util_Helper::getPickListValues($fieldModel->getName());
		$allPickListValues =  array_map('Ottocrat_Util_Helper::toSafeHTML', $allPickListValues);

        $viewer = $this->getViewer($request);
        $viewer->assign('SELECTED_PICKLIST_FIELDMODEL',$fieldModel);
		$viewer->assign('SELECTED_MODULE_NAME',$sourceModule);
		$viewer->assign('MODULE',$moduleName);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedName);
        $viewer->assign('ROLE_PICKLIST_VALUES',$pickListValuesForRole);
        $viewer->assign('ALL_PICKLIST_VALUES', $allPickListValues);
        $viewer->view('PickListValueByRole.tpl',$qualifiedName);
    }

	 /**
     * Function which will assign existing values to the roles
     * @param Ottocrat_Request $request
     */
    public function showAssignValueToRoleView(Ottocrat_Request $request) {
		$sourceModule = $request->get('source_module');
        $pickFieldId = $request->get('pickListFieldId');
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldId);

		$moduleName = $request->getModule();
        $qualifiedName = $request->getModule(false);

        $selectedFieldAllPickListValues = Ottocrat_Util_Helper::getPickListValues($fieldModel->getName());
		$selectedFieldAllPickListValues =  array_map('Ottocrat_Util_Helper::toSafeHTML', $selectedFieldAllPickListValues);
        $viewer = $this->getViewer($request);
        $viewer->assign('SELECTED_PICKLIST_FIELDMODEL',$fieldModel);
		$viewer->assign('SELECTED_MODULE_NAME',$sourceModule);
		$viewer->assign('MODULE',$moduleName);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedName);
        $viewer->assign('ROLES_LIST', Settings_Roles_Record_Model::getAll());
        $viewer->assign('SELECTED_PICKLISTFIELD_ALL_VALUES',$selectedFieldAllPickListValues);
        $viewer->view('AssignValueToRole.tpl',$qualifiedName);
	}
}