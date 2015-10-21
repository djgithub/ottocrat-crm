<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_Index_View extends Ottocrat_Basic_View {

    //Variables which decalres whether the older setting need to be loaded or new one
    public static $loadOlderSettingUi = false;

	function __construct() {
		parent::__construct();
	}
	
	function checkPermission(Ottocrat_Request $request) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if(!$currentUserModel->isAdminUser()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Ottocrat'));
		}
	}

	protected function transformToUI5URL(Ottocrat_Request $request) {
		$params = 'module=Settings&action=index';

		if ($request->has('item')) {
			switch ($request->get('item')) {
				case 'LayoutEditor':
					$params = 'module=Settings&action=LayoutBlockList&parenttab=Settings&formodule='.$request->get('source_module');
					break;
				case 'EditWorkflows':
					$params = 'module=com_ottocrat_workflow&action=workflowlist&list_module='.$request->get('source_module');
					break;
				case 'PicklistEditor':
					$params = 'module=PickList&action=PickList&parenttab=Settings&moduleName='.$request->get('source_module');
					break;
				case 'SMSServerConfig':
					$params = 'module='. $request->get('source_module').'&action=SMSConfigServer&parenttab=Settings&formodule='.$request->get('source_module');
					break;
				case 'CustomFieldList':
					$params = 'module=Settings&action=CustomFieldList&parenttab=Settings&formodule='.$request->get('source_module');
					break;
				case 'GroupDetailView':
					$params = 'module=Settings&action=GroupDetailView&groupId='.$request->get('groupId');
					break;
				case 'ModuleManager' :
					$params = 'module=Settings&action=ModuleManager&parenttab=Settings';
					break;
				case 'MailScanner':
					$params = 'module=Settings&action=MailScanner&parenttab=Settings';
					break;
				case 'WebForms':
					$params = 'module=Webforms&action=index&parenttab=Settings';
					break;
				case 'CustomFields' :
					$params = 'module=Settings&action=CustomFieldList&parenttab=Settings&formodule='.$request->get('source_module');
					break;
			}
		}
		return '../index.php?' . Ottocrat_Request:: encryptLink($params);
	}

	public function preProcess (Ottocrat_Request $request) {
		parent::preProcess($request, false);
		$this->preProcessSettings($request);
	}

	public function preProcessSettings (Ottocrat_Request $request) {

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

		if(Settings_Ottocrat_Index_View::$loadOlderSettingUi) {
            // Customization
            $viewer->assign('UI5_URL', $this->transformToUI5URL($request));
            // END
        }

		$viewer->assign('SELECTED_FIELDID',$fieldId);
		$viewer->assign('SELECTED_MENU', $selectedMenu);
		$viewer->assign('SETTINGS_MENUS', $menuModels);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
                $viewer->assign('LOAD_OLD', Settings_Ottocrat_Index_View::$loadOlderSettingUi);
		$viewer->view('SettingsMenuStart.tpl', $qualifiedModuleName);
	}

	public function postProcessSettings (Ottocrat_Request $request) {

		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->view('SettingsMenuEnd.tpl', $qualifiedModuleName);
	}

	public function postProcess (Ottocrat_Request $request) {
		$this->postProcessSettings($request);
		parent::postProcess($request);
	}

	public function process(Ottocrat_Request $request) {
        if( !Settings_Ottocrat_Index_View::$loadOlderSettingUi) {
            //NOTE: We plan to embed UI5 Settings until we are complete.
            $viewer = $this->getViewer($request);
            $qualifiedModuleName = $request->getModule(false);
			$usersCount = Users_Record_Model::getCount(true);
			$activeWorkFlows = Settings_Workflows_Module_Model::getActiveWorkflowCount();
			$activeModules = Settings_ModuleManager_Module_Model::getModulesCount(true);
			$pinnedSettingsShortcuts = Settings_Ottocrat_MenuItem_Model::getPinnedItems();

			$viewer->assign('USERS_COUNT',$usersCount);
			$viewer->assign('ACTIVE_WORKFLOWS',$activeWorkFlows);
			$viewer->assign('ACTIVE_MODULES',$activeModules);
			$viewer->assign('SETTINGS_SHORTCUTS',$pinnedSettingsShortcuts);
			$viewer->assign('MODULE',$qualifiedModuleName);
            $viewer->view('Index.tpl', $qualifiedModuleName);
        }
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Ottocrat_Request $request
	 * @return <Array> - List of Ottocrat_JsScript_Model instances
	 */
	function getHeaderScripts(Ottocrat_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Ottocrat.resources.Ottocrat',
			'modules.Settings.Ottocrat.resources.Ottocrat',
			'modules.Settings.Ottocrat.resources.Edit',
			"modules.Settings.$moduleName.resources.$moduleName",
			'modules.Settings.Ottocrat.resources.Index',
			"modules.Settings.$moduleName.resources.Index",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	public static function getSelectedFieldFromModule($menuModels,$moduleName) {
		if($menuModels) {
			foreach($menuModels  as $menuModel) {
				$menuItems = $menuModel->getMenuItems();
				foreach($menuItems as $item) {
					$linkTo = $item->getUrl();
					if(stripos($linkTo, '&module='.$moduleName) !== false || stripos($linkTo, '?module='.$moduleName) !== false) {
						return $item;
					}
				}
			}
		}
		return false;
	}
        
        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateReadAccess(); 
        }
}
