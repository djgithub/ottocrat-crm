<?php
/*********************************************************************************
** The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
*
 ********************************************************************************/
class Vendors extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "ottocrat_vendor";
	var $table_index= 'vendorid';
	var $tab_name = Array('ottocrat_crmentity','ottocrat_vendor','ottocrat_vendorcf');
	var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_vendor'=>'vendorid','ottocrat_vendorcf'=>'vendorid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_vendorcf', 'vendorid');
	var $column_fields = Array();

        //Pavani: Assign value to entity_table
        var $entity_table = "ottocrat_crmentity";
        var $sortby_fields = Array('vendorname','category');

        // This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
                                'Vendor Name'=>Array('vendor'=>'vendorname'),
                                'Phone'=>Array('vendor'=>'phone'),
                                'Email'=>Array('vendor'=>'email'),
                                'Category'=>Array('vendor'=>'category')
                                );
        var $list_fields_name = Array(
                                        'Vendor Name'=>'vendorname',
                                        'Phone'=>'phone',
                                        'Email'=>'email',
                                        'Category'=>'category'
                                     );
        var $list_link_field= 'vendorname';

	var $search_fields = Array(
                                'Vendor Name'=>Array('vendor'=>'vendorname'),
                                'Phone'=>Array('vendor'=>'phone')
                                );
        var $search_fields_name = Array(
                                        'Vendor Name'=>'vendorname',
                                        'Phone'=>'phone'
                                     );
	//Specifying required fields for vendors
        var $required_fields =  array();

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to ottocrat_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'vendorname', 'assigned_user_id');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'vendorname';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'vendorname';

	/**	Constructor which will set the column_fields in this object
	 */
	function Vendors() {
		$this->log =LoggerManager::getLogger('vendor');
		$this->log->debug("Entering Vendors() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Vendors');
		$this->log->debug("Exiting Vendor method ...");
	}

	function save_module($module)
	{
	}

	/**	function used to get the list of products which are related to the vendor
	 *	@param int $id - vendor id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_products(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab")."','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.parent_id.value=\"\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>";
			}
		}

		$query = "SELECT ottocrat_products.productid, ottocrat_products.productname, ottocrat_products.productcode,
					ottocrat_products.commissionrate, ottocrat_products.qty_per_unit, ottocrat_products.unit_price,
					ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,ottocrat_vendor.vendorname
			  		FROM ottocrat_products
			  		INNER JOIN ottocrat_vendor ON ottocrat_vendor.vendorid = ottocrat_products.vendor_id
			  		INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_products.productid INNER JOIN ottocrat_productcf
				    ON ottocrat_products.productid = ottocrat_productcf.productid
					LEFT JOIN ottocrat_users
						ON ottocrat_users.id=ottocrat_crmentity.smownerid
					LEFT JOIN ottocrat_groups
						ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			  		WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_vendor.vendorid = $id";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/**	function used to get the list of purchase orders which are related to the vendor
	 *	@param int $id - vendor id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_purchase_orders($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_purchase_orders(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab")."','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "select case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,ottocrat_crmentity.*, ottocrat_purchaseorder.*,ottocrat_vendor.vendorname from ottocrat_purchaseorder inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_purchaseorder.purchaseorderid left outer join ottocrat_vendor on ottocrat_purchaseorder.vendorid=ottocrat_vendor.vendorid LEFT JOIN ottocrat_purchaseordercf ON ottocrat_purchaseordercf.purchaseorderid = ottocrat_purchaseorder.purchaseorderid LEFT JOIN ottocrat_pobillads ON ottocrat_pobillads.pobilladdressid = ottocrat_purchaseorder.purchaseorderid LEFT JOIN ottocrat_poshipads ON ottocrat_poshipads.poshipaddressid = ottocrat_purchaseorder.purchaseorderid  left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid where ottocrat_crmentity.deleted=0 and ottocrat_purchaseorder.vendorid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_purchase_orders method ...");
		return $return_value;
	}
	//Pavani: Function to create, export query for vendors module
        /** Function to export the vendors in CSV Format
        * @param reference variable - where condition is passed when the query is executed
        * Returns Export Vendors Query.
        */
        function create_export_query($where)
        {
                global $log;
                global $current_user;
                $log->debug("Entering create_export_query(".$where.") method ...");

                include("include/utils/ExportUtils.php");

                //To get the Permitted fields query and the permitted fields list
                $sql = getPermittedFieldsQuery("Vendors", "detail_view");
                $fields_list = getFieldsListFromQuery($sql);

                $query = "SELECT $fields_list FROM ".$this->entity_table."
                                INNER JOIN ottocrat_vendor
                                        ON ottocrat_crmentity.crmid = ottocrat_vendor.vendorid
                                LEFT JOIN ottocrat_vendorcf
                                        ON ottocrat_vendorcf.vendorid=ottocrat_vendor.vendorid
                                LEFT JOIN ottocrat_seattachmentsrel
                                        ON ottocrat_vendor.vendorid=ottocrat_seattachmentsrel.crmid
                                LEFT JOIN ottocrat_attachments
                                ON ottocrat_seattachmentsrel.attachmentsid = ottocrat_attachments.attachmentsid
                                LEFT JOIN ottocrat_users
                                        ON ottocrat_crmentity.smownerid = ottocrat_users.id and ottocrat_users.status='Active'
                                ";
                $where_auto = " ottocrat_crmentity.deleted = 0 ";

                 if($where != "")
                   $query .= "  WHERE ($where) AND ".$where_auto;
                else
                   $query .= "  WHERE ".$where_auto;

                $log->debug("Exiting create_export_query method ...");
                return $query;
        }

	/**	function used to get the list of contacts which are related to the vendor
	 *	@param int $id - vendor id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab")."','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,ottocrat_contactdetails.*, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,ottocrat_vendorcontactrel.vendorid,ottocrat_account.accountname from ottocrat_contactdetails
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_contactdetails.contactid
				inner join ottocrat_vendorcontactrel on ottocrat_vendorcontactrel.contactid=ottocrat_contactdetails.contactid
				INNER JOIN ottocrat_contactaddress ON ottocrat_contactdetails.contactid = ottocrat_contactaddress.contactaddressid
				INNER JOIN ottocrat_contactsubdetails ON ottocrat_contactdetails.contactid = ottocrat_contactsubdetails.contactsubscriptionid
				INNER JOIN ottocrat_customerdetails ON ottocrat_contactdetails.contactid = ottocrat_customerdetails.customerid
				INNER JOIN ottocrat_contactscf ON ottocrat_contactdetails.contactid = ottocrat_contactscf.contactid
				left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				left join ottocrat_account on ottocrat_account.accountid = ottocrat_contactdetails.accountid
				left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
				where ottocrat_crmentity.deleted=0 and ottocrat_vendorcontactrel.vendorid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
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

		$rel_table_arr = Array("Products"=>"ottocrat_products","PurchaseOrder"=>"ottocrat_purchaseorder","Contacts"=>"ottocrat_vendorcontactrel");

		$tbl_field_arr = Array("ottocrat_products"=>"productid","ottocrat_vendorcontactrel"=>"contactid","ottocrat_purchaseorder"=>"purchaseorderid");

		$entity_tbl_field_arr = Array("ottocrat_products"=>"vendor_id","ottocrat_vendorcontactrel"=>"vendorid","ottocrat_purchaseorder"=>"vendorid");

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
		$log->debug("Exiting transferRelatedRecords...");
	}

	/** Returns a list of the associated emails
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_emails(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'></td>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
			ottocrat_activity.activityid, ottocrat_activity.subject,
			ottocrat_activity.activitytype, ottocrat_crmentity.modifiedtime,
			ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_activity.date_start,ottocrat_activity.time_start, ottocrat_seactivityrel.crmid as parent_id
			FROM ottocrat_activity, ottocrat_seactivityrel, ottocrat_vendor, ottocrat_users, ottocrat_crmentity
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid=ottocrat_crmentity.smownerid
			WHERE ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
				AND ottocrat_vendor.vendorid = ottocrat_seactivityrel.crmid
				AND ottocrat_users.id=ottocrat_crmentity.smownerid
				AND ottocrat_crmentity.crmid = ottocrat_activity.activityid
				AND ottocrat_vendor.vendorid = ".$id."
				AND ottocrat_activity.activitytype='Emails'
				AND ottocrat_crmentity.deleted = 0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}

	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */
	function generateReportsQuery($module, $queryPlanner) {
		$moduletable = $this->table_name;
		$moduleindex = $this->table_index;
		$modulecftable = $this->tab_name[2];
		$modulecfindex = $this->tab_name_index[$modulecftable];

		$query = "from $moduletable
			inner join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex
			inner join ottocrat_crmentity on ottocrat_crmentity.crmid=$moduletable.$moduleindex
			left join ottocrat_groups as ottocrat_groups$module on ottocrat_groups$module.groupid = ottocrat_crmentity.smownerid
			left join ottocrat_users as ottocrat_users".$module." on ottocrat_users".$module.".id = ottocrat_crmentity.smownerid
			left join ottocrat_groups on ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			left join ottocrat_users on ottocrat_users.id = ottocrat_crmentity.smownerid
			left join ottocrat_users as ottocrat_lastModifiedByVendors on ottocrat_lastModifiedByVendors.id = ottocrat_crmentity.modifiedby ";
		return $query;
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule, $queryplanner) {

		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("ottocrat_crmentityVendors",array("ottocrat_usersVendors","ottocrat_lastModifiedByVendors"));
		$matrix->setDependency("ottocrat_vendor",array("ottocrat_crmentityVendors","ottocrat_vendorcf","ottocrat_email_trackVendors"));
		if (!$queryplanner->requireTable('ottocrat_vendor', $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module,$secmodule,"ottocrat_vendor","vendorid", $queryplanner);
		// TODO Support query planner
		if ($queryplanner->requireTable("ottocrat_crmentityVendors",$matrix)){
		    $query .=" left join ottocrat_crmentity as ottocrat_crmentityVendors on ottocrat_crmentityVendors.crmid=ottocrat_vendor.vendorid and ottocrat_crmentityVendors.deleted=0";
		}
		if ($queryplanner->requireTable("ottocrat_vendorcf")){
		    $query .=" left join ottocrat_vendorcf on ottocrat_vendorcf.vendorid = ottocrat_crmentityVendors.crmid";
		}
		if ($queryplanner->requireTable("ottocrat_email_trackVendors")){
		    $query .=" LEFT JOIN ottocrat_email_track AS ottocrat_email_trackVendors ON ottocrat_email_trackVendors.crmid = ottocrat_vendor.vendorid";
		}
		if ($queryplanner->requireTable("ottocrat_usersVendors")){
		    $query .=" left join ottocrat_users as ottocrat_usersVendors on ottocrat_usersVendors.id = ottocrat_crmentityVendors.smownerid";
		}
		if ($queryplanner->requireTable("ottocrat_lastModifiedByVendors")){
		    $query .=" left join ottocrat_users as ottocrat_lastModifiedByVendors on ottocrat_lastModifiedByVendors.id = ottocrat_crmentityVendors.modifiedby ";
		}
        if ($queryplanner->requireTable("ottocrat_createdbyVendors")){
			$query .= " left join ottocrat_users as ottocrat_createdbyVendors on ottocrat_createdbyVendors.id = ottocrat_crmentityVendors.smcreatorid ";
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
			"Products" =>array("ottocrat_products"=>array("vendor_id","productid"),"ottocrat_vendor"=>"vendorid"),
			"PurchaseOrder" =>array("ottocrat_purchaseorder"=>array("vendorid","purchaseorderid"),"ottocrat_vendor"=>"vendorid"),
			"Contacts" =>array("ottocrat_vendorcontactrel"=>array("vendorid","contactid"),"ottocrat_vendor"=>"vendorid"),
			"Emails" => array("ottocrat_seactivityrel"=>array("crmid","activityid"),"ottocrat_vendor"=>"vendorid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;
		//Deleting Vendor related PO.
		$po_q = 'SELECT ottocrat_crmentity.crmid FROM ottocrat_crmentity
			INNER JOIN ottocrat_purchaseorder ON ottocrat_crmentity.crmid=ottocrat_purchaseorder.purchaseorderid
			INNER JOIN ottocrat_vendor ON ottocrat_vendor.vendorid=ottocrat_purchaseorder.vendorid
			WHERE ottocrat_crmentity.deleted=0 AND ottocrat_purchaseorder.vendorid=?';
		$po_res = $this->db->pquery($po_q, array($id));
		$po_ids_list = array();
		for($k=0;$k < $this->db->num_rows($po_res);$k++)
		{
			$po_id = $this->db->query_result($po_res,$k,"crmid");
			$po_ids_list[] = $po_id;
			$sql = 'UPDATE ottocrat_crmentity SET deleted = 1 WHERE crmid = ?';
			$this->db->pquery($sql, array($po_id));
		}
		//Backup deleted Vendors related Potentials.
		$params = array($id, RB_RECORD_UPDATED, 'ottocrat_crmentity', 'deleted', 'crmid', implode(",", $po_ids_list));
		$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);

		//Backup Product-Vendor Relation
		$pro_q = 'SELECT productid FROM ottocrat_products WHERE vendor_id=?';
		$pro_res = $this->db->pquery($pro_q, array($id));
		if ($this->db->num_rows($pro_res) > 0) {
			$pro_ids_list = array();
			for($k=0;$k < $this->db->num_rows($pro_res);$k++)
			{
				$pro_ids_list[] = $this->db->query_result($pro_res,$k,"productid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'ottocrat_products', 'vendor_id', 'productid', implode(",", $pro_ids_list));
			$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//Deleting Product-Vendor Relation.
		$pro_q = 'UPDATE ottocrat_products SET vendor_id = 0 WHERE vendor_id = ?';
		$this->db->pquery($pro_q, array($id));

		/*//Backup Contact-Vendor Relaton
		$con_q = 'SELECT contactid FROM ottocrat_vendorcontactrel WHERE vendorid = ?';
		$con_res = $this->db->pquery($con_q, array($id));
		if ($this->db->num_rows($con_res) > 0) {
			for($k=0;$k < $this->db->num_rows($con_res);$k++)
			{
				$con_id = $this->db->query_result($con_res,$k,"contactid");
				$params = array($id, RB_RECORD_DELETED, 'ottocrat_vendorcontactrel', 'vendorid', 'contactid', $con_id);
				$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
			}
		}
		//Deleting Contact-Vendor Relaton
		$vc_sql = 'DELETE FROM ottocrat_vendorcontactrel WHERE vendorid=?';
		$this->db->pquery($vc_sql, array($id));*/

		parent::unlinkDependencies($module, $id);
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Contacts')
				$adb->pquery("insert into ottocrat_vendorcontactrel values (?,?)", array($crmid, $with_crmid));
			elseif($with_module == 'Products')
				$adb->pquery("update ottocrat_products set vendor_id=? where productid=?", array($crmid, $with_crmid));
			else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

    // Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;
        if($return_module == 'Contacts') {
			$sql = 'DELETE FROM ottocrat_vendorcontactrel WHERE vendorid=? AND contactid=?';
			$this->db->pquery($sql, array($id,$return_id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id);
		}
	}

}
?>
