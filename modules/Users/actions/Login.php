<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Login_Action extends Ottocrat_Action_Controller {

	function loginRequired() {
		return false;
	}
        
        
        function checkPermission(Ottocrat_Request $request) {  
               return true;  
        } 

	function process(Ottocrat_Request $request) {
		$username = $request->get('username');
		$password = $request->get('password');
//print_r($request);
		#Ottocrat_Session::destroy();

			if( $request->get('dbcreation')=='yes') //to create tables of product
		{
			/*$dbcre = CRMEntity::getInstance('DbCreate');
			$dbcre->column_fields['user_name'] = $username;
			$dbcre->DbCreateProcess();*/
			global $adb, $OT_USER,$OT_DB,$OT_PASSWORD;
			$adb->disconnect();
			if($_REQUEST['user']!='' & $_REQUEST['password']!='' & $_REQUEST['dbname']!='')

				$adb->resetSettings('mysqli', 'localhost', $_REQUEST['dbname'], $_REQUEST['user'],$_REQUEST['password']);

			else

				$adb->resetSettings('mysqli', 'localhost', $OT_DB, $OT_USER, $OT_PASSWORD);

			$adb->checkConnection();


			Install_InitSchema_Model::initialize();

			// Install all the available modules

			Install_Utils_Model::installModules();



			Install_InitSchema_Model::upgrade();

			exit;


		}

		$user = CRMEntity::getInstance('Users');
		$user->column_fields['user_name'] = $username;
		if ($user->doLogin($password)) {
		//	session_regenerate_id(true); // to overcome session id reuse.
			if($username!=='admin') {
				global $adb,$OT_USER,$OT_DB,$OT_PASSWORD;

				$db_name = $user->column_fields["user_name"];

				if ($db_name != '') {
					$adb->disconnect();
					$adb->resetSettings('mysqli', 'localhost', $OT_DB, $OT_USER, $OT_PASSWORD);
				}
				$_SESSION['username'] = $db_name;
			}
			$userid = $user->retrieve_user_id($username);
			Ottocrat_Session::set('AUTHUSERID', $userid);

			// For Backward compatability
			// TODO Remove when switch-to-old look is not needed
			$_SESSION['authenticated_user_id'] = $userid;
			$_SESSION['app_unique_key'] = vglobal('application_unique_key');
			$_SESSION['authenticated_user_language'] = vglobal('default_language');
            
            		//Enabled session variable for KCFINDER 
            		$_SESSION['KCFINDER'] = array(); 
            		$_SESSION['KCFINDER']['disabled'] = false; 
            		$_SESSION['KCFINDER']['uploadURL'] = "test/upload"; 
            		$_SESSION['KCFINDER']['uploadDir'] = "../test/upload";
			$deniedExts = implode(" ", vglobal('upload_badext'));
			$_SESSION['KCFINDER']['deniedExts'] = $deniedExts;
			// End



			//Track the login History
			$moduleModel = Users_Module_Model::getInstance('Users');
			$moduleModel->saveLoginHistory($user->column_fields['user_name']);
			//End

              if(isset($_SESSION['return_params'])){ 
					$return_params = $_SESSION['return_params'];
				}

			//header ('Location: index.php?module=Users&parent=Settings&view=SystemSetup');
			header ('Location: index.php');

			exit();
		} else {
			header ('Location: '.Ottocrat_Request:: encryptLink('index
			.php?module=Users&parent=Settings&view=Login&error=1'));
			exit;
		}
	}
	
		}