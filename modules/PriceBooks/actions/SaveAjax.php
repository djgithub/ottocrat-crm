<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class PriceBooks_SaveAjax_Action extends Ottocrat_SaveAjax_Action {

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

			//To store the relationship between Products/Services and PriceBooks
			if ($parentRecordId && ($parentModuleName === 'Products' || $parentModuleName === 'Services')) {
				$parentRecordModel = Ottocrat_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
				$recordModel->updateListPrice($parentRecordId, $parentRecordModel->get('unit_price'));
			}
		}
		return $recordModel;
	}
}
