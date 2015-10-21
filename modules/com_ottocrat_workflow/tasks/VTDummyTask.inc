<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
class VTDummyTask extends VTTask{
	public $executeImmediately = true; 
	
	public function getFieldNames(){return array();}
	public function doTask($entity){
		$statement=$this->statement;
		echo "This is a dummy workflow task with $statement";
	}
}
?>