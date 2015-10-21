<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Potentials_SaveAjax_Action extends Ottocrat_SaveAjax_Action {

	public function process(Ottocrat_Request $request) {
		//Restrict to store indirect relationship from Potentials to Contacts
		$sourceModule = $request->get('sourceModule');
		$relationOperation = $request->get('relationOperation');

		if ($relationOperation && $sourceModule === 'Contacts') {
			$request->set('relationOperation', false);
		}

		parent::process($request);
	}
}
