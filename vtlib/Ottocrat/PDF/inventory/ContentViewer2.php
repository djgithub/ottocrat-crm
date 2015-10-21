<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/ContentViewer.php';

class Ottocrat_PDF_InventoryTaxGroupContentViewer extends Ottocrat_PDF_InventoryContentViewer {

	function __construct() {
		// NOTE: General A4 PDF width ~ 189 (excluding margins on either side)
			
		$this->cells = array( // Name => Width
			'Code'		=> 30,
			'Name'		=> 65,
			'Quantity'	=> 20,
			'Price'		=> 25,
			'Discount'	=> 20,
			'Total'		=> 30
		);
	}
	
}
