<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: ottocrat CRM Open source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_MainUI_View extends MailManager_Abstract_View {

    /**
     * Process the request for displaying UI
     * @global String $moduleName
     * @param Ottocrat_Request $request
     * @return MailManager_Response
     */
	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$response = new Ottocrat_Response();
		$viewer = $this->getViewer($request);
		if($this->getOperationArg($request) == "_quicklinks") {
			$content = $viewer->view('MainuiQuickLinks.tpl', $moduleName, true);
			$response->setResult( array('ui' => $content));
			return $response;
		} else {
			if ($this->hasMailboxModel()) {
				$connector = $this->getConnector();

				if ($connector->hasError()) {
					$viewer->assign('ERROR', $connector->lastError());
				} else {
					$folders = $connector->folders();
					$connector->updateFolders();
					$viewer->assign('FOLDERS', $folders);
				}
				$this->closeConnector();
			}
			$viewer->assign('MODULE', $moduleName);
			$content = $viewer->view('Mainui.tpl', $moduleName, true);
			$response->setResult( array('mailbox' => $this->hasMailboxModel(), 'ui' => $content));
			return $response;
		}
	}
        
        public function validateRequest(Ottocrat_Request $request) {
            return $request->validateReadAccess(); 
        } 
}
?>