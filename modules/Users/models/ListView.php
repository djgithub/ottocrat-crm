<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Users_ListView_Model extends Ottocrat_ListView_Model {

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Ottocrat_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$linkTypes = array('LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
		$links = Ottocrat_Link_Model::getAllByType($this->getModule()->getId(), $linkTypes, $linkParams);

		$basicLinks = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_RECORD',
				'linkurl' => $this->getModule()->getCreateRecordUrl(),
				'linkicon' => ''
			)
		);
		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Ottocrat_Link_Model::getInstanceFromValues($basicLink);
		}
		$advancedLinks = $this->getAdvancedLinks();
		foreach($advancedLinks as $advancedLink) {
			$links['LISTVIEW'][] = Ottocrat_Link_Model::getInstanceFromValues($advancedLink);
		}

		return $links;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Ottocrat_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		return array();
	}

	/**
	 * Functions returns the query
	 * @return string
	 */
     public function getQuery() {
            $listQuery = parent::getQuery();
        //remove the status active condition since in users list view we need to consider inactive users as well
            $searchKey = $this->get('search_key');
            if(!empty($searchKey)) {
                $listQueryComponents = explode(" WHERE ottocrat_users.status='Active' AND", $listQuery);
                $listQuery = implode(' WHERE ', $listQueryComponents);
            }
            return $listQuery;
    }

	/**
	 * Function to get the list view entries
	 * @param Ottocrat_Paging_Model $pagingModel, $status (Active or Inactive User). Default false
	 * @return <Array> - Associative array of record id mapped to Ottocrat_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$queryGenerator = $this->get('query_generator');

		// Added as Users module do not have custom filters and id column is added by querygenerator.
		$fields = $queryGenerator->getFields();
		$fields[] = 'id';
		$queryGenerator->setFields($fields);
		
		return parent::getListViewEntries($pagingModel);
	}

	/*
	 * Function to give advance links of Users module
	 * @return array of advanced links
	 */
	public function getAdvancedLinks(){
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = array();
		$importPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Import');
		if($importPermission && $createPermission) {
                    $advancedLinks[] = array(
                        'linktype' => 'LISTVIEW',
                        'linklabel' => 'LBL_EXPORT',
                        'linkurl' => 'javascript:Settings_Users_List_Js.triggerExportAction()',
                        'linkicon' => ''
                    );
		}

		return $advancedLinks;
	}
}
