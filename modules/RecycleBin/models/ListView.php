<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class RecycleBin_ListView_Model extends Ottocrat_ListView_Model {

	/**
	 * Static Function to get the Instance of Ottocrat ListView model for a given module and custom view
	 * @param <String> $moduleName - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Ottocrat_ListView_Model instance
	 */
	public static function getInstance($moduleName, $sourceModule) {
		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'ListView', $moduleName);
		$instance = new $modelClassName();

		$sourceModuleModel = Ottocrat_Module_Model::getInstance($sourceModule);
		$queryGenerator = new QueryGenerator($sourceModuleModel->get('name'), $currentUser);
		$cvidObj = CustomView_Record_Model::getAllFilterByModule($sourceModuleModel->get('name')); 
        $cvid = $cvidObj->getId('cvid'); 
        $queryGenerator->initForCustomViewById($cvid);

		$controller = new ListViewController($db, $currentUser, $queryGenerator);

		return $instance->set('module', $sourceModuleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
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

        $orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');

		if(!empty($orderBy)){
            $columnFieldMapping = $moduleModel->getColumnFieldMapping();
            $orderByFieldName = $columnFieldMapping[$orderBy];
            $orderByFieldModel = $moduleModel->getField($orderByFieldName);
            if($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Ottocrat_Field_Model::REFERENCE_TYPE){
                //IF it is reference add it in the where fields so that from clause will be having join of the table
                $queryGenerator = $this->get('query_generator');
                $queryGenerator->addWhereField($orderByFieldName);
            }
        }
		if (!empty($orderBy) && $orderBy === 'smownerid') {
			$fieldModel = Ottocrat_Field_Model::getInstance('assigned_user_id', $moduleModel);
			if ($fieldModel->getFieldDataType() == 'owner') {
				$orderBy = 'COALESCE(CONCAT(ottocrat_users.first_name,ottocrat_users.last_name),ottocrat_groups.groupname)';
			}
		}

		$listQuery = $this->getQuery();
		$listQuery = preg_replace("/ottocrat_crmentity.deleted\s*=\s*0/i", 'ottocrat_crmentity.deleted = 1', $listQuery);

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
                        $columnList[] = $fieldModel->get('table').'.'.$fieldModel->get('column');
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

		$listQuery = $queryGenerator->getQuery();
		$listQuery = preg_replace("/ottocrat_crmentity.deleted\s*=\s*0/i", 'ottocrat_crmentity.deleted = 1', $listQuery);

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
		$listViewCount = $db->query_result($listResult, 0, 'count');
		return $listViewCount;
	}

}