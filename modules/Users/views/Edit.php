<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

Class Users_Edit_View extends Users_PreferenceEdit_View {

	public function preProcess(Ottocrat_Request $request) {
		parent::preProcess($request, false);
		$this->preProcessSettings($request);
	}

	public function preProcessSettings(Ottocrat_Request $request) {

		$username=$_SESSION['username'];
		$add_user_flg		=	0;

		if($username!='') {
			global $adb, $WP_USER,$WP_DB,$WP_PASSWORD,$OT_USER,$OT_DB,$OT_PASSWORD;
			$adb->disconnect();
			$adb->resetSettings('mysqli', 'localhost', $WP_DB, $WP_USER, $WP_PASSWORD);
			$Query = "SELECT users FROM logicint_tlrsite.tbl_user u inner join logicint_tlrsite.tbl_packagemaster p on u
		.package_id=p.package_id  WHERE username='$username'";
			$adb->checkConnection();
			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$Result = $adb->pquery($Query, array());
			$maxuser_cnt = $adb->query_result($Result, 0, "users");

			$adb->disconnect();
			$adb->resetSettings('mysqli', 'localhost', $OT_DB, $OT_USER, $OT_PASSWORD);

			$uQuery = "SELECT count(*) as usercnt FROM ottocrat_users";
			$adb->checkConnection();
			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$uResult = $adb->pquery($uQuery, array());
			$user_cnt = $adb->query_result($uResult, 0, "usercnt");


			if ($maxuser_cnt > $user_cnt || $maxuser_cnt == 0) $add_user_flg = 1;

		}else
		{
			$add_user_flg 		= 1;
		}
		if(!$add_user_flg & $_REQUEST['record']=='')
		{
			header('Location:'.Ottocrat_Request:: encryptLink('index.php?module=Users&parent=Settings&view=List'));
			exit();
		}
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$selectedMenuId = $request->get('block');
		$fieldId = $request->get('fieldid');

		$settingsModel = Settings_Ottocrat_Module_Model::getInstance();
		$menuModels = $settingsModel->getMenus();

		if(!empty($selectedMenuId)) {
			$selectedMenu = Settings_Ottocrat_Menu_Model::getInstanceById($selectedMenuId);
		} elseif(!empty($moduleName) && $moduleName != 'Ottocrat') {
			$fieldItem = Settings_Ottocrat_Index_View::getSelectedFieldFromModule($menuModels,$moduleName);
			if($fieldItem){
				$selectedMenu = Settings_Ottocrat_Menu_Model::getInstanceById($fieldItem->get('blockid'));
				$fieldId = $fieldItem->get('fieldid');
			} else {
				reset($menuModels);
				$firstKey = key($menuModels);
				$selectedMenu = $menuModels[$firstKey];
			}
		} else {
			reset($menuModels);
			$firstKey = key($menuModels);
			$selectedMenu = $menuModels[$firstKey];
		}

		$viewer->assign('SELECTED_FIELDID',$fieldId);
		$viewer->assign('SELECTED_MENU', $selectedMenu);
		$viewer->assign('SETTINGS_MENUS', $menuModels);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('LOAD_OLD', Settings_Ottocrat_Index_View::$loadOlderSettingUi);
		$viewer->assign('IS_PREFERENCE', false);

		$viewer->view('SettingsMenuStart.tpl', $qualifiedModuleName);
	}

	public function postProcessSettings(Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->view('SettingsMenuEnd.tpl', $qualifiedModuleName);
	}

	public function postProcess(Ottocrat_Request $request) {
		$this->postProcessSettings($request);
		parent::postProcess($request);
	}
	
	public function getHeaderScripts(Ottocrat_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Settings.Ottocrat.resources.Index'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	public function process(Ottocrat_Request $request) {
		parent::process($request);
	}
}