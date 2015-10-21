<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once 'include/events/VTEventHandler.inc';

class Ottocrat_RecordLabelUpdater_Handler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		global $adb;

		if ($eventName == 'ottocrat.entity.aftersave') {
            $module = $data->getModuleName();
            if($module != "Users"){
                $labelInfo = getEntityName($module, $data->getId());

			if ($labelInfo) {
				$label = decode_html($labelInfo[$data->getId()]);
				$adb->pquery('UPDATE ottocrat_crmentity SET label=? WHERE crmid=?', array($label, $data->getId()));
			}
            }
		}
	}
}