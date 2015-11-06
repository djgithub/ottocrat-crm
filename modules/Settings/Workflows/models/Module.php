<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

require_once 'modules/com_ottocrat_workflow/include.inc';
require_once 'modules/com_ottocrat_workflow/expression_engine/VTExpressionsManager.inc';

class Settings_Workflows_Module_Model extends Settings_Ottocrat_Module_Model {

	var $baseTable = 'com_ottocrat_workflows';
	var $baseIndex = 'workflow_id';
	var $listFields = array('summary' => 'Summary', 'module_name' => 'Module', 'execution_condition' => 'Execution Condition');
	var $name = 'Workflows';

	static $metaVariables = array(
		'Current Date' => '(general : (__OttocratMeta__) date) ($_DATE_FORMAT_)',
		'Current Time' => '(general : (__OttocratMeta__) time)',
		'System Timezone' => '(general : (__OttocratMeta__) dbtimezone)',
		'User Timezone' => '(general : (__OttocratMeta__) usertimezone)',
		'CRM Detail View URL' => '(general : (__OttocratMeta__) crmdetailviewurl)',
		'Portal Detail View URL' => '(general : (__OttocratMeta__) portaldetailviewurl)',
		'Site Url' => '(general : (__OttocratMeta__) siteurl)',
		'Portal Url' => '(general : (__OttocratMeta__) portalurl)',
		'Record Id' => '(general : (__OttocratMeta__) recordId)',
		'LBL_HELPDESK_SUPPORT_NAME' => '(general : (__OttocratMeta__) supportName)',
		'LBL_HELPDESK_SUPPORT_EMAILID' => '(general : (__OttocratMeta__) supportEmailid)',
	);

	static $triggerTypes = array(
		1 => 'ON_FIRST_SAVE',
		2 => 'ONCE',
		3 => 'ON_EVERY_SAVE',
		4 => 'ON_MODIFY',
        // Reserving 5 & 6 for ON_DELETE and ON_SCHEDULED types.
		6=>	 'ON_SCHEDULE'
	);

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public static function getDefaultUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module=Workflows&parent=Settings&view=List');
	}

	/**
	 * Function to get the url for create view of the module
	 * @return <string> - url
	 */
	public static function getCreateViewUrl() {
		return "javascript:Settings_Workflows_List_Js.triggerCreate('".Ottocrat_Request:: encryptLink("index.php?module=Workflows&parent=Settings&view=Edit")."')";
	}

	public static function getCreateRecordUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module=Workflows&parent=Settings&view=Edit');
	}

	public static function getSupportedModules() {
		$moduleModels = Ottocrat_Module_Model::getAll(array(0,2));
		$supportedModuleModels = array();
		foreach($moduleModels as $tabId => $moduleModel) {
			if($moduleModel->isWorkflowSupported() && $moduleModel->getName() != 'Webmails') {
				$supportedModuleModels[$tabId] = $moduleModel;
			}
		}
		return $supportedModuleModels;
	}

	public static function getTriggerTypes() {
		return self::$triggerTypes;
	}

	public static function getExpressions() {
		$db = PearDatabase::getInstance();

		$mem = new VTExpressionsManager($db);
		return $mem->expressionFunctions();
	}

	public static function getMetaVariables() {
		return self::$metaVariables;
	}

	public function getListFields() {
		if(!$this->listFieldModels) {
			$fields = $this->listFields;
			$fieldObjects = array();
			foreach($fields as $fieldName => $fieldLabel) {
				if($fieldName == 'module_name' || $fieldName == 'execution_condition') {
					$fieldObjects[$fieldName] = new Ottocrat_Base_Model(array('name' => $fieldName, 'label' => $fieldLabel, 'sort'=>false));
				} else {
					$fieldObjects[$fieldName] = new Ottocrat_Base_Model(array('name' => $fieldName, 'label' => $fieldLabel));
				}
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}
        
        /**
     * Function to get the count of active workflows
     * @return <Integer> count of active workflows
     */
    public function getActiveWorkflowCount(){
        $db = PearDatabase::getInstance();

		$query = 'SELECT count(*) AS count FROM com_ottocrat_workflows 
                  INNER JOIN ottocrat_tab ON ottocrat_tab.name = com_ottocrat_workflows.module_name 
                  AND ottocrat_tab.presence IN (0,2)';

		$result = $db->pquery($query, array());
		return $db->query_result($result, 0, 'count');
    }      
}
