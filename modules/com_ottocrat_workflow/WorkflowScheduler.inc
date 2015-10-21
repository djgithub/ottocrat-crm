<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

require_once ('include/events/VTEntityData.inc');
require_once ('include/QueryGenerator/QueryGenerator.php');
require_once ('include/Zend/Json.php');
require_once ('include/utils/utils.php');
require_once ('modules/com_ottocrat_workflow/VTWorkflowUtils.php');
require_once ('modules/com_ottocrat_workflow/VTWorkflowManager.inc');
require_once ('modules/com_ottocrat_workflow/VTTaskManager.inc');
require_once ('modules/com_ottocrat_workflow/VTTaskQueue.inc');
require_once ('modules/com_ottocrat_workflow/VTEntityCache.inc');
require_once ('include/Webservices/WebserviceField.php');
require_once ('include/Webservices/EntityMeta.php');
require_once ('include/Webservices/OttocratWebserviceObject.php');
require_once ('include/Webservices/OttocratCRMObject.php');
require_once ('include/Webservices/OttocratCRMObjectMeta.php');
require_once ('include/Webservices/DataTransform.php');
require_once ('include/Webservices/Retrieve.php');