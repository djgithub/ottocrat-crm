<?php

/* +**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once 'include/utils/utils.php';
require_once 'include/utils/VtlibUtils.php';
require_once 'modules/Emails/class.phpmailer.php';
require_once 'modules/Emails/mail.php';
require_once 'modules/Ottocrat/helpers/ShortURL.php';
global $adb;
$adb = PearDatabase::getInstance();

if (isset($_REQUEST['user_name']) && isset($_REQUEST['emailId'])) {
    $username = vtlib_purify($_REQUEST['user_name']);
    $result = $adb->pquery('select email1 from ottocrat_users where user_name= ? ', array($username));
    if ($adb->num_rows($result) > 0) {
        $email = $adb->query_result($result, 0, 'email1');
    }

    if (vtlib_purify($_REQUEST['emailId']) == $email) {
        $time = time();
        $options = array(
            'handler_path' => 'modules/Users/handlers/ForgotPassword.php',
            'handler_class' => 'Users_ForgotPassword_Handler',
            'handler_function' => 'changePassword',
            'handler_data' => array(
                'username' => $username,
                'email' => $email,
                'time' => $time,
                'hash' => md5($username . $time)
            )
        );
        $trackURL = Ottocrat_ShortURL_Helper::generateURL($options);
        $content = 'Dear Customer,<br><br> 
                            You recently requested a password reset for your OttocratCRM Open source Account.<br> 
                            To create a new password, click on the link <a target="_blank" href=' . $trackURL . '>here</a>. 
                            <br><br> 
                            This request was made on ' . date("Y-m-d H:i:s") . ' and will expire in next 24 hours.<br><br> 
		            Regards,<br> 
		            OttocratCRM Open source Support Team.<br>' ;
        $mail = new PHPMailer();
        $query = "select from_email_field,server_username from ottocrat_systems where server_type=?";
        $params = array('email');
        $result = $adb->pquery($query,$params);
        $from = $adb->query_result($result,0,'from_email_field');
        if($from == '') {$from =$adb->query_result($result,0,'server_username'); }
        $subject='Request : ForgotPassword - ottocratcrm';
        
        setMailerProperties($mail,$subject, $content, $from, $username, $email);
        $status = MailSend($mail);
        if ($status === 1)
            header('Location: '.Ottocrat_Request:: encryptLink("index.php?modules=Users&view=Login&status=1"));
        else
            header('Location: '.Ottocrat_Request:: encryptLink("  index.php?modules=Users&view=Login&statusError=1"));
    } else {
        header('Location:  '.Ottocrat_Request:: encryptLink(" index.php?modules=Users&view=Login&fpError=1"));
    }
}