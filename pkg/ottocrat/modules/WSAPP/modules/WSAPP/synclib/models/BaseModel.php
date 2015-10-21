<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
class WSAPP_BaseModel {

	protected $data;

	function  __construct($values = array()) {
		$this->data = $values;
	}

	public function getData(){
		return $this->data;
	}

	public function setData($values){
		$this->data = $values;
		return $this;
	}

	public function set($key,$value){
		$this->data[$key] = $value;
		return $this;
	}

	public function get($key){
		return $this->data[$key];
	}

	public function has($key) {
		return array_key_exists($key, $this->data);
	}

	/**
	 * Function to check if the key is empty.
	 * @param type $key
	 */
	public function isEmpty($key) {
		return (!isset($this->data[$key]) || empty($this->data[$key]));
	}

}

?>
