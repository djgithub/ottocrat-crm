<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Picklist_Field_Model extends Ottocrat_Field_Model {



    public function isEditable() {
        $nonEditablePickListValues = array( 'campaignrelstatus', 'duration_minutes','email_flag','hdnTaxType',
                        'payment_duration','recurringtype','recurring_frequency','visibility');
        if(in_array($this->getName(), $nonEditablePickListValues)) return false;
        return true;
    }

    /**
     * Function which will give the picklistvalues for given roleids
     * @param type $roleIdList -- array of role ids
     * @param type $groupMode -- Intersection/Conjuction , intersection will give only picklist values that exist for all roles
     * @return type -- array
     */
    public function getPicklistValues($roleIdList, $groupMode='INTERSECTION') {
        if(!$this->isRoleBased()) {
            return parent::getPicklistValues();
        }
        $intersectionMode = false;
        if($groupMode == 'INTERSECTION') {
            $intersectionMode = true;
        }

        $db = PearDatabase::getInstance();
        $fieldName = $this->getName();
        $tableName = 'ottocrat_'.$fieldName;
        $idColName = $fieldName.'id';
        $query = 'SELECT '.$fieldName;
        if($intersectionMode) {
            $query .= ',count(roleid) as rolecount ';
        }
        $query .= ' FROM  ottocrat_role2picklist INNER JOIN '.$tableName.' ON ottocrat_role2picklist.picklistvalueid = '.$tableName.'.picklist_valueid'.
                 ' WHERE roleid IN ('.generateQuestionMarks($roleIdList).') order by sortid';
        if($intersectionMode) {
            $query .= ' GROUP BY picklistvalueid';
        }
		$result = $db->pquery($query, $roleIdList);
        $pickListValues = array();
        $num_rows = $db->num_rows($result);
        for($i=0; $i<$num_rows; $i++) {
            $rowData = $db->query_result_rowdata($result, $i);
            if($intersectionMode) {
                //not equal if specify that the picklistvalue is not present for all the roles
                if($rowData['rolecount'] != count($roleIdList)){
                    continue;
                }
            }
			//Need to decode the picklist values twice which are saved from old ui
            $pickListValues[] = decode_html(decode_html($rowData[$fieldName]));
        }
        return $pickListValues;
    }

    /**
	 * Function to get instance
	 * @param <String> $value - fieldname or fieldid
	 * @param <type> $module - optional - module instance
	 * @return <Ottocrat_Field_Model>
	 */
	public static function  getInstance($value, $module = false) {
		$fieldObject = parent::getInstance($value, $module);
		if($fieldObject) {
			return self::getInstanceFromFieldObject($fieldObject);
		}
		return false;
	}

    /**
	 * Static Function to get the instance fo Ottocrat Field Model from a given Ottocrat_Field object
	 * @param Ottocrat_Field $fieldObj - vtlib field object
	 * @return Ottocrat_Field_Model instance
	 */
	public static function getInstanceFromFieldObject(Ottocrat_Field $fieldObj) {
		$objectProperties = get_object_vars($fieldObj);
		$fieldModel = new self();
		foreach($objectProperties as $properName=>$propertyValue) {
			$fieldModel->$properName = $propertyValue;
		}
		return $fieldModel;
	}

	/**
     * Function which will give the editable picklist values for a field
     * @param type $fieldName -- string
     * @return type -- array of values
     */
	public static function getEditablePicklistValues($fieldName){
		$cache = Ottocrat_Cache::getInstance();
		$EditablePicklistValues = $cache->get('EditablePicklistValues', $fieldName);
        if($EditablePicklistValues) {
            return $EditablePicklistValues;
        }
        $db = PearDatabase::getInstance();
		$primaryKey = Ottocrat_Util_Helper::getPickListId($fieldName);
		
        $query="SELECT $primaryKey ,$fieldName FROM ottocrat_$fieldName WHERE presence=1 AND $fieldName <> '--None--'";
        $values = array();
        $result = $db->pquery($query, array());
        $num_rows = $db->num_rows($result);
        for($i=0; $i<$num_rows; $i++) {
			//Need to decode the picklist values twice which are saved from old ui
            $values[$db->query_result($result,$i,$primaryKey)] = decode_html(decode_html($db->query_result($result,$i,$fieldName)));
        }
		$cache->set('EditablePicklistValues', $fieldName, $values);
        return $values;
	}

	/**
     * Function which will give the non editable picklist values for a field
     * @param type $fieldName -- string
     * @return type -- array of values
     */
	public static function getNonEditablePicklistValues($fieldName){
		$cache = Ottocrat_Cache::getInstance();
		$NonEditablePicklistValues = $cache->get('NonEditablePicklistValues', $fieldName);
        if($NonEditablePicklistValues) {
            return $NonEditablePicklistValues;
        }
        $db = PearDatabase::getInstance();

        $query = "select $fieldName from ottocrat_$fieldName where presence=0";
        $values = array();
        $result = $db->pquery($query, array());
        $num_rows = $db->num_rows($result);
        for($i=0; $i<$num_rows; $i++) {
			//Need to decode the picklist values twice which are saved from old ui
            $values[] = decode_html(decode_html($db->query_result($result,$i,$fieldName)));
        }
        $cache->set('NonEditablePicklistValues', $fieldName, $values);
        return $values;
	}

}