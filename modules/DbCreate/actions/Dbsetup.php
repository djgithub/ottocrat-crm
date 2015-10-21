<?php
/*+**********************************************************************************
 * The contents of this file are subject to the Ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  Ottocrat CRM Open Source
 * The Initial Developer of the Original Code is Dnyandev.
 * Portions created by Ottocrat are Copyright (C) Ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Dbcreate_Dbsetup_Action extends Ottocrat_Action_Controller {

    function loginRequired() {
        return false;
    }


    function checkPermission(Ottocrat_Request $request) {
        return true;
    }

    function process(Ottocrat_Request $request) {


        echo 'I am successfully here';
        global $adb; print_r($adb);
      #  $adb->resetSettings('mysqli', 'localhost', 'dipali','dipali', 'pass123');
        $adb->createTables("schema/products.xml");

    }

}
