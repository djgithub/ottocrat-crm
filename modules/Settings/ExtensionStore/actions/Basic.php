<?php

/*
 * Copyright (C) www.ottocrat.com. All rights reserved.
 * @license Proprietary
 */

class Settings_ExtensionStore_Basic_Action extends Settings_Ottocrat_IndexAjax_View {

    protected $modelInstance;

    function __construct() {
        parent::__construct();
        $this->exposeMethod('postReview');
        $this->exposeMethod('getDateString');
        $this->exposeMethod('uninstallExtension');
        $this->exposeMethod('registerAccount');
        $this->exposeMethod('updateTrialMode');
        $this->exposeMethod('updateCardDetails');
        $this->exposeMethod('logoutMarketPlace');
    }

    function process(Ottocrat_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    protected function getModelInstance() {
        if(!isset($this->modelInstance)){
            $this->modelInstance = Settings_ExtensionStore_Extension_Model::getInstance();
        }
        return $this->modelInstance;
    }
    
    protected function postReview(Ottocrat_Request $request) {
        $listing = $request->get('listing');
        $comment = $request->get('comment');
        $rating = $request->get('rating');
        $modelInstance = $this->getModelInstance();
        
        $postResponse = $modelInstance->postReview($listing, $comment, $rating);
        $postResult = $postResponse['result'];
        $createdTime = str_replace('T', " ", $postResult['createdon']);
        $postResult['createdon'] = $this->getDateString($createdTime);
        $customerInfo = $modelInstance->getCustomerDetails($postResult['CustomerId']);
        $postResult['Customer'] = $customerInfo['result'];
        $response = new Ottocrat_Response();
        $response->setResult($postResult);
        $response->emit();
    }

    protected function getDateString($date) {
        $dateString = Ottocrat_Util_Helper::formatDateTimeIntoDayString($date);
        return $dateString;
    }


    protected function logoutMarketPlace(Ottocrat_Request $request) { 
         $modelInstance = $this->getModelInstance(); 
         $modelInstance->logoutMarketPlace($request);
    }


    protected function uninstallExtension(Ottocrat_Request $request) {
        $extensionName = $request->get('extensionName');
        $extensionInstance = Settings_ExtensionStore_Extension_Model::getModuleFromExtnName($extensionName);
        $extnType = $extensionInstance->get('extnType');
        $response = new Ottocrat_Response();
        
        if($extnType == 'module'){
            $extensionInstance->delete();
            //Remove extension files
            global $root_directory;
            $deletePaths = array();
            $pathToExtensionLayouts = $root_directory . "layouts/vlayout/modules/$extensionName";
            $layoutDirAccessible = isFileAccessible($pathToExtensionLayouts);
            if($layoutDirAccessible){
                $deletePaths['layouts'] = $pathToExtensionLayouts;
            }

            $pathToExtensionModule = $root_directory . "modules/$extensionName";
            $moduleDirAccessible = isFileAccessible($pathToExtensionModule);
            if($moduleDirAccessible){
                $deletePaths['modules'] = $pathToExtensionModule; 
            }

            foreach ($deletePaths as $dirName) {
                $this->deleteRecursively($dirName);
            }
            $response->setResult(array('success' => true, 'message' => 'extension deleted'));
        }else if($extnType == 'language'){
            $languageInstance = Settings_ExtensionStore_Extension_Model::getLanguageInstance($extensionName);
            if($languageInstance) {
                $langPrefix = $languageInstance->get('prefix');
                Ottocrat_Language::deregister($langPrefix);
                //remove files
                $langDir = "languages/$langPrefix";
                if(isFileAccessible($langDir)) {
                    $this->deleteRecursively($langDir);
                }
            }
            $response->setResult(array('success' => true, 'message' => 'extension deleted'));
        }  else {
            $response->setError('Error in deleting extension');
        }    
        $response->emit();
    }
    
    //Source from http://php.net/manual/en/function.rmdir.php
    protected function deleteRecursively($dirName){
        $files = array_diff(scandir($dirName), array('.','..')); 
        foreach ($files as $file) { 
            (is_dir("$dirName/$file")) ? $this->deleteRecursively("$dirName/$file") : unlink("$dirName/$file"); 
            $afterDeletefiles = array_diff(scandir($dirName), array('.','..'));
            if(count($afterDeletefiles) == 0){
                rmdir($dirName);
            }
        }
        return true;
    }

    protected function registerAccount(Ottocrat_Request $request) {
        $options = array();
        $userAction = $request->get('userAction');
        $options['emailAddress'] = $request->get('emailAddress');
        $options['password'] = $request->get('password');
        $modelInstance = $this->getModelInstance();
        
        if ($userAction == 'signup') {
            $options['firstName'] = $request->get('firstName');
            $options['lastName'] = $request->get('lastName');
            $options['companyName'] = $request->get('companyName');
            $options['confirmPassword'] = $request->get('confirmPassword');
            $profieInfo = $modelInstance->signup($options);
        } elseif ($userAction == 'login') {
            $options['savePassword'] = ($request->get('savePassword') == 'true') ? true:false;
            $options['password'] = md5($options['password']);
            $profieInfo = $modelInstance->login($options);
        } elseif ($userAction == 'register') {
            $options['savePassword'] = $request->get('savePassword');
            $options['password'] = $options['password'];
            $profieInfo = $modelInstance->register($options);
        }
        $response = new Ottocrat_Response();
        if ($profieInfo['success'] != 'true') {
            $response->setError('', $profieInfo['error']);
        } else {
            $response->setResult($profieInfo['result']);
        }
        $response->emit();
    }

    protected function updateTrialMode(Ottocrat_Request $request) {
        $response = new Ottocrat_Response();
        $importedModuleName = $request->get('extensionName');
        $trial = $request->get('trial');
        $db = PearDatabase::getInstance();
        $db->pquery('UPDATE ottocrat_tab SET trial = ? WHERE name = ?', array($trial, $importedModuleName));
        $response->setResult(array('success' => true, 'message' => 'Extension Store Installed'));
        $response->emit();
    }
    
    protected function updateCardDetails(Ottocrat_Request $request) {
        $number = $request->get('cardNumber');
        $expmonth = (int) $request->get('expMonth');
        $expyear = (int) $request->get('expYear');
        $cvc = (int) $request->get('cvccode');
        $customerId = (int) $request->get('customerId');
        $customerCardId = (int) $request->get('customerCardId');
        $modelInstance = $this->getModelInstance();

        if (empty($customerCardId)) {
            $result = $modelInstance->createCard($number, $expmonth, $expyear, $cvc);
        } else {
            $result = $modelInstance->updateCard($number, $expmonth, $expyear, $cvc, $customerId);
        }
        $response = new Ottocrat_Response();
        if ($result['success'] != 'true') {
            $response->setError('', $result['error']);
        } else {
            $response->setResult($result['result']);
        }
        $response->emit();
    }
    
    public function validateRequest(Ottocrat_Request $request) {
        $request->validateWriteAccess();
    }
}
