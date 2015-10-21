<?php
require_once('include/utils/utils.php');
require_once 'vtlib/Ottocrat/Module.php';
require_once dirname(__FILE__) .'/ModTracker.php';
class ModTrackerUtils
{
	static function modTrac_changeModuleVisibility($tabid,$status) {
		if($status == 'module_disable'){
			ModTracker::disableTrackingForModule($tabid);
		} else {
			ModTracker::enableTrackingForModule($tabid);
		}
	}
	function modTrac_getModuleinfo(){
		global $adb;
		$query = $adb->pquery("SELECT ottocrat_modtracker_tabs.visible,ottocrat_tab.name,ottocrat_tab.tabid
								FROM ottocrat_tab
								LEFT JOIN ottocrat_modtracker_tabs ON ottocrat_modtracker_tabs.tabid = ottocrat_tab.tabid
								WHERE ottocrat_tab.isentitytype = 1 AND ottocrat_tab.name NOT IN('Emails', 'Webmails')",array());
		$rows = $adb->num_rows($query);

        for($i = 0;$i < $rows; $i++){
			$infomodules[$i]['tabid']  = $adb->query_result($query,$i,'tabid');
			$infomodules[$i]['visible']  = $adb->query_result($query,$i,'visible');
			$infomodules[$i]['name'] = $adb->query_result($query,$i,'name');
		}

		return $infomodules;
	}
}
?>
