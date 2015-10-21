@echo off
cd ..
set mysql_dir=MYSQLINSTALLDIR
set mysql_username=MYSQLUSERNAME
set mysql_password=MYSQLPASSWORD
set mysql_port=MYSQLPORT
set mysql_bundled=MYSQLBUNDLEDSTATUS
set apache_dir=APACHEINSTALLDIR
set apache_bin=APACHEBIN
set apache_conf=APACHECONF
set apache_port=APACHEPORT
set apache_bundled=APACHEBUNDLED
set apache_service=APACHESERVICE

set OTTOCRAT_HOME=%cd%


if %apache_bundled% == true goto StopApacheCheck
goto StopMySQL

:StopApacheCheck
if %apache_service% == true goto StopApacheService
cd /d %apache_dir%
rem shut down apache
echo ""
echo "stopping ottocratcrm apache"
echo ""
bin\ShutdownApache logs\httpd.pid
goto StopMySQL

:StopApacheService
cd /d %apache_dir%
rem shut down apache
echo ""
echo "stopping ottocratcrm504 apache service"
echo ""
bin\apache -n ottocratcrm504 -k stop
echo ""
echo "uninstalling ottocratcrm504 apache service"
echo ""
bin\apache -k uninstall -n ottocratcrm504
rem .\bin\ShutdownApache.exe logs\httpd.pid
goto StopMySQL


:StopMySQL
if %mysql_bundled% == true (
	rem cd /d %OTTOCRAT_HOME%\mysql\bin
	rem  shutdown mysql 
	cd /d %mysql_dir%\bin
	mysqladmin --port=%mysql_port% --user=%mysql_username% --password=%mysql_password% shutdown
	echo ""
	echo "ottocrat CRM  MySQL Sever is shut down"
	echo ""
	cd /d %OTTOCRAT_HOME%\bin
)
