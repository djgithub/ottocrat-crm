<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Mobile_WS_TaxByType extends Mobile_WS_Controller{
    
    function process(Mobile_API_Request $request) {
		global $current_user;
		$response = new Mobile_API_Response();
		$current_user = $this->getActiveUser();

		$taxType = $request->get('taxType');
        
        	$result = $this->getTaxDetails($taxType);
		$response->setResult($result);

		return $response;
	}
    
    protected function getTaxDetails($taxType){
       global $adb;
       $tableName = $this->getTableName($taxType);
       $result = $adb->pquery("SELECT * FROM $tableName WHERE deleted = 0", array());
       $rowCount =  $adb->num_rows($result);
        if($rowCount){
            for($i = 0; $i < $rowCount; $i++){
                $row = $adb->query_result_rowdata($result, $i);
                $recordDetails[] = $row;
            }
        }
        return $recordDetails;
    }
    
    protected function getTableName($taxType){
        switch($taxType){
            case 'shipping':
                return 'ottocrat_shippingtaxinfo';
                break;
            case 'inventory':
                return 'ottocrat_inventorytaxinfo';
                break;
        }
    }
}
