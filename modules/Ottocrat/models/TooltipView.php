<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

vimport('~~/include/Webservices/Query.php');

class Ottocrat_TooltipView_Model extends Ottocrat_DetailRecordStructure_Model {

	protected $fields = false;

	/**
	 * Function to set the module instance
	 * @param <Ottocrat_Module_Model> $moduleInstance - module model
	 * @return Ottocrat_DetailView_Model>
	 */
	public function setModule($moduleInstance) {
		$this->module = $moduleInstance;
		$this->fields = $this->module->getSummaryViewFieldsList();
		if (empty($this->fields)) {
			$this->fields = $this->module->getMandatoryFieldModels();
		}
		return $this;
	}
	
	/**
	 * Function to get list of tooltip enabled field model.
	 * @return <Ottocrat_Field_Model>
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Function to load record
	 * @param <Number> $recordId
	 * @return <Ottocrat_Record_Model>
	 */
	protected function loadRecord($recordId) {
		$moduleName = $this->module->getName();
		
		// Preparation to pull required tool-tip field values.
		$referenceFields = array(); $fieldNames = array();
		foreach ($this->fields as $fieldModel) {
			$fieldType = $fieldModel->getFieldDataType();
			$fieldName = $fieldModel->get('name');
			
			$fieldNames[] = $fieldName;
			if ($fieldType == 'reference' || $fieldType == 'owner') {
				$referenceFields[] = $fieldName;
			}
		}
		$wsid = vtws_getWebserviceEntityId($moduleName, $recordId);
		$q = sprintf("SELECT %s FROM %s WHERE id='%s' LIMIT 1;", implode(',', $fieldNames), $moduleName, $wsid);
		
		// Retrieves only required fields of the record with permission check.
		try {
			$data = array_shift(vtws_query($q, Users_Record_Model::getCurrentUserModel()));

			if ($data) {
				// De-transform the webservice ID to CRM ID.
				foreach ($data as $key => $value) {
					if (in_array($key, $referenceFields)) {
						$value = array_pop(explode('x', $value));
					}
					$data[$key] = $value;
				}
			}
			
			$this->record = Ottocrat_Record_Model::getCleanInstance($moduleName);
			$this->record->setData($data);
			
		} catch(WebServiceException $wex) {
			// Error retrieving information !
		}
		return $this;
	}
	
	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if (!$this->structuredValues) {
			$tooltipFieldsList = $this->fields;
			$recordModel = $this->getRecord();
			$this->structuredValues = array('TOOLTIP_FIELDS' => array());
			if ($tooltipFieldsList) {
				foreach ($tooltipFieldsList as $fieldModel) {
					$fieldName = $fieldModel->get('name');
					if($fieldModel->isViewableInDetailView()) {
						$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						$this->structuredValues['TOOLTIP_FIELDS'][$fieldName] = $fieldModel;
					}
				}
			}
		}
		
		return $this->structuredValues;
	}
	
	/**
	 * Function to get the instance
	 * @param <String> $moduleName - module name
	 * @param <String> $recordId - record id
	 * @return <Ottocrat_DetailView_Model>
	 */
	public static function getInstance($moduleName,$recordId) {
		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'TooltipView', $moduleName);
		$instance = new $modelClassName();

		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		
		return $instance->setModule($moduleModel)->loadRecord($recordId);
	}
}
