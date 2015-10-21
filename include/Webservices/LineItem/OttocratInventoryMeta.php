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

require_once "include/Webservices/OttocratCRMObjectMeta.php";

/**
 * Description of OttocratInventoryMeta
 */
class OttocratInventoryMeta extends OttocratCRMObjectMeta {
	private $metaTableList = array('ottocrat_inventorytaxinfo','ottocrat_shippingtaxinfo');
	private $metaTablePrefix = array('ottocrat_inventorytaxinfo'=>'', 'ottocrat_shippingtaxinfo'=>'S & H ');
	
	public function retrieveMeta() {
		parent::retrieveMeta();
		$this->retrieveMetaForTables();
	}

	function retrieveMetaForTables() {
		$db = PearDatabase::getInstance();
		foreach ($this->metaTableList as $tableName) {
			$sql = "SELECT * FROM $tableName WHERE deleted=0";
			$params = array();
			$result = $db->pquery($sql, $params);
			if(!empty($result)){
				$it = new SqlResultIterator($db, $result);
				foreach ($it as $row) {
					$fieldArray = $this->getFieldArrayFromTaxRow($row,$tableName,
						$this->metaTablePrefix[$tableName]);
					$webserviceField = WebserviceField::fromArray($db, $fieldArray);
					$webserviceField->setDefault($row->percentage);
					$this->moduleFields[$webserviceField->getFieldName()] = $webserviceField;
				}
			}
		}
	}

	function getFieldArrayFromTaxRow($row, $tableName, $prefix) {
		$field = array();
		$field['fieldname'] = $row->taxname;
		$field['columnname'] = $row->taxname;
		$field['tablename'] = $tableName;
		$field['fieldlabel'] = $prefix.$row->taxlabel;
		$field['displaytype'] = 1;
		$field['uitype'] = 1;
		$fieldDataType = 'V';
		$typeOfData = $fieldType.'~O';

		$field['typeofdata'] = $typeOfData;
		$field['tabid'] = null;
		$field['fieldid'] = null;
		$field['masseditable'] = 0;
		return $field;
	}
	
}
?>