<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Services_Module_Model extends Products_Module_Model {
	
	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		$supportedModulesList = array('Leads', 'Accounts', 'HelpDesk', 'Potentials');
		if (($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList')
				|| in_array($sourceModule, $supportedModulesList)
				|| in_array($sourceModule, getInventoryModules())) {

			$condition = " ottocrat_service.discontinued = 1 ";

			if ($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList') {
				$condition .= " AND ottocrat_service.serviceid NOT IN (SELECT productid FROM ottocrat_pricebookproductrel WHERE pricebookid = '$record') ";
			} elseif (in_array($sourceModule, $supportedModulesList)) {
				$condition .= " AND ottocrat_service.serviceid NOT IN (SELECT relcrmid FROM ottocrat_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM ottocrat_crmentityrel WHERE relcrmid = '$record') ";
			}

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
	
	/**
	 * Function returns query for Services-PriceBooks Relationship
	 * @param <Ottocrat_Record_Model> $recordModel
	 * @param <Ottocrat_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_service_pricebooks($recordModel, $relatedModuleModel) {
		$query = 'SELECT ottocrat_pricebook.pricebookid, ottocrat_pricebook.bookname, ottocrat_pricebook.active, ottocrat_crmentity.crmid,
						ottocrat_crmentity.smownerid, ottocrat_pricebookproductrel.listprice, ottocrat_service.unit_price
					FROM ottocrat_pricebook
					INNER JOIN ottocrat_pricebookproductrel ON ottocrat_pricebook.pricebookid = ottocrat_pricebookproductrel.pricebookid
					INNER JOIN ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_pricebook.pricebookid
					INNER JOIN ottocrat_service on ottocrat_service.serviceid = ottocrat_pricebookproductrel.productid
					INNER JOIN ottocrat_pricebookcf on ottocrat_pricebookcf.pricebookid = ottocrat_pricebook.pricebookid
					LEFT JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid
					LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid '
					. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
					WHERE ottocrat_service.serviceid = '.$recordModel->getId().' and ottocrat_crmentity.deleted = 0';
		
		return $query;
	}
}