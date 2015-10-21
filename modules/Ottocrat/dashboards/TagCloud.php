<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_TagCloud_Dashboard extends Ottocrat_IndexAjax_View {
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Ottocrat_Request $request
	 * @return <Array> - List of Ottocrat_JsScript_Model instances
	 */
	public function getHeaderScripts(Ottocrat_Request $request) {

		$jsFileNames = array(
			'~/libraries/jquery/jquery.tagcloud.js'
		);

		$headerScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $headerScriptInstances;
	}
	
	public function process(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		
		$linkId = $request->get('linkid');
		
		$widget = Ottocrat_Widget_Model::getInstance($linkId, $currentUser->getId());
		
		$tags = Ottocrat_Tag_Model::getAll($currentUser->id);

		//Include special script and css needed for this widget
		$viewer->assign('SCRIPTS',$this->getHeaderScripts($request));

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('TAGS', $tags);
		$viewer->assign('MODULE_NAME', $moduleName);
		
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/TagCloudContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TagCloud.tpl', $moduleName);
		}
		
	}
	
}