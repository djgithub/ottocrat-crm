<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_Module_Model extends Ottocrat_Module_Model {

	/**
	 * Function returns query for PriceBook-Product relation
	 * @param <Ottocrat_Record_Model> $recordModel
	 * @param <Ottocrat_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_pricebook_products($recordModel, $relatedModuleModel) {
		$query = 'SELECT ottocrat_products.productid, ottocrat_products.productname, ottocrat_products.productcode, ottocrat_products.commissionrate,
						ottocrat_products.qty_per_unit, ottocrat_products.unit_price, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
						ottocrat_pricebookproductrel.listprice
				FROM ottocrat_products
				INNER JOIN ottocrat_pricebookproductrel ON ottocrat_products.productid = ottocrat_pricebookproductrel.productid
				INNER JOIN ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_products.productid
				INNER JOIN ottocrat_pricebook on ottocrat_pricebook.pricebookid = ottocrat_pricebookproductrel.pricebookid
				INNER JOIN ottocrat_productcf on ottocrat_productcf.productid = ottocrat_products.productid
				LEFT JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid '
				. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
				WHERE ottocrat_pricebook.pricebookid = '.$recordModel->getId().' and ottocrat_crmentity.deleted = 0';
		return $query;
	}


	/**
	 * Function returns query for PriceBooks-Services Relationship
	 * @param <Ottocrat_Record_Model> $recordModel
	 * @param <Ottocrat_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_pricebook_services($recordModel, $relatedModuleModel) {
		$query = 'SELECT ottocrat_service.serviceid, ottocrat_service.servicename, ottocrat_service.service_no, ottocrat_service.commissionrate,
					ottocrat_service.qty_per_unit, ottocrat_service.unit_price, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
					ottocrat_pricebookproductrel.listprice
			FROM ottocrat_service
			INNER JOIN ottocrat_pricebookproductrel on ottocrat_service.serviceid = ottocrat_pricebookproductrel.productid
			INNER JOIN ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_service.serviceid
			INNER JOIN ottocrat_pricebook on ottocrat_pricebook.pricebookid = ottocrat_pricebookproductrel.pricebookid
			INNER JOIN ottocrat_servicecf on ottocrat_servicecf.serviceid = ottocrat_service.serviceid
			LEFT JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid '
			. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
			WHERE ottocrat_pricebook.pricebookid = '.$recordModel->getId().' and ottocrat_crmentity.deleted = 0';
		return $query;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery, $currencyId = false) {
		$relatedModulesList = array('Products', 'Services');
		if (in_array($sourceModule, $relatedModulesList)) {
			$pos = stripos($listQuery, ' where ');
			if ($currencyId && in_array($field, array('productid', 'serviceid'))) {
				$condition = " ottocrat_pricebook.pricebookid IN (SELECT pricebookid FROM ottocrat_pricebookproductrel WHERE productid = $record)
								AND ottocrat_pricebook.currency_id = $currencyId AND ottocrat_pricebook.active = 1";
			} else if($field == 'productsRelatedList') {
				$condition = "ottocrat_pricebook.pricebookid NOT IN (SELECT pricebookid FROM ottocrat_pricebookproductrel WHERE productid = $record)
								AND ottocrat_pricebook.active = 1";
			}
			if ($pos) {
				$split = spliti(' where ', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
	
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}
	
	/**
	 * Funtion that returns fields that will be showed in the record selection popup
	 * @return <Array of fields>
	 */
	public function getPopupViewFieldsList() {
		$popupFileds = $this->getSummaryViewFieldsList();
		$reqPopUpFields = array('Currency' => 'currency_id'); 
		foreach ($reqPopUpFields as $fieldLabel => $fieldName) {
			$fieldModel = Ottocrat_Field_Model::getInstance($fieldName,$this); 
			if ($fieldModel->getPermissions('readwrite')) { 
				$popupFileds[$fieldName] = $fieldModel; 
			}
		}
		return array_keys($popupFileds);
	}
}