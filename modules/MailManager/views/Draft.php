<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: ottocrat CRM Open source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_Draft_View extends MailManager_Abstract_View {

	/**
	 * Function to process request, currently not used
	 * @param Ottocrat_Request $request
	 */
	public function process(Ottocrat_Request $request) {
	}

	/**
	 * Returns a List of search strings on the internal ottocrat Drafts
	 * @return Array of ottocrat Email Fields
	 */
	public static function getSearchOptions() {
		$options = array('subject'=>'SUBJECT', 'saved_toid'=>'TO','description'=>'BODY','bccmail'=>'BCC','ccmail'=>'CC');
		return $options;
	}

	/**
	 * Function which returns the Draft Model
	 * @return MailManager_Draft_Model
	 */
	public function connectorWithModel() {
		if ($this->mMailboxModel === false) {
			$this->mMailboxModel = MailManager_Draft_Model::getInstance();
		}
		return $this->mMailboxModel;
	}
}
?>