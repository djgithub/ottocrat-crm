<?php
/*********************************************************************************
** The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
*
 ********************************************************************************/

/*
 * File containing methods to proceed with the ui validation for all the forms
 *
 */
/**
 * Get field validation information
 */
function getDBValidationData($tablearray, $tabid='') {
	return Ottocrat_Deprecated::getModuleFieldTypeOfDataInfos($tablearray, $tabid);
  }

?>
