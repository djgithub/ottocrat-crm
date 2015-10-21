<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Google_SaveSettings_Action extends Ottocrat_BasicAjax_Action {

    public function process(Ottocrat_Request $request) {
        $sourceModule = $request->get('sourcemodule');
        $fieldMapping = $request->get('fieldmapping');
        Google_Utils_Helper::saveSettings($request);
        Google_Utils_Helper::saveFieldMappings($sourceModule, $fieldMapping);
        $response = new Ottocrat_Response;
        $result = array('settingssaved' => true);
        $response->setResult($result);
        $response->emit();
    }
    
}

?>