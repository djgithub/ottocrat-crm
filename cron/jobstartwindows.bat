REM  **************************************************************************************
REM  * The contents of this file are subject to the ottocrat CRM Public License Version 1.0 *
REM  * ("License"); You may not use this file except in compliance with the License       *
REM  * The Original Code is:  ottocrat CRM Open Source                                      *
REM  * The Initial Developer of the Original Code is ottocrat.                              *
REM  * Portions created by ottocrat are Copyright (C) ottocrat.                               *
REM  * All Rights Reserved.                                                               *
REM  *                                                                                    *
REM  **************************************************************************************  
@echo off
set SCH_INSTALL=%1
FOR %%X in (%SCH_INSTALL%) DO SET SCH_INSTALL=%%~sX
schtasks /create /tn "ottocratCRM Notification Scheduler" /tr %SCH_INSTALL%\apache\htdocs\ottocratCRM\cron\intimateTaskStatus.bat /sc daily /st 11:00:00 /RU SYSTEM
schtasks /create /tn "ottocratCRM Email Reminder" /tr %SCH_INSTALL%\apache\htdocs\ottocratCRM\modules\Calendar\SendReminder.bat /sc minute /mo 1 /RU SYSTEM
