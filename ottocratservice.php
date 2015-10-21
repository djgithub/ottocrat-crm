<?php
/*+*******************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ********************************************************************************/
if(isset($_REQUEST['service']))
{
	if($_REQUEST['service'] == "customerportal")
	{
		include("soap/customerportal.php");
	}
	elseif($_REQUEST['service'] == "firefox")
	{
		include("soap/firefoxtoolbar.php");
	}
	elseif($_REQUEST['service'] == "wordplugin")
	{
		include("soap/wordplugin.php");
	}
	elseif($_REQUEST['service'] == "thunderbird")
	{
		include("soap/thunderbirdplugin.php");
	}
	else
	{
		echo "No Service Configured for ". strip_tags($_REQUEST[service]);
	}
}
else
{
	echo "<h1>ottocratCRM Soap Services</h1>";
	echo "<li>ottocratCRM Outlook Plugin EndPoint URL -- Click <a href='ottocratservice.php?service=outlook'>here</a></li>";
	echo "<li>ottocratCRM Word Plugin EndPoint URL -- Click <a href='ottocratservice.php?service=wordplugin'>here</a></li>";
	echo "<li>ottocratCRM ThunderBird Extenstion EndPoint URL -- Click <a href='ottocratservice.php?service=thunderbird'>here</a></li>";
	echo "<li>ottocratCRM Customer Portal EndPoint URL -- Click <a href='ottocratservice.php?service=customerportal'>here</a></li>";
	echo "<li>ottocratCRM WebForm EndPoint URL -- Click <a href='ottocratservice.php?service=webforms'>here</a></li>";
	echo "<li>ottocratCRM FireFox Extension EndPoint URL -- Click <a href='ottocratservice.php?service=firefox'>here</a></li>";
}


?>
