<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Ottocrat ListView Model Class
 */
class Ottocrat_ListView_Model extends Ottocrat_Base_Model {



	/**
	 * Function to get the Module Model
	 * @return Ottocrat_Module_Model instance
	 */
	public function getModule() {
		return $this->get('module');
	}

	/**
	 * Function to get the Quick Links for the List view of the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Ottocrat_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$moduleLinks = $this->getModule()->getSideBarLinks($linkParams);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$listLinkTypes = array('LISTVIEWSIDEBARLINK', 'LISTVIEWSIDEBARWIDGET');
		$listLinks = Ottocrat_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

		if($listLinks['LISTVIEWSIDEBARLINK']) {
			foreach($listLinks['LISTVIEWSIDEBARLINK'] as $link) {
				$moduleLinks['SIDEBARLINK'][] = $link;
			}
		}
		/* new code added for cloudtag*/
		if($currentUser->getTagCloudStatus()) {
			$tagWidget = array(
				'linktype' => 'LISTVIEWSIDEBARWIDGET',
				'linklabel' => 'LBL_TAG_CLOUD',
				'linkurl' => 'module='.$this->getModule()->getName().'&view=ShowTagCloud&mode=showTags',
				'linkicon' => '',
			);
			$linkModel = Ottocrat_Link_Model::getInstanceFromValues($tagWidget);
			if($listLinks['LISTVIEWSIDEBARWIDGET']) array_push($listLinks['LISTVIEWSIDEBARWIDGET'], $linkModel);
			else $listLinks['LISTVIEWSIDEBARWIDGET'][] = $linkModel;
		}

		if($listLinks['LISTVIEWSIDEBARWIDGET']) {
			foreach($listLinks['LISTVIEWSIDEBARWIDGET'] as $link) {
				//$link->linkurl = $link->linkurl.'&record=all&source_module='.$this->getModule()->getName();
				$link->linkurl = Ottocrat_Request::encryptLink($link->linkurl.'&record=all&source_module='.$this->getModule()->getName());
				$moduleLinks['SIDEBARWIDGET'][] = $link;
			}
		}
	#	print_r($moduleLinks);
		return $moduleLinks;
	}

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Ottocrat_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
		$links = Ottocrat_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

		$basicLinks = $this->getBasicLinks();

		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Ottocrat_Link_Model::getInstanceFromValues($basicLink);
		}

		$advancedLinks = $this->getAdvancedLinks();

		foreach($advancedLinks as $advancedLink) {
			$links['LISTVIEW'][] = Ottocrat_Link_Model::getInstanceFromValues($advancedLink);
		}

		if($currentUserModel->isAdminUser()) {

			$settingsLinks = $this->getSettingLinks();
			foreach($settingsLinks as $settingsLink) {
				$links['LISTVIEWSETTING'][] = Ottocrat_Link_Model::getInstanceFromValues($settingsLink);
			}
		}

		return $links;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Ottocrat_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWMASSACTION');
		$links = Ottocrat_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);


		$massActionLinks = array();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_EDIT',
