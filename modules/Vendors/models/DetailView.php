<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Vendors_DetailView_Model extends Ottocrat_DetailView_Model {

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
		$emailModuleModel = Ottocrat_Module_Model::getInstance('Emails');
		if($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_SEND_EMAIL',
				'linkurl' => 'javascript:Ottocrat_Detail_Js.triggerSendEmail("'.Ottocrat_Request:: encryptLink('index.php?module='.$this->getModule()->getName().'&view=MassActionAjax&mode=showComposeEmailForm&step=step1').'","Emails");',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEWBASIC'][] = Ottocrat_Link_Model::getInstanceFromValues($basicActionLink);
		}
		$purchaseOrderModuleModel = Ottocrat_Module_Model::getInstance('PurchaseOrder');
		if($currentUserModel->hasModuleActionPermission($purchaseOrderModuleModel->getId(), 'EditView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => vtranslate('LBL_CREATE').' '.vtranslate($purchaseOrderModuleModel->getSingularLabelKey(), 'PurchaseOrder'),
				'linkurl' => $recordModel->getCreatePurchaseOrderUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Ottocrat_Link_Model::getInstanceFromValues($basicActionLink);
		}

		return $linkModelList;
	}
		
}
