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
 * $Header: /advent/projects/wesat/ottocrat_crm/sugarcrm/modules/Activities/Activity.php,v 1.26 2005/03/26 10:42:13 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('modules/Calendar/RenderRelatedListUI.php');
require_once('modules/Calendar/CalendarCommon.php');

// Task is used to store customer information.
class Activity extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "ottocrat_activity";
	var $table_index= 'activityid';
	var $reminder_table = 'ottocrat_activity_reminder';
	var $tab_name = Array('ottocrat_crmentity','ottocrat_activity','ottocrat_activitycf');

	var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_activity'=>'activityid','ottocrat_seactivityrel'=>'activityid','ottocrat_cntactivityrel'=>'activityid','ottocrat_salesmanactivityrel'=>'activityid','ottocrat_activity_reminder'=>'activity_id','ottocrat_recurringevents'=>'activityid','ottocrat_activitycf'=>'activityid');

	var $column_fields = Array();
	var $sortby_fields = Array('subject','due_date','date_start','smownerid','activitytype','lastname');	//Sorting is added for due date and start date

	// This is used to retrieve related ottocrat_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'assigned_user_id', 'contactname', 'contact_phone', 'contact_email', 'parent_name');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_activitycf', 'activityid');

	// This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
       'Close'=>Array('activity'=>'status'),
       'Type'=>Array('activity'=>'activitytype'),
       'Subject'=>Array('activity'=>'subject'),
       'Related to'=>Array('seactivityrel'=>'parent_id'),
       'Start Date'=>Array('activity'=>'date_start'),
       'Start Time'=>Array('activity','time_start'),
       'End Date'=>Array('activity'=>'due_date'),
       'End Time'=>Array('activity','time_end'),
       'Recurring Type'=>Array('recurringevents'=>'recurringtype'),
       'Assigned To'=>Array('crmentity'=>'smownerid'),
       'Contact Name'=>Array('contactdetails'=>'lastname')
       );

       var $range_fields = Array(
		'name',
		'date_modified',
		'start_date',
		'id',
		'status',
		'date_due',
		'time_start',
		'description',
		'contact_name',
		'priority',
		'duehours',
		'dueminutes',
		'location'
	   );


       var $list_fields_name = Array(
       'Close'=>'status',
       'Type'=>'activitytype',
       'Subject'=>'subject',
       'Contact Name'=>'lastname',
       'Related to'=>'parent_id',
       'Start Date & Time'=>'date_start',
       'End Date & Time'=>'due_date',
	   'Recurring Type'=>'recurringtype',
       'Assigned To'=>'assigned_user_id',
       'Start Date'=>'date_start',
       'Start Time'=>'time_start',
       'End Date'=>'due_date',
       'End Time'=>'time_end');

       var $list_link_field= 'subject';

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'due_date';
	var $default_sort_order = 'ASC';

	//var $groupTable = Array('ottocrat_activitygrouprelation','activityid');

	function Activity() {
		$this->log = LoggerManager::getLogger('Calendar');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Calendar');
	}

	function save_module($module)
	{
		global $adb;
        //Handling module specific save
		//Insert into seactivity rel
		$insertion_mode = $this->mode;
        if(isset($this->column_fields['parent_id']) && $this->column_fields['parent_id'] != '')
		{
			$this->insertIntoEntityTable("ottocrat_seactivityrel", $module);
		}
		elseif($this->column_fields['parent_id']=='' && $insertion_mode=="edit")
		{
			$this->deleteRelation("ottocrat_seactivityrel");
		}

        $recordId = $this->id;
		if(isset($_REQUEST['contactidlist']) && $_REQUEST['contactidlist'] != '') {
			$adb->pquery( 'DELETE from ottocrat_cntactivityrel WHERE activityid = ?', array($recordId));

			$contactIdsList = explode (';', $_REQUEST['contactidlist']);
			$count = count($contactIdsList);

			$sql = 'INSERT INTO ottocrat_cntactivityrel VALUES ';
			for($i=0; $i<$count; $i++) {
				$sql .= " ($contactIdsList[$i], $recordId)";
				if ($i != $count - 1) {
					$sql .= ',';
				}
			}
			$adb->pquery($sql, array());
		} else if ($_REQUEST['contactidlist'] == '' && $insertion_mode == "edit") {
        	$adb->pquery('DELETE FROM ottocrat_cntactivityrel WHERE activityid = ?', array($recordId));
        }
        
        //Insert into cntactivity rel
        if(isset($this->column_fields['contact_id']) && $this->column_fields['contact_id'] != '' && !isset($_REQUEST['contactidlist']))
        {
                $this->insertIntoEntityTable('ottocrat_cntactivityrel', $module);
        }
        elseif($this->column_fields['contact_id'] =='' && $insertion_mode=="edit" && !isset($_REQUEST['contactidlist']))
        {
                $this->deleteRelation('ottocrat_cntactivityrel');
        }
        
		$recur_type='';
		if(($recur_type == "--None--" || $recur_type == '') && $this->mode == "edit")
		{
			$sql = 'delete  from ottocrat_recurringevents where activityid=?';
			$adb->pquery($sql, array($this->id));
		}
		//Handling for recurring type
		//Insert into ottocrat_recurring event table
		if(isset($this->column_fields['recurringtype']) && $this->column_fields['recurringtype']!='' && $this->column_fields['recurringtype']!='--None--')
		{
			$recur_type = trim($this->column_fields['recurringtype']);
			$recur_data = getrecurringObjValue();
			if(is_object($recur_data))
	      			$this->insertIntoRecurringTable($recur_data);
		}

		//Insert into ottocrat_activity_remainder table

			$this->insertIntoReminderTable('ottocrat_activity_reminder',$module,"");

		//Handling for invitees
			$selected_users_string =  $_REQUEST['inviteesid'];
			$invitees_array = explode(';',$selected_users_string);
			$this->insertIntoInviteeTable($module,$invitees_array);

		//Inserting into sales man activity rel
		$this->insertIntoSmActivityRel($module);

		$this->insertIntoActivityReminderPopup($module);
	}


	/** Function to insert values in ottocrat_activity_reminder_popup table for the specified module
  	  * @param $cbmodule -- module:: Type varchar
 	 */
	function insertIntoActivityReminderPopup($cbmodule) {

		global $adb;

		$cbrecord = $this->id;
		unset($_SESSION['next_reminder_time']);
		if(isset($cbmodule) && isset($cbrecord)) {
			$cbdate = getValidDBInsertDateValue($this->column_fields['date_start']);
			$cbtime = $this->column_fields['time_start'];

			$reminder_query = "SELECT reminderid FROM ottocrat_activity_reminder_popup WHERE semodule = ? and recordid = ?";
			$reminder_params = array($cbmodule, $cbrecord);
			$reminderidres = $adb->pquery($reminder_query, $reminder_params);

			$reminderid = null;
			if($adb->num_rows($reminderidres) > 0) {
				$reminderid = $adb->query_result($reminderidres, 0, "reminderid");
			}

			if(isset($reminderid)) {
                $current_date = new DateTime();
                $record_date = new DateTime($cbdate.' '.$cbtime);

                $current = $current_date->format('Y-m-d H:i:s');
                $record = $record_date->format('Y-m-d H:i:s');
                if(strtotime($record) > strtotime($current)){
                    $callback_query = "UPDATE ottocrat_activity_reminder_popup set status = 0, date_start = ?, time_start = ? WHERE reminderid = ?";
                    $callback_params = array($cbdate, $cbtime, $reminderid);
                }
			} else {
				$callback_query = "INSERT INTO ottocrat_activity_reminder_popup (recordid, semodule, date_start, time_start) VALUES (?,?,?,?)";
				$callback_params = array($cbrecord, $cbmodule, $cbdate, $cbtime);
			}

            if($callback_query)
                $adb->pquery($callback_query, $callback_params);
		}
	}


	/** Function to insert values in ottocrat_activity_remainder table for the specified module,
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
 	 */
	function insertIntoReminderTable($table_name,$module,$recurid)
	{
	 	global $log;
		$log->info("in insertIntoReminderTable  ".$table_name."    module is  ".$module);
		if($_REQUEST['set_reminder'] == 'Yes')
		{
			unset($_SESSION['next_reminder_time']);
			$log->debug("set reminder is set");
			$rem_days = $_REQUEST['remdays'];
			$log->debug("rem_days is ".$rem_days);
			$rem_hrs = $_REQUEST['remhrs'];
			$log->debug("rem_hrs is ".$rem_hrs);
			$rem_min = $_REQUEST['remmin'];
			$log->debug("rem_minutes is ".$rem_min);
			$reminder_time = $rem_days * 24 * 60 + $rem_hrs * 60 + $rem_min;
			$log->debug("reminder_time is ".$reminder_time);
			if ($recurid == "")
			{
				if($_REQUEST['mode'] == 'edit')
				{
					$this->activity_reminder($this->id,$reminder_time,0,$recurid,'edit');
				}
				else
				{
					$this->activity_reminder($this->id,$reminder_time,0,$recurid,'');
				}
			}
			else
			{
				$this->activity_reminder($this->id,$reminder_time,0,$recurid,'');
			}
		}
		elseif($_REQUEST['set_reminder'] == 'No')
		{
			$this->activity_reminder($this->id,'0',0,$recurid,'delete');
		}
	}


	// Code included by Jaguar - starts
	/** Function to insert values in ottocrat_recurringevents table for the specified tablename,module
  	  * @param $recurObj -- Recurring Object:: Type varchar
 	 */
