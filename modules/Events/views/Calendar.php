<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

// TODO This is a stop-gap measure to have the
// user continue working with Calendar when dropping from Event View.
class Events_Calendar_View extends Ottocrat_Index_View {
	
	public function preProcess(Ottocrat_Request $request, $display = true) {}
	public function postProcess(Ottocrat_Request $request) {}
	
	public function process(Ottocrat_Request $request) {
		header("Location:".Ottocrat_Request:: encryptLink(" index.php?module=Calendar&view=Calendar"));
	}
}
