<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_RelationAjax_Action extends Ottocrat_RelationAjax_Action {

	function process(Ottocrat_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode) && method_exists($this, "$mode")) {
			$this->$mode($request);
			return;
		}
	}

	/**
	 * Function adds PriceBooks-Products Relation
	 * @param type $request
	 */
	function addListPrice($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');
		$relatedModule =  $request->get('related_module');
		$relInfos = $request->get('relinfo');
		$relatedModule = $request->get('related_module');

		$sourceModuleModel = Ottocrat_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Ottocrat_Module_Model::getInstance($relatedModule);
		$relationModel = Ottocrat_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relInfos as $relInfo) {
			$price = CurrencyField::convertToDBFormat($relInfo['price'], null, true);
			$relationModel->addListPrice($sourceRecordId, $relInfo['id'], $price);
		}
	}
	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 */
	function addRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		$sourceModuleModel = Ottocrat_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Ottocrat_Module_Model::getInstance($relatedModule);
		$relationModel = Ottocrat_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relatedRecordIdList as $relatedRecordId) {
			$relationModel->addRelation($sourceRecordId,$relatedRecordId,$listPrice);
		}
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param <array> $request
	 */
	function deleteRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		$sourceModuleModel = Ottocrat_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Ottocrat_Module_Model::getInstance($relatedModule);
		$relationModel = PriceBooks_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relatedRecordIdList as $relatedRecordId) {
			$relationModel->deleteRelation($sourceRecordId,$relatedRecordId);
		}
	}
}
