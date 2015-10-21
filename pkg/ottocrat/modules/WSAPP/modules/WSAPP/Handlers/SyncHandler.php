<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

abstract class SyncHandler{

    protected $user;
    protected $key;
    protected $syncServer;
    protected $syncModule;

    abstract function get($module,$token,$user);
    abstract function put($element,$user);
    abstract function map($element,$user);
    abstract function nativeToSyncFormat($element);
    abstract function syncToNativeFormat($element);

}
?>
