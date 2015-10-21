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
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */

require_once('include/utils/utils.php'); //new
require_once('include/utils/RecurringType.php');
require_once('include/utils/EmailTemplate.php');
require_once 'include/QueryGenerator/QueryGenerator.php';
require_once 'include/ListView/ListViewController.php';
require_once 'includes/runtime/Cache.php';

function is_admin($user) {
	return Ottocrat_Functions::userIsAdministrator($user);
}

function parse_calendardate($local_format) {
	return Ottocrat_Functions::currentUserJSDateFormat($local_format);
}

function from_html($string, $encode = true) {
	return Ottocrat_Functions::fromHTML($string, $encode);
}

function fck_from_html($string) {
	return Ottocrat_Functions::fromHTML_FCK($string);
}

function popup_from_html($string, $encode = true) {
	return Ottocrat_Functions::fromHTML_Popup($string, $encode);
}

function fetchCurrency($id) {
	return Ottocrat_Functions::userCurrencyId($id);
}

function getCurrencyName($currencyid, $show_symbol = true) {
	return Ottocrat_Functions::getCurrencyName($currencyid, $show_symbol);
}

function getTabid($module) {
	return Ottocrat_Functions::getModuleId($module);
	}

function getFieldid($tabid, $fieldname, $onlyactive = true) {
	return Ottocrat_Functions::getModuleFieldId($tabid, $fieldname, $onlyactive);
}

function getTabOwnedBy($module) {
	return Ottocrat_Functions::getModuleOwner($module);
}

function getSalesEntityType($crmid) {
	return Ottocrat_Functions::getCRMRecordType($crmid);
}

function getAccountName($account_id) {
	return Ottocrat_Functions::getCRMRecordLabel($account_id);
}

function getProductName($product_id) {
	return Ottocrat_Functions::getCRMRecordLabel($product_id);
}

function getPotentialName($potential_id) {
	return Ottocrat_Functions::getCRMRecordLabel($potential_id);
}

function getContactName($contact_id) {
	return Ottocrat_Functions::getCRMRecordLabel($contact_id);
}

function getFullNameFromQResult($result, $row_count, $module) {
	return Ottocrat_Deprecated::getFullNameFromQResult($result, $row_count, $module);
}

function getFullNameFromArray($module, $fieldValues) {
	return Ottocrat_Deprecated::getFullNameFromArray($module, $fieldValues);
}

function getCampaignName($campaign_id) {
	return Ottocrat_Functions::getCRMRecordLabel($campaign_id);
}

function getVendorName($vendor_id) {
	return Ottocrat_Functions::getCRMRecordLabel($vendor_id);
}

function getQuoteName($quote_id) {
	return Ottocrat_Functions::getCRMRecordLabel($quote_id);
}

function getPriceBookName($pricebookid) {
	return Ottocrat_Functions::getCRMRecordLabel($pricebookid);
}

function getSoName($so_id) {
	return Ottocrat_Functions::getCRMRecordLabel($so_id);
}

function getGroupName($groupid) {
	return Ottocrat_Functions::getGroupName($groupid);
}

function getUserName($userid) {
	return Ottocrat_Functions::getUserName($userid);
}

function getUserFullName($userid) {
	return Ottocrat_Functions::getUserRecordLabel($userid);
}

function getParentName($parent_id) {
	return Ottocrat_Functions::getCRMRecordLabel($parent_id);
	}

function getValidDisplayDate($cur_date_val) {
	return Ottocrat_Functions::currentUserDisplayDate($cur_date_val);
}

function getNewDisplayDate() {
	return Ottocrat_Functions::currentUserDisplayDateNew();
}

/** This function returns the conversion rate and ottocrat_currency symbol
 * in array format for a given id.
 * param $id - ottocrat_currency id.
 */
function getCurrencySymbolandCRate($id) {
	return Ottocrat_Functions::getCurrencySymbolandRate($id);
}

/** This function returns the terms and condition from the database.
 * Takes no param and the return type is text.
 */
function getTermsandConditions() {
	return Ottocrat_Functions::getInventoryTermsAndCondition();
}

