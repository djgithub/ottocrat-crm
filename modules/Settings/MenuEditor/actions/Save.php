<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MenuEditor_Save_Action extends Settings_Ottocrat_Index_Action {

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule(false);
		$menuEditorModuleModel = Settings_Ottocrat_Module_Model::getInstance($moduleName);
		$selectedModulesList = $request->get('selectedModulesList');

		if ($selectedModulesList) {
			$menuEditorModuleModel->set('selectedModulesList', $selectedModulesList);
			$menuEditorModuleModel->saveMenuStruncture();
		}
		$loadUrl = $menuEditorModuleModel->getIndexViewUrl();
		header("Location: $loadUrl");
	}

        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
