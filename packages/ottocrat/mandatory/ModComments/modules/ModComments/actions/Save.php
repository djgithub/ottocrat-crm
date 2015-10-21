<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class ModComments_Save_Action extends Ottocrat_Save_Action {

	public function process(Ottocrat_Request $request) {
		$recordId = $request->get('record');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$request->set('assigned_user_id', $currentUserModel->getId());
		$request->set('userid', $currentUserModel->getId());
		
		$recordModel = $this->saveRecord($request);
		$responseFieldsToSent = array('reasontoedit','commentcontent');
		$fieldModelList = $recordModel->getModule()->getFields();
		foreach ($responseFieldsToSent as $fieldName) {
            $fieldModel = $fieldModelList[$fieldName];
            $fieldValue = $recordModel->get($fieldName);
			$result[$fieldName] = $fieldModel->getDisplayValue(Ottocrat_Util_Helper::toSafeHTML($fieldValue));
		}

		$result['success'] = true;
		$result['modifiedtime'] = Ottocrat_Util_Helper::formatDateDiffInStrings($recordModel->get('modifiedtime'));
		$result['modifiedtimetitle'] = Ottocrat_Util_Helper::formatDateTimeIntoDayString($recordModel->get('modifiedtime'));

		$response = new Ottocrat_Response();
		$response->setEmitType(Ottocrat_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
	
	/**
	 * Function to save record
	 * @param <Ottocrat_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request) {
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Ottocrat_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			$relatedRecordId = $recordModel->getId();

			$relationModel = Ottocrat_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
		return $recordModel;
	}
	
	/**
	 * Function to get the record model based on the request parameters
	 * @param Ottocrat_Request $request
	 * @return Ottocrat_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Ottocrat_Request $request) {
		$recordModel = parent::getRecordModelFromRequest($request);
		
		$recordModel->set('commentcontent', $request->getRaw('commentcontent'));
		$recordModel->set('reasontoedit', $request->getRaw('reasontoedit'));

		return $recordModel;
	}
	
}
