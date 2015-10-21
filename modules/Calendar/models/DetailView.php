<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_DetailView_Model extends Ottocrat_DetailView_Model {

	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks() {
		$recordModel = $this->getRecord();
        $moduleName = $recordModel->getType();
		$relatedLinks = array();
		$module_url= $recordModel->getDetailViewUrl().'&mode=showDetailViewByMode&requestMode=full';

		//link which shows the summary information(generally detail of record)
		$relatedLinks[] = array(
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => vtranslate('SINGLE_'.$moduleName, $moduleName).' '. vtranslate('LBL_DETAILS', $moduleName),
				//'linkurl' => $recordModel->getDetailViewUrl().'&mode=showDetailViewByMode&requestMode=full',
				'linkurl' =>Ottocrat_Request::encryptLink($module_url),
				'linkicon' => ''
		);

		$parentModuleModel = $this->getModule();
		$module_url= $recordModel->getDetailViewUrl().'&mode=showRecentActivities&page=1';
		if($parentModuleModel->isTrackingEnabled()) {
			$relatedLinks[] = array(
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => vtranslate('LBL_UPDATES'),
					//'linkurl' => $recordModel->getDetailViewUrl().'&mode=showRecentActivities&page=1',
					'linkurl' => Ottocrat_Request::encryptLink($module_url),
					'linkicon' => ''
			);
		}
		return $relatedLinks;
	}
}
