<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Products_Module_Model extends Ottocrat_Module_Model {

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		$supportedModulesList = array($this->getName(), 'Vendors', 'Leads', 'Accounts', 'Contacts', 'Potentials');
		if (($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList')
				|| in_array($sourceModule, $supportedModulesList)
				|| in_array($sourceModule, getInventoryModules())) {

			$condition = " ottocrat_products.discontinued = 1 ";
			if ($sourceModule === $this->getName()) {
				$condition .= " AND ottocrat_products.productid NOT IN (SELECT productid FROM ottocrat_seproductsrel WHERE crmid = '$record' UNION SELECT crmid FROM ottocrat_seproductsrel WHERE productid = '$record') AND ottocrat_products.productid <> '$record' ";
			} elseif ($sourceModule === 'PriceBooks') {
				$condition .= " AND ottocrat_products.productid NOT IN (SELECT productid FROM ottocrat_pricebookproductrel WHERE pricebookid = '$record') ";
			} elseif ($sourceModule === 'Vendors') {
				$condition .= " AND ottocrat_products.vendor_id != '$record' ";
			} elseif (in_array($sourceModule, $supportedModulesList)) {
				$condition .= " AND ottocrat_products.productid NOT IN (SELECT productid FROM ottocrat_seproductsrel WHERE crmid = '$record')";
			}

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule) {
		if ($relatedModule === 'Leads') {
			$specificQuery = 'AND ottocrat_leaddetails.converted = 0';
			return $specificQuery;
		}
		return parent::getSpecificRelationQuery($relatedModule);
 	}

	/**
	 * Function to get prices for specified products with specific currency
	 * @param <Integer> $currenctId
	 * @param <Array> $productIdsList
	 * @return <Array>
	 */
	public function getPricesForProducts($currencyId, $productIdsList) {
		return getPricesForProducts($currencyId, $productIdsList, $this->getName());
	}
	
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}
	
	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param <String> $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param <String> $parentModule - parent module name
	 * @return <Array of Ottocrat_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId=false, $parentModule=false, $relatedModule=false) {
		if(!empty($searchValue) && empty($parentId) && empty($parentModule) && (in_array($relatedModule, getInventoryModules()))) {
			$matchingRecords = Products_Record_Model::getSearchResult($searchValue, $this->getName());
		}else {
			return parent::searchRecord($searchValue);
		}

		return $matchingRecords;
	}
	
	/**
	 * Function returns query for Product-PriceBooks relation
	 * @param <Ottocrat_Record_Model> $recordModel
	 * @param <Ottocrat_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_product_pricebooks($recordModel, $relatedModuleModel) {
		$query = 'SELECT ottocrat_pricebook.pricebookid, ottocrat_pricebook.bookname, ottocrat_pricebook.active, ottocrat_crmentity.crmid, 
						ottocrat_crmentity.smownerid, ottocrat_pricebookproductrel.listprice, ottocrat_products.unit_price
					FROM ottocrat_pricebook
					INNER JOIN ottocrat_pricebookproductrel ON ottocrat_pricebook.pricebookid = ottocrat_pricebookproductrel.pricebookid
					INNER JOIN ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_pricebook.pricebookid
					INNER JOIN ottocrat_products on ottocrat_products.productid = ottocrat_pricebookproductrel.productid
					INNER JOIN ottocrat_pricebookcf on ottocrat_pricebookcf.pricebookid = ottocrat_pricebook.pricebookid
					LEFT JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid
					LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid '
					. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
					WHERE ottocrat_products.productid = '.$recordModel->getId().' and ottocrat_crmentity.deleted = 0';
					
		return $query;
	}
}