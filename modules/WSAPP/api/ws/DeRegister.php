<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
require_once 'modules/WSAPP/SyncServer.php';
function wsapp_deregister ($type, $key, $user) {

	$instance = new SyncServer();
	return $instance->deregister($type, $key, $user);
}
