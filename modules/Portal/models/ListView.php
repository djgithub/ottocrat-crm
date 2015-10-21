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
 * Portal ListView Model Class
 */
class Portal_ListView_Model extends Ottocrat_ListView_Model {
    
	public function getListViewEntries($pagingModel) {
        $db = PearDatabase::getInstance();
        $moduleModel = Ottocrat_Module_Model::getInstance('Portal');
        
		$listQuery = $this->getQuery();

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();
        
        $orderBy = $this->get('orderby');
        $sortOrder = $this->get('sortorder');

        if(!empty($orderBy))
            $listQuery .= ' ORDER BY '.$orderBy.' '.$sortOrder;
        

		$listQuery .= " LIMIT $startIndex,".($pageLimit);
        
		$listResult = $db->pquery($listQuery, array());

		$listViewEntries = array();
        
        for($i = 0; $i < $db->num_rows($listResult); $i++) {
            $row = $db->fetch_row($listResult, $i);
            $listViewEntries[$row['portalid']] = array();
            $listViewEntries[$row['portalid']]['portalname'] = $row['portalname'];
            $listViewEntries[$row['portalid']]['portalurl'] = $row['portalurl'];
            $listViewEntries[$row['portalid']]['createdtime'] = Ottocrat_Date_UIType::getDisplayDateValue($row['createdtime']);
        }
        $index = 0;
		foreach($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
		}
        
        return $listViewRecordModels;
    }
    
    public function getQuery() {
        $query = 'SELECT portalid, portalname, portalurl, createdtime FROM ottocrat_portal';
        $searchValue = $this->get('search_value');
        if(!empty($searchValue))
            $query .= " WHERE portalname LIKE '".$searchValue."%'";
        
        return $query;
    }

    public function calculatePageRange($record, $pagingModel) {
        $pageLimit = $pagingModel->getPageLimit();
        $page = $pagingModel->get('page');
        
        $startSequence = ($page - 1) * $pageLimit + 1;
        $endSequence = $startSequence + count($record) - 1;
        $recordCount = Portal_ListView_Model::getRecordCount();
        
        $pageCount = intval($recordCount / $pageLimit);
        if(($recordCount % $pageLimit) != 0)
            $pageCount++;
        if($pageCount == 0)
            $pageCount = 1;
        if($page < $pageCount)
            $nextPageExists = true;
        else
            $nextPageExists = false;
        
        $result = array(
            'startSequence' => $startSequence,
            'endSequence' => $endSequence,
            'recordCount' => $recordCount,
            'pageCount' => $pageCount,
            'nextPageExists' => $nextPageExists,
            'pageLimit' => $pageLimit
        );
        
        return $result;
    }
    
    public function getRecordCount() {
        $db = PearDatabase::getInstance();
        $listQuery = $this->getQuery();
        $queryParts = explode('FROM', $listQuery);
        $query = 'SELECT COUNT(*) AS count FROM '.$queryParts[1];
        $result = $db->pquery($query, array());
        
        return $db->query_result($result, 0, 'count');
    }
}
