<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Block_Model extends Ottocrat_Block_Model {

    public function isActionsAllowed () {
        $actionNotSupportedModules = array('calendar','events');
        if(in_array(strtolower($this->module->name), $actionNotSupportedModules)) {
			return false;
		}
		return true;
	}

    /**
	 * Function to check whether adding custom field is allowed or not
	 * @return <Boolean> true/false
	 */
	public function isAddCustomFieldEnabled() {
        $actionNotSupportedModules = array('calendar','events','faq', 'helpdesk');
		$blocksEliminatedArray = array('calendar' => array('LBL_TASK_INFORMATION', 'LBL_DESCRIPTION_INFORMATION'),
									'helpdesk' =>  array('LBL_TICKET_RESOLUTION', 'LBL_COMMENTS'),
                                                                   'faq'=>array('LBL_COMMENT_INFORMATION'),
                                    'events' => array('LBL_EVENT_INFORMATION','LBL_REMINDER_INFORMATION','LBL_DESCRIPTION_INFORMATION',
                                                      'LBL_RECURRENCE_INFORMATION','LBL_RELATED_TO','LBL_INVITE_USER_BLOCK'));
        if(in_array(strtolower($this->module->name), $actionNotSupportedModules)) {
			if(!empty($blocksEliminatedArray[strtolower($this->module->name)])) {
				if(in_array($this->get('label'), $blocksEliminatedArray[strtolower($this->module->name)])) {
					return false;
				}
			} else {
				return false;
			}
		}
        return true;
    }

    public static function updateFieldSequenceNumber($blockFieldSequence) {
        $fieldIdList = array();
        $db = PearDatabase::getInstance();

        $query = 'UPDATE ottocrat_field SET ';
        $query .=' sequence= CASE ';
        foreach($blockFieldSequence as $newFieldSequence ) {
			$fieldId = $newFieldSequence['fieldid'];
			$sequence = $newFieldSequence['sequence'];
			$block = $newFieldSequence['block'];
            $fieldIdList[] = $fieldId;

			$query .= ' WHEN fieldid='.$fieldId.' THEN '.$sequence;
        }

		$query .=' END, block=CASE ';

		foreach($blockFieldSequence as $newFieldSequence ) {
			$fieldId = $newFieldSequence['fieldid'];
			$sequence = $newFieldSequence['sequence'];
			$block = $newFieldSequence['block'];
			$query .= ' WHEN fieldid='.$fieldId.' THEN '.$block;
		}
		$query .=' END ';

        $query .= ' WHERE fieldid IN ('.generateQuestionMarks($fieldIdList).')';

        $db->pquery($query, array($fieldIdList));
    }

    public static function getInstance($value, $moduleInstance = false) {
		$blockInstance = parent::getInstance($value, $moduleInstance);
		$blockModel = self::getInstanceFromBlockObject($blockInstance);
		return $blockModel;
	}

	/**
	 * Function to retrieve block instance from Ottocrat_Block object
	 * @param Ottocrat_Block $blockObject - vtlib block object
	 * @return Ottocrat_Block_Model
	 */
	public static function getInstanceFromBlockObject(Ottocrat_Block $blockObject) {
		$objectProperties = get_object_vars($blockObject);
		$blockModel = new self();
		foreach($objectProperties as $properName=>$propertyValue) {
			$blockModel->$properName = $propertyValue;
		}
		return $blockModel;
	}

    /**
	 * Function to retrieve block instances for a module
	 * @param <type> $moduleModel - module instance
	 * @return <array> - list of Ottocrat_Block_Model
	 */
	public static function getAllForModule($moduleModel) {
		$blockObjects = parent::getAllForModule($moduleModel);
		$blockModelList = array();

		if($blockObjects) {
			foreach($blockObjects as $blockObject) {
				$blockModelList[] = self::getInstanceFromBlockObject($blockObject);
			}
		}
		return $blockModelList;
	}

	public function getLayoutBlockActiveFields() {
		$fields = $this->getFields();
		$activeFields = array();
		foreach($fields as $fieldName => $fieldModel) {
			if($fieldModel->isActiveField()) {
				$activeFields[$fieldName] = $fieldModel;
			}
		}
		return $activeFields;
	}
}