function insertIntoRecurringTable(& $recurObj)
{
	global $log,$adb;
	$st_date = $recurObj->startdate->get_DB_formatted_date();
	$end_date = $recurObj->enddate->get_DB_formatted_date();
	if(!empty($recurObj->recurringenddate)){
		$recurringenddate = $recurObj->recurringenddate->get_DB_formatted_date();
	}
	$type = $recurObj->getRecurringType();
	$flag="true";

	if($_REQUEST['mode'] == 'edit')
	{
		$activity_id=$this->id;

		$sql='select min(recurringdate) AS min_date,max(recurringdate) AS max_date, recurringtype, activityid from ottocrat_recurringevents where activityid=? group by activityid, recurringtype';
		$result = $adb->pquery($sql, array($activity_id));
		$noofrows = $adb->num_rows($result);
		for($i=0; $i<$noofrows; $i++)
		{
			$recur_type_b4_edit = $adb->query_result($result,$i,"recurringtype");
			$date_start_b4edit = $adb->query_result($result,$i,"min_date");
			$end_date_b4edit = $adb->query_result($result,$i,"max_date");
		}
		if(($st_date == $date_start_b4edit) && ($end_date==$end_date_b4edit) && ($type == $recur_type_b4_edit))
		{
			if($_REQUEST['set_reminder'] == 'Yes')
			{
				$sql = 'delete from ottocrat_activity_reminder where activity_id=?';
				$adb->pquery($sql, array($activity_id));
				$sql = 'delete  from ottocrat_recurringevents where activityid=?';
				$adb->pquery($sql, array($activity_id));
				$flag="true";
			}
			elseif($_REQUEST['set_reminder'] == 'No')
			{
				$sql = 'delete  from ottocrat_activity_reminder where activity_id=?';
				$adb->pquery($sql, array($activity_id));
				$flag="false";
			}
			else
				$flag="false";
		}
		else
		{
			$sql = 'delete from ottocrat_activity_reminder where activity_id=?';
			$adb->pquery($sql, array($activity_id));
			$sql = 'delete  from ottocrat_recurringevents where activityid=?';
			$adb->pquery($sql, array($activity_id));
		}
	}

	$recur_freq = $recurObj->getRecurringFrequency();
	$recurringinfo = $recurObj->getDBRecurringInfoString();

	if($flag=="true") {
		$max_recurid_qry = 'select max(recurringid) AS recurid from ottocrat_recurringevents;';
		$result = $adb->pquery($max_recurid_qry, array());
		$noofrows = $adb->num_rows($result);
		$recur_id = 0;
		if($noofrows > 0) {
			$recur_id = $adb->query_result($result,0,"recurid");
		}
		$current_id =$recur_id+1;
		$recurring_insert = "insert into ottocrat_recurringevents values (?,?,?,?,?,?,?)";
		$rec_params = array($current_id, $this->id, $st_date, $type, $recur_freq, $recurringinfo,$recurringenddate);
		$adb->pquery($recurring_insert, $rec_params);
		unset($_SESSION['next_reminder_time']);
		if($_REQUEST['set_reminder'] == 'Yes') {
			$this->insertIntoReminderTable("ottocrat_activity_reminder",$module,$current_id,'');
		}
	}
}


	/** Function to insert values in ottocrat_invitees table for the specified module,tablename ,invitees_array
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
	  * @param $invitees_array Array
 	 */
	function insertIntoInviteeTable($module,$invitees_array)
	{
		global $log,$adb;
		$log->debug("Entering insertIntoInviteeTable(".$module.",".$invitees_array.") method ...");
		if($this->mode == 'edit'){
			$sql = "delete from ottocrat_invitees where activityid=?";
			$adb->pquery($sql, array($this->id));
		}
		foreach($invitees_array as $inviteeid)
		{
			if($inviteeid != '')
			{
				$query="insert into ottocrat_invitees values(?,?)";
				$adb->pquery($query, array($this->id, $inviteeid));
			}
		}
		$log->debug("Exiting insertIntoInviteeTable method ...");

	}


	/** Function to insert values in ottocrat_salesmanactivityrel table for the specified module
  	  * @param $module -- module:: Type varchar
 	 */

  	function insertIntoSmActivityRel($module)
  	{
    		global $adb;
    		global $current_user;
    		if($this->mode == 'edit'){
      			$sql = "delete from ottocrat_salesmanactivityrel where activityid=?";
      			$adb->pquery($sql, array($this->id));
    		}

		$user_sql = $adb->pquery("select count(*) as count from ottocrat_users where id=?", array($this->column_fields['assigned_user_id']));
    	if($adb->query_result($user_sql, 0, 'count') != 0) {
		$sql_qry = "insert into ottocrat_salesmanactivityrel (smid,activityid) values(?,?)";
    		$adb->pquery($sql_qry, array($this->column_fields['assigned_user_id'], $this->id));

		if(isset($_REQUEST['inviteesid']) && $_REQUEST['inviteesid']!='')
		{
			$selected_users_string =  $_REQUEST['inviteesid'];
			$invitees_array = explode(';',$selected_users_string);
			foreach($invitees_array as $inviteeid)
			{
				if($inviteeid != '')
				{
					$resultcheck = $adb->pquery("select * from ottocrat_salesmanactivityrel where activityid=? and smid=?",array($this->id,$inviteeid));
					if($adb->num_rows($resultcheck) != 1){
						$query="insert into ottocrat_salesmanactivityrel values(?,?)";
						$adb->pquery($query, array($inviteeid, $this->id));
					}
				}
			}
		}
	}
}

	/**
	 *
	 * @param String $tableName
	 * @return String
	 */
	public function getJoinClause($tableName) {
        if($tableName == "ottocrat_activity_reminder")
            return 'LEFT JOIN';
		return parent::getJoinClause($tableName);
	}


	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	function getSortOrder()
	{
		global $log;
		$log->debug("Entering getSortOrder() method ...");
		if(isset($_REQUEST['sorder']))
			$sorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		else
			$sorder = (($_SESSION['ACTIVITIES_SORT_ORDER'] != '')?($_SESSION['ACTIVITIES_SORT_ORDER']):($this->default_sort_order));
		$log->debug("Exiting getSortOrder method ...");
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'subject')
	 */
	function getOrderBy()
	{
		global $log;
		$log->debug("Entering getOrderBy() method ...");

		$use_default_order_by = '';
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (isset($_REQUEST['order_by']))
			$order_by = $this->db->sql_escape_string($_REQUEST['order_by']);
		else
			$order_by = (($_SESSION['ACTIVITIES_ORDER_BY'] != '')?($_SESSION['ACTIVITIES_ORDER_BY']):($use_default_order_by));
		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
	}
	// Mike Crowe Mod --------------------------------------------------------



