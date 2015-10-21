<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Products_SaveAjax_Action extends Ottocrat_SaveAjax_Action {

	public function process(Ottocrat_Request $request) {
		//the new values are added to $_REQUEST for Ajax Save, are removing the Tax details depend on the 'ajxaction' value
		$_REQUEST['ajxaction'] = 'DETAILVIEW';
		$request->set('ajaxaction', 'DETAILVIEW');
		parent::process($request);
	}
}
