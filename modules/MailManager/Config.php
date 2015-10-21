<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Manages Mail Manager configurations
 */
class MailManager_Config {
	
	static $MAILMANAGER_CONFIG = array(
		// Max upload limit in bytes
		'MAXUPLOADLIMIT'=> 5242880,

		// Max Download Limit in Bytes, as the files are encoded the file size increases
		// so the limit is set to close to 7MB
		'MAXDOWNLOADLIMIT'=>7000000,

		// Increase the memory_limit for larger attachments
		'MEMORY_LIMIT'	=> '256M'
	);

	/**
	 * Get configuration parameter configured value or default one
	 */
	static function get($key, $defvalue=false) {
		if(isset(self::$MAILMANAGER_CONFIG)){
			if(isset(self::$MAILMANAGER_CONFIG[$key])) {
				return self::$MAILMANAGER_CONFIG[$key];
			}
		}
		return $defvalue;
	}
}
?>