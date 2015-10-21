<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *
 *********************************************************************************/

require_once "include/Webservices/OttocratActorOperation.php";
require_once 'include/Webservices/LineItem/OttocratTaxMeta.php';
require_once("include/events/include.inc");
require_once 'modules/com_ottocrat_workflow/VTEntityCache.inc';
require_once 'data/CRMEntity.php';
require_once 'include/events/SqlResultIterator.inc';
require_once 'include/Webservices/LineItem/OttocratLineItemMeta.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Utils.php';
require_once 'modules/Emails/mail.php';


/**
 * Description of OttocratTaxOperation
 */
class OttocratTaxOperation  extends OttocratActorOperation {

	public function __construct($webserviceObject, $user, $adb, $log) {
		parent::__construct($webserviceObject,$user,$adb,$log);
		$this->entityTableName = $this->getActorTables();
		if($this->entityTableName === null){
			throw new WebServiceException(WebServiceErrorCode::$UNKOWNENTITY,"Entity is not associated with any tables");
		}
		$this->meta = new OttocratTaxMeta($this->entityTableName,$webserviceObject,$adb,$user);
		$this->moduleFields = null;
	}

	public function create($elementType, $element) {
		$element = $this->restrictFields($element);
		$taxName = $this->getNewTaxName();
		$element['taxname'] = $taxName;
		$element['deleted'] = 0;
		$createdElement = parent::create($elementType, $element);
		$sql = "alter table ottocrat_inventoryproductrel add column $taxName decimal(7,3)";
		$result = $this->pearDB->pquery($sql,array());
		if(!is_object($result)) {
			list($typeId,$id) = vtws_getIdComponents($element['id']);
			$this->dropRow($id);
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
				"Database error while adding tax column($taxName) for inventory lineitem table");
		}
		return $createdElement;
	}

	public function update($element) {
		$element['taxname'] = $this->getCurrentTaxName();
		return parent::update($element);
	}

	public function delete($id) {
		$ids = vtws_getIdComponents($id);
		$elemId = $ids[1];

		$result = null;
		$query = 'update '.$this->entityTableName.' set deleted=1 where '.$this->meta->getObectIndexColumn().'=?';
		$transactionSuccessful = vtws_runQueryAsTransaction($query,array($elemId),$result);
		if(!$transactionSuccessful){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
				"Database error while performing required operation");
		}
		return array("status"=>"successful");
	}

	private function dropRow($id) {
		$sql = 'delete from ottocrat_inventorytaxinfo where taxid = ?';
		$params = array($id);
		$result = $this->pearDB->pquery($sql, $params);
	}

	private function getCurrentTaxName() {
		$sql = 'select taxname from ottocrat_inventorytaxinfo order by taxid desc limit 1';
		$params = array();
		$result = $this->pearDB->pquery($sql, $params);
		$it = new SqlResultIterator($this->pearDB, $result);
		$currentTaxName = null;
		foreach ($it as $row) {
			$currentTaxName = $row->taxname;
		}
		return $currentTaxName;
	}

	private function getNewTaxName() {
		$currentTaxName = $this->getCurrentTaxName();

		if(empty($currentTaxName)) {
			return 'tax1';
		}

		$matches = null;
		if ( preg_match('/tax(\d+)/', $currentTaxName, $matches) != 0 ) {
			$taxNumber = (int) $matches[1];
			$taxNumber++;
			return 'tax'.$taxNumber;
		}
		return 'tax1';
	}

}
?>