<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class PBXManager_Module_Model extends Ottocrat_Module_Model {

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	public function isQuickCreateSupported() {
		//PBXManager module is not enabled for quick create
		return false;
	}

	public function isWorkflowSupported() {
		return true;
	}
    
    /**
	 * Overided to make editview=false for this module
	 */
	public function isPermitted($actionName) {
        if($actionName == 'EditView')
            return false;
        else
            return ($this->isActive() && Users_Privileges_Model::isPermitted($this->getName(), $actionName));
	}
    
    /**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks(){
               if(!$this->isEntityModule()) {
            return array();
        }
		vimport('~~modules/com_ottocrat_workflow/VTWorkflowUtils.php');

		$layoutEditorImagePath = Ottocrat_Theme::getImagePath('LayoutEditor.gif');
		$editWorkflowsImagePath = Ottocrat_Theme::getImagePath('EditWorkflows.png');
		$settingsLinks = array();

		if(VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_WORKFLOWS',
                    'linkurl' => Ottocrat_Request:: encryptLink('index.php?parent=Settings&module=Workflows&view=List&sourceModule='.$this->getName()),
					'linkicon' => $editWorkflowsImagePath
			);
		}

        $settingsLinks[] = array(
                    'linktype' => 'LISTVIEWSETTINGS',
                    'linklabel'=> 'LBL_SERVER_CONFIGURATION',
                    'linkurl' => Ottocrat_Request:: encryptLink('index.php?parent=Settings&module=PBXManager&view=Index'),
                    'linkicon'=> ''
        );
		return $settingsLinks;
	}
    
    /**
     * Funxtion to identify if the module supports quick search or not
     */
    public function isQuickSearchEnabled() {
        return false;
    }
    
    public function isListViewNameFieldNavigationEnabled() {
        return false;
    }
}
?>
