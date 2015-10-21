<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Products_MassSave_Action extends Ottocrat_MassSave_Action {

	public function process(Ottocrat_Request $request) {
		//the new values are added to $_REQUEST for MassSave, are removing the Tax details depend on the 'action' value
		$_REQUEST['action'] = 'MassEditSave';
		$request->set('action', 'MassEditSave');

		//the new values are added to $_REQUEST for MassSave, the unit price depend on the 'mass_edit_check' value
		$_REQUEST['unit_price_mass_edit_check'] = 'off';
		parent::process($request);
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Ottocrat_Request $request
	 * @return Ottocrat_Record_Model or Module specific Record Model instance
	 */
	function getRecordModelsFromRequest(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$recordModels = parent::getRecordModelsFromRequest($request);
		$fieldModelList = $moduleModel->getFields();

		foreach($recordModels as $id => $model) {
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				$fieldDataType = $fieldModel->getFieldDataType();
				// This is added as we are marking massedit in ottocrat6 as not an ajax operation
				// and this will force the date fields to be saved in user format. If the user format
				// is other than y-m-d then it fails. We need to review the above process API changes
				// which was added to fix unit price issue where it was getting changed when mass edited.
				if($fieldDataType == 'date' || ($fieldDataType == 'currency') && $uiType != '72') {
					$model->set($fieldName, $fieldModel->getUITypeModel()->getDBInsertValue($model->get($fieldName)));
				}
			}
		}
		return $recordModels;
	}
}
