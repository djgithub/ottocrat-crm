<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
class Leads extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "ottocrat_leaddetails";
	var $table_index= 'leadid';

	var $tab_name = Array('ottocrat_crmentity','ottocrat_leaddetails','ottocrat_leadsubdetails','ottocrat_leadaddress','ottocrat_leadscf');
	var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_leaddetails'=>'leadid','ottocrat_leadsubdetails'=>'leadsubscriptionid','ottocrat_leadaddress'=>'leadaddressid','ottocrat_leadscf'=>'leadid');

	var $entity_table = "ottocrat_crmentity";

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_leadscf', 'leadid');

	//construct this from database;
	var $column_fields = Array();
	var $sortby_fields = Array('lastname','firstname','email','phone','company','smownerid','website');

	// This is used to retrieve related ottocrat_fields from form posts.
	var $additional_column_fields = Array('smcreatorid', 'smownerid', 'contactid','potentialid' ,'crmid');

	// This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
		'First Name'=>Array('leaddetails'=>'firstname'),
		'Last Name'=>Array('leaddetails'=>'lastname'),
		'Company'=>Array('leaddetails'=>'company'),
		'Phone'=>Array('leadaddress'=>'phone'),
		'Website'=>Array('leadsubdetails'=>'website'),
		'Email'=>Array('leaddetails'=>'email'),
		'Assigned To'=>Array('crmentity'=>'smownerid')
	);
	var $list_fields_name = Array(
		'First Name'=>'firstname',
		'Last Name'=>'lastname',
		'Company'=>'company',
		'Phone'=>'phone',
		'Website'=>'website',
		'Email'=>'email',
		'Assigned To'=>'assigned_user_id'
	);
	var $list_link_field= 'lastname';

	var $search_fields = Array(
		'Name'=>Array('leaddetails'=>'lastname'),
		'Company'=>Array('leaddetails'=>'company')
	);
	var $search_fields_name = Array(
		'Name'=>'lastname',
		'Company'=>'company'
	);

	var $required_fields =  array();

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to ottocrat_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'lastname', 'createdtime' ,'modifiedtime');

	//Default Fields for Email Templates -- Pavani
	var $emailTemplate_defaultFields = array('firstname','lastname','leadsource','leadstatus','rating','industry','secondaryemail','email','annualrevenue','designation','salutation');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'lastname';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'lastname';

	//var $groupTable = Array('ottocrat_leadgrouprelation','leadid');

	function Leads()	{
		$this->log = LoggerManager::getLogger('lead');
		$this->log->debug("Entering Leads() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Leads');
		$this->log->debug("Exiting Lead method ...");
	}

	/** Function to handle module specific operations when saving a entity
	*/
	function save_module($module)
	{
	}

	// Mike Crowe Mod --------------------------------------------------------Default ordering for us

	/** Function to export the lead records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Leads Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Leads", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT $fields_list,case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name
	      			FROM ".$this->entity_table."
				INNER JOIN ottocrat_leaddetails
					ON ottocrat_crmentity.crmid=ottocrat_leaddetails.leadid
				LEFT JOIN ottocrat_leadsubdetails
					ON ottocrat_leaddetails.leadid = ottocrat_leadsubdetails.leadsubscriptionid
				LEFT JOIN ottocrat_leadaddress
					ON ottocrat_leaddetails.leadid=ottocrat_leadaddress.leadaddressid
				LEFT JOIN ottocrat_leadscf
					ON ottocrat_leadscf.leadid=ottocrat_leaddetails.leadid
	                        LEFT JOIN ottocrat_groups
                        	        ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_users
					ON ottocrat_crmentity.smownerid = ottocrat_users.id and ottocrat_users.status='Active'
				";

		$query .= $this->getNonAdminAccessControlQuery('Leads',$current_user);
		$where_auto = " ottocrat_crmentity.deleted=0 AND ottocrat_leaddetails.converted =0";

		if($where != "")
			$query .= " where ($where) AND ".$where_auto;
		else
			$query .= " where ".$where_auto;

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}



	/** Returns a list of the associated tasks
 	 * @param  integer   $id      - leadid
 	 * returns related Task or Event record in array format
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
		$query = "SELECT ottocrat_activity.*,ottocrat_seactivityrel.crmid as parent_id, ottocrat_contactdetails.lastname,
			ottocrat_contactdetails.contactid, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
			ottocrat_crmentity.modifiedtime,case when (ottocrat_users.user_name not like '') then
		$userNameSql else ottocrat_groups.groupname end as user_name,
		ottocrat_recurringevents.recurringtype
		from ottocrat_activity inner join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=
		ottocrat_activity.activityid inner join ottocrat_crmentity on ottocrat_crmentity.crmid=
		ottocrat_activity.activityid left join ottocrat_cntactivityrel on
		ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid left join
		ottocrat_contactdetails on ottocrat_contactdetails.contactid = ottocrat_cntactivityrel.contactid
		left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
		left outer join ottocrat_recurringevents on ottocrat_recurringevents.activityid=
		ottocrat_activity.activityid left join ottocrat_groups on ottocrat_groups.groupid=
		ottocrat_crmentity.smownerid where ottocrat_seactivityrel.crmid=".$id." and
			ottocrat_crmentity.deleted = 0 and ((ottocrat_activity.activitytype='Task' and
			ottocrat_activity.status not in ('Completed','Deferred')) or
			(ottocrat_activity.activitytype NOT in ('Emails','Task') and
			ottocrat_activity.eventstatus not in ('','Held'))) ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/** Returns a list of the associated Campaigns
	  * @param $id -- campaign id :: Type Integer
	  * @returns list of campaigns in array format
	  */
	function get_campaigns($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_campaigns(".$id.") method ...");
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

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab")."','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name ,
				ottocrat_campaign.campaignid, ottocrat_campaign.campaignname, ottocrat_campaign.campaigntype, ottocrat_campaign.campaignstatus,
				ottocrat_campaign.expectedrevenue, ottocrat_campaign.closingdate, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
				ottocrat_crmentity.modifiedtime from ottocrat_campaign
				inner join ottocrat_campaignleadrel on ottocrat_campaignleadrel.campaignid=ottocrat_campaign.campaignid
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_campaign.campaignid
				inner join ottocrat_campaignscf ON ottocrat_campaignscf.campaignid = ottocrat_campaign.campaignid
				left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				left join ottocrat_users on ottocrat_users.id = ottocrat_crmentity.smownerid
				where ottocrat_campaignleadrel.leadid=".$id." and ottocrat_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_campaigns method ...");
		return $return_value;
	}


		/** Returns a list of the associated emails
	 	 * @param  integer   $id      - leadid
	 	 * returns related emails record in array format
		*/
	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_emails(".$id.") method ...");
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

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab")."','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'></td>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query ="select case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name," .
				" ottocrat_activity.activityid, ottocrat_activity.subject, ottocrat_activity.semodule, ottocrat_activity.activitytype," .
				" ottocrat_activity.date_start, ottocrat_activity.time_start, ottocrat_activity.status, ottocrat_activity.priority, ottocrat_crmentity.crmid," .
				" ottocrat_crmentity.smownerid,ottocrat_crmentity.modifiedtime, ottocrat_users.user_name, ottocrat_seactivityrel.crmid as parent_id " .
				" from ottocrat_activity" .
				" inner join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=ottocrat_activity.activityid" .
				" inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid" .
				" left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid" .
				" left join ottocrat_users on  ottocrat_users.id=ottocrat_crmentity.smownerid" .
				" where ottocrat_activity.activitytype='Emails' and ottocrat_crmentity.deleted=0 and ottocrat_seactivityrel.crmid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}

	/**
	 * Function to get Lead related Task & Event which have activity type Held, Completed or Deferred.
	 * @param  integer   $id      - leadid
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
			ottocrat_activity.due_date,ottocrat_activity.time_start,ottocrat_activity.time_end,
			ottocrat_crmentity.modifiedtime,ottocrat_crmentity.createdtime,
			ottocrat_crmentity.description, $userNameSql as user_name,ottocrat_groups.groupname
				from ottocrat_activity
				inner join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=ottocrat_activity.activityid
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
				left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				left join ottocrat_users on ottocrat_crmentity.smownerid= ottocrat_users.id
				where (ottocrat_activity.activitytype != 'Emails')
				and (ottocrat_activity.status = 'Completed' or ottocrat_activity.status = 'Deferred' or (ottocrat_activity.eventstatus = 'Held' and ottocrat_activity.eventstatus != ''))
				and ottocrat_seactivityrel.crmid=".$id."
	                        and ottocrat_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('Leads',$query,$id);
	}

	/**
	* Function to get lead related Products
	* @param  integer   $id      - leadid
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
				INNER JOIN ottocrat_seproductsrel ON ottocrat_products.productid = ottocrat_seproductsrel.productid  and ottocrat_seproductsrel.setype = 'Leads'
			 	INNER JOIN ottocrat_productcf
					ON ottocrat_products.productid = ottocrat_productcf.productid
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_products.productid
				INNER JOIN ottocrat_leaddetails ON ottocrat_leaddetails.leadid = ottocrat_seproductsrel.crmid
				LEFT JOIN ottocrat_users
					ON ottocrat_users.id=ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_groups
					ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			   WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_leaddetails.leadid = $id";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/** Function to get the Columnnames of the Leads Record
	* Used By ottocratCRM Word Plugin
	* Returns the Merge Fields for Word Plugin
	*/
	function getColumnNames_Lead()
	{
		global $log,$current_user;
		$log->debug("Entering getColumnNames_Lead() method ...");
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
		{
			$sql1 = "select fieldlabel from ottocrat_field where tabid=7 and ottocrat_field.presence in (0,2)";
			$params1 = array();
		}else
		{
			$profileList = getCurrentUserProfileList();
			$sql1 = "select ottocrat_field.fieldid,fieldlabel from ottocrat_field inner join ottocrat_profile2field on ottocrat_profile2field.fieldid=ottocrat_field.fieldid inner join ottocrat_def_org_field on ottocrat_def_org_field.fieldid=ottocrat_field.fieldid where ottocrat_field.tabid=7 and ottocrat_field.displaytype in (1,2,3,4) and ottocrat_profile2field.visible=0 and ottocrat_def_org_field.visible=0 and ottocrat_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and ottocrat_profile2field.profileid in (". generateQuestionMarks($profileList) .")  group by fieldid";
				array_push($params1, $profileList);
			}
		}
		$result = $this->db->pquery($sql1, $params1);
		$numRows = $this->db->num_rows($result);
		for($i=0; $i < $numRows;$i++)
		{
	   	$custom_fields[$i] = $this->db->query_result($result,$i,"fieldlabel");
	   	$custom_fields[$i] = preg_replace("/\s+/","",$custom_fields[$i]);
	   	$custom_fields[$i] = strtoupper($custom_fields[$i]);
		}
		$mergeflds = $custom_fields;
		$log->debug("Exiting getColumnNames_Lead method ...");
		return $mergeflds;
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

		$rel_table_arr = Array("Activities"=>"ottocrat_seactivityrel","Documents"=>"ottocrat_senotesrel","Attachments"=>"ottocrat_seattachmentsrel",
					"Products"=>"ottocrat_seproductsrel","Campaigns"=>"ottocrat_campaignleadrel");

		$tbl_field_arr = Array("ottocrat_seactivityrel"=>"activityid","ottocrat_senotesrel"=>"notesid","ottocrat_seattachmentsrel"=>"attachmentsid",
					"ottocrat_seproductsrel"=>"productid","ottocrat_campaignleadrel"=>"campaignid");

		$entity_tbl_field_arr = Array("ottocrat_seactivityrel"=>"crmid","ottocrat_senotesrel"=>"crmid","ottocrat_seattachmentsrel"=>"crmid",
					"ottocrat_seproductsrel"=>"crmid","ottocrat_campaignleadrel"=>"leadid");

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
	function generateReportsSecQuery($module,$secmodule, $queryPlanner) {
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('ottocrat_leaddetails',array('ottocrat_crmentityLeads', 'ottocrat_leadaddress','ottocrat_leadsubdetails','ottocrat_leadscf','ottocrat_email_trackLeads'));
		$matrix->setDependency('ottocrat_crmentityLeads',array('ottocrat_groupsLeads','ottocrat_usersLeads','ottocrat_lastModifiedByLeads'));

		// TODO Support query planner
		if (!$queryPlanner->requireTable("ottocrat_leaddetails",$matrix)){
			return '';
		}
		$query = $this->getRelationQuery($module,$secmodule,"ottocrat_leaddetails","leadid", $queryPlanner);
		if ($queryPlanner->requireTable("ottocrat_crmentityLeads",$matrix)){
		    $query .= " left join ottocrat_crmentity as ottocrat_crmentityLeads on ottocrat_crmentityLeads.crmid = ottocrat_leaddetails.leadid and ottocrat_crmentityLeads.deleted=0";
		}
		if ($queryPlanner->requireTable("ottocrat_leadaddress")){
		    $query .= " left join ottocrat_leadaddress on ottocrat_leaddetails.leadid = ottocrat_leadaddress.leadaddressid";
		}
		if ($queryPlanner->requireTable("ottocrat_leadsubdetails")){
		    $query .= " left join ottocrat_leadsubdetails on ottocrat_leadsubdetails.leadsubscriptionid = ottocrat_leaddetails.leadid";
		}
		if ($queryPlanner->requireTable("ottocrat_leadscf")){
		    $query .= " left join ottocrat_leadscf on ottocrat_leadscf.leadid = ottocrat_leaddetails.leadid";
		}
		if ($queryPlanner->requireTable("ottocrat_email_trackLeads")){
		    $query .= " LEFT JOIN ottocrat_email_track AS ottocrat_email_trackLeads ON ottocrat_email_trackLeads.crmid = ottocrat_leaddetails.leadid";
		}
		if ($queryPlanner->requireTable("ottocrat_groupsLeads")){
		    $query .= " left join ottocrat_groups as ottocrat_groupsLeads on ottocrat_groupsLeads.groupid = ottocrat_crmentityLeads.smownerid";
		}
		if ($queryPlanner->requireTable("ottocrat_usersLeads")){
		    $query .= " left join ottocrat_users as ottocrat_usersLeads on ottocrat_usersLeads.id = ottocrat_crmentityLeads.smownerid";
		}
		if ($queryPlanner->requireTable("ottocrat_lastModifiedByLeads")){
		    $query .= " left join ottocrat_users as ottocrat_lastModifiedByLeads on ottocrat_lastModifiedByLeads.id = ottocrat_crmentityLeads.modifiedby ";
		}
        if ($queryPlanner->requireTable("ottocrat_createdbyLeads")){
			$query .= " left join ottocrat_users as ottocrat_createdbyLeads on ottocrat_createdbyLeads.id = ottocrat_crmentityLeads.smcreatorid ";
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
			"Calendar" => array("ottocrat_seactivityrel"=>array("crmid","activityid"),"ottocrat_leaddetails"=>"leadid"),
			"Products" => array("ottocrat_seproductsrel"=>array("crmid","productid"),"ottocrat_leaddetails"=>"leadid"),
			"Campaigns" => array("ottocrat_campaignleadrel"=>array("leadid","campaignid"),"ottocrat_leaddetails"=>"leadid"),
			"Documents" => array("ottocrat_senotesrel"=>array("crmid","notesid"),"ottocrat_leaddetails"=>"leadid"),
			"Services" => array("ottocrat_crmentityrel"=>array("crmid","relcrmid"),"ottocrat_leaddetails"=>"leadid"),
			"Emails" => array("ottocrat_seactivityrel"=>array("crmid","activityid"),"ottocrat_leaddetails"=>"leadid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Campaigns') {
			$sql = 'DELETE FROM ottocrat_campaignleadrel WHERE leadid=? AND campaignid=?';
			$this->db->pquery($sql, array($id, $return_id));
		}
		elseif($return_module == 'Products') {
			$sql = 'DELETE FROM ottocrat_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			$sql = 'DELETE FROM ottocrat_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	function getListButtons($app_strings,$mod_strings = false) {
		$list_buttons = Array();

		if(isPermitted('Leads','Delete','') == 'yes') {
			$list_buttons['del'] =	$app_strings[LBL_MASS_DELETE];
		}
		if(isPermitted('Leads','EditView','') == 'yes') {
			$list_buttons['mass_edit'] = $app_strings[LBL_MASS_EDIT];
			$list_buttons['c_owner'] = $app_strings[LBL_CHANGE_OWNER];
		}
		if(isPermitted('Emails','EditView','') == 'yes')
			$list_buttons['s_mail'] = $app_strings[LBL_SEND_MAIL_BUTTON];

		// end of mailer export
		return $list_buttons;
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Products')
				$adb->pquery("insert into ottocrat_seproductsrel values (?,?,?)", array($crmid, $with_crmid, $module));
			elseif($with_module == 'Campaigns')
				$adb->pquery("insert into  ottocrat_campaignleadrel values(?,?,1)", array($with_crmid, $crmid));
			else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

	function getQueryForDuplicates($module, $tableColumns, $selectedColumns = '', $ignoreEmpty = false) {
		if(is_array($tableColumns)) {
			$tableColumnsString = implode(',', $tableColumns);
		}
        $selectClause = "SELECT " . $this->table_name . "." . $this->table_index . " AS recordid," . $tableColumnsString;

        // Select Custom Field Table Columns if present
        if (isset($this->customFieldTable))
            $query .= ", " . $this->customFieldTable[0] . ".* ";

        $fromClause = " FROM $this->table_name";

        $fromClause .= " INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = $this->table_name.$this->table_index";

		if($this->tab_name) {
			foreach($this->tab_name as $tableName) {
				if($tableName != 'ottocrat_crmentity' && $tableName != $this->table_name) {
					if($this->tab_name_index[$tableName]) {
						$fromClause .= " INNER JOIN " . $tableName . " ON " . $tableName . '.' . $this->tab_name_index[$tableName] .
							" = $this->table_name.$this->table_index";
					}
				}
			}
		}
        $fromClause .= " LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
						LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";

        $whereClause = " WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_leaddetails.converted=0 ";
        $whereClause .= $this->getListViewSecurityParameter($module);

		if($ignoreEmpty) {
			foreach($tableColumns as $tableColumn){
				$whereClause .= " AND ($tableColumn IS NOT NULL AND $tableColumn != '') ";
			}
		}

        if (isset($selectedColumns) && trim($selectedColumns) != '') {
            $sub_query = "SELECT $selectedColumns FROM $this->table_name AS t " .
                    " INNER JOIN ottocrat_crmentity AS crm ON crm.crmid = t." . $this->table_index;
            // Consider custom table join as well.
            if (isset($this->customFieldTable)) {
                $sub_query .= " LEFT JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
            }
            $sub_query .= " WHERE crm.deleted=0 GROUP BY $selectedColumns HAVING COUNT(*)>1";
        } else {
            $sub_query = "SELECT $tableColumnsString $fromClause $whereClause GROUP BY $tableColumnsString HAVING COUNT(*)>1";
        }

		$i = 1;
		foreach($tableColumns as $tableColumn){
			$tableInfo = explode('.', $tableColumn);
			$duplicateCheckClause .= " ifnull($tableColumn,'null') = ifnull(temp.$tableInfo[1],'null')";
			if (count($tableColumns) != $i++) $duplicateCheckClause .= " AND ";
		}

        $query = $selectClause . $fromClause .
                " LEFT JOIN ottocrat_users_last_import ON ottocrat_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
                " INNER JOIN (" . $sub_query . ") AS temp ON " . $duplicateCheckClause .
                $whereClause .
                " ORDER BY $tableColumnsString," . $this->table_name . "." . $this->table_index . " ASC";
		return $query;
    }
}

?>