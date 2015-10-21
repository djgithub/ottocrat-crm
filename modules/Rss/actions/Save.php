<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
require_once('libraries/magpierss/rss_fetch.inc');

class Rss_Save_Action extends Ottocrat_Save_Action {

	public function process(Ottocrat_Request $request) {
        $response = new Ottocrat_Response();
		$moduleName = $request->getModule();
        $url = $request->get('feedurl');
        $recordModel = Rss_Record_Model::getCleanInstance($moduleName);
        $result = $recordModel->validateRssUrl($url);
        
        if($result) {
            $recordModel->save($url);
            $response->setResult(array('success' => true, 'message' => vtranslate('JS_RSS_SUCCESSFULLY_SAVED', $moduleName), 'id' => $recordModel->getId()));
        } else {
            $response->setResult(array('success' => false, 'message' => vtranslate('JS_INVALID_RSS_URL', $moduleName)));   
        }
        
        $response->emit();
	}
}
