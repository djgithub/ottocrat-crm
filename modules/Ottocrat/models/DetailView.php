<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_DetailView_Model extends Ottocrat_Base_Model {

	protected $module = false;
	protected $record = false;

	/**
	 * Function to get Module instance
	 * @return <Ottocrat_Module_Model>
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the module instance
	 * @param <Ottocrat_Module_Model> $moduleInstance - module model
	 * @return Ottocrat_DetailView_Model>
	 */
	public function setModule($moduleInstance) {
		$this->module = $moduleInstance;
		return $this;
	}

	/**
	 * Function to get the Record model
	 * @return <Ottocrat_Record_Model>
	 */
	public function getRecord() {
		return $this->record;
	}

	/**
	 * Function to set the record instance3
	 * @param <type> $recordModuleInstance - record model
	 * @return Ottocrat_DetailView_Model
	 */
	public function setRecord($recordModuleInstance) {
		$this->record = $recordModuleInstance;
		return $this;
	}

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$linkTypes = array('DETAILVIEWBASIC','DETAILVIEW');
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();

		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

		$detailViewLink = array();

		if(Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId)) {
			$detailViewLinks[] = array(
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => 'LBL_EDIT',
					'linkurl' => $recordModel->getEditViewUrl(),// not require to encrypt here
					'linkicon' => ''
			);

			foreach ($detailViewLinks as $detailViewLink) {
				$linkModelList['DETAILVIEWBASIC'][] = Ottocrat_Link_Model::getInstanceFromValues($detailViewLink);
			}
		}

		$linkModelListDetails = Ottocrat_Link_Model::getAllByType($moduleModel->getId(),$linkTypes,$linkParams);
		//Mark all detail view basic links as detail view links.
		//Since ui will be look ugly if you need many basic links
		$detailViewBasiclinks = $linkModelListDetails['DETAILVIEWBASIC'];
		unset($linkModelListDetails['DETAILVIEWBASIC']);

		if(Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
			$deletelinkModel = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => sprintf("%s %s", getTranslatedString('LBL_DELETE', $moduleName), vtranslate('SINGLE_'. $moduleName, $moduleName)),
					'linkurl' => 'javascript:Ottocrat_Detail_Js.deleteRecord("'.$recordModel->getDeleteUrl().'")',
					'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Ottocrat_Link_Model::getInstanceFromValues($deletelinkModel);
		}

		if(Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId)) {
			$duplicateLinkModel = array(
						'linktype' => 'DETAILVIEWBASIC',
						'linklabel' => 'LBL_DUPLICATE',
						'linkurl' =>$recordModel->getDuplicateRecordUrl(),
						'linkicon' => ''
				);
			$linkModelList['DETAILVIEW'][] = Ottocrat_Link_Model::getInstanceFromValues($duplicateLinkModel);
		}

		if(!empty($detailViewBasiclinks)) {
			foreach($detailViewBasiclinks as $linkModel) {
				// Remove view history, needed in ottocrat5 to see history but not in ottocrat6
				if($linkModel->linklabel == 'View History') {
					continue;
				}
				$linkModelList['DETAILVIEW'][] = $linkModel;
			}
		}

		$relatedLinks = $this->getDetailViewRelatedLinks();

		foreach($relatedLinks as $relatedLinkEntry) {
			$relatedLink = Ottocrat_Link_Model::getInstanceFromValues($relatedLinkEntry);
			$linkModelList[$relatedLink->getType()][] = $relatedLink;
		}

		$widgets = $this->getWidgets();
		foreach($widgets as $widgetLinkModel) {
			$linkModelList['DETAILVIEWWIDGET'][] = $widgetLinkModel;
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if($currentUserModel->isAdminUser()) {
			$settingsLinks = $moduleModel->getSettingLinks();
			foreach($settingsLinks as $settingsLink) {
				$linkModelList['DETAILVIEWSETTING'][] = Ottocrat_Link_Model::getInstanceFromValues($settingsLink);
			}
		}

		return $linkModelList;
	}

	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks() {
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$parentModuleModel = $this->getModule();
		$relatedLinks = array();



		//ottocrat-changes

		if($parentModuleModel->isTrackingEnabled()) {
			$relatedLinks = array(array(
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_UPDATES',
				'linkurl' =>$recordModel->getDetailUrlWNoChange().'&mode=showRecentActivities&page=1',
				'linkicon' => ''
			));
		}

		if($parentModuleModel->isSummaryViewSupported()) {
			$relatedLinks[] = array(
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => vtranslate('SINGLE_' . $moduleName, $moduleName) . ' ' . vtranslate('LBL_SUMMARY', $moduleName),
				'linkKey' => 'LBL_RECORD_SUMMARY',
				'linkurl' => $recordModel->getDetailUrlWNoChange().'&mode=showDetailViewByMode&requestMode=summary',
				'linkicon' => ''
			);
		}
		//link which shows the summary information(generally detail of record)
		$relatedLinks[] = array(
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => vtranslate('SINGLE_'.$moduleName, $moduleName).' '. vtranslate('LBL_DETAILS', $moduleName),
                                'linkKey' => 'LBL_RECORD_DETAILS',
				'linkurl' => $recordModel->getDetailUrlWNoChange().'&mode=showDetailViewByMode&requestMode=full',
				'linkicon' => ''
		);

		$modCommentsModel = Ottocrat_Module_Model::getInstance('ModComments');
		if($parentModuleModel->isCommentEnabled() && $modCommentsModel->isPermitted('DetailView')) {
			$relatedLinks[] = array(
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => 'ModComments',
					'linkurl' => $recordModel->getDetailUrlWNoChange().'&mode=showAllComments',
					'linkicon' => ''
			);
		}



		$relationModels = $parentModuleModel->getRelations();

		foreach($relationModels as $relation) {
			//TODO : Way to get limited information than getting all the information
			$link = array(
					'linktype' => 'DETAILVIEWRELATED',
					'linklabel' => $relation->get('label'),
					'linkurl' => $relation->getListUrl($recordModel),
					'linkicon' => '',
					'relatedModuleName' => $relation->get('relatedModuleName') 
			);
			$relatedLinks[] = $link;
		}

		return $relatedLinks;
	}

	/**
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Ottocrat_Link_Model
	 */
	public function getWidgets() {
		$moduleModel = $this->getModule();
		$widgets = array();

		$modCommentsModel = Ottocrat_Module_Model::getInstance('ModComments');
		if($moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'ModComments',
					'linkurl' =>'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().	'&mode=showRecentComments&page=1&limit=5'
			);
		}

		if($moduleModel->isTrackingEnabled()) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'LBL_UPDATES',
					'linkurl' =>  'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().'&mode=showRecentActivities&page=1&limit=5',//not require encryption
			);
		}

		$widgetLinks = array();
		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Ottocrat_Link_Model::getInstanceFromValues($widgetDetails);
		}
		return $widgetLinks;
	}

	/**
	 * Function to get the Quick Links for the Detail view of the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Ottocrat_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$moduleLinks = $this->getModule()->getSideBarLinks($linkTypes);

		$listLinkTypes = array('DETAILVIEWSIDEBARLINK', 'DETAILVIEWSIDEBARWIDGET');
		$listLinks = Ottocrat_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

		if($listLinks['DETAILVIEWSIDEBARLINK']) {
			foreach($listLinks['DETAILVIEWSIDEBARLINK'] as $link) {
				$link->linkurl = $link->linkurl.'&record='.$this->getRecord()->getId().'&source_module='.$this->getModule()->getName();
				$moduleLinks['SIDEBARLINK'][] = $link;
			}
		}

		if($currentUser->getTagCloudStatus()) {
			$tagWidget = array(
				'linktype' => 'DETAILVIEWSIDEBARWIDGET',
				'linklabel' => 'LBL_TAG_CLOUD',
				'linkurl' => 'module='.$this->getModule()->getName().'&view=ShowTagCloud&mode=showTags',
				'linkicon' => '',
			);
			$linkModel = Ottocrat_Link_Model::getInstanceFromValues($tagWidget);
			if($listLinks['DETAILVIEWSIDEBARWIDGET']) array_push($listLinks['DETAILVIEWSIDEBARWIDGET'], $linkModel);
			else $listLinks['DETAILVIEWSIDEBARWIDGET'][] = $linkModel;
		}

		if($listLinks['DETAILVIEWSIDEBARWIDGET']) {
			foreach($listLinks['DETAILVIEWSIDEBARWIDGET'] as $link) {
				$link->linkurl =  Ottocrat_Request::encryptLink($link->linkurl.'&record='.$this->getRecord()->getId().'&source_module='.$this->getModule()->getName());
				$moduleLinks['SIDEBARWIDGET'][] = $link;
			}
		}

		return $moduleLinks;
	}

	/**
	 * Function to get the module label
	 * @return <String> - label
	 */
	public function getModuleLabel() {
		return $this->getModule()->get('label');
	}

	/**
	 *  Function to get the module name
	 *  @return <String> - name of the module
	 */
	public function getModuleName() {
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the instance
	 * @param <String> $moduleName - module name
	 * @param <String> $recordId - record id
	 * @return <Ottocrat_DetailView_Model>
	 */
	public static function getInstance($moduleName,$recordId) {
		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'DetailView', $moduleName);
		$instance = new $modelClassName();

		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$recordModel = Ottocrat_Record_Model::getInstanceById($recordId, $moduleName);

		return $instance->setModule($moduleModel)->setRecord($recordModel);
	}
}
