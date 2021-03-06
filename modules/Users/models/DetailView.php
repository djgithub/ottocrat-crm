<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Users_DetailView_Model extends Ottocrat_DetailView_Model {
    
    
    /**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();

		if (($currentUserModel->isAdminUser() == true || $currentUserModel->get('id') == $recordId) && $recordModel->get('status') == 'Active' ) {
			$recordModel = $this->getRecord();

			$detailViewLinks = array(
				array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $recordModel->getEditViewUrl(),
				'linkicon' => ''
				),
				array(
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => 'LBL_CHANGE_PASSWORD',
//					'linkurl' => "javascript:Users_Detail_Js.triggerChangePassword('index.php?module=Users&view=EditAjax&mode=changePassword&recordId=$recordId','Users')",
					'linkurl' => 'javascript:Users_Detail_Js.triggerChangePassword('. Ottocrat_Request::encryptLink("index.php?module=Users&view=EditAjax&mode=changePassword&recordId=.$recordId").',\'Users\')',
					'linkicon' => ''
				)
			);

			foreach ($detailViewLinks as $detailViewLink) {
				$linkModelList['DETAILVIEWBASIC'][] = Ottocrat_Link_Model::getInstanceFromValues($detailViewLink);
			}
			
			$detailViewPreferenceLinks = array(
				array(
					'linktype' => 'DETAILVIEWPREFERENCE',
					'linklabel' => 'LBL_CHANGE_PASSWORD',
//					'linkurl' => "javascript:Users_Detail_Js.triggerChangePassword('index.php?module=Users&view=EditAjax&mode=changePassword&recordId=$recordId','Users')",
					'linkurl' => "javascript:Users_Detail_Js.triggerChangePassword('".Ottocrat_Request::encryptLink('index.php?module=Users&view=EditAjax&mode=changePassword&recordId='.$recordId)."','Users')",
					'linkicon' => ''
				),
				array(
					'linktype' => 'DETAILVIEWPREFERENCE',
					'linklabel' => 'LBL_EDIT',
					'linkurl' => $recordModel->getPreferenceEditViewUrl(),
					'linkicon' => ''
				)
			);

			foreach ($detailViewPreferenceLinks as $detailViewLink) {
				$linkModelList['DETAILVIEWPREFERENCE'][] = Ottocrat_Link_Model::getInstanceFromValues($detailViewLink);
			}

			if($currentUserModel->isAdminUser() && $currentUserModel->get('id') != $recordId){
				$detailViewActionLinks = array(
					array(
						'linktype' => 'DETAILVIEW',
						'linklabel' => 'LBL_DELETE',
						'linkurl' => 'javascript:Users_Detail_Js.triggerDeleteUser("' . $recordModel->getDeleteUrl() . '")',
						'linkicon' => ''
					)
				);

				foreach ($detailViewActionLinks as $detailViewLink) {
					$linkModelList['DETAILVIEW'][] = Ottocrat_Link_Model::getInstanceFromValues($detailViewLink);
				}
			}
			return $linkModelList;
		}
	}
}