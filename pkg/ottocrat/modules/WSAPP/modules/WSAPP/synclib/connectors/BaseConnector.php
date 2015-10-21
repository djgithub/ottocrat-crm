<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
require_once 'modules/WSAPP/synclib/models/SyncStateModel.php';

abstract class WSAPP_BaseConnector {

	protected $syncController;
	
	function __construct() {
		
	}
	
	public function pull(){
		return false;
	}

	public function push(){
		return false;
	}

	function postEvent($type, $synchronizedRecords, $syncStateModel){
		return false;
	}

	function preEvent($type){
		return false;
	}

	abstract function getName();

	function getSyncState(){
		return new WSAPP_SyncStateModel();
	}
	function updateSyncState(){

	}

	function getSynchronizeController(){
		return $this->syncController;
	}

	function setSynchronizeController($syncController){
		$this->syncController = $syncController;
	}

	/*
	 * This will performs basic transformation between two records
	 * <params>
	 *		The sourece records refers to record which has data
	 *			Target record refers to record to which data has to be copied
	 *
	 */
	public function performBasicTransformations(WSAPP_SyncRecordModel $sourceRecord,WSAPP_SyncRecordModel $targetRecord){
		$targetRecord->setType($sourceRecord->getType())
				     ->setMode($sourceRecord->getMode())
					 ->setSyncIdentificationKey($sourceRecord->getSyncIdentificationKey());
		return $targetRecord;
	}

	public function performBasicTransformationsToSourceRecords(WSAPP_SyncRecordModel $sourceRecord, WSAPP_SyncRecordModel $targetRecord){
		$sourceRecord->setId($targetRecord->getId())
					->setModifiedTime($targetRecord->getModifiedTime());
		return $sourceRecord;
	}

	public function performBasicTransformationsToTargetRecords(WSAPP_SyncRecordModel $sourceRecord, WSAPP_SyncRecordModel $targetRecord){
		$sourceRecord->setId($targetRecord->get('_id'))
					->setModifiedTime($targetRecord->get('_modifiedtime'));
			
		return $sourceRecord;
	}
	
}
?>
