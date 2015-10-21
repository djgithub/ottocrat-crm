<?php
/*********************************************************************************
** The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
*
 ********************************************************************************/

require_once('include/database/PearDatabase.php');
require_once('include/utils/VtlibUtils.php');
require_once('vtlib/Ottocrat/Cron.php');
global $adb;
if(isset($_REQUEST['record']) && $_REQUEST['record']!='') {
    $cronTask = Ottocrat_Cron::getInstanceById($_REQUEST['record']);
    $cronTask->updateStatus($_REQUEST['status']);
    if($_REQUEST['timevalue'] != '') {

        if($_REQUEST['time'] == 'min') {

            $time = $_REQUEST['timevalue']*60;
        }
        else {
            $time = $_REQUEST['timevalue']*60*60;
        }
        $cronTask->updateFrequency($time);
    }
}
$loc = "Location: ".Ottocrat_Request:: encryptLink("index.php?action=CronTasksAjax&file=ListCronJobs&module=CronTasks&directmode=ajax");
header($loc);
?>
