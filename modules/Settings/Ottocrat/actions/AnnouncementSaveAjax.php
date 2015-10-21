<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_AnnouncementSaveAjax_Action extends Settings_Ottocrat_Basic_Action {
    
    public function process(Ottocrat_Request $request) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $annoucementModel = Settings_Ottocrat_Announcement_Model::getInstanceByCreator($currentUser);
        $annoucementModel->set('announcement',$request->get('announcement'));
        $annoucementModel->save();
        $responce = new Ottocrat_Response();
        $responce->setResult(array('success'=>true));
        $responce->emit();
    }
    
    public function validateRequest(Ottocrat_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}