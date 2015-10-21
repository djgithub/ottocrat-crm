<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *
 *********************************************************************************/

require_once 'modules/com_ottocrat_workflow/VTSimpleTemplate.inc';

/**
 * Description of VTEmailRecipientsTemplate
 *
 * @author MAK
 */
class VTEmailRecipientsTemplate extends VTSimpleTemplate {

	public function __construct($templateString) {
		parent::__construct($templateString);
	}

	protected function useValue($data, $fieldname) {
		return $data['emailoptout'] == 0;
	}
}
?>