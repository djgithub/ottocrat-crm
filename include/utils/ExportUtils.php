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


/**	function used to get the permitted blocks
 *	@param string $module - module name
 *	@param string $disp_view - view name, this may be create_view, edit_view or detail_view
 *	@return string $blockid_list - list of block ids within the paranthesis with comma seperated
 */
function getPermittedBlocks($module, $disp_view)
{
	global $adb, $log;
	$log->debug("Entering into the function getPermittedBlocks($module, $disp_view)");

        $tabid = getTabid($module);
        $block_detail = Array();
        $query="select blockid,blocklabel,show_title from ottocrat_blocks where tabid=? and $disp_view=0 and visible = 0 order by sequence";
        $result = $adb->pquery($query, array($tabid));
        $noofrows = $adb->num_rows($result);
	$blockid_list ='(';
	for($i=0; $i<$noofrows; $i++)
	{
		$blockid = $adb->query_result($result,$i,"blockid");
		if($i != 0)
			$blockid_list .= ', ';
		$blockid_list .= $blockid;
		$block_label[$blockid] = $adb->query_result($result,$i,"blocklabel");
	}
	$blockid_list .= ')';

	$log->debug("Exit from the function getPermittedBlocks($module, $disp_view). Return value = $blockid_list");
	return $blockid_list;
}

/**	function used to get the query which will list the permitted fields
 *	@param string $module - module name
 *	@param string $disp_view - view name, this may be create_view, edit_view or detail_view
 *	@return string $sql - query to get the list of fields which are permitted to the current user
 */
function getPermittedFieldsQuery($module, $disp_view)
{
	global $adb, $log;
	$log->debug("Entering into the function getPermittedFieldsQuery($module, $disp_view)");

	global $current_user;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');

	//To get the permitted blocks
	$blockid_list = getPermittedBlocks($module, $disp_view);

        $tabid = getTabid($module);
	if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0 || $module == "Users")
	{
 		$sql = "SELECT ottocrat_field.columnname, ottocrat_field.fieldlabel, ottocrat_field.tablename FROM ottocrat_field WHERE ottocrat_field.tabid=".$tabid." AND ottocrat_field.block IN $blockid_list AND ottocrat_field.displaytype IN (1,2,4,5) and ottocrat_field.presence in (0,2) ORDER BY block,sequence";
  	}
  	else
  	{
		$profileList = getCurrentUserProfileList();
		$sql = "SELECT ottocrat_field.columnname, ottocrat_field.fieldlabel, ottocrat_field.tablename FROM ottocrat_field INNER JOIN ottocrat_profile2field ON ottocrat_profile2field.fieldid=ottocrat_field.fieldid INNER JOIN ottocrat_def_org_field ON ottocrat_def_org_field.fieldid=ottocrat_field.fieldid WHERE ottocrat_field.tabid=".$tabid." AND ottocrat_field.block IN ".$blockid_list." AND ottocrat_field.displaytype IN (1,2,4,5) AND ottocrat_profile2field.visible=0 AND ottocrat_def_org_field.visible=0 AND ottocrat_profile2field.profileid IN (". implode(",", $profileList) .") and ottocrat_field.presence in (0,2) GROUP BY ottocrat_field.fieldid ORDER BY block,sequence";
	}

	$log->debug("Exit from the function getPermittedFieldsQuery($module, $disp_view). Return value = $sql");
	return $sql;
}

/**	function used to get the list of fields from the input query as a comma seperated string
 *	@param string $query - field table query which contains the list of fields
 *	@return string $fields - list of fields as a comma seperated string
 */
