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
 * Base Model Class
 */
class Ottocrat_Base_Model {
	protected $valueMap;

	/**
	 * Constructor
	 * @param Array $values
	 */
	function  __construct($values=array()) {
		$this->valueMap = $values;
	}

	/**
	 * Function to get the value for a given key
	 * @param $key
	 * @return Value for the given key
	 */
	public function get($key){
		return $this->valueMap[$key];
	}

	/**
	 * Function to get the value if its safe to use for SQL Query (column).
	 * @param <String> $key
	 * @param <Boolean> $skipEmpty - Skip the check if string is empty
	 * @return Value for the given key
	 */
	public function getForSql($key, $skipEmtpy=true) {
		return Ottocrat_Util_Helper::validateStringForSql($this->get($key), $skipEmtpy);
	}

	/**
	 * Function to set the value for a given key
	 * @param $key
	 * @param $value
	 * @return Ottocrat_Base_Model
	 */
	public function set($key,$value){
		$this->valueMap[$key] = $value;
		return $this;
	}

	/**
	 * Function to set all the values for the Object
	 * @param Array (key-value mapping) $values
	 * @return Ottocrat_Base_Model
	 */
	public function setData($values){
		$this->valueMap = $values;
		return $this;
	}

	/**
	 * Function to get all the values of the Object
	 * @return Array (key-value mapping)
	 */
	public function getData(){
		return $this->valueMap;
	}

	/**
	 * Function to check if the key exists.
	 * @param String $key
	 */
	public function has($key) {
		return array_key_exists($key, $this->valueMap);
	}

	/**
	 * Function to check if the key is empty.
	 * @param type $key
	 */
	public function isEmpty($key) {
		return (!isset($this->valueMap[$key]) || empty($this->valueMap[$key]));
	}

}