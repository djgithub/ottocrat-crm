<?php
/*+********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *******************************************************************************/
 
class CronTasks {
 	
 	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
 					
		require_once('include/utils/utils.php');			
		global $adb,$mod_strings;

		if($eventType == 'module.postinstall') {
		$fieldid = $adb->getUniqueID('ottocrat_settings_field');
		$blockid = getSettingsBlockId('LBL_OTHER_SETTINGS');
		$seq_res = $adb->pquery("SELECT max(sequence) AS max_seq FROM ottocrat_settings_field WHERE blockid = ?", array($blockid));
			if ($adb->num_rows($seq_res) > 0) {
				$cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
				if ($cur_seq != null)	$seq = $cur_seq + 1;
			}
			
		$adb->pquery('INSERT INTO ottocrat_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence) 
				VALUES (?,?,?,?,?,?,?)', array($fieldid, $blockid, 'Scheduler', 'Cron.png', 'Allows you to Configure Cron Task', Ottocrat_Request:: encryptLink('index.php?module=CronTasks&action=ListCronJobs&parenttab=Settings'), $seq));
		}	
	}
}
