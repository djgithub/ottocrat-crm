<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Google_MapAjax_Action extends Ottocrat_BasicAjax_Action {

    public function process(Ottocrat_Request $request) {
        switch ($request->get("mode")) {
            case 'getLocation':$result = $this->getLocation($request);
                break;
        }
        echo json_encode($result);
    }

    /**
     * get address for the record, based on the module type.
     * @param Ottocrat_Request $request
     * @return type 
     */
    function getLocation(Ottocrat_Request $request) {
        $address = Google_Map_Helper::getLocation($request);
        return empty($address) ? "" : array("address" => join(",", $address));
    }
    
    public function validateRequest(Ottocrat_Request $request) { 
        $request->validateReadAccess(); 
    } 

}

?>
