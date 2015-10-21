<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class SMSNotifier_Edit_View extends Ottocrat_Edit_View {

	public function checkPermission(Ottocrat_Request $request) {
		throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $request->getModule()));
	}
}