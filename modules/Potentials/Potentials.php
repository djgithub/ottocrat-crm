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
 * $Header: /advent/projects/wesat/ottocrat_crm/sugarcrm/modules/Potentials/Potentials.php,v 1.65 2005/04/28 08:08:27 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

class Potentials extends CRMEntity {
	var $log;
	var $db;

	var $module_name="Potentials";
	var $table_name = "ottocrat_potential";
	var $table_index= 'potentialid';

	var $tab_name = Array('ottocrat_crmentity','ottocrat_potential','ottocrat_potentialscf');
	var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_potential'=>'potentialid','ottocrat_potentialscf'=>'potentialid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_potentialscf', 'potentialid');

	var $column_fields = Array();

	var $sortby_fields = Array('potentialname','amount','closingdate','smownerid','accountname');

	// This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
			'Potential'=>Array('potential'=>'potentialname'),
			'Organization Name'=>Array('potential'=>'related_to'),
			'Contact Name'=>Array('potential'=>'contact_id'),
			'Sales Stage'=>Array('potential'=>'sales_stage'),
			'Amount'=>Array('potential'=>'amount'),
			'Expected Close Date'=>Array('potential'=>'closingdate'),
			'Assigned To'=>Array('crmentity','smownerid')
			);

	var $list_fields_name = Array(
			'Potential'=>'potentialname',
			'Organization Name'=>'related_to',
			'Contact Name'=>'contact_id',
			'Sales Stage'=>'sales_stage',
			'Amount'=>'amount',
			'Expected Close Date'=>'closingdate',
			'Assigned To'=>'assigned_user_id');

	var $list_link_field= 'potentialname';

	var $search_fields = Array(
			'Potential'=>Array('potential'=>'potentialname'),
			'Related To'=>Array('potential'=>'related_to'),
			'Expected Close Date'=>Array('potential'=>'closedate')
			);

	var $search_fields_name = Array(
			'Potential'=>'potentialname',
			'Related To'=>'related_to',
			'Expected Close Date'=>'closingdate'
			);

	var $required_fields =  array();

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to ottocrat_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'potentialname');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'potentialname';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'potentialname';

	//var $groupTable = Array('ottocrat_potentialgrouprelation','potentialid');
	function Potentials() {
		$this->log = LoggerManager::getLogger('potential');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Potentials');
	}

	function save_module($module)
	{
	}

	/** Function to create list query
	* @param reference variable - where condition is passed when the query is executed
	* Returns Query.
	*/
	function create_list_query($order_by, $where)
	{
		global $log,$current_user;
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
	        require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
        	$tab_id = getTabid("Potentials");
		$log->debug("Entering create_list_query(".$order_by.",". $where.") method ...");
		// Determine if the ottocrat_account name is present in the where clause.
		$account_required = preg_match("/accounts\.name/", $where);

		if($account_required)
		{
			$query = "SELECT ottocrat_potential.potentialid,  ottocrat_potential.potentialname, ottocrat_potential.dateclosed FROM ottocrat_potential, ottocrat_account ";
			$where_auto = "account.accountid = ottocrat_potential.related_to AND ottocrat_crmentity.deleted=0 ";
		}
		else
		{
			$query = 'SELECT ottocrat_potential.potentialid, ottocrat_potential.potentialname, ottocrat_crmentity.smcreatorid, ottocrat_potential.closingdate FROM ottocrat_potential inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_potential.potentialid LEFT JOIN ottocrat_groups on ottocrat_groups.groupid = ottocrat_crmentity.smownerid left join ottocrat_users on ottocrat_users.id = ottocrat_crmentity.smownerid ';
			$where_auto = ' AND ottocrat_crmentity.deleted=0';
		}

		$query .= $this->getNonAdminAccessControlQuery('Potentials',$current_user);
		if($where != "")
			$query .= " where $where ".$where_auto;
		else
			$query .= " where ".$where_auto;
		if($order_by != "")
			$query .= " ORDER BY $order_by";

		$log->debug("Exiting create_list_query method ...");
		return $query;
	}

	/** Function to export the Opportunities records in CSV Format
	* @param reference variable - order by is passed when the query is executed
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Potentials Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(". $where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Potentials", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT $fields_list,case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name
				FROM ottocrat_potential
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_potential.potentialid
				LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid=ottocrat_users.id
				LEFT JOIN ottocrat_account on ottocrat_potential.related_to=ottocrat_account.accountid
				LEFT JOIN ottocrat_contactdetails on ottocrat_potential.contact_id=ottocrat_contactdetails.contactid
				LEFT JOIN ottocrat_potentialscf on ottocrat_potentialscf.potentialid=ottocrat_potential.potentialid
                LEFT JOIN ottocrat_groups
        	        ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_campaign
					ON ottocrat_campaign.campaignid = ottocrat_potential.campaignid";

		$query .= $this->getNonAdminAccessControlQuery('Potentials',$current_user);
		$where_auto = "  ottocrat_crmentity.deleted = 0 ";

                if($where != "")
                   $query .= "  WHERE ($where) AND ".$where_auto;
                else
                   $query .= "  WHERE ".$where_auto;

		$log->debug("Exiting create_export_query method ...");
		return $query;

	}



	/** Returns a list of the associated contacts
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
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

		$accountid = $this->column_fields['related_to'];
		$search_string = "&fromPotential=true&acc_id=$accountid";

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab$search_string")."','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = 'select case when (ottocrat_users.user_name not like "") then '.$userNameSql.' else ottocrat_groups.groupname end as user_name,
					ottocrat_contactdetails.accountid,ottocrat_potential.potentialid, ottocrat_potential.potentialname, ottocrat_contactdetails.contactid,
					ottocrat_contactdetails.lastname, ottocrat_contactdetails.firstname, ottocrat_contactdetails.title, ottocrat_contactdetails.department,
					ottocrat_contactdetails.email, ottocrat_contactdetails.phone, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
					ottocrat_crmentity.modifiedtime , ottocrat_account.accountname from ottocrat_potential
					left join ottocrat_contpotentialrel on ottocrat_contpotentialrel.potentialid = ottocrat_potential.potentialid
					inner join ottocrat_contactdetails on ((ottocrat_contactdetails.contactid = ottocrat_contpotentialrel.contactid) or (ottocrat_contactdetails.contactid = ottocrat_potential.contact_id))
					INNER JOIN ottocrat_contactaddress ON ottocrat_contactdetails.contactid = ottocrat_contactaddress.contactaddressid
					INNER JOIN ottocrat_contactsubdetails ON ottocrat_contactdetails.contactid = ottocrat_contactsubdetails.contactsubscriptionid
					INNER JOIN ottocrat_customerdetails ON ottocrat_contactdetails.contactid = ottocrat_customerdetails.customerid
					INNER JOIN ottocrat_contactscf ON ottocrat_contactdetails.contactid = ottocrat_contactscf.contactid
					inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_contactdetails.contactid
					left join ottocrat_account on ottocrat_account.accountid = ottocrat_contactdetails.accountid
					left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
					left join ottocrat_users on ottocrat_crmentity.smownerid=ottocrat_users.id
					where ottocrat_potential.potentialid = '.$id.' and ottocrat_crmentity.deleted=0';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/** Returns a list of the associated calls
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
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
				if(getFieldVisibilityPermission('Events',$current_user->id,'parent_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Events\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_EVENT', $related_module) ."'>";
				}
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_activity.activityid as 'tmp_activity_id',ottocrat_activity.*,ottocrat_seactivityrel.crmid as parent_id, ottocrat_contactdetails.lastname,ottocrat_contactdetails.firstname,
					ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_crmentity.modifiedtime,
					case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
					ottocrat_recurringevents.recurringtype from ottocrat_activity
					inner join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=ottocrat_activity.activityid
					inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
					left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid
					left join ottocrat_contactdetails on ottocrat_contactdetails.contactid = ottocrat_cntactivityrel.contactid
					inner join ottocrat_potential on ottocrat_potential.potentialid=ottocrat_seactivityrel.crmid
					left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
					left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
					left outer join ottocrat_recurringevents on ottocrat_recurringevents.activityid=ottocrat_activity.activityid
					where ottocrat_seactivityrel.crmid=".$id." and ottocrat_crmentity.deleted=0
					and ((ottocrat_activity.activitytype='Task' and ottocrat_activity.status not in ('Completed','Deferred'))
					or (ottocrat_activity.activitytype NOT in ('Emails','Task') and  ottocrat_activity.eventstatus not in ('','Held'))) ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	 /**
	 * Function to get Contact related Products
	 * @param  integer   $id  - contactid
	 * returns related Products record in array format
	 */
	function get_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_products(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
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

		$query = "SELECT ottocrat_products.productid, ottocrat_products.productname, ottocrat_products.productcode,
				ottocrat_products.commissionrate, ottocrat_products.qty_per_unit, ottocrat_products.unit_price,
				ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid
				FROM ottocrat_products
				INNER JOIN ottocrat_seproductsrel ON ottocrat_products.productid = ottocrat_seproductsrel.productid and ottocrat_seproductsrel.setype = 'Potentials'
				INNER JOIN ottocrat_productcf
				ON ottocrat_products.productid = ottocrat_productcf.productid
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_products.productid
				INNER JOIN ottocrat_potential ON ottocrat_potential.potentialid = ottocrat_seproductsrel.crmid
				LEFT JOIN ottocrat_users
					ON ottocrat_users.id=ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_groups
					ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_potential.potentialid = $id";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/**	Function used to get the Sales Stage history of the Potential
	 *	@param $id - potentialid
	 *	return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are array which contains all the column values of an row
	 */
	function get_stage_history($id)
	{
		global $log;
		$log->debug("Entering get_stage_history(".$id.") method ...");

		global $adb;
		global $mod_strings;
		global $app_strings;

		$query = 'select ottocrat_potstagehistory.*, ottocrat_potential.potentialname from ottocrat_potstagehistory inner join ottocrat_potential on ottocrat_potential.potentialid = ottocrat_potstagehistory.potentialid inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_potential.potentialid where ottocrat_crmentity.deleted = 0 and ottocrat_potential.potentialid = ?';
		$result=$adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_SALES_STAGE'];
		$header[] = $app_strings['LBL_PROBABILITY'];
		$header[] = $app_strings['LBL_CLOSE_DATE'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Sales Stage, Expected Close Dates are mandatory fields. So no need to do security check to these fields.
		global $current_user;

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$amount_access = (getFieldVisibilityPermission('Potentials', $current_user->id, 'amount') != '0')? 1 : 0;
		$probability_access = (getFieldVisibilityPermission('Potentials', $current_user->id, 'probability') != '0')? 1 : 0;
		$picklistarray = getAccessPickListValues('Potentials');

		$potential_stage_array = $picklistarray['sales_stage'];
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = 'Not Accessible';

		while($row = $adb->fetch_array($result))
		{
			$entries = Array();

			$entries[] = ($amount_access != 1)? $row['amount'] : 0;
			$entries[] = (in_array($row['stage'], $potential_stage_array))? $row['stage']: $error_msg;
			$entries[] = ($probability_access != 1) ? $row['probability'] : 0;
			$entries[] = DateTimeField::convertToUserFormat($row['closedate']);
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDate();

			$entries_list[] = $entries;
		}

		$return_data = Array('header'=>$header,'entries'=>$entries_list);

	 	$log->debug("Exiting get_stage_history method ...");

		return $return_data;
	}

	/**
	* Function to get Potential related Task & Event which have activity type Held, Completed or Deferred.
	* @param  integer   $id
	* returns related Task or Event record in array format
	*/
	function get_history($id)
	{
			global $log;
			$log->debug("Entering get_history(".$id.") method ...");
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
			$query = "SELECT ottocrat_activity.activityid, ottocrat_activity.subject, ottocrat_activity.status,
		ottocrat_activity.eventstatus, ottocrat_activity.activitytype,ottocrat_activity.date_start,
		ottocrat_activity.due_date, ottocrat_activity.time_start,ottocrat_activity.time_end,
		ottocrat_crmentity.modifiedtime, ottocrat_crmentity.createdtime,
		ottocrat_crmentity.description,case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name
				from ottocrat_activity
				inner join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=ottocrat_activity.activityid
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
				left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
				where (ottocrat_activity.activitytype != 'Emails')
				and (ottocrat_activity.status = 'Completed' or ottocrat_activity.status = 'Deferred' or (ottocrat_activity.eventstatus = 'Held' and ottocrat_activity.eventstatus != ''))
				and ottocrat_seactivityrel.crmid=".$id."
                                and ottocrat_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('Potentials',$query,$id);
	}


	  /**
	  * Function to get Potential related Quotes
	  * @param  integer   $id  - potentialid
	  * returns related Quotes record in array format
	  */
	function get_quotes($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_quotes(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'potential_id', 'readwrite') == '0') {
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
		$query = "select case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
					ottocrat_account.accountname, ottocrat_crmentity.*, ottocrat_quotes.*, ottocrat_potential.potentialname from ottocrat_quotes
					inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_quotes.quoteid
					left outer join ottocrat_potential on ottocrat_potential.potentialid=ottocrat_quotes.potentialid
					left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
                    LEFT JOIN ottocrat_quotescf ON ottocrat_quotescf.quoteid = ottocrat_quotes.quoteid
					LEFT JOIN ottocrat_quotesbillads ON ottocrat_quotesbillads.quotebilladdressid = ottocrat_quotes.quoteid
					LEFT JOIN ottocrat_quotesshipads ON ottocrat_quotesshipads.quoteshipaddressid = ottocrat_quotes.quoteid
					left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
					LEFT join ottocrat_account on ottocrat_account.accountid=ottocrat_quotes.accountid
					where ottocrat_crmentity.deleted=0 and ottocrat_potential.potentialid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_quotes method ...");
		return $return_value;
	}

	/**
	 * Function to get Potential related SalesOrder
 	 * @param  integer   $id  - potentialid
	 * returns related SalesOrder record in array format
	 */
	function get_salesorder($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_salesorder(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'potential_id', 'readwrite') == '0') {
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
		$query = "select ottocrat_crmentity.*, ottocrat_salesorder.*, ottocrat_quotes.subject as quotename
			, ottocrat_account.accountname, ottocrat_potential.potentialname,case when
			(ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname
			end as user_name from ottocrat_salesorder
			inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_salesorder.salesorderid
			left outer join ottocrat_quotes on ottocrat_quotes.quoteid=ottocrat_salesorder.quoteid
			left outer join ottocrat_account on ottocrat_account.accountid=ottocrat_salesorder.accountid
			left outer join ottocrat_potential on ottocrat_potential.potentialid=ottocrat_salesorder.potentialid
			left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
            LEFT JOIN ottocrat_salesordercf ON ottocrat_salesordercf.salesorderid = ottocrat_salesorder.salesorderid
            LEFT JOIN ottocrat_invoice_recurring_info ON ottocrat_invoice_recurring_info.start_period = ottocrat_salesorder.salesorderid
			LEFT JOIN ottocrat_sobillads ON ottocrat_sobillads.sobilladdressid = ottocrat_salesorder.salesorderid
			LEFT JOIN ottocrat_soshipads ON ottocrat_soshipads.soshipaddressid = ottocrat_salesorder.salesorderid
			left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
			 where ottocrat_crmentity.deleted=0 and ottocrat_potential.potentialid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_salesorder method ...");
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

		$rel_table_arr = Array("Activities"=>"ottocrat_seactivityrel","Contacts"=>"ottocrat_contpotentialrel","Products"=>"ottocrat_seproductsrel",
						"Attachments"=>"ottocrat_seattachmentsrel","Quotes"=>"ottocrat_quotes","SalesOrder"=>"ottocrat_salesorder",
						"Documents"=>"ottocrat_senotesrel");

		$tbl_field_arr = Array("ottocrat_seactivityrel"=>"activityid","ottocrat_contpotentialrel"=>"contactid","ottocrat_seproductsrel"=>"productid",
						"ottocrat_seattachmentsrel"=>"attachmentsid","ottocrat_quotes"=>"quoteid","ottocrat_salesorder"=>"salesorderid",
						"ottocrat_senotesrel"=>"notesid");

		$entity_tbl_field_arr = Array("ottocrat_seactivityrel"=>"crmid","ottocrat_contpotentialrel"=>"potentialid","ottocrat_seproductsrel"=>"crmid",
						"ottocrat_seattachmentsrel"=>"crmid","ottocrat_quotes"=>"potentialid","ottocrat_salesorder"=>"potentialid",
						"ottocrat_senotesrel"=>"crmid");

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

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryplanner){
		$matrix = $queryplanner->newDependencyMatrix();
		$matrix->setDependency('ottocrat_crmentityPotentials',array('ottocrat_groupsPotentials','ottocrat_usersPotentials','ottocrat_lastModifiedByPotentials'));
		$matrix->setDependency('ottocrat_potential', array('ottocrat_crmentityPotentials','ottocrat_accountPotentials',
											'ottocrat_contactdetailsPotentials','ottocrat_campaignPotentials','ottocrat_potentialscf'));


		if (!$queryplanner->requireTable("ottocrat_potential",$matrix)){
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"ottocrat_potential","potentialid", $queryplanner);

		if ($queryplanner->requireTable("ottocrat_crmentityPotentials",$matrix)){
			$query .= " left join ottocrat_crmentity as ottocrat_crmentityPotentials on ottocrat_crmentityPotentials.crmid=ottocrat_potential.potentialid and ottocrat_crmentityPotentials.deleted=0";
		}
		if ($queryplanner->requireTable("ottocrat_accountPotentials")){
			$query .= " left join ottocrat_account as ottocrat_accountPotentials on ottocrat_potential.related_to = ottocrat_accountPotentials.accountid";
		}
		if ($queryplanner->requireTable("ottocrat_contactdetailsPotentials")){
			$query .= " left join ottocrat_contactdetails as ottocrat_contactdetailsPotentials on ottocrat_potential.contact_id = ottocrat_contactdetailsPotentials.contactid";
		}
		if ($queryplanner->requireTable("ottocrat_potentialscf")){
			$query .= " left join ottocrat_potentialscf on ottocrat_potentialscf.potentialid = ottocrat_potential.potentialid";
		}
		if ($queryplanner->requireTable("ottocrat_groupsPotentials")){
			$query .= " left join ottocrat_groups ottocrat_groupsPotentials on ottocrat_groupsPotentials.groupid = ottocrat_crmentityPotentials.smownerid";
		}
		if ($queryplanner->requireTable("ottocrat_usersPotentials")){
			$query .= " left join ottocrat_users as ottocrat_usersPotentials on ottocrat_usersPotentials.id = ottocrat_crmentityPotentials.smownerid";
		}
		if ($queryplanner->requireTable("ottocrat_campaignPotentials")){
			$query .= " left join ottocrat_campaign as ottocrat_campaignPotentials on ottocrat_potential.campaignid = ottocrat_campaignPotentials.campaignid";
		}
		if ($queryplanner->requireTable("ottocrat_lastModifiedByPotentials")){
			$query .= " left join ottocrat_users as ottocrat_lastModifiedByPotentials on ottocrat_lastModifiedByPotentials.id = ottocrat_crmentityPotentials.modifiedby ";
		}
        if ($queryplanner->requireTable("ottocrat_createdbyPotentials")){
			$query .= " left join ottocrat_users as ottocrat_createdbyPotentials on ottocrat_createdbyPotentials.id = ottocrat_crmentityPotentials.smcreatorid ";
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
			"Calendar" => array("ottocrat_seactivityrel"=>array("crmid","activityid"),"ottocrat_potential"=>"potentialid"),
			"Products" => array("ottocrat_seproductsrel"=>array("crmid","productid"),"ottocrat_potential"=>"potentialid"),
			"Quotes" => array("ottocrat_quotes"=>array("potentialid","quoteid"),"ottocrat_potential"=>"potentialid"),
			"SalesOrder" => array("ottocrat_salesorder"=>array("potentialid","salesorderid"),"ottocrat_potential"=>"potentialid"),
			"Documents" => array("ottocrat_senotesrel"=>array("crmid","notesid"),"ottocrat_potential"=>"potentialid"),
			"Accounts" => array("ottocrat_potential"=>array("potentialid","related_to")),
			"Contacts" => array("ottocrat_potential"=>array("potentialid","contact_id")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;
		/*//Backup Activity-Potentials Relation
		$act_q = "select activityid from ottocrat_seactivityrel where crmid = ?";
		$act_res = $this->db->pquery($act_q, array($id));
		if ($this->db->num_rows($act_res) > 0) {
			for($k=0;$k < $this->db->num_rows($act_res);$k++)
			{
				$act_id = $this->db->query_result($act_res,$k,"activityid");
				$params = array($id, RB_RECORD_DELETED, 'ottocrat_seactivityrel', 'crmid', 'activityid', $act_id);
				$this->db->pquery("insert into ottocrat_relatedlists_rb values (?,?,?,?,?,?)", $params);
			}
		}
		$sql = 'delete from ottocrat_seactivityrel where crmid = ?';
		$this->db->pquery($sql, array($id));*/

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Accounts') {
			$this->trash($this->module_name, $id);
		} elseif($return_module == 'Campaigns') {
			$sql = 'UPDATE ottocrat_potential SET campaignid = ? WHERE potentialid = ?';
			$this->db->pquery($sql, array(null, $id));
		} elseif($return_module == 'Products') {
			$sql = 'DELETE FROM ottocrat_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Contacts') {
			$sql = 'DELETE FROM ottocrat_contpotentialrel WHERE potentialid=? AND contactid=?';
			$this->db->pquery($sql, array($id, $return_id));

			//If contact related to potential through edit of record,that entry will be present in
			//ottocrat_potential contact_id column,which should be set to zero
			$sql = 'UPDATE ottocrat_potential SET contact_id = ? WHERE potentialid=? AND contact_id=?';
			$this->db->pquery($sql, array(0,$id, $return_id));

			// Potential directly linked with Contact (not through Account - ottocrat_contpotentialrel)
			$directRelCheck = $this->db->pquery('SELECT related_to FROM ottocrat_potential WHERE potentialid=? AND contact_id=?', array($id, $return_id));
			if($this->db->num_rows($directRelCheck)) {
				$this->trash($this->module_name, $id);
			}

		} else {
			$sql = 'DELETE FROM ottocrat_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Contacts') { //When we select contact from potential related list
				$sql = "insert into ottocrat_contpotentialrel values (?,?)";
				$adb->pquery($sql, array($with_crmid, $crmid));

			} elseif($with_module == 'Products') {//when we select product from potential related list
				$sql = "insert into ottocrat_seproductsrel values (?,?,?)";
				$adb->pquery($sql, array($crmid, $with_crmid,'Potentials'));

			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

}
?>