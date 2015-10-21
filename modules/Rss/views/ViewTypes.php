<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Rss_ViewTypes_View extends Ottocrat_IndexAjax_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('getRssWidget');
        $this->exposeMethod('getRssAddForm');
    }
        
	/**
     * Function to display rss sidebar widget
     * @param <Ottocrat_Request> $request 
     */
    public function getRssWidget(Ottocrat_Request $request) {
        $module = $request->get('module');
        $moduleModel = Ottocrat_Module_Model::getInstance($module);
        $rssSources = $moduleModel->getRssSources();
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $module);
        $viewer->assign('RSS_SOURCES', $rssSources);
        echo $viewer->view('RssWidgetContents.tpl', $module, true);
    }
    
    /**
     * Function to get the rss add form 
     * @param <Ottocrat_Request> $request
     */
    public function getRssAddForm(Ottocrat_Request $request) { 
        $module = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($module);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE',$module);
        $viewer->view('RssAddForm.tpl', $module);
    }
   
}
