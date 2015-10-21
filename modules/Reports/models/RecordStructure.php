<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Ottocrat Edit View Record Structure Model
 */
class Reports_RecordStructure_Model extends Ottocrat_RecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure($moduleName) {
		if (!empty($this->structuredValues[$moduleName])) {
			return $this->structuredValues[$moduleName];
		}
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		if ($moduleName === 'Emails') {
			$restrictedTablesList = array('ottocrat_emaildetails', 'ottocrat_attachments');
			$moduleRecordStructure = array();
			$blockModelList = $moduleModel->getBlocks();
			foreach ($blockModelList as $blockLabel => $blockModel) {
				$fieldModelList = $blockModel->getFields();
				if (!empty($fieldModelList)) {
					$moduleRecordStructure[$blockLabel] = array();
					foreach ($fieldModelList as $fieldName => $fieldModel) {
						if (!in_array($fieldModel->get('table'), $restrictedTablesList) && $fieldModel->isViewable()) {
							$moduleRecordStructure[$blockLabel][$fieldName] = $fieldModel;
						}
					}
				}
			}
		} else if($moduleName === 'Calendar') { 
			$recordStructureInstance = Ottocrat_RecordStructure_Model::getInstanceForModule($moduleModel);
			$moduleRecordStructure = array();
			$calendarRecordStructure = $recordStructureInstance->getStructure();
			
			$eventsModel = Ottocrat_Module_Model::getInstance('Events');
			$recordStructureInstance = Ottocrat_RecordStructure_Model::getInstanceForModule($eventsModel);
			$eventRecordStructure = $recordStructureInstance->getStructure();
			
			$blockLabel = 'LBL_CUSTOM_INFORMATION';
			if($eventRecordStructure[$blockLabel]) {
				if($calendarRecordStructure[$blockLabel]) {
					$calendarRecordStructure[$blockLabel] = array_merge($calendarRecordStructure[$blockLabel], $eventRecordStructure[$blockLabel]);
				} else {
					$calendarRecordStructure[$blockLabel] = $eventRecordStructure[$blockLabel];
				}
			}
			$moduleRecordStructure = $calendarRecordStructure;
		} else {
			$recordStructureInstance = Ottocrat_RecordStructure_Model::getInstanceForModule($moduleModel);
			$moduleRecordStructure = $recordStructureInstance->getStructure();
		}
		$this->structuredValues[$moduleName] = $moduleRecordStructure;
		return $moduleRecordStructure;
	}

	/**
	 * Function returns the Primary Module Record Structure
	 * @return <Ottocrat_RecordStructure_Model>
	 */
	function getPrimaryModuleRecordStructure() {
		$primaryModule = $this->getRecord()->getPrimaryModule();
		$primaryModuleRecordStructure = $this->getStructure($primaryModule);
		return $primaryModuleRecordStructure;
	}

	/**
	 * Function returns the Secondary Modules Record Structure
	 * @return <Array of Ottocrat_RecordSructure_Models>
	 */
	function getSecondaryModuleRecordStructure() {
		$recordStructureInstances = array();

		$secondaryModule = $this->getRecord()->getSecondaryModules();
		if (!empty($secondaryModule)) {
			$moduleList = explode(':', $secondaryModule);

			foreach ($moduleList as $moduleName) {
				if (!empty($moduleName)) {
					$recordStructureInstances[$moduleName] = $this->getStructure($moduleName);
				}
			}
		}
		return $recordStructureInstances;
	}

}

?>
