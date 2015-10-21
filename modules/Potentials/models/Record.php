<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Potentials_Record_Model extends Ottocrat_Record_Model {

	function getCreateInvoiceUrl() {
		$invoiceModuleModel = Ottocrat_Module_Model::getInstance('Invoice');
		return Ottocrat_Request:: encryptLink( 'index.php?module='.$invoiceModuleModel->getName().'&view='
			.$invoiceModuleModel->getEditViewName().'&account_id='.$this->get('related_to').'&contact_id='.$this->get
			('contact_id'));
	}

	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl() {
		$calendarModuleModel = Ottocrat_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return <String>
	 */
	function getCreateTaskUrl() {
		$calendarModuleModel = Ottocrat_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function to get List of Fields which are related from Contacts to Inventyory Record
	 * @return <array>
	 */
	public function getInventoryMappingFields() {
		return array(
				array('parentField'=>'related_to', 'inventoryField'=>'account_id', 'defaultValue'=>''),
				array('parentField'=>'contact_id', 'inventoryField'=>'contact_id', 'defaultValue'=>''),
		);
	}

    /**
	 * Function returns the url for create quote
	 * @return <String>
	 */
	public function getCreateQuoteUrl() {
		$quoteModuleModel = Ottocrat_Module_Model::getInstance('Quotes');
		return $quoteModuleModel->getCreateRecordUrl().'&sourceRecord='.$this->getId().'&sourceModule='.$this->getModuleName().'&potential_id='.$this->getId().'&relationOperation=true';
	}
}
