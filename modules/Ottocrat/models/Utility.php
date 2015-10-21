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
 * Ottocrat Action Model Class
 */
class Ottocrat_Utility_Model extends Ottocrat_Action_Model {

	public function isUtilityTool() {
		return true;
	}

	public function isModuleEnabled($module) {
		$db = PearDatabase::getInstance();
		if(!$module->isEntityModule()) {
            if(!$module->isUtilityActionEnabled())
                return false;
		}
		$tabId = $module->getId();
		$sql = 'SELECT 1 FROM ottocrat_profile2utility WHERE tabid = ? AND activityid = ? LIMIT 1';
		$params = array($tabId, $this->getId());
		$result = $db->pquery($sql, $params);
		if($result && $db->num_rows($result) > 0) {
			return true;
		}
		return false;
	}

}