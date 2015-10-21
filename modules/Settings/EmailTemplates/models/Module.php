<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */
/**
 * Email Template Model Class
 */
class Settings_EmailTemplates_Module_Model extends Settings_Ottocrat_Module_Model {

	/**
	 * Function retruns List of Email Templates
	 * @return string
	 */
	function getListViewUrl() {
		return 'module=EmailTemplates&parent=Settings&view=List';
	}

	/**
	 * Function returns all the Email Template Models
	 * @return <Array of EmailTemplates_Record_Model>
	 */
	function getAll() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM ottocrat_emailtemplates WHERE deleted = 0', array());

		$emailTemplateModels = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$emailTemplateModel = Settings_EmailTemplates_Record_Model::getInstance();
			$emailTemplateModel->setData($db->query_result_rowdata($result, $i));
			$emailTemplateModels[] = $emailTemplateModel;
		}

		return $emailTemplateModels;
	}
}
