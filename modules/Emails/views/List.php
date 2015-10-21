<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Emails_List_View extends Ottocrat_List_View {

	public function preProcess(Ottocrat_Request $request) {
	}

	public function process(Ottocrat_Request $request) {
		header('Location: '.Ottocrat_Request:: encryptLink('index.php?module=MailManager&view=List'));
	}
}