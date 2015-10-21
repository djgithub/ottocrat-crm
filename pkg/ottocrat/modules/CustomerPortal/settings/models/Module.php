<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_CustomerPortal_Module_Model extends Settings_Ottocrat_Module_Model {

	var $name = 'CustomerPortal';

	/**
	 * Function to get Current portal user
	 * @return <Interger> userId
	 */
	public function getCurrentPortalUser() {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT prefvalue FROM ottocrat_customerportal_prefs WHERE prefkey = 'userid' AND tabid = 0", array());
		if ($db->num_rows($result)) {
			return $db->query_result($result, 0, 'prefvalue');
		}
		return false;
	}

	/**
	 * Function to get current default assignee from portal
	 * @return <Integer> userId
	 */
	public function getCurrentDefaultAssignee() {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT prefvalue FROM ottocrat_customerportal_prefs WHERE prefkey = 'defaultassignee' AND tabid = 0", array());
		if ($db->num_rows($result)) {
			return $db->query_result($result, 0, 'prefvalue');
		}
		return false;
	}

	/**
	 * Function to get list of portal modules
	 * @return <Array> list of portal modules <Ottocrat_Module_Model>
	 */
	public function getModulesList() {
		if (!$this->portalModules) {
			$db = PearDatabase::getInstance();

			$query = "SELECT ottocrat_customerportal_tabs.*, ottocrat_customerportal_prefs.prefvalue, ottocrat_tab.name FROM ottocrat_customerportal_tabs
					INNER JOIN ottocrat_customerportal_prefs ON ottocrat_customerportal_prefs.tabid = ottocrat_customerportal_tabs.tabid AND ottocrat_customerportal_prefs.prefkey='showrelatedinfo'
					INNER JOIN ottocrat_tab ON ottocrat_customerportal_tabs.tabid = ottocrat_tab.tabid AND ottocrat_tab.presence = 0 ORDER BY ottocrat_customerportal_tabs.sequence";

			$result = $db->pquery($query, array());
			$rows = $db->num_rows($result);

			for($i=0; $i<$rows; $i++) {
				$rowData = $db->query_result_rowdata($result, $i);
				$tabId = $rowData['tabid'];
				$moduleModel = Ottocrat_Module_Model::getInstance($tabId);
				foreach ($rowData as $key => $value) {
					$moduleModel->set($key, $value);
				}
				$portalModules[$tabId] = $moduleModel;
			}
			$this->portalModules = $portalModules;
		}
		return $this->portalModules;
	}

	/**
	 * Function to save the details of Portal modules
	 */
	public function save() {
		$db = PearDatabase::getInstance();
		$privileges = $this->get('privileges');
		$defaultAssignee = $this->get('defaultAssignee');
		$portalModulesInfo = $this->get('portalModulesInfo');
		
		//Update details of view all record option for every module from Customer portal
		$updateQuery = "UPDATE ottocrat_customerportal_prefs SET prefvalue = CASE ";
		foreach ($portalModulesInfo as $tabId => $moduleDetails) {
			$prefValue = $moduleDetails['prefValue'];
			$updateQuery .= " WHEN tabid = $tabId THEN $prefValue ";
		}
		$updateQuery .= " WHEN prefkey = ? THEN $privileges ";
		$updateQuery .= " WHEN prefkey = ? THEN $defaultAssignee ";
		$updateQuery .= " ELSE prefvalue END";

		$db->pquery($updateQuery, array('userid', 'defaultassignee'));

		//Update the sequence of every module in Customer portal
		$updateSequenceQuery = "UPDATE ottocrat_customerportal_tabs SET visible = CASE ";

		foreach ($portalModulesInfo as $tabId => $moduleDetails) {
			$visible = $moduleDetails['visible'];
			$updateSequenceQuery .= " WHEN tabid = $tabId THEN $visible ";
		}

		$updateSequenceQuery .= " END, sequence = CASE ";
		foreach ($portalModulesInfo as $tabId => $moduleDetails) {
			$sequence = $moduleDetails['sequence'];
			$updateSequenceQuery .= " WHEN tabid = $tabId THEN $sequence ";
		}
		$updateSequenceQuery .= "END";
		
		$db->pquery($updateSequenceQuery, array());
	}
}
