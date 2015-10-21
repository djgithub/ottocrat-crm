<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

include_once 'data/CRMEntity.php';
require_once 'vtlib/Ottocrat/Link.php';
include_once 'vtlib/Ottocrat/Module.php';
include_once('vtlib/Ottocrat/Menu.php');
require 'include/events/include.inc';
require_once 'include/utils/utils.php';

class PBXManager extends CRMEntity {
    
    protected $incominglinkLabel = 'Incoming Calls';
    protected $tabId = 0;
    protected $headerScriptLinkType = 'HEADERSCRIPT';
    protected $dependentModules = array('Contacts', 'Leads','Accounts');


    var $db;
    var $table_name = 'ottocrat_pbxmanager';
    var $table_index = 'pbxmanagerid';
    var $customFieldTable = Array('ottocrat_pbxmanagercf', 'pbxmanagerid');
    var $tab_name = Array('ottocrat_crmentity', 'ottocrat_pbxmanager', 'ottocrat_pbxmanagercf');
    var $tab_name_index = Array(
        'ottocrat_crmentity' => 'crmid',
        'ottocrat_pbxmanager' => 'pbxmanagerid',
        'ottocrat_pbxmanagercf' => 'pbxmanagerid');
    var $list_fields = Array(
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'ottocrat_'
        'Call Status'    => Array('ottocrat_pbxmanager', 'callstatus'),
        'Customer' => Array('ottocrat_pbxmanager', 'customer'),
        'User' => Array('ottocrat_pbxmanager', 'user'),
        'Recording'=> Array('ottocrat_pbxmanager', 'recordingurl'),
        'Start Time'=> Array('ottocrat_pbxmanager', 'starttime'),
    );
    var $list_fields_name = Array(
        /* Format: Field Label => fieldname */
        'Call Status' => 'callstatus',
        'Customer' => 'customer',
        'User'     => 'user',
        'Recording' => 'recordingurl',
        'Start Time' => 'starttime',
    );
    // Make the field link to detail view
    var $list_link_field = 'customernumber';
    // For Popup listview and UI type support
    var $search_fields = Array(
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'ottocrat_'
        'Customer' => Array('ottocrat_pbxmanager', 'customer'),
    );
    var $search_fields_name = Array(
        /* Format: Field Label => fieldname */
        'Customer' => 'customer',
    );
    // For Popup window record selection
    var $popup_fields = Array('customernumber');
    // For Alphabetical search
    var $def_basicsearch_col = 'customer';
    // Column value to use on detail view record text display
    var $def_detailview_recname = 'customernumber';
    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to ottocrat_field.fieldname values.
//    var $mandatory_fields = Array('assigned_user_id');
    var $column_fields = Array();
    var $default_order_by = 'customernumber';
    var $default_sort_order = 'ASC';
    
    function PBXManager(){
        $this->db = PearDatabase::getInstance();
        $this->column_fields = getColumnFields('PBXManager');
    }
    
     /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    function vtlib_handler($modulename, $event_type) {
         if ($event_type == 'module.postinstall') {
            $this->addLinksForPBXManager();
            $this->registerLookupEvents();
            $this->addSettingsLinks();
            $this->addActionMapping();
            $this->setModuleRelatedDependencies();
            $this->addUserExtensionField();
        } else if ($event_type == 'module.disabled') {
            $this->removeLinksForPBXManager();
            $this->unregisterLookupEvents();
            $this->removeSettingsLinks();
            $this->removeActionMapping();
            $this->unsetModuleRelatedDependencies();
        } else if ($event_type == 'module.enabled') {
            $this->addLinksForPBXManager();
            $this->registerLookupEvents();
            $this->addSettingsLinks();
            $this->addActionMapping();
            $this->setModuleRelatedDependencies();
        } else if ($event_type == 'module.preuninstall') {
            $this->removeLinksForPBXManager();
            $this->unregisterLookupEvents();
            $this->removeSettingsLinks();
            $this->removeActionMapping();
            $this->unsetModuleRelatedDependencies();
        } else if ($event_type == 'module.preupdate') {
            // TODO Handle actions before this module is updated.
        } else if ($event_type == 'module.postupdate') {
            // TODO Handle actions before this module is updated.
        }
    }
    
    
    /** Function to handle module specific operations when saving a entity
	*/
	function save_module($module){
	}
        
