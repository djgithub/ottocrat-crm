<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class ModTracker_Relation_Model extends Ottocrat_Record_Model {

	function setParent($parent) {
		$this->parent = $parent;
	}

	function getParent() {
		return $this->parent;
	}

	function getLinkedRecord() {
        $db = PearDatabase::getInstance();
        
		$targetId = $this->get('targetid');
		$targetModule = $this->get('targetmodule');
        
        $query = 'SELECT * FROM ottocrat_crmentity WHERE crmid = ?';
		$params = array($targetId);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);
		$moduleModels = array();
		if($noOfRows) {
			if(!array_key_exists($targetModule, $moduleModels)) {
				$moduleModel = Ottocrat_Module_Model::getInstance($targetModule);
			}
			$row = $db->query_result_rowdata($result, 0);
			$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Record', $targetModule);
			$recordInstance = new $modelClassName();
			$recordInstance->setData($row)->setModuleFromInstance($moduleModel);
			$recordInstance->set('id', $row['crmid']);
			return $recordInstance;
		}
		return false;
	}
}