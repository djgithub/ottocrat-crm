<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_DetailView_Model extends Ottocrat_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();

		if ($recordModel->get('filestatus') && $recordModel->get('filename') && $recordModel->get('filelocationtype') === 'I') {
			$basicActionLink = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => 'LBL_DOWNLOAD_FILE',
					'linkurl' => $recordModel->getDownloadFileURL(),
					'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Ottocrat_Link_Model::getInstanceFromValues($basicActionLink);
		}
		$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_CHECK_FILE_INTEGRITY',
				'linkurl' => $recordModel->checkFileIntegrityURL(),
				'linkicon' => ''
		);
		$linkModelList['DETAILVIEW'][] = Ottocrat_Link_Model::getInstanceFromValues($basicActionLink);

		if ($recordModel->get('filestatus') && $recordModel->get('filename') && $recordModel->get('filelocationtype') === 'I') {
			$emailModuleModel = Ottocrat_Module_Model::getInstance('Emails');

			if($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
				$basicActionLink = array(
						'linktype' => 'DETAILVIEW',
						'linklabel' => 'LBL_EMAIL_FILE_AS_ATTACHMENT',
						'linkurl' => "javascript:Documents_Detail_Js.triggerSendEmail('". ZEND_JSON::encode(array($recordModel->getId())) ."')",
						'linkicon' => ''
				);
				$linkModelList['DETAILVIEW'][] = Ottocrat_Link_Model::getInstanceFromValues($basicActionLink);
			}
		}

		return $linkModelList;
	}

}