function getFieldsListFromQuery($query)
{
	global $adb, $log;
	$log->debug("Entering into the function getFieldsListFromQuery($query)");

	$result = $adb->query($query);
	$num_rows = $adb->num_rows($result);

	for($i=0; $i < $num_rows;$i++)
	{
		$columnName = $adb->query_result($result,$i,"columnname");
		$fieldlabel = $adb->query_result($result,$i,"fieldlabel");
		$tablename = $adb->query_result($result,$i,"tablename");

		//HANDLE HERE - Mismatch fieldname-tablename in field table, in future we have to avoid these if elses
		if($columnName == 'smownerid')//for all assigned to user name
		{
			$fields .= "case when (ottocrat_users.user_name not like '') then ottocrat_users.user_name else ottocrat_groups.groupname end as '".$fieldlabel."',";
		}
		elseif($tablename == 'ottocrat_account' && $columnName == 'parentid')//Account - Member Of
		{
			 $fields .= "ottocrat_account2.accountname as '".$fieldlabel."',";
		}
		elseif($tablename == 'ottocrat_contactdetails' && $columnName == 'accountid')//Contact - Account Name
		{
			$fields .= "ottocrat_account.accountname as '".$fieldlabel."',";
		}
		elseif($tablename == 'ottocrat_contactdetails' && $columnName == 'reportsto')//Contact - Reports To
		{
			$fields .= " concat(ottocrat_contactdetails2.lastname,' ',ottocrat_contactdetails2.firstname) as 'Reports To Contact',";
		}
		elseif($tablename == 'ottocrat_potential' && $columnName == 'related_to')//Potential - Related to (changed for B2C model support)
		{
			$fields .= "ottocrat_potential.related_to as '".$fieldlabel."',";
		}
		elseif($tablename == 'ottocrat_potential' && $columnName == 'campaignid')//Potential - Campaign Source
		{
			$fields .= "ottocrat_campaign.campaignname as '".$fieldlabel."',";
		}
		elseif($tablename == 'ottocrat_seproductsrel' && $columnName == 'crmid')//Product - Related To
		{
			$fields .= "case ottocrat_crmentityRelatedTo.setype
					when 'Leads' then concat('Leads :::: ',ottocrat_ProductRelatedToLead.lastname,' ',ottocrat_ProductRelatedToLead.firstname)
					when 'Accounts' then concat('Accounts :::: ',ottocrat_ProductRelatedToAccount.accountname)
					when 'Potentials' then concat('Potentials :::: ',ottocrat_ProductRelatedToPotential.potentialname)
				    End as 'Related To',";
		}
		elseif($tablename == 'ottocrat_products' && $columnName == 'contactid')//Product - Contact
		{
			$fields .= " concat(ottocrat_contactdetails.lastname,' ',ottocrat_contactdetails.firstname) as 'Contact Name',";
		}
		elseif($tablename == 'ottocrat_products' && $columnName == 'vendor_id')//Product - Vendor Name
		{
			$fields .= "ottocrat_vendor.vendorname as '".$fieldlabel."',";
		}
		elseif($tablename == 'ottocrat_producttaxrel' && $columnName == 'taxclass')//avoid product - taxclass
		{
			$fields .= "";
		}
		elseif($tablename == 'ottocrat_attachments' && $columnName == 'name')//Emails filename
		{
			$fields .= $tablename.".name as '".$fieldlabel."',";
		}
		//By Pavani...Handling mismatch field and table name for trouble tickets
      	elseif($tablename == 'ottocrat_troubletickets' && $columnName == 'product_id')//Ticket - Product
        {
			$fields .= "ottocrat_products.productname as '".$fieldlabel."',";
        }
        elseif($tablename == 'ottocrat_notes' && ($columnName == 'filename' || $columnName == 'filetype' || $columnName == 'filesize' || $columnName == 'filelocationtype' || $columnName == 'filestatus' || $columnName == 'filedownloadcount' ||$columnName == 'folderid')){
			continue;
		}
		elseif(($tablename == 'ottocrat_invoice' || $tablename == 'ottocrat_quotes' || $tablename == 'ottocrat_salesorder')&& $columnName == 'accountid') {
			$fields .= 'concat("Accounts::::",ottocrat_account.accountname) as "'.$fieldlabel.'",';
		}
		elseif(($tablename == 'ottocrat_invoice' || $tablename == 'ottocrat_quotes' || $tablename == 'ottocrat_salesorder' || $tablename == 'ottocrat_purchaseorder') && $columnName == 'contactid') {
			$fields .= 'concat("Contacts::::",ottocrat_contactdetails.lastname," ",ottocrat_contactdetails.firstname) as "'.$fieldlabel.'",';
		}
		elseif($tablename == 'ottocrat_invoice' && $columnName == 'salesorderid') {
			$fields .= 'concat("SalesOrder::::",ottocrat_salesorder.subject) as "'.$fieldlabel.'",';
		}
		elseif(($tablename == 'ottocrat_quotes' || $tablename == 'ottocrat_salesorder') && $columnName == 'potentialid') {
			$fields .= 'concat("Potentials::::",ottocrat_potential.potentialname) as "'.$fieldlabel.'",';
		}
		elseif($tablename == 'ottocrat_quotes' && $columnName == 'inventorymanager') {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'ottocrat_inventoryManager.first_name', 'last_name' => 'ottocrat_inventoryManager.last_name'), 'Users');
			$fields .= $userNameSql. ' as "'.$fieldlabel.'",';
		}
		elseif($tablename == 'ottocrat_salesorder' && $columnName == 'quoteid') {
			$fields .= 'concat("Quotes::::",ottocrat_quotes.subject) as "'.$fieldlabel.'",';
		}
		elseif($tablename == 'ottocrat_purchaseorder' && $columnName == 'vendorid') {
			$fields .= 'concat("Vendors::::",ottocrat_vendor.vendorname) as "'.$fieldlabel.'",';
		}
		else
		{
			$fields .= $tablename.".".$columnName. " as '" .$fieldlabel."',";
		}
	}
	$fields = trim($fields,",");

	$log->debug("Exit from the function getFieldsListFromQuery($query). Return value = $fields");
	return $fields;
}



?>
