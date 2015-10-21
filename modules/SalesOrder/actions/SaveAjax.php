<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
class SalesOrder_SaveAjax_Action extends Inventory_SaveAjax_Action {
	
	/**
	 * Function to get the record model based on the request parameters
	 * @param Ottocrat_Request $request
	 * @return Ottocrat_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		
		if($request->get('field') == 'enable_recurring'){
			$enableRecurrence = true;
		}
		if(!empty($recordId)) {
			$recordModel = Ottocrat_Record_Model::getInstanceById($recordId, $moduleName);
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');

			$fieldModelList = $recordModel->getModule()->getFields();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
                //For not converting createdtime and modified time to user format
                $uiType = $fieldModel->get('uitype');
                if ($uiType == 70) {
                    $fieldValue = $recordModel->get($fieldName);
                } else {
					$fieldValue = $fieldModel->getUITypeModel()->getUserRequestValue($recordModel->get($fieldName));
                }
				

				if ($fieldName === $request->get('field')) {
					$fieldValue = $request->get('value');
				}
				
				/**
				 * If field is enable recurrence then we need to pass related fields of
				 * recurrence to save,because untill enable recurrence is checked,the 
				 * related field values wont get saved
				 */
				if($enableRecurrence){
					$requestFieldValue = $request->get($fieldName);
					if($requestFieldValue != ''){
						$fieldValue = $request->get($fieldName);
					}
				}
				
                $fieldDataType = $fieldModel->getFieldDataType();
                if ($fieldDataType == 'time') {
					$fieldValue = Ottocrat_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if ($fieldValue !== null) {
					if (!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		} else {
			$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

			$recordModel = Ottocrat_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('mode', '');

			$fieldModelList = $moduleModel->getFields();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				if ($request->has($fieldName)) {
					$fieldValue = $request->get($fieldName, null);
				} else {
					$fieldValue = $fieldModel->getDefaultFieldValue();
				}
				$fieldDataType = $fieldModel->getFieldDataType();
				if ($fieldDataType == 'time') {
					$fieldValue = Ottocrat_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if ($fieldValue !== null) {
					if (!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				}
			} 
		}
		return $recordModel;
	}
}