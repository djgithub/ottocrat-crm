<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

	function vtws_extendSession(){
		global $adb,$API_VERSION,$application_unique_key;
		if(isset($_SESSION["authenticated_user_id"]) && $_SESSION["app_unique_key"] == $application_unique_key){
			$userId = $_SESSION["authenticated_user_id"];
			$sessionManager = new SessionManager();
			$sessionManager->set("authenticatedUserId", $userId);
			$crmObject = OttocratWebserviceObject::fromName($adb,"Users");
			$userId = vtws_getId($crmObject->getEntityId(),$userId);
			$ottocratVersion = vtws_getOttocratVersion();
			$resp = array("sessionName"=>$sessionManager->getSessionId(),"userId"=>$userId,"version"=>$API_VERSION,"ottocratVersion"=>$ottocratVersion);
			return $resp;
		}else{
			throw new WebServiceException(WebServiceErrorCode::$AUTHFAILURE,"Authencation Failed");
		}
	}
?>