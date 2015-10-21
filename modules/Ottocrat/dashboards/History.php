<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_History_Dashboard extends Ottocrat_IndexAjax_View {

	public function process(Ottocrat_Request $request) {
		$LIMIT = 10;
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);

		$moduleName = $request->getModule();
		$type = $request->get('type');
		$page = $request->get('page');
		$linkId = $request->get('linkid');
                if( empty($page)) { $page=1; }
		$pagingModel = new Ottocrat_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', $LIMIT);

		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$history = $moduleModel->getHistory($pagingModel, $type);
		$widget = Ottocrat_Widget_Model::getInstance($linkId, $currentUser->getId());
		$modCommentsModel = Ottocrat_Module_Model::getInstance('ModComments'); 

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('HISTORIES', $history);
		$viewer->assign('PAGE', $page);
		$viewer->assign('NEXTPAGE', (count($history) < $LIMIT)? 0 : $page+1);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel); 
		
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/HistoryContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/History.tpl', $moduleName);
		}
	}
}
