<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Google_Map_Helper {

    /**
     * get the location for the record based on the module type
     * @param type $request
     * @return type
     */
    static function getLocation($request) {
        $recordId = $request->get('recordid');
        $module = $request->get('source_module');
        $locationFields = self::getLocationFields($module);
        $address = array();
        if (!empty($locationFields)) {
            $recordModel = Ottocrat_Record_Model::getInstanceById($recordId, $module);
            foreach ($locationFields as $key => $value) {
                $address[$key] = Ottocrat_Util_Helper::getDecodedValue($recordModel->get($value));
            }
        }
        return $address;
    }

    /**
     * get location values for:
     * street, city, country
     * @param type $module
     * @return type
     */
    static function getLocationFields($module) {
        switch ($module) {
            case 'Contacts': return array('street' => 'mailingstreet', 'city' => 'mailingcity', 'country' => 'mailingcountry');
                break;
            case 'Leads' : return array('street' => 'lane', 'city' => 'city', 'country' => 'country');
                break;
	    case 'Accounts' : return array('street' => 'bill_street', 'city' => 'bill_city', 'country' => 'bill_country');
		break;
            default : return array();
                break;
        }
    }

}

?>
