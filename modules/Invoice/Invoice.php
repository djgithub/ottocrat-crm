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

class Invoice extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "ottocrat_invoice";
	var $table_index= 'invoiceid';
	var $tab_name = Array('ottocrat_crmentity','ottocrat_invoice','ottocrat_invoicebillads','ottocrat_invoiceshipads','ottocrat_invoicecf', 'ottocrat_inventoryproductrel');
	var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_invoice'=>'invoiceid','ottocrat_invoicebillads'=>'invoicebilladdressid','ottocrat_invoiceshipads'=>'invoiceshipaddressid','ottocrat_invoicecf'=>'invoiceid','ottocrat_inventoryproductrel'=>'id');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_invoicecf', 'invoiceid');

	var $column_fields = Array();

	var $update_product_array = Array();

	var $sortby_fields = Array('subject','invoice_no','invoicestatus','smownerid','accountname','lastname');

	// This is used to retrieve related ottocrat_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id' );

	// This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
				//'Invoice No'=>Array('crmentity'=>'crmid'),
				'Invoice No'=>Array('invoice'=>'invoice_no'),
				'Subject'=>Array('invoice'=>'subject'),
				'Sales Order'=>Array('invoice'=>'salesorderid'),
				'Status'=>Array('invoice'=>'invoicestatus'),
				'Total'=>Array('invoice'=>'total'),
				'Assigned To'=>Array('crmentity'=>'smownerid')
				);

	var $list_fields_name = Array(
				        'Invoice No'=>'invoice_no',
				        'Subject'=>'subject',
				        'Sales Order'=>'salesorder_id',
				        'Status'=>'invoicestatus',
				        'Total'=>'hdnGrandTotal',
				        'Assigned To'=>'assigned_user_id'
				      );
	var $list_link_field= 'subject';

	var $search_fields = Array(
				//'Invoice No'=>Array('crmentity'=>'crmid'),
				'Invoice No'=>Array('invoice'=>'invoice_no'),
				'Subject'=>Array('purchaseorder'=>'subject'),
                'Account Name'=>Array('contactdetails'=>'account_id'),
                'Created Date' => Array('crmentity'=>'createdtime'),
                'Assigned To'=>Array('crmentity'=>'smownerid'),
				);

	var $search_fields_name = Array(
				        'Invoice No'          => 'invoice_no',
				        'Subject'             => 'subject',
                        'Account Name'        => 'account_id',
                        'Created Time'        => 'createdtime',
                        'Assigned To'         => 'assigned_user_id'
				      );

	// This is the list of ottocrat_fields that are required.
	var $required_fields =  array("accountname"=>1);

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'ASC';

	//var $groupTable = Array('ottocrat_invoicegrouprelation','invoiceid');

	var $mandatory_fields = Array('subject','createdtime' ,'modifiedtime', 'assigned_user_id');
	var $_salesorderid;
	var $_recurring_mode;

	// For Alphabetical search
	var $def_basicsearch_col = 'subject';

	var $entity_table = "ottocrat_crmentity";

	// For workflows update field tasks is deleted all the lineitems.
	var $isLineItemUpdate = true;

	/**	Constructor which will set the column_fields in this object
	 */
	function Invoice() {
		$this->log =LoggerManager::getLogger('Invoice');
		$this->log->debug("Entering Invoice() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Invoice');
		$this->log->debug("Exiting Invoice method ...");
	}


	/** Function to handle the module specific save operations

	*/

	function save_module($module) {
		global $updateInventoryProductRel_deduct_stock;
		$updateInventoryProductRel_deduct_stock = true;

		//in ajax save we should not call this function, because this will delete all the existing product values
		if(isset($this->_recurring_mode) && $this->_recurring_mode == 'recurringinvoice_from_so' && isset($this->_salesorderid) && $this->_salesorderid!='') {
			// We are getting called from the RecurringInvoice cron service!
			$this->createRecurringInvoiceFromSO();

		} else if(isset($_REQUEST)) {
			if($_REQUEST['action'] != 'InvoiceAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
					&& $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates'
					&& $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false && $_REQUEST['action'] != 'FROM_WS') {
				//Based on the total Number of rows we will save the product relationship with this entity
				saveInventoryProductDetails($this, 'Invoice');
			} else if($_REQUEST['action'] == 'InvoiceAjax' || $_REQUEST['action'] == 'MassEditSave' || $_REQUEST['action'] == 'FROM_WS') {
				$updateInventoryProductRel_deduct_stock = false;
			}
		}
		// Update the currency id and the conversion rate for the invoice
		$update_query = "update ottocrat_invoice set currency_id=?, conversion_rate=? where invoiceid=?";

		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		$this->db->pquery($update_query, $update_params);
	}

	/**
	 * Customizing the restore procedure.
	 */
	function restore($module, $id) {
		global $updateInventoryProductRel_deduct_stock;
		$status = getInvoiceStatus($id);
		if($status != 'Cancel') {
			$updateInventoryProductRel_deduct_stock = true;
		}
		parent::restore($module, $id);
	}

	/**
	 * Customizing the Delete procedure.
	 */
	function trash($module, $recordId) {
		$status = getInvoiceStatus($recordId);
		if($status != 'Cancel') {
			addProductsToStock($recordId);
		}
		parent::trash($module, $recordId);
	}

	/**	function used to get the name of the current object
	 *	@return string $this->name - name of the current object
	 */
	function get_summary_text()
	{
		global $log;
		$log->debug("Entering get_summary_text() method ...");
		$log->debug("Exiting get_summary_text method ...");
		return $this->name;
	}


	/**	function used to get the list of activities which are related to the invoice
	 *	@param int $id - invoice id
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
		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
				ottocrat_contactdetails.lastname, ottocrat_contactdetails.firstname, ottocrat_contactdetails.contactid,
				ottocrat_activity.*,ottocrat_seactivityrel.crmid as parent_id,ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
				ottocrat_crmentity.modifiedtime
				from ottocrat_activity
				inner join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=ottocrat_activity.activityid
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
				left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid= ottocrat_activity.activityid
				left join ottocrat_contactdetails on ottocrat_contactdetails.contactid = ottocrat_cntactivityrel.contactid
				left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
				left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				where ottocrat_seactivityrel.crmid=".$id." and activitytype='Task' and ottocrat_crmentity.deleted=0
						and (ottocrat_activity.status is not NULL and ottocrat_activity.status != 'Completed')
						and (ottocrat_activity.status is not NULL and ottocrat_activity.status != 'Deferred')";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/**	function used to get the the activity history related to the quote
	 *	@param int $id - invoice id
	 *	@return array - return an array which will be returned from the function GetHistory
	 */
	function get_history($id)
	{
		global $log;
		$log->debug("Entering get_history(".$id.") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_contactdetails.lastname, ottocrat_contactdetails.firstname,
				ottocrat_contactdetails.contactid,ottocrat_activity.*,ottocrat_seactivityrel.*,
				ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_crmentity.modifiedtime,
				ottocrat_crmentity.createdtime, ottocrat_crmentity.description,
				case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name
				from ottocrat_activity
				inner join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=ottocrat_activity.activityid
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
				left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid= ottocrat_activity.activityid
				left join ottocrat_contactdetails on ottocrat_contactdetails.contactid = ottocrat_cntactivityrel.contactid
                left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
				where ottocrat_activity.activitytype='Task'
					and (ottocrat_activity.status = 'Completed' or ottocrat_activity.status = 'Deferred')
					and ottocrat_seactivityrel.crmid=".$id."
					and ottocrat_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('Invoice',$query,$id);
	}



	/**	Function used to get the Status history of the Invoice
	 *	@param $id - invoice id
	 *	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_invoicestatushistory($id)
	{
		global $log;
		$log->debug("Entering get_invoicestatushistory(".$id.") method ...");

		global $adb;
		global $mod_strings;
		global $app_strings;

		$query = 'select ottocrat_invoicestatushistory.*, ottocrat_invoice.invoice_no from ottocrat_invoicestatushistory inner join ottocrat_invoice on ottocrat_invoice.invoiceid = ottocrat_invoicestatushistory.invoiceid inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_invoice.invoiceid where ottocrat_crmentity.deleted = 0 and ottocrat_invoice.invoiceid = ?';
		$result=$adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Invoice No'];
		$header[] = $app_strings['LBL_ACCOUNT_NAME'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_INVOICE_STATUS'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Account Name , Amount are mandatory fields. So no need to do security check to these fields.
		global $current_user;

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$invoicestatus_access = (getFieldVisibilityPermission('Invoice', $current_user->id, 'invoicestatus') != '0')? 1 : 0;
		$picklistarray = getAccessPickListValues('Invoice');

		$invoicestatus_array = ($invoicestatus_access != 1)? $picklistarray['invoicestatus']: array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($invoicestatus_access != 1)? 'Not Accessible': '-';

		while($row = $adb->fetch_array($result))
		{
			$entries = Array();

			// Module Sequence Numbering
			//$entries[] = $row['invoiceid'];
			$entries[] = $row['invoice_no'];
			// END
			$entries[] = $row['accountname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['invoicestatus'], $invoicestatus_array))? $row['invoicestatus']: $error_msg;
			$entries[] = DateTimeField::convertToUserFormat($row['lastmodified']);

			$entries_list[] = $entries;
		}

		$return_data = Array('header'=>$header,'entries'=>$entries_list);

	 	$log->debug("Exiting get_invoicestatushistory method ...");

		return $return_data;
	}

	// Function to get column name - Overriding function of base class
	function get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype = '') {
		if ($columname == 'salesorderid') {
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

		// Define the dependency matrix ahead
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('ottocrat_crmentityInvoice', array('ottocrat_usersInvoice', 'ottocrat_groupsInvoice', 'ottocrat_lastModifiedByInvoice'));
		$matrix->setDependency('ottocrat_inventoryproductrelInvoice', array('ottocrat_productsInvoice', 'ottocrat_serviceInvoice'));
		$matrix->setDependency('ottocrat_invoice',array('ottocrat_crmentityInvoice', "ottocrat_currency_info$secmodule",
				'ottocrat_invoicecf', 'ottocrat_salesorderInvoice', 'ottocrat_invoicebillads',
				'ottocrat_invoiceshipads', 'ottocrat_inventoryproductrelInvoice', 'ottocrat_contactdetailsInvoice', 'ottocrat_accountInvoice'));

		if (!$queryPlanner->requireTable('ottocrat_invoice', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"ottocrat_invoice","invoiceid", $queryPlanner);

		if ($queryPlanner->requireTable('ottocrat_crmentityInvoice', $matrix)) {
			$query .= " left join ottocrat_crmentity as ottocrat_crmentityInvoice on ottocrat_crmentityInvoice.crmid=ottocrat_invoice.invoiceid and ottocrat_crmentityInvoice.deleted=0";
		}
		if ($queryPlanner->requireTable('ottocrat_invoicecf')) {
			$query .= " left join ottocrat_invoicecf on ottocrat_invoice.invoiceid = ottocrat_invoicecf.invoiceid";
		}
		if ($queryPlanner->requireTable("ottocrat_currency_info$secmodule")) {
			$query .= " left join ottocrat_currency_info as ottocrat_currency_info$secmodule on ottocrat_currency_info$secmodule.id = ottocrat_invoice.currency_id";
		}
		if ($queryPlanner->requireTable('ottocrat_salesorderInvoice')) {
			$query .= " left join ottocrat_salesorder as ottocrat_salesorderInvoice on ottocrat_salesorderInvoice.salesorderid=ottocrat_invoice.salesorderid";
		}
		if ($queryPlanner->requireTable('ottocrat_invoicebillads')) {
			$query .= " left join ottocrat_invoicebillads on ottocrat_invoice.invoiceid=ottocrat_invoicebillads.invoicebilladdressid";
		}
		if ($queryPlanner->requireTable('ottocrat_invoiceshipads')) {
			$query .= " left join ottocrat_invoiceshipads on ottocrat_invoice.invoiceid=ottocrat_invoiceshipads.invoiceshipaddressid";
		}
		if ($queryPlanner->requireTable('ottocrat_inventoryproductrelInvoice', $matrix)) {
			$query .= " left join ottocrat_inventoryproductrel as ottocrat_inventoryproductrelInvoice on ottocrat_invoice.invoiceid = ottocrat_inventoryproductrelInvoice.id";
            // To Eliminate duplicates in reports
            if(($module == 'Products' || $module == 'Services') && $secmodule == "Invoice"){
                if($module == 'Products'){
                    $query .= " and ottocrat_inventoryproductrelInvoice.productid = ottocrat_products.productid ";    
                }else if($module == 'Services'){
                    $query .= " and ottocrat_inventoryproductrelInvoice.productid = ottocrat_service.serviceid "; 
                }
            }
		}
		if ($queryPlanner->requireTable('ottocrat_productsInvoice')) {
			$query .= " left join ottocrat_products as ottocrat_productsInvoice on ottocrat_productsInvoice.productid = ottocrat_inventoryproductrelInvoice.productid";
		}
		if ($queryPlanner->requireTable('ottocrat_serviceInvoice')) {
			$query .= " left join ottocrat_service as ottocrat_serviceInvoice on ottocrat_serviceInvoice.serviceid = ottocrat_inventoryproductrelInvoice.productid";
		}
		if ($queryPlanner->requireTable('ottocrat_groupsInvoice')) {
			$query .= " left join ottocrat_groups as ottocrat_groupsInvoice on ottocrat_groupsInvoice.groupid = ottocrat_crmentityInvoice.smownerid";
		}
		if ($queryPlanner->requireTable('ottocrat_usersInvoice')) {
			$query .= " left join ottocrat_users as ottocrat_usersInvoice on ottocrat_usersInvoice.id = ottocrat_crmentityInvoice.smownerid";
		}
		if ($queryPlanner->requireTable('ottocrat_contactdetailsInvoice')) {
			$query .= " left join ottocrat_contactdetails as ottocrat_contactdetailsInvoice on ottocrat_invoice.contactid = ottocrat_contactdetailsInvoice.contactid";
		}
		if ($queryPlanner->requireTable('ottocrat_accountInvoice')) {
			$query .= " left join ottocrat_account as ottocrat_accountInvoice on ottocrat_accountInvoice.accountid = ottocrat_invoice.accountid";
		}
		if ($queryPlanner->requireTable('ottocrat_lastModifiedByInvoice')) {
			$query .= " left join ottocrat_users as ottocrat_lastModifiedByInvoice on ottocrat_lastModifiedByInvoice.id = ottocrat_crmentityInvoice.modifiedby ";
		}
        if ($queryPlanner->requireTable("ottocrat_createdbyInvoice")){
			$query .= " left join ottocrat_users as ottocrat_createdbyInvoice on ottocrat_createdbyInvoice.id = ottocrat_crmentityInvoice.smcreatorid ";
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
			"Calendar" =>array("ottocrat_seactivityrel"=>array("crmid","activityid"),"ottocrat_invoice"=>"invoiceid"),
			"Documents" => array("ottocrat_senotesrel"=>array("crmid","notesid"),"ottocrat_invoice"=>"invoiceid"),
			"Accounts" => array("ottocrat_invoice"=>array("invoiceid","accountid")),
                        "Contacts" => array("ottocrat_invoice"=>array("invoiceid","contactid")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Accounts' || $return_module == 'Contacts') {
			$this->trash('Invoice',$id);
		} elseif($return_module=='SalesOrder') {
			$relation_query = 'UPDATE ottocrat_invoice set salesorderid=? where invoiceid=?';
			$this->db->pquery($relation_query, array(null,$id));
		} else {
			$sql = 'DELETE FROM ottocrat_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	/*
	 * Function to get the relations of salesorder to invoice for recurring invoice procedure
	 * @param - $salesorder_id Salesorder ID
	 */
	function createRecurringInvoiceFromSO(){
		global $adb;
		$salesorder_id = $this->_salesorderid;
		$query1 = "SELECT * FROM ottocrat_inventoryproductrel WHERE id=?";
		$res = $adb->pquery($query1, array($salesorder_id));
		$no_of_products = $adb->num_rows($res);
		$fieldsList = $adb->getFieldsArray($res);
		$update_stock = array();
		for($j=0; $j<$no_of_products; $j++) {
			$row = $adb->query_result_rowdata($res, $j);
			$col_value = array();
			for($k=0; $k<count($fieldsList); $k++) {
				if($fieldsList[$k]!='lineitem_id'){
					$col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
				}
			}
			if(count($col_value) > 0) {
				$col_value['id'] = $this->id;
				$columns = array_keys($col_value);
				$values = array_values($col_value);
				$query2 = "INSERT INTO ottocrat_inventoryproductrel(". implode(",",$columns) .") VALUES (". generateQuestionMarks($values) .")";
				$adb->pquery($query2, array($values));
				$prod_id = $col_value['productid'];
				$qty = $col_value['quantity'];
				$update_stock[$col_value['sequence_no']] = $qty;
				updateStk($prod_id,$qty,'',array(),'Invoice');
			}
		}

		$query1 = "SELECT * FROM ottocrat_inventorysubproductrel WHERE id=?";
		$res = $adb->pquery($query1, array($salesorder_id));
		$no_of_products = $adb->num_rows($res);
		$fieldsList = $adb->getFieldsArray($res);
		for($j=0; $j<$no_of_products; $j++) {
			$row = $adb->query_result_rowdata($res, $j);
			$col_value = array();
			for($k=0; $k<count($fieldsList); $k++) {
					$col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
			}
			if(count($col_value) > 0) {
				$col_value['id'] = $this->id;
				$columns = array_keys($col_value);
				$values = array_values($col_value);
				$query2 = "INSERT INTO ottocrat_inventorysubproductrel(". implode(",",$columns) .") VALUES (". generateQuestionMarks($values) .")";
				$adb->pquery($query2, array($values));
				$prod_id = $col_value['productid'];
				$qty = $update_stock[$col_value['sequence_no']];
				updateStk($prod_id,$qty,'',array(),'Invoice');
			}
		}

		// Add the Shipping taxes for the Invoice
		$query3 = "SELECT * FROM ottocrat_inventoryshippingrel WHERE id=?";
		$res = $adb->pquery($query3, array($salesorder_id));
		$no_of_shippingtax = $adb->num_rows($res);
		$fieldsList = $adb->getFieldsArray($res);
		for($j=0; $j<$no_of_shippingtax; $j++) {
			$row = $adb->query_result_rowdata($res, $j);
			$col_value = array();
			for($k=0; $k<count($fieldsList); $k++) {
				$col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
			}
			if(count($col_value) > 0) {
				$col_value['id'] = $this->id;
				$columns = array_keys($col_value);
				$values = array_values($col_value);
				$query4 = "INSERT INTO ottocrat_inventoryshippingrel(". implode(",",$columns) .") VALUES (". generateQuestionMarks($values) .")";
				$adb->pquery($query4, array($values));
			}
		}

		//Update the netprice (subtotal), taxtype, discount, S&H charge, adjustment and total for the Invoice

		$updatequery  = " UPDATE ottocrat_invoice SET ";
		$updateparams = array();
		// Remaining column values to be updated -> column name to field name mapping
		$invoice_column_field = Array (
			'adjustment' => 'txtAdjustment',
			'subtotal' => 'hdnSubTotal',
			'total' => 'hdnGrandTotal',
			'taxtype' => 'hdnTaxType',
			'discount_percent' => 'hdnDiscountPercent',
			'discount_amount' => 'hdnDiscountAmount',
			's_h_amount' => 'hdnS_H_Amount',
		);
		$updatecols = array();
		foreach($invoice_column_field as $col => $field) {
			$updatecols[] = "$col=?";
			$updateparams[] = $this->column_fields[$field];
		}
		if (count($updatecols) > 0) {
			$updatequery .= implode(",", $updatecols);

			$updatequery .= " WHERE invoiceid=?";
			array_push($updateparams, $this->id);

			$adb->pquery($updatequery, $updateparams);
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
	* Returns Export Invoice Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Invoice", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM ".$this->entity_table."
				INNER JOIN ottocrat_invoice ON ottocrat_invoice.invoiceid = ottocrat_crmentity.crmid
				LEFT JOIN ottocrat_invoicecf ON ottocrat_invoicecf.invoiceid = ottocrat_invoice.invoiceid
				LEFT JOIN ottocrat_salesorder ON ottocrat_salesorder.salesorderid = ottocrat_invoice.salesorderid
				LEFT JOIN ottocrat_invoicebillads ON ottocrat_invoicebillads.invoicebilladdressid = ottocrat_invoice.invoiceid
				LEFT JOIN ottocrat_invoiceshipads ON ottocrat_invoiceshipads.invoiceshipaddressid = ottocrat_invoice.invoiceid
				LEFT JOIN ottocrat_inventoryproductrel ON ottocrat_inventoryproductrel.id = ottocrat_invoice.invoiceid
				LEFT JOIN ottocrat_products ON ottocrat_products.productid = ottocrat_inventoryproductrel.productid
				LEFT JOIN ottocrat_service ON ottocrat_service.serviceid = ottocrat_inventoryproductrel.productid
				LEFT JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_invoice.contactid
				LEFT JOIN ottocrat_account ON ottocrat_account.accountid = ottocrat_invoice.accountid
				LEFT JOIN ottocrat_currency_info ON ottocrat_currency_info.id = ottocrat_invoice.currency_id
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('Invoice',$current_user);
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