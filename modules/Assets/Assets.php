<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
class Assets extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name = 'ottocrat_assets';
	var $table_index= 'assetsid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_assetscf', 'assetsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('ottocrat_crmentity','ottocrat_assets','ottocrat_assetscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'ottocrat_crmentity'=>'crmid',
		'ottocrat_assets'=>'assetsid',
		'ottocrat_assetscf'=>'assetsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
   		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'ottocrat_'
		'Asset No'=>Array('assets'=>'asset_no'),
        'Asset Name'=>Array('assets'=>'assetname'),
		'Customer Name'=>Array('account'=>'account'),
        'Product Name'=>Array('products'=>'product'),
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Asset No'=>'asset_no',
        'Asset Name'=>'assetname',
		'Customer Name'=>'account',
        'Product Name'=>'product',
	);

	// Make the field link to detail view
	var $list_link_field= 'assetname';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'ottocrat_'
		'Asset No'=>Array('assets'=>'asset_no'),
        'Asset Name'=>Array('assets'=>'assetname'),
		'Customer Name'=>Array('account'=>'account'),
		'Product Name'=>Array('products'=>'product')
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'Asset No'=>'asset_no',
        'Asset Name'=>'assetname',
		'Customer Name'=>'account',
		'Product Name'=>'product'
	);

	// For Popup window record selection
	var $popup_fields = Array ('assetname','account','product');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();

	// For Alphabetical search
	var $def_basicsearch_col = 'assetname';

	// Required Information for enabling Import feature
	var $required_fields = Array('assetname'=>1);

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to ottocrat_field.fieldname values.
	var $mandatory_fields = Array('assetname', 'product', 'assigned_user_id');

	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');

	var $default_order_by = 'assetname';
	var $default_sort_order='ASC';

	var $unit_price;

	/**	Constructor which will set the column_fields in this object
	 */
	function __construct() {
		global $log;
		$this->column_fields = getColumnFields(get_class($this));
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
	}

	function save_module($module){
		//module specific save
	}

	/**
	 * Return query to use based on given modulename, fieldname
	 * Useful to handle specific case handling for Popup
	 */
	function getQueryByModuleField($module, $fieldname, $srcrecord) {
		// $srcrecord could be empty
	}

	/**
	 * Get list view query.
	 */
	function getListQuery($module, $where='') {
		$query = "SELECT ottocrat_crmentity.*, $this->table_name.*";

		// Select Custom Field Table Columns if present
		if(!empty($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
		}
		$query .= " LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid";
		$query .= " LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";


		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM ottocrat_field" .
				" INNER JOIN ottocrat_fieldmodulerel ON ottocrat_fieldmodulerel.fieldid = ottocrat_field.fieldid" .
				" WHERE uitype='10' AND ottocrat_fieldmodulerel.module=?", array($module));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
		}

		$query .= "	WHERE ottocrat_crmentity.deleted = 0 ".$where;
		$query .= $this->getListViewSecurityParameter($module);
		return $query;
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	function getListViewSecurityParameter($module) {
		global $current_user;
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		$sec_query = '';
		$tabid = getTabid($module);

		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
			&& $defaultOrgSharingPermission[$tabid] == 3) {

				$sec_query .= " AND (ottocrat_crmentity.smownerid in($current_user->id) OR ottocrat_crmentity.smownerid IN
					(
						SELECT ottocrat_user2role.userid FROM ottocrat_user2role
						INNER JOIN ottocrat_users ON ottocrat_users.id=ottocrat_user2role.userid
						INNER JOIN ottocrat_role ON ottocrat_role.roleid=ottocrat_user2role.roleid
						WHERE ottocrat_role.parentrole LIKE '".$current_user_parent_role_seq."::%'
					)
					OR ottocrat_crmentity.smownerid IN
					(
						SELECT shareduserid FROM ottocrat_tmp_read_user_sharing_per
						WHERE userid=".$current_user->id." AND tabid=".$tabid."
					)
					OR
						(";

					// Build the query based on the group association of current user.
					if(sizeof($current_user_groups) > 0) {
						$sec_query .= " ottocrat_groups.groupid IN (". implode(",", $current_user_groups) .") OR ";
					}
					$sec_query .= " ottocrat_groups.groupid IN
						(
							SELECT ottocrat_tmp_read_group_sharing_per.sharedgroupid
							FROM ottocrat_tmp_read_group_sharing_per
							WHERE userid=".$current_user->id." and tabid=".$tabid."
						)";
				$sec_query .= ")
				)";
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	function create_export_query($where)
	{
		global $current_user;

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery('Assets', "detail_view");

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, ottocrat_users.user_name AS user_name
					FROM ottocrat_crmentity INNER JOIN $this->table_name ON ottocrat_crmentity.crmid=$this->table_name.$this->table_index";

		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
		}

		$query .= " LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";
		$query .= " LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid = ottocrat_users.id and ottocrat_users.status='Active'";

		$where_auto = " ottocrat_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		// Security Check for Field Access
		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[getTabid('Assets')] == 3)
		{
			//Added security check to get the permitted records only
			$query = $query." ".getListViewSecurityParameter($thismodule);
		}
		return $query;
	}

	/**
	 * Transform the value while exporting
	 */
	function transform_export_value($key, $value) {
		if($key == 'owner') return getOwnerName($value);
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will give the basic query to find duplicates
	 */
	function getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$select_cols='') {
		$select_clause = "SELECT ". $this->table_name .".".$this->table_index ." AS recordid, ottocrat_users_last_import.deleted,".$table_cols;

		// Select Custom Field Table Columns if present
		if(isset($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
		}
		$from_clause .= " LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
						LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";

		$where_clause = "	WHERE ottocrat_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);

		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN ottocrat_crmentity AS crm ON crm.crmid = t.".$this->table_index;
			// Consider custom table join as well.
			if(isset($this->customFieldTable)) {
				$sub_query .= " INNER JOIN ".$this->customFieldTable[0]." tcf ON tcf.".$this->customFieldTable[1]." = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}


		$query = $select_clause . $from_clause .
					" LEFT JOIN ottocrat_users_last_import ON ottocrat_users_last_import.bean_id=" . $this->table_name .".".$this->table_index .
					" INNER JOIN (" . $sub_query . ") AS temp ON ".get_on_clause($field_values,$ui_type_arr,$module) .
					$where_clause .
					" ORDER BY $table_cols,". $this->table_name .".".$this->table_index ." ASC";

		return $query;
	}
	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// function save_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }


	/*
	 * Function to get the primary query part of a report
	 * @param - $module primary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	// function generateReportsQuery($module){ }

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	// function generateReportsSecQuery($module,$secmodule){ }

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;
		parent::unlinkDependencies($module, $id);
	}

 	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
		require_once('include/utils/utils.php');
		global $adb;

 		if($eventType == 'module.postinstall') {
			//Add Assets Module to Customer Portal
			global $adb;

			$this->addModuleToCustomerPortal();

			include_once('vtlib/Ottocrat/Module.php');

			// Mark the module as Standard module
			$adb->pquery('UPDATE ottocrat_tab SET customized=0 WHERE name=?', array($moduleName));

			//adds sharing accsess
			$AssetsModule  = Ottocrat_Module::getInstance('Assets');
			Ottocrat_Access::setDefaultSharing($AssetsModule);

			//Showing Assets module in the related modules in the More Information Tab
			$assetInstance = Ottocrat_Module::getInstance('Assets');
			$assetLabel = 'Assets';

			$accountInstance = Ottocrat_Module::getInstance('Accounts');
			$accountInstance->setRelatedlist($assetInstance,$assetLabel,array(ADD),'get_dependents_list');

			$productInstance = Ottocrat_Module::getInstance('Products');
			$productInstance->setRelatedlist($assetInstance,$assetLabel,array(ADD),'get_dependents_list');

			$InvoiceInstance = Ottocrat_Module::getInstance('Invoice');
			$InvoiceInstance->setRelatedlist($assetInstance,$assetLabel,array(ADD),'get_dependents_list');

			$result = $adb->pquery("SELECT 1 FROM ottocrat_modentity_num WHERE semodule = ? AND active = 1", array($moduleName));
			if (!($adb->num_rows($result))) {
				//Initialize module sequence for the module
				$adb->pquery("INSERT INTO ottocrat_modentity_num values(?,?,?,?,?,?)", array($adb->getUniqueId("ottocrat_modentity_num"), $moduleName, 'ASSET', 1, 1, 1));
			}

		} else if($eventType == 'module.disabled') {
		// TODO Handle actions when this module is disabled.
		} else if($eventType == 'module.enabled') {
		// TODO Handle actions when this module is enabled.
		} else if($eventType == 'module.preuninstall') {
		// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
		// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			$this->addModuleToCustomerPortal();

			$result = $adb->pquery("SELECT 1 FROM ottocrat_modentity_num WHERE semodule = ? AND active =1 ", array($moduleName));
			if (!($adb->num_rows($result))) {
				//Initialize module sequence for the module
				$adb->pquery("INSERT INTO ottocrat_modentity_num values(?,?,?,?,?,?)", array($adb->getUniqueId("ottocrat_modentity_num"), $moduleName, 'ASSET', 1, 1, 1));
			}
		}
 	}

	function addModuleToCustomerPortal() {
		$adb = PearDatabase::getInstance();

		$assetsResult = $adb->pquery('SELECT tabid FROM ottocrat_tab WHERE name=?', array('Assets'));
		$assetsTabId = $adb->query_result($assetsResult, 0, 'tabid');
		if(getTabid('CustomerPortal') && $assetsTabId) {
			$checkAlreadyExists = $adb->pquery('SELECT 1 FROM ottocrat_customerportal_tabs WHERE tabid=?', array($assetsTabId));
			if($checkAlreadyExists && $adb->num_rows($checkAlreadyExists) < 1) {
				$maxSequenceQuery = $adb->query("SELECT max(sequence) as maxsequence FROM ottocrat_customerportal_tabs");
				$maxSequence = $adb->query_result($maxSequenceQuery, 0, 'maxsequence');
				$nextSequence = $maxSequence+1;
				$adb->query("INSERT INTO ottocrat_customerportal_tabs(tabid,visible,sequence) VALUES ($assetsTabId,1,$nextSequence)");
			}
			$checkAlreadyExists = $adb->pquery('SELECT 1 FROM ottocrat_customerportal_prefs WHERE tabid=?', array($assetsTabId));
			if($checkAlreadyExists && $adb->num_rows($checkAlreadyExists) < 1) {
				$adb->query("INSERT INTO ottocrat_customerportal_prefs(tabid,prefkey,prefvalue) VALUES ($assetsTabId,'showrelatedinfo',1)");
			}
		}
	}

    /**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId) {
		global $adb,$log;
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array("Documents"=>"ottocrat_senotesrel","Attachments"=>"ottocrat_seattachmentsrel");

		$tbl_field_arr = Array("ottocrat_senotesrel"=>"notesid","ottocrat_seattachmentsrel"=>"attachmentsid");

		$entity_tbl_field_arr = Array("ottocrat_senotesrel"=>"crmid","ottocrat_seattachmentsrel"=>"crmid");

		foreach($transferEntityIds as $transferId) {
			foreach($rel_table_arr as $rel_module=>$rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result =  $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
						" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)",
						array($transferId,$entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if($res_cnt > 0) {
					for($i=0;$i<$res_cnt;$i++) {
						$id_field_value = $adb->query_result($sel_result,$i,$id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?",
							array($entityId,$transferId,$id_field_value));
					}
				}
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		$log->debug("Exiting transferRelatedRecords...");
	}
}
?>
