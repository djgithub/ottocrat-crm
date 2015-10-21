<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

abstract class Ottocrat_Footer_View extends Ottocrat_Header_View {

	function __construct() {
		parent::__construct();
	}

	//Note: To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/*function preProcessParentTplName(Ottocrat_Request $request) {
		return parent::preProcessTplName($request);
	}*/

	/*function postProcess(Ottocrat_Request $request) {
		parent::postProcess($request);
	}*/
}
