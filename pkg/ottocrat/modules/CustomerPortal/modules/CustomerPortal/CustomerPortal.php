<?php
/*+********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *******************************************************************************/

class CustomerPortal {

 	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {

		require_once('include/utils/utils.php');
		global $adb,$mod_strings;

 		if($eventType == 'module.postinstall') {
			$portalModules = array("HelpDesk","Faq","Invoice","Quotes","Products","Services","Documents",
									"Contacts","Accounts","Project","ProjectTask","ProjectMilestone","Assets");

			$query = "SELECT max(sequence) AS max_tabseq FROM ottocrat_customerportal_tabs";
			$res = $adb->pquery($query,array());
			$tabseq = $adb->query_result($res,0,'max_tabseq');
			$i = ++$tabseq;
			foreach($portalModules as $module) {
				$tabIdResult = $adb->pquery('SELECT tabid FROM ottocrat_tab WHERE name=?', array($module));
				$tabId = $adb->query_result($tabIdResult, 0, 'tabid');
				if($tabId) {
					++$i;
					$adb->query("INSERT INTO ottocrat_customerportal_tabs (tabid,visible,sequence) VALUES ($tabId,1,$i)");
					$adb->query("INSERT INTO ottocrat_customerportal_prefs(tabid,prefkey,prefvalue) VALUES ($tabId,'showrelatedinfo',1)");
				}
			}

			$adb->query("INSERT INTO ottocrat_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (0,'userid',1)");
			$adb->query("INSERT INTO ottocrat_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (0,'defaultassignee',1)");

			// Mark the module as Standard module
			$adb->pquery('UPDATE ottocrat_tab SET customized=0 WHERE name=?', array($moduleName));

			$fieldid = $adb->getUniqueID('ottocrat_settings_field');
			$blockid = getSettingsBlockId('LBL_OTHER_SETTINGS');
			$seq_res = $adb->pquery("SELECT max(sequence) AS max_seq FROM ottocrat_settings_field WHERE blockid = ?", array($blockid));
			if ($adb->num_rows($seq_res) > 0) {
				$cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
				if ($cur_seq != null)	$seq = $cur_seq + 1;
			}

			$adb->pquery('INSERT INTO ottocrat_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence)
				VALUES (?,?,?,?,?,?,?)', array($fieldid, $blockid, 'LBL_CUSTOMER_PORTAL', 'portal_icon.png', 'PORTAL_EXTENSION_DESCRIPTION', Ottocrat_Request:: encryptLink('index.php?module=CustomerPortal&action=index&parenttab=Settings'), $seq));


		} else if($eventType == 'module.disabled') {
		// TODO Handle actions when this module is disabled.
		} else if($eventType == 'module.enabled') {
		// TODO Handle actions when this module is enabled.
		} else if($eventType == 'module.preuninstall') {
		// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
		// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
		// TODO Handle actions after this module is updated.
		}
 	}
}
?>
