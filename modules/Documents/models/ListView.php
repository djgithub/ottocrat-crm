<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_ListView_Model extends Ottocrat_ListView_Model {

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

		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		if($createPermission) {
			$basicLinks = array(
					array(
							'linktype' => 'LISTVIEWBASIC',
							'linklabel' => 'LBL_ADD_RECORD',
							'linkurl' => $moduleModel->getCreateRecordUrl(),
							'linkicon' => ''
					),
					array(
							'linktype' => 'LISTVIEWBASIC',
							'linklabel' => 'LBL_ADD_FOLDER',
							'linkurl' => 'javascript:Documents_List_Js.triggerAddFolder("'.$moduleModel->getAddFolderUrl().'")',
							'linkicon' => ''
					)
			);
			foreach($basicLinks as $basicLink) {
				$links['LISTVIEWBASIC'][] = Ottocrat_Link_Model::getInstanceFromValues($basicLink);
			}
		}

		$exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
		if($exportPermission) {
			$advancedLink = array(
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_EXPORT',
					'linkurl' => 'javascript:Ottocrat_List_Js.triggerExportAction("'.$moduleModel->getExportUrl().'")',
					'linkicon' => ''
			);
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

                //Opensource fix to make documents module mass editable
                if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) { 
                    $massActionLink = array( 
                                'linktype' => 'LISTVIEWMASSACTION', 
                                'linklabel' => 'LBL_EDIT', 
                                'linkurl' => 'javascript:Ottocrat_List_Js.triggerMassEdit("'.Ottocrat_Request:: encryptLink('index.php?module='.$moduleModel->get('name').'&view=MassActionAjax&mode=showMassEditForm').'");',
                                'linkicon' => '' 
                        ); 
                    $links['LISTVIEWMASSACTION'][] = Ottocrat_Link_Model::getInstanceFromValues($massActionLink); 
                } 
        
		if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:Ottocrat_List_Js.massDeleteRecords("'.Ottocrat_Request:: encryptLink('index.php?module=' . $moduleModel->getName() . '&action=MassDelete').'");',
				'linkicon' => ''
			);

			$links['LISTVIEWMASSACTION'][] = Ottocrat_Link_Model::getInstanceFromValues($massActionLink);
		}

		$massActionLink = array(
			'linktype' => 'LISTVIEWMASSACTION',
			'linklabel' => 'LBL_MOVE',
			'linkurl' => 'javascript:Documents_List_Js.massMove("'.Ottocrat_Request:: encryptLink('index.php?module=' . $moduleModel->getName() . '&view=MoveDocuments').'");',
			'linkicon' => ''
		);

		$links['LISTVIEWMASSACTION'][] = Ottocrat_Link_Model::getInstanceFromValues($massActionLink);

		return $links;
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

        $folderKey = $this->get('folder_id');
        $folderValue = $this->get('folder_value');
        if(!empty($folderValue)) {
            $queryGenerator->addCondition($folderKey,$folderValue,'e');
        }

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
		if (!empty($orderBy) && $orderBy === 'smownerid') { 
			$fieldModel = Ottocrat_Field_Model::getInstance('assigned_user_id', $moduleModel); 
			if ($fieldModel->getFieldDataType() == 'owner') { 
				$orderBy = 'COALESCE(CONCAT(ottocrat_users.first_name,ottocrat_users.last_name),ottocrat_groups.groupname)'; 
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
                        $referenceNameFieldOrderBy[] = getSqlForNameInDisplayFormat(array('first_name'=>$columnList[0],'last_name'=>$columnList[1]),'Users').' '.$sortOrder;
                    } else {
                        $referenceNameFieldOrderBy[] = implode('', $columnList).' '.$sortOrder ;
                    }
                }
                $listQuery .= ' ORDER BY '. implode(',',$referenceNameFieldOrderBy);
            }else{
                $listQuery .= ' ORDER BY '. $orderBy . ' ' .$sortOrder;
            }
		}

		$viewid = ListViewSession::getCurrentView($moduleName);
        if(empty($viewid)){
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

}