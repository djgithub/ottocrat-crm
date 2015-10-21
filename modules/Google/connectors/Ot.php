<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */
vimport('~~/modules/WSAPP/synclib/connectors/OttocratConnector.php');
vimport('~~/modules/WSAPP/SyncServer.php');
include_once 'include/Webservices/Query.php';
include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Retrieve.php';

class Google_Ottocrat_Connector extends WSAPP_OttocratConnector {

	/**
	 * function to push data to ottocrat
	 * @param type $recordList
	 * @param type $syncStateModel
	 * @return type
	 */
	public function push($recordList, $syncStateModel) {
		return parent::push($recordList, $syncStateModel);
	}

	/**
	 * function to get data from ottocrat
	 * @param type $syncStateModel
	 * @return type
	 */
	public function pull($syncStateModel) {
		$records = parent::pull($syncStateModel);
		return $records;
	}

	/**
	 * function that returns syncTrackerhandler name
	 * @return string
	 */
	public function getSyncTrackerHandlerName() {
		return 'Google_ottocratSyncHandler';
	}
	
}
