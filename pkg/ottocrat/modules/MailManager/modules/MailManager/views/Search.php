<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: ottocrat CRM Open source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_Search_View extends MailManager_Relation_View {

	/**
	 * Processes the request for search Operation
	 * @global <type> $currentUserModel
	 * @param Ottocrat_Request $request
	 * @return boolean
	 */
	public function process(Ottocrat_Request $request) {

		$response = new Ottocrat_Response(true);
		$viewer = $this->getViewer($request);
		if ('popupui' == $this->getOperationArg($request)) {
			$viewer->view( 'Search.Popupui.tpl', 'MailManager' );
			$response = false;

		} else if ('email' == $this->getOperationArg($request)) {
			$searchTerm = $request->get('q');
			if (empty($searchTerm)) $searchTerm = '%@'; // To avoid empty value of email to be filtered.
			else $searchTerm = "%$searchTerm%";

			$filteredResult = MailManager::lookupMailInOttocrat($searchTerm, Users_Record_Model::getCurrentUserModel());

			MailManager_Utils_Helper::emitJSON($filteredResult);
			$response = false;
		}
		return $response;
	}
}

?>