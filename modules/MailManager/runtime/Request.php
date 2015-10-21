<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: ottocrat CRM Open source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_Request extends Ottocrat_Request {

	public function get($key, $defvalue = '') {
		return urldecode(parent::get($key, $defvalue));
	}

	public static function getInstance($request) {
		return new MailManager_Request($request->getAll(), $request->getAll());
	}
}