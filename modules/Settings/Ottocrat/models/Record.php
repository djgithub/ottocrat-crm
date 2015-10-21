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
 * Roles Record Model Class
 */
abstract class Settings_Ottocrat_Record_Model extends Ottocrat_Base_Model {

	abstract function getId();
	abstract function getName();

	public function getRecordLinks() {

		$links = array();
		$recordLinks = array();
		foreach ($recordLinks as $recordLink) {
			$links[] = Ottocrat_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}
	
	public function getDisplayValue($key) {
		return $this->get($key);
	}
}
