<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Settings Module Model Class
 */
class Settings_Profiles_Module_Model extends Settings_Ottocrat_Module_Model {

	var $baseTable = 'ottocrat_profile';
	var $baseIndex = 'profileid';
	var $listFields = array('profilename' => 'Name', 'description' => 'Description');

	const GLOBAL_ACTION_VIEW = 1;
	const GLOBAL_ACTION_EDIT = 2;
	const GLOBAL_ACTION_DEFAULT_VALUE = 1;

	const IS_PERMITTED_VALUE = 0;
	const NOT_PERMITTED_VALUE = 1;

	const FIELD_ACTIVE = 0;
	const FIELD_INACTIVE = 1;
	
	const FIELD_READWRITE = 0;
	const FIELD_READONLY = 1;
	
	var $name = 'Profiles';

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module=Profiles&parent=Settings&view=List');
	}

	/**
	 * Function to get the url for create view of the module
	 * @return <string> - url
	 */
	public function getCreateRecordUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module=Profiles&parent=Settings&view=Edit');
	}

	/**
	 * Function to get non visible modules list
	 * @return <Array> list of modules
	 */
	public static function getNonVisibleModulesList() {
        return array('ModTracker', 'Portal', 'Webmails', 'Users', 'Mobile', 'Integration', 'WSAPP', 'ConfigEditor',
					'FieldFormulas', 'OttocratBackup', 'CronTasks', 'Import', 'Tooltip', 'CustomerPortal', 'Home');
    }
}
