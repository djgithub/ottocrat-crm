<?php

/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Leads_Edit_View extends Ottocrat_Edit_View {

	public function process(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
        $recordModel = $this->record;
        if(!$recordModel){
            if (!empty($recordId)) {
                $recordModel = Ottocrat_Record_Model::getInstanceById($recordId, $moduleName);
            } else {
                $recordModel = Ottocrat_Record_Model::getCleanInstance($moduleName);
            }
        }

		$viewer = $this->getViewer($request);

		$salutationFieldModel = Ottocrat_Field_Model::getInstance('salutationtype', $recordModel->getModule());
		// Fix for http://trac.ottocrat.com/cgi-bin/trac.cgi/ticket/7851
		$salutationType = $request->get('salutationtype');
		if(!empty($salutationType)){ 
                    $salutationFieldModel->set('fieldvalue', $request->get('salutationtype')); 
                } 
                else{ 
                    $salutationFieldModel->set('fieldvalue', $recordModel->get('salutationtype')); 
                } 
		$viewer->assign('SALUTATION_FIELD_MODEL', $salutationFieldModel);

		parent::process($request);
	}

}
