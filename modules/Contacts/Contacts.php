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
 * $Header: /advent/projects/wesat/ottocrat_crm/sugarcrm/modules/Contacts/Contacts.php,v 1.70 2005/04/27 11:21:49 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
// Contact is used to store customer information.
class Contacts extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "ottocrat_contactdetails";
	var $table_index= 'contactid';
	var $tab_name = Array('ottocrat_crmentity','ottocrat_contactdetails','ottocrat_contactaddress','ottocrat_contactsubdetails','ottocrat_contactscf','ottocrat_customerdetails');
	var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_contactdetails'=>'contactid','ottocrat_contactaddress'=>'contactaddressid','ottocrat_contactsubdetails'=>'contactsubscriptionid','ottocrat_contactscf'=>'contactid','ottocrat_customerdetails'=>'customerid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_contactscf', 'contactid');

	var $column_fields = Array();

	var $sortby_fields = Array('lastname','firstname','title','email','phone','smownerid','accountname');

	var $list_link_field= 'lastname';

	// This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
	'First Name' => Array('contactdetails'=>'firstname'),
	'Last Name' => Array('contactdetails'=>'lastname'),
	'Title' => Array('contactdetails'=>'title'),
	'Account Name' => Array('account'=>'accountid'),
	'Email' => Array('contactdetails'=>'email'),
	'Office Phone' => Array('contactdetails'=>'phone'),
	'Assigned To' => Array('crmentity'=>'smownerid')
	);

	var $range_fields = Array(
		'first_name',
		'last_name',
		'primary_address_city',
		'account_name',
		'account_id',
		'id',
		'email1',
		'salutation',
		'title',
		'phone_mobile',
		'reports_to_name',
		'primary_address_street',
		'primary_address_city',
		'primary_address_state',
		'primary_address_postalcode',
		'primary_address_country',
		'alt_address_city',
		'alt_address_street',
		'alt_address_city',
		'alt_address_state',
		'alt_address_postalcode',
		'alt_address_country',
		'office_phone',
		'home_phone',
		'other_phone',
		'fax',
		'department',
		'birthdate',
		'assistant_name',
		'assistant_phone');


	var $list_fields_name = Array(
	'First Name' => 'firstname',
	'Last Name' => 'lastname',
	'Title' => 'title',
	'Account Name' => 'account_id',
	'Email' => 'email',
	'Office Phone' => 'phone',
	'Assigned To' => 'assigned_user_id'
	);

	var $search_fields = Array(
	'First Name' => Array('contactdetails'=>'firstname'),
	'Last Name' => Array('contactdetails'=>'lastname'),
	'Title' => Array('contactdetails'=>'title'),
	'Account Name'=>Array('contactdetails'=>'account_id'),
	'Assigned To'=>Array('crmentity'=>'smownerid'),
		);

	var $search_fields_name = Array(
	'First Name' => 'firstname',
	'Last Name' => 'lastname',
	'Title' => 'title',
	'Account Name'=>'account_id',
	'Assigned To'=>'assigned_user_id'
	);

	// This is the list of ottocrat_fields that are required
	var $required_fields =  array("lastname"=>1);

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to ottocrat_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id','lastname','createdtime' ,'modifiedtime');

	//Default Fields for Email Templates -- Pavani
	var $emailTemplate_defaultFields = array('firstname','lastname','salutation','title','email','department','phone','mobile','support_start_date','support_end_date');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'lastname';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'lastname';

	var $related_module_table_index = array(
		'Potentials' => array('table_name' => 'ottocrat_potential', 'table_index' => 'potentialid', 'rel_index' => 'contact_id'),
		'Quotes' => array('table_name' => 'ottocrat_quotes', 'table_index' => 'quoteid', 'rel_index' => 'contactid'),
		'SalesOrder' => array('table_name' => 'ottocrat_salesorder', 'table_index' => 'salesorderid', 'rel_index' => 'contactid'),
		'PurchaseOrder' => array('table_name' => 'ottocrat_purchaseorder', 'table_index' => 'purchaseorderid', 'rel_index' => 'contactid'),
		'Invoice' => array('table_name' => 'ottocrat_invoice', 'table_index' => 'invoiceid', 'rel_index' => 'contactid'),
		'HelpDesk' => array('table_name' => 'ottocrat_troubletickets', 'table_index' => 'ticketid', 'rel_index' => 'contact_id'),
		'Products' => array('table_name' => 'ottocrat_seproductsrel', 'table_index' => 'productid', 'rel_index' => 'crmid'),
		'Calendar' => array('table_name' => 'ottocrat_cntactivityrel', 'table_index' => 'activityid', 'rel_index' => 'contactid'),
		'Documents' => array('table_name' => 'ottocrat_senotesrel', 'table_index' => 'notesid', 'rel_index' => 'crmid'),
		'ServiceContracts' => array('table_name' => 'ottocrat_servicecontracts', 'table_index' => 'servicecontractsid', 'rel_index' => 'sc_related_to'),
		'Services' => array('table_name' => 'ottocrat_crmentityrel', 'table_index' => 'crmid', 'rel_index' => 'crmid'),
		'Campaigns' => array('table_name' => 'ottocrat_campaigncontrel', 'table_index' => 'campaignid', 'rel_index' => 'contactid'),
		'Assets' => array('table_name' => 'ottocrat_assets', 'table_index' => 'assetsid', 'rel_index' => 'contact'),
		'Project' => array('table_name' => 'ottocrat_project', 'table_index' => 'projectid', 'rel_index' => 'linktoaccountscontacts'),
		'Emails' => array('table_name' => 'ottocrat_seactivityrel', 'table_index' => 'crmid', 'rel_index' => 'activityid'),
	);

	function Contacts() {
		$this->log = LoggerManager::getLogger('contact');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Contacts');
	}

	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/** Function to get the number of Contacts assigned to a particular User.
	*  @param varchar $user name - Assigned to User
	*  Returns the count of contacts assigned to user.
	*/
	function getCount($user_name)
	{
		global $log;
		$log->debug("Entering getCount(".$user_name.") method ...");
		$query = "select count(*) from ottocrat_contactdetails  inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_contactdetails.contactid inner join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid where user_name=? and ottocrat_crmentity.deleted=0";
		$result = $this->db->pquery($query,array($user_name),true,"Error retrieving contacts count");
		$rows_found =  $this->db->getRowCount($result);
		$row = $this->db->fetchByAssoc($result, 0);


		$log->debug("Exiting getCount method ...");
		return $row["count(*)"];
	}

	// This function doesn't seem to be used anywhere. Need to check and remove it.
	/** Function to get the Contact Details assigned to a particular User based on the starting count and the number of subsequent records.
	*  @param varchar $user_name - Assigned User
	*  @param integer $from_index - Initial record number to be displayed
	*  @param integer $offset - Count of the subsequent records to be displayed.
	*  Returns Query.
	*/
    function get_contacts($user_name,$from_index,$offset)
    {
	global $log;
	$log->debug("Entering get_contacts(".$user_name.",".$from_index.",".$offset.") method ...");
      $query = "select ottocrat_users.user_name,ottocrat_groups.groupname,ottocrat_contactdetails.department department, ottocrat_contactdetails.phone office_phone, ottocrat_contactdetails.fax fax, ottocrat_contactsubdetails.assistant assistant_name, ottocrat_contactsubdetails.otherphone other_phone, ottocrat_contactsubdetails.homephone home_phone,ottocrat_contactsubdetails.birthday birthdate, ottocrat_contactdetails.lastname last_name,ottocrat_contactdetails.firstname first_name,ottocrat_contactdetails.contactid as id, ottocrat_contactdetails.salutation as salutation, ottocrat_contactdetails.email as email1,ottocrat_contactdetails.title as title,ottocrat_contactdetails.mobile as phone_mobile,ottocrat_account.accountname as account_name,ottocrat_account.accountid as account_id, ottocrat_contactaddress.mailingcity as primary_address_city,ottocrat_contactaddress.mailingstreet as primary_address_street, ottocrat_contactaddress.mailingcountry as primary_address_country,ottocrat_contactaddress.mailingstate as primary_address_state, ottocrat_contactaddress.mailingzip as primary_address_postalcode,   ottocrat_contactaddress.othercity as alt_address_city,ottocrat_contactaddress.otherstreet as alt_address_street, ottocrat_contactaddress.othercountry as alt_address_country,ottocrat_contactaddress.otherstate as alt_address_state, ottocrat_contactaddress.otherzip as alt_address_postalcode  from ottocrat_contactdetails inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_contactdetails.contactid inner join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid left join ottocrat_account on ottocrat_account.accountid=ottocrat_contactdetails.accountid left join ottocrat_contactaddress on ottocrat_contactaddress.contactaddressid=ottocrat_contactdetails.contactid left join ottocrat_contactsubdetails on ottocrat_contactsubdetails.contactsubscriptionid = ottocrat_contactdetails.contactid left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid left join ottocrat_users on ottocrat_crmentity.smownerid=ottocrat_users.id where user_name='" .$user_name ."' and ottocrat_crmentity.deleted=0 limit " .$from_index ."," .$offset;

	$log->debug("Exiting get_contacts method ...");
      return $this->process_list_query1($query);
    }


    /** Function to process list query for a given query
    *  @param $query
    *  Returns the results of query in array format
    */
    function process_list_query1($query)
    {
	global $log;
	$log->debug("Entering process_list_query1(".$query.") method ...");

        $result =& $this->db->query($query,true,"Error retrieving $this->object_name list: ");
        $list = Array();
        $rows_found =  $this->db->getRowCount($result);
        if($rows_found != 0)
        {
		   $contact = Array();
               for($index = 0 , $row = $this->db->fetchByAssoc($result, $index); $row && $index <$rows_found;$index++, $row = $this->db->fetchByAssoc($result, $index))

             {
                foreach($this->range_fields as $columnName)
                {
                    if (isset($row[$columnName])) {

                        $contact[$columnName] = $row[$columnName];
                    }
                    else
                    {
                            $contact[$columnName] = "";
                    }
	     }
// TODO OPTIMIZE THE QUERY ACCOUNT NAME AND ID are set separetly for every ottocrat_contactdetails and hence
// ottocrat_account query goes for ecery single ottocrat_account row

                    $list[] = $contact;
                }
        }

        $response = Array();
        $response['list'] = $list;
        $response['row_count'] = $rows_found;
        $response['next_offset'] = $next_offset;
        $response['previous_offset'] = $previous_offset;


	$log->debug("Exiting process_list_query1 method ...");
        return $response;
    }


    /** Function to process list query for Plugin with Security Parameters for a given query
    *  @param $query
    *  Returns the results of query in array format
    */
    function plugin_process_list_query($query)
    {
          global $log,$adb,$current_user;
          $log->debug("Entering process_list_query1(".$query.") method ...");
          $permitted_field_lists = Array();
          require('user_privileges/user_privileges_'.$current_user->id.'.php');
          if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
          {
              $sql1 = "select columnname from ottocrat_field where tabid=4 and block <> 75 and ottocrat_field.presence in (0,2)";
			  $params1 = array();
          }else
          {
              $profileList = getCurrentUserProfileList();
              $sql1 = "select columnname from ottocrat_field inner join ottocrat_profile2field on ottocrat_profile2field.fieldid=ottocrat_field.fieldid inner join ottocrat_def_org_field on ottocrat_def_org_field.fieldid=ottocrat_field.fieldid where ottocrat_field.tabid=4 and ottocrat_field.block <> 6 and ottocrat_field.block <> 75 and ottocrat_field.displaytype in (1,2,4,3) and ottocrat_profile2field.visible=0 and ottocrat_def_org_field.visible=0 and ottocrat_field.presence in (0,2)";
			  $params1 = array();
			  if (count($profileList) > 0) {
			  	 $sql1 .= " and ottocrat_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
			  	 array_push($params1, $profileList);
			  }
          }
          $result1 = $this->db->pquery($sql1, $params1);
          for($i=0;$i < $adb->num_rows($result1);$i++)
          {
              $permitted_field_lists[] = $adb->query_result($result1,$i,'columnname');
          }

          $result =& $this->db->query($query,true,"Error retrieving $this->object_name list: ");
          $list = Array();
          $rows_found =  $this->db->getRowCount($result);
          if($rows_found != 0)
          {
              for($index = 0 , $row = $this->db->fetchByAssoc($result, $index); $row && $index <$rows_found;$index++, $row = $this->db->fetchByAssoc($result, $index))
              {
                  $contact = Array();

		  $contact[lastname] = in_array("lastname",$permitted_field_lists) ? $row[lastname] : "";
		  $contact[firstname] = in_array("firstname",$permitted_field_lists)? $row[firstname] : "";
		  $contact[email] = in_array("email",$permitted_field_lists) ? $row[email] : "";


                  if(in_array("accountid",$permitted_field_lists))
                  {
                      $contact[accountname] = $row[accountname];
                      $contact[account_id] = $row[accountid];
                  }else
		  {
                      $contact[accountname] = "";
                      $contact[account_id] = "";
		  }
                  $contact[contactid] =  $row[contactid];
                  $list[] = $contact;
              }
          }

          $response = Array();
          $response['list'] = $list;
          $response['row_count'] = $rows_found;
          $response['next_offset'] = $next_offset;
          $response['previous_offset'] = $previous_offset;
          $log->debug("Exiting process_list_query1 method ...");
          return $response;
    }


	/** Returns a list of the associated opportunities
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_opportunities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_opportunities(".$id.") method ...");
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
				$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_action.value=\"updateRelations\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		// Should Opportunities be listed on Secondary Contacts ignoring the boundaries of Organization.
		// Useful when the Reseller are working to gain Potential for other Organization.
		$ignoreOrganizationCheck = true;

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query ='select case when (ottocrat_users.user_name not like "") then '.$userNameSql.' else ottocrat_groups.groupname end as user_name,
		ottocrat_contactdetails.accountid, ottocrat_contactdetails.contactid , ottocrat_potential.potentialid, ottocrat_potential.potentialname,
		ottocrat_potential.potentialtype, ottocrat_potential.sales_stage, ottocrat_potential.amount, ottocrat_potential.closingdate,
		ottocrat_potential.related_to, ottocrat_potential.contact_id, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_account.accountname
		from ottocrat_contactdetails
		left join ottocrat_contpotentialrel on ottocrat_contpotentialrel.contactid=ottocrat_contactdetails.contactid
		left join ottocrat_potential on (ottocrat_potential.potentialid = ottocrat_contpotentialrel.potentialid or ottocrat_potential.contact_id=ottocrat_contactdetails.contactid)
		inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_potential.potentialid
		left join ottocrat_account on ottocrat_account.accountid=ottocrat_contactdetails.accountid
		LEFT JOIN ottocrat_potentialscf ON ottocrat_potential.potentialid = ottocrat_potentialscf.potentialid
		left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
		left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
		where  ottocrat_crmentity.deleted=0 and ottocrat_contactdetails.contactid ='.$id;

		if (!$ignoreOrganizationCheck) {
			// Restrict the scope of listing to only related contacts of the organization linked to potential via related_to of Potential
			$query .= ' and (ottocrat_contactdetails.accountid = ottocrat_potential.related_to or ottocrat_contactdetails.contactid=ottocrat_potential.contact_id)';
		}

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_opportunities method ...");
		return $return_value;
	}


	/** Returns a list of the associated tasks
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
				if(getFieldVisibilityPermission('Calendar',$current_user->id,'contact_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Task\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_TODO', $related_module) ."'>&nbsp;";
				}
				if(getFieldVisibilityPermission('Events',$current_user->id,'contact_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Events\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_EVENT', $related_module) ."'>";
				}
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name," .
				" ottocrat_contactdetails.lastname, ottocrat_contactdetails.firstname,  ottocrat_activity.activityid ," .
				" ottocrat_activity.subject, ottocrat_activity.activitytype, ottocrat_activity.date_start, ottocrat_activity.due_date," .
				" ottocrat_activity.time_start,ottocrat_activity.time_end, ottocrat_cntactivityrel.contactid, ottocrat_crmentity.crmid," .
				" ottocrat_crmentity.smownerid, ottocrat_crmentity.modifiedtime, ottocrat_recurringevents.recurringtype," .
				" case when (ottocrat_activity.activitytype = 'Task') then ottocrat_activity.status else ottocrat_activity.eventstatus end as status, " .
				" ottocrat_seactivityrel.crmid as parent_id " .
				" from ottocrat_contactdetails " .
				" inner join ottocrat_cntactivityrel on ottocrat_cntactivityrel.contactid = ottocrat_contactdetails.contactid" .
				" inner join ottocrat_activity on ottocrat_cntactivityrel.activityid=ottocrat_activity.activityid" .
				" inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_cntactivityrel.activityid " .
				" left join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid = ottocrat_cntactivityrel.activityid " .
				" left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid" .
				" left outer join ottocrat_recurringevents on ottocrat_recurringevents.activityid=ottocrat_activity.activityid" .
				" left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid" .
				" where ottocrat_contactdetails.contactid=".$id." and ottocrat_crmentity.deleted = 0" .
						" and ((ottocrat_activity.activitytype='Task' and ottocrat_activity.status not in ('Completed','Deferred'))" .
						" or (ottocrat_activity.activitytype Not in ('Emails','Task') and  ottocrat_activity.eventstatus not in ('','Held')))";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}
	/**
	* Function to get Contact related Task & Event which have activity type Held, Completed or Deferred.
	* @param  integer   $id      - contactid
	* returns related Task or Event record in array format
	*/
	function get_history($id)
	{
		global $log;
		$log->debug("Entering get_history(".$id.") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_activity.activityid, ottocrat_activity.subject, ottocrat_activity.status
			, ottocrat_activity.eventstatus,ottocrat_activity.activitytype, ottocrat_activity.date_start,
			ottocrat_activity.due_date,ottocrat_activity.time_start,ottocrat_activity.time_end,
			ottocrat_contactdetails.contactid, ottocrat_contactdetails.firstname,
			ottocrat_contactdetails.lastname, ottocrat_crmentity.modifiedtime,
			ottocrat_crmentity.createdtime, ottocrat_crmentity.description,ottocrat_crmentity.crmid,
			case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name
				from ottocrat_activity
				inner join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid= ottocrat_activity.activityid
				inner join ottocrat_contactdetails on ottocrat_contactdetails.contactid= ottocrat_cntactivityrel.contactid
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
				left join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid=ottocrat_activity.activityid
                left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
				where (ottocrat_activity.activitytype != 'Emails')
				and (ottocrat_activity.status = 'Completed' or ottocrat_activity.status = 'Deferred' or (ottocrat_activity.eventstatus = 'Held' and ottocrat_activity.eventstatus != ''))
				and ottocrat_cntactivityrel.contactid=".$id."
                                and ottocrat_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php
		$log->debug("Entering get_history method ...");
		return getHistory('Contacts',$query,$id);
	}
	/**
	* Function to get Contact related Tickets.
	* @param  integer   $id      - contactid
	* returns related Ticket records in array format
	*/
	function get_tickets($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_tickets(".$id.") method ...");
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'parent_id', 'readwrite') == '0') {
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
				ottocrat_crmentity.crmid, ottocrat_troubletickets.title, ottocrat_contactdetails.contactid, ottocrat_troubletickets.parent_id,
				ottocrat_contactdetails.firstname, ottocrat_contactdetails.lastname, ottocrat_troubletickets.status, ottocrat_troubletickets.priority,
				ottocrat_crmentity.smownerid, ottocrat_troubletickets.ticket_no, ottocrat_troubletickets.contact_id
				from ottocrat_troubletickets inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_troubletickets.ticketid
				left join ottocrat_contactdetails on ottocrat_contactdetails.contactid=ottocrat_troubletickets.contact_id
				LEFT JOIN ottocrat_ticketcf ON ottocrat_troubletickets.ticketid = ottocrat_ticketcf.ticketid
				left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
				left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				where ottocrat_crmentity.deleted=0 and ottocrat_contactdetails.contactid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}

	  /**
	  * Function to get Contact related Quotes
	  * @param  integer   $id  - contactid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'contact_id', 'readwrite') == '0') {
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
		$query = "select case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,ottocrat_crmentity.*, ottocrat_quotes.*,ottocrat_potential.potentialname,ottocrat_contactdetails.lastname,ottocrat_account.accountname from ottocrat_quotes inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_quotes.quoteid left outer join ottocrat_contactdetails on ottocrat_contactdetails.contactid=ottocrat_quotes.contactid left outer join ottocrat_potential on ottocrat_potential.potentialid=ottocrat_quotes.potentialid  left join ottocrat_account on ottocrat_account.accountid = ottocrat_quotes.accountid LEFT JOIN ottocrat_quotescf ON ottocrat_quotescf.quoteid = ottocrat_quotes.quoteid LEFT JOIN ottocrat_quotesbillads ON ottocrat_quotesbillads.quotebilladdressid = ottocrat_quotes.quoteid LEFT JOIN ottocrat_quotesshipads ON ottocrat_quotesshipads.quoteshipaddressid = ottocrat_quotes.quoteid left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid where ottocrat_crmentity.deleted=0 and ottocrat_contactdetails.contactid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_quotes method ...");
		return $return_value;
	  }
	/**
	 * Function to get Contact related SalesOrder
 	 * @param  integer   $id  - contactid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'contact_id', 'readwrite') == '0') {
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
		$query = "select case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,ottocrat_crmentity.*, ottocrat_salesorder.*, ottocrat_quotes.subject as quotename, ottocrat_account.accountname, ottocrat_contactdetails.lastname from ottocrat_salesorder inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_salesorder.salesorderid LEFT JOIN ottocrat_salesordercf ON ottocrat_salesordercf.salesorderid = ottocrat_salesorder.salesorderid LEFT JOIN ottocrat_sobillads ON ottocrat_sobillads.sobilladdressid = ottocrat_salesorder.salesorderid LEFT JOIN ottocrat_soshipads ON ottocrat_soshipads.soshipaddressid = ottocrat_salesorder.salesorderid left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid left outer join ottocrat_quotes on ottocrat_quotes.quoteid=ottocrat_salesorder.quoteid left outer join ottocrat_account on ottocrat_account.accountid=ottocrat_salesorder.accountid LEFT JOIN ottocrat_invoice_recurring_info ON ottocrat_invoice_recurring_info.start_period = ottocrat_salesorder.salesorderid left outer join ottocrat_contactdetails on ottocrat_contactdetails.contactid=ottocrat_salesorder.contactid left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid where ottocrat_crmentity.deleted=0  and  ottocrat_salesorder.contactid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_salesorder method ...");
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

		$query = 'SELECT ottocrat_products.productid, ottocrat_products.productname, ottocrat_products.productcode,
		 		  ottocrat_products.commissionrate, ottocrat_products.qty_per_unit, ottocrat_products.unit_price,
				  ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,ottocrat_contactdetails.lastname
				FROM ottocrat_products
				INNER JOIN ottocrat_seproductsrel
					ON ottocrat_seproductsrel.productid=ottocrat_products.productid and ottocrat_seproductsrel.setype="Contacts"
				INNER JOIN ottocrat_productcf
					ON ottocrat_products.productid = ottocrat_productcf.productid
				INNER JOIN ottocrat_crmentity
					ON ottocrat_crmentity.crmid = ottocrat_products.productid
				INNER JOIN ottocrat_contactdetails
					ON ottocrat_contactdetails.contactid = ottocrat_seproductsrel.crmid
				LEFT JOIN ottocrat_users
					ON ottocrat_users.id=ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_groups
					ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			   WHERE ottocrat_contactdetails.contactid = '.$id.' and ottocrat_crmentity.deleted = 0';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	 }

	/**
	 * Function to get Contact related PurchaseOrder
 	 * @param  integer   $id  - contactid
	 * returns related PurchaseOrder record in array format
	 */
	 function get_purchase_orders($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_purchase_orders(".$id.") method ...");
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'contact_id', 'readwrite') == '0') {
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
		$query = "select case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,ottocrat_crmentity.*, ottocrat_purchaseorder.*,ottocrat_vendor.vendorname,ottocrat_contactdetails.lastname from ottocrat_purchaseorder inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_purchaseorder.purchaseorderid left outer join ottocrat_vendor on ottocrat_purchaseorder.vendorid=ottocrat_vendor.vendorid left outer join ottocrat_contactdetails on ottocrat_contactdetails.contactid=ottocrat_purchaseorder.contactid left join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid LEFT JOIN ottocrat_purchaseordercf ON ottocrat_purchaseordercf.purchaseorderid = ottocrat_purchaseorder.purchaseorderid LEFT JOIN ottocrat_pobillads ON ottocrat_pobillads.pobilladdressid = ottocrat_purchaseorder.purchaseorderid LEFT JOIN ottocrat_poshipads ON ottocrat_poshipads.poshipaddressid = ottocrat_purchaseorder.purchaseorderid left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid where ottocrat_crmentity.deleted=0 and ottocrat_purchaseorder.contactid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_purchase_orders method ...");
		return $return_value;
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
//ottocrat-changes
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
			$query = "select case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,ottocrat_emaildetails.from_email as from_email,replace(replace(ottocrat_emaildetails.to_email,'\"]',''),'[\"','') as saved_toid," .
				" ottocrat_activity.activityid, ottocrat_activity.subject, ottocrat_activity.activitytype, ottocrat_crmentity.modifiedtime," .
				" ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_activity.date_start, ottocrat_activity.time_start, ottocrat_seactivityrel.crmid as parent_id,concat(date_start,time_start) as dateTime " .
				" from ottocrat_activity, ottocrat_seactivityrel, ottocrat_contactdetails, ottocrat_users,ottocrat_emaildetails, ottocrat_crmentity " .
				" left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid" .
				" where ottocrat_seactivityrel.activityid = ottocrat_activity.activityid" .
				" and ottocrat_contactdetails.contactid = ottocrat_seactivityrel.crmid and ottocrat_users.id=ottocrat_crmentity.smownerid" .
				" and ottocrat_crmentity.crmid = ottocrat_activity.activityid and ottocrat_activity.activityid=ottocrat_emaildetails.emailid  and ottocrat_contactdetails.contactid = ".$id." and" .
						" ottocrat_activity.activitytype='Emails' and ottocrat_crmentity.deleted = 0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
