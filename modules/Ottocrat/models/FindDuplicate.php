<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Ottocrat_FindDuplicate_Model extends Ottocrat_Base_Model {

	public function setModule($moduleModel) {
		$this->module = $moduleModel;
	}

	public function getModule() {
		return $this->module;
	}

	function getListViewHeaders() {
		$db = PearDatabase::getInstance();
		$moduleModel = $this->getModule();
		$listViewHeaders = array();
		$listViewHeaders[] = new Ottocrat_Base_Model(array('name' => 'recordid', 'label' => 'Record Id'));
		$headers = $db->getFieldsArray($this->result);
		foreach($headers as $header) {
			$fieldModel = $moduleModel->getFieldByColumn($header);
			if($fieldModel) {
				$listViewHeaders[] = $fieldModel;
			}
		}
		return $listViewHeaders;
	}

	function getListViewEntries(Ottocrat_Paging_Model $paging) {
        $db = PearDatabase::getInstance();
        $moduleModel = $this->getModule();
        $module = $moduleModel->getName();

        $fields = $this->get('fields');
        $fieldModels = $moduleModel->getFields();
        if(is_array($fields)) {
            foreach($fields as $fieldName) {
                $fieldModel = $fieldModels[$fieldName];
                $tableColumns[] = $fieldModel->get('table').'.'.$fieldModel->get('column');
            }
        }

        $startIndex = $paging->getStartIndex();
        $pageLimit = $paging->getPageLimit();
		$ignoreEmpty = $this->get('ignoreEmpty');

        $focus = CRMEntity::getInstance($module);
        $query = $focus->getQueryForDuplicates($module, $tableColumns, '', $ignoreEmpty);

		$query .= " LIMIT $startIndex, ". ($pageLimit+1);
		
		$result = $db->pquery($query, array());
        $rows = $db->num_rows($result);
        $this->result = $result;

        $group = 'group0';
        $temp = $fieldValues = array(); $groupCount = 0;
        $groupRecordCount = 0;
        $entries = array();
        for($i=0; $i<$rows; $i++) {
			$entries[] = $db->query_result_rowdata($result, $i);
		}

		$paging->calculatePageRange($entries);

        if ($rows > $pageLimit) {
			array_pop($entries);
            $paging->set('nextPageExists', true);
        } else {
            $paging->set('nextPageExists', false);
        }
		$rows = count($entries);

		for ($i=0; $i<$rows; $i++) {
			$row = $entries[$i];
            if($i != 0) {
                $slicedArray = array_slice($row, 2);
                array_walk($temp, 'lower_array');
                array_walk($slicedArray, 'lower_array');
                $arrDiff = array_diff($temp, $slicedArray);
                if(count($arrDiff) > 0) {
                    $groupCount++;
                    $temp = $slicedArray;
                    $groupRecordCount = 0;
                }
                $group = "group".$groupCount;
            }
            $fieldValues[$group][$groupRecordCount]['recordid'] = $row['recordid'];
            foreach($row as $field => $value) {
                if($i == 0 && $field != 'recordid') $temp[$field] = $value;
                $fieldModel = $fieldModels[$field];
                $resultRow[$field] = $value;
            }
            $fieldValues[$group][$groupRecordCount++] = $resultRow;
        }

        return $fieldValues;
    }

	public static function getInstance($module) {
		$self = new self();
		$moduleModel = Ottocrat_Module_Model::getInstance($module);
		$self->setModule($moduleModel);
		return $self;
	}

	public function getRecordCount() {
		if($this->rows) {
			$rows = $this->rows;
		} else {
			$db = PearDatabase::getInstance();

			$moduleModel = $this->getModule();
			$module = $moduleModel->getName();
			$fields = $this->get('fields');
			$fieldModels = $moduleModel->getFields();
			if(is_array($fields)) {
				foreach($fields as $fieldName) {
					$fieldModel = $fieldModels[$fieldName];
					$tableColumns[] = $fieldModel->get('table').'.'.$fieldModel->get('column');
				}
			}
			$focus = CRMEntity::getInstance($module);
			$ignoreEmpty = $this->get('ignoreEmpty');
			$query = $focus->getQueryForDuplicates($module, $tableColumns, '', $ignoreEmpty);

			$position = stripos($query, 'from');
			if ($position) {
				$split = spliti('from ', $query);
				$splitCount = count($split);
				$query = 'SELECT count(*) AS count ';
				for ($i=1; $i<$splitCount; $i++) {
					$query = $query. ' FROM ' .$split[$i];
				}
			}
			$result = $db->pquery($query, array());
			$rows = $db->query_result($result, 0, 'count');

		}
		return $rows;
	}
}