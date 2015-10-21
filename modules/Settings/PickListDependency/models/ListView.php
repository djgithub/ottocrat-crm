<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_PickListDependency_ListView_Model extends Settings_Ottocrat_ListView_Model {

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Ottocrat_Field_Model instances
	 */
	public function getListViewHeaders() {
		$field = new Ottocrat_Base_Model();
		$field->set('name', 'sourceLabel');
		$field->set('label', 'Module');
		$field->set('sort',false);

		$field1 = new Ottocrat_Base_Model();
		$field1->set('name', 'sourcefieldlabel');
		$field1->set('label', 'Source Field');
		$field1->set('sort',false);

		$field2 = new Ottocrat_Base_Model();
		$field2->set('name', 'targetfieldlabel');
		$field2->set('label', 'Target Field');
		$field2->set('sort',false);

		return array($field, $field1, $field2);
	}

	/**
	 * Function to get the list view entries
	 * @param Ottocrat_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Ottocrat_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$forModule = $this->get('formodule');

		$dependentPicklists = Ottocrat_DependencyPicklist::getDependentPicklistFields($forModule);

		$noOfRecords = count($dependentPicklists);
		$recordModelClass = Ottocrat_Loader::getComponentClassName('Model', 'Record', 'Settings:PickListDependency');

		$listViewRecordModels = array();
		for($i=0; $i<$noOfRecords; $i++) {
			$record = new $recordModelClass();
			$module = $dependentPicklists[$i]['module'];
			unset($dependentPicklists[$i]['module']);
			$record->setData($dependentPicklists[$i]);
			$record->set('sourceModule',$module);
			$record->set('sourceLabel', vtranslate($module, $module));
			$listViewRecordModels[] = $record;
		}
		$pagingModel->calculatePageRange($listViewRecordModels);
		return $listViewRecordModels;
	}
}