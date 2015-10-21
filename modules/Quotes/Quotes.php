<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header$
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
class Quotes extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "ottocrat_quotes";
	var $table_index= 'quoteid';
	var $tab_name = Array('ottocrat_crmentity','ottocrat_quotes','ottocrat_quotesbillads','ottocrat_quotesshipads','ottocrat_quotescf','ottocrat_inventoryproductrel');
	var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_quotes'=>'quoteid','ottocrat_quotesbillads'=>'quotebilladdressid','ottocrat_quotesshipads'=>'quoteshipaddressid','ottocrat_quotescf'=>'quoteid','ottocrat_inventoryproductrel'=>'id');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_quotescf', 'quoteid');
	var $entity_table = "ottocrat_crmentity";

	var $billadr_table = "ottocrat_quotesbillads";

	var $object_name = "Quote";

	var $new_schema = true;

	var $column_fields = Array();

	var $sortby_fields = Array('subject','crmid','smownerid','accountname','lastname');

	// This is used to retrieve related ottocrat_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id' );

	// This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
				//'Quote No'=>Array('crmentity'=>'crmid'),
				// Module Sequence Numbering
				'Quote No'=>Array('quotes'=>'quote_no'),
				// END
				'Subject'=>Array('quotes'=>'subject'),
				'Quote Stage'=>Array('quotes'=>'quotestage'),
				'Potential Name'=>Array('quotes'=>'potentialid'),
				'Account Name'=>Array('account'=> 'accountid'),
				'Total'=>Array('quotes'=> 'total'),
				'Assigned To'=>Array('crmentity'=>'smownerid')
				);

	var $list_fields_name = Array(
				        'Quote No'=>'quote_no',
				        'Subject'=>'subject',
				        'Quote Stage'=>'quotestage',
				        'Potential Name'=>'potential_id',
					'Account Name'=>'account_id',
					'Total'=>'hdnGrandTotal',
				        'Assigned To'=>'assigned_user_id'
				      );
	var $list_link_field= 'subject';

	var $search_fields = Array(
				'Quote No'=>Array('quotes'=>'quote_no'),
				'Subject'=>Array('quotes'=>'subject'),
				'Account Name'=>Array('quotes'=>'accountid'),
				'Quote Stage'=>Array('quotes'=>'quotestage'),
				);

	var $search_fields_name = Array(
					'Quote No'=>'quote_no',
				        'Subject'=>'subject',
				        'Account Name'=>'account_id',
				        'Quote Stage'=>'quotestage',
				      );

	// This is the list of ottocrat_fields that are required.
	var $required_fields =  array("accountname"=>1);

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'ASC';
	//var $groupTable = Array('ottocrat_quotegrouprelation','quoteid');

	var $mandatory_fields = Array('subject','createdtime' ,'modifiedtime', 'assigned_user_id');

	// For Alphabetical search
	var $def_basicsearch_col = 'subject';

	// For workflows update field tasks is deleted all the lineitems.
	var $isLineItemUpdate = true;

	/**	Constructor which will set the column_fields in this object
	 */
	function Quotes() {
		$this->log =LoggerManager::getLogger('quote');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Quotes');
	}

	function save_module()
	{
		global $adb;
		//in ajax save we should not call this function, because this will delete all the existing product values
		if($_REQUEST['action'] != 'QuotesAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
				&& $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates'
				&& $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false) {
			//Based on the total Number of rows we will save the product relationship with this entity
			saveInventoryProductDetails($this, 'Quotes');
		}

		// Update the currency id and the conversion rate for the quotes
		$update_query = "update ottocrat_quotes set currency_id=?, conversion_rate=? where quoteid=?";
		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		$adb->pquery($update_query, $update_params);
	}

	/**	function used to get the list of sales orders which are related to the Quotes
	 *	@param int $id - quote id
	 *	@return array - return an array which will be returned from the function GetRelatedList
	 */
	function get_salesorder($id)
	{
		global $log,$singlepane_view;
		$log->debug("Entering get_salesorder(".$id.") method ...");
		require_once('modules/SalesOrder/SalesOrder.php');
	        $focus = new SalesOrder();

		$button = '';

		if($singlepane_view == 'true')
			$returnset = '&return_module=Quotes&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=Quotes&return_action=CallRelatedList&return_id='.$id;

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "select ottocrat_crmentity.*, ottocrat_salesorder.*, ottocrat_quotes.subject as quotename
			, ottocrat_account.accountname,case when (ottocrat_users.user_name not like '') then
			$userNameSql else ottocrat_groups.groupname end as user_name
		from ottocrat_salesorder
		inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_salesorder.salesorderid
		left outer join ottocrat_quotes on ottocrat_quotes.quoteid=ottocrat_salesorder.quoteid
		left outer join ottocrat_account on ottocrat_account.accountid=ottocrat_salesorder.accountid
		left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
        LEFT JOIN ottocrat_salesordercf ON ottocrat_salesordercf.salesorderid = ottocrat_salesorder.salesorderid
        LEFT JOIN ottocrat_invoice_recurring_info ON ottocrat_invoice_recurring_info.start_period = ottocrat_salesorder.salesorderid
		LEFT JOIN ottocrat_sobillads ON ottocrat_sobillads.sobilladdressid = ottocrat_salesorder.salesorderid
		LEFT JOIN ottocrat_soshipads ON ottocrat_soshipads.soshipaddressid = ottocrat_salesorder.salesorderid
		left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
		where ottocrat_crmentity.deleted=0 and ottocrat_salesorder.quoteid = ".$id;
		$log->debug("Exiting get_salesorder method ...");
		return GetRelatedList('Quotes','SalesOrder',$focus,$query,$button,$returnset);
	}

	/**	function used to get the list of activities which are related to the Quotes
	 *	@param int $id - quote id
	 *	@return array - return an array which will be returned from the function GetRelatedList
	 */
	function get_activities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_activities(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/Activity.php");
		$other = new Activity();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		$button .= '<input type="hidden" name="activity_mode">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				if(getFieldVisibilityPermission('Calendar',$current_user->id,'parent_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Task\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_TODO', $related_module) ."'>&nbsp;";
				}
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else
		ottocrat_groups.groupname end as user_name, ottocrat_contactdetails.contactid,
		ottocrat_contactdetails.lastname, ottocrat_contactdetails.firstname, ottocrat_activity.*,
		ottocrat_seactivityrel.crmid as parent_id,ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
		ottocrat_crmentity.modifiedtime,ottocrat_recurringevents.recurringtype
		from ottocrat_activity
		inner join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=
		ottocrat_activity.activityid
		inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
		left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid=
		ottocrat_activity.activityid
		left join ottocrat_contactdetails on ottocrat_contactdetails.contactid =
		ottocrat_cntactivityrel.contactid
		left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
		left outer join ottocrat_recurringevents on ottocrat_recurringevents.activityid=
		ottocrat_activity.activityid
		left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
		where ottocrat_seactivityrel.crmid=".$id." and ottocrat_crmentity.deleted=0 and
			activitytype='Task' and (ottocrat_activity.status is not NULL and
			ottocrat_activity.status != 'Completed') and (ottocrat_activity.status is not NULL and
			ottocrat_activity.status != 'Deferred')";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/**	function used to get the the activity history related to the quote
	 *	@param int $id - quote id
	 *	@return array - return an array which will be returned from the function GetHistory
	 */
	function get_history($id)
	{
		global $log;
		$log->debug("Entering get_history(".$id.") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_activity.activityid, ottocrat_activity.subject, ottocrat_activity.status,
			ottocrat_activity.eventstatus, ottocrat_activity.activitytype,ottocrat_activity.date_start,
			ottocrat_activity.due_date,ottocrat_activity.time_start, ottocrat_activity.time_end,
			ottocrat_contactdetails.contactid,
			ottocrat_contactdetails.firstname,ottocrat_contactdetails.lastname, ottocrat_crmentity.modifiedtime,
			ottocrat_crmentity.createdtime, ottocrat_crmentity.description, case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name
			from ottocrat_activity
				inner join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=ottocrat_activity.activityid
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
				left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid= ottocrat_activity.activityid
				left join ottocrat_contactdetails on ottocrat_contactdetails.contactid= ottocrat_cntactivityrel.contactid
                                left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
				where ottocrat_activity.activitytype='Task'
  				and (ottocrat_activity.status = 'Completed' or ottocrat_activity.status = 'Deferred')
	 	        	and ottocrat_seactivityrel.crmid=".$id."
                                and ottocrat_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('Quotes',$query,$id);
	}





	/**	Function used to get the Quote Stage history of the Quotes
	 *	@param $id - quote id
	 *	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_quotestagehistory($id)
	{
		global $log;
		$log->debug("Entering get_quotestagehistory(".$id.") method ...");

		global $adb;
		global $mod_strings;
		global $app_strings;

		$query = 'select ottocrat_quotestagehistory.*, ottocrat_quotes.quote_no from ottocrat_quotestagehistory inner join ottocrat_quotes on ottocrat_quotes.quoteid = ottocrat_quotestagehistory.quoteid inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_quotes.quoteid where ottocrat_crmentity.deleted = 0 and ottocrat_quotes.quoteid = ?';
		$result=$adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Quote No'];
		$header[] = $app_strings['LBL_ACCOUNT_NAME'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['Quote Stage'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Account Name , Total are mandatory fields. So no need to do security check to these fields.
		global $current_user;

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$quotestage_access = (getFieldVisibilityPermission('Quotes', $current_user->id, 'quotestage') != '0')? 1 : 0;
		$picklistarray = getAccessPickListValues('Quotes');

		$quotestage_array = ($quotestage_access != 1)? $picklistarray['quotestage']: array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($quotestage_access != 1)? 'Not Accessible': '-';

		while($row = $adb->fetch_array($result))
		{
			$entries = Array();

			// Module Sequence Numbering
			//$entries[] = $row['quoteid'];
			$entries[] = $row['quote_no'];
			// END
			$entries[] = $row['accountname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['quotestage'], $quotestage_array))? $row['quotestage']: $error_msg;
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDateTimeValue();

			$entries_list[] = $entries;
		}

		$return_data = Array('header'=>$header,'entries'=>$entries_list);

	 	$log->debug("Exiting get_quotestagehistory method ...");

		return $return_data;
	}

	// Function to get column name - Overriding function of base class
	function get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype='') {
		if ($columname == 'potentialid' || $columname == 'contactid') {
			if ($fldvalue == '') return null;
		}
		return parent::get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype);
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryPlanner){
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('ottocrat_crmentityQuotes', array('ottocrat_usersQuotes', 'ottocrat_groupsQuotes', 'ottocrat_lastModifiedByQuotes'));
		$matrix->setDependency('ottocrat_inventoryproductrelQuotes', array('ottocrat_productsQuotes', 'ottocrat_serviceQuotes'));
		$matrix->setDependency('ottocrat_quotes',array('ottocrat_crmentityQuotes', "ottocrat_currency_info$secmodule",
				'ottocrat_quotescf', 'ottocrat_potentialRelQuotes', 'ottocrat_quotesbillads','ottocrat_quotesshipads',
				'ottocrat_inventoryproductrelQuotes', 'ottocrat_contactdetailsQuotes', 'ottocrat_accountQuotes',
				'ottocrat_invoice_recurring_info','ottocrat_quotesQuotes','ottocrat_usersRel1'));

		if (!$queryPlanner->requireTable('ottocrat_quotes', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"ottocrat_quotes","quoteid", $queryPlanner);
		if ($queryPlanner->requireTable("ottocrat_crmentityQuotes", $matrix)){
			$query .= " left join ottocrat_crmentity as ottocrat_crmentityQuotes on ottocrat_crmentityQuotes.crmid=ottocrat_quotes.quoteid and ottocrat_crmentityQuotes.deleted=0";
		}
		if ($queryPlanner->requireTable("ottocrat_quotescf")){
			$query .= " left join ottocrat_quotescf on ottocrat_quotes.quoteid = ottocrat_quotescf.quoteid";
		}
		if ($queryPlanner->requireTable("ottocrat_quotesbillads")){
			$query .= " left join ottocrat_quotesbillads on ottocrat_quotes.quoteid=ottocrat_quotesbillads.quotebilladdressid";
		}
		if ($queryPlanner->requireTable("ottocrat_quotesshipads")){
			$query .= " left join ottocrat_quotesshipads on ottocrat_quotes.quoteid=ottocrat_quotesshipads.quoteshipaddressid";
		}
		if ($queryPlanner->requireTable("ottocrat_currency_info$secmodule")){
			$query .= " left join ottocrat_currency_info as ottocrat_currency_info$secmodule on ottocrat_currency_info$secmodule.id = ottocrat_quotes.currency_id";
		}
		if ($queryPlanner->requireTable("ottocrat_inventoryproductrelQuotes",$matrix)){
			$query .= " left join ottocrat_inventoryproductrel as ottocrat_inventoryproductrelQuotes on ottocrat_quotes.quoteid = ottocrat_inventoryproductrelQuotes.id";
            // To Eliminate duplicates in reports
            if(($module == 'Products' || $module == 'Services') && $secmodule == "Quotes"){
                if($module == 'Products'){
                    $query .= " and ottocrat_inventoryproductrelQuotes.productid = ottocrat_products.productid ";    
                }else if($module== 'Services'){
                    $query .= " and ottocrat_inventoryproductrelQuotes.productid = ottocrat_service.serviceid ";
                }
            }
		}
		if ($queryPlanner->requireTable("ottocrat_productsQuotes")){
			$query .= " left join ottocrat_products as ottocrat_productsQuotes on ottocrat_productsQuotes.productid = ottocrat_inventoryproductrelQuotes.productid";
		}
		if ($queryPlanner->requireTable("ottocrat_serviceQuotes")){
			$query .= " left join ottocrat_service as ottocrat_serviceQuotes on ottocrat_serviceQuotes.serviceid = ottocrat_inventoryproductrelQuotes.productid";
		}
		if ($queryPlanner->requireTable("ottocrat_groupsQuotes")){
			$query .= " left join ottocrat_groups as ottocrat_groupsQuotes on ottocrat_groupsQuotes.groupid = ottocrat_crmentityQuotes.smownerid";
		}
		if ($queryPlanner->requireTable("ottocrat_usersQuotes")){
			$query .= " left join ottocrat_users as ottocrat_usersQuotes on ottocrat_usersQuotes.id = ottocrat_crmentityQuotes.smownerid";
		}
		if ($queryPlanner->requireTable("ottocrat_usersRel1")){
			$query .= " left join ottocrat_users as ottocrat_usersRel1 on ottocrat_usersRel1.id = ottocrat_quotes.inventorymanager";
		}
		if ($queryPlanner->requireTable("ottocrat_potentialRelQuotes")){
			$query .= " left join ottocrat_potential as ottocrat_potentialRelQuotes on ottocrat_potentialRelQuotes.potentialid = ottocrat_quotes.potentialid";
		}
		if ($queryPlanner->requireTable("ottocrat_contactdetailsQuotes")){
			$query .= " left join ottocrat_contactdetails as ottocrat_contactdetailsQuotes on ottocrat_contactdetailsQuotes.contactid = ottocrat_quotes.contactid";
		}
		if ($queryPlanner->requireTable("ottocrat_accountQuotes")){
			$query .= " left join ottocrat_account as ottocrat_accountQuotes on ottocrat_accountQuotes.accountid = ottocrat_quotes.accountid";
		}
		if ($queryPlanner->requireTable("ottocrat_lastModifiedByQuotes")){
			$query .= " left join ottocrat_users as ottocrat_lastModifiedByQuotes on ottocrat_lastModifiedByQuotes.id = ottocrat_crmentityQuotes.modifiedby ";
		}
        if ($queryPlanner->requireTable("ottocrat_createdbyQuotes")){
			$query .= " left join ottocrat_users as ottocrat_createdbyQuotes on ottocrat_createdbyQuotes.id = ottocrat_crmentityQuotes.smcreatorid ";
		}
		return $query;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"SalesOrder" =>array("ottocrat_salesorder"=>array("quoteid","salesorderid"),"ottocrat_quotes"=>"quoteid"),
			"Calendar" =>array("ottocrat_seactivityrel"=>array("crmid","activityid"),"ottocrat_quotes"=>"quoteid"),
			"Documents" => array("ottocrat_senotesrel"=>array("crmid","notesid"),"ottocrat_quotes"=>"quoteid"),
			"Accounts" => array("ottocrat_quotes"=>array("quoteid","accountid")),
			"Contacts" => array("ottocrat_quotes"=>array("quoteid","contactid")),
			"Potentials" => array("ottocrat_quotes"=>array("quoteid","potentialid")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Accounts' ) {
			$this->trash('Quotes',$id);
		} elseif($return_module == 'Potentials') {
			$relation_query = 'UPDATE ottocrat_quotes SET potentialid=? WHERE quoteid=?';
			$this->db->pquery($relation_query, array(null, $id));
		} elseif($return_module == 'Contacts') {
			$relation_query = 'UPDATE ottocrat_quotes SET contactid=? WHERE quoteid=?';
			$this->db->pquery($relation_query, array(null, $id));
		} else {
			$sql = 'DELETE FROM ottocrat_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	function insertIntoEntityTable($table_name, $module, $fileid = '')  {
		//Ignore relation table insertions while saving of the record
		if($table_name == 'ottocrat_inventoryproductrel') {
			return;
		}
		parent::insertIntoEntityTable($table_name, $module, $fileid);
	}

	/*Function to create records in current module.
	**This function called while importing records to this module*/
	function createRecords($obj) {
		$createRecords = createRecords($obj);
		return $createRecords;
	}

	/*Function returns the record information which means whether the record is imported or not
	**This function called while importing records to this module*/
	function importRecord($obj, $inventoryFieldData, $lineItemDetails) {
		$entityInfo = importRecord($obj, $inventoryFieldData, $lineItemDetails);
		return $entityInfo;
	}

	/*Function to return the status count of imported records in current module.
	**This function called while importing records to this module*/
	function getImportStatusCount($obj) {
		$statusCount = getImportStatusCount($obj);
		return $statusCount;
	}

	function undoLastImport($obj, $user) {
		$undoLastImport = undoLastImport($obj, $user);
	}

	/** Function to export the lead records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Quotes Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Quotes", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM ".$this->entity_table."
				INNER JOIN ottocrat_quotes ON ottocrat_quotes.quoteid = ottocrat_crmentity.crmid
				LEFT JOIN ottocrat_quotescf ON ottocrat_quotescf.quoteid = ottocrat_quotes.quoteid
				LEFT JOIN ottocrat_quotesbillads ON ottocrat_quotesbillads.quotebilladdressid = ottocrat_quotes.quoteid
				LEFT JOIN ottocrat_quotesshipads ON ottocrat_quotesshipads.quoteshipaddressid = ottocrat_quotes.quoteid
				LEFT JOIN ottocrat_inventoryproductrel ON ottocrat_inventoryproductrel.id = ottocrat_quotes.quoteid
				LEFT JOIN ottocrat_products ON ottocrat_products.productid = ottocrat_inventoryproductrel.productid
				LEFT JOIN ottocrat_service ON ottocrat_service.serviceid = ottocrat_inventoryproductrel.productid
				LEFT JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_quotes.contactid
				LEFT JOIN ottocrat_potential ON ottocrat_potential.potentialid = ottocrat_quotes.potentialid
				LEFT JOIN ottocrat_account ON ottocrat_account.accountid = ottocrat_quotes.accountid
				LEFT JOIN ottocrat_currency_info ON ottocrat_currency_info.id = ottocrat_quotes.currency_id
				LEFT JOIN ottocrat_users AS ottocrat_inventoryManager ON ottocrat_inventoryManager.id = ottocrat_quotes.inventorymanager
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('Quotes',$current_user);
		$where_auto = " ottocrat_crmentity.deleted=0";

		if($where != "") {
			$query .= " where ($where) AND ".$where_auto;
		} else {
			$query .= " where ".$where_auto;
		}

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

}

?>
