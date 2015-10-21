<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
include_once 'include/Webservices/DescribeObject.php';

class Mobile_WS_Describe extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		$current_user = $this->getActiveUser();
		
		$module = $request->get('module');
		$describeInfo = vtws_describe($module, $current_user);
		Mobile_WS_Utils::fixDescribeFieldInfo($module, $describeInfo);
		
		$response = new Mobile_API_Response();
		$response->setResult(array('describe' => $describeInfo));
		
		return $response;
	}
}