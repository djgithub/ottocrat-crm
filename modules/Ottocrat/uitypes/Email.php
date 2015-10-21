<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_Email_UIType extends Ottocrat_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Email.tpl';
	}

	public function getDisplayValue($value, $recordId) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$internalMailer = $currentUser->get('internal_mailer');
		if($value){
            $moduleName = $this->get('field')->get('block')->module->name;
            $fieldName = $this->get('field')->get('name');
			if ($internalMailer == 1) {
				/**
                 *  We should not add "emailField" class to user name field.
                 *  If we do so, for sending mail from list view is taking that value as a TO field. 
                 */
                if($moduleName == "Users" && $fieldName == "user_name"){
                    $value = "<a class='cursorPointer' onclick=\"Ottocrat_Helper_Js.getInternalMailer($recordId," .
                    		"'$fieldName','$moduleName');\">" . textlength_check($value) . "</a>";
                }else{
                	$value = "<a class='emailField cursorPointer' onclick=\"Ottocrat_Helper_Js.getInternalMailer($recordId," .
                    		"'$fieldName','$moduleName');\">" . textlength_check($value) . "</a>";
                }
			} else {
                if($moduleName == "Users" && $fieldName == "user_name"){
                    $value = "<a class='cursorPointer'  href='mailto:" . $value . "'>" . textlength_check($value) . "</a>";
                }else{
                    $value = "<a class='emailField cursorPointer'  href='mailto:" . $value . "'>" . textlength_check($value) . "</a>";
                }
			}
		}
		return $value;
	}
}