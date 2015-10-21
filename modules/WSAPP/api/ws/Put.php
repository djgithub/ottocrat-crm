<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
require_once 'include/Webservices/Create.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Delete.php';
require_once 'modules/WSAPP/Utils.php';

function wsapp_put ($key, $element, $user) {
        $name = wsapp_getApplicationName($key);
        $handlerDetails  = wsapp_getHandler($name);
        require_once $handlerDetails['handlerpath'];
        $handler = new $handlerDetails['handlerclass']($key);
        return $handler->put($element,$user);
}