//				'linkurl' => 'javascript:Ottocrat_List_Js.triggerMassEdit("index.php?module='.$moduleModel->get('name').'&view=MassActionAjax&mode=showMassEditForm");',
				'linkurl' => 'javascript:Ottocrat_List_Js.triggerMassEdit( "index.php?module='.$moduleModel->get('name').'&view=MassActionAjax&mode=showMassEditForm");',
				'linkicon' => ''
			);
		}
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
//				'linkurl' => 'javascript:Ottocrat_List_Js.massDeleteRecords("index.php?module='.$moduleModel->get('name').'&action=MassDelete");',
				'linkurl' => 'javascript:Ottocrat_List_Js.massDeleteRecords("'.Ottocrat_Request::encryptLink( "index.php?module='.$moduleModel->get('name').'&action=MassDelete").'");',
				'linkicon' => ''
			);
		}

		$modCommentsModel = Ottocrat_Module_Model::getInstance('ModComments');
		if($moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('EditView')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_ADD_COMMENT',
				'linkurl' => 'index.php?module='.$moduleModel->get('name').'&view=MassActionAjax&mode=showAddCommentForm',
//				'linkurl' => Ottocrat_Request::encryptLink('module='.$moduleModel->get('name').'&view=MassActionAjax&mode=showAddCommentForm'), //not require encrypt this url
				'linkicon' => ''
			);
		}

		foreach($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Ottocrat_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Ottocrat_Field_Model instances
	 */
	public function getListViewHeaders() {
        $listViewContoller = $this->get('listview_controller');
        $module = $this->getModule();
		$headerFieldModels = array();
		$headerFields = $listViewContoller->getListViewHeaderFields();
		foreach($headerFields as $fieldName => $webserviceField) {
			if($webserviceField && !in_array($webserviceField->getPresence(), array(0,2))) continue;
			$headerFieldModels[$fieldName] = Ottocrat_Field_Model::getInstance($fieldName,$module);
		}
		return $headerFieldModels;
	}

	/**
	 * Function to get the list view entries
	 * @param Ottocrat_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Ottocrat_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$db = PearDatabase::getInstance();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');

         $searchParams = $this->get('search_params');
        if(empty($searchParams)) {
            $searchParams = array();
        }
        $glue = "";
        if(count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

        
        $orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');

		//List view will be displayed on recently created/modified records
		if(empty($orderBy) && empty($sortOrder) && $moduleName != "Users"){
			$orderBy = 'modifiedtime';
			$sortOrder = 'DESC';
		}

        if(!empty($orderBy)){
            $columnFieldMapping = $moduleModel->getColumnFieldMapping();
            $orderByFieldName = $columnFieldMapping[$orderBy];
            $orderByFieldModel = $moduleModel->getField($orderByFieldName);
            if($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Ottocrat_Field_Model::REFERENCE_TYPE){
                //IF it is reference add it in the where fields so that from clause will be having join of the table
                $queryGenerator = $this->get('query_generator');
                $queryGenerator->addWhereField($orderByFieldName);
                //$queryGenerator->whereFields[] = $orderByFieldName;
            }
        }
		$listQuery = $this->getQuery();

		$sourceModule = $this->get('src_module');
		if(!empty($sourceModule)) {
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		if(!empty($orderBy)) {
            if($orderByFieldModel && $orderByFieldModel->isReferenceField()){
                $referenceModules = $orderByFieldModel->getReferenceList();
                $referenceNameFieldOrderBy = array();
                foreach($referenceModules as $referenceModuleName) {
                    $referenceModuleModel = Ottocrat_Module_Model::getInstance($referenceModuleName);
                    $referenceNameFields = $referenceModuleModel->getNameFields();

                    $columnList = array();
                    foreach($referenceNameFields as $nameField) {
                        $fieldModel = $referenceModuleModel->getField($nameField);
                        $columnList[] = $fieldModel->get('table').$orderByFieldModel->getName().'.'.$fieldModel->get('column');
                    }
                    if(count($columnList) > 1) {
                        $referenceNameFieldOrderBy[] = getSqlForNameInDisplayFormat(array('first_name'=>$columnList[0],'last_name'=>$columnList[1]),'Users', '').' '.$sortOrder;
                    } else {
                        $referenceNameFieldOrderBy[] = implode('', $columnList).' '.$sortOrder ;
                    }
                }
                $listQuery .= ' ORDER BY '. implode(',',$referenceNameFieldOrderBy);
            }
            else if (!empty($orderBy) && $orderBy === 'smownerid') { 
                $fieldModel = Ottocrat_Field_Model::getInstance('assigned_user_id', $moduleModel); 
                if ($fieldModel->getFieldDataType() == 'owner') { 
                    $orderBy = 'COALESCE(CONCAT(ottocrat_users.first_name,ottocrat_users.last_name),ottocrat_groups.groupname)'; 
                } 
                $listQuery .= ' ORDER BY '. $orderBy . ' ' .$sortOrder;
            }
            else{
                $listQuery .= ' ORDER BY '. $orderBy . ' ' .$sortOrder;
            }
		}

		$viewid = ListViewSession::getCurrentView($moduleName);
		if(empty($viewid)) {
            $viewid = $pagingModel->get('viewid');
		}
        $_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');

		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		$listQuery .= " LIMIT $startIndex,".($pageLimit+1);

		$listResult = $db->pquery($listQuery, array());

		$listViewRecordModels = array();
		$listViewEntries =  $listViewContoller->getListViewRecords($moduleFocus,$moduleName, $listResult);

		$pagingModel->calculatePageRange($listViewEntries);

		if($db->num_rows($listResult) > $pageLimit){
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}

		$index = 0;
		foreach($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
		}
		return $listViewRecordModels;
	}

	/**
	 * Function to get the list view entries
	 * @param Ottocrat_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Ottocrat_Record_Model instance.
	 */
	public function getListViewCount() {
		$db = PearDatabase::getInstance();

		$queryGenerator = $this->get('query_generator');

        
        $searchParams = $this->get('search_params');
        if(empty($searchParams)) {
            $searchParams = array();
        }
        
        $glue = "";
        if(count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);
        
        $searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}
        $moduleName = $this->getModule()->get('name');
        $moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
        
        

		$listQuery = $this->getQuery();


		$sourceModule = $this->get('src_module');
		if(!empty($sourceModule)) {
			$moduleModel = $this->getModule();
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}
		$position = stripos($listQuery, ' from ');
		if ($position) {
			$split = spliti(' from ', $listQuery);
			$splitCount = count($split);
			$listQuery = 'SELECT count(*) AS count ';
			for ($i=1; $i<$splitCount; $i++) {
				$listQuery = $listQuery. ' FROM ' .$split[$i];
			}
		}

		if($this->getModule()->get('name') == 'Calendar'){
			$listQuery .= ' AND activitytype <> "Emails"';
		}

		$listResult = $db->pquery($listQuery, array());
		return $db->query_result($listResult, 0, 'count');
	}

	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
		return $listQuery;
	}
	/**
	 * Static Function to get the Instance of Ottocrat ListView model for a given module and custom view
	 * @param <String> $moduleName - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Ottocrat_ListView_Model instance
	 */
	public static function getInstance($moduleName, $viewId='0') {
		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'ListView', $moduleName);
		$instance = new $modelClassName();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);
		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);
		$customView = new CustomView();
		if (!empty($viewId) && $viewId != "0") {
			$queryGenerator->initForCustomViewById($viewId);

			//Used to set the viewid into the session which will be used to load the same filter when you refresh the page
			$viewId = $customView->getViewId($moduleName);
		} else {
			$viewId = $customView->getViewId($moduleName);
			if(!empty($viewId) && $viewId != 0) {
				$queryGenerator->initForDefaultCustomView();
			} else {
				$entityInstance = CRMEntity::getInstance($moduleName);
				$listFields = $entityInstance->list_fields_name;
				$listFields[] = 'id';
				$queryGenerator->setFields($listFields);
			}
		}
		$controller = new ListViewController($db, $currentUser, $queryGenerator);

		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
	}

    /**
	 * Static Function to get the Instance of Ottocrat ListView model for a given module and custom view
	 * @param <String> $value - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Ottocrat_ListView_Model instance
	 */
	public static function getInstanceForPopup($value) {
		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'ListView', $value);
		$instance = new $modelClassName();
		$moduleModel = Ottocrat_Module_Model::getInstance($value);

		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);
		
		$listFields = $moduleModel->getPopupViewFieldsList();
        
        $listFields[] = 'id';
        $queryGenerator->setFields($listFields);

		$controller = new ListViewController($db, $currentUser, $queryGenerator);

		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
	}

	/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = array();
		$importPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Import');
		if($importPermission && $createPermission) {
			$advancedLinks[] = array(
							'linktype' => 'LISTVIEW',
							'linklabel' => 'LBL_IMPORT',
							'linkurl' => $moduleModel->getImportUrl(),
							'linkicon' => ''
			);
		}

		$exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
		if($exportPermission) {
			$advancedLinks[] = array(
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_EXPORT',
					'linkurl' => 'javascript:Ottocrat_List_Js.triggerExportAction("'.$this->getModule()->getExportUrl().'")',
					'linkicon' => ''
				);
		}

		$duplicatePermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'DuplicatesHandling');
		if($duplicatePermission) {
			$advancedLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_FIND_DUPLICATES',
//				'linkurl' => 'Javascript:Ottocrat_List_Js.showDuplicateSearchForm("index.php?module='.$moduleModel->getName().
//								'&view=MassActionAjax&mode=showDuplicatesSearchForm")',
				'linkurl' => 'Javascript:Ottocrat_List_Js.showDuplicateSearchForm("'.Ottocrat_Request::encryptLink( "index.php?module='.$moduleModel->getName().'&view=MassActionAjax&mode=showDuplicatesSearchForm").'")',
				'linkicon' => ''
			);
		}

		return $advancedLinks;
	}

	/*
	 * Function to get Setting links
	 * @return array of setting links
	 */
	public function getSettingLinks() {
		return $this->getModule()->getSettingLinks();
	}

	/*
	 * Function to get Basic links
	 * @return array of Basic links
	 */
	public function getBasicLinks(){
		$basicLinks = array();
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		if($createPermission) {
			$basicLinks[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'LBL_ADD_RECORD',
					'linkurl' => $moduleModel->getCreateRecordUrl(),
					'linkicon' => ''
			);
		}
		return $basicLinks;
	}

	public function extendPopupFields($fieldsList) {
		$moduleModel = $this->get('module');
		$queryGenerator = $this->get('query_generator');

		$listFields = $moduleModel->getPopupViewFieldsList();
		
		$listFields[] = 'id';
		$listFields = array_merge($listFields, $fieldsList);
		$queryGenerator->setFields($listFields);
	}
}
