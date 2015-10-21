<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_GetData_Action extends Ottocrat_IndexAjax_View {

	public function process(Ottocrat_Request $request) {
		$record = $request->get('record');
		$sourceModule = $request->get('source_module');
		$response = new Ottocrat_Response();

		$permitted = Users_Privileges_Model::isPermitted($sourceModule, 'DetailView', $record);
		if($permitted) {
			$recordModel = Ottocrat_Record_Model::getInstanceById($record, $sourceModule);
			$data = $recordModel->getData();
			$response->setResult(array('success'=>true, 'data'=>array_map('decode_html',$data)));
		} else {
			$response->setResult(array('success'=>false, 'message'=>vtranslate('LBL_PERMISSION_DENIED')));
		}
		$response->emit();
	}
}
