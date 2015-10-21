<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
require_once('Smarty_setup.php');

class ConfigEditor_Viewer extends ottocratCRM_Smarty {
	function ConfigEditor_Viewer() {
		parent::ottocratCRM_Smarty();
		
		global $app_strings, $mod_strings, $currentModule, $theme;
		
		$this->assign('CUSTOM_MODULE', true);

		$this->assign('APP', $app_strings);
		$this->assign('MOD', $mod_strings);
		$this->assign('MODULE', $currentModule);
		// TODO: Update Single Module Instance name here.
		$this->assign('SINGLE_MOD', 'SINGLE_'.$currentModule); 
		$this->assign('CATEGORY', 'Settings');
		$this->assign('IMAGE_PATH', "themes/$theme/images/");
		$this->assign('THEME', $theme);
	}
}
?>