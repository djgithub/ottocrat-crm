<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_Cache_Connector_Memory {
	function set($key, $value) {
		$this->$key = $value;
	}
	function get($key) {
		return isset($this->$key)? $this->$key : false;
	}
    
    function flush(){
        return true;
    }
}