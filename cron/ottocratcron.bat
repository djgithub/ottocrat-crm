@echo OFF
REM #*********************************************************************************
REM # The contents of this file are subject to the ottocrat CRM Public License Version 1.0
REM # ("License"); You may not use this file except in compliance with the License
REM # The Original Code is:  ottocrat CRM Open Source
REM # The Initial Developer of the Original Code is ottocrat.
REM # Portions created by ottocrat are Copyright (C) ottocrat.
REM # All Rights Reserved.
REM #
REM # ********************************************************************************

set OTTOCRATCRM_ROOTDIR="C:\Program Files\ottocratcrm5\apache\htdocs\ottocratCRM"
set PHP_EXE="C:\Program Files\ottocratcrm5\php\php.exe"

cd /D %OTTOCRATCRM_ROOTDIR%

%PHP_EXE% -f ottocratcron.php
