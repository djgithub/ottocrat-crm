<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_CheckMailBoxMaxLimit_Action extends Settings_Ottocrat_Index_Action {
	
	public function process(Ottocrat_Request $request) {
		$recordsCount = Settings_MailConverter_Record_Model::getCount();
		$qualifiedModuleName = $request->getModule(false);
		$response = new Ottocrat_Response();
		if ($recordsCount < 2) {
			$result = array(true);
			$response->setResult($result);
		} else {
			$response->setError(vtranslate('LBL_MAX_LIMIT_ONLY_TWO', $qualifiedModuleName));
		}
		$response->emit();
	}
}
?>