    /**
     * To add a phone extension field in user preferences page 
     */
    function addUserExtensionField(){
        global $log;
        $module = Ottocrat_Module::getInstance('Users');
        if ($module) {
            $module->initTables();
            $blockInstance = Ottocrat_Block::getInstance('LBL_MORE_INFORMATION', $module);
            if ($blockInstance) {
                $fieldInstance = new Ottocrat_Field();
                $fieldInstance->name = 'phone_crm_extension';
                $fieldInstance->label = 'CRM Phone Extension';
                $fieldInstance->uitype = 11;
                $fieldInstance->typeofdata = 'V~O';
                $blockInstance->addField($fieldInstance);
            }
        }
        $log->fatal('User Extension Field added');
    }
    
    /**
     * To register phone lookup events 
     */
    function registerLookupEvents(){
        global $log;
        $adb = PearDatabase::getInstance();
        $EventManager = new VTEventsManager($adb);
        $createEvent = 'ottocrat.entity.aftersave';
        $deleteEVent = 'ottocrat.entity.afterdelete';
        $restoreEvent = 'ottocrat.entity.afterrestore';
        $batchSaveEvent = 'ottocrat.batchevent.save';
        $batchDeleteEvent = 'ottocrat.batchevent.delete';
        $handler_path = 'modules/PBXManager/PBXManagerHandler.php';
        $className = 'PBXManagerHandler';
        $batchEventClassName = 'PBXManagerBatchHandler';
        $EventManager->registerHandler($createEvent, $handler_path, $className,'','["VTEntityDelta"]');
        $EventManager->registerHandler($deleteEVent, $handler_path, $className);
        $EventManager->registerHandler($restoreEvent, $handler_path, $className);
        $EventManager->registerHandler($batchSaveEvent, $handler_path, $batchEventClassName);
        $EventManager->registerHandler($batchDeleteEvent, $handler_path, $batchEventClassName);
        $log->fatal('Lookup Events Registered');
    }
    
    /**
     * To add PBXManager module in module($this->dependentModules) related lists
    */
    function setModuleRelatedDependencies(){
        global $log;
        $pbxmanager = Ottocrat_Module::getInstance('PBXManager');
        foreach ($this->dependentModules as $module) {
            $moduleInstance = Ottocrat_Module::getInstance($module);
            $moduleInstance->setRelatedList($pbxmanager,"PBXManager",array(),'get_dependents_list');
        }
        $log->fatal('Successfully added Module Related lists');
    }
    
    /**
     * To remove PBXManager module from module($this->dependentModules) related lists
    */
    function unsetModuleRelatedDependencies(){
        global $log;
        $pbxmanager = Ottocrat_Module::getInstance('PBXManager');
        foreach ($this->dependentModules as $module) {
            $moduleInstance = Ottocrat_Module::getInstance($module);
            $moduleInstance->unsetRelatedList($pbxmanager,"PBXManager",'get_dependents_list');
        }
        $log->fatal('Successfully removed Module Related lists');
    }
     
    /**
     * To unregister phone lookup events 
     */
    function unregisterLookupEvents(){
        global $log;
        $adb = PearDatabase::getInstance();
        $EventManager = new VTEventsManager($adb);
        $className = 'PBXManagerHandler';
        $batchEventClassName = 'PBXManagerBatchHandler';
        $EventManager->unregisterHandler($className);
        $EventManager->unregisterHandler($batchEventClassName);
        $log->fatal('Lookup Events Unregistered');
    }
    
     /**
     * To add a link in ottocrat_links which is to load our PBXManagerJS.js 
     */
     function addLinksForPBXManager() {
         global $log;
        $handlerInfo = array('path' => 'modules/PBXManager/PBXManager.php',
            'class' => 'PBXManager',
            'method' => 'checkLinkPermission');

        Ottocrat_Link::addLink($this->tabId, $this->headerScriptLinkType, $this->incominglinkLabel, 'modules/PBXManager/resources/PBXManagerJS.js','','',$handlerInfo);
        $log->fatal('Links added');
    }
    
     /**
     * To remove link for PBXManagerJS.js from ottocrat_links
     */
    function removeLinksForPBXManager() {
        global $log;
        //Deleting Headerscripts links
        Ottocrat_Link::deleteLink($this->tabId, $this->headerScriptLinkType, $this->incominglinkLabel,'modules/PBXManager/resources/PBXManagerJS.js');
        $log->fatal('Links Removed');
	}
    
    /**
     * To add Integration->PBXManager block in Settings page
    */
    function addSettingsLinks(){
        global $log;
        $adb = PearDatabase::getInstance();
        $integrationBlock = $adb->pquery('SELECT * FROM ottocrat_settings_blocks WHERE label=?',array('LBL_INTEGRATION'));
        $integrationBlockCount = $adb->num_rows($integrationBlock);
        
        // To add Block
        if($integrationBlockCount > 0){
            $blockid = $adb->query_result($integrationBlock, 0, 'blockid');
        }else{
            $blockid = $adb->getUniqueID('ottocrat_settings_blocks');
            $sequenceResult = $adb->pquery("SELECT max(sequence) as sequence FROM ottocrat_settings_blocks", array());
            if($adb->num_rows($sequenceResult)) {
                $sequence = $adb->query_result($sequenceResult, 0, 'sequence');
            }
            $adb->pquery("INSERT INTO ottocrat_settings_blocks(blockid, label, sequence) VALUES(?,?,?)", array($blockid, 'LBL_INTEGRATION', ++$sequence));
        }
        
        // To add a Field
        $fieldid = $adb->getUniqueID('ottocrat_settings_field');
        $adb->pquery("INSERT INTO ottocrat_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence, active)
            VALUES(?,?,?,?,?,?,?,?)", array($fieldid, $blockid, 'LBL_PBXMANAGER', '','PBXManager module Configuration',Ottocrat_Request:: encryptLink( 'index.php?module=PBXManager&parent=Settings&view=Index'), 2, 0));
        $log->fatal('Settings Block and Field added');
    }
    
    /**
     * To delete Integration->PBXManager block in Settings page
    */
    function removeSettingsLinks(){
        global $log;
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM ottocrat_settings_field WHERE name=?', array('LBL_PBXMANAGER'));
        $log->fatal('Settings Field Removed');
        
    }
    
     /**
     * To enable(ReceiveIncomingCall & MakeOutgoingCall) tool in profile
     */
     function addActionMapping() {
        global $log;
        $adb = PearDatabase::getInstance();
        $module = new Ottocrat_Module();
        $moduleInstance = $module->getInstance('PBXManager');
        
        //To add actionname as ReceiveIncomingcalls
        $maxActionIdresult = $adb->pquery('SELECT max(actionid+1) AS actionid FROM ottocrat_actionmapping',array());
        if($adb->num_rows($maxActionIdresult)) {
            $actionId = $adb->query_result($maxActionIdresult, 0, 'actionid');
        }
        $adb->pquery('INSERT INTO ottocrat_actionmapping
                     (actionid, actionname, securitycheck) VALUES(?,?,?)',array($actionId,'ReceiveIncomingCalls',0));
        $moduleInstance->enableTools('ReceiveIncomingcalls');
        $log->fatal('ReceiveIncomingcalls ActionName Added');
        
        //To add actionname as MakeOutgoingCalls
        $maxActionIdresult = $adb->pquery('SELECT max(actionid+1) AS actionid FROM ottocrat_actionmapping',array());
        if($adb->num_rows($maxActionIdresult)) {
            $actionId = $adb->query_result($maxActionIdresult, 0, 'actionid');
        }
        $adb->pquery('INSERT INTO ottocrat_actionmapping
                     (actionid, actionname, securitycheck) VALUES(?,?,?)',array($actionId,'MakeOutgoingCalls',0));
        $moduleInstance->enableTools('MakeOutgoingCalls');
        $log->fatal('MakeOutgoingCalls ActionName Added');
    }
    
    /**
     * To remove(ReceiveIncomingCall & MakeOutgoingCall) tool from profile
     */
    function removeActionMapping() {
        global $log;
        $adb = PearDatabase::getInstance();
        $module = new Ottocrat_Module();
        $moduleInstance = $module->getInstance('PBXManager');
        
        $moduleInstance->disableTools('ReceiveIncomingcalls');
        $adb->pquery('DELETE FROM ottocrat_actionmapping 
                     WHERE actionname=?', array('ReceiveIncomingCalls'));
        $log->fatal('ReceiveIncomingcalls ActionName Removed');
        
        $moduleInstance->disableTools('MakeOutgoingCalls');
        $adb->pquery('DELETE FROM ottocrat_actionmapping 
                      WHERE actionname=?', array('MakeOutgoingCalls'));
        $log->fatal('MakeOutgoingCalls ActionName Removed');
    }
    
    function checkLinkPermission($linkData){
        $module = new Ottocrat_Module();
        $moduleInstance = $module->getInstance('PBXManager');
        
        if($moduleInstance) {
            return true;
        }else {
            return false;
        }
    }
}