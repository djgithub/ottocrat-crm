<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Rss_Module_Model extends Ottocrat_Module_Model {

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Ottocrat_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = Ottocrat_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_ADD_FEED_SOURCE',
				'linkurl' => $this->getDefaultUrl(),
				'linkicon' => '',
			)
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Ottocrat_Link_Model::getInstanceFromValues($quickLink);
		}
        $quickWidgets = array(
			array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_RSS_FEED_SOURCES',
				'linkurl' => 'module='.$this->get('name').'&view=ViewTypes&mode=getRssWidget',
				'linkicon' => ''
			),
		);
		foreach($quickWidgets as $quickWidget) {
			$links['SIDEBARWIDGET'][] = Ottocrat_Link_Model::getInstanceFromValues($quickWidget);
		}
        
		return $links;
	}
    
    /**
     * Function to get rss sources list
     */
    public function getRssSources() { 
        $db = PearDatabase::getInstance();
        
        $sql = 'Select *from ottocrat_rss';
        $result = $db->pquery($sql, array());
        $noOfRows = $db->num_rows($result);

		$records = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['rssid'];
			$records[$row['id']] = $this->getRecordFromArray($row);
		}
        return $records;
    }
}
