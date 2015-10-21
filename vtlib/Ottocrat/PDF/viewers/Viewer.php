<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
abstract class Ottocrat_PDF_Viewer {
	
	protected $labelModel;
	
	function setLabelModel($m) {
		$this->labelModel = $m;
	}
	
	abstract function totalHeight($parent);
	abstract function initDisplay($parent);
	abstract function display($parent);
}
