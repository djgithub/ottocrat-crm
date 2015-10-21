<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Block_Action extends Settings_Ottocrat_Index_Action {
    
    public function __construct() {
        $this->exposeMethod('save');
        $this->exposeMethod('updateSequenceNumber');
        $this->exposeMethod('delete');
    }
    
    public function save(Ottocrat_Request $request) {
        $blockId = $request->get('blockid');
        $sourceModule = $request->get('sourceModule');
        $modueInstance = Ottocrat_Module_Model::getInstance($sourceModule);

        if(!empty($blockId)) {
            $blockInstance = Settings_LayoutEditor_Block_Model::getInstance($blockId);
            $blockInstance->set('display_status',$request->get('display_status'));
			$isDuplicate = false;
        } else {
            $blockInstance = new Settings_LayoutEditor_Block_Model();
            $blockInstance->set('label', $request->get('label'));
			$blockInstance->set('iscustom', '1');
             //Indicates block id after which you need to add the new block
            $beforeBlockId = $request->get('beforeBlockId');
            if(!empty($beforeBlockId)) {
                $beforeBlockInstance = Ottocrat_Block_Model::getInstance($beforeBlockId);
				$beforeBlockSequence = $beforeBlockInstance->get('sequence');
				$newBlockSequence = ($beforeBlockSequence+1);
				//To give sequence one more than prev block 
                $blockInstance->set('sequence', $newBlockSequence);
				//push all other block down so that we can keep new block there
				Ottocrat_Block_Model::pushDown($beforeBlockSequence, $modueInstance->getId());
            }
			$isDuplicate = Ottocrat_Block_Model::checkDuplicate($request->get('label'), $modueInstance->getId());
        }

		$response = new Ottocrat_Response();
		if (!$isDuplicate) {
			try{
				$id = $blockInstance->save($modueInstance);
				$responseInfo = array('id'=>$id,'label'=>$blockInstance->get('label'),'isCustom'=>$blockInstance->isCustomized(), 'beforeBlockId'=>$beforeBlockId, 'isAddCustomFieldEnabled'=>$blockInstance->isAddCustomFieldEnabled());
				if(empty($blockId)) {
					//if mode is create add all blocks sequence so that client will place the new block correctly
					$responseInfo['sequenceList'] = Ottocrat_Block_Model::getAllBlockSequenceList($modueInstance->getId());
				}
				$response->setResult($responseInfo);
			} catch(Exception $e) {
				$response->setError($e->getCode(),$e->getMessage());
			}
		} else {
			$response->setError('502', vtranslate('LBL_DUPLICATES_EXIST', $request->getModule(false)));
		}
        $response->emit();
    }
    
    public function updateSequenceNumber(Ottocrat_Request $request) {
        $response = new Ottocrat_Response();
        try{
            $sequenceList = $request->get('sequence');
            Ottocrat_Block_Model::updateSequenceNumber($sequenceList);
            $response->setResult(array('success'=>true));
        }catch(Exception $e) {
            $response->setError($e->getCode(),$e->getMessage());
        }
        $response->emit();
    }
    
    
    public function delete(Ottocrat_Request $request) {
        $response = new Ottocrat_Response();
        $blockId = $request->get('blockid');
        $checkIfFieldsExists = Ottocrat_Block_Model::checkFieldsExists($blockId);
        if($checkIfFieldsExists) {
            $response->setError('502','Fields exists for the block');
            $response->emit();
            return;
        }
        $blockInstance = Ottocrat_Block_Model::getInstance($blockId);
        if(!$blockInstance->isCustomized()) {
            $response->setError('502','Cannot delete non custom blocks');
            $response->emit();
            return;
        }
        try{
            $blockInstance->delete(false);
            $response->setResult(array('success'=>true));
        }catch(Exception $e) {
            $response->setError($e->getCode(),$e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Ottocrat_Request $request) { 
        $request->validateWriteAccess(); 
    } 

}