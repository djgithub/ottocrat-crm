<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_CronTasks_UpdateSequence_Action extends Settings_Ottocrat_Index_Action {

	public function process(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$sequencesList = $request->get('sequencesList');

		$moduleModel = Settings_CronTasks_Module_Model::getInstance($qualifiedModuleName);

		$response = new Ottocrat_Response();
		if ($sequencesList) {
			$moduleModel->updateSequence($sequencesList);
			$response->setResult(array(true));
		} else {
			$response->setError();
		}

		$response->emit();
	}

}