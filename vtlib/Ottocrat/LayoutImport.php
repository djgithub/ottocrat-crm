<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
include_once('vtlib/Ottocrat/LayoutExport.php');

/**
 * Provides API to import language into ottocrat CRM
 * @package vtlib
 */
class Ottocrat_LayoutImport extends Ottocrat_LayoutExport {
    
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->_export_tmpdir;
	}
        
	/**
	 * Initialize Import
	 * @access private
	 */
	function initImport($zipfile, $overwrite) {
		$this->__initSchema();
		$name = $this->getModuleNameFromZip($zipfile);
                return $name;
	}

	/**
	 * Import Module from zip file
	 * @param String Zip file name
	 * @param Boolean True for overwriting existing module
	 */
	function import($zipfile, $overwrite=false) {
		$this->initImport($zipfile, $overwrite);

		// Call module import function
		$this->import_Layout($zipfile);
	}

	/**
	 * Update Module from zip file
	 * @param Object Instance of Language (to keep Module update API consistent)
	 * @param String Zip file name
	 * @param Boolean True for overwriting existing module
	 */
	function update($instance, $zipfile, $overwrite=true) {
		$this->import($zipfile, $overwrite);
	}

	/**
	 * Import Module
	 * @access private
	 */
	function import_Layout($zipfile) {
		$name = $this->_modulexml->name;
		$label = $this->_modulexml->label;

		self::log("Importing $label ... STARTED");
		$unzip = new Ottocrat_Unzip($zipfile);
		$filelist = $unzip->getList();
		$ottocrat6format = false;

		foreach($filelist as $filename=>$fileinfo) {
			if(!$unzip->isdir($filename)) {

				if(strpos($filename, '/') === false) continue;


				$targetdir  = substr($filename, 0, strripos($filename,'/'));
				$targetfile = basename($filename);
                                $dounzip = false;
                                // Case handling for jscalendar
                                if(stripos($targetdir, "layouts/$label/skins") === 0) {
                                    $dounzip = true;
                                    $ottocrat6format = true;
                                }
                                // ottocrat6 format
                                else if (stripos($targetdir, "layouts/$label/modules") === 0) {
                                    $ottocrat6format = true;
                                    $dounzip = true;
                                }
				if($dounzip) {
					// ottocrat6 format
					if ($ottocrat6format) {
                                               $targetdir = "layouts/$label/" . str_replace("layouts/$label", "", $targetdir);
						@mkdir($targetdir, 0777, true);
					}

					if($unzip->unzip($filename, "$targetdir/$targetfile") !== false) {
						self::log("Copying file $filename ... DONE");
					} else {
						self::log("Copying file $filename ... FAILED");
					}
				} else {
					self::log("Copying file $filename ... SKIPPED");
				}
			}
		}
		if($unzip) $unzip->close();

		self::register($label, $name);

		self::log("Importing $label [$prefix] ... DONE");

		return;
	}    
}