<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_Boolean_UIType extends Ottocrat_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Boolean.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
		if($value == 1 || $value == '1' || strtolower($value) == 'on') {
			return Ottocrat_Language_Handler::getTranslatedString('LBL_YES', $this->get('field')->getModuleName());
		}
		return Ottocrat_Language_Handler::getTranslatedString('LBL_NO', $this->get('field')->getModuleName());
	}
    
     public function getListSearchTemplateName() {
        return 'uitypes/BooleanFieldSearchView.tpl';
    }

}