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
 * Assets Field Model Class
 */
class Assets_Field_Model extends Ottocrat_Field_Model {

	/**
	 * Function returns special validator for fields
	 * @return <Array>
	 */
	function getValidator() {
		$validator = array();
		$fieldName = $this->getName();

		switch($fieldName) {
            case 'datesold' : $funcName = array('name'=>'lessThanOrEqualToToday'); 
                              array_push($validator, $funcName); 
                              break; 
			default : $validator = parent::getValidator();
						break;
		}
		return $validator;
	}
}
