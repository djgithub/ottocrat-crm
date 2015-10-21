<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_RemoveWidget_Action extends Ottocrat_IndexAjax_View {

	public function process(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$linkId = $request->get('linkid');
		$response = new Ottocrat_Response();
		
		if ($request->has('widgetid')) {
			$widget = Ottocrat_Widget_Model::getInstanceWithWidgetId($request->get('widgetid'), $currentUser->getId());
		} else {
			$widget = Ottocrat_Widget_Model::getInstance($linkId, $currentUser->getId());
		}

		if (!$widget->isDefault()) {
			$widget->remove();
			$response->setResult(array('linkid' => $linkId, 'name' => $widget->getName(), 'url' => $widget->getUrl(), 'title' => vtranslate($widget->getTitle(), $request->getModule())));
		} else {
			$response->setError(vtranslate('LBL_CAN_NOT_REMOVE_DEFAULT_WIDGET', $moduleName));
		}
		$response->emit();
	}
        
        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}
