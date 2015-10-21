<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Function to get or set a global variable
 * @param type $key
 * @param type $value
 * @return value of the given key
 */
function vglobal($key, $value=null) {
	if($value !== null) {
		$GLOBALS[$key] = $value;
	}
	return $GLOBALS[$key];
}