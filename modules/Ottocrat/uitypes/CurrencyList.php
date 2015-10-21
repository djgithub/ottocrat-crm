<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
class Ottocrat_CurrencyList_UIType extends Ottocrat_Base_UIType {
	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/CurrencyList.tpl';
	}

	public function getDisplayValue($value) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT currency_name FROM ottocrat_currency_info WHERE currency_status = ? AND id = ?',
					array('Active', $value));
		if($db->num_rows($result)) {
			return $db->query_result($result, 0, 'currency_name');
		}
		return $value;
	}

	public function getCurrenyListReferenceFieldName() {
		return 'currency_name';
	}
}
?>
