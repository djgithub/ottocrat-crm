<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'includes/runtime/Viewer.php';

class Mobile_UI_Viewer extends Ottocrat_Viewer{

	private $parameters = array();
	function assign($key, $value) {
		$this->parameters[$key] = $value;
	}

	function viewController() {
		$smarty = new Ottocrat_Viewer();

		foreach($this->parameters as $k => $v) {
			$smarty->assign($k, $v);
		}

		$smarty->assign("IS_SAFARI", Mobile::isSafari());
		$smarty->assign("SKIN", Mobile::config('Default.Skin'));
		return $smarty;
	}

	function process($templateName) {
		$smarty = $this->viewController();
		$response = new Mobile_API_Response();
		$response->setResult($smarty->fetch(vtlib_getModuleTemplate('Mobile', $templateName)));
		return $response;
	}

}