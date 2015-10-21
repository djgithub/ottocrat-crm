<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~modules/PickList/DependentPickListUtils.php');

class Settings_PickListDependency_Module_Model extends Settings_Ottocrat_Module_Model {

	var $baseTable = 'ottocrat_picklist_dependency';
	var $baseIndex = 'id';
	var $name = 'PickListDependency';

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return Ottocrat_Request:: encryptLink( 'index.php?module=PickListDependency&parent=Settings&view=List');
	}

	/**
	 * Function to get the url for Adding Dependency
	 * @return <string> - url
	 */
	public function getCreateRecordUrl() {
		return "javascript:Settings_PickListDependency_Js.triggerAdd(event)";
	}
    
    public function isPagingSupported() {
        return false;
    }

	public static function getAvailablePicklists($module) {
		return Ottocrat_DependencyPicklist::getAvailablePicklists($module);
	}
	
	public static function getPicklistSupportedModules() {
		$adb = PearDatabase::getInstance();

		$query = "SELECT distinct ottocrat_field.tabid, ottocrat_tab.tablabel, ottocrat_tab.name as tabname FROM ottocrat_field
						INNER JOIN ottocrat_tab ON ottocrat_tab.tabid = ottocrat_field.tabid
						WHERE uitype IN ('15','16')
						AND ottocrat_field.tabid != 29
						AND ottocrat_field.displaytype = 1
						AND ottocrat_field.presence in ('0','2')
						AND ottocrat_field.block != 'NULL'
					GROUP BY ottocrat_field.tabid HAVING count(*) > 1";
		// END
		$result = $adb->pquery($query, array());
		while($row = $adb->fetch_array($result)) {
			$modules[$row['tablabel']] = $row['tabname'];
		}
		ksort($modules);
		
        $modulesModelsList = array();
        foreach($modules as $moduleLabel => $moduleName) {
            $instance = new Ottocrat_Module_Model();
            $instance->name = $moduleName;
            $instance->label = $moduleLabel;
            $modulesModelsList[] = $instance;
        }
        return $modulesModelsList;
    }
}
