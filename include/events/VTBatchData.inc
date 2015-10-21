<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

	class VTBatchData {

		/**
		 * Gets the array of data from the entity object as an array.
		 * @return An array representation VTEntityData
		 */
		function getData(){
			return $this->data;
		}
        

		/**
		 * Function gives an array of VTEntityData
		 * @param <Array of VTEntityData> $data
		 */
		function setData($data) {
			$this->data = $data;
		}

		/**
		 * Get the name of the module triggering the event
		 * @return The module name.
		 */
		function getModuleName(){
			return $this->moduleName;
		}

		/**
		 * Function sets the module name
		 * @param type $module
		 */
		function setModuleName($module) {
			$this->moduleName = $module;
		}
	}