/** This function returns a string with removed new line character, single quote, and back slash double quoute.
 * param $str - string to be converted.
 */
function br2nl($str) {
	return Ottocrat_Functions::br2nl($str);
}

/**
 * This function is used to get the blockid of the customblock for a given module.
 * Takes the input parameter as $tabid - module tabid and $label - custom label
 * This returns string type value
 */
function getBlockId($tabid, $label) {
	return Ottocrat_Deprecated::getBlockId($tabid, $label);
}

/**
 * This function is used to get the Parent Tab name for a given module.
 * Takes the input parameter as $module - module name
 * This returns value string type
 */
function getParentTabFromModule($module) {
	return Ottocrat_Deprecated::getParentTabFromModule($module);
}

/**
 * This function is used to get the Parent Tab name for a given module.
 * Takes no parameter but gets the ottocrat_parenttab value from form request
 * This returns value string type
 */
function getParentTab() {
	return Ottocrat_Deprecated::getParentTab();
}

/**
 * This function is used to set the Object values from the REQUEST values.
 * @param  object reference $focus - reference of the object
 */
function setObjectValuesFromRequest($focus) {
	return Ottocrat_Deprecated::copyValuesFromRequest($focus);
}

function create_tab_data_file() {
	return Ottocrat_Deprecated::createModuleMetaFile();
}

/**
 * Function to write the ottocrat_parenttabid and name to a flat file parent_tabdata.txt so that the data
 * is obtained from the file instead of repeated queries
 * returns null
 */
function create_parenttab_data_file() {
	return Ottocrat_Deprecated::createModuleGroupMetaFile();
}

function getEntityName($module, $ids_list, $compute=true) {
	if ($compute) {
		return Ottocrat_Functions::computeCRMRecordLabels($module, $ids_list);
	} else {
		return Ottocrat_Functions::getCRMRecordLabels($module, $ids_list);
	}
}

/**
 * 	This function is used to decide the File Storage Path in where we will upload the file in the server.
 * 	return string $filepath  - filepath inwhere the file should be stored in the server will be return
 */
function decideFilePath() {
	return Ottocrat_Functions::initStorageFileDirectory();
}

/**
 * 	This function is used to check whether the attached file is a image file or not
 * 	@param string $file_details  - ottocrat_files array which contains all the uploaded file details
 * 	return string $save_image - true or false. if the image can be uploaded then true will return otherwise false.
 */
function validateImageFile($file_details) {
	return Ottocrat_Functions::validateImage($file_details);
}

/**
 * 	This function is used to get the Email Template Details like subject and content for particular template.
 * 	@param integer $templateid  - Template Id for an Email Template
 * 	return array $returndata - Returns Subject, Body of Template of the the particular email template.
 */
function getTemplateDetails($templateid) {
	return Ottocrat_Deprecated::getTemplateDetails($templateid);
}

/**
 * 	This function is used to merge the Template Details with the email description
 *  @param string $description  -body of the mail(ie template)
 * 	@param integer $tid  - Id of the entity
 *  @param string $parent_type - module of the entity
 * 	return string $description - Returns description, merged with the input template.
 */
function getMergedDescription($description, $id, $parent_type) {
	return Ottocrat_Functions::getMergedDescription($description, $id, $parent_type);
}

/** 	Function used to retrieve a single field value from database
 * 	@param string $tablename - tablename from which we will retrieve the field value
 * 	@param string $fieldname - fieldname to which we want to get the value from database
 * 	@param string $idname	 - idname which is the name of the entity id in the table like, inoviceid, quoteid, etc.,
 * 	@param int    $id	 - entity id
 * 	return string $fieldval  - field value of the needed fieldname from database will be returned
 */
function getSingleFieldValue($tablename, $fieldname, $idname, $id) {
	return Ottocrat_Functions::getSingleFieldValue($tablename, $fieldname, $idname, $id);
}

/** 	Function used to retrieve the announcements from database
 * 	The function accepts no argument and returns the announcements
 * 	return string $announcement  - List of announments for the CRM users
 */
function get_announcements() {
	return Ottocrat_Deprecated::getAnnouncements();
}

