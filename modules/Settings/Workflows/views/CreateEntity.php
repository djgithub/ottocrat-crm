<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Workflows_CreateEntity_View extends Settings_Ottocrat_Index_View {

	public function process(Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$workflowId = $request->get('for_workflow');
		$workflowModel = Settings_Workflows_Record_Model::getInstance($workflowId);

		$relatedModule = $request->get('relatedModule');
		$relatedModuleModel = Ottocrat_Module_Model::getInstance($relatedModule);

		$workflowModuleModel = $workflowModel->getModule();

		$viewer->assign('WORKFLOW_MODEL', $workflowModel);
		$viewer->assign('REFERENCE_FIELD_NAME', $workflowModel->getReferenceFieldName($relatedModule));
		$viewer->assign('RELATED_MODULE_MODEL', $relatedModuleModel);
		$viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());
		$viewer->assign('MODULE_MODEL', $workflowModuleModel);
		$viewer->assign('SOURCE_MODULE',$workflowModuleModel->getName());
		$viewer->assign('RELATED_MODULE_MODEL_NAME','');
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('CreateEntity.tpl', $qualifiedModuleName);
	}
}