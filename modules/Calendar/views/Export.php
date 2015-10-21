<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_Export_View extends Ottocrat_Export_View {

	public function preprocess(Ottocrat_Request $request) {
	}

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ACTION', 'ExportData');
		
		$viewer->view('Export.tpl', $moduleName);
	}

	public function postprocess(Ottocrat_Request $request) {
	}
}