/**
 *  Function to get recurring info depending on the recurring type
 *  return  $recurObj       - Object of class RecurringType
 */
function getrecurringObjValue() {
	return Ottocrat_Functions::getRecurringObjValue();
}

function getTranslatedString($str, $module = 'Ottocrat') {
	return Ottocrat_Functions::getTranslatedString($str, $module);
}

/**
 * Get translated currency name string.
 * @param String $str - input currency name
 * @return String $str - translated currency name
 */
function getTranslatedCurrencyString($str) {
	return Ottocrat_Deprecated::getTranslatedCurrencyString($str);
	}

function getTicketComments($ticketid) {
	return Ottocrat_Functions::getTicketComments($ticketid);
}

function makeRandomPassword() {
	return Ottocrat_Functions::generateRandomPassword();
}

/**
 * This function is used to get cvid of default "all" view for any module.
 * @return a cvid of a module
 */
function getCvIdOfAll($module) {
	return Ottocrat_Deprecated::getIdOfCustomViewByNameAll($module);
}

/** gives the option  to display  the tagclouds or not for the current user
 * * @param $id -- user id:: Type integer
 * * @returns true or false in $tag_cloud_view
 * * Added to provide User based Tagcloud
 * */
function getTagCloudView($id = "") {
	return Ottocrat_Functions::getTagCloudView($id);
}

/** Stores the option in database to display  the tagclouds or not for the current user
 * * @param $id -- user id:: Type integer
 * * Added to provide User based Tagcloud
 * */
function SaveTagCloudView($id = "") {
	return Ottocrat_Deprecated::SaveTagCloudView($id);
}

/**     function used to change the Type of Data for advanced filters in custom view and Reports
 * *     @param string $table_name - tablename value from field table
 * *     @param string $column_nametable_name - columnname value from field table
 * *     @param string $type_of_data - current type of data of the field. It is to return the same TypeofData
 * *            if the  field is not matched with the $new_field_details array.
 * *     return string $type_of_data - If the string matched with the $new_field_details array then the Changed
 * *	       typeofdata will return, else the same typeofdata will return.
 * *
 * *     EXAMPLE: If you have a field entry like this:
 * *
 * * 		fieldlabel         | typeofdata | tablename            | columnname       |
 * *	        -------------------+------------+----------------------+------------------+
 * *		Potential Name     | I~O        | ottocrat_quotes        | potentialid      |
 * *
 * *     Then put an entry in $new_field_details  like this:
 * *
 * *				"ottocrat_quotes:potentialid"=>"V",
 * *
 * *	Now in customview and report's advance filter this field's criteria will be show like string.
 * *
 * */
function ChangeTypeOfData_Filter($table_name, $column_name, $type_of_data) {
	return Ottocrat_Functions::transformFieldTypeOfData($table_name, $column_name, $type_of_data);
}

/** Clear the Smarty cache files(in Smarty/smarty_c)
 * * This function will called after migration.
 * */
function clear_smarty_cache($path = null) {
	Ottocrat_Deprecated::clearSmartyCompiledFiles($path);
}

/** Get Smarty compiled file for the specified template filename.
 * * @param $template_file Template filename for which the compiled file has to be returned.
 * * @return Compiled file for the specified template file.
 * */
function get_smarty_compiled_file($template_file, $path = null) {
	return Ottocrat_Deprecated::getSmartyCompiledTemplateFile($template_file, $path);
}

/** Function to carry out all the necessary actions after migration */
function perform_post_migration_activities() {
	Ottocrat_Deprecated::postApplicationMigrationTasks();
}

/** Function to get picklist values for the given field that are accessible for the given role.
 *  @ param $tablename picklist fieldname.
 *  It gets the picklist values for the given fieldname
 *  	$fldVal = Array(0=>value,1=>value1,-------------,n=>valuen)
 *  @return Array of picklist values accessible by the user.
 */
function getPickListValues($tablename, $roleid) {
	return Ottocrat_Functions::getPickListValuesFromTableForRole($tablename, $roleid);
}

