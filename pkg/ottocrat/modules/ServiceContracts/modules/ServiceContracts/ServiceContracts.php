<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
class ServiceContracts extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name = 'ottocrat_servicecontracts';
	var $table_index= 'servicecontractsid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_servicecontractscf', 'servicecontractsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('ottocrat_crmentity', 'ottocrat_servicecontracts', 'ottocrat_servicecontractscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'ottocrat_crmentity' => 'crmid',
		'ottocrat_servicecontracts' => 'servicecontractsid',
		'ottocrat_servicecontractscf'=>'servicecontractsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'ottocrat_'
		'Subject' => Array('servicecontracts', 'subject'),
		'Assigned To' => Array('crmentity','smownerid'),
		'Contract No' => Array('servicecontracts','contract_no'),
		'Used Units' => Array('servicecontracts','used_units'),
		'Total Units' => Array('servicecontracts','total_units')
	);
	var $list_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
		'Contract No' =>  'contract_no',
		'Used Units' => 'used_units',
		'Total Units' => 'total_units'
	);

	// Make the field link to detail view
	var $list_link_field = 'subject';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'ottocrat_'
		'Subject' => Array('servicecontracts', 'subject'),
		'Contract No' => Array('servicecontracts', 'contract_no'),
		'Assigned To' => Array('ottocrat_crmentity','assigned_user_id'),
		'Used Units' => Array('servicecontracts','used_units'),
		'Total Units' => Array('servicecontracts','total_units')
	);
	var $search_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Subject' => 'subject',
		'Contract No' => 'contract_no',
		'Assigned To' => 'assigned_user_id',
		'Used Units' => 'used_units',
		'Total Units' => 'total_units'
	);

	// For Popup window record selection
	var $popup_fields = Array ('subject');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();

	// For Alphabetical search
	var $def_basicsearch_col = 'subject';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'subject';

	// Required Information for enabling Import feature
	var $required_fields = Array ('assigned_user_id'=>1);

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to ottocrat_field.fieldname values.
	var $mandatory_fields = Array('subject','assigned_user_id');

	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');

	var $default_order_by = 'subject';
	var $default_sort_order='ASC';

	function __construct() {
		global $log;
		$this->column_fields = getColumnFields(get_class($this));
		$this->db = new PearDatabase();
		$this->log = $log;
	}

	function save_module($module) {
		$return_action = $_REQUEST['return_action'];
		$for_module = $_REQUEST['return_module'];
		$for_crmid  = $_REQUEST['return_id'];
		if ($return_action && $for_module && $for_crmid) {
			if ($for_module == 'HelpDesk') {
				$on_focus = CRMEntity::getInstance($for_module);
				$on_focus->save_related_module($for_module, $for_crmid, $module, $this->id);
			}
		}
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

			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index =".
					"$this->table_name.$columnname";
		}

		global $current_user;
		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= "WHERE ottocrat_crmentity.deleted = 0 ".$where;
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

    /*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryplanner) {

		$matrix = $queryplanner->newDependencyMatrix();
        $matrix->setDependency('ottocrat_crmentityServiceContracts',array('ottocrat_groupsServiceContracts','ottocrat_usersServiceContracts'));
		$matrix->setDependency('ottocrat_servicecontracts',array('ottocrat_servicecontractscf','ottocrat_crmentityServiceContracts'));
        if (!$queryplanner->requireTable('ottocrat_servicecontracts', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"ottocrat_servicecontracts","servicecontractsid", $queryplanner);

        if ($queryplanner->requireTable("ottocrat_crmentityServiceContracts",$matrix)){
			$query .= " left join ottocrat_crmentity as ottocrat_crmentityServiceContracts on ottocrat_crmentityServiceContracts.crmid = ottocrat_servicecontracts.servicecontractsid  and ottocrat_crmentityServiceContracts.deleted=0";
		}
        if ($queryplanner->requireTable("ottocrat_servicecontractscf")){
		    $query .= " left join ottocrat_servicecontractscf on ottocrat_servicecontractscf.servicecontractsid = ottocrat_servicecontracts.servicecontractsid";
		}
        if ($queryplanner->requireTable("ottocrat_groupsServiceContracts")){
			$query .= " left join ottocrat_groups as ottocrat_groupsServiceContracts on ottocrat_groupsServiceContracts.groupid = ottocrat_crmentityServiceContracts.smownerid";
		}
		if ($queryplanner->requireTable("ottocrat_usersServiceContracts")){
			$query .= " left join ottocrat_users as ottocrat_usersServiceContracts on ottocrat_usersServiceContracts.id = ottocrat_crmentityServiceContracts.smownerid";
		}
        if($queryplanner->requireTable("ottocrat_contactdetailsRelServiceContracts")){
            $query .= " left join ottocrat_contactdetails as ottocrat_contactdetailsRelServiceContracts on ottocrat_contactdetailsRelServiceContracts.contactid = ottocrat_servicecontracts.sc_related_to";
        }
        if($queryplanner->requireTable("ottocrat_accountRelServiceContracts")){
            $query .= " left join ottocrat_account as ottocrat_accountRelServiceContracts on ottocrat_accountRelServiceContracts.accountid = ottocrat_servicecontracts.sc_related_to";
        }
         if ($queryplanner->requireTable("ottocrat_lastModifiedByServiceContracts")){
			$query .= " left join ottocrat_users as ottocrat_lastModifiedByServiceContracts on ottocrat_lastModifiedByServiceContracts.id = ottocrat_crmentityServiceContracts.modifiedby ";
		}
        if ($queryplanner->requireTable("ottocrat_createdbyServiceContracts")){
			$query .= " left join ottocrat_users as ottocrat_createdbyServiceContracts on ottocrat_createdbyServiceContracts.id = ottocrat_crmentityServiceContracts.smcreatorid ";
		}
        return $query;
	}
	/**
	 * Create query to export the records.
	 */
	function create_export_query($where)
	{
		global $current_user,$currentModule;

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery('ServiceContracts', "detail_view");

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, ottocrat_users.user_name AS user_name
					FROM ottocrat_crmentity INNER JOIN $this->table_name ON ottocrat_crmentity.crmid=$this->table_name.$this->table_index";

		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
		}

		$query .= " LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";
		$query .= " LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid = ottocrat_users.id and ".
		"ottocrat_users.status='Active'";

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM ottocrat_field" .
				" INNER JOIN ottocrat_fieldmodulerel ON ottocrat_fieldmodulerel.fieldid = ottocrat_field.fieldid" .
				" WHERE uitype='10' AND ottocrat_fieldmodulerel.module=?", array($thismodule));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = ".
					"$this->table_name.$columnname";
		}

		$query .= $this->getNonAdminAccessControlQuery($thismodule,$current_user);
		$where_auto = " ottocrat_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		return $query;
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
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {

		require_once('include/utils/utils.php');
		global $adb;

 		if($eventType == 'module.postinstall') {
			require_once('vtlib/Ottocrat/Module.php');

			$moduleInstance = Ottocrat_Module::getInstance($moduleName);

			$accModuleInstance = Ottocrat_Module::getInstance('Accounts');
			$accModuleInstance->setRelatedList($moduleInstance,'Service Contracts',array('add'),'get_dependents_list');

			$conModuleInstance = Ottocrat_Module::getInstance('Contacts');
			$conModuleInstance->setRelatedList($moduleInstance,'Service Contracts',array('add'),'get_dependents_list');

			$helpDeskInstance = Ottocrat_Module::getInstance("HelpDesk");
			$helpDeskInstance->setRelatedList($moduleInstance,"Service Contracts",Array('ADD','SELECT'));

			// Initialize module sequence for the module
			$adb->pquery("INSERT into ottocrat_modentity_num values(?,?,?,?,?,?)",array($adb->getUniqueId("ottocrat_modentity_num"),$moduleName,'SERCON',1,1,1));

			// Make the picklist value 'Complete' for status as non-editable
			$adb->query("UPDATE ottocrat_contract_status SET presence=0 WHERE contract_status='Complete'");

			// Mark the module as Standard module
			$adb->pquery('UPDATE ottocrat_tab SET customized=0 WHERE name=?', array($moduleName));

		} else if($eventType == 'module.disabled') {
			$em = new VTEventsManager($adb);
			$em->setHandlerInActive('ServiceContractsHandler');

		} else if($eventType == 'module.enabled') {
			$em = new VTEventsManager($adb);
			$em->setHandlerActive('ServiceContractsHandler');

		} else if($eventType == 'module.preuninstall') {
		// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
		// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
		// TODO Handle actions after this module is updated.
		}
 	}

	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	function save_related_module($module, $crmid, $with_module, $with_crmids) {

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			if ($with_module == 'HelpDesk') {
				$this->updateHelpDeskRelatedTo($crmid,$with_crmid);
				$this->updateServiceContractState($crmid);
			}
		}
	 }

	 // Function to Update the parent_id of HelpDesk with sc_related_to of ServiceContracts if the parent_id is not set.
	 function updateHelpDeskRelatedTo($focusId, $entityIds) {

		if(!is_array($entityIds)) $entityIds = array($entityIds);
		$selectTicketsQuery = "SELECT ticketid FROM ottocrat_troubletickets
								WHERE (parent_id IS NULL OR parent_id = 0 OR contact_id IS NULL OR contact_id =0)
									AND ticketid IN (" . generateQuestionMarks($entityIds) .")";$selectTicketsResult = $this->db->pquery($selectTicketsQuery, array($entityIds));
		$noOfTickets = $this->db->num_rows($selectTicketsResult);
		for($i=0; $i < $noOfTickets; ++$i) {
			$ticketId = $this->db->query_result($selectTicketsResult,$i,'ticketid');
			$serviceContractsRelateToTypeResult = $this->db->pquery('SELECT setype FROM ottocrat_crmentity WHERE crmid =
				(SELECT sc_related_to FROM ottocrat_servicecontracts WHERE servicecontractsid = ?)', array($focusId));
			$serviceContractsRelateToType = $this->db->query_result($serviceContractsRelateToTypeResult, 0, 'setype');
			if($serviceContractsRelateToType == 'Accounts') {
				$updateQuery = "UPDATE ottocrat_troubletickets, ottocrat_servicecontracts SET parent_id=ottocrat_servicecontracts.sc_related_to" .
					" WHERE ottocrat_servicecontracts.sc_related_to IS NOT NULL AND ottocrat_servicecontracts.sc_related_to != 0" .
					" AND ottocrat_servicecontracts.servicecontractsid = ? AND ottocrat_troubletickets.ticketid = ?";
				$this->db->pquery($updateQuery, array($focusId, $ticketId));
			} elseif($serviceContractsRelateToType == 'Contacts') {
				$updateQuery = "UPDATE ottocrat_troubletickets, ottocrat_servicecontracts SET contact_id=ottocrat_servicecontracts.sc_related_to" .
					" WHERE ottocrat_servicecontracts.sc_related_to IS NOT NULL AND ottocrat_servicecontracts.sc_related_to != 0" .
					" AND ottocrat_servicecontracts.servicecontractsid = ? AND ottocrat_troubletickets.ticketid = ?";
				$this->db->pquery($updateQuery, array($focusId, $ticketId));
			}
		}
	}

	// Function to Compute and Update the Used Units and Progress of the Service Contract based on all the related Trouble tickets.
	function updateServiceContractState($focusId) {
		$this->id = $focusId;
		$this->retrieve_entity_info($focusId,'ServiceContracts');

		$contractTicketsResult = $this->db->pquery("SELECT relcrmid FROM ottocrat_crmentityrel
														WHERE module = 'ServiceContracts'
														AND relmodule = 'HelpDesk' AND crmid = ?
													UNION
														SELECT crmid FROM ottocrat_crmentityrel
														WHERE relmodule = 'ServiceContracts'
														AND module = 'HelpDesk' AND relcrmid = ?",
													array($focusId,$focusId));

		$noOfTickets = $this->db->num_rows($contractTicketsResult);
		$ticketFocus = CRMEntity::getInstance('HelpDesk');
		$totalUsedUnits = 0;
		for($i=0; $i < $noOfTickets; ++$i) {
			$ticketId = $this->db->query_result($contractTicketsResult, $i, 'relcrmid');
			$ticketFocus->id = $ticketId;
			if(isRecordExists($ticketId)) {
				$ticketFocus->retrieve_entity_info($ticketId, 'HelpDesk');
				if (strtolower($ticketFocus->column_fields['ticketstatus']) == 'closed') {
					$totalUsedUnits += $this->computeUsedUnits($ticketFocus->column_fields);
				}
			}
		}
		$this->updateUsedUnits($totalUsedUnits);

		$this->calculateProgress();
	}

	// Function to Upate the Used Units of the Service Contract based on the given Ticket id.
	function computeUsedUnits($ticketData, $operator='+') {
		$trackingUnit = strtolower($this->column_fields['tracking_unit']);
		$workingHoursPerDay = 24;

		$usedUnits = 0;
		if ($trackingUnit == 'incidents') {
			$usedUnits = 1;
		} elseif ($trackingUnit == 'days') {
			if(!empty($ticketData['days'])) {
				$usedUnits = $ticketData['days'];
			} elseif(!empty($ticketData['hours'])) {
				$usedUnits = $ticketData['hours'] / $workingHoursPerDay;
			}
		} elseif ($trackingUnit == 'hours') {
			if(!empty($ticketData['hours'])) {
				$usedUnits = $ticketData['hours'];
			} elseif(!empty($ticketData['days'])) {
				$usedUnits = $ticketData['days'] * $workingHoursPerDay;
			}
		}
		return $usedUnits;
	}

	// Function to Upate the Used Units of the Service Contract.
	function updateUsedUnits($usedUnits) {
		$this->column_fields['used_units'] = $usedUnits;
		$updateQuery = "UPDATE ottocrat_servicecontracts SET used_units = $usedUnits WHERE servicecontractsid = ?";
		$this->db->pquery($updateQuery, array($this->id));
	}

	// Function to Calculate the End Date, Planned Duration, Actual Duration and Progress of a Service Contract
	function calculateProgress() {
		$updateCols = array();
		$updateParams = array();

		$startDate = $this->column_fields['start_date'];
		$dueDate = $this->column_fields['due_date'];
		$endDate = $this->column_fields['end_date'];

		$usedUnits = decimalFormat($this->column_fields['used_units']);
		$totalUnits = decimalFormat($this->column_fields['total_units']);

		$contractStatus = $this->column_fields['contract_status'];

		// Update the End date if the status is Complete or if the Used Units reaches/exceeds Total Units
		// We need to do this first to make sure Actual duration is computed properly
		if($contractStatus == 'Complete' || (!empty($usedUnits) && !empty($totalUnits) && $usedUnits >= $totalUnits)) {
			if(empty($endDate)) {
				$endDate = date('Y-m-d');
				$this->db->pquery('UPDATE ottocrat_servicecontracts SET end_date=? WHERE servicecontractsid = ?', array(date('Y-m-d'), $this->id));
			}
		} else {
			$endDate = null;
			$this->db->pquery('UPDATE ottocrat_servicecontracts SET end_date=? WHERE servicecontractsid = ?', array(null, $this->id));
		}

		// Calculate the Planned Duration based on Due date and Start date. (in days)
		if(!empty($dueDate) && !empty($startDate)) {
			$plannedDurationUpdate = " planned_duration = (TO_DAYS(due_date)-TO_DAYS(start_date)+1)";
		} else {
			$plannedDurationUpdate = " planned_duration = ''";
		}
		array_push($updateCols, $plannedDurationUpdate);

		// Calculate the Actual Duration based on End date and Start date. (in days)
		if(!empty($endDate) && !empty($startDate)) {
			$actualDurationUpdate = "actual_duration = (TO_DAYS(end_date)-TO_DAYS(start_date)+1)";
		} else {
			$actualDurationUpdate = "actual_duration = ''";
		}
		array_push($updateCols, $actualDurationUpdate);

		// Update the Progress based on Used Units and Total Units (in percentage)
		if(!empty($usedUnits) && !empty($totalUnits)) {
			$progressUpdate = 'progress = ?';
			$progressUpdateParams = floatval(($usedUnits * 100) / $totalUnits);
		} else {
			$progressUpdate = 'progress = ?';
			$progressUpdateParams = null;
		}
		array_push($updateCols, $progressUpdate);
		array_push($updateParams, $progressUpdateParams);

		if(count($updateCols) > 0) {
			$updateQuery = 'UPDATE ottocrat_servicecontracts SET '. implode(",", $updateCols) .' WHERE servicecontractsid = ?';
			array_push($updateParams, $this->id);
			$this->db->pquery($updateQuery, $updateParams);
		}
	}

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	function delete_related_module($module, $crmid, $with_module, $with_crmid) {
	 	parent::delete_related_module($module, $crmid, $with_module, $with_crmid);
	 	if ($with_module == 'HelpDesk') {
	 		$this->updateServiceContractState($crmid);
	 	}
	}

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/** Function to unlink an entity with given Id from another entity */
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log, $currentModule;

		if($return_module == 'Accounts') {
			$focus = CRMEntity::getInstance($return_module);
			$entityIds = $focus->getRelatedContactsIds($return_id);
			array_push($entityIds, $return_id);
			$entityIds = implode(',', $entityIds);
			$return_modules = "'Accounts','Contacts'";
		} else {
			$entityIds = $return_id;
			$return_modules = "'".$return_module."'";
		}

		$query = 'DELETE FROM ottocrat_crmentityrel WHERE (relcrmid='.$id.' AND module IN ('.$return_modules.') AND crmid IN ('.$entityIds.')) OR (crmid='.$id.' AND relmodule IN ('.$return_modules.') AND relcrmid IN ('.$entityIds.'))';
		$this->db->pquery($query, array());

		$sql = 'SELECT tabid, tablename, columnname FROM ottocrat_field WHERE fieldid IN (SELECT fieldid FROM ottocrat_fieldmodulerel WHERE module=? AND relmodule IN ('.$return_modules.'))';
		$fieldRes = $this->db->pquery($sql, array($currentModule));
		$numOfFields = $this->db->num_rows($fieldRes);
		for ($i = 0; $i < $numOfFields; $i++) {
			$tabId = $this->db->query_result($fieldRes, $i, 'tabid');
			$tableName = $this->db->query_result($fieldRes, $i, 'tablename');
			$columnName = $this->db->query_result($fieldRes, $i, 'columnname');
			$relatedModule = vtlib_getModuleNameById($tabId);
			$focusObj = CRMEntity::getInstance($relatedModule);

			$updateQuery = "UPDATE $tableName SET $columnName=? WHERE $columnName IN ($entityIds) AND $focusObj->table_index=?";
			$updateParams = array(null, $id);
			$this->db->pquery($updateQuery, $updateParams);
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