<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Documents_Detail_View extends Ottocrat_Detail_View {
	
	function preProcess(Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('NO_SUMMARY', true);
		parent::preProcess($request);
	}
	
	/**
	 * Function to get Ajax is enabled or not
	 * @param Ottocrat_Record_Model record model
	 * @return <boolean> true/false
	 */
	public function isAjaxEnabled($recordModel) {
		return false;
	}

	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	 */
	function showModuleBasicView($request) {
		return $this->showModuleDetailView($request);
	}

}