/** Function to check the file access is made within web root directory and whether it is not from unsafe directories */
function checkFileAccessForInclusion($filepath) {
	Ottocrat_Deprecated::checkFileAccessForInclusion($filepath);
}

/** Function to check the file deletion within the deletable (safe) directories*/
function checkFileAccessForDeletion($filepath) {
	Ottocrat_Deprecated::checkFileAccessForDeletion($filepath);
}

/** Function to check the file access is made within web root directory. */
function checkFileAccess($filepath) {
	Ottocrat_Deprecated::checkFileAccess($filepath);
}

/**
 * function to return whether the file access is made within ottocrat root directory
 * and it exists.
 * @global String $root_directory ottocrat root directory as given in config.inc.php file.
 * @param String $filepath relative path to the file which need to be verified
 * @return Boolean true if file is a valid file within ottocrat root directory, false otherwise.
 */
function isFileAccessible($filepath) {
	return Ottocrat_Deprecated::isFileAccessible($filepath);
}

/** Function to get the ActivityType for the given entity id
 *  @param entityid : Type Integer
 *  return the activity type for the given id
 */
function getActivityType($id) {
	return Ottocrat_Functions::getActivityType($id);
}

/** Function to get owner name either user or group */
function getOwnerName($id) {
	return Ottocrat_Functions::getOwnerRecordLabel($id);
}

/** Function to get owner name either user or group */
function getOwnerNameList($idList) {
	return Ottocrat_Functions::getOwnerRecordLabels($idList);
}

/**
 * This function is used to get the blockid of the settings block for a given label.
 * @param $label - settings label
 * @return string type value
 */
function getSettingsBlockId($label) {
	return Ottocrat_Deprecated::getSettingsBlockId($label);
}

/**
 * this function returns the entity field name for a given module; for e.g. for Contacts module it return concat(lastname, ' ', firstname)
 * @param string $module - the module name
 * @return string $fieldsname - the entity field name for the module
 */
function getEntityField($module) {
	return Ottocrat_Functions::getEntityModuleSQLColumnString($module);
}

/**
 * this function returns the entity information for a given module; for e.g. for Contacts module
 * it returns the information of tablename, modulename, fieldsname and id gets from ottocrat_entityname
 * @param string $module - the module name
 * @return array $data - the entity information for the module
 */
function getEntityFieldNames($module) {
	return Ottocrat_Functions::getEntityModuleInfoFieldsFormatted($module);
}

/**
 * this function returns the entity field name for a given module; for e.g. for Contacts module it return concat(lastname, ' ', firstname)
 * @param1 $module - name of the module
 * @param2 $fieldsName - fieldname with respect to module (ex : 'Accounts' - 'accountname', 'Contacts' - 'lastname','firstname')
 * @param3 $fieldValues - array of fieldname and its value
 * @return string $fieldConcatName - the entity field name for the module
 */
function getEntityFieldNameDisplay($module, $fieldsName, $fieldValues) {
	return Ottocrat_Deprecated::getCurrentUserEntityFieldNameDisplay($module, $fieldsName, $fieldValues);
}

// ottocrat cache utility
require_once('include/utils/VTCacheUtils.php');

// vtlib customization: Extended ottocrat CRM utlitiy functions
require_once('include/utils/VtlibUtils.php');

// END

function vt_suppressHTMLTags($string) {
	return Ottocrat_Functions::suppressHTMLTags($string);
}

function getSqlForNameInDisplayFormat($input, $module, $glue = ' ') {
	return Ottocrat_Deprecated::getSqlForNameInDisplayFormat($input, $module, $glue);
}

function getModuleSequenceNumber($module, $recordId) {
	return Ottocrat_Deprecated::getModuleSequenceNumber($module, $recordId);
	}

function getInvoiceStatus($invoiceId) {
	return Ottocrat_Functions::getInvoiceStatus($invoiceId);
}

function decimalFormat($value){
	return Ottocrat_Functions::formatDecimal($value);
}

function updateRecordLabel($module,$recordId){
	return Ottocrat_Functions::updateCRMRecordLabel($module, $recordId);
}


function get_group_options() {
return Ottocrat_Functions::get_group_options();
}
?>
