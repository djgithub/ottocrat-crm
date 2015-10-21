<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_UserRole_UIType extends Ottocrat_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/UserRole.tpl';
	}

	/**
	 * Function to get the display value in detail view
	 * @param <Integer> crmid of record
	 * @return <String>
	 */
	public function getEditViewDisplayValue($value) {
		if($value) {
			$userName = getRoleName($value);
			return $userName;
		}
	}

	/**
	 * Function to get display value
	 * @param <String> $value
	 * @param <Number> $recordId
	 * @return <String> display value
	 */
	public function getDisplayValue($value, $recordId) {
		$displayValue = $this->getEditViewDisplayValue($value);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			$roleRecordModel = new Settings_Roles_Record_Model();
			$roleRecordModel->set('roleid', $value);
			return '<a href="'. $roleRecordModel->getEditViewUrl() .'">'. textlength_check($displayValue) .'</a>';
		}
		return $displayValue;
	}
}