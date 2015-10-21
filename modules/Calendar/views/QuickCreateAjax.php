<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_QuickCreateAjax_View extends Ottocrat_QuickCreateAjax_View {

	public function  process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();

		$moduleList = array('Calendar','Events');

		$quickCreateContents = array();
		foreach($moduleList as $module) {
			$info = array();

			$recordModel = Ottocrat_Record_Model::getCleanInstance($module);
			$moduleModel = $recordModel->getModule();

			$fieldList = $moduleModel->getFields();
			$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

			foreach($requestFieldList as $fieldName => $fieldValue) {
				$fieldModel = $fieldList[$fieldName];
				if($fieldModel->isEditable()) {
					$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
				}
			}

			$recordStructureInstance = Ottocrat_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Ottocrat_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);

			$info['recordStructureModel'] = $recordStructureInstance;
			$info['recordStructure'] = $recordStructureInstance->getStructure();
			$info['moduleModel'] = $moduleModel;
			$quickCreateContents[$module] = $info;
		}
		$picklistDependencyDatasource = Ottocrat_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer = $this->getViewer($request);
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUICK_CREATE_CONTENTS', $quickCreateContents);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));

		$viewer->view('QuickCreate.tpl', $moduleName);
	}
}
