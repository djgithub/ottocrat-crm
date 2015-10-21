<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_Relation_Model extends Ottocrat_Relation_Model{

	/**
	 * Function returns the Query for the relationhips
	 * @param <Ottocrat_Record_Model> $recordModel
	 * @param type $actions
	 * @return <String>
	 */
	public function getQuery($recordModel, $actions=false){
		$parentModuleModel = $this->getParentModuleModel();
		$relatedModuleModel = $this->getRelationModuleModel();
		$relatedModuleName = $relatedModuleModel->get('name');
		$parentModuleName = $parentModuleModel->get('name');
		$functionName = $this->get('name');
		$focus = CRMEntity::getInstance($parentModuleName);
		$focus->id = $recordModel->getId();
		if(method_exists($parentModuleModel, $functionName)) {
			$query = $parentModuleModel->$functionName($recordModel, $relatedModuleModel);
		} else {
			$result = $focus->$functionName($recordModel->getId(), $parentModuleModel->getId(),
											$relatedModuleModel->getId(), $actions);
			$query = $result['query'];
		}

		//modify query if any module has summary fields, those fields we are displayed in related list of that module
		$relatedListFields = $relatedModuleModel->getConfigureRelatedListFields();
		if(count($relatedListFields) > 0) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$queryGenerator = new QueryGenerator($relatedModuleName, $currentUser);
			$queryGenerator->setFields($relatedListFields);
			$selectColumnSql = $queryGenerator->getSelectClauseColumnSQL();
			$newQuery = spliti('FROM', $query);
			$selectColumnSql = 'SELECT DISTINCT ottocrat_crmentity.crmid,'.$selectColumnSql;
		}
		if($functionName == ('get_pricebook_products' || 'get_pricebook_services')){
			$selectColumnSql = $selectColumnSql.', ottocrat_pricebookproductrel.listprice';
		}
		$query = $selectColumnSql.' FROM '.$newQuery[1];
		return $query;
	}

	/**
	 * Function to add PriceBook-Products/Services Relation
	 * @param <Integer> $sourceRecordId
	 * @param <Integer> $destinationRecordId
	 * @param <Integer> $listPrice
	 */
	public function addListPrice($sourceRecordId, $destinationRecordId, $listPrice) {
		$sourceModuleName = $this->getParentModuleModel()->get('name');

		$priceBookModel = Ottocrat_Record_Model::getInstanceById($sourceRecordId, $sourceModuleName);
		$priceBookModel->updateListPrice($destinationRecordId, $listPrice);
	}

	/**
	 * Function that deletes PriceBooks related records information
	 * @param <Integer> $sourceRecordId - PriceBook Id
	 * @param <Integer> $relatedRecordId - Related Record Id
	 */
	public function deleteRelation($sourceRecordId, $relatedRecordId){
		$sourceModuleName = $this->getParentModuleModel()->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		if($sourceModuleName == 'PriceBooks' && ($destinationModuleName == 'Products' || $destinationModuleName == 'Services')) {
			$priceBookModel = Ottocrat_Record_Model::getInstanceById($sourceRecordId, $sourceModuleName);
			$priceBookModel->deleteListPrice($relatedRecordId);
		} else {
			parent::deleteRelation($sourceRecordId, $relatedRecordId);
		}
	}
}