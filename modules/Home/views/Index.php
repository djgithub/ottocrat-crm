<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Home_Index_View extends Ottocrat_Index_View {

	function process (Ottocrat_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		//$viewer->assign('HOME_PAGES', Home_Page_Model::getAll());
		//$viewer->assign('HOME_PAGE_WIDGETS', Home_Widget_Model::getAll());

		$viewer->view('Index.tpl', $moduleName);
	}
}