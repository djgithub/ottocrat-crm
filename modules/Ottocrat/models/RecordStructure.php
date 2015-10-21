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
 * Ottocrat Record Structure Model
 */
class Ottocrat_RecordStructure_Model extends Ottocrat_Base_Model {

	protected $record = false;
	protected $module = false;
	protected $structuredValues = false;

	const RECORD_STRUCTURE_MODE_DEFAULT = '';
	const RECORD_STRUCTURE_MODE_DETAIL = 'Detail';
	const RECORD_STRUCTURE_MODE_EDIT = 'Edit';
	const RECORD_STRUCTURE_MODE_QUICKCREATE = 'QuickCreate';
	const RECORD_STRUCTURE_MODE_MASSEDIT = 'MassEdit';
	const RECORD_STRUCTURE_MODE_SUMMARY = 'Summary';

	/**
	 * Function to set the record Model
	 * @param <type> $record - record instance
	 * @return Ottocrat_RecordStructure_Model
	 */
	public function setRecord($record) {
		$this->record = $record;
		return $this;
	}

	/**
	 * Function to get the record
	 * @return <Ottocrat_Record_Model>
	 */
	public function getRecord() {
		return $this->record;
	}

	public function getRecordName() {
		return $this->record->getName();
	}

	/**
	 * Function to get the module
	 * @return <Ottocrat_Module_Model>
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the module
	 * @param <type> $module - module model
	 * @return Ottocrat_RecordStructure_Model
	 */
	public function setModule($module) {
		$this->module = $module;
		return $this;
	}

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if(!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach($blockModelList as $blockLabel=>$blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty ($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach($fieldModelList as $fieldName=>$fieldModel) {
					if($fieldModel->isViewable()) {
						if($recordExists) {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}

	/**
	 * Function to retieve the instance from record model
	 * @param <Ottocrat_Record_Model> $recordModel - record instance
	 * @return Ottocrat_RecordStructure_Model
	 */
	public static function getInstanceFromRecordModel($recordModel, $mode=self::RECORD_STRUCTURE_MODE_DEFAULT) {
		$moduleModel = $recordModel->getModule();
		$className = Ottocrat_Loader::getComponentClassName('Model', $mode.'RecordStructure', $moduleModel->getName(true));

		$instance = new $className();
		$instance->setModule($moduleModel)->setRecord($recordModel);
		return $instance;
	}

	/**
	 * Function to retieve the instance from module model
	 * @param <Ottocrat_Module_Model> $moduleModel - module instance
	 * @return Ottocrat_RecordStructure_Model
	 */
	public static function getInstanceForModule($moduleModel, $mode=self::RECORD_STRUCTURE_MODE_DEFAULT) {
		$className = Ottocrat_Loader::getComponentClassName('Model', $mode.'RecordStructure', $moduleModel->get('name'));
		$instance = new $className();
		$instance->setModule($moduleModel);
		return $instance;
	}
}