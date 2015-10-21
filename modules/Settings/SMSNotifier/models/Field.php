<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_SMSNotifier_Field_Model extends Ottocrat_Field_Model {

	/**
	 * Function to get field data type
	 * @return <String> data type
	 */
	public function getFieldDataType() {
		return $this->get('type');
	}

	/**
	 * Function to get instance of this model
	 * @param <Array> $rowData
	 * @return <Settings_SMSNotifier_Field_Model> field model
	 */
	public static function getInstanceByRow($rowData) {
		$fieldModel = new self();
		foreach ($rowData as $key => $value) {
			$fieldModel->set($key, $value);
		}
		return $fieldModel;
	}

}