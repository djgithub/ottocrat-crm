<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_RelatedList_View extends Ottocrat_RelatedList_View {
	function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$parentRecordModel = Ottocrat_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Ottocrat_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$relationModel = $relationListView->getRelationModel();

		$viewer = $this->getViewer($request);
		if (array_key_exists($relatedModuleName, $relationModel->getEmailEnabledModulesInfoForDetailView())) {
			$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($relatedModuleName));
			$viewer->assign('STATUS_VALUES', $relationModel->getCampaignRelationStatusValues());
			$viewer->assign('SELECTED_IDS', $request->get('selectedIds'));
			$viewer->assign('EXCLUDED_IDS', $request->get('excludedIds'));
		}
		return parent::process($request);
	}
}