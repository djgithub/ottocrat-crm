<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class SMSNotifier_Module_Model extends Ottocrat_Module_Model {

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	public function isQuickCreateSupported() {
		//SMSNotifier module is not enabled for quick create
		return false;
	}
	
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}

	/**
	 * Function to get the module is permitted to specific action
	 * @param <String> $actionName
	 * @return <boolean>
	 */
	public function isPermitted($actionName) {
		if ($actionName === 'EditView') {
			return false;
		}
		return Users_Privileges_Model::isPermitted($this->getName(), $actionName);
	}
    
    
    /**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks(){
		vimport('~~modules/com_ottocrat_workflow/VTWorkflowUtils.php');

		$editWorkflowsImagePath = Ottocrat_Theme::getImagePath('EditWorkflows.png');
		$settingsLinks = array();


		if(VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_WORKFLOWS',
                    'linkurl' =>Ottocrat_Request:: encryptLink( 'index.php?parent=Settings&module=Workflows&view=List&sourceModule='.$this->getName()),
					'linkicon' => $editWorkflowsImagePath
			);
		}
		
        $settingsLinks[] =  array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => vtranslate('LBL_SERVER_CONFIG', $moduleName),
					'linkurl' => Ottocrat_Request:: encryptLink('index	.php?module=SMSNotifier&parent=Settings&view=List'),
					'linkicon' => ''
				);
		return $settingsLinks;
	}

}
?>
