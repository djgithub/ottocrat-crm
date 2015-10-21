<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */
vimport('~~/modules/WSAPP/synclib/controllers/SynchronizeController.php');

class Google_Calendar_Controller  extends WSAPP_SynchronizeController{
    public function getTargetConnector() {
        $oauth2Connector = new Google_Oauth2_Connector("Calendar",$this->user->id);
        $oauth2Connection = $oauth2Connector->authorize();
        $connector =  new Google_Calendar_Connector($oauth2Connection);
        $connector->setSynchronizeController($this);
        return $connector;
    }
    
	public function getSourceConnector() {
         $connector = new Google_Ottocrat_Connector();
         $connector->setSynchronizeController($this);
         $targetName = $this->targetConnector->getName();
		if(empty($targetName)){
			throw new Exception('Target Name cannot be empty');
		}
         return $connector->setName('Ottocrat_'.$targetName);
     }
	
    public function getSyncType() {
        return WSAPP_SynchronizeController::WSAPP_SYNCHRONIZECONTROLLER_USER_SYNCTYPE;
    }
    
    public function getSourceType() {
        return 'Events';
    }
}
