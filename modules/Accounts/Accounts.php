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
 * $Header: /advent/projects/wesat/ottocrat_crm/sugarcrm/modules/Accounts/Accounts.php,v 1.53 2005/04/28 08:06:45 rank Exp $
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
class Accounts extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "ottocrat_account";
	var $table_index= 'accountid';
	var $tab_name = Array('ottocrat_crmentity','ottocrat_account','ottocrat_accountbillads','ottocrat_accountshipads','ottocrat_accountscf');
	var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_account'=>'accountid','ottocrat_accountbillads'=>'accountaddressid','ottocrat_accountshipads'=>'accountaddressid','ottocrat_accountscf'=>'accountid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_accountscf', 'accountid');
	var $entity_table = "ottocrat_crmentity";

	var $column_fields = Array();

	var $sortby_fields = Array('accountname','bill_city','website','phone','smownerid');

	//var $groupTable = Array('ottocrat_accountgrouprelation','accountid');

	// This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
			'Account Name'=>Array('ottocrat_account'=>'accountname'),
			'Billing City'=>Array('ottocrat_accountbillads'=>'bill_city'),
			'Website'=>Array('ottocrat_account'=>'website'),
			'Phone'=>Array('ottocrat_account'=> 'phone'),
			'Assigned To'=>Array('ottocrat_crmentity'=>'smownerid')
			);

	var $list_fields_name = Array(
			'Account Name'=>'accountname',
			'Billing City'=>'bill_city',
			'Website'=>'website',
			'Phone'=>'phone',
			'Assigned To'=>'assigned_user_id'
			);
	var $list_link_field= 'accountname';

	var $search_fields = Array(
			'Account Name'=>Array('ottocrat_account'=>'accountname'),
			'Billing City'=>Array('ottocrat_accountbillads'=>'bill_city'),
			'Assigned To'=>Array('ottocrat_crmentity'=>'smownerid'),
			);

	var $search_fields_name = Array(
			'Account Name'=>'accountname',
			'Billing City'=>'bill_city',
			'Assigned To'=>'assigned_user_id',
			);
	// This is the list of ottocrat_fields that are required
	var $required_fields =  array();

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to ottocrat_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'accountname');

	//Default Fields for Email Templates -- Pavani
	var $emailTemplate_defaultFields = array('accountname','account_type','industry','annualrevenue','phone','email1','rating','website','fax');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'accountname';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'accountname';

	var $related_module_table_index = array(
		'Contacts' => array('table_name' => 'ottocrat_contactdetails', 'table_index' => 'contactid', 'rel_index' => 'accountid'),
		'Potentials' => array('table_name' => 'ottocrat_potential', 'table_index' => 'potentialid', 'rel_index' => 'related_to'),
		'Quotes' => array('table_name' => 'ottocrat_quotes', 'table_index' => 'quoteid', 'rel_index' => 'accountid'),
		'SalesOrder' => array('table_name' => 'ottocrat_salesorder', 'table_index' => 'salesorderid', 'rel_index' => 'accountid'),
		'Invoice' => array('table_name' => 'ottocrat_invoice', 'table_index' => 'invoiceid', 'rel_index' => 'accountid'),
		'HelpDesk' => array('table_name' => 'ottocrat_troubletickets', 'table_index' => 'ticketid', 'rel_index' => 'parent_id'),
		'Products' => array('table_name' => 'ottocrat_seproductsrel', 'table_index' => 'productid', 'rel_index' => 'crmid'),
		'Calendar' => array('table_name' => 'ottocrat_seactivityrel', 'table_index' => 'activityid', 'rel_index' => 'crmid'),
		'Documents' => array('table_name' => 'ottocrat_senotesrel', 'table_index' => 'notesid', 'rel_index' => 'crmid'),
		'ServiceContracts' => array('table_name' => 'ottocrat_servicecontracts', 'table_index' => 'servicecontractsid', 'rel_index' => 'sc_related_to'),
		'Services' => array('table_name' => 'ottocrat_crmentityrel', 'table_index' => 'crmid', 'rel_index' => 'crmid'),
		'Campaigns' => array('table_name' => 'ottocrat_campaignaccountrel', 'table_index' => 'campaignid', 'rel_index' => 'accountid'),
		'Assets' => array('table_name' => 'ottocrat_assets', 'table_index' => 'assetsid', 'rel_index' => 'account'),
		'Project' => array('table_name' => 'ottocrat_project', 'table_index' => 'projectid', 'rel_index' => 'linktoaccountscontacts'),
	);

	function Accounts() {
		$this->log =LoggerManager::getLogger('account');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Accounts');
	}

	/** Function to handle module specific operations when saving a entity
	*/
	function save_module($module) {

	}


	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
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

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
				ottocrat_campaign.campaignid, ottocrat_campaign.campaignname, ottocrat_campaign.campaigntype, ottocrat_campaign.campaignstatus,
				ottocrat_campaign.expectedrevenue, ottocrat_campaign.closingdate, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
				ottocrat_crmentity.modifiedtime
				from ottocrat_campaign
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_campaign.campaignid
				INNER JOIN ottocrat_campaignscf ON ottocrat_campaignscf.campaignid = ottocrat_campaign.campaignid
				LEFT JOIN ottocrat_campaignaccountrel ON ottocrat_campaignaccountrel.campaignid=ottocrat_campaign.campaignid
				LEFT JOIN ottocrat_campaigncontrel ON ottocrat_campaigncontrel.campaignid=ottocrat_campaign.campaignid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid=ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
				WHERE ottocrat_crmentity.deleted=0 AND (ottocrat_campaignaccountrel.accountid=$id";

		if(!empty ($entityIds)){
			$query .= " OR ottocrat_campaigncontrel.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_campaigns method ...");
		return $return_value;
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id','readwrite') == '0') {
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
		$query = "SELECT ottocrat_contactdetails.*,
			ottocrat_crmentity.crmid,
                        ottocrat_crmentity.smownerid,
			ottocrat_account.accountname,
			case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name
			FROM ottocrat_contactdetails
			INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_contactdetails.contactid
			LEFT JOIN ottocrat_account ON ottocrat_account.accountid = ottocrat_contactdetails.accountid
			INNER JOIN ottocrat_contactaddress ON ottocrat_contactdetails.contactid = ottocrat_contactaddress.contactaddressid
			INNER JOIN ottocrat_contactsubdetails ON ottocrat_contactdetails.contactid = ottocrat_contactsubdetails.contactsubscriptionid
			INNER JOIN ottocrat_customerdetails ON ottocrat_contactdetails.contactid = ottocrat_customerdetails.customerid
			INNER JOIN ottocrat_contactscf ON ottocrat_contactdetails.contactid = ottocrat_contactscf.contactid
			LEFT JOIN ottocrat_groups	ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid = ottocrat_users.id
			WHERE ottocrat_crmentity.deleted = 0
			AND ottocrat_contactdetails.accountid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
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
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		// TODO: We need to add pull contacts if its linked as secondary in Potentials too.
		// These relations are captued in ottocrat_contpotentialrel
		// Better to provide switch to turn-on / off this feature like in
		// Contacts::get_opportunities

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT ottocrat_potential.potentialid, ottocrat_potential.related_to, ottocrat_potential.potentialname, ottocrat_potential.sales_stage,ottocrat_potential.contact_id,
				ottocrat_potential.potentialtype, ottocrat_potential.amount, ottocrat_potential.closingdate, ottocrat_potential.potentialtype, ottocrat_account.accountname,
				case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid
				FROM ottocrat_potential
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_potential.potentialid
				LEFT JOIN ottocrat_account ON ottocrat_account.accountid = ottocrat_potential.related_to
				INNER JOIN ottocrat_potentialscf ON ottocrat_potential.potentialid = ottocrat_potentialscf.potentialid
				LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid = ottocrat_users.id
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				WHERE ottocrat_crmentity.deleted = 0 AND (ottocrat_potential.related_to = $id ";
		if(!empty($entityIds)) {
			$query .= " OR ottocrat_potential.contact_id IN (".$entityIds.")";
		}

		$query .= ')';

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

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT ottocrat_activity.*, ottocrat_cntactivityrel.*, ottocrat_seactivityrel.crmid as parent_id, ottocrat_contactdetails.lastname,
				ottocrat_contactdetails.firstname, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_crmentity.modifiedtime,
				case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
				ottocrat_recurringevents.recurringtype
				FROM ottocrat_activity
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_activity.activityid
				LEFT JOIN ottocrat_seactivityrel ON ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
				LEFT JOIN ottocrat_cntactivityrel ON ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid
				LEFT JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_cntactivityrel.contactid
				LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
				LEFT OUTER JOIN ottocrat_recurringevents ON ottocrat_recurringevents.activityid = ottocrat_activity.activityid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				WHERE ottocrat_crmentity.deleted = 0
				AND ((ottocrat_activity.activitytype='Task' and ottocrat_activity.status not in ('Completed','Deferred'))
				OR (ottocrat_activity.activitytype not in ('Emails','Task') and  ottocrat_activity.eventstatus not in ('','Held')))
				AND (ottocrat_seactivityrel.crmid = $id";

		if(!empty ($entityIds)){
			$query .= " OR ottocrat_cntactivityrel.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
        }
        // There could be more than one contact for an activity.
        $query .= ' GROUP BY ottocrat_activity.activityid';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/**
	 * Function to get Account related Task & Event which have activity type Held, Completed or Deferred.
 	 * @param  integer   $id      - accountid
 	 * returns related Task or Event record in array format
 	 */
	function get_history($id)
	{
		global $log;
                $log->debug("Entering get_history(".$id.") method ...");

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT DISTINCT(ottocrat_activity.activityid), ottocrat_activity.subject, ottocrat_activity.status, ottocrat_activity.eventstatus,
				ottocrat_activity.activitytype, ottocrat_activity.date_start, ottocrat_activity.due_date, ottocrat_activity.time_start, ottocrat_activity.time_end,
				ottocrat_crmentity.modifiedtime, ottocrat_crmentity.createdtime, ottocrat_crmentity.description,
				case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name
				FROM ottocrat_activity
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_activity.activityid
				LEFT JOIN ottocrat_seactivityrel ON ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
				LEFT JOIN ottocrat_cntactivityrel ON ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid
				LEFT JOIN ottocrat_contactdetails ON ottocrat_contactdetails.contactid = ottocrat_cntactivityrel.contactid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid
				WHERE (ottocrat_activity.activitytype != 'Emails')
				AND (ottocrat_activity.status = 'Completed'
					OR ottocrat_activity.status = 'Deferred'
					OR (ottocrat_activity.eventstatus = 'Held' AND ottocrat_activity.eventstatus != ''))
				AND ottocrat_crmentity.deleted = 0 AND (ottocrat_seactivityrel.crmid = $id";

		if(!empty ($entityIds)){
			$query .= " OR ottocrat_cntactivityrel.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php
		$log->debug("Exiting get_history method ...");
		return getHistory('Accounts',$query,$id);
	}

	/** Returns a list of the associated emails
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user, $adb;
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

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,ottocrat_emaildetails.from_email as from_email,replace(replace(ottocrat_emaildetails.to_email,'\"]',''),'[\"','') as saved_toid,
			ottocrat_activity.activityid, ottocrat_activity.subject, ottocrat_activity.activitytype, ottocrat_crmentity.modifiedtime,
			ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_activity.date_start,ottocrat_activity.time_start, ottocrat_seactivityrel.crmid as parent_id
			FROM ottocrat_activity, ottocrat_seactivityrel, ottocrat_account, ottocrat_users,ottocrat_emaildetails, ottocrat_crmentity
			LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid=ottocrat_crmentity.smownerid
			WHERE ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
				AND ottocrat_seactivityrel.crmid IN (".$entityIds.")
				AND ottocrat_users.id=ottocrat_crmentity.smownerid
				AND ottocrat_crmentity.crmid = ottocrat_activity.activityid
				 AND ottocrat_activity.activityid=ottocrat_emaildetails.emailid
				AND ottocrat_activity.activitytype='Emails'
				AND ottocrat_account.accountid = ".$id."
				AND ottocrat_crmentity.deleted = 0";
echo $query;
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}


	/**
	* Function to get Account related Quotes
	* @param  integer   $id      - accountid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id','readwrite') == '0') {
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

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
				ottocrat_crmentity.*, ottocrat_quotes.*, ottocrat_potential.potentialname, ottocrat_account.accountname
				FROM ottocrat_quotes
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_quotes.quoteid
				LEFT OUTER JOIN ottocrat_account ON ottocrat_account.accountid = ottocrat_quotes.accountid
				LEFT OUTER JOIN ottocrat_potential ON ottocrat_potential.potentialid = ottocrat_quotes.potentialid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
                LEFT JOIN ottocrat_quotescf ON ottocrat_quotescf.quoteid = ottocrat_quotes.quoteid
				LEFT JOIN ottocrat_quotesbillads ON ottocrat_quotesbillads.quotebilladdressid = ottocrat_quotes.quoteid
				LEFT JOIN ottocrat_quotesshipads ON ottocrat_quotesshipads.quoteshipaddressid = ottocrat_quotes.quoteid
				LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid = ottocrat_users.id
				WHERE ottocrat_crmentity.deleted = 0 AND (ottocrat_account.accountid = $id";

		if(!empty ($entityIds)){
			$query .= " OR ottocrat_quotes.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_quotes method ...");
		return $return_value;
	}
	/**
	* Function to get Account related Invoices
	* @param  integer   $id      - accountid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id','readwrite') == '0') {
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

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
				ottocrat_crmentity.*, ottocrat_invoice.*, ottocrat_account.accountname, ottocrat_salesorder.subject AS salessubject
				FROM ottocrat_invoice
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_invoice.invoiceid
				LEFT OUTER JOIN ottocrat_account ON ottocrat_account.accountid = ottocrat_invoice.accountid
				LEFT OUTER JOIN ottocrat_salesorder ON ottocrat_salesorder.salesorderid = ottocrat_invoice.salesorderid
                LEFT JOIN ottocrat_invoicecf ON ottocrat_invoicecf.invoiceid = ottocrat_invoice.invoiceid
				LEFT JOIN ottocrat_invoicebillads ON ottocrat_invoicebillads.invoicebilladdressid = ottocrat_invoice.invoiceid
				LEFT JOIN ottocrat_invoiceshipads ON ottocrat_invoiceshipads.invoiceshipaddressid = ottocrat_invoice.invoiceid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid = ottocrat_users.id
				WHERE ottocrat_crmentity.deleted = 0 AND (ottocrat_invoice.accountid = $id";

		if(!empty ($entityIds)){
			$query .= " OR ottocrat_invoice.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_invoices method ...");
		return $return_value;
	}

	/**
	* Function to get Account related SalesOrder
	* @param  integer   $id      - accountid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id','readwrite') == '0') {
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

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT ottocrat_crmentity.*, ottocrat_salesorder.*, ottocrat_quotes.subject AS quotename, ottocrat_account.accountname,
				case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name
				FROM ottocrat_salesorder
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_salesorder.salesorderid
				LEFT OUTER JOIN ottocrat_quotes ON ottocrat_quotes.quoteid = ottocrat_salesorder.quoteid
				LEFT OUTER JOIN ottocrat_account ON ottocrat_account.accountid = ottocrat_salesorder.accountid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
                LEFT JOIN ottocrat_invoice_recurring_info ON ottocrat_invoice_recurring_info.start_period = ottocrat_salesorder.salesorderid
                LEFT JOIN ottocrat_salesordercf ON ottocrat_salesordercf.salesorderid = ottocrat_salesorder.salesorderid
				LEFT JOIN ottocrat_sobillads ON ottocrat_sobillads.sobilladdressid = ottocrat_salesorder.salesorderid
				LEFT JOIN ottocrat_soshipads ON ottocrat_soshipads.soshipaddressid = ottocrat_salesorder.salesorderid
				LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid = ottocrat_users.id
				WHERE ottocrat_crmentity.deleted = 0 AND (ottocrat_salesorder.accountid = $id";

		if(!empty ($entityIds)){
			$query .= " OR ottocrat_salesorder.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_salesorder method ...");
		return $return_value;
	}
	/**
	* Function to get Account related Tickets
	* @param  integer   $id      - accountid
	* returns related Ticket record in array format
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'parent_id','readwrite') == '0') {
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

		$entityIds = $this->getRelatedContactsIds($id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name, ottocrat_users.id,
				ottocrat_troubletickets.title, ottocrat_troubletickets.ticketid AS crmid, ottocrat_troubletickets.status, ottocrat_troubletickets.priority,
				ottocrat_troubletickets.parent_id, ottocrat_troubletickets.contact_id, ottocrat_troubletickets.ticket_no, ottocrat_crmentity.smownerid, ottocrat_crmentity.modifiedtime
				FROM ottocrat_troubletickets
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_troubletickets.ticketid
				LEFT JOIN ottocrat_ticketcf ON ottocrat_troubletickets.ticketid = ottocrat_ticketcf.ticketid
				LEFT JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				WHERE  ottocrat_crmentity.deleted = 0 and (ottocrat_troubletickets.parent_id = $id";

		if(!empty ($entityIds)){
			$query .= " OR ottocrat_troubletickets.contact_id IN (".$entityIds."))";
		} else {
			$query .= ")";
		}
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}
	/**
	* Function to get Account related Products
	* @param  integer   $id      - accountid
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

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$query = "SELECT ottocrat_products.productid, ottocrat_products.productname, ottocrat_products.productcode, ottocrat_products.commissionrate,
				ottocrat_products.qty_per_unit, ottocrat_products.unit_price, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid
				FROM ottocrat_products
				INNER JOIN ottocrat_seproductsrel ON ottocrat_products.productid = ottocrat_seproductsrel.productid
				and ottocrat_seproductsrel.setype IN ('Accounts', 'Contacts')
				INNER JOIN ottocrat_productcf ON ottocrat_products.productid = ottocrat_productcf.productid
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_products.productid
				LEFT JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_seproductsrel.crmid IN (".$entityIds.")";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/** Function to export the account records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Accounts Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
                $log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Accounts", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list,case when (ottocrat_users.user_name not like '') then ottocrat_users.user_name else ottocrat_groups.groupname end as user_name
	       			FROM ".$this->entity_table."
				INNER JOIN ottocrat_account
					ON ottocrat_account.accountid = ottocrat_crmentity.crmid
				LEFT JOIN ottocrat_accountbillads
					ON ottocrat_accountbillads.accountaddressid = ottocrat_account.accountid
				LEFT JOIN ottocrat_accountshipads
					ON ottocrat_accountshipads.accountaddressid = ottocrat_account.accountid
				LEFT JOIN ottocrat_accountscf
					ON ottocrat_accountscf.accountid = ottocrat_account.accountid
	                        LEFT JOIN ottocrat_groups
                        	        ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_users
					ON ottocrat_users.id = ottocrat_crmentity.smownerid and ottocrat_users.status = 'Active'
				LEFT JOIN ottocrat_account ottocrat_account2
					ON ottocrat_account2.accountid = ottocrat_account.parentid
				";//ottocrat_account2 is added to get the Member of account

		$query .= $this->getNonAdminAccessControlQuery('Accounts',$current_user);
		$where_auto = " ottocrat_crmentity.deleted = 0 ";

		if($where != "")
			$query .= " WHERE ($where) AND ".$where_auto;
		else
			$query .= " WHERE ".$where_auto;

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to get the Columnnames of the Account Record
	* Used By ottocratCRM Word Plugin
	* Returns the Merge Fields for Word Plugin
	*/
	function getColumnNames_Acnt()
	{
		global $log,$current_user;
		$log->debug("Entering getColumnNames_Acnt() method ...");
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
		{
			$sql1 = "SELECT fieldlabel FROM ottocrat_field WHERE tabid = 6 and ottocrat_field.presence in (0,2)";
			$params1 = array();
		}else
		{
			$profileList = getCurrentUserProfileList();
			$sql1 = "select ottocrat_field.fieldid,fieldlabel from ottocrat_field INNER JOIN ottocrat_profile2field on ottocrat_profile2field.fieldid=ottocrat_field.fieldid inner join ottocrat_def_org_field on ottocrat_def_org_field.fieldid=ottocrat_field.fieldid where ottocrat_field.tabid=6 and ottocrat_field.displaytype in (1,2,4) and ottocrat_profile2field.visible=0 and ottocrat_def_org_field.visible=0 and ottocrat_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and ottocrat_profile2field.profileid in (". generateQuestionMarks($profileList) .")  group by fieldid";
			    array_push($params1,  $profileList);
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
		$log->debug("Exiting getColumnNames_Acnt method ...");
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

		$rel_table_arr = Array("Contacts"=>"ottocrat_contactdetails","Potentials"=>"ottocrat_potential","Quotes"=>"ottocrat_quotes",
					"SalesOrder"=>"ottocrat_salesorder","Invoice"=>"ottocrat_invoice","Activities"=>"ottocrat_seactivityrel",
					"Documents"=>"ottocrat_senotesrel","Attachments"=>"ottocrat_seattachmentsrel","HelpDesk"=>"ottocrat_troubletickets",
					"Products"=>"ottocrat_seproductsrel","ServiceContracts"=>"ottocrat_servicecontracts","Campaigns"=>"ottocrat_campaignaccountrel",
					"Assets"=>"ottocrat_assets","Project"=>"ottocrat_project");

		$tbl_field_arr = Array("ottocrat_contactdetails"=>"contactid","ottocrat_potential"=>"potentialid","ottocrat_quotes"=>"quoteid",
					"ottocrat_salesorder"=>"salesorderid","ottocrat_invoice"=>"invoiceid","ottocrat_seactivityrel"=>"activityid",
					"ottocrat_senotesrel"=>"notesid","ottocrat_seattachmentsrel"=>"attachmentsid","ottocrat_troubletickets"=>"ticketid",
					"ottocrat_seproductsrel"=>"productid","ottocrat_servicecontracts"=>"servicecontractsid","ottocrat_campaignaccountrel"=>"campaignid",
					"ottocrat_assets"=>"assetsid","ottocrat_project"=>"projectid","ottocrat_payments"=>"paymentsid");

		$entity_tbl_field_arr = Array("ottocrat_contactdetails"=>"accountid","ottocrat_potential"=>"related_to","ottocrat_quotes"=>"accountid",
					"ottocrat_salesorder"=>"accountid","ottocrat_invoice"=>"accountid","ottocrat_seactivityrel"=>"crmid",
					"ottocrat_senotesrel"=>"crmid","ottocrat_seattachmentsrel"=>"crmid","ottocrat_troubletickets"=>"parent_id",
					"ottocrat_seproductsrel"=>"crmid","ottocrat_servicecontracts"=>"sc_related_to","ottocrat_campaignaccountrel"=>"accountid",
					"ottocrat_assets"=>"account","ottocrat_project"=>"linktoaccountscontacts","ottocrat_payments"=>"relatedorganization");

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
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables =  array (
			"Contacts" => array("ottocrat_contactdetails"=>array("accountid","contactid"),"ottocrat_account"=>"accountid"),
			"Potentials" => array("ottocrat_potential"=>array("related_to","potentialid"),"ottocrat_account"=>"accountid"),
			"Quotes" => array("ottocrat_quotes"=>array("accountid","quoteid"),"ottocrat_account"=>"accountid"),
			"SalesOrder" => array("ottocrat_salesorder"=>array("accountid","salesorderid"),"ottocrat_account"=>"accountid"),
			"Invoice" => array("ottocrat_invoice"=>array("accountid","invoiceid"),"ottocrat_account"=>"accountid"),
			"Calendar" => array("ottocrat_seactivityrel"=>array("crmid","activityid"),"ottocrat_account"=>"accountid"),
			"HelpDesk" => array("ottocrat_troubletickets"=>array("parent_id","ticketid"),"ottocrat_account"=>"accountid"),
			"Products" => array("ottocrat_seproductsrel"=>array("crmid","productid"),"ottocrat_account"=>"accountid"),
			"Documents" => array("ottocrat_senotesrel"=>array("crmid","notesid"),"ottocrat_account"=>"accountid"),
			"Campaigns" => array("ottocrat_campaignaccountrel"=>array("accountid","campaignid"),"ottocrat_account"=>"accountid"),
			"Emails" => array("ottocrat_seactivityrel"=>array("crmid","activityid"),"ottocrat_account"=>"accountid"),
		);
		return $rel_tables[$secmodule];
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryPlanner){

		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('ottocrat_crmentityAccounts', array('ottocrat_groupsAccounts', 'ottocrat_usersAccounts', 'ottocrat_lastModifiedByAccounts'));
		$matrix->setDependency('ottocrat_account', array('ottocrat_crmentityAccounts',' ottocrat_accountbillads', 'ottocrat_accountshipads', 'ottocrat_accountscf', 'ottocrat_accountAccounts', 'ottocrat_email_trackAccounts'));

		if (!$queryPlanner->requireTable('ottocrat_account', $matrix)) {
			return '';
		}

         // Activities related to contact should linked to accounts if contact is related to that account
        if($module == "Calendar"){
            // query to get all the contacts related to Accounts
            $relContactsQuery = "SELECT contactid FROM ottocrat_contactdetails as ottocrat_tmpContactCalendar
                        INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_tmpContactCalendar.contactid
                        WHERE ottocrat_tmpContactCalendar.accountid IS NOT NULL AND ottocrat_tmpContactCalendar.accountid !=''
                        AND ottocrat_crmentity.deleted=0";

            $query = " left join ottocrat_cntactivityrel as ottocrat_tmpcntactivityrel ON
                ottocrat_activity.activityid = ottocrat_tmpcntactivityrel.activityid AND
                ottocrat_tmpcntactivityrel.contactid IN ($relContactsQuery)
                left join ottocrat_contactdetails as ottocrat_tmpcontactdetails on ottocrat_tmpcntactivityrel.contactid = ottocrat_tmpcontactdetails.contactid ";
        }else {
            $query = "";
        }

		$query .= $this->getRelationQuery($module,$secmodule,"ottocrat_account","accountid", $queryPlanner);

        if($module == "Calendar"){
            $query .= " OR ottocrat_account.accountid = ottocrat_tmpcontactdetails.accountid " ;
        }
        // End

		if ($queryPlanner->requireTable('ottocrat_crmentityAccounts', $matrix)) {
			$query .= " left join ottocrat_crmentity as ottocrat_crmentityAccounts on ottocrat_crmentityAccounts.crmid=ottocrat_account.accountid and ottocrat_crmentityAccounts.deleted=0";
		}
		if ($queryPlanner->requireTable('ottocrat_accountbillads')) {
			$query .= " left join ottocrat_accountbillads on ottocrat_account.accountid=ottocrat_accountbillads.accountaddressid";
		}
		if ($queryPlanner->requireTable('ottocrat_accountshipads')) {
			$query .= " left join ottocrat_accountshipads on ottocrat_account.accountid=ottocrat_accountshipads.accountaddressid";
		}
		if ($queryPlanner->requireTable('ottocrat_accountscf')) {
			$query .= " left join ottocrat_accountscf on ottocrat_account.accountid = ottocrat_accountscf.accountid";
		}
		if ($queryPlanner->requireTable('ottocrat_accountAccounts', $matrix)) {
			$query .= "	left join ottocrat_account as ottocrat_accountAccounts on ottocrat_accountAccounts.accountid = ottocrat_account.parentid";
		}
		if ($queryPlanner->requireTable('ottocrat_email_track')) {
			$query .= " LEFT JOIN ottocrat_email_track AS ottocrat_email_trackAccounts ON ottocrat_email_trackAccounts .crmid = ottocrat_account.accountid";
		}
		if ($queryPlanner->requireTable('ottocrat_groupsAccounts')) {
			$query .= "	left join ottocrat_groups as ottocrat_groupsAccounts on ottocrat_groupsAccounts.groupid = ottocrat_crmentityAccounts.smownerid";
		}
		if ($queryPlanner->requireTable('ottocrat_usersAccounts')) {
			$query .= " left join ottocrat_users as ottocrat_usersAccounts on ottocrat_usersAccounts.id = ottocrat_crmentityAccounts.smownerid";
		}
		if ($queryPlanner->requireTable('ottocrat_lastModifiedByAccounts')) {
            $query .= " left join ottocrat_users as ottocrat_lastModifiedByAccounts on ottocrat_lastModifiedByAccounts.id = ottocrat_crmentityAccounts.modifiedby ";
		}
        if ($queryPlanner->requireTable("ottocrat_createdbyAccounts")){
			$query .= " left join ottocrat_users as ottocrat_createdbyAccounts on ottocrat_createdbyAccounts.id = ottocrat_crmentityAccounts.smcreatorid ";
		}

		return $query;
	}

	/**
	* Function to get Account hierarchy of the given Account
	* @param  integer   $id      - accountid
	* returns Account hierarchy in array format
	*/
	function getAccountHierarchy($id) {
		global $log, $adb, $current_user;
        $log->debug("Entering getAccountHierarchy(".$id.") method ...");
		require('user_privileges/user_privileges_'.$current_user->id.'.php');

		$tabname = getParentTab();
		$listview_header = Array();
		$listview_entries = array();

		foreach ($this->list_fields_name as $fieldname=>$colname) {
			if(getFieldVisibilityPermission('Accounts', $current_user->id, $colname) == '0') {
				$listview_header[] = getTranslatedString($fieldname);
			}
		}

		$accounts_list = Array();

		// Get the accounts hierarchy from the top most account in the hierarch of the current account, including the current account
		$encountered_accounts = array($id);
		$accounts_list = $this->__getParentAccounts($id, $accounts_list, $encountered_accounts);

		// Get the accounts hierarchy (list of child accounts) based on the current account
		$accounts_list = $this->__getChildAccounts($id, $accounts_list, $accounts_list[$id]['depth']);

		// Create array of all the accounts in the hierarchy
		foreach($accounts_list as $account_id => $account_info) {
			$account_info_data = array();

			$hasRecordViewAccess = (is_admin($current_user)) || (isPermitted('Accounts', 'DetailView', $account_id) == 'yes');

			foreach ($this->list_fields_name as $fieldname=>$colname) {
				// Permission to view account is restricted, avoid showing field values (except account name)
				if(!$hasRecordViewAccess && $colname != 'accountname') {
					$account_info_data[] = '';
				} else if(getFieldVisibilityPermission('Accounts', $current_user->id, $colname) == '0') {
					$data = $account_info[$colname];
					if ($colname == 'accountname') {
						if ($account_id != $id) {
							if($hasRecordViewAccess) {
								$data = '<a href="'.Ottocrat_Request:: encryptLink("index.php?module=Accounts&action=DetailView&record=$account_id&parenttab=$tabname").'">'.$data.'</a>';
							} else {
								$data = '<i>'.$data.'</i>';
							}
						} else {
							$data = '<b>'.$data.'</b>';
						}
						// - to show the hierarchy of the Accounts
						$account_depth = str_repeat(" .. ", $account_info['depth'] * 2);
						$data = $account_depth . $data;
					} else if ($colname == 'website') {
						$data = '<a href="http://'. $data .'" target="_blank">'.$data.'</a>';
					}
					$account_info_data[] = $data;
				}
			}
			$listview_entries[$account_id] = $account_info_data;
		}

		$account_hierarchy = array('header'=>$listview_header,'entries'=>$listview_entries);
        $log->debug("Exiting getAccountHierarchy method ...");
		return $account_hierarchy;
	}

	/**
	* Function to Recursively get all the upper accounts of a given Account
	* @param  integer   $id      		- accountid
	* @param  array   $parent_accounts   - Array of all the parent accounts
	* returns All the parent accounts of the given accountid in array format
	*/
	function __getParentAccounts($id, &$parent_accounts, &$encountered_accounts) {
		global $log, $adb;
        $log->debug("Entering __getParentAccounts(".$id.",".$parent_accounts.") method ...");

		$query = "SELECT parentid FROM ottocrat_account " .
				" INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_account.accountid" .
				" WHERE ottocrat_crmentity.deleted = 0 and ottocrat_account.accountid = ?";
		$params = array($id);

		$res = $adb->pquery($query, $params);

		if ($adb->num_rows($res) > 0 &&
			$adb->query_result($res, 0, 'parentid') != '' && $adb->query_result($res, 0, 'parentid') != 0 &&
			!in_array($adb->query_result($res, 0, 'parentid'),$encountered_accounts)) {

			$parentid = $adb->query_result($res, 0, 'parentid');
			$encountered_accounts[] = $parentid;
			$this->__getParentAccounts($parentid,$parent_accounts,$encountered_accounts);
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_account.*, ottocrat_accountbillads.*," .
				" CASE when (ottocrat_users.user_name not like '') THEN $userNameSql ELSE ottocrat_groups.groupname END as user_name " .
				" FROM ottocrat_account" .
				" INNER JOIN ottocrat_crmentity " .
				" ON ottocrat_crmentity.crmid = ottocrat_account.accountid" .
				" INNER JOIN ottocrat_accountbillads" .
				" ON ottocrat_account.accountid = ottocrat_accountbillads.accountaddressid " .
				" LEFT JOIN ottocrat_groups" .
				" ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid" .
				" LEFT JOIN ottocrat_users" .
				" ON ottocrat_users.id = ottocrat_crmentity.smownerid" .
				" WHERE ottocrat_crmentity.deleted = 0 and ottocrat_account.accountid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);

		$parent_account_info = array();
		$depth = 0;
		$immediate_parentid = $adb->query_result($res, 0, 'parentid');
		if (isset($parent_accounts[$immediate_parentid])) {
			$depth = $parent_accounts[$immediate_parentid]['depth'] + 1;
		}
		$parent_account_info['depth'] = $depth;
		foreach($this->list_fields_name as $fieldname=>$columnname) {
			if ($columnname == 'assigned_user_id') {
				$parent_account_info[$columnname] = $adb->query_result($res, 0, 'user_name');
			} else {
				$parent_account_info[$columnname] = $adb->query_result($res, 0, $columnname);
			}
		}
		$parent_accounts[$id] = $parent_account_info;
        $log->debug("Exiting __getParentAccounts method ...");
		return $parent_accounts;
	}

	/**
	* Function to Recursively get all the child accounts of a given Account
	* @param  integer   $id      		- accountid
	* @param  array   $child_accounts   - Array of all the child accounts
	* @param  integer   $depth          - Depth at which the particular account has to be placed in the hierarchy
	* returns All the child accounts of the given accountid in array format
	*/
	function __getChildAccounts($id, &$child_accounts, $depth) {
		global $log, $adb;
        $log->debug("Entering __getChildAccounts(".$id.",".$child_accounts.",".$depth.") method ...");

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_account.*, ottocrat_accountbillads.*," .
				" CASE when (ottocrat_users.user_name not like '') THEN $userNameSql ELSE ottocrat_groups.groupname END as user_name " .
				" FROM ottocrat_account" .
				" INNER JOIN ottocrat_crmentity " .
				" ON ottocrat_crmentity.crmid = ottocrat_account.accountid" .
				" INNER JOIN ottocrat_accountbillads" .
				" ON ottocrat_account.accountid = ottocrat_accountbillads.accountaddressid " .
				" LEFT JOIN ottocrat_groups" .
				" ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid" .
				" LEFT JOIN ottocrat_users" .
				" ON ottocrat_users.id = ottocrat_crmentity.smownerid" .
				" WHERE ottocrat_crmentity.deleted = 0 and parentid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);

		$num_rows = $adb->num_rows($res);

		if ($num_rows > 0) {
			$depth = $depth + 1;
			for($i=0;$i<$num_rows;$i++) {
				$child_acc_id = $adb->query_result($res, $i, 'accountid');
				if(array_key_exists($child_acc_id,$child_accounts)) {
					continue;
				}
				$child_account_info = array();
				$child_account_info['depth'] = $depth;
				foreach($this->list_fields_name as $fieldname=>$columnname) {
					if ($columnname == 'assigned_user_id') {
						$child_account_info[$columnname] = $adb->query_result($res, $i, 'user_name');
					} else {
						$child_account_info[$columnname] = $adb->query_result($res, $i, $columnname);
					}
				}
				$child_accounts[$child_acc_id] = $child_account_info;
				$this->__getChildAccounts($child_acc_id, $child_accounts, $depth);
			}
		}
        $log->debug("Exiting __getChildAccounts method ...");
		return $child_accounts;
	}

	// Function to unlink the dependent records of the given record by id
	function unlinkDependencies($module, $id) {
		global $log;

		//Deleting Account related Potentials.
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
		//Backup deleted Account related Potentials.
		$params = array($id, RB_RECORD_UPDATED, 'ottocrat_crmentity', 'deleted', 'crmid', implode(",", $pot_ids_list));
		$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);

		//Deleting Account related Quotes.
		$quo_q = 'SELECT ottocrat_crmentity.crmid FROM ottocrat_crmentity
			INNER JOIN ottocrat_quotes ON ottocrat_crmentity.crmid=ottocrat_quotes.quoteid
			INNER JOIN ottocrat_account ON ottocrat_account.accountid=ottocrat_quotes.accountid
			WHERE ottocrat_crmentity.deleted=0 AND ottocrat_quotes.accountid=?';
		$quo_res = $this->db->pquery($quo_q, array($id));
		$quo_ids_list = array();
		for($k=0;$k < $this->db->num_rows($quo_res);$k++)
		{
			$quo_id = $this->db->query_result($quo_res,$k,"crmid");
			$quo_ids_list[] = $quo_id;
			$sql = 'UPDATE ottocrat_crmentity SET deleted = 1 WHERE crmid = ?';
			$this->db->pquery($sql, array($quo_id));
		}
		//Backup deleted Account related Quotes.
		$params = array($id, RB_RECORD_UPDATED, 'ottocrat_crmentity', 'deleted', 'crmid', implode(",", $quo_ids_list));
		$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);

		//Backup Contact-Account Relation
		$con_q = 'SELECT contactid FROM ottocrat_contactdetails WHERE accountid = ?';
		$con_res = $this->db->pquery($con_q, array($id));
		if ($this->db->num_rows($con_res) > 0) {
			$con_ids_list = array();
			for($k=0;$k < $this->db->num_rows($con_res);$k++)
			{
				$con_ids_list[] = $this->db->query_result($con_res,$k,"contactid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'ottocrat_contactdetails', 'accountid', 'contactid', implode(",", $con_ids_list));
			$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		}
		//Deleting Contact-Account Relation.
		$con_q = 'UPDATE ottocrat_contactdetails SET accountid = 0 WHERE accountid = ?';
		$this->db->pquery($con_q, array($id));

		//Backup Trouble Tickets-Account Relation
		$tkt_q = 'SELECT ticketid FROM ottocrat_troubletickets WHERE parent_id = ?';
		$tkt_res = $this->db->pquery($tkt_q, array($id));
		if ($this->db->num_rows($tkt_res) > 0) {
			$tkt_ids_list = array();
			for($k=0;$k < $this->db->num_rows($tkt_res);$k++)
			{
				$tkt_ids_list[] = $this->db->query_result($tkt_res,$k,"ticketid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'ottocrat_troubletickets', 'parent_id', 'ticketid', implode(",", $tkt_ids_list));
			$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		}
		//Deleting Trouble Tickets-Account Relation.
		$tt_q = 'UPDATE ottocrat_troubletickets SET parent_id = 0 WHERE parent_id = ?';
		$this->db->pquery($tt_q, array($id));

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Campaigns') {
			$sql = 'DELETE FROM ottocrat_campaignaccountrel WHERE accountid=? AND campaignid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} else if($return_module == 'Products') {
			$sql = 'DELETE FROM ottocrat_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			$sql = 'DELETE FROM ottocrat_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = $this->db;

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Products')
				$adb->pquery("insert into ottocrat_seproductsrel values(?,?,?)", array($crmid, $with_crmid, $module));
			elseif($with_module == 'Campaigns') {
				$checkResult = $adb->pquery('SELECT 1 FROM ottocrat_campaignaccountrel WHERE campaignid = ? AND accountid = ?',
												array($with_crmid, $crmid));
				if($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$adb->pquery("insert into ottocrat_campaignaccountrel values(?,?,1)", array($with_crmid, $crmid));
			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

	function getListButtons($app_strings,$mod_strings = false) {
		$list_buttons = Array();

		if(isPermitted('Accounts','Delete','') == 'yes') {
			$list_buttons['del'] = $app_strings[LBL_MASS_DELETE];
		}
		if(isPermitted('Accounts','EditView','') == 'yes') {
			$list_buttons['mass_edit'] = $app_strings[LBL_MASS_EDIT];
			$list_buttons['c_owner'] = $app_strings[LBL_CHANGE_OWNER];
		}
		if(isPermitted('Emails','EditView','') == 'yes') {
			$list_buttons['s_mail'] = $app_strings[LBL_SEND_MAIL_BUTTON];
		}
		// mailer export
		if(isPermitted('Accounts','Export','') == 'yes') {
			$list_buttons['mailer_exp'] = $mod_strings[LBL_MAILER_EXPORT];
		}
		// end of mailer export
		return $list_buttons;
	}

	/* Function to get attachments in the related list of accounts module */
	function get_attachments($id, $cur_tab_id, $rel_tab_id, $actions = false) {

		global $currentModule, $app_strings, $singlepane_view;
		$this_module = $currentModule;
		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = vtlib_toSingular($related_module);
		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab")."','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
						"<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
						" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true'){
			$returnset = "&return_module=$this_module&return_action=DetailView&return_id=$id";
		} else {
			$returnset = "&return_module=$this_module&return_action=CallRelatedList&return_id=$id";
		}

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=> 'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,
				'Documents' ActivityType,ottocrat_attachments.type  FileType,crm2.modifiedtime lastmodified,ottocrat_crmentity.modifiedtime,
				ottocrat_seattachmentsrel.attachmentsid attachmentsid, ottocrat_notes.notesid crmid, ottocrat_notes.notecontent description,ottocrat_notes.*
				from ottocrat_notes
				INNER JOIN ottocrat_senotesrel ON ottocrat_senotesrel.notesid= ottocrat_notes.notesid
				LEFT JOIN ottocrat_notescf ON ottocrat_notescf.notesid= ottocrat_notes.notesid
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid= ottocrat_notes.notesid and ottocrat_crmentity.deleted=0
				INNER JOIN ottocrat_crmentity crm2 ON crm2.crmid=ottocrat_senotesrel.crmid
				LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				LEFT JOIN ottocrat_seattachmentsrel ON ottocrat_seattachmentsrel.crmid =ottocrat_notes.notesid
				LEFT JOIN ottocrat_attachments ON ottocrat_seattachmentsrel.attachmentsid = ottocrat_attachments.attachmentsid
				LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid= ottocrat_users.id
				WHERE crm2.crmid IN (".$entityIds.")";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		return $return_value;
	}

	/**
	 * Function to handle the dependents list for the module.
	 * NOTE: UI type '10' is used to stored the references to other modules for a given record.
	 * These dependent records can be retrieved through this function.
	 * For eg: A trouble ticket can be related to an Account or a Contact.
	 * From a given Contact/Account if we need to fetch all such dependent trouble tickets, get_dependents_list function can be used.
	 */
	function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions = false) {

		global $currentModule, $app_strings, $singlepane_view, $current_user;

		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = 'SINGLE_' . $related_module;
		$button = '';

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true')
			$returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		else
			$returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";

		$return_value = null;
		$dependentFieldSql = $this->db->pquery("SELECT tabid, fieldname, columnname FROM ottocrat_field WHERE uitype='10' AND" .
				" fieldid IN (SELECT fieldid FROM ottocrat_fieldmodulerel WHERE relmodule=? AND module=?)", array($currentModule, $related_module));
		$numOfFields = $this->db->num_rows($dependentFieldSql);

		if ($numOfFields > 0) {
			$dependentColumn = $this->db->query_result($dependentFieldSql, 0, 'columnname');
			$dependentField = $this->db->query_result($dependentFieldSql, 0, 'fieldname');

			$button .= '<input type="hidden" name="' . $dependentColumn . '" id="' . $dependentColumn . '" value="' . $id . '">';
			$button .= '<input type="hidden" name="' . $dependentColumn . '_type" id="' . $dependentColumn . '_type" value="' . $currentModule . '">';
			if ($actions) {
				if (is_string($actions))
					$actions = explode(',', strtoupper($actions));
				if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes'
						&& getFieldVisibilityPermission($related_module, $current_user->id, $dependentField, 'readwrite') == '0') {
					$button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname, $related_module) . "' class='crmbutton small create'" .
							" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
							" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname, $related_module) . "'>&nbsp;";
				}
			}

			$entityIds = $this->getRelatedContactsIds();
			array_push($entityIds, $id);
			$entityIds = implode(',', $entityIds);

			$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name','last_name' => 'ottocrat_users.last_name'), 'Users');

			$query = "SELECT ottocrat_crmentity.*, $other->table_name.*";
			$query .= ", CASE WHEN (ottocrat_users.user_name NOT LIKE '') THEN $userNameSql ELSE ottocrat_groups.groupname END AS user_name";

			$more_relation = '';
			if (!empty($other->related_tables)) {
				foreach ($other->related_tables as $tname => $relmap) {
					$query .= ", $tname.*";

					// Setup the default JOIN conditions if not specified
					if (empty($relmap[1]))
						$relmap[1] = $other->table_name;
					if (empty($relmap[2]))
						$relmap[2] = $relmap[0];
					$more_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
				}
			}

			$query .= " FROM $other->table_name";
			$query .= " INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = $other->table_name.$other->table_index";
			$query .= $more_relation;
			$query .= " LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid";
			$query .= " LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";
			$query .= " WHERE ottocrat_crmentity.deleted = 0 AND $other->table_name.$dependentColumn IN (".$entityIds.")";

			$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);
		}
		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	/**
	 * Function to handle the related list for the module.
	 * NOTE: Ottocrat_Module::setRelatedList sets reference to this function in ottocrat_relatedlists table
	 * if function name is not explicitly specified.
	 */
	function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions = false) {

		global $currentModule, $app_strings, $singlepane_view;

		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = 'SINGLE_' . $related_module;

		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' " .
						" type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab")."','test','width=640,height=602,resizable=0,scrollbars=0');\"" .
						" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module, $related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
						"<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
						" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname, $related_module) . "'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true') {
			$returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		} else {
			$returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";
		}

		$more_relation = '';
		if (!empty($other->related_tables)) {
			foreach ($other->related_tables as $tname => $relmap) {
				$query .= ", $tname.*";

				// Setup the default JOIN conditions if not specified
				if (empty($relmap[1]))
					$relmap[1] = $other->table_name;
				if (empty($relmap[2]))
					$relmap[2] = $relmap[0];
				$more_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');

		$query = "SELECT ottocrat_crmentity.*, $other->table_name.*,
				CASE WHEN (ottocrat_users.user_name NOT LIKE '') THEN $userNameSql ELSE ottocrat_groups.groupname END AS user_name FROM $other->table_name
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = $other->table_name.$other->table_index
				INNER JOIN ottocrat_crmentityrel ON (ottocrat_crmentityrel.relcrmid = ottocrat_crmentity.crmid OR ottocrat_crmentityrel.crmid = ottocrat_crmentity.crmid)
				$more_relation
				LEFT  JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
				LEFT  JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
				WHERE ottocrat_crmentity.deleted = 0 AND (ottocrat_crmentityrel.crmid IN (" .$entityIds. ") OR ottocrat_crmentityrel.relcrmid IN (". $entityIds . "))";

		$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	/* Function to get related contact ids for an account record*/
	function getRelatedContactsIds($id = null) {
		global $adb;
		if($id ==null)
		$id = $this->id;
		$entityIds = array();
		$query = 'SELECT contactid FROM ottocrat_contactdetails
				INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_contactdetails.contactid
				WHERE ottocrat_contactdetails.accountid = ? AND ottocrat_crmentity.deleted = 0';
		$accountContacts = $adb->pquery($query, array($id));
		$numOfContacts = $adb->num_rows($accountContacts);
		if($accountContacts && $numOfContacts > 0) {
			for($i=0; $i < $numOfContacts; ++$i) {
				array_push($entityIds, $adb->query_result($accountContacts, $i, 'contactid'));
			}
		}
		return $entityIds;
	}
}

?>
