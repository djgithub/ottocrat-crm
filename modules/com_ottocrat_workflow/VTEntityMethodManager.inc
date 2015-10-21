<?php
/*+*******************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ******************************************************************************/
require_once("include/events/SqlResultIterator.inc");
class VTEntityMethodManager{
	
	function __construct($adb){
		$this->adb = $adb;
	}
	
	
	function addEntityMethod($moduleName, $methodName, $functionPath, $functionName){
		$adb = $this->adb;
		$id = $adb->getUniqueId("com_ottocrat_workflowtasks_entitymethod");
		$adb->pquery("insert into com_ottocrat_workflowtasks_entitymethod (workflowtasks_entitymethod_id, module_name, function_path, function_name, method_name) values (?,?,?,?,?)", array($id, $moduleName, $functionPath, $functionName, $methodName));
	}
	
	
	
	function executeMethod($entityData, $methodName){
		$adb = $this->adb;
		$moduleName = $entityData->getModuleName();
		$result = $adb->pquery("select function_path, function_name from com_ottocrat_workflowtasks_entitymethod where module_name=? and method_name=?", array($moduleName, $methodName));
		if($adb->num_rows($result)!=0){
			$data = $adb->raw_query_result_rowdata($result, 0);
			$functionPath = $data['function_path'];
			$functionName = $data['function_name'];
			require_once($functionPath);
			$functionName($entityData);
		}
	}
	
	function methodsForModule($moduleName){
		$adb = $this->adb;
		$result = $adb->pquery("select method_name from com_ottocrat_workflowtasks_entitymethod where module_name=?", array($moduleName));
		$it = new SqlResultIterator($adb, $result);
		$methodNames = array();
		foreach($it as $row){
			$methodNames[] = $row->method_name;
		}
		return $methodNames;
	}
	/*
	private function methodExists($object, $methodName){
		$className = get_class($object);
		$class = new ReflectionClass($className);
		$methods = $class->getMethods();
		foreach($methods as $method){
			if($method->getName()==$methodName){
				return true;
			}
		}
		return false;
	}*/

	/**
	 * Function to remove workflowtasks entity method 
	 * @param <String> Module Name
	 * @param <String> Entity Method Name.
	 */
	function removeEntityMethod($moduleName, $methodName){
		$adb = $this->adb;
		$adb->pquery("DELETE FROM com_ottocrat_workflowtasks_entitymethod WHERE module_name = ? and method_name= ?", array($moduleName, $methodName));
	}
}
?>