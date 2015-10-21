<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ************************************************************************************/

class ProjectTask_Module_Model extends Ottocrat_Module_Model {

	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = parent::getSideBarLinks($linkParams);
		unset($links['SIDEBARLINK']);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_PROJECTS_LIST',
				'linkurl' => $this->getProjectsListUrl(),
				'linkicon' => '',
			),
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_TASKS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			),
            array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_MILESTONES_LIST',
				'linkurl' => $this->getMilestonesListUrl(),
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Ottocrat_Link_Model::getInstanceFromValues($quickLink);
		}

		return $links;
	}

	public function getProjectsListUrl() {
		$taskModel = Ottocrat_Module_Model::getInstance('Project');
		return $taskModel->getListViewUrl();
	}
	
    public function getMilestonesListUrl() {
    $milestoneModel = Ottocrat_Module_Model::getInstance('ProjectMilestone');
    return $milestoneModel->getListViewUrl();
}
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}

}