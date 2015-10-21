<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Mobile_UI_Logout extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		HTTP_Session::destroy(HTTP_Session::detectId());
		header('Location: index.php');
		exit;
	}

}