#echo $query;
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
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
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'></td>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
					ottocrat_campaign.campaignid, ottocrat_campaign.campaignname, ottocrat_campaign.campaigntype, ottocrat_campaign.campaignstatus,
					ottocrat_campaign.expectedrevenue, ottocrat_campaign.closingdate, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
					ottocrat_crmentity.modifiedtime from ottocrat_campaign
					inner join ottocrat_campaigncontrel on ottocrat_campaigncontrel.campaignid=ottocrat_campaign.campaignid
					inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_campaign.campaignid
					inner join ottocrat_campaignscf ON ottocrat_campaignscf.campaignid = ottocrat_campaign.campaignid
					left join ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
					left join ottocrat_users on ottocrat_users.id = ottocrat_crmentity.smownerid
					where ottocrat_campaigncontrel.contactid=".$id." and ottocrat_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_campaigns method ...");
		return $return_value;
	}

	/**
	* Function to get Contact related Invoices
	* @param  integer   $id      - contactid
	* returns related Invoices record in array format
	*/
	function get_invoices($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_invoices(".$id.") method ...");
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'contact_id', 'readwrite') == '0') {
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
		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
			ottocrat_crmentity.*,
			ottocrat_invoice.*,
			ottocrat_contactdetails.lastname,ottocrat_contactdetails.firstname,
			ottocrat_salesorder.subject AS salessubject
			FROM ottocrat_invoice
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_invoice.invoiceid
			LEFT OUTER JOIN ottocrat_contactdetails
				ON ottocrat_contactdetails.contactid = ottocrat_invoice.contactid
			LEFT OUTER JOIN ottocrat_salesorder
				ON ottocrat_salesorder.salesorderid = ottocrat_invoice.salesorderid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
            LEFT JOIN ottocrat_invoicecf
                ON ottocrat_invoicecf.invoiceid = ottocrat_invoice.invoiceid
			LEFT JOIN ottocrat_invoicebillads
				ON ottocrat_invoicebillads.invoicebilladdressid = ottocrat_invoice.invoiceid
			LEFT JOIN ottocrat_invoiceshipads
				ON ottocrat_invoiceshipads.invoiceshipaddressid = ottocrat_invoice.invoiceid
			LEFT JOIN ottocrat_users
				ON ottocrat_crmentity.smownerid = ottocrat_users.id
			WHERE ottocrat_crmentity.deleted = 0
			AND ottocrat_contactdetails.contactid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_invoices method ...");
		return $return_value;
	}

    /**
	* Function to get Contact related vendors.
	* @param  integer   $id      - contactid
	* returns related vendor records in array format
	*/
	function get_vendors($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_vendors(".$id.") method ...");
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'parent_id', 'readwrite') == '0') {
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
		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
				ottocrat_crmentity.crmid, ottocrat_vendor.*,  ottocrat_vendorcf.*
				from ottocrat_vendor inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_vendor.vendorid
                INNER JOIN ottocrat_vendorcontactrel on ottocrat_vendorcontactrel.vendorid=ottocrat_vendor.vendorid
				LEFT JOIN ottocrat_vendorcf on ottocrat_vendorcf.vendorid=ottocrat_vendor.vendorid
				LEFT JOIN ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_groups on ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				WHERE ottocrat_crmentity.deleted=0 and ottocrat_vendorcontactrel.contactid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_vendors method ...");
		return $return_value;
	}

	/** Function to export the contact records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Contacts Query.
	*/
        function create_export_query($where)
        {
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Contacts", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT ottocrat_contactdetails.salutation as 'Salutation',$fields_list,case when (ottocrat_users.user_name not like '') then ottocrat_users.user_name else ottocrat_groups.groupname end as user_name
                                FROM ottocrat_contactdetails
                                inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_contactdetails.contactid
                                LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid=ottocrat_users.id and ottocrat_users.status='Active'
                                LEFT JOIN ottocrat_account on ottocrat_contactdetails.accountid=ottocrat_account.accountid
				left join ottocrat_contactaddress on ottocrat_contactaddress.contactaddressid=ottocrat_contactdetails.contactid
				left join ottocrat_contactsubdetails on ottocrat_contactsubdetails.contactsubscriptionid=ottocrat_contactdetails.contactid
			        left join ottocrat_contactscf on ottocrat_contactscf.contactid=ottocrat_contactdetails.contactid
			        left join ottocrat_customerdetails on ottocrat_customerdetails.customerid=ottocrat_contactdetails.contactid
	                        LEFT JOIN ottocrat_groups
                        	        ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_contactdetails ottocrat_contactdetails2
					ON ottocrat_contactdetails2.contactid = ottocrat_contactdetails.reportsto";
		$query .= getNonAdminAccessControlQuery('Contacts',$current_user);
		$where_auto = " ottocrat_crmentity.deleted = 0 ";

                if($where != "")
                   $query .= "  WHERE ($where) AND ".$where_auto;
                else
                   $query .= "  WHERE ".$where_auto;

		$log->info("Export Query Constructed Successfully");
		$log->debug("Exiting create_export_query method ...");
		return $query;
        }


