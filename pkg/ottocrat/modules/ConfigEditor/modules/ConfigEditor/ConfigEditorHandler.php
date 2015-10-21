<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
class ConfigEditorHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {

		if($eventName == 'ottocrat.entity.beforesave') {
			// Entity is about to be saved, take required action
		}

		if($eventName == 'ottocrat.entity.aftersave') {
			// Entity has been saved, take next action
		}
	}
}

?>