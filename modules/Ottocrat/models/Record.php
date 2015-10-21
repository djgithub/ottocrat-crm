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
 * Ottocrat Entity Record Model Class
 */
class Ottocrat_Record_Model extends Ottocrat_Base_Model {

	protected $module = false;

	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId() {
		return $this->get('id');
	}

	/**
	 * Function to set the id of the record
	 * @param <type> $value - id value
	 * @return <Object> - current instance
	 */
	public function setId($value) {
		return $this->set('id',$value);
	}

	/**
	 * Fuction to get the Name of the record
	 * @return <String> - Entity Name of the record
	 */
	public function getName() {
		$displayName = $this->get('label');
		if(empty($displayName)) {
			$displayName = $this->getDisplayName();
		}
		return Ottocrat_Util_Helper::toSafeHTML(decode_html($displayName));
	}

	/**
	 * Function to get the Module to which the record belongs
	 * @return Ottocrat_Module_Model
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the Module to which the record belongs
	 * @param <String> $moduleName
	 * @return Ottocrat_Record_Model or Module Specific Record Model instance
	 */
	public function setModule($moduleName) {
		$this->module = Ottocrat_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to set the Module to which the record belongs from the Module model instance
	 * @param <Ottocrat_Module_Model> $module
	 * @return Ottocrat_Record_Model or Module Specific Record Model instance
	 */
	public function setModuleFromInstance($module) {
		$this->module = $module;
		return $this;
	}

	/**
	 * Function to get the entity instance of the recrod
	 * @return CRMEntity object
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * Function to set the entity instance of the record
	 * @param CRMEntity $entity
	 * @return Ottocrat_Record_Model instance
	 */
	public function setEntity($entity) {
		$this->entity = $entity;
		return $this;
	}

	/**
	 * Function to get raw data
	 * @return <Array>
	 */
	public function getRawData() {
		return $this->rawData;
	}

	/**
	 * Function to set raw data
	 * @param <Array> $data
	 * @return Ottocrat_Record_Model instance
	 */
	public function setRawData($data) {
		$this->rawData = $data;
		return $this;
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl() {
		$module = $this->getModule();
//		return 'index.php?module='.$this->getModuleName().'&view='.$module->getDetailViewName().'&record='.$this->getId();
		$module_url='index.php?module='.$this->getModuleName().'&view='.$module->getDetailViewName().'&record='.$this->getId();
		return Ottocrat_Request::encryptLink($module_url);
	}

	public  function  getDetailUrlWNoChange(){ //dont add function for encrypt url here
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getDetailViewName().'&record='.$this->getId();
	}
	/**
	 * Function to get the complete Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getFullDetailViewUrl() {
		$module = $this->getModule();
//		return 'index.php?module='.$this->getModuleName().'&view='.$module->getDetailViewName().'&record='.$this->getId().'&mode=showDetailViewByMode&requestMode=full';
$module_url='index.php?module='.$this->getModuleName().'&view='.$module->getDetailViewName().'&record='.$this->getId().'&mode=showDetailViewByMode&requestMode=full';
		return Ottocrat_Request::encryptLink($module_url);
	}

	/**
	 * Function to get the Edit View url for the record
	 * @return <String> - Record Edit View Url
	 */
	public function getEditViewUrl() {
		$module = $this->getModule();
//		return 'index.php?module='.$this->getModuleName().'&view='.$module->getEditViewName().'&record='.$this->getId();
		$module_url='index.php?module='.$this->getModuleName().'&view='.$module->getEditViewName().'&record='.$this->getId();
		return Ottocrat_Request::encryptLink($module_url);
	}

	/**
	 * Function to get the Update View url for the record
	 * @return <String> - Record Upadte view Url
	 */
	public function getUpdatesUrl() {
		//return $this->getDetailViewUrl()."&mode=showRecentActivities&page=1&tab_label=LBL_UPDATES";

		$module_url=$this->getDetailUrlWNoChange()."&mode=showRecentActivities&page=1&tab_label=LBL_UPDATES";
		return Ottocrat_Request::encryptLink($module_url);

	}

	/**
	 * Function to get the Delete Action url for the record
	 * @return <String> - Record Delete Action Url
	 */
	public function getDeleteUrl() {
		$module = $this->getModule();
		//return 'index.php?module='.$this->getModuleName().'&action='.$module->getDeleteActionName().'&record=.$this->getId();
		$module_url='index.php?module='.$this->getModuleName().'&action='.$module->getDeleteActionName().'&record='.$this->getId();
		return Ottocrat_Request::encryptLink($module_url);
	}

	/**
	 * Function to get the name of the module to which the record belongs
	 * @return <String> - Record Module Name
	 */
	public function getModuleName() {
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		return Ottocrat_Util_Helper::getLabel($this->getId());
	}

	/**
	 * Function to retieve display value for a field
	 * @param <String> $fieldName - field name for which values need to get
	 * @return <String>
	 */
	public function getDisplayValue($fieldName,$recordId = false) {
		if(empty($recordId)) {
			$recordId = $this->getId();
		}
		$fieldModel = $this->getModule()->getField($fieldName);
       
        // For showing the "Date Sent" and "Time Sent" in email related list in user time zone
        if($fieldName == "time_start" && $this->getModule()->getName() == "Emails"){
            $date = new DateTime();
            $dateTime = new DateTimeField($date->format('Y-m-d').' '.$this->get($fieldName));
            $value = $dateTime->getDisplayTime();
            $this->set($fieldName, $value);
            return $value;
        }else if($fieldName == "date_start" && $this->getModule()->getName() == "Emails"){
            $dateTime = new DateTimeField($this->get($fieldName).' '.$this->get('time_start'));
            $value = $dateTime->getDisplayDate();
            $this->set($fieldName, $value);
            return $value;
        }
        // End
        
		if($fieldModel) {
			return $fieldModel->getDisplayValue($this->get($fieldName), $recordId, $this);
		}
		return false;
	}

	/**
	 * Function returns the Ottocrat_Field_Model
	 * @param <String> $fieldName - field name
	 * @return <Ottocrat_Field_Model>
	 */
	public function getField($fieldName) {
		return $this->getModule()->getField($fieldName);
	}

	/**
	 * Function returns all the field values in user format
	 * @return <Array>
	 */
	public function getDisplayableValues() {
		$displayableValues = array();
		$data = $this->getData();
		foreach($data as $fieldName=>$value) {
			$fieldValue = $this->getDisplayValue($fieldName);
			$displayableValues[$fieldName] = ($fieldValue) ? $fieldValue : $value;
		}
		return $displayableValues;
	}

	/**
	 * Function to save the current Record Model
	 */
	public function save() {
		$this->getModule()->saveRecord($this);
	}

	/**
	 * Function to delete the current Record Model
	 */
	public function delete() {
		$this->getModule()->deleteRecord($this);
	}

	/**
	 * Static Function to get the instance of a clean Ottocrat Record Model for the given module name
	 * @param <String> $moduleName
	 * @return Ottocrat_Record_Model or Module Specific Record Model instance
	 */
	public static function getCleanInstance($moduleName) {
		//TODO: Handle permissions
		$focus = CRMEntity::getInstance($moduleName);
		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		return $instance->setData($focus->column_fields)->setModule($moduleName)->setEntity($focus);
	}

	/**
	 * Static Function to get the instance of the Ottocrat Record Model given the recordid and the module name
	 * @param <Number> $recordId
	 * @param <String> $moduleName
	 * @return Ottocrat_Record_Model or Module Specific Record Model instance
	 */
	public static function getInstanceById($recordId, $module=null) {
		//TODO: Handle permissions
		if(is_object($module) && is_a($module, 'Ottocrat_Module_Model')) {
			$moduleName = $module->get('name');
		} elseif (is_string($module)) {
			$module = Ottocrat_Module_Model::getInstance($module);
			$moduleName = $module->get('name');
		} elseif(empty($module)) {
			$moduleName = getSalesEntityType($recordId);
			$module = Ottocrat_Module_Model::getInstance($moduleName);
		}

		$focus = CRMEntity::getInstance($moduleName);
		$focus->id = $recordId;
		$focus->retrieve_entity_info($recordId, $moduleName);
		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		return $instance->setData($focus->column_fields)->set('id',$recordId)->setModuleFromInstance($module)->setEntity($focus);
	}

	/**
	 * Static Function to get the list of records matching the search key
	 * @param <String> $searchKey
	 * @return <Array> - List of Ottocrat_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $module=false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT label, crmid, setype, createdtime FROM ottocrat_crmentity WHERE label LIKE ? AND ottocrat_crmentity.deleted = 0';
		$params = array("%$searchKey%");

		if($module !== false) {
			$query .= ' AND setype = ?';
			$params[] = $module;
		}
		//Remove the ordering for now to improve the speed
		//$query .= ' ORDER BY createdtime DESC';

		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$moduleModels = $matchingRecords = $leadIdsList = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);

		for($i=0, $recordsCount = 0; $i<$noOfRows && $recordsCount<100; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
				$row['id'] = $row['crmid'];
				$moduleName = $row['setype'];
				if(!array_key_exists($moduleName, $moduleModels)) {
					$moduleModels[$moduleName] = Ottocrat_Module_Model::getInstance($moduleName);
				}
				$moduleModel = $moduleModels[$moduleName];
				$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Record', $moduleName);
				$recordInstance = new $modelClassName();
				$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
				$recordsCount++;
			}
		}
		return $matchingRecords;
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isEditable() {
		return Users_Privileges_Model::isPermitted($this->getModuleName(), 'EditView', $this->getId());
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isDeletable() {
		return Users_Privileges_Model::isPermitted($this->getModuleName(), 'Delete', $this->getId());
	}

	/**
	 * Funtion to get Duplicate Record Url
	 * @return <String>
	 */
	public function getDuplicateRecordUrl() {
		$module = $this->getModule();
		//return 'index.php?module='.$this->getModuleName().'&view='.$module->getEditViewName().'&record='
		//.$this->getId().'&isDuplicate=true';
		$module_url='index.php?module='.$this->getModuleName().'&view='.$module->getEditViewName().'&record='.$this->getId().'&isDuplicate=true';
		return Ottocrat_Request::encryptLink($module_url);
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param <String> $value
	 * @return <String>
	 */
	public function getRelatedListDisplayValue($fieldName) {
		$fieldModel = $this->getModule()->getField($fieldName);
		return $fieldModel->getRelatedListDisplayValue($this->get($fieldName));
	}

	/**
	 * Function to delete corresponding image
	 * @param <type> $imageId
	 */
	public function deleteImage($imageId) {
		$db = PearDatabase::getInstance();

		$checkResult = $db->pquery('SELECT crmid FROM ottocrat_seattachmentsrel WHERE attachmentsid = ?', array($imageId));
		$crmId = $db->query_result($checkResult, 0, 'crmid');

		if ($this->getId() === $crmId) {
			$db->pquery('DELETE FROM ottocrat_attachments WHERE attachmentsid = ?', array($imageId));
			$db->pquery('DELETE FROM ottocrat_seattachmentsrel WHERE attachmentsid = ?', array($imageId));
			return true;
		}
		return false;
	}

	/**
	 * Function to get Descrption value for this record
	 * @return <String> Descrption
	 */
	public function getDescriptionValue() {
		$description = $this->get('description');
		if(empty($description)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT description FROM ottocrat_crmentity WHERE crmid = ?", array($this->getId()));
			$description =  $db->query_result($result, 0, "description");
		}
		return $description;
	}

	/**
	 * Function to transfer related records of parent records to this record
	 * @param <Array> $recordIds
	 * @return <Boolean> true/false
	 */
	public function transferRelationInfoOfRecords($recordIds = array()) {
		if ($recordIds) {
			$moduleName = $this->getModuleName();
			$focus = CRMEntity::getInstance($moduleName);
			if (method_exists($focus, 'transferRelatedRecords')) {
				$focus->transferRelatedRecords($moduleName, $recordIds, $this->getId());
			}
		}
		return true;
	}
}