/** Function to get the Columnnames of the Contacts
* Used By ottocratCRM Word Plugin
* Returns the Merge Fields for Word Plugin
*/
function getColumnNames()
{
	global $log, $current_user;
	$log->debug("Entering getColumnNames() method ...");
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
	{
	 $sql1 = "select fieldlabel from ottocrat_field where tabid=4 and block <> 75 and ottocrat_field.presence in (0,2)";
	 $params1 = array();
	}else
	{
	 $profileList = getCurrentUserProfileList();
	 $sql1 = "select ottocrat_field.fieldid,fieldlabel from ottocrat_field inner join ottocrat_profile2field on ottocrat_profile2field.fieldid=ottocrat_field.fieldid inner join ottocrat_def_org_field on ottocrat_def_org_field.fieldid=ottocrat_field.fieldid where ottocrat_field.tabid=4 and ottocrat_field.block <> 75 and ottocrat_field.displaytype in (1,2,4,3) and ottocrat_profile2field.visible=0 and ottocrat_def_org_field.visible=0 and ottocrat_field.presence in (0,2)";
	 $params1 = array();
	 if (count($profileList) > 0) {
	 	$sql1 .= " and ottocrat_profile2field.profileid in (". generateQuestionMarks($profileList) .") group by fieldid";
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
	$log->debug("Exiting getColumnNames method ...");
	return $mergeflds;
}
//End
/** Function to get the Contacts assigned to a user with a valid email address.
* @param varchar $username - User Name
* @param varchar $emailaddress - Email Addr for each contact.
* Used By ottocratCRM Outlook Plugin
* Returns the Query
*/
function get_searchbyemailid($username,$emailaddress)
{
	global $log;
	global $current_user;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	$log->debug("Entering get_searchbyemailid(".$username.",".$emailaddress.") method ...");
	$query = "select ottocrat_contactdetails.lastname,ottocrat_contactdetails.firstname,
					ottocrat_contactdetails.contactid, ottocrat_contactdetails.salutation,
					ottocrat_contactdetails.email,ottocrat_contactdetails.title,
					ottocrat_contactdetails.mobile,ottocrat_account.accountname,
					ottocrat_account.accountid as accountid  from ottocrat_contactdetails
						inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_contactdetails.contactid
						inner join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
						left join ottocrat_account on ottocrat_account.accountid=ottocrat_contactdetails.accountid
						left join ottocrat_contactaddress on ottocrat_contactaddress.contactaddressid=ottocrat_contactdetails.contactid
			      LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";
	$query .= getNonAdminAccessControlQuery('Contacts',$current_user);
	$query .= "where ottocrat_crmentity.deleted=0";
	if(trim($emailaddress) != '') {
		$query .= " and ((ottocrat_contactdetails.email like '". formatForSqlLike($emailaddress) .
		"') or ottocrat_contactdetails.lastname REGEXP REPLACE('".$emailaddress.
		"',' ','|') or ottocrat_contactdetails.firstname REGEXP REPLACE('".$emailaddress.
		"',' ','|'))  and ottocrat_contactdetails.email != ''";
	} else {
		$query .= " and (ottocrat_contactdetails.email like '". formatForSqlLike($emailaddress) .
		"' and ottocrat_contactdetails.email != '')";
	}

	$log->debug("Exiting get_searchbyemailid method ...");
	return $this->plugin_process_list_query($query);
}

/** Function to get the Contacts associated with the particular User Name.
*  @param varchar $user_name - User Name
*  Returns query
*/

function get_contactsforol($user_name)
{
	global $log,$adb;
	global $current_user;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($user_name);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

	if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
  {
    $sql1 = "select tablename,columnname from ottocrat_field where tabid=4 and ottocrat_field.presence in (0,2)";
	$params1 = array();
  }else
  {
    $profileList = getCurrentUserProfileList();
    $sql1 = "select tablename,columnname from ottocrat_field inner join ottocrat_profile2field on ottocrat_profile2field.fieldid=ottocrat_field.fieldid inner join ottocrat_def_org_field on ottocrat_def_org_field.fieldid=ottocrat_field.fieldid where ottocrat_field.tabid=4 and ottocrat_field.displaytype in (1,2,4,3) and ottocrat_profile2field.visible=0 and ottocrat_def_org_field.visible=0 and ottocrat_field.presence in (0,2)";
	$params1 = array();
	if (count($profileList) > 0) {
		$sql1 .= " and ottocrat_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
		array_push($params1, $profileList);
	}
  }
  $result1 = $adb->pquery($sql1, $params1);
  for($i=0;$i < $adb->num_rows($result1);$i++)
  {
      $permitted_lists[] = $adb->query_result($result1,$i,'tablename');
      $permitted_lists[] = $adb->query_result($result1,$i,'columnname');
      if($adb->query_result($result1,$i,'columnname') == "accountid")
      {
        $permitted_lists[] = 'ottocrat_account';
        $permitted_lists[] = 'accountname';
      }
  }
	$permitted_lists = array_chunk($permitted_lists,2);
	$column_table_lists = array();
	for($i=0;$i < count($permitted_lists);$i++)
	{
	   $column_table_lists[] = implode(".",$permitted_lists[$i]);
  }

	$log->debug("Entering get_contactsforol(".$user_name.") method ...");
	$query = "select ottocrat_contactdetails.contactid as id, ".implode(',',$column_table_lists)." from ottocrat_contactdetails
						inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_contactdetails.contactid
						inner join ottocrat_users on ottocrat_users.id=ottocrat_crmentity.smownerid
						left join ottocrat_customerdetails on ottocrat_customerdetails.customerid=ottocrat_contactdetails.contactid
						left join ottocrat_account on ottocrat_account.accountid=ottocrat_contactdetails.accountid
						left join ottocrat_contactaddress on ottocrat_contactaddress.contactaddressid=ottocrat_contactdetails.contactid
						left join ottocrat_contactsubdetails on ottocrat_contactsubdetails.contactsubscriptionid = ottocrat_contactdetails.contactid
                        left join ottocrat_campaigncontrel on ottocrat_contactdetails.contactid = ottocrat_campaigncontrel.contactid
                        left join ottocrat_campaignrelstatus on ottocrat_campaignrelstatus.campaignrelstatusid = ottocrat_campaigncontrel.campaignrelstatusid
			      LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
						where ottocrat_crmentity.deleted=0 and ottocrat_users.user_name='".$user_name."'";
  $log->debug("Exiting get_contactsforol method ...");
	return $query;
}


	/** Function to handle module specific operations when saving a entity
	*/
	function save_module($module)
	{
		$this->insertIntoAttachment($this->id,$module);
	}

	/**
	 *      This function is used to add the ottocrat_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
	 *      @param int $id  - entity id to which the ottocrat_files to be uploaded
	 *      @param string $module  - the current module name
	*/
	function insertIntoAttachment($id,$module)
	{
		global $log, $adb,$upload_badext;
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;
		//This is to added to store the existing attachment id of the contact where we should delete this when we give new image
		$old_attachmentid = $adb->query_result($adb->pquery("select ottocrat_crmentity.crmid from ottocrat_seattachmentsrel inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_seattachmentsrel.attachmentsid where  ottocrat_seattachmentsrel.crmid=?", array($id)),0,'crmid');
		foreach($_FILES as $fileindex => $files)
		{
			if($files['name'] != '' && $files['size'] > 0)
			{
				$files['original_name'] = vtlib_purify($_REQUEST[$fileindex.'_hidden']);
				$file_saved = $this->uploadAndSaveFile($id,$module,$files);
			}
		}

		$imageNameSql = 'SELECT name FROM ottocrat_seattachmentsrel INNER JOIN ottocrat_attachments ON
								ottocrat_seattachmentsrel.attachmentsid = ottocrat_attachments.attachmentsid LEFT JOIN ottocrat_contactdetails ON
								ottocrat_contactdetails.contactid = ottocrat_seattachmentsrel.crmid WHERE ottocrat_seattachmentsrel.crmid = ?';
		$imageNameResult = $adb->pquery($imageNameSql,array($id));
		$imageName = decode_html($adb->query_result($imageNameResult, 0, "name"));

		//Inserting image information of record into base table
		$adb->pquery('UPDATE ottocrat_contactdetails SET imagename = ? WHERE contactid = ?',array($imageName,$id));

		//This is to handle the delete image for contacts
		if($module == 'Contacts' && $file_saved)
		{
			if($old_attachmentid != '')
			{
				$setype = $adb->query_result($adb->pquery("select setype from ottocrat_crmentity where crmid=?", array($old_attachmentid)),0,'setype');
				if($setype == 'Contacts Image')
				{
					$del_res1 = $adb->pquery("delete from ottocrat_attachments where attachmentsid=?", array($old_attachmentid));
					$del_res2 = $adb->pquery("delete from ottocrat_seattachmentsrel where attachmentsid=?", array($old_attachmentid));
				}
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
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

		$rel_table_arr = Array("Potentials"=>"ottocrat_contpotentialrel","Potentials"=>"ottocrat_potential","Activities"=>"ottocrat_cntactivityrel",
				"Emails"=>"ottocrat_seactivityrel","HelpDesk"=>"ottocrat_troubletickets","Quotes"=>"ottocrat_quotes","PurchaseOrder"=>"ottocrat_purchaseorder",
				"SalesOrder"=>"ottocrat_salesorder","Products"=>"ottocrat_seproductsrel","Documents"=>"ottocrat_senotesrel",
				"Attachments"=>"ottocrat_seattachmentsrel","Campaigns"=>"ottocrat_campaigncontrel",'Invoice'=>'ottocrat_invoice',
                'ServiceContracts'=>'ottocrat_servicecontracts','Project'=>'ottocrat_project','Assets'=>'ottocrat_assets');

		$tbl_field_arr = Array("ottocrat_contpotentialrel"=>"potentialid","ottocrat_potential"=>"potentialid","ottocrat_cntactivityrel"=>"activityid",
				"ottocrat_seactivityrel"=>"activityid","ottocrat_troubletickets"=>"ticketid","ottocrat_quotes"=>"quoteid","ottocrat_purchaseorder"=>"purchaseorderid",
				"ottocrat_salesorder"=>"salesorderid","ottocrat_seproductsrel"=>"productid","ottocrat_senotesrel"=>"notesid",
				"ottocrat_seattachmentsrel"=>"attachmentsid","ottocrat_campaigncontrel"=>"campaignid",'ottocrat_invoice'=>'invoiceid',
                'ottocrat_servicecontracts'=>'servicecontractsid','ottocrat_project'=>'projectid','ottocrat_assets'=>'assetsid',
                'ottocrat_payments'=>'paymentsid');

		$entity_tbl_field_arr = Array("ottocrat_contpotentialrel"=>"contactid","ottocrat_potential"=>"contact_id","ottocrat_cntactivityrel"=>"contactid",
				"ottocrat_seactivityrel"=>"crmid","ottocrat_troubletickets"=>"contact_id","ottocrat_quotes"=>"contactid","ottocrat_purchaseorder"=>"contactid",
				"ottocrat_salesorder"=>"contactid","ottocrat_seproductsrel"=>"crmid","ottocrat_senotesrel"=>"crmid",
				"ottocrat_seattachmentsrel"=>"crmid","ottocrat_campaigncontrel"=>"contactid",'ottocrat_invoice'=>'contactid',
                'ottocrat_servicecontracts'=>'sc_related_to','ottocrat_project'=>'linktoaccountscontacts','ottocrat_assets'=>'contact',
                'ottocrat_payments'=>'relatedcontact');

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
			$adb->pquery("UPDATE ottocrat_potential SET related_to = ? WHERE related_to = ?", array($entityId, $transferId));
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
		$matrix->setDependency('ottocrat_crmentityContacts',array('ottocrat_groupsContacts','ottocrat_usersContacts','ottocrat_lastModifiedByContacts'));
		$matrix->setDependency('ottocrat_contactdetails', array('ottocrat_crmentityContacts','ottocrat_contactaddress',
								'ottocrat_customerdetails','ottocrat_contactsubdetails','ottocrat_contactscf'));

		if (!$queryplanner->requireTable('ottocrat_contactdetails', $matrix)) {
			return '';
		}


		$query = $this->getRelationQuery($module,$secmodule,"ottocrat_contactdetails","contactid", $queryplanner);

		if ($queryplanner->requireTable("ottocrat_crmentityContacts",$matrix)){
			$query .= " left join ottocrat_crmentity as ottocrat_crmentityContacts on ottocrat_crmentityContacts.crmid = ottocrat_contactdetails.contactid  and ottocrat_crmentityContacts.deleted=0";
		}
		if ($queryplanner->requireTable("ottocrat_contactdetailsContacts")){
			$query .= " left join ottocrat_contactdetails as ottocrat_contactdetailsContacts on ottocrat_contactdetailsContacts.contactid = ottocrat_contactdetails.reportsto";
		}
		if ($queryplanner->requireTable("ottocrat_contactaddress")){
			$query .= " left join ottocrat_contactaddress on ottocrat_contactdetails.contactid = ottocrat_contactaddress.contactaddressid";
		}
		if ($queryplanner->requireTable("ottocrat_customerdetails")){
			$query .= " left join ottocrat_customerdetails on ottocrat_customerdetails.customerid = ottocrat_contactdetails.contactid";
		}
		if ($queryplanner->requireTable("ottocrat_contactsubdetails")){
			$query .= " left join ottocrat_contactsubdetails on ottocrat_contactdetails.contactid = ottocrat_contactsubdetails.contactsubscriptionid";
		}
		if ($queryplanner->requireTable("ottocrat_accountContacts")){
			$query .= " left join ottocrat_account as ottocrat_accountContacts on ottocrat_accountContacts.accountid = ottocrat_contactdetails.accountid";
		}
		if ($queryplanner->requireTable("ottocrat_contactscf")){
			$query .= " left join ottocrat_contactscf on ottocrat_contactdetails.contactid = ottocrat_contactscf.contactid";
		}
		if ($queryplanner->requireTable("ottocrat_email_trackContacts")){
			$query .= " LEFT JOIN ottocrat_email_track AS ottocrat_email_trackContacts ON ottocrat_email_trackContacts.crmid = ottocrat_contactdetails.contactid";
		}
		if ($queryplanner->requireTable("ottocrat_groupsContacts")){
			$query .= " left join ottocrat_groups as ottocrat_groupsContacts on ottocrat_groupsContacts.groupid = ottocrat_crmentityContacts.smownerid";
		}
		if ($queryplanner->requireTable("ottocrat_usersContacts")){
			$query .= " left join ottocrat_users as ottocrat_usersContacts on ottocrat_usersContacts.id = ottocrat_crmentityContacts.smownerid";
		}
		if ($queryplanner->requireTable("ottocrat_lastModifiedByContacts")){
			$query .= " left join ottocrat_users as ottocrat_lastModifiedByContacts on ottocrat_lastModifiedByContacts.id = ottocrat_crmentityContacts.modifiedby ";
		}
        if ($queryplanner->requireTable("ottocrat_createdbyContacts")){
			$query .= " left join ottocrat_users as ottocrat_createdbyContacts on ottocrat_createdbyContacts.id = ottocrat_crmentityContacts.smcreatorid ";
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
			"Calendar" => array("ottocrat_cntactivityrel"=>array("contactid","activityid"),"ottocrat_contactdetails"=>"contactid"),
			"HelpDesk" => array("ottocrat_troubletickets"=>array("contact_id","ticketid"),"ottocrat_contactdetails"=>"contactid"),
			"Quotes" => array("ottocrat_quotes"=>array("contactid","quoteid"),"ottocrat_contactdetails"=>"contactid"),
			"PurchaseOrder" => array("ottocrat_purchaseorder"=>array("contactid","purchaseorderid"),"ottocrat_contactdetails"=>"contactid"),
			"SalesOrder" => array("ottocrat_salesorder"=>array("contactid","salesorderid"),"ottocrat_contactdetails"=>"contactid"),
			"Products" => array("ottocrat_seproductsrel"=>array("crmid","productid"),"ottocrat_contactdetails"=>"contactid"),
			"Campaigns" => array("ottocrat_campaigncontrel"=>array("contactid","campaignid"),"ottocrat_contactdetails"=>"contactid"),
			"Documents" => array("ottocrat_senotesrel"=>array("crmid","notesid"),"ottocrat_contactdetails"=>"contactid"),
			"Accounts" => array("ottocrat_contactdetails"=>array("contactid","accountid")),
			"Invoice" => array("ottocrat_invoice"=>array("contactid","invoiceid"),"ottocrat_contactdetails"=>"contactid"),
			"Emails" => array("ottocrat_seactivityrel"=>array("crmid","activityid"),"ottocrat_contactdetails"=>"contactid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;

		//Deleting Contact related Potentials.
		$pot_q = 'SELECT ottocrat_crmentity.crmid FROM ottocrat_crmentity
			INNER JOIN ottocrat_potential ON ottocrat_crmentity.crmid=ottocrat_potential.potentialid
			LEFT JOIN ottocrat_account ON ottocrat_account.accountid=ottocrat_potential.related_to
			WHERE ottocrat_crmentity.deleted=0 AND ottocrat_potential.related_to=?';
		$pot_res = $this->db->pquery($pot_q, array($id));
		$pot_ids_list = array();
		for($k=0;$k < $this->db->num_rows($pot_res);$k++)
		{
			$pot_id = $this->db->query_result($pot_res,$k,"crmid");
			$pot_ids_list[] = $pot_id;
			$sql = 'UPDATE ottocrat_crmentity SET deleted = 1 WHERE crmid = ?';
			$this->db->pquery($sql, array($pot_id));
		}
		//Backup deleted Contact related Potentials.
		$params = array($id, RB_RECORD_UPDATED, 'ottocrat_crmentity', 'deleted', 'crmid', implode(",", $pot_ids_list));
		$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);

		//Backup Contact-Trouble Tickets Relation
		$tkt_q = 'SELECT ticketid FROM ottocrat_troubletickets WHERE contact_id=?';
		$tkt_res = $this->db->pquery($tkt_q, array($id));
		if ($this->db->num_rows($tkt_res) > 0) {
			$tkt_ids_list = array();
			for($k=0;$k < $this->db->num_rows($tkt_res);$k++)
			{
				$tkt_ids_list[] = $this->db->query_result($tkt_res,$k,"ticketid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'ottocrat_troubletickets', 'contact_id', 'ticketid', implode(",", $tkt_ids_list));
			$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//removing the relationship of contacts with Trouble Tickets
		$this->db->pquery('UPDATE ottocrat_troubletickets SET contact_id=0 WHERE contact_id=?', array($id));

		//Backup Contact-PurchaseOrder Relation
		$po_q = 'SELECT purchaseorderid FROM ottocrat_purchaseorder WHERE contactid=?';
		$po_res = $this->db->pquery($po_q, array($id));
		if ($this->db->num_rows($po_res) > 0) {
			$po_ids_list = array();
			for($k=0;$k < $this->db->num_rows($po_res);$k++)
			{
				$po_ids_list[] = $this->db->query_result($po_res,$k,"purchaseorderid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'ottocrat_purchaseorder', 'contactid', 'purchaseorderid', implode(",", $po_ids_list));
			$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//removing the relationship of contacts with PurchaseOrder
		$this->db->pquery('UPDATE ottocrat_purchaseorder SET contactid=0 WHERE contactid=?', array($id));

		//Backup Contact-SalesOrder Relation
		$so_q = 'SELECT salesorderid FROM ottocrat_salesorder WHERE contactid=?';
		$so_res = $this->db->pquery($so_q, array($id));
		if ($this->db->num_rows($so_res) > 0) {
			$so_ids_list = array();
			for($k=0;$k < $this->db->num_rows($so_res);$k++)
			{
				$so_ids_list[] = $this->db->query_result($so_res,$k,"salesorderid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'ottocrat_salesorder', 'contactid', 'salesorderid', implode(",", $so_ids_list));
			$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//removing the relationship of contacts with SalesOrder
		$this->db->pquery('UPDATE ottocrat_salesorder SET contactid=0 WHERE contactid=?', array($id));

		//Backup Contact-Quotes Relation
		$quo_q = 'SELECT quoteid FROM ottocrat_quotes WHERE contactid=?';
		$quo_res = $this->db->pquery($quo_q, array($id));
		if ($this->db->num_rows($quo_res) > 0) {
			$quo_ids_list = array();
			for($k=0;$k < $this->db->num_rows($quo_res);$k++)
			{
				$quo_ids_list[] = $this->db->query_result($quo_res,$k,"quoteid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'ottocrat_quotes', 'contactid', 'quoteid', implode(",", $quo_ids_list));
			$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//removing the relationship of contacts with Quotes
		$this->db->pquery('UPDATE ottocrat_quotes SET contactid=0 WHERE contactid=?', array($id));
		//remove the portal info the contact
		$this->db->pquery('DELETE FROM ottocrat_portalinfo WHERE id = ?', array($id));
		$this->db->pquery('UPDATE ottocrat_customerdetails SET portal=0,support_start_date=NULL,support_end_date=NULl WHERE customerid=?', array($id));
		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Accounts') {
			$sql = 'UPDATE ottocrat_contactdetails SET accountid = ? WHERE contactid = ?';
			$this->db->pquery($sql, array(null, $id));
		} elseif($return_module == 'Potentials') {
			$sql = 'DELETE FROM ottocrat_contpotentialrel WHERE contactid=? AND potentialid=?';
			$this->db->pquery($sql, array($id, $return_id));

			//If contact related to potential through edit of record,that entry will be present in
			//ottocrat_potential contact_id column,which should be set to zero
			$sql = 'UPDATE ottocrat_potential SET contact_id = ? WHERE contact_id=? AND potentialid=?';
			$this->db->pquery($sql, array(0,$id, $return_id));
		} elseif($return_module == 'Campaigns') {
			$sql = 'DELETE FROM ottocrat_campaigncontrel WHERE contactid=? AND campaignid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Products') {
			$sql = 'DELETE FROM ottocrat_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Vendors') {
			$sql = 'DELETE FROM ottocrat_vendorcontactrel WHERE vendorid=? AND contactid=?';
			$this->db->pquery($sql, array($return_id, $id));
		} else {
			$sql = 'DELETE FROM ottocrat_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	//added to get mail info for portal user
	//type argument included when when addin customizable tempalte for sending portal login details
	public static function getPortalEmailContents($entityData, $password, $type='') {
        require_once 'config.inc.php';
		global $PORTAL_URL, $HELPDESK_SUPPORT_EMAIL_ID;

		$adb = PearDatabase::getInstance();
		$moduleName = $entityData->getModuleName();

		$companyDetails = getCompanyDetails();

		$portalURL = '<a href="'.$PORTAL_URL.'" style="font-family:Arial, Helvetica, sans-serif;font-size:12px; font-weight:bolder;text-decoration:none;color: #4242FD;">'.getTranslatedString('Please Login Here', $moduleName).'</a>';

		//here id is hardcoded with 5. it is for support start notification in ottocrat_notificationscheduler
		$query='SELECT ottocrat_emailtemplates.subject,ottocrat_emailtemplates.body
					FROM ottocrat_notificationscheduler
						INNER JOIN ottocrat_emailtemplates ON ottocrat_emailtemplates.templateid=ottocrat_notificationscheduler.notificationbody
					WHERE schedulednotificationid=5';

		$result = $adb->pquery($query, array());
		$body=decode_html($adb->query_result($result,0,'body'));
		$contents=$body;
		$contents = str_replace('$contact_name$',$entityData->get('firstname')." ".$entityData->get('lastname'),$contents);
		$contents = str_replace('$login_name$',$entityData->get('email'),$contents);
		$contents = str_replace('$password$',$password,$contents);
		$contents = str_replace('$URL$',$portalURL,$contents);
		$contents = str_replace('$support_team$',getTranslatedString('Support Team', $moduleName),$contents);
		$contents = str_replace('$logo$','<img src="cid:logo" />',$contents);

		//Company Details
		$contents = str_replace('$address$',$companyDetails['address'],$contents);
		$contents = str_replace('$companyname$',$companyDetails['companyname'],$contents);
		$contents = str_replace('$phone$',$companyDetails['phone'],$contents);
		$contents = str_replace('$companywebsite$',$companyDetails['website'],$contents);
		$contents = str_replace('$supportemail$',$HELPDESK_SUPPORT_EMAIL_ID,$contents);

		if($type == "LoginDetails") {
			$temp=$contents;
			$value["subject"]=decode_html($adb->query_result($result,0,'subject'));
			$value["body"]=$temp;
			return $value;
		}
		return $contents;
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Products') {
				$adb->pquery("insert into ottocrat_seproductsrel values (?,?,?)", array($crmid, $with_crmid, 'Contacts'));

			} elseif($with_module == 'Campaigns') {
				$adb->pquery("insert into ottocrat_campaigncontrel values(?,?,1)", array($with_crmid, $crmid));

			} elseif($with_module == 'Potentials') {
				$adb->pquery("insert into ottocrat_contpotentialrel values(?,?)", array($crmid, $with_crmid));

			}
            else if($with_module == 'Vendors'){
        		$adb->pquery("insert into ottocrat_vendorcontactrel values (?,?)", array($with_crmid,$crmid));
            }else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

	function getListButtons($app_strings,$mod_strings = false) {
		$list_buttons = Array();

		if(isPermitted('Contacts','Delete','') == 'yes') {
			$list_buttons['del'] = $app_strings[LBL_MASS_DELETE];
		}
		if(isPermitted('Contacts','EditView','') == 'yes') {
			$list_buttons['mass_edit'] = $app_strings[LBL_MASS_EDIT];
			$list_buttons['c_owner'] = $app_strings[LBL_CHANGE_OWNER];
		}
		if(isPermitted('Emails','EditView','') == 'yes'){
			$list_buttons['s_mail'] = $app_strings[LBL_SEND_MAIL_BUTTON];
		}
		return $list_buttons;
	}
}

?>
