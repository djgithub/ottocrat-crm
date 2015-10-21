<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Leads_Record_Model extends Ottocrat_Record_Model {

	/**
	 * Function returns the url for converting lead
	 */
	function getConvertLeadUrl() {
		return ('index.php?module='.$this->getModuleName().'&view=ConvertLead&record='.$this->getId());//same url
	}

	/**
	 * Static Function to get the list of records matching the search key
	 * @param <String> $searchKey
	 * @return <Array> - List of Ottocrat_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $module=false) {
		$db = PearDatabase::getInstance();

		//$deletedCondition = $this->getModule()->getDeletedRecordCondition();
		$deletedCondition =Ottocrat_Record_Model:: getModule()->getDeletedRecordCondition();
		$query = 'SELECT * FROM ottocrat_crmentity
                    INNER JOIN ottocrat_leaddetails ON ottocrat_leaddetails.leadid = ottocrat_crmentity.crmid
                    WHERE label LIKE ? AND '.$deletedCondition;
		$params = array("%$searchKey%");
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$moduleModels = array();
		$matchingRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$moduleName = $row['setype'];
			if(!array_key_exists($moduleName, $moduleModels)) {
				$moduleModels[$moduleName] = Ottocrat_Module_Model::getInstance($moduleName);
			}
			$moduleModel = $moduleModels[$moduleName];
			$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Record', $moduleName);
			$recordInstance = new $modelClassName();
			$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
		}
		return $matchingRecords;
	}

	/**
	 * Function returns Account fields for Lead Convert
	 * @return Array
	 */
	function getAccountFieldsForLeadConvert() {
		$accountsFields = array();
		$privilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = 'Accounts';

		if(!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			return;
		}

		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		if ($moduleModel->isActive()) {
			$fieldModels = $moduleModel->getFields();
            //Fields that need to be shown
            $complusoryFields = array('industry');
			foreach ($fieldModels as $fieldName => $fieldModel) {
				if($fieldModel->isMandatory() && $fieldName != 'assigned_user_id') {
                    $keyIndex = array_search($fieldName,$complusoryFields);
                    if($keyIndex !== false) {
                        unset($complusoryFields[$keyIndex]);
                    }
					$leadMappedField = $this->getConvertLeadMappedField($fieldName, $moduleName);
					$fieldModel->set('fieldvalue', $this->get($leadMappedField));
					$accountsFields[] = $fieldModel;
				}
			}
            foreach($complusoryFields as $complusoryField) {
                $fieldModel = Ottocrat_Field_Model::getInstance($complusoryField, $moduleModel);
				if($fieldModel->getPermissions('readwrite')) {
                    $industryFieldModel = $moduleModel->getField($complusoryField);
                    $industryLeadMappedField = $this->getConvertLeadMappedField($complusoryField, $moduleName);
                    $industryFieldModel->set('fieldvalue', $this->get($industryLeadMappedField));
                    $accountsFields[] = $industryFieldModel;
                }
            }
		}
		return $accountsFields;
	}

	/**
	 * Function returns Contact fields for Lead Convert
	 * @return Array
	 */
	function getContactFieldsForLeadConvert() {
		$contactsFields = array();
		$privilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = 'Contacts';

		if(!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			return;
		}

		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		if ($moduleModel->isActive()) {
			$fieldModels = $moduleModel->getFields();
            $complusoryFields = array('firstname', 'email');
            foreach($fieldModels as $fieldName => $fieldModel) {
                if($fieldModel->isMandatory() &&  $fieldName != 'assigned_user_id' && $fieldName != 'account_id') {
                    $keyIndex = array_search($fieldName,$complusoryFields);
                    if($keyIndex !== false) {
                        unset($complusoryFields[$keyIndex]);
                    }

                    $leadMappedField = $this->getConvertLeadMappedField($fieldName, $moduleName);
                    $fieldValue = $this->get($leadMappedField);
                    if ($fieldName === 'account_id') {
                        $fieldValue = $this->get('company');
                    }
                    $fieldModel->set('fieldvalue', $fieldValue);
                    $contactsFields[] = $fieldModel;
                }
            }

			foreach($complusoryFields as $complusoryField) {
                $fieldModel = Ottocrat_Field_Model::getInstance($complusoryField, $moduleModel);
				if($fieldModel->getPermissions('readwrite')) {
					$leadMappedField = $this->getConvertLeadMappedField($complusoryField, $moduleName);
					$fieldModel = $moduleModel->getField($complusoryField);
					$fieldModel->set('fieldvalue', $this->get($leadMappedField));
					$contactsFields[] = $fieldModel;
				}
			}
		}
		return $contactsFields;
	}

	/**
	 * Function returns Potential fields for Lead Convert
	 * @return Array
	 */
	function getPotentialsFieldsForLeadConvert() {
		$potentialFields = array();
		$privilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = 'Potentials';

		if(!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			return;
		}

		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		if ($moduleModel->isActive()) {
			$fieldModels = $moduleModel->getFields();

            $complusoryFields = array('amount');
			foreach($fieldModels as $fieldName => $fieldModel) {
				if($fieldModel->isMandatory() &&  $fieldName != 'assigned_user_id' && $fieldName != 'related_to'
						&& $fieldName != 'contact_id') {
                    $keyIndex = array_search($fieldName,$complusoryFields);
                    if($keyIndex !== false) {
                        unset($complusoryFields[$keyIndex]);
                    }
					$leadMappedField = $this->getConvertLeadMappedField($fieldName, $moduleName);
					$fieldModel->set('fieldvalue', $this->get($leadMappedField));
					$potentialFields[] = $fieldModel;
				}
			}
            foreach($complusoryFields as $complusoryField) {
                $fieldModel = Ottocrat_Field_Model::getInstance($complusoryField, $moduleModel);
                if($fieldModel->getPermissions('readwrite')) {
                    $fieldModel = $moduleModel->getField($complusoryField);
                    $amountLeadMappedField = $this->getConvertLeadMappedField($complusoryField, $moduleName);
                    $fieldModel->set('fieldvalue', $this->get($amountLeadMappedField));
                    $potentialFields[] = $fieldModel;
                }
            }
		}
		return $potentialFields;
	}

	/**
	 * Function returns field mapped to Leads field, used in Lead Convert for settings the field values
	 * @param <String> $fieldName
	 * @return <String>
	 */
	function getConvertLeadMappedField($fieldName, $moduleName) {
		$mappingFields = $this->get('mappingFields');

		if (!$mappingFields) {
			$db = PearDatabase::getInstance();
			$mappingFields = array();

			$result = $db->pquery('SELECT * FROM ottocrat_convertleadmapping', array());
			$numOfRows = $db->num_rows($result);

			$accountInstance = Ottocrat_Module_Model::getInstance('Accounts');
			$accountFieldInstances = $accountInstance->getFieldsById();

			$contactInstance = Ottocrat_Module_Model::getInstance('Contacts');
			$contactFieldInstances = $contactInstance->getFieldsById();

			$potentialInstance = Ottocrat_Module_Model::getInstance('Potentials');
			$potentialFieldInstances = $potentialInstance->getFieldsById();

			$leadInstance = Ottocrat_Module_Model::getInstance('Leads');
			$leadFieldInstances = $leadInstance->getFieldsById();

			for($i=0; $i<$numOfRows; $i++) {
				$row = $db->query_result_rowdata($result,$i);
				if(empty($row['leadfid'])) continue;

				$leadFieldInstance = $leadFieldInstances[$row['leadfid']];
				if(!$leadFieldInstance) continue;

				$leadFieldName = $leadFieldInstance->getName();
				$accountFieldInstance = $accountFieldInstances[$row['accountfid']];
				if ($row['accountfid'] && $accountFieldInstance) {
					$mappingFields['Accounts'][$accountFieldInstance->getName()] = $leadFieldName;
				}
				$contactFieldInstance = $contactFieldInstances[$row['contactfid']];
				if ($row['contactfid'] && $contactFieldInstance) {
					$mappingFields['Contacts'][$contactFieldInstance->getName()] = $leadFieldName;
				}
				$potentialFieldInstance = $potentialFieldInstances[$row['potentialfid']];
				if ($row['potentialfid'] && $potentialFieldInstance) {
					$mappingFields['Potentials'][$potentialFieldInstance->getName()] = $leadFieldName;
				}
			}
			$this->set('mappingFields', $mappingFields);
		}
		return $mappingFields[$moduleName][$fieldName];
	}

	/**
	 * Function returns the fields required for Lead Convert
	 * @return <Array of Ottocrat_Field_Model>
	 */
	function getConvertLeadFields() {
		$convertFields = array();
		$accountFields = $this->getAccountFieldsForLeadConvert();
		if(!empty($accountFields)) {
			$convertFields['Accounts'] = $accountFields;
		}

		$contactFields = $this->getContactFieldsForLeadConvert();
		if(!empty($contactFields)) {
			$convertFields['Contacts'] = $contactFields;
		}

		$potentialsFields = $this->getPotentialsFieldsForLeadConvert();
		if(!empty($potentialsFields)) {
			$convertFields['Potentials'] = $potentialsFields;
		}
		return $convertFields;
	}

	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl() {
		$calendarModuleModel = Ottocrat_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return <String>
	 */
	function getCreateTaskUrl() {
		$calendarModuleModel = Ottocrat_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl().'&parent_id='.$this->getId();
	}
    
    /**
	 * Function to check whether the lead is converted or not
	 * @return True if the Lead is Converted false otherwise.
	 */
    function isLeadConverted() {
        $db = PearDatabase::getInstance();
        $id = $this->getId();
        $sql = "select converted from ottocrat_leaddetails where converted = 1 and leadid=?";
        $result = $db->pquery($sql,array($id));
        $rowCount = $db->num_rows($result);
        if($rowCount > 0){
            return true;
        }
        return false;
    }

}
