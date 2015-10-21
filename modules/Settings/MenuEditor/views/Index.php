<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MenuEditor_Index_View extends Settings_Ottocrat_Index_View {

	public function process(Ottocrat_Request $request) {
		$allModelsList = Ottocrat_Menu_Model::getAll(true);
		$menuModelStructure = Ottocrat_MenuStructure_Model::getInstanceFromMenuList($allModelsList);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('ALL_MODULES', $menuModelStructure->getMore());
		$viewer->assign('SELECTED_MODULES', $menuModelStructure->getTop());
		$viewer->assign('MODULE_NAME', $moduleName);
		
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