//Function Call for Related List -- Start
	/**
	 * Function to get Activity related Contacts
	 * @param  integer   $id      - activityid
	 * returns related Contacts record in array format
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

		$returnset = '&return_module='.$this_module.'&return_action=DetailView&activity_mode=Events&return_id='.$id;

		$search_string = '';
		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab$search_string")."','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		$query = 'select ottocrat_users.user_name,ottocrat_contactdetails.accountid,ottocrat_contactdetails.contactid, ottocrat_contactdetails.firstname,ottocrat_contactdetails.lastname, ottocrat_contactdetails.department, ottocrat_contactdetails.title, ottocrat_contactdetails.email, ottocrat_contactdetails.phone, ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_crmentity.modifiedtime from ottocrat_contactdetails inner join ottocrat_cntactivityrel on ottocrat_cntactivityrel.contactid=ottocrat_contactdetails.contactid inner join ottocrat_crmentity on ottocrat_crmentity.crmid = ottocrat_contactdetails.contactid left join ottocrat_users on ottocrat_users.id = ottocrat_crmentity.smownerid left join ottocrat_groups on ottocrat_groups.groupid = ottocrat_crmentity.smownerid where ottocrat_cntactivityrel.activityid='.$id.' and ottocrat_crmentity.deleted=0';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/**
	 * Function to get Activity related Users
	 * @param  integer   $id      - activityid
	 * returns related Users record in array format
	 */

	function get_users($id) {
		global $log;
                $log->debug("Entering get_contacts(".$id.") method ...");
		global $app_strings;

		$focus = new Users();

		$button = '<input title="Change" accessKey="" tabindex="2" type="button" class="crmbutton small edit"
					value="'.getTranslatedString('LBL_SELECT_USER_BUTTON_LABEL').'" name="button" LANGUAGE=javascript
					onclick=\'return window.open("'.Ottocrat_Request:: encryptLink('index.php?module=Users&return_module=Calendar&return_action={$return_modname}&activity_mode=Events&action=Popup&popuptype=detailview&form=EditView&form_submit=true&select=enable&return_id='.$id.'&recordid='.$id).'","test","width=640,height=525,resizable=0,scrollbars=0")\';>';

		$returnset = '&return_module=Calendar&return_action=CallRelatedList&return_id='.$id;

		$query = 'SELECT ottocrat_users.id, ottocrat_users.first_name,ottocrat_users.last_name, ottocrat_users.user_name, ottocrat_users.email1, ottocrat_users.email2, ottocrat_users.status, ottocrat_users.is_admin, ottocrat_user2role.roleid, ottocrat_users.secondaryemail, ottocrat_users.phone_home, ottocrat_users.phone_work, ottocrat_users.phone_mobile, ottocrat_users.phone_other, ottocrat_users.phone_fax,ottocrat_activity.date_start,ottocrat_activity.due_date,ottocrat_activity.time_start,ottocrat_activity.duration_hours,ottocrat_activity.duration_minutes from ottocrat_users inner join ottocrat_salesmanactivityrel on ottocrat_salesmanactivityrel.smid=ottocrat_users.id  inner join ottocrat_activity on ottocrat_activity.activityid=ottocrat_salesmanactivityrel.activityid inner join ottocrat_user2role on ottocrat_user2role.userid=ottocrat_users.id where ottocrat_activity.activityid='.$id;

		$return_data = GetRelatedList('Calendar','Users',$focus,$query,$button,$returnset);

		if($return_data == null) $return_data = Array();
		$return_data['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_users method ...");
		return $return_data;
	}

	/**
         * Function to get activities for given criteria
	 * @param   string   $criteria     - query string
	 * returns  activity records in array format($list) or null value
         */
  	function get_full_list($criteria) {
	 	global $log;
		$log->debug("Entering get_full_list(".$criteria.") method ...");
	    $query = "select ottocrat_crmentity.crmid,ottocrat_crmentity.smownerid,ottocrat_crmentity.setype, ottocrat_activity.*,
	    		ottocrat_contactdetails.lastname, ottocrat_contactdetails.firstname, ottocrat_contactdetails.contactid
	    		from ottocrat_activity
	    		inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
	    		left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid= ottocrat_activity.activityid
	    		left join ottocrat_contactdetails on ottocrat_contactdetails.contactid= ottocrat_cntactivityrel.contactid
	    		left join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
	    		WHERE ottocrat_crmentity.deleted=0 ".$criteria;
    	$result =& $this->db->query($query);

    if($this->db->getRowCount($result) > 0){

      // We have some data.
      while ($row = $this->db->fetchByAssoc($result)) {
        foreach($this->list_fields_name as $field)
        {
          if (isset($row[$field])) {
            $this->$field = $row[$field];
          }
          else {
            $this->$field = '';
          }
        }
        $list[] = $this;
      }
    }
    if (isset($list))
    	{
		$log->debug("Exiting get_full_list method ...");
	    return $list;
	}
	else
	{
		$log->debug("Exiting get_full_list method ...");
	    return null;
	}

  }


//calendarsync
    /**
     * Function to get meeting count
     * @param  string   $user_name        - User Name
     * return  integer  $row["count(*)"]  - count
     */
    function getCount_Meeting($user_name)
	{
		global $log;
	        $log->debug("Entering getCount_Meeting(".$user_name.") method ...");
      $query = "select count(*) from ottocrat_activity inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid inner join ottocrat_salesmanactivityrel on ottocrat_salesmanactivityrel.activityid=ottocrat_activity.activityid inner join ottocrat_users on ottocrat_users.id=ottocrat_salesmanactivityrel.smid where user_name=? and ottocrat_crmentity.deleted=0 and ottocrat_activity.activitytype='Meeting'";
      $result = $this->db->pquery($query, array($user_name),true,"Error retrieving contacts count");
      $rows_found =  $this->db->getRowCount($result);
      $row = $this->db->fetchByAssoc($result, 0);
	$log->debug("Exiting getCount_Meeting method ...");
      return $row["count(*)"];
    }

    function get_calendars($user_name,$from_index,$offset)
    {
	    global $log;
            $log->debug("Entering get_calendars(".$user_name.",".$from_index.",".$offset.") method ...");
		$query = "select ottocrat_activity.location as location,ottocrat_activity.duration_hours as duehours, ottocrat_activity.duration_minutes as dueminutes,ottocrat_activity.time_start as time_start, ottocrat_activity.subject as name,ottocrat_crmentity.modifiedtime as date_modified, ottocrat_activity.date_start start_date,ottocrat_activity.activityid as id,ottocrat_activity.status as status, ottocrat_crmentity.description as description, ottocrat_activity.priority as ottocrat_priority, ottocrat_activity.due_date as date_due ,ottocrat_contactdetails.firstname cfn, ottocrat_contactdetails.lastname cln from ottocrat_activity inner join ottocrat_salesmanactivityrel on ottocrat_salesmanactivityrel.activityid=ottocrat_activity.activityid inner join ottocrat_users on ottocrat_users.id=ottocrat_salesmanactivityrel.smid left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid=ottocrat_activity.activityid left join ottocrat_contactdetails on ottocrat_contactdetails.contactid=ottocrat_cntactivityrel.contactid inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid where user_name='" .$user_name ."' and ottocrat_crmentity.deleted=0 and ottocrat_activity.activitytype='Meeting' limit " .$from_index ."," .$offset;
	$log->debug("Exiting get_calendars method ...");
	    return $this->process_list_query1($query);
    }
//calendarsync
	/**
	 * Function to get task count
	 * @param  string   $user_name        - User Name
	 * return  integer  $row["count(*)"]  - count
	 */
    function getCount($user_name)
    {
	    global $log;
            $log->debug("Entering getCount(".$user_name.") method ...");
        $query = "select count(*) from ottocrat_activity inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid inner join ottocrat_salesmanactivityrel on ottocrat_salesmanactivityrel.activityid=ottocrat_activity.activityid inner join ottocrat_users on ottocrat_users.id=ottocrat_salesmanactivityrel.smid where user_name=? and ottocrat_crmentity.deleted=0 and ottocrat_activity.activitytype='Task'";
        $result = $this->db->pquery($query,array($user_name), true,"Error retrieving contacts count");
        $rows_found =  $this->db->getRowCount($result);
        $row = $this->db->fetchByAssoc($result, 0);

	$log->debug("Exiting getCount method ...");
        return $row["count(*)"];
    }

    /**
     * Function to get list of task for user with given limit
     * @param  string   $user_name        - User Name
     * @param  string   $from_index       - query string
     * @param  string   $offset           - query string
     * returns tasks in array format
     */
    function get_tasks($user_name,$from_index,$offset)
    {
	global $log;
        $log->debug("Entering get_tasks(".$user_name.",".$from_index.",".$offset.") method ...");
	 $query = "select ottocrat_activity.subject as name,ottocrat_crmentity.modifiedtime as date_modified, ottocrat_activity.date_start start_date,ottocrat_activity.activityid as id,ottocrat_activity.status as status, ottocrat_crmentity.description as description, ottocrat_activity.priority as priority, ottocrat_activity.due_date as date_due ,ottocrat_contactdetails.firstname cfn, ottocrat_contactdetails.lastname cln from ottocrat_activity inner join ottocrat_salesmanactivityrel on ottocrat_salesmanactivityrel.activityid=ottocrat_activity.activityid inner join ottocrat_users on ottocrat_users.id=ottocrat_salesmanactivityrel.smid left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid=ottocrat_activity.activityid left join ottocrat_contactdetails on ottocrat_contactdetails.contactid=ottocrat_cntactivityrel.contactid inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid where user_name='" .$user_name ."' and ottocrat_crmentity.deleted=0 and ottocrat_activity.activitytype='Task' limit " .$from_index ."," .$offset;
	 $log->debug("Exiting get_tasks method ...");
    return $this->process_list_query1($query);

    }

    /**
     * Function to process the activity list query
     * @param  string   $query     - query string
     * return  array    $response  - activity lists
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
            $task = Array();
              for($index = 0 , $row = $this->db->fetchByAssoc($result, $index); $row && $index <$rows_found;$index++, $row = $this->db->fetchByAssoc($result, $index))

             {
                foreach($this->range_fields as $columnName)
                {
					if (isset($row[$columnName])) {
						if($columnName == 'time_start'){
							$startDate = new DateTimeField($row['date_start'].' '.
									$row[$columnName]);
							$task[$columnName] = $startDate->getDBInsertTimeValue();
						}else{
							$task[$columnName] = $row[$columnName];
						}
                    }
                    else
                    {
                            $task[$columnName] = "";
                    }
	            }

                $task[contact_name] = return_name($row, 'cfn', 'cln');

                    $list[] = $task;
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

    	/**
	 * Function to get reminder for activity
	 * @param  integer   $activity_id     - activity id
	 * @param  string    $reminder_time   - reminder time
	 * @param  integer   $reminder_sent   - 0 or 1
	 * @param  integer   $recurid         - recuring eventid
	 * @param  string    $remindermode    - string like 'edit'
	 */
	function activity_reminder($activity_id,$reminder_time,$reminder_sent=0,$recurid,$remindermode='')
	{
		global $log;
		$log->debug("Entering ottocrat_activity_reminder(".$activity_id.",".$reminder_time.",".$reminder_sent.",".$recurid.",".$remindermode.") method ...");
		//Check for ottocrat_activityid already present in the reminder_table
		$query_exist = "SELECT activity_id FROM ".$this->reminder_table." WHERE activity_id = ?";
		$result_exist = $this->db->pquery($query_exist, array($activity_id));

		if($remindermode == 'edit')
		{
			if($this->db->num_rows($result_exist) > 0)
			{
				$query = "UPDATE ".$this->reminder_table." SET";
				$query .=" reminder_sent = ?, reminder_time = ? WHERE activity_id =?";
				$params = array($reminder_sent, $reminder_time, $activity_id);
			}
			else
			{
				$query = "INSERT INTO ".$this->reminder_table." VALUES (?,?,?,?)";
				$params = array($activity_id, $reminder_time, 0, $recurid);
			}
		}
		elseif(($remindermode == 'delete') && ($this->db->num_rows($result_exist) > 0))
		{
			$query = "DELETE FROM ".$this->reminder_table." WHERE activity_id = ?";
			$params = array($activity_id);
		}
		else
		{
			if($_REQUEST['set_reminder'] == 'Yes'){
				$query = "INSERT INTO ".$this->reminder_table." VALUES (?,?,?,?)";
				$params = array($activity_id, $reminder_time, 0, $recurid);
			}
		}
		if(!empty($query)){
			$this->db->pquery($query,$params,true,"Error in processing ottocrat_table $this->reminder_table");
		}
		$log->debug("Exiting ottocrat_activity_reminder method ...");
	}

	//Used for ottocratCRM Outlook Add-In
	/**
 	* Function to get tasks to display in outlookplugin
 	* @param   string    $username     -  User name
 	* return   string    $query        -  sql query
 	*/
	function get_tasksforol($username)
	{
		global $log,$adb;
		$log->debug("Entering get_tasksforol(".$username.") method ...");
		global $current_user;
		require_once("modules/Users/Users.php");
		$seed_user=new Users();
		$user_id=$seed_user->retrieve_user_id($username);
		$current_user=$seed_user;
		$current_user->retrieve_entity_info($user_id, 'Users');
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
  		{
    		$sql1 = "select tablename,columnname from ottocrat_field where tabid=9 and tablename <> 'ottocrat_recurringevents' and tablename <> 'ottocrat_activity_reminder' and ottocrat_field.presence in (0,2)";
			$params1 = array();
  		}else
  	{
    	$profileList = getCurrentUserProfileList();
    	$sql1 = "select tablename,columnname from ottocrat_field inner join ottocrat_profile2field on ottocrat_profile2field.fieldid=ottocrat_field.fieldid inner join ottocrat_def_org_field on ottocrat_def_org_field.fieldid=ottocrat_field.fieldid where ottocrat_field.tabid=9 and tablename <> 'ottocrat_recurringevents' and tablename <> 'ottocrat_activity_reminder' and ottocrat_field.displaytype in (1,2,4,3) and ottocrat_profile2field.visible=0 and ottocrat_def_org_field.visible=0 and ottocrat_field.presence in (0,2)";
		$params1 = array();
		if (count($profileList) > 0) {
  			$sql1 .= " and ottocrat_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
			array_push($params1, $profileList);
		}
  	}
  	$result1 = $adb->pquery($sql1,$params1);
  	for($i=0;$i < $adb->num_rows($result1);$i++)
  	{
		$permitted_lists[] = $adb->query_result($result1,$i,'tablename');
      	$permitted_lists[] = $adb->query_result($result1,$i,'columnname');
      	/*if($adb->query_result($result1,$i,'columnname') == "parentid")
      	{
        	$permitted_lists[] = 'ottocrat_account';
        	$permitted_lists[] = 'accountname';
      	}*/
  		}
		$permitted_lists = array_chunk($permitted_lists,2);
		$column_table_lists = array();
		for($i=0;$i < count($permitted_lists);$i++)
		{
	   		$column_table_lists[] = implode(".",$permitted_lists[$i]);
  		}

		$query = "select ottocrat_activity.activityid as taskid, ".implode(',',$column_table_lists)." from ottocrat_activity inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
			 inner join ottocrat_users on ottocrat_users.id = ottocrat_crmentity.smownerid
			 left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid=ottocrat_activity.activityid
			 left join ottocrat_contactdetails on ottocrat_contactdetails.contactid=ottocrat_cntactivityrel.contactid
			 left join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
			 where ottocrat_users.user_name='".$username."' and ottocrat_crmentity.deleted=0 and ottocrat_activity.activitytype='Task'";
		$log->debug("Exiting get_tasksforol method ...");
		return $query;
	}

	/**
 	* Function to get calendar query for outlookplugin
 	* @param   string    $username     -  User name                                                                            * return   string    $query        -  sql query                                                                            */
	function get_calendarsforol($user_name)
	{
		global $log,$adb;
		$log->debug("Entering get_calendarsforol(".$user_name.") method ...");
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
    		$sql1 = "select tablename,columnname from ottocrat_field where tabid=9 and tablename <> 'ottocrat_recurringevents' and tablename <> 'ottocrat_activity_reminder' and ottocrat_field.presence in (0,2)";
  			$params1 = array();
  		}else
  		{
    		$profileList = getCurrentUserProfileList();
    		$sql1 = "select tablename,columnname from ottocrat_field inner join ottocrat_profile2field on ottocrat_profile2field.fieldid=ottocrat_field.fieldid inner join ottocrat_def_org_field on ottocrat_def_org_field.fieldid=ottocrat_field.fieldid where ottocrat_field.tabid=9 and tablename <> 'ottocrat_recurringevents' and tablename <> 'ottocrat_activity_reminder' and ottocrat_field.displaytype in (1,2,4,3) and ottocrat_profile2field.visible=0 and ottocrat_def_org_field.visible=0 and ottocrat_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and ottocrat_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
				array_push($params1,$profileList);
			}
  		}
  		$result1 = $adb->pquery($sql1, $params1);
  		for($i=0;$i < $adb->num_rows($result1);$i++)
  		{
			$permitted_lists[] = $adb->query_result($result1,$i,'tablename');
      		$permitted_lists[] = $adb->query_result($result1,$i,'columnname');
      		if($adb->query_result($result1,$i,'columnname') == "date_start")
      		{
        		$permitted_lists[] = 'ottocrat_activity';
        		$permitted_lists[] = 'time_start';
      		}
      		if($adb->query_result($result1,$i,'columnname') == "due_date")
      		{
				$permitted_lists[] = 'ottocrat_activity';
        		$permitted_lists[] = 'time_end';
      		}
  		}
		$permitted_lists = array_chunk($permitted_lists,2);
		$column_table_lists = array();
		for($i=0;$i < count($permitted_lists);$i++)
		{
	   		$column_table_lists[] = implode(".",$permitted_lists[$i]);
  		}

	  	$query = "select ottocrat_activity.activityid as clndrid, ".implode(',',$column_table_lists)." from ottocrat_activity
				inner join ottocrat_salesmanactivityrel on ottocrat_salesmanactivityrel.activityid=ottocrat_activity.activityid
				inner join ottocrat_users on ottocrat_users.id=ottocrat_salesmanactivityrel.smid
				left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid=ottocrat_activity.activityid
				left join ottocrat_contactdetails on ottocrat_contactdetails.contactid=ottocrat_cntactivityrel.contactid
				left join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
				inner join ottocrat_crmentity on ottocrat_crmentity.crmid=ottocrat_activity.activityid
				where ottocrat_users.user_name='".$user_name."' and ottocrat_crmentity.deleted=0 and ottocrat_activity.activitytype='Meeting'";
		$log->debug("Exiting get_calendarsforol method ...");
		return $query;
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;

		$sql = 'DELETE FROM ottocrat_activity_reminder WHERE activity_id=?';
		$this->db->pquery($sql, array($id));

		$sql = 'DELETE FROM ottocrat_recurringevents WHERE activityid=?';
		$this->db->pquery($sql, array($id));

		$sql = 'DELETE FROM ottocrat_cntactivityrel WHERE activityid = ?';
		$this->db->pquery($sql, array($id));

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Contacts') {
			$sql = 'DELETE FROM ottocrat_cntactivityrel WHERE contactid = ? AND activityid = ?';
			$this->db->pquery($sql, array($return_id, $id));
		} elseif($return_module == 'HelpDesk') {
			$sql = 'DELETE FROM ottocrat_seactivityrel WHERE crmid = ? AND activityid = ?';
			$this->db->pquery($sql, array($return_id, $id));
		} elseif($return_module == 'Accounts') {
			$sql = 'DELETE FROM ottocrat_seactivityrel WHERE crmid = ? AND activityid = ?';
			$this->db->pquery($sql, array($return_id, $id));
			$sql = 'DELETE FROM ottocrat_cntactivityrel WHERE activityid = ? AND contactid IN	(SELECT contactid from ottocrat_contactdetails where accountid=?)';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			$sql='DELETE FROM ottocrat_seactivityrel WHERE activityid=?';
			$this->db->pquery($sql, array($id));

			$sql = 'DELETE FROM ottocrat_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	/**
	 * this function sets the status flag of activity to true or false depending on the status passed to it
	 * @param string $status - the status of the activity flag to set
	 * @return:: true if successful; false otherwise
	 */
	function setActivityReminder($status){
		global $adb;
		if($status == "on"){
			$flag = 0;
		}elseif($status == "off"){
			$flag = 1;
		}else{
			return false;
		}
		$sql = "update ottocrat_activity_reminder_popup set status=1 where recordid=?";
		$adb->pquery($sql, array($this->id));
		return true;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Contacts" => array("ottocrat_cntactivityrel"=>array("activityid","contactid"),"ottocrat_activity"=>"activityid"),
			"Leads" => array("ottocrat_seactivityrel"=>array("activityid","crmid"),"ottocrat_activity"=>"activityid"),
			"Accounts" => array("ottocrat_seactivityrel"=>array("activityid","crmid"),"ottocrat_activity"=>"activityid"),
			"Potentials" => array("ottocrat_seactivityrel"=>array("activityid","crmid"),"ottocrat_activity"=>"activityid"),
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
		$matrix->setDependency('ottocrat_crmentityCalendar',array('ottocrat_groupsCalendar','ottocrat_usersCalendar','ottocrat_lastModifiedByCalendar'));
		$matrix->setDependency('ottocrat_cntactivityrel',array('ottocrat_contactdetailsCalendar'));
		$matrix->setDependency('ottocrat_seactivityrel',array('ottocrat_crmentityRelCalendar'));
		$matrix->setDependency('ottocrat_crmentityRelCalendar',array('ottocrat_accountRelCalendar','ottocrat_leaddetailsRelCalendar','ottocrat_potentialRelCalendar',
								'ottocrat_quotesRelCalendar','ottocrat_purchaseorderRelCalendar','ottocrat_invoiceRelCalendar',
								'ottocrat_salesorderRelCalendar','ottocrat_troubleticketsRelCalendar','ottocrat_campaignRelCalendar'));
		$matrix->setDependency('ottocrat_activity',array('ottocrat_crmentityCalendar','ottocrat_cntactivityrel','ottocrat_activitycf',
								'ottocrat_seactivityrel','ottocrat_activity_reminder','ottocrat_recurringevents'));

		if (!$queryPlanner->requireTable('ottocrat_activity', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"ottocrat_activity","activityid", $queryPlanner);

		if ($queryPlanner->requireTable("ottocrat_crmentityCalendar",$matrix)){
			$query .=" left join ottocrat_crmentity as ottocrat_crmentityCalendar on ottocrat_crmentityCalendar.crmid=ottocrat_activity.activityid and ottocrat_crmentityCalendar.deleted=0";
		}
		if ($queryPlanner->requireTable("ottocrat_cntactivityrel",$matrix)){
			$query .=" 	left join ottocrat_cntactivityrel on ottocrat_cntactivityrel.activityid= ottocrat_activity.activityid";
		}
		if ($queryPlanner->requireTable("ottocrat_contactdetailsCalendar")){
			$query .=" 	left join ottocrat_contactdetails as ottocrat_contactdetailsCalendar on ottocrat_contactdetailsCalendar.contactid= ottocrat_cntactivityrel.contactid";
		}
		if ($queryPlanner->requireTable("ottocrat_activitycf")){
			$query .=" 	left join ottocrat_activitycf on ottocrat_activitycf.activityid = ottocrat_activity.activityid";
		}
		if ($queryPlanner->requireTable("ottocrat_seactivityrel",$matrix)){
			$query .=" 	left join ottocrat_seactivityrel on ottocrat_seactivityrel.activityid = ottocrat_activity.activityid";
		}
		if ($queryPlanner->requireTable("ottocrat_activity_reminder")){
			$query .=" 	left join ottocrat_activity_reminder on ottocrat_activity_reminder.activity_id = ottocrat_activity.activityid";
		}
		if ($queryPlanner->requireTable("ottocrat_recurringevents")){
			$query .=" 	left join ottocrat_recurringevents on ottocrat_recurringevents.activityid = ottocrat_activity.activityid";
		}
		if ($queryPlanner->requireTable("ottocrat_crmentityRelCalendar",$matrix)){
			$query .=" 	left join ottocrat_crmentity as ottocrat_crmentityRelCalendar on ottocrat_crmentityRelCalendar.crmid = ottocrat_seactivityrel.crmid and ottocrat_crmentityRelCalendar.deleted=0";
		}
		if ($queryPlanner->requireTable("ottocrat_accountRelCalendar")){
			$query .=" 	left join ottocrat_account as ottocrat_accountRelCalendar on ottocrat_accountRelCalendar.accountid=ottocrat_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("ottocrat_leaddetailsRelCalendar")){
			$query .=" 	left join ottocrat_leaddetails as ottocrat_leaddetailsRelCalendar on ottocrat_leaddetailsRelCalendar.leadid = ottocrat_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("ottocrat_potentialRelCalendar")){
			$query .=" 	left join ottocrat_potential as ottocrat_potentialRelCalendar on ottocrat_potentialRelCalendar.potentialid = ottocrat_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("ottocrat_quotesRelCalendar")){
			$query .=" 	left join ottocrat_quotes as ottocrat_quotesRelCalendar on ottocrat_quotesRelCalendar.quoteid = ottocrat_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("ottocrat_purchaseorderRelCalendar")){
			$query .=" 	left join ottocrat_purchaseorder as ottocrat_purchaseorderRelCalendar on ottocrat_purchaseorderRelCalendar.purchaseorderid = ottocrat_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("ottocrat_invoiceRelCalendar")){
			$query .=" 	left join ottocrat_invoice as ottocrat_invoiceRelCalendar on ottocrat_invoiceRelCalendar.invoiceid = ottocrat_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("ottocrat_salesorderRelCalendar")){
			$query .=" 	left join ottocrat_salesorder as ottocrat_salesorderRelCalendar on ottocrat_salesorderRelCalendar.salesorderid = ottocrat_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("ottocrat_troubleticketsRelCalendar")){
			$query .=" left join ottocrat_troubletickets as ottocrat_troubleticketsRelCalendar on ottocrat_troubleticketsRelCalendar.ticketid = ottocrat_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("ottocrat_campaignRelCalendar")){
			$query .=" 	left join ottocrat_campaign as ottocrat_campaignRelCalendar on ottocrat_campaignRelCalendar.campaignid = ottocrat_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("ottocrat_groupsCalendar")){
			$query .=" left join ottocrat_groups as ottocrat_groupsCalendar on ottocrat_groupsCalendar.groupid = ottocrat_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("ottocrat_usersCalendar")){
			$query .=" 	left join ottocrat_users as ottocrat_usersCalendar on ottocrat_usersCalendar.id = ottocrat_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("ottocrat_lastModifiedByCalendar")){
			$query .="  left join ottocrat_users as ottocrat_lastModifiedByCalendar on ottocrat_lastModifiedByCalendar.id = ottocrat_crmentityCalendar.modifiedby ";
		}
        if ($queryPlanner->requireTable("ottocrat_createdbyCalendar")){
			$query .= " left join ottocrat_users as ottocrat_createdbyCalendar on ottocrat_createdbyCalendar.id = ottocrat_crmentityCalendar.smcreatorid ";
		}
		return $query;
	}

	public function getNonAdminAccessControlQuery($module, $user,$scope='') {
		require('user_privileges/user_privileges_'.$user->id.'.php');
		require('user_privileges/sharing_privileges_'.$user->id.'.php');
		$query = ' ';
		$tabId = getTabid($module);
		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2]
				== 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u'.$user->id.'_t'.$tabId;
			$sharingRuleInfoVariable = $module.'_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;
			$this->setupTemporaryTable($tableName, $sharedTabId, $user,
					$current_user_parent_role_seq, $current_user_groups);

			$sharedUsers = $this->getListViewAccessibleUsers($user->id);
            // we need to include group id's in $sharedUsers list to get the current user's group records
            if($current_user_groups){
                $sharedUsers = $sharedUsers.','. implode(',',$current_user_groups);
            }
			$query = " INNER JOIN $tableName $tableName$scope ON ($tableName$scope.id = ".
					"ottocrat_crmentity$scope.smownerid and $tableName$scope.shared=0 and $tableName$scope.id IN ($sharedUsers)) ";
		}
		return $query;
	}

    /**
     * To get non admin access query for Reports generation
     * @param type $tableName
     * @param type $tabId
     * @param type $user
     * @param type $parent_roles
     * @param type $groups
     * @return $query
     */
    public function getReportsNonAdminAccessControlQuery($tableName, $tabId, $user, $parent_roles,$groups){
        $sharedUsers = $this->getListViewAccessibleUsers($user->id);
        $this->setupTemporaryTable($tableName, $tabId, $user, $parent_roles,$groups);
        $query = "SELECT id FROM $tableName WHERE $tableName.shared=0 AND $tableName.id IN ($sharedUsers)";
        return $query;
    }

	protected function setupTemporaryTable($tableName, $tabId, $user, $parentRole, $userGroups) {
		$module = null;
		if (!empty($tabId)) {
			$module = getTabname($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $user, $parentRole, $userGroups);
		$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key, shared ".
			"int(1) default 0) ignore ".$query;
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array());
		if(is_object($result)) {
			$query = "REPLACE INTO $tableName (id) SELECT userid as id FROM ottocrat_sharedcalendar WHERE sharedid = ?";
			$result = $db->pquery($query, array($user->id));

			//For newly created users, entry will not be there in ottocrat_sharedcalendar table
			//so, consider the users whose having the calendarsharedtype is public
			$query = "REPLACE INTO $tableName (id) SELECT id FROM ottocrat_users WHERE calendarsharedtype = ?";
			$result = $db->pquery($query, array('public'));

			if(is_object($result)) {
				return true;
			}
		}
		return false;
	}

	protected function getListViewAccessibleUsers($sharedid) {
		$db = PearDatabase::getInstance();;
		$query = "SELECT ottocrat_users.id as userid FROM ottocrat_sharedcalendar
					RIGHT JOIN ottocrat_users ON ottocrat_sharedcalendar.userid=ottocrat_users.id and status= 'Active'
					WHERE sharedid=? OR (ottocrat_users.status='Active' AND ottocrat_users.calendarsharedtype='public' AND ottocrat_users.id <> ?);";
			$result = $db->pquery($query, array($sharedid, $sharedid));
			$rows = $db->num_rows($result);
		if($db->num_rows($result)!=0)
		{
			for($j=0;$j<$db->num_rows($result);$j++) {
				$userid[] = $db->query_result($result,$j,'userid');
			}
			$shared_ids = implode (",",$userid);
		}
		$userid[] = $sharedid;
		$shared_ids = implode (",",$userid);
		return $shared_ids;
	}
}
?>
