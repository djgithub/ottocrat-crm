<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_ShowWidget_View extends Ottocrat_IndexAjax_View {

	function checkPermission(Ottocrat_Request $request) {
		return true;
	}

	function process(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$moduleName = $request->getModule();
		$componentName = $request->get('name');
		$linkId = $request->get('linkid');
		if(!empty($componentName)) {
			$className = Ottocrat_Loader::getComponentClassName('Dashboard', $componentName, $moduleName);
			if(!empty($className)) {
				$widget = NULL;
				if(!empty($linkId)) {
					$widget = new Ottocrat_Widget_Model();
					$widget->set('linkid', $linkId);
					$widget->set('userid', $currentUser->getId());
					$widget->set('filterid', $request->get('filterid', NULL));
					if ($request->has('data')) {
						$widget->set('data', $request->get('data'));
					}
					$widget->add();
				}
				$classInstance = new $className();
				$classInstance->process($request, $widget);
				return;
			}
	}

		$response = new Ottocrat_Response();
		$response->setResult(array('success'=>false,'message'=>  vtranslate('NO_DATA')));
		$response->emit();
	}
        
        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}