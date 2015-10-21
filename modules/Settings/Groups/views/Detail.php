<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Groups_Detail_View extends Settings_Ottocrat_Index_View {
    
    
    public function process(Ottocrat_Request $request) {
        
        $groupId = $request->get('record');		
        $qualifiedModuleName = $request->getModule(false);
        
        $recordModel = Settings_Groups_Record_Model::getInstance($groupId);
        
        $viewer = $this->getViewer($request);

		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        $viewer->view('DetailView.tpl',$qualifiedModuleName);

        
    }
}