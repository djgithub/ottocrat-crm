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

// Faq is used to store ottocrat_faq information.
class Faq extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "ottocrat_faq";
	var $table_index= 'id';
	//fix for Custom Field for FAQ 
        var $tab_name = Array('ottocrat_crmentity','ottocrat_faq','ottocrat_faqcf');  
        var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_faq'=>'id','ottocrat_faqcomments'=>'faqid','ottocrat_faqcf'=>'faqid');  
        var $customFieldTable = Array('ottocrat_faqcf', 'faqid');  

	var $entity_table = "ottocrat_crmentity";

	var $column_fields = Array();

	var $sortby_fields = Array('question','category','id');

	// This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
				'FAQ Id'=>Array('faq'=>'id'),
				'Question'=>Array('faq'=>'question'),
				'Category'=>Array('faq'=>'category'),
				'Product Name'=>Array('faq'=>'product_id'),
				'Created Time'=>Array('crmentity'=>'createdtime'),
				'Modified Time'=>Array('crmentity'=>'modifiedtime')
				);

	var $list_fields_name = Array(
				        'FAQ Id'=>'',
				        'Question'=>'question',
				        'Category'=>'faqcategories',
				        'Product Name'=>'product_id',
						'Created Time'=>'createdtime',
						'Modified Time'=>'modifiedtime'
				      );
	var $list_link_field= 'question';

	var $search_fields = Array(
				'Account Name'=>Array('account'=>'accountname'),
				'City'=>Array('accountbillads'=>'bill_city'),
				);

	var $search_fields_name = Array(
				        'Account Name'=>'accountname',
				        'City'=>'bill_city',
				      );

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'id';
	var $default_sort_order = 'DESC';

	var $mandatory_fields = Array('question','faq_answer','createdtime' ,'modifiedtime');

	// For Alphabetical search
	var $def_basicsearch_col = 'question';

	/**	Constructor which will set the column_fields in this object
	 */
	function Faq() {
		$this->log =LoggerManager::getLogger('faq');
		$this->log->debug("Entering Faq() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Faq');
		$this->log->debug("Exiting Faq method ...");
	}

	function save_module($module)
	{
		//Inserting into Faq comment table
		$this->insertIntoFAQCommentTable('ottocrat_faqcomments', $module);

	}


	/** Function to insert values in ottocrat_faqcomments table for the specified module,
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
 	 */
	function insertIntoFAQCommentTable($table_name, $module)
	{
		global $log;
		$log->info("in insertIntoFAQCommentTable  ".$table_name."    module is  ".$module);
        	global $adb;

        	$current_time = $adb->formatDate(date('Y-m-d H:i:s'), true);

		if($this->column_fields['comments'] != '')
			$comment = $this->column_fields['comments'];
		else
			$comment = $_REQUEST['comments'];

		if($comment != '')
		{
			$params = array('', $this->id, from_html($comment), $current_time);
			$sql = "insert into ottocrat_faqcomments values(?, ?, ?, ?)";
			$adb->pquery($sql, $params);
		}
	}


	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */
	function generateReportsQuery($module, $queryPlanner) {
	 			$moduletable = $this->table_name;
	 			$moduleindex = $this->table_index;

	 			$query = "from $moduletable
					inner join ottocrat_crmentity on ottocrat_crmentity.crmid=$moduletable.$moduleindex
					left join ottocrat_products as ottocrat_products$module on ottocrat_products$module.productid = ottocrat_faq.product_id
					left join ottocrat_groups as ottocrat_groups$module on ottocrat_groups$module.groupid = ottocrat_crmentity.smownerid
					left join ottocrat_users as ottocrat_users$module on ottocrat_users$module.id = ottocrat_crmentity.smownerid
					left join ottocrat_groups on ottocrat_groups.groupid = ottocrat_crmentity.smownerid
					left join ottocrat_users on ottocrat_users.id = ottocrat_crmentity.smownerid
                    left join ottocrat_users as ottocrat_lastModifiedBy".$module." on ottocrat_lastModifiedBy".$module.".id = ottocrat_crmentity.modifiedby";
	            return $query;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Documents" => array("ottocrat_senotesrel"=>array("crmid","notesid"),"ottocrat_faq"=>"id"),
		);
		return $rel_tables[$secmodule];
	}

	function clearSingletonSaveFields() {
		$this->column_fields['comments'] = '';
	}

}
?>
