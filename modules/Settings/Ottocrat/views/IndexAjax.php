<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_IndexAjax_View extends Settings_Ottocrat_Index_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('getSettingsShortCutBlock');
                $this->exposeMethod('realignSettingsShortCutBlock');
	}
	
	public function preProcess (Ottocrat_Request $request) {
		return;
	}

	public function postProcess (Ottocrat_Request $request) {
		return;
	}
	
	public function process (Ottocrat_Request $request) {
		$mode = $request->getMode();

		if($mode){
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	public function getSettingsShortCutBlock(Ottocrat_Request $request) {
		$fieldid = $request->get('fieldid');
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$pinnedSettingsShortcuts = Settings_Ottocrat_MenuItem_Model::getPinnedItems();
		$viewer->assign('SETTINGS_SHORTCUT',$pinnedSettingsShortcuts[$fieldid]);
		$viewer->assign('MODULE',$qualifiedModuleName);
		$viewer->view('SettingsShortCut.tpl', $qualifiedModuleName);
	}
        
        public function realignSettingsShortCutBlock(Ottocrat_Request $request){
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$pinnedSettingsShortcuts = Settings_Ottocrat_MenuItem_Model::getPinnedItems();
		$viewer->assign('SETTINGS_SHORTCUT',$pinnedSettingsShortcuts);
		$viewer->assign('MODULE',$qualifiedModuleName);
		$viewer->view('ReAlignSettingsShortCut.tpl', $qualifiedModuleName);
	}
}