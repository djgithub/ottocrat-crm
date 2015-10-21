<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/vtlib/Ottocrat/Module.php');

/**
 * Ottocrat Module Model Class
 */
class Ottocrat_Module_Model extends Ottocrat_Module {

	protected $blocks = false;
	protected $nameFields = false;
	protected $moduleMeta = false;
	protected $fields = false;
	protected $relations = null;

	/**
	 * Function to get the Module/Tab id
	 * @return <Number>
	 */
	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	public function isEntityModule() {
		return ($this->isentitytype== '1') ? true :false ;
	}

	/**
	 * Function to check whether the module is enabled for quick create
	 * @return <Boolean> - true/false
	 */
	public function isQuickCreateSupported() {
		return $this->isEntityModule();
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return true;
	}

	/**
	 * Function to get singluar label key
	 * @return <String> - Singular module label key
	 */
	public function getSingularLabelKey(){
		return 'SINGLE_'.$this->get('name');
	}

	/**
	 * Function to get the value of a given property
	 * @param <String> $propertyName
	 * @return <Object>
	 * @throws Exception
	 */
	public function get($propertyName) {
		if(property_exists($this,$propertyName)){
			return $this->$propertyName;
		}
		throw new Exception( $propertyName.' doest not exists in class '.get_class($this));
	}

	/**
	 * Function to set the value of a given property
	 * @param <String> $propertyName
	 * @param <Object> $propertyValue
	 * @return Ottocrat_Module_Model instance
	 */
	public function set($propertyName, $propertyValue) {
		$this->$propertyName = $propertyValue;
		return $this;
	}

	/**
	 * Function checks if the module is Active
	 * @return <Boolean>
	 */
	public function isActive() {
		return in_array($this->get('presence'), array(0,2));
	}

	/**
	 * Function checks if the module is enabled for tracking changes
	 * @return <Boolean>
	 */
	public function isTrackingEnabled() {
		require_once 'modules/ModTracker/ModTracker.php';
		$trackingEnabled = ModTracker::isTrackingEnabledForModule($this->getName());
		return ($this->isActive() && $trackingEnabled);
	}

	/**
	 * Function checks if comment is enabled
	 * @return boolean
	 */
	public function isCommentEnabled() {
		$enabled = false;
		$db = PearDatabase::getInstance();
		$commentsModuleModel = Ottocrat_Module_Model::getInstance('ModComments');
		if($commentsModuleModel && $commentsModuleModel->isActive()) {
			$relatedToFieldResult = $db->pquery('SELECT fieldid FROM ottocrat_field WHERE fieldname = ? AND tabid = ?',
					array('related_to', $commentsModuleModel->getId()));
			$fieldId = $db->query_result($relatedToFieldResult, 0, 'fieldid');
			if(!empty($fieldId)) {
				$relatedModuleResult = $db->pquery('SELECT relmodule FROM ottocrat_fieldmodulerel WHERE fieldid = ?', array($fieldId));
				$rows = $db->num_rows($relatedModuleResult);

				for($i=0; $i<$rows; $i++) {
					$relatedModule = $db->query_result($relatedModuleResult, $i, 'relmodule');
					if($this->getName() == $relatedModule) {
						$enabled = true;
					}
				}
			}
		} else {
			$enabled = false;
		}
		return $enabled;
	}

	/**
	 * Function to save a given record model of the current module
	 * @param Ottocrat_Record_Model $recordModel
	 */
	public function saveRecord($recordModel) {
		$moduleName = $this->get('name');
		$focus = CRMEntity::getInstance($moduleName);
		$fields = $focus->column_fields;
		foreach($fields as $fieldName => $fieldValue) {
			$fieldValue = $recordModel->get($fieldName);
			if(is_array($fieldValue)){
                $focus->column_fields[$fieldName] = $fieldValue;
            }else if($fieldValue !== null) {
				$focus->column_fields[$fieldName] = decode_html($fieldValue);
			}
		}
		$focus->mode = $recordModel->get('mode');
		$focus->id = $recordModel->getId();
		$focus->save($moduleName);
		return $recordModel->setId($focus->id);
	}

	/**
	 * Function to delete a given record model of the current module
	 * @param Ottocrat_Record_Model $recordModel
	 */
	public function deleteRecord($recordModel) {
		$moduleName = $this->get('name');
		$focus = CRMEntity::getInstance($moduleName);
		$focus->trash($moduleName, $recordModel->getId());
		if(method_exists($focus, 'transferRelatedRecords')) {
			if($recordModel->get('transferRecordIDs'))
				$focus->transferRelatedRecords($moduleName, $recordModel->get('transferRecordIDs'), $recordModel->getId());
		}
	}

	/**
	 * Function to get the module meta information
	 * @param <type> $userModel - user model
	 */
	public function getModuleMeta($userModel = false) {
		if(empty($this->moduleMeta)){
			if(empty($userModel)) {
			$userModel = Users_Record_Model::getCurrentUserModel();
		}
			$this->moduleMeta = Ottocrat_ModuleMeta_Model::getInstance($this->get('name'), $userModel);
		}
		return $this->moduleMeta;
	}

    //Note : This api is using only in RelationListview - for getting columnfields of Related Module
    //Need to review........

	/**
	 * Function to get the module field mapping
	 * @return <array>
	 */
	public function getColumnFieldMapping(){
		$moduleMeta = $this->getModuleMeta();
		$meta = $moduleMeta->getMeta();
		$fieldColumnMapping =  $meta->getFieldColumnMapping();
		return array_flip($fieldColumnMapping);
	}

	/**
	 * Function to get the ListView Component Name
	 * @return string
	 */
	public function getListViewName() {
		return 'List';
	}

	/**
	 * Function to get the DetailView Component Name
	 * @return string
	 */
	public function getDetailViewName() {
		return 'Detail';
	}

	/**
	 * Function to get the EditView Component Name
	 * @return string
	 */
	public function getEditViewName(){
		return 'Edit';
	}

	/**
	 * Function to get the DuplicateView Component Name
	 * @return string
	 */
	public function getDuplicateViewName(){
		return 'Edit';
	}

	/**
	 * Function to get the Delete Action Component Name
	 * @return string
	 */
	public function getDeleteActionName() {
		return 'Delete';
	}

	/**
	 * Function to get the Default View Component Name
	 * @return string
	 */
	public function getDefaultViewName() {
		return 'List';
	}

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		//return 'index.php?module='.$this->get('name').'&view='.$this->getDefaultViewName();
		$module_url='index.php?module='.$this->get('name').'&view='.$this->getDefaultViewName();
		return Ottocrat_Request::encryptLink($module_url);
	}

	/**
	 * Function to get the url for list view of the module
	 * @return <string> - url
	 */
	public function getListViewUrl() {
		//return 'index.php?module='.$this->get('name').'&view='.$this->getListViewName();
		$module_url='index.php?module='.$this->get('name').'&view='.$this->getListViewName();
		return Ottocrat_Request::encryptLink($module_url);
	}

	/**
	 * Function to get the url for the Create Record view of the module
	 * @return <String> - url
	 */
	public function getCreateRecordUrl() {
		//return 'index.php?module='.$this->get('name').'&view='.$this->getEditViewName();
		$module_url='index.php?module='.$this->get('name').'&view='.$this->getEditViewName();
		return Ottocrat_Request::encryptLink($module_url);
	}

	/**
	 * Function to get the url for the Create Record view of the module
	 * @return <String> - url
	 */
	public function getQuickCreateUrl() {
		//return 'index.php?module='.$this->get('name').'&view=QuickCreateAjax';
		$module_url='index.php?module='.$this->get('name').'&view=QuickCreateAjax';
		return Ottocrat_Request::encryptLink($module_url);

	}

	/**
	 * Function to get the url for the Import action of the module
	 * @return <String> - url
	 */
	public function getImportUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module='.$this->get('name').'&view=Import');
	}

	/**
	 * Function to get the url for the Export action of the module
	 * @return <String> - url
	 */
	public function getExportUrl() {
		return 'index.php?module='.$this->get('name').'&view=Export';
	}

	/**
	 * Function to get the url for the Find Duplicates action of the module
	 * @return <String> - url
	 */
	public function getFindDuplicatesUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module='.$this->get('name').'&view=FindDuplicates');
	}

	/**
	 * Function to get the url to view Dashboard for the module
	 * @return <String> - url
	 */
	public function getDashBoardUrl() {
	//	return 'index.php?module='. $this->get('name').'&view=DashBoard';
		$module_url='index.php?module='. $this->get('name').'&view=DashBoard';
		return Ottocrat_Request::encryptLink($module_url);
	}

	/**
	 * Function to get the url to view Details for the module
	 * @return <String> - url
	 */
	public function getDetailViewUrl($id) {
		//return 'index.php?module='. $this->get('name').'&view='.$this->getDetailViewName().'&record='.$id;
		$module_url='index.php?module='. $this->get('name').'&view='.$this->getDetailViewName().'&record='.$id;
		return Ottocrat_Request::encryptLink($module_url);
	}
	/**
	 * Function to get a Ottocrat Record Model instance from an array of key-value mapping
	 * @param <Array> $valueArray
	 * @return Ottocrat_Record_Model or Module Specific Record Model instance
	 */
	public function getRecordFromArray($valueArray, $rawData=false) {
		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Record', $this->get('name'));
		$recordInstance = new $modelClassName();
		return $recordInstance->setData($valueArray)->setModuleFromInstance($this)->setRawData($rawData);
	}

	/**
	 * Function returns all the blocks for the module
	 * @return <Array of Ottocrat_Block_Model> - list of block models
	 */
	public function getBlocks() {
		if(empty($this->blocks)) {
			$blocksList = array();
			$moduleBlocks = Ottocrat_Block_Model::getAllForModule($this);
			foreach($moduleBlocks as $block){
				$blocksList[$block->get('label')] = $block;
			}
			$this->blocks = $blocksList;
		}
		return $this->blocks;
	}

	/**
	 * Function that returns all the fields for the module
	 * @return <Array of Ottocrat_Field_Model> - list of field models
	 */
	public function getFields($blockInstance=false) {
		if(empty($this->fields)){
			$moduleBlockFields = Ottocrat_Field_Model::getAllForModule($this);
            $this->fields = array();
            foreach($moduleBlockFields as $moduleFields){
                foreach($moduleFields as $moduleField){
                     $block = $moduleField->get('block');
                    if(empty($block)) {
                        continue;
                }
                    $this->fields[$moduleField->get('name')] = $moduleField;
            }
            }
		}
		return $this->fields;
	}


	/**
	 * Function gives fields based on the type
	 * @param <String> $type - field type
	 * @return <Array of Ottocrat_Field_Model> - list of field models
	 */
	public function getFieldsByType($type) {
        if(!is_array($type)) {
            $type = array($type);
        }
		$fields = $this->getFields();
		$fieldList = array();
		foreach($fields as $field) {
			$fieldType = $field->getFieldDataType();
			if(in_array($fieldType,$type)) {
				$fieldList[$field->getName()] = $field;
			}
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the type
	 * @return <Ottocrat_Field_Model> with field label as key
	 */
	public function getFieldsByLabel() {
		$fields = $this->getFields();
		$fieldList = array();
		foreach($fields as $field) {
			$fieldLabel = $field->get('label');
			$fieldList[$fieldLabel] = $field;
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the fieldid
	 * @return <Ottocrat_Field_Model> with field id as key
	 */
	public function getFieldsById() {
		$fields = $this->getFields();
		$fieldList = array();
		foreach($fields as $field) {
			$fieldId = $field->getId();
			$fieldList[$fieldId] = $field;
		}
		return $fieldList;
	}

	/**
	 * Function returns all the relation models
	 * @return <Array of Ottocrat_Relation_Model>
	 */
	public function getRelations() {
		if(empty($this->relations)) {
			return Ottocrat_Relation_Model::getAllRelations($this);
		}
		return $this->relations;
	}

	/**
	 * Function that returns all the quickcreate fields for the module
	 * @return <Array of Ottocrat_Field_Model> - list of field models
	 */
    public function getQuickCreateFields() {
        $fieldList = $this->getFields();
        $quickCreateFieldList = array();
        foreach($fieldList as $fieldName => $fieldModel) {
            if($fieldModel->isQuickCreateEnabled() && $fieldModel->isEditable()) {
                $quickCreateFieldList[$fieldName] = $fieldModel;
            }
        }
        return $quickCreateFieldList;
    }

	/**
	 * Function to get the field mode
	 * @param <String> $fieldName - field name
	 * @return <Ottocrat_Field_Model>
	 */
	public function getField($fieldName){
		return Ottocrat_Field_Model::getInstance($fieldName,$this);
	}

	/**
	 * Function to get the field by column name.
	 * @param <String> $columnName - column name
	 * @return <Ottocrat_Field_Model>
	 */
	public function getFieldByColumn($columnName) {
		$fields = $this->getFields();
		if ($fields) {
			foreach ($fields as $field) {
				if ($field->get('column') == $columnName) {
					return $field;
				}
			}
		}
		return NULL;
	}

	/**
	 * Function to retrieve name fields of a module
	 * @return <array> - array which contains fields which together construct name fields
	 */
	public function getNameFields(){

        $nameFieldObject = Ottocrat_Cache::get('EntityField',$this->getName());
        $moduleName = $this->getName();
		if($nameFieldObject && $nameFieldObject->fieldname) {
			$this->nameFields = explode(',', $nameFieldObject->fieldname);
		} else {
			$adb = PearDatabase::getInstance();

			$query = "SELECT fieldname, tablename, entityidfield FROM ottocrat_entityname WHERE tabid = ?";
			$result = $adb->pquery($query, array($this->getId()));
			$this->nameFields = array();
			if($result){
				$rowCount = $adb->num_rows($result);
				if($rowCount > 0){
					$fieldNames = $adb->query_result($result,0,'fieldname');
					$this->nameFields = explode(',', $fieldNames);
				}
			}

            //added to handle entity names for these two modules
            //@Note: need to move these to database
            switch($moduleName) {
                case 'HelpDesk': $this->nameFields = array('ticket_title'); $fieldNames = 'ticket_title'; break;
                case 'Documents': $this->nameFields = array('notes_title'); $fieldNames = 'notes_title';  break;
            }
			$entiyObj = new stdClass();
			$entiyObj->basetable = $adb->query_result($result, 0, 'tablename');
			$entiyObj->basetableid =  $adb->query_result($result, 0, 'entityidfield');
			$entiyObj->fieldname =  $fieldNames;
			Ottocrat_Cache::set('EntityField',$this->getName(), $entiyObj);
		}
        
        return $this->nameFields;
	}

	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Ottocrat_Record_Model or Module Specific Record Model instances
	 */
	public function getRecentRecords($limit=10) {
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
        $deletedCondition = $this->getDeletedRecordCondition();
		$nonAdminQuery .= Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());
		$query = 'SELECT * FROM ottocrat_crmentity '.$nonAdminQuery.' WHERE setype=? AND '.$deletedCondition.' AND modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = array($this->getName(), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$recentRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

    /**
     * Function that returns deleted records condition
	 * @return <String>
     */
    public function getDeletedRecordCondition() {
       return 'ottocrat_crmentity.deleted = 0';
    }

	/**
	 * Funtion that returns fields that will be showed in the record selection popup
	 * @return <Array of fields>
	 */
    public function getPopupFields() {
        $entityInstance = CRMEntity::getInstance($this->getName());
        return $entityInstance->search_fields_name;
    }

    /**
     * Function that returns related list header fields that will be showed in the Related List View
     * @return <Array> returns related fields list.
     */
	public function getRelatedListFields()
    {
        $entityInstance = CRMEntity::getInstance($this->getName());
        $list_fields_name = $entityInstance->list_fields_name;
        $list_fields = $entityInstance->list_fields;
        $relatedListFields = array();
        foreach ($list_fields as $key => $fieldInfo) {
            foreach ($fieldInfo as $columnName) {
                if (array_key_exists($key, $list_fields_name)) {
                    $relatedListFields[$columnName] = $list_fields_name[$key];
                }
            }

        }
        return $relatedListFields;
    }

	public function getConfigureRelatedListFields(){
		$showRelatedFieldModel = $this->getSummaryViewFieldsList();
		$relatedListFields = array();
		if(count($showRelatedFieldModel) > 0) {
			foreach ($showRelatedFieldModel as $key => $field) {
				$relatedListFields[$field->get('column')] = $field->get('name');
			}
		}
        return $relatedListFields;
	}

	public function isWorkflowSupported() {
		if($this->isEntityModule()) {
			return true;
		}
		return false;
	}

	/**
	 * Function checks if a module has module sequence numbering
	 * @return boolean
	 */
	public function hasSequenceNumberField() {
        if(!empty($this->fields)) {
            $fieldList = $this->getFields();
            foreach($fieldList as $fieldName => $fieldModel) {
                if($fieldModel->get('uitype') === '4') {
                    return true;
                }
            }
        }else{
            $db = PearDatabase::getInstance();
            $query = 'SELECT 1 FROM ottocrat_field WHERE uitype=4 and tabid=?';
            $params = array($this->getId());
            $result = $db->pquery($query, $params);
            return $db->num_rows($result) > 0 ? true : false;
        }
        return false;
	}

	/**
	 * Static Function to get the instance of Ottocrat Module Model for the given id or name
	 * @param mixed id or name of the module
	 */
	public static function getInstance($value) {
        $instance = Ottocrat_Cache::get('module',$value);
        if(!$instance){
            $instance = false;
            $moduleObject = parent::getInstance($value);
            if($moduleObject) {
                $instance = self::getInstanceFromModuleObject($moduleObject);
                Ottocrat_Cache::set('module',$value,$instance);
				if (is_string($value)) {
					Ottocrat_Cache::set('module', $moduleObject->name, $instance);
				} else if (is_int($value)) {
					Ottocrat_Cache::set('module', $moduleObject->id, $instance);
				}
            }
        }
		return $instance;
	}


	/**
	 * Function to get the instance of Ottocrat Module Model from a given Ottocrat_Module object
	 * @param Ottocrat_Module $moduleObj
	 * @return Ottocrat_Module_Model instance
	 */
	public static function getInstanceFromModuleObject(Ottocrat_Module $moduleObj){
		$objectProperties = get_object_vars($moduleObj);
		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Module', $objectProperties['name']);
		$moduleModel = new $modelClassName();
		foreach($objectProperties as $properName=>$propertyValue){
			$moduleModel->$properName = $propertyValue;
		}
		return $moduleModel;
	}

	/**
	 * Function to get the instance of Ottocrat Module Model from a given list of key-value mapping
	 * @param <Array> $valueArray
	 * @return Ottocrat_Module_Model instance
	 */
	public static function getInstanceFromArray($valueArray) {
		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Module', $valueArray['name']);
		$instance = new $modelClassName();
        $instance->initialize($valueArray);
		return $instance;
	}

	/**
	 * Function to get all modules from CRM
	 * @param <array> $presence
	 * @param <array> $restrictedModulesList
	 * @return <array> List of module models <Ottocrat_Module_Model>
	 */
	public static function getAll($presence = array(), $restrictedModulesList = array()) {
		$db = PearDatabase::getInstance();
		self::preModuleInitialize2();
        $moduleModels = Ottocrat_Cache::get('ottocrat', 'modules');


        if(!$moduleModels){
            $moduleModels = array();

            $query = 'SELECT * FROM ottocrat_tab';
            $params = array();
            if($presence) {
                $query .= ' WHERE presence IN ('. generateQuestionMarks($presence) .')';
                array_push($params, $presence);
            }

            $result = $db->pquery($query, $params);
            $noOfModules = $db->num_rows($result);
            for($i=0; $i<$noOfModules; ++$i) {
                $row = $db->query_result_rowdata($result, $i);
                $moduleModels[$row['tabid']] = self::getInstanceFromArray($row);
                Ottocrat_Cache::set('module',$row['tabid'], $moduleModels[$row['tabid']]);
                Ottocrat_Cache::set('module',$row['name'], $moduleModels[$row['tabid']]);
            }
            if(!$presence){
                Ottocrat_Cache::set('ottocrat', 'modules',$moduleModels);
            }
        }

        if($presence && $moduleModels){
            foreach ($moduleModels as $key => $moduleModel){
                if(!in_array($moduleModel->get('presence'), $presence)){
                    unset($moduleModels[$key]);
                }
            }
        }

        if($restrictedModulesList && $moduleModels) {
            foreach ($moduleModels as $key => $moduleModel){
                if(in_array($moduleModel->getName(), $restrictedModulesList)){
                    unset($moduleModels[$key]);
                }
            }
        }

		return $moduleModels;
	}

	public static function getEntityModules() {
		self::preModuleInitialize2();
		$moduleModels = Ottocrat_Cache::get('ottocrat','EntityModules');
        if(!$moduleModels){
            $presence = array(0, 2);
            $moduleModels = self::getAll($presence);
            $restrictedModules = array('Webmails', 'Emails', 'Integration', 'Dashboard');
            foreach($moduleModels as $key => $moduleModel){
                if(in_array($moduleModel->getName(),$restrictedModules) || $moduleModel->get('isentitytype') != 1){
                    unset($moduleModels[$key]);
                }
            }
            Ottocrat_Cache::set('ottocrat','EntityModules',$moduleModels);
        }
		return $moduleModels;
	}

	/**
	 * Function to get the list of all accessible modules for Quick Create
	 * @return <Array> - List of Ottocrat_Record_Model or Module Specific Record Model instances
	 */
	public static function getQuickCreateModules() {
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$db = PearDatabase::getInstance();
		self::preModuleInitialize2();

		$sql = 'SELECT DISTINCT ottocrat_tab.*
					FROM ottocrat_field
					INNER JOIN ottocrat_tab ON ottocrat_tab.tabid = ottocrat_field.tabid
					WHERE quickcreate=0 AND ottocrat_tab.presence != 1';
		$params = array();
		$result = $db->pquery($sql, $params);
		$noOfModules = $db->num_rows($result);

		$quickCreateModules = array();
		for($i=0; $i<$noOfModules; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if($userPrivModel->hasModuleActionPermission($row['name'], 'EditView')) {
				$moduleModel = self::getInstanceFromArray($row);
				$quickCreateModules[$row['name']] = $moduleModel;
			}
		}
		return $quickCreateModules;
	}

	/**
	 * Function to get the list of all searchable modules
	 * @return <Array> - List of Ottocrat_Module_Model instances
	 */
	public static function getSearchableModules() {
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$entityModules = self::getEntityModules();

		$searchableModules = array();
		foreach ($entityModules as $tabid => $moduleModel) {
				$moduleName = $moduleModel->getName();
				if ($moduleName == 'Users' || $moduleName == 'Emails' || $moduleName == 'Events' ) continue;
				if($userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'DetailView')) {
						$searchableModules[$moduleName] = $moduleModel;
				}
		}
		return $searchableModules;
	}

	protected static function preModuleInitialize2() {
		if(!Ottocrat_Cache::get('EntityField','all')){
            $db = PearDatabase::getInstance();
            // Initialize meta information - to speed up instance creation (Ottocrat_ModuleBasic::initialize2)
            $result = $db->pquery('SELECT modulename,tablename,entityidfield,fieldname FROM ottocrat_entityname', array());

            for($index = 0, $len = $db->num_rows($result); $index < $len; ++$index) {
                
                $fieldNames = $db->query_result($result, $index, 'fieldname');
                $modulename = $db->query_result($result, $index, 'modulename');
                //added to handle entity names for these two modules
                //@Note: need to move these to database
                switch($modulename) {
                    case 'HelpDesk': $fieldNames = 'ticket_title'; break;
                    case 'Documents': $fieldNames = 'notes_title';  break;
                }
                $entiyObj = new stdClass();
                $entiyObj->basetable = $db->query_result($result, $index, 'tablename');
                $entiyObj->basetableid =  $db->query_result($result, $index, 'entityidfield');
                $entiyObj->fieldname =  $fieldNames;
                
                Ottocrat_Cache::set('EntityField',$modulename,$entiyObj);
                Ottocrat_Cache::set('EntityField','all',true);
            }
        }
	}

    public static function getPicklistSupportedModules() {
        vimport('~~/modules/PickList/PickListUtils.php');
	    $modules = getPickListModules();
        $modulesModelsList = array();
        foreach($modules as $moduleLabel => $moduleName) {
            $instance = new self();
            $instance->name = $moduleName;
            $instance->label = $moduleLabel;
            $modulesModelsList[] = $instance;
        }
        return $modulesModelsList;
    }

	public static function getCleanInstance($moduleName){
		$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Module', $moduleName);
		$instance = new $modelClassName();
		return $instance;
	}

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Ottocrat_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = Ottocrat_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Ottocrat_Link_Model::getInstanceFromValues($quickLink);
		}

		$quickWidgets = array(
			array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_RECENTLY_MODIFIED',
				'linkurl' => Ottocrat_Request::encryptLink('module='.$this->get('name').'&view=IndexAjax&mode=showActiveRecords'),
				'linkicon' => ''
			),
		);
		foreach($quickWidgets as $quickWidget) {
			$links['SIDEBARWIDGET'][] = Ottocrat_Link_Model::getInstanceFromValues($quickWidget);
		}

		return $links;
	}

	/**
	 * Function returns export query - deprecated
	 * @param <String> $where
	 * @return <String> export query
	 */
	public function getExportQuery($focus, $where) {
		$focus = CRMEntity::getInstance($this->getName());
		$query = $focus->create_export_query($where);
		return $query;
	}

	/**
	 * Function returns the default custom filter for the module
	 * @return <Int> custom filter id
	 */
	public function getDefaultCustomFilter() {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT cvid FROM ottocrat_customview WHERE setdefault = 1 AND entitytype = ?",
					array($this->getName()));
		if ($db->num_rows($result)) {
			return $db->query_result($result, 0, 'cvid');
		}
		return false;
	}

	/**
	 * Function returns latest comments for the module
	 * @param <Ottocrat_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getComments($pagingModel) {
		$comments = array();
		if(!$this->isCommentEnabled()) {
			return $comments;
		}
		//TODO: need to handle security and performance
		$db = PearDatabase::getInstance();

		$nonAdminAccessQuery = Users_Privileges_Model::getNonAdminAccessControlQuery('ModComments');

		$result = $db->pquery('SELECT ottocrat_crmentity.*, ottocrat_modcomments.* FROM ottocrat_modcomments
						INNER JOIN ottocrat_crmentity ON ottocrat_modcomments.modcommentsid = ottocrat_crmentity.crmid
							AND ottocrat_crmentity.deleted = 0
						INNER JOIN ottocrat_crmentity crmentity2 ON ottocrat_modcomments.related_to = crmentity2.crmid
							AND crmentity2.deleted = 0 AND crmentity2.setype = ?
						 '.$nonAdminAccessQuery.'
						ORDER BY ottocrat_crmentity.createdtime DESC LIMIT ?, ?',
						array($this->getName(), $pagingModel->getStartIndex(), $pagingModel->getPageLimit()));

		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$commentModel = Ottocrat_Record_Model::getCleanInstance('ModComments');
			$commentModel->setData($row);
			$time = $commentModel->get('createdtime');
			$comments[$time] = $commentModel;
		}

		return $comments;
	}

	/**
	 * Function returns comments and recent activities across module
	 * @param <Ottocrat_Paging_Model> $pagingModel
	 * @param <String> $type - comments, updates or all
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type=false) {
		if(empty($type)) {
			$type = 'all';
		}
		//TODO: need to handle security
		$comments = array();
		if($type == 'all' || $type == 'comments') {
			$modCommentsModel = Ottocrat_Module_Model::getInstance('ModComments');
			if($modCommentsModel->isPermitted('DetailView')){
				$comments = $this->getComments($pagingModel);
			}
			if($type == 'comments') {
				return $comments;
			}
		}

		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT ottocrat_modtracker_basic.*
								FROM ottocrat_modtracker_basic
								INNER JOIN ottocrat_crmentity ON ottocrat_modtracker_basic.crmid = ottocrat_crmentity.crmid
									AND deleted = 0 AND module = ?
								ORDER BY ottocrat_modtracker_basic.id DESC LIMIT ?, ?',
								array($this->getName(), $pagingModel->getStartIndex(), $pagingModel->getPageLimit()));

		$activites = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			if(Users_Privileges_Model::isPermitted($row['module'], 'DetailView', $row['crmid'])){
				$modTrackerRecorModel = new ModTracker_Record_Model();
				$modTrackerRecorModel->setData($row)->setParent($row['crmid'], $row['module']);
				$time = $modTrackerRecorModel->get('changedon');
				$activites[$time] = $modTrackerRecorModel;
			}
		}

		$history = array_merge($activites, $comments);

		$dateTime = array();
		foreach($history as $time=>$model) {
				$dateTime[] = $time;
		}

		if(!empty($history)) {
			array_multisort($dateTime,SORT_DESC,SORT_STRING,$history);
			return $history;
		}
		return false;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <String> $mode - upcoming/overdue mode
	 * @param <Ottocrat_Paging_Model> $pagingModel - $pagingModel
	 * @param <String> $user - all/userid
	 * @param <String> $recordId - record id
	 * @return <Array>
	 */
	function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Ottocrat_Datetime_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Ottocrat_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$query = "SELECT ottocrat_crmentity.crmid, crmentity2.crmid AS parent_id, ottocrat_crmentity.smownerid, ottocrat_crmentity.setype, ottocrat_activity.* FROM ottocrat_activity
					INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_activity.activityid
					INNER JOIN ottocrat_seactivityrel ON ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
					INNER JOIN ottocrat_crmentity AS crmentity2 ON ottocrat_seactivityrel.crmid = crmentity2.crmid AND crmentity2.deleted = 0 AND crmentity2.setype = ?
					LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

		$query .= " WHERE ottocrat_crmentity.deleted=0
					AND (ottocrat_activity.activitytype NOT IN ('Emails'))
					AND (ottocrat_activity.status is NULL OR ottocrat_activity.status NOT IN ('Completed', 'Deferred'))
					AND (ottocrat_activity.eventstatus is NULL OR ottocrat_activity.eventstatus NOT IN ('Held'))";

		if ($recordId) {
			$query .= " AND ottocrat_seactivityrel.crmid = ?";
		} elseif ($mode === 'upcoming') {
			$query .= " AND due_date >= '$currentDate'";
		} elseif ($mode === 'overdue') {
			$query .= " AND due_date < '$currentDate'";
		}

		$params = array($this->getName());
		if($user != 'all' && $user != '') {
			if($user === $currentUser->id) {
				$query .= " AND ottocrat_crmentity.smownerid = ?";
				array_push($params, $user);
			}
		}

		$query .= " ORDER BY date_start, time_start LIMIT ". $pagingModel->getStartIndex() .", ". ($pagingModel->getPageLimit()+1);


		if ($recordId) {
			array_push($params, $recordId);
		}

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		$groupsIds = Ottocrat_Util_Helper::getGroupsIdsForUsers($currentUser->getId());
		$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$newRow = $db->query_result_rowdata($result, $i);
			$model = Ottocrat_Record_Model::getCleanInstance('Calendar');
			$ownerId = $newRow['smownerid'];
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$visibleFields = array('activitytype','date_start','time_start','due_date','time_end','assigned_user_id','visibility','smownerid','crmid');
			$visibility = true;
			if(in_array($ownerId, $groupsIds)) {
				$visibility = false;
			} else if($ownerId == $currentUser->getId()){
				$visibility = false;
			}
			if(!$currentUser->isAdminUser() && $newRow['activitytype'] != 'Task' && $newRow['visibility'] == 'Private' && $ownerId && $visibility) {
				foreach($newRow as $data => $value) {
					if(in_array($data, $visibleFields) != -1) {
						unset($newRow[$data]);
					}
				}
				$newRow['subject'] = vtranslate('Busy','Events').'*';
			}
			if($newRow['activitytype'] == 'Task') {
				unset($newRow['visibility']);
			}

			$model->setData($newRow);
			$model->setId($newRow['crmid']);
			$activities[] = $model;
		}

		$pagingModel->calculatePageRange($activities);
		if($numOfRows > $pagingModel->getPageLimit()){
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $activities;
	}

	/**
	 * Function to get list of fields which are required while importing records
	 * @param <String> $module
	 * @return <Array> list of fields
	 */
	function getRequiredFields($module = '') {
		$moduleInstance = CRMEntity::getInstance($this->getName());
		$requiredFields = $moduleInstance->required_fields;
		if (empty ($requiredFields)) {
			if (empty ($module)) {
				$module = $this->getName();
			}
			$moduleInstance->initRequiredFields($module);
		}
		return $moduleInstance->required_fields;
	}

	/**
	 * Function to get the module is permitted to specific action
	 * @param <String> $actionName
	 * @return <boolean>
	 */
	public function isPermitted($actionName) {
		return ($this->isActive() && Users_Privileges_Model::isPermitted($this->getName(), $actionName));
	}

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule) {
		if($relatedModule == 'Documents'){
			return ' AND ottocrat_notes.filestatus = 1 ';
		}
		return;
	}

	/**
	 * Function to get where condition query for dashboards
	 * @param <Integer> $owner
	 * @return <String> query
	 */
	public function getOwnerWhereConditionForDashBoards ($owner) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$sharingAccessModel = Settings_SharingAccess_Module_Model::getInstance($this->getName());
		$params = array();
		if(!empty($owner) && $currentUserModel->isAdminUser()) {//If admin user, then allow users data
			$ownerSql =  ' smownerid = '. $owner;
			$params[] = $owner;
		} else if(!empty($owner)){//If not admin user, then check sharing access for that module
			if($sharingAccessModel->isPrivate()) {
				$subordinateUserModels = $currentUserModel->getSubordinateUsers();
				$subordinateUsers = array();
				foreach($subordinateUserModels as $id=>$name) {
					$subordinateUsers[] = $id;
				}
				if(in_array($owner, $subordinateUsers)) {
					$ownerSql = ' smownerid = '. $owner ;
				} else {
					$ownerSql = ' smownerid = '. $currentUserModel->getId();
				}
			} else {
				$ownerSql = ' smownerid = '. $owner ;
			}
		} else {//If no owner filter, then check if the module access is Private
			if($sharingAccessModel->isPrivate() && (!$currentUserModel->isAdminUser())) {
				$subordinateUserModels = $currentUserModel->getSubordinateUsers();
				foreach($subordinateUserModels as $id=>$name) {
					$subordinateUsers[] = $id;
					$params[] = $id;
				}
				if($subordinateUsers) {
					$ownerSql =  ' smownerid IN ('. implode(',' , $subordinateUsers) .')';
				} else {
					$ownerSql =  ' smownerid = '.$currentUserModel->getId();
				}
			}
		}
		return $ownerSql;
	}

	/**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks(){
        if(!$this->isEntityModule()) {
            return array();
        }
		vimport('~~modules/com_ottocrat_workflow/VTWorkflowUtils.php');

		$layoutEditorImagePath = Ottocrat_Theme::getImagePath('LayoutEditor.gif');
		$editWorkflowsImagePath = Ottocrat_Theme::getImagePath('EditWorkflows.png');
		$settingsLinks = array();

		$settingsLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_FIELDS',
                    'linkurl' => Ottocrat_Request:: encryptLink('index.php?parent=Settings&module=LayoutEditor&sourceModule='.$this->getName()),
					'linkicon' => $layoutEditorImagePath
		);

		if(VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_WORKFLOWS',
                    'linkurl' => Ottocrat_Request:: encryptLink('index.php?parent=Settings&module=Workflows&view=List&sourceModule='.$this->getName()),
					'linkicon' => $editWorkflowsImagePath
			);
		}

		$settingsLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
					'linkurl' => Ottocrat_Request:: encryptLink('index.php?parent=Settings&module=Picklist&view=Index&source_module='.$this->getName()),
					'linkicon' => ''
		);

		if($this->hasSequenceNumberField()) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_MODULE_SEQUENCE_NUMBERING',
				'linkurl' => Ottocrat_Request:: encryptLink('index.php?parent=Settings&module=Ottocrat&view=CustomRecordNumbering&sourceModule='.$this->getName()),
				'linkicon' => ''
			);
		}

        $webformSupportedModule = Settings_Webforms_Module_Model :: getSupportedModulesList();
        if(array_key_exists($this->getName(), $webformSupportedModule)){
            $settingsLinks[] =	array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_SETUP_WEBFORMS',
					'linkurl' => Ottocrat_Request:: encryptLink('index.php?module=Webforms&parent=Settings&view=Edit&sourceModule='.$this->getName()),
					'linkicon' => '');
        }
		return $settingsLinks;
	}

    public function isCustomizable() {
        return $this->customized == '1' ? true : false;
    }

    public function isModuleUpgradable() {
        return $this->isCustomizable() ? true : false;
    }

    public function isExportable() {
        return $this->isCustomizable() ? true : false;
    }

	/**
	 * Function to get list of field for summary view
	 * @return <Array> list of field models <Ottocrat_Field_Model>
	 */
	public function getSummaryViewFieldsList() {
		if (!$this->summaryFields) {
			$summaryFields = array();
			$fields = $this->getFields();
			foreach ($fields as $fieldName => $fieldModel) {
				if ($fieldModel->isSummaryField() && $fieldModel->isActiveField()) {
					$summaryFields[$fieldName] = $fieldModel;
				}
			}
			$this->summaryFields = $summaryFields;
		}
		return $this->summaryFields;
	}


	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	public function getSearchRecordsQuery($searchValue, $parentId=false, $parentModule=false) {
		return "SELECT * FROM ottocrat_crmentity WHERE label LIKE '%$searchValue%' AND ottocrat_crmentity.deleted = 0";
	}

	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param <String> $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param <String> $parentModule - parent module name
	 * @return <Array of Ottocrat_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId=false, $parentModule=false, $relatedModule=false) {
		if(!empty($searchValue) && empty($parentId) && empty($parentModule)) {
			$matchingRecords = Ottocrat_Record_Model::getSearchResult($searchValue, $this->getName());
		} else if($parentId && $parentModule) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery($this->getSearchRecordsQuery($searchValue, $parentId, $parentModule), array());
			$noOfRows = $db->num_rows($result);

			$moduleModels = array();
			$matchingRecords = array();
			for($i=0; $i<$noOfRows; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])){
					$row['id'] = $row['crmid'];
					$moduleName = $row['setype'];
					if(!array_key_exists($moduleName, $moduleModels)) {
						$moduleModels[$moduleName] = Ottocrat_Module_Model::getInstance($moduleName);
					}
					$moduleModel = $moduleModels[$moduleName];
					$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Record', $moduleName);
					$recordInstance = new $modelClassName();
					$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
				}
			}
		}

		return $matchingRecords;
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Ottocrat_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		$relatedModuleName = $relatedModule->getName();

		$focus = CRMEntity::getInstance($this->getName());
		$focus->id = $recordId;

		$result = $focus->$functionName($recordId, $this->getId(), $relatedModule->getId());
		$query = $result['query'] .' '. $this->getSpecificRelationQuery($relatedModuleName);
		$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);

		//modify query if any module has summary fields, those fields we are displayed in related list of that module
		$relatedListFields = $relatedModule->getConfigureRelatedListFields();
		
        if($relatedModuleName == 'Documents') {
                    $relatedListFields['filelocationtype'] = 'filelocationtype';
                    $relatedListFields['filestatus'] = 'filestatus';
                }

        if(count($relatedListFields) > 0) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$queryGenerator = new QueryGenerator($relatedModuleName, $currentUser);
			$queryGenerator->setFields($relatedListFields);
			$selectColumnSql = $queryGenerator->getSelectClauseColumnSQL();
			$newQuery = spliti('FROM', $query);
			$selectColumnSql = 'SELECT DISTINCT ottocrat_crmentity.crmid,'.$selectColumnSql;
			$query = $selectColumnSql.' FROM '.$newQuery[1];
		}

		if ($nonAdminQuery) {
			$query = appendFromClauseToQuery($query, $nonAdminQuery);
		}

		return $query;
	}

	/**
	 * Function to get Non admin access control query
	 * @param <String> $relatedModuleName
	 * @return <String>
	 */
	public function getNonAdminAccessControlQueryForRelation($relatedModuleName) {
		$modulesList = array('Faq', 'PriceBook', 'Vendors', 'Users');

		if (!in_array($relatedModuleName, $modulesList)) {
			return Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleName);
		}
	}

	/**
	 * Function returns the default column for Alphabetic search
	 * @return <String> columnname
	 */
	public function getAlphabetSearchField(){
		$focus = CRMEntity::getInstance($this->get('name'));
		return $focus->def_basicsearch_col;
	}

    /**
     * Function which will give complusory mandatory fields
     * @return type
     */
    public function getCumplosoryMandatoryFieldList() {
        $focus = CRMEntity::getInstance($this->getName());
        $compulsoryMandtoryFields = $focus->mandatory_fields;
        if(empty($compulsoryMandtoryFields)) {
            $compulsoryMandtoryFields = array();
        }
        return $compulsoryMandtoryFields;
    }


	/**
	 * Function returns all the related modules for workflows create entity task
	 * @return <JSON>
	 */
	public function vtJsonDependentModules() {
		vimport('~~/modules/com_ottocrat_workflow/WorkflowComponents.php');
		$db = PearDatabase::getInstance();
		$param = array('modulename'=>$this->getName());
		return vtJsonDependentModules($db, $param);
	}

	/**
	 * Function returns mandatory field Models
	 * @return <Array of Ottocrat_Field_Model>
	 */
	public function getMandatoryFieldModels(){
		$fields = $this->getFields();
		$mandatoryFields = array();
		if ($fields) {
			foreach ($fields as $field) {
				if ($field->isMandatory()) {
					$mandatoryFields[] = $field;
				}
			}
		}
		return $mandatoryFields;
	}

	public function getRelatedModuleRecordIds(Ottocrat_Request $request, $recordIds = array()) {
		$db = PearDatabase::getInstance();
		$relatedModules = $request->get('related_modules');
		$focus = CRMEntity::getInstance($this->getName());
		$relatedModuleMapping = $focus->related_module_table_index;
		$relatedIds = array();
		if(!empty($relatedModules)) {
			for ($i=0; $i<count($relatedModules); $i++) {
				$params = array();
				$module = $relatedModules[$i];
				$tablename = $relatedModuleMapping[$module]['table_name'];
				$tabIndex = $relatedModuleMapping[$module]['table_index'];
				$relIndex = $relatedModuleMapping[$module]['rel_index'];
				$sql = "SELECT ottocrat_crmentity.crmid FROM ottocrat_crmentity";
				if($tablename == 'ottocrat_crmentityrel'){
					$sql .= " INNER JOIN $tablename ON ($tablename.relcrmid = ottocrat_crmentity.crmid OR $tablename.crmid = ottocrat_crmentity.crmid)
						WHERE ($tablename.crmid IN (".  generateQuestionMarks($recordIds).")) OR ($tablename.relcrmid IN (".  generateQuestionMarks($recordIds)."))";
					foreach ($recordIds as $key => $recordId) {
						array_push($params, $recordId);
					}
				} else {
					$sql .= " INNER JOIN $tablename ON $tablename.$tabIndex = ottocrat_crmentity.crmid
						WHERE $tablename.$relIndex IN (".  generateQuestionMarks($recordIds).")";
				}
				foreach ($recordIds as $key => $recordId) {
					array_push($params, $recordId);
				}
				$result1 = $db->pquery($sql, $params);
				$num_rows = $db->num_rows($result1);
				for($j=0; $j<$num_rows; $j++){
					$relatedIds[] = $db->query_result($result1, $j, 'crmid');
				}
			}
			return $relatedIds;
		} else {
			return $relatedIds;
		}
	}

	public function transferRecordsOwnership($transferOwnerId, $relatedModuleRecordIds){
		$db = PearDatabase::getInstance();
		$query = 'UPDATE ottocrat_crmentity SET smownerid = ? WHERE crmid IN ('.  generateQuestionMarks($relatedModuleRecordIds).')';
		$db->pquery($query, array($transferOwnerId,$relatedModuleRecordIds));
	}

    /**
    * Function to get orderby sql from orderby field
    */
    public function getOrderBySql($orderBy){
             $orderByField = $this->getFieldByColumn($orderBy);
             return $orderByField->get('table') . '.' . $orderBy;
    }

     public function getDefaultSearchField(){
        $nameFields = $this->getNameFields();
        //To make the first field as the name field
        return $nameFields[0];
    }

	/**
	 * Function to get popup view fields
	 */
	public function getPopupViewFieldsList(){
		$summaryFieldsList = $this->getSummaryViewFieldsList();

		if(count($summaryFieldsList) > 0){
			 $popupFields = array_keys($summaryFieldsList);
		}else{
			$popupFields = array_values($this->getRelatedListFields());
		}
		return $popupFields;
	}

	/**
     * Funxtion to identify if the module supports quick search or not
     */
    public function isQuickSearchEnabled() {
        return true;
    }

    /**
     * function to check if the extension module is permitted for utility action
     * @return <boolean> false
     */
    public function isUtilityActionEnabled() {
        return false;
    }
    
    public function isListViewNameFieldNavigationEnabled() {
        return true;
    }
}
