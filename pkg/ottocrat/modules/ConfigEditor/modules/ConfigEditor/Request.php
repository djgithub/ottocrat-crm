<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
class ConfigEditor_Request {
	protected $valuemap;
	
	function __construct($values) {
		$this->valuemap = $values;
	}
	
	function get($key, $defvalue='') {
		$value = $defvalue;
		if (isset($this->valuemap[$key])) {
			$value = $this->valuemap[$key];
		}
		if (!empty($value)) {
			$value = vtlib_purify($value);
		}
		return $value;
	}
	
	function values() {
		return $this->valuemap;
	}
}
?>