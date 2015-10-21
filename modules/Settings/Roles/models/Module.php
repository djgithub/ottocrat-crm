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
class Settings_Roles_Module_Model extends Settings_Ottocrat_Module_Model {

	var $baseTable = 'ottocrat_role';
	var $baseIndex = 'roleid';
	var $listFields = array('roleid' => 'Role Id', 'rolename' => 'Name');
	var $name = 'Roles';

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module=Roles&parent=Settings&view=Index');
	}

	/**
	 * Function to get the url for Create view of the module
	 * @return <string> - url
	 */
	public function getCreateRecordUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module=Roles&parent=Settings&view=Index');
	}
}
