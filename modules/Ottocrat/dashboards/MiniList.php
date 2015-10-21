<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_MiniList_Dashboard extends Ottocrat_IndexAjax_View {

	public function process(Ottocrat_Request $request, $widget=NULL) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->get('widgetid');
		}

		$widget = Ottocrat_Widget_Model::getInstanceWithWidgetId($widgetId, $currentUser->getId());

		$minilistWidgetModel = new Ottocrat_MiniList_Model();
		$minilistWidgetModel->setWidgetModel($widget);

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MINILIST_WIDGET_MODEL', $minilistWidgetModel);
		$viewer->assign('BASE_MODULE', $minilistWidgetModel->getTargetModule());
                $viewer->assign('SCRIPTS', $this->getHeaderScripts());

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/MiniListContents.tpl', $moduleName);
		} else {
			$widget->set('title', $minilistWidgetModel->getTitle());

			$viewer->view('dashboards/MiniList.tpl', $moduleName);
		}

	}
        
        function getHeaderScripts() {
        return $this->checkAndConvertJsScripts(array('modules.Emails.resources.MassEdit'));
	}
}