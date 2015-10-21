<?php

/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Mass Edit Record Structure Model
 */
class Products_MassEditRecordStructure_Model extends Ottocrat_MassEditRecordStructure_Model {
	
	/*
	 * Function that return Field Restricted are not
	 *	@params Field Model
	 *  @returns boolean true or false
	 */
	public function isFieldRestricted($fieldModel) {
		$restricted = parent::isFieldRestricted($fieldModel);
		if($restricted && ($fieldModel->getFieldDataType() == 'productTax' || $fieldModel->getName() == 'unit_price')){
			return false;
		} else {
			return $restricted;
		}
	}
}
?>
