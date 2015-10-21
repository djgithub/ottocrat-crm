<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Products_RelationListView_Model extends Ottocrat_RelationListView_Model {

	/**
	 * Function to get the links for related list
	 * @return <Array> List of action models <Ottocrat_Link_Model>
	 */
	public function getLinks() {
		$relationModel = $this->getRelationModel();
		$parentModel = $this->getParentRecordModel();
		
		$isSubProduct = false;
		if($parentModel->getModule()->getName() == $relationModel->getRelationModuleModel()->getName()) {
			$isSubProduct = $relationModel->isSubProduct($parentModel->getId());
		}
		
		if(!$isSubProduct){
			return parent::getLinks();
		}
	}
	
	public function getHeaders() {
		$headerFields = parent::getHeaders();
		if($this->getRelationModel()->getRelationModuleModel()->getName() == 'PriceBooks') {
			//Added to support Unit Price
			$unitPriceField = new Ottocrat_Field_Model();
			$unitPriceField->set('name', 'unit_price');
			$unitPriceField->set('column', 'unit_price');
			$unitPriceField->set('label', 'Unit Price');
			
			$headerFields['unit_price'] = $unitPriceField;
			
			//Added to support List Price
			$field = new Ottocrat_Field_Model();
			$field->set('name', 'listprice');
			$field->set('column', 'listprice');
			$field->set('label', 'List Price');
			
			$headerFields['listprice'] = $field;
		}
		
		return $headerFields;
	}
	
}
