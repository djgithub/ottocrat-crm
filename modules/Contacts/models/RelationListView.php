<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_RelationListView_Model extends Ottocrat_RelationListView_Model {
    
    public function getCreateViewUrl(){
        $createViewUrl = parent::getCreateViewUrl();
		$relationModuleModel = $this->getRelationModel()->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();

        //if parent module has account id it should be related to Potentials
        if($parentRecordModule->get('account_id') && $relationModuleModel->getName() == 'Potentials') {
            $createViewUrl .= '&related_to='.$parentRecordModule->get('account_id');
        }
		return $createViewUrl;
	}
}