<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Leads_MappingDelete_Action extends Settings_Ottocrat_Index_Action {

	public function process(Ottocrat_Request $request) {
		$recordId = $request->get('mappingId');
		$qualifiedModuleName = $request->getModule(false);

		$response = new Ottocrat_Response();
		if ($recordId) {
			Settings_Leads_Mapping_Model::deleteMapping(array($recordId));
			$response->setResult(array(vtranslate('LBL_DELETED_SUCCESSFULLY', $qualifiedModuleName)));
		} else {
			$response->setError(vtranslate('LBL_INVALID_MAPPING', $qualifiedModuleName));
		}
		$response->emit();
	}
}