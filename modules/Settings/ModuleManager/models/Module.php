<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_ModuleManager_Module_Model extends Ottocrat_Module_Model {

    public static function getNonVisibleModulesList() {
        return array('ModTracker','Webmails','Users','Mobile','Integration','WSAPP',
                     'ConfigEditor','FieldFormulas','OttocratBackup','CronTasks','Import' ,'Tooltip',
                    'CustomerPortal', 'Home', 'ExtensionStore');
    }


    /**
     * Function to get the url of new module import
     */
    public static function getNewModuleImportUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module=ExtensionStore&parent=Settings&view=ExtensionImport');
    }
	
    
    /**
     * Function to get the url of Extension store
     */
    public static function getExtensionStoreUrl() {
        return Ottocrat_Request:: encryptLink('index.php?module=ExtensionStore&parent=Settings&view=ExtensionImport&mode=index');
    }

    /**
     * Function to get the url of new module import 
     */
    public static function getUserModuleFileImportUrl() {
            return Ottocrat_Request:: encryptLink('index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1');
    }
	
	/**
     * Function to disable a module 
     * @param type $moduleName - name of the module
     */
    public function disableModule($moduleName) {
		//Handling events after disable module
		vtlib_toggleModuleAccess($moduleName, false);
    }

    /**
     * Function to enable the module
     * @param type $moduleName -- name of the module
     */
    public function enableModule($moduleName) {
		//Handling events after enable module
		vtlib_toggleModuleAccess($moduleName, true);
    }


	/**
	 * Static Function to get the instance of Ottocrat Module Model for all the modules
	 * @return <Array> - List of Ottocrat Module Model or sub class instances
	 */
	public static function getAll() {
		 return parent::getAll(array(0,1), self::getNonVisibleModulesList());
	}

    /**
     * Function which will get count of modules
     * @param <Boolean> $onlyActive - if true get count of only active modules else all the modules
     * @return <integer> number of modules
     */
    public static function getModulesCount($onlyActive = false) {
        $db = PearDatabase::getInstance();

        $query = 'SELECT * FROM ottocrat_tab';
		$params = array();
		if($onlyActive) {
            $presence = array(0);
            $nonVisibleModules = self::getNonVisibleModulesList();
			$query .= ' WHERE presence IN ('. generateQuestionMarks($presence) .')';
            $query .= ' AND name NOT IN ('.generateQuestionMarks($nonVisibleModules).')';
			array_push($params, $presence,$nonVisibleModules);
		}
        $result = $db->pquery($query, $params);
		return $db->num_rows($result);
    }

	/**
	 * Function that returns all those modules that support Module Sequence Numbering
	 * @global PearDatabase $db - database connector
	 * @return <Array of Ottocrat_Module_Model>
	 */
	public static function getModulesSupportingSequenceNumbering() {
		$db = PearDatabase::getInstance();
		$sql="SELECT tabid, name FROM ottocrat_tab WHERE isentitytype = 1 AND presence = 0 AND tabid IN
			(SELECT DISTINCT tabid FROM ottocrat_field WHERE uitype = '4')";
		$result = $db->pquery($sql, array());

		$moduleModels = array();
		for($i=0; $i<$db->num_rows($result); ++$i) {
			$row =  $db->query_result_rowdata($result, $i);
			$moduleModels[$row['name']] = self::getInstanceFromArray($row);
		}
		return $moduleModels;
	}

	/**
	 * Function to get restricted modules list
	 * @return <Array> List module names
	 */
	public static function getActionsRestrictedModulesList() {
		return array('Home', 'Emails', 'Webmails');
	}
}
