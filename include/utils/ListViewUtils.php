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
 * $Header: /cvsroot/ottocratcrm/ottocrat_crm/include/utils/ListViewUtils.php,v 1.32 2006/02/03 06:53:08 mangai Exp $
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/database/PearDatabase.php');
require_once('include/ComboUtil.php'); //new
require_once('include/utils/CommonUtils.php'); //new
require_once('user_privileges/default_module_view.php'); //new
require_once('include/utils/UserInfoUtil.php');
require_once('include/Zend/Json.php');

/** Function to get the list query for a module
 * @param $module -- module name:: Type string
 * @param $where -- where:: Type string
 * @returns $query -- query:: Type query
 */
function getListQuery($module, $where = '') {
	global $log;
	$log->debug("Entering getListQuery(" . $module . "," . $where . ") method ...");

	global $current_user;
	require('user_privileges/user_privileges_' . $current_user->id . '.php');
	require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
	$tab_id = getTabid($module);
	$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'ottocrat_users.first_name', 'last_name' =>
				'ottocrat_users.last_name'), 'Users');
	switch ($module) {
		Case "HelpDesk":
			$query = "SELECT ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
			ottocrat_troubletickets.title, ottocrat_troubletickets.status,
			ottocrat_troubletickets.priority, ottocrat_troubletickets.parent_id,
			ottocrat_contactdetails.contactid, ottocrat_contactdetails.firstname,
			ottocrat_contactdetails.lastname, ottocrat_account.accountid,
			ottocrat_account.accountname, ottocrat_ticketcf.*, ottocrat_troubletickets.ticket_no
			FROM ottocrat_troubletickets
			INNER JOIN ottocrat_ticketcf
				ON ottocrat_ticketcf.ticketid = ottocrat_troubletickets.ticketid
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_troubletickets.ticketid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_contactdetails
				ON ottocrat_troubletickets.parent_id = ottocrat_contactdetails.contactid
			LEFT JOIN ottocrat_account
				ON ottocrat_account.accountid = ottocrat_troubletickets.parent_id
			LEFT JOIN ottocrat_users
				ON ottocrat_crmentity.smownerid = ottocrat_users.id
			LEFT JOIN ottocrat_products
				ON ottocrat_products.productid = ottocrat_troubletickets.product_id";
			$query .= ' ' . getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;

		Case "Accounts":
			//Query modified to sort by assigned to
			$query = "SELECT ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
			ottocrat_account.accountname, ottocrat_account.email1,
			ottocrat_account.email2, ottocrat_account.website, ottocrat_account.phone,
			ottocrat_accountbillads.bill_city,
			ottocrat_accountscf.*
			FROM ottocrat_account
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_account.accountid
			INNER JOIN ottocrat_accountbillads
				ON ottocrat_account.accountid = ottocrat_accountbillads.accountaddressid
			INNER JOIN ottocrat_accountshipads
				ON ottocrat_account.accountid = ottocrat_accountshipads.accountaddressid
			INNER JOIN ottocrat_accountscf
				ON ottocrat_account.accountid = ottocrat_accountscf.accountid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_account ottocrat_account2
				ON ottocrat_account.parentid = ottocrat_account2.accountid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;

		Case "Potentials":
			//Query modified to sort by assigned to
			$query = "SELECT ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
			ottocrat_account.accountname,
			ottocrat_potential.related_to, ottocrat_potential.potentialname,
			ottocrat_potential.sales_stage, ottocrat_potential.amount,
			ottocrat_potential.currency, ottocrat_potential.closingdate,
			ottocrat_potential.typeofrevenue, ottocrat_potential.contact_id,
			ottocrat_potentialscf.*
			FROM ottocrat_potential
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_potential.potentialid
			INNER JOIN ottocrat_potentialscf
				ON ottocrat_potentialscf.potentialid = ottocrat_potential.potentialid
			LEFT JOIN ottocrat_account
				ON ottocrat_potential.related_to = ottocrat_account.accountid
			LEFT JOIN ottocrat_contactdetails
				ON ottocrat_potential.contact_id = ottocrat_contactdetails.contactid
			LEFT JOIN ottocrat_campaign
				ON ottocrat_campaign.campaignid = ottocrat_potential.campaignid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;

		Case "Leads":
			$query = "SELECT ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
			ottocrat_leaddetails.firstname, ottocrat_leaddetails.lastname,
			ottocrat_leaddetails.company, ottocrat_leadaddress.phone,
			ottocrat_leadsubdetails.website, ottocrat_leaddetails.email,
			ottocrat_leadscf.*
			FROM ottocrat_leaddetails
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_leaddetails.leadid
			INNER JOIN ottocrat_leadsubdetails
				ON ottocrat_leadsubdetails.leadsubscriptionid = ottocrat_leaddetails.leadid
			INNER JOIN ottocrat_leadaddress
				ON ottocrat_leadaddress.leadaddressid = ottocrat_leadsubdetails.leadsubscriptionid
			INNER JOIN ottocrat_leadscf
				ON ottocrat_leaddetails.leadid = ottocrat_leadscf.leadid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_leaddetails.converted = 0 " . $where;
			break;
		Case "Products":
			$query = "SELECT ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_crmentity.description, ottocrat_products.*, ottocrat_productcf.*
			FROM ottocrat_products
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_products.productid
			INNER JOIN ottocrat_productcf
				ON ottocrat_products.productid = ottocrat_productcf.productid
			LEFT JOIN ottocrat_vendor
				ON ottocrat_vendor.vendorid = ottocrat_products.vendor_id
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid";
			if ((isset($_REQUEST["from_dashboard"]) && $_REQUEST["from_dashboard"] == true) && (isset($_REQUEST["type"]) && $_REQUEST["type"] == "dbrd"))
				$query .= " INNER JOIN ottocrat_inventoryproductrel on ottocrat_inventoryproductrel.productid = ottocrat_products.productid";

			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= " WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "Documents":
			$query = "SELECT case when (ottocrat_users.user_name not like '') then $userNameSql else ottocrat_groups.groupname end as user_name,ottocrat_crmentity.crmid, ottocrat_crmentity.modifiedtime,
			ottocrat_crmentity.smownerid,ottocrat_attachmentsfolder.*,ottocrat_notes.*
			FROM ottocrat_notes
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_notes.notesid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_attachmentsfolder
				ON ottocrat_notes.folderid = ottocrat_attachmentsfolder.folderid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "Contacts":
			//Query modified to sort by assigned to
			$query = "SELECT ottocrat_contactdetails.firstname, ottocrat_contactdetails.lastname,
			ottocrat_contactdetails.title, ottocrat_contactdetails.accountid,
			ottocrat_contactdetails.email, ottocrat_contactdetails.phone,
			ottocrat_crmentity.smownerid, ottocrat_crmentity.crmid
			FROM ottocrat_contactdetails
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_contactdetails.contactid
			INNER JOIN ottocrat_contactaddress
				ON ottocrat_contactaddress.contactaddressid = ottocrat_contactdetails.contactid
			INNER JOIN ottocrat_contactsubdetails
				ON ottocrat_contactsubdetails.contactsubscriptionid = ottocrat_contactdetails.contactid
			INNER JOIN ottocrat_contactscf
				ON ottocrat_contactscf.contactid = ottocrat_contactdetails.contactid
			LEFT JOIN ottocrat_account
				ON ottocrat_account.accountid = ottocrat_contactdetails.accountid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_contactdetails ottocrat_contactdetails2
				ON ottocrat_contactdetails.reportsto = ottocrat_contactdetails2.contactid
			LEFT JOIN ottocrat_customerdetails
				ON ottocrat_customerdetails.customerid = ottocrat_contactdetails.contactid";
			if ((isset($_REQUEST["from_dashboard"]) && $_REQUEST["from_dashboard"] == true) &&
					(isset($_REQUEST["type"]) && $_REQUEST["type"] == "dbrd")) {
				$query .= " INNER JOIN ottocrat_campaigncontrel on ottocrat_campaigncontrel.contactid = " .
						"ottocrat_contactdetails.contactid";
			}
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "Calendar":

			$query = "SELECT ottocrat_activity.activityid as act_id,ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_crmentity.setype,
		ottocrat_activity.*,
		ottocrat_contactdetails.lastname, ottocrat_contactdetails.firstname,
		ottocrat_contactdetails.contactid,
		ottocrat_account.accountid, ottocrat_account.accountname
		FROM ottocrat_activity
		LEFT JOIN ottocrat_activitycf
			ON ottocrat_activitycf.activityid = ottocrat_activity.activityid
		LEFT JOIN ottocrat_cntactivityrel
			ON ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid
		LEFT JOIN ottocrat_contactdetails
			ON ottocrat_contactdetails.contactid = ottocrat_cntactivityrel.contactid
		LEFT JOIN ottocrat_seactivityrel
			ON ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
		LEFT OUTER JOIN ottocrat_activity_reminder
			ON ottocrat_activity_reminder.activity_id = ottocrat_activity.activityid
		LEFT JOIN ottocrat_crmentity
			ON ottocrat_crmentity.crmid = ottocrat_activity.activityid
		LEFT JOIN ottocrat_users
			ON ottocrat_users.id = ottocrat_crmentity.smownerid
		LEFT JOIN ottocrat_groups
			ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
		LEFT JOIN ottocrat_users ottocrat_users2
			ON ottocrat_crmentity.modifiedby = ottocrat_users2.id
		LEFT JOIN ottocrat_groups ottocrat_groups2
			ON ottocrat_crmentity.modifiedby = ottocrat_groups2.groupid
		LEFT OUTER JOIN ottocrat_account
			ON ottocrat_account.accountid = ottocrat_contactdetails.accountid
		LEFT OUTER JOIN ottocrat_leaddetails
	       		ON ottocrat_leaddetails.leadid = ottocrat_seactivityrel.crmid
		LEFT OUTER JOIN ottocrat_account ottocrat_account2
	        	ON ottocrat_account2.accountid = ottocrat_seactivityrel.crmid
		LEFT OUTER JOIN ottocrat_potential
	       		ON ottocrat_potential.potentialid = ottocrat_seactivityrel.crmid
		LEFT OUTER JOIN ottocrat_troubletickets
	       		ON ottocrat_troubletickets.ticketid = ottocrat_seactivityrel.crmid
		LEFT OUTER JOIN ottocrat_salesorder
			ON ottocrat_salesorder.salesorderid = ottocrat_seactivityrel.crmid
		LEFT OUTER JOIN ottocrat_purchaseorder
			ON ottocrat_purchaseorder.purchaseorderid = ottocrat_seactivityrel.crmid
		LEFT OUTER JOIN ottocrat_quotes
			ON ottocrat_quotes.quoteid = ottocrat_seactivityrel.crmid
		LEFT OUTER JOIN ottocrat_invoice
	                ON ottocrat_invoice.invoiceid = ottocrat_seactivityrel.crmid
		LEFT OUTER JOIN ottocrat_campaign
		ON ottocrat_campaign.campaignid = ottocrat_seactivityrel.crmid";

			//added to fix #5135
			if (isset($_REQUEST['from_homepage']) && ($_REQUEST['from_homepage'] ==
					"upcoming_activities" || $_REQUEST['from_homepage'] == "pending_activities")) {
				$query.=" LEFT OUTER JOIN ottocrat_recurringevents
			             ON ottocrat_recurringevents.activityid=ottocrat_activity.activityid";
			}
			//end

			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query.=" WHERE ottocrat_crmentity.deleted = 0 AND activitytype != 'Emails' " . $where;
			break;
		Case "Emails":
			$query = "SELECT DISTINCT ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
			ottocrat_activity.activityid, ottocrat_activity.subject,
			ottocrat_activity.date_start,
			ottocrat_contactdetails.lastname, ottocrat_contactdetails.firstname,
			ottocrat_contactdetails.contactid
			FROM ottocrat_activity
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_activity.activityid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_seactivityrel
				ON ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
			LEFT JOIN ottocrat_contactdetails
				ON ottocrat_contactdetails.contactid = ottocrat_seactivityrel.crmid
			LEFT JOIN ottocrat_cntactivityrel
				ON ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid
				AND ottocrat_cntactivityrel.contactid = ottocrat_cntactivityrel.contactid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_salesmanactivityrel
				ON ottocrat_salesmanactivityrel.activityid = ottocrat_activity.activityid
			LEFT JOIN ottocrat_emaildetails
				ON ottocrat_emaildetails.emailid = ottocrat_activity.activityid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_activity.activitytype = 'Emails'";
			$query .= "AND ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "Faq":
			$query = "SELECT ottocrat_crmentity.crmid, ottocrat_crmentity.createdtime, ottocrat_crmentity.modifiedtime,
			ottocrat_faq.*
			FROM ottocrat_faq
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_faq.id
			LEFT JOIN ottocrat_products
				ON ottocrat_faq.product_id = ottocrat_products.productid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;

		Case "Vendors":
			$query = "SELECT ottocrat_crmentity.crmid, ottocrat_vendor.*
			FROM ottocrat_vendor
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_vendor.vendorid
			INNER JOIN ottocrat_vendorcf
				ON ottocrat_vendor.vendorid = ottocrat_vendorcf.vendorid
			WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "PriceBooks":
			$query = "SELECT ottocrat_crmentity.crmid, ottocrat_pricebook.*, ottocrat_currency_info.currency_name
			FROM ottocrat_pricebook
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_pricebook.pricebookid
			INNER JOIN ottocrat_pricebookcf
				ON ottocrat_pricebook.pricebookid = ottocrat_pricebookcf.pricebookid
			LEFT JOIN ottocrat_currency_info
				ON ottocrat_pricebook.currency_id = ottocrat_currency_info.id
			WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "Quotes":
			//Query modified to sort by assigned to
			$query = "SELECT ottocrat_crmentity.*,
			ottocrat_quotes.*,
			ottocrat_quotesbillads.*,
			ottocrat_quotesshipads.*,
			ottocrat_potential.potentialname,
			ottocrat_account.accountname,
			ottocrat_currency_info.currency_name
			FROM ottocrat_quotes
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_quotes.quoteid
			INNER JOIN ottocrat_quotesbillads
				ON ottocrat_quotes.quoteid = ottocrat_quotesbillads.quotebilladdressid
			INNER JOIN ottocrat_quotesshipads
				ON ottocrat_quotes.quoteid = ottocrat_quotesshipads.quoteshipaddressid
			LEFT JOIN ottocrat_quotescf
				ON ottocrat_quotes.quoteid = ottocrat_quotescf.quoteid
			LEFT JOIN ottocrat_currency_info
				ON ottocrat_quotes.currency_id = ottocrat_currency_info.id
			LEFT OUTER JOIN ottocrat_account
				ON ottocrat_account.accountid = ottocrat_quotes.accountid
			LEFT OUTER JOIN ottocrat_potential
				ON ottocrat_potential.potentialid = ottocrat_quotes.potentialid
			LEFT JOIN ottocrat_contactdetails
				ON ottocrat_contactdetails.contactid = ottocrat_quotes.contactid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users as ottocrat_usersQuotes
			        ON ottocrat_usersQuotes.id = ottocrat_quotes.inventorymanager";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "PurchaseOrder":
			//Query modified to sort by assigned to
			$query = "SELECT ottocrat_crmentity.*,
			ottocrat_purchaseorder.*,
			ottocrat_pobillads.*,
			ottocrat_poshipads.*,
			ottocrat_vendor.vendorname,
			ottocrat_currency_info.currency_name
			FROM ottocrat_purchaseorder
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_purchaseorder.purchaseorderid
			LEFT OUTER JOIN ottocrat_vendor
				ON ottocrat_purchaseorder.vendorid = ottocrat_vendor.vendorid
			LEFT JOIN ottocrat_contactdetails
				ON ottocrat_purchaseorder.contactid = ottocrat_contactdetails.contactid
			INNER JOIN ottocrat_pobillads
				ON ottocrat_purchaseorder.purchaseorderid = ottocrat_pobillads.pobilladdressid
			INNER JOIN ottocrat_poshipads
				ON ottocrat_purchaseorder.purchaseorderid = ottocrat_poshipads.poshipaddressid
			LEFT JOIN ottocrat_purchaseordercf
				ON ottocrat_purchaseordercf.purchaseorderid = ottocrat_purchaseorder.purchaseorderid
			LEFT JOIN ottocrat_currency_info
				ON ottocrat_purchaseorder.currency_id = ottocrat_currency_info.id
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "SalesOrder":
			//Query modified to sort by assigned to
			$query = "SELECT ottocrat_crmentity.*,
			ottocrat_salesorder.*,
			ottocrat_sobillads.*,
			ottocrat_soshipads.*,
			ottocrat_quotes.subject AS quotename,
			ottocrat_account.accountname,
			ottocrat_currency_info.currency_name
			FROM ottocrat_salesorder
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_salesorder.salesorderid
			INNER JOIN ottocrat_sobillads
				ON ottocrat_salesorder.salesorderid = ottocrat_sobillads.sobilladdressid
			INNER JOIN ottocrat_soshipads
				ON ottocrat_salesorder.salesorderid = ottocrat_soshipads.soshipaddressid
			LEFT JOIN ottocrat_salesordercf
				ON ottocrat_salesordercf.salesorderid = ottocrat_salesorder.salesorderid
			LEFT JOIN ottocrat_currency_info
				ON ottocrat_salesorder.currency_id = ottocrat_currency_info.id
			LEFT OUTER JOIN ottocrat_quotes
				ON ottocrat_quotes.quoteid = ottocrat_salesorder.quoteid
			LEFT OUTER JOIN ottocrat_account
				ON ottocrat_account.accountid = ottocrat_salesorder.accountid
			LEFT JOIN ottocrat_contactdetails
				ON ottocrat_salesorder.contactid = ottocrat_contactdetails.contactid
			LEFT JOIN ottocrat_potential
				ON ottocrat_potential.potentialid = ottocrat_salesorder.potentialid
			LEFT JOIN ottocrat_invoice_recurring_info
				ON ottocrat_invoice_recurring_info.salesorderid = ottocrat_salesorder.salesorderid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "Invoice":
			//Query modified to sort by assigned to
			//query modified -Code contribute by Geoff(http://forums.ottocrat.com/viewtopic.php?t=3376)
			$query = "SELECT ottocrat_crmentity.*,
			ottocrat_invoice.*,
			ottocrat_invoicebillads.*,
			ottocrat_invoiceshipads.*,
			ottocrat_salesorder.subject AS salessubject,
			ottocrat_account.accountname,
			ottocrat_currency_info.currency_name
			FROM ottocrat_invoice
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_invoice.invoiceid
			INNER JOIN ottocrat_invoicebillads
				ON ottocrat_invoice.invoiceid = ottocrat_invoicebillads.invoicebilladdressid
			INNER JOIN ottocrat_invoiceshipads
				ON ottocrat_invoice.invoiceid = ottocrat_invoiceshipads.invoiceshipaddressid
			LEFT JOIN ottocrat_currency_info
				ON ottocrat_invoice.currency_id = ottocrat_currency_info.id
			LEFT OUTER JOIN ottocrat_salesorder
				ON ottocrat_salesorder.salesorderid = ottocrat_invoice.salesorderid
			LEFT OUTER JOIN ottocrat_account
			        ON ottocrat_account.accountid = ottocrat_invoice.accountid
			LEFT JOIN ottocrat_contactdetails
				ON ottocrat_contactdetails.contactid = ottocrat_invoice.contactid
			INNER JOIN ottocrat_invoicecf
				ON ottocrat_invoice.invoiceid = ottocrat_invoicecf.invoiceid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "Campaigns":
			//Query modified to sort by assigned to
			//query modified -Code contribute by Geoff(http://forums.ottocrat.com/viewtopic.php?t=3376)
			$query = "SELECT ottocrat_crmentity.*,
			ottocrat_campaign.*
			FROM ottocrat_campaign
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_campaign.campaignid
			INNER JOIN ottocrat_campaignscf
			        ON ottocrat_campaign.campaignid = ottocrat_campaignscf.campaignid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_products
				ON ottocrat_products.productid = ottocrat_campaign.product_id";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE ottocrat_crmentity.deleted = 0 " . $where;
			break;
		Case "Users":
			$query = "SELECT id,user_name,first_name,last_name,email1,phone_mobile,phone_work,is_admin,status,email2,
					ottocrat_user2role.roleid as roleid,ottocrat_role.depth as depth
				 	FROM ottocrat_users
				 	INNER JOIN ottocrat_user2role ON ottocrat_users.id = ottocrat_user2role.userid
				 	INNER JOIN ottocrat_role ON ottocrat_user2role.roleid = ottocrat_role.roleid
					WHERE deleted=0 AND status <> 'Inactive'" . $where;
			break;
		default:
			// vtlib customization: Include the module file
			$focus = CRMEntity::getInstance($module);
			$query = $focus->getListQuery($module, $where);
		// END
	}

	if ($module != 'Users') {
		$query = listQueryNonAdminChange($query, $module);
	}
	$log->debug("Exiting getListQuery method ...");
	return $query;
}

/* * This function stores the variables in session sent in list view url string.
 * Param $lv_array - list view session array
 * Param $noofrows - no of rows
 * Param $max_ent - maximum entires
 * Param $module - module name
 * Param $related - related module
 * Return type void.
 */

function setSessionVar($lv_array, $noofrows, $max_ent, $module = '', $related = '') {
	$start = '';
	if ($noofrows >= 1) {
		$lv_array['start'] = 1;
		$start = 1;
	} elseif ($related != '' && $noofrows == 0) {
		$lv_array['start'] = 1;
		$start = 1;
	} else {
		$lv_array['start'] = 0;
		$start = 0;
	}

	if (isset($_REQUEST['start']) && $_REQUEST['start'] != '') {
		$lv_array['start'] = ListViewSession::getRequestStartPage();
		$start = ListViewSession::getRequestStartPage();
	} elseif ($_SESSION['rlvs'][$module][$related]['start'] != '') {

		if ($related != '') {
			$lv_array['start'] = $_SESSION['rlvs'][$module][$related]['start'];
			$start = $_SESSION['rlvs'][$module][$related]['start'];
		}
	}
	if (isset($_REQUEST['viewname']) && $_REQUEST['viewname'] != '')
		$lv_array['viewname'] = vtlib_purify($_REQUEST['viewname']);

	if ($related == '')
		$_SESSION['lvs'][$_REQUEST['module']] = $lv_array;
	else
		$_SESSION['rlvs'][$module][$related] = $lv_array;

	if ($start < ceil($noofrows / $max_ent) && $start != '') {
		$start = ceil($noofrows / $max_ent);
		if ($related == '')
			$_SESSION['lvs'][$currentModule]['start'] = $start;
	}
}

/* * Function to get the table headers for related listview
 * Param $navigation_arrray - navigation values in array
 * Param $url_qry - url string
 * Param $module - module name
 * Param $action- action file name
 * Param $viewid - view id
 * Returns an string value
 */

function getRelatedTableHeaderNavigation($navigation_array, $url_qry, $module, $related_module, $recordid) {
	global $log, $app_strings, $adb;
	$log->debug("Entering getTableHeaderNavigation(" . $navigation_array . "," . $url_qry . "," . $module . "," . $action_val . "," . $viewid . ") method ...");
	global $theme;
	$relatedTabId = getTabid($related_module);
	$tabid = getTabid($module);

	$relatedListResult = $adb->pquery('SELECT * FROM ottocrat_relatedlists WHERE tabid=? AND
		related_tabid=?', array($tabid, $relatedTabId));
	if (empty($relatedListResult))
		return;
	$relatedListRow = $adb->fetch_row($relatedListResult);
	$header = $relatedListRow['label'];
	$actions = $relatedListRow['actions'];
	$functionName = $relatedListRow['name'];

	$urldata = "module=$module&action={$module}Ajax&file=DetailViewAjax&record={$recordid}&" .
			"ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$relatedListRow['relation_id']}" .
			"&actions={$actions}&{$url_qry}";

	$formattedHeader = str_replace(' ', '', $header);
	$target = 'tbl_' . $module . '_' . $formattedHeader;
	$imagesuffix = $module . '_' . $formattedHeader;

	$output = '<td align="right" style="padding="5px;">';
	if (($navigation_array['prev']) != 0) {
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=1\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . ottocrat_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['prev'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><img src="' . ottocrat_imageurl('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
	} else {
		$output .= '<img src="' . ottocrat_imageurl('start_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . ottocrat_imageurl('previous_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}

	$jsHandler = "return VT_disableFormSubmit(event);";
	$output .= "<input class='small' name='pagenum' type='text' value='{$navigation_array['current']}'
		style='width: 3em;margin-right: 0.7em;' onchange=\"loadRelatedListBlock('{$urldata}&start='+this.value+'','{$target}','{$imagesuffix}');\"
		onkeypress=\"$jsHandler\">";
	$output .= "<span name='listViewCountContainerName' class='small' style='white-space: nowrap;'>";
	$computeCount = $_REQUEST['withCount'];
	if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true
			|| ((boolean) $computeCount) == true) {
		$output .= $app_strings['LBL_LIST_OF'] . ' ' . $navigation_array['verylast'];
	} else {
		$output .= "<img src='" . ottocrat_imageurl('windowRefresh.gif', $theme) . "' alt='" . $app_strings['LBL_HOME_COUNT'] . "'
			onclick=\"loadRelatedListBlock('{$urldata}&withCount=true&start={$navigation_array['current']}','{$target}','{$imagesuffix}');\"
			align='absmiddle' name='" . $module . "_listViewCountRefreshIcon'/>
			<img name='" . $module . "_listViewCountContainerBusy' src='" . ottocrat_imageurl('vtbusy.gif', $theme) . "' style='display: none;'
			align='absmiddle' alt='" . $app_strings['LBL_LOADING'] . "'>";
	}
	$output .= '</span>';

	if (($navigation_array['next']) != 0) {
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['next'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><img src="' . ottocrat_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['verylast'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><img src="' . ottocrat_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
	} else {
		$output .= '<img src="' . ottocrat_imageurl('next_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . ottocrat_imageurl('end_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}
	$output .= '</td>';
	$log->debug("Exiting getTableHeaderNavigation method ...");
	if ($navigation_array['first'] == '')
		return;
	else
		return $output;
}

/* Function to get the Entity Id of a given Entity Name */

function getEntityId($module, $entityName) {
	global $log, $adb;
	$log->info("in getEntityId " . $entityName);

	$query = "select fieldname,tablename,entityidfield from ottocrat_entityname where modulename = ?";
	$result = $adb->pquery($query, array($module));
	$fieldsname = $adb->query_result($result, 0, 'fieldname');
	$tablename = $adb->query_result($result, 0, 'tablename');
	$entityidfield = $adb->query_result($result, 0, 'entityidfield');
	if (!(strpos($fieldsname, ',') === false)) {
		$fieldlists = explode(',', $fieldsname);
		$fieldsname = "trim(concat(";
		$fieldsname = $fieldsname . implode(",' ',", $fieldlists);
		$fieldsname = $fieldsname . "))";
		$entityName = trim($entityName);
	}

	if ($entityName != '') {
		$sql = "select $entityidfield from $tablename INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = $tablename.$entityidfield " .
				" WHERE ottocrat_crmentity.deleted = 0 and $fieldsname=?";
		$result = $adb->pquery($sql, array($entityName));
		if ($adb->num_rows($result) > 0) {
			$entityId = $adb->query_result($result, 0, $entityidfield);
		}
	}
	if (!empty($entityId))
		return $entityId;
	else
		return 0;
}

function decode_html($str) {
	global $default_charset;$default_charset='UTF-8'; 
	// Direct Popup action or Ajax Popup action should be treated the same.
	if ($_REQUEST['action'] == 'Popup' || $_REQUEST['file'] == 'Popup')
		return html_entity_decode($str);
	else
		return html_entity_decode($str, ENT_QUOTES, $default_charset);
}

function popup_decode_html($str) {
	global $default_charset;
	$slashes_str = popup_from_html($str);
	$slashes_str = htmlspecialchars($slashes_str, ENT_QUOTES, $default_charset);
	return decode_html(br2nl($slashes_str));
}

//function added to check the text length in the listview.
function textlength_check($field_val) {
	global $listview_max_textlength, $default_charset;
	if ($listview_max_textlength && $listview_max_textlength > 0) {
		$temp_val = preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $field_val);
		if (function_exists('mb_strlen')) {
			if (mb_strlen(html_entity_decode($temp_val)) > $listview_max_textlength) {
				$temp_val = mb_substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $field_val), 0, $listview_max_textlength, $default_charset) . '...';
			}
		} elseif (strlen(html_entity_decode($field_val)) > $listview_max_textlength) {
			$temp_val = substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $field_val), 0, $listview_max_textlength) . '...';
		}
	} else {
		$temp_val = $field_val;
	}
	return $temp_val;
}

/**
 * this function accepts a modulename and a fieldname and returns the first related module for it
 * it expects the uitype of the field to be 10
 * @param string $module - the modulename
 * @param string $fieldname - the field name
 * @return string $data - the first related module
 */
function getFirstModule($module, $fieldname) {
	global $adb;
	$sql = "select fieldid, uitype from ottocrat_field where tabid=? and fieldname=?";
	$result = $adb->pquery($sql, array(getTabid($module), $fieldname));

	if ($adb->num_rows($result) > 0) {
		$uitype = $adb->query_result($result, 0, "uitype");

		if ($uitype == 10) {
			$fieldid = $adb->query_result($result, 0, "fieldid");
			$sql = "select * from ottocrat_fieldmodulerel where fieldid=?";
			$result = $adb->pquery($sql, array($fieldid));
			$count = $adb->num_rows($result);

			if ($count > 0) {
				$data = $adb->query_result($result, 0, "relmodule");
			}
		}
	}
	return $data;
}

function VT_getSimpleNavigationValues($start, $size, $total) {
	$prev = $start - 1;
	if ($prev < 0) {
		$prev = 0;
	}
	if ($total === null) {
		return array('start' => $start, 'first' => $start, 'current' => $start, 'end' => $start, 'end_val' => $size, 'allflag' => 'All',
			'prev' => $prev, 'next' => $start + 1, 'verylast' => 'last');
	}
	if (empty($total)) {
		$lastPage = 1;
	} else {
		$lastPage = ceil($total / $size);
	}

	$next = $start + 1;
	if ($next > $lastPage) {
		$next = 0;
	}
	return array('start' => $start, 'first' => $start, 'current' => $start, 'end' => $start, 'end_val' => $size, 'allflag' => 'All',
		'prev' => $prev, 'next' => $next, 'verylast' => $lastPage);
}

function getRecordRangeMessage($listResult, $limitStartRecord, $totalRows = '') {
	global $adb, $app_strings;
	$numRows = $adb->num_rows($listResult);
	$recordListRangeMsg = '';
	if ($numRows > 0) {
		$recordListRangeMsg = $app_strings['LBL_SHOWING'] . ' ' . $app_strings['LBL_RECORDS'] .
				' ' . ($limitStartRecord + 1) . ' - ' . ($limitStartRecord + $numRows);
		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true) {
			$recordListRangeMsg .= ' ' . $app_strings['LBL_LIST_OF'] . " $totalRows";
		}
	}
	return $recordListRangeMsg;
}

function listQueryNonAdminChange($query, $module, $scope = '') {
	$instance = CRMEntity::getInstance($module);
	return $instance->listQueryNonAdminChange($query, $scope);
}

function html_strlen($str) {
	$chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	return count($chars);
}

function html_substr($str, $start, $length = NULL) {
	if ($length === 0)
		return "";
	//check if we can simply use the built-in functions
	if (strpos($str, '&') === false) { //No entities. Use built-in functions
		if ($length === NULL)
			return substr($str, $start);
		else
			return substr($str, $start, $length);
	}

	// create our array of characters and html entities
	$chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
	$html_length = count($chars);
	// check if we can predict the return value and save some processing time
	if (($html_length === 0) or ($start >= $html_length) or (isset($length) and ($length <= -$html_length)))
		return "";

	//calculate start position
	if ($start >= 0) {
		$real_start = $chars[$start][1];
	} else { //start'th character from the end of string
		$start = max($start, -$html_length);
		$real_start = $chars[$html_length + $start][1];
	}
	if (!isset($length)) // no $length argument passed, return all remaining characters
		return substr($str, $real_start);
	else if ($length > 0) { // copy $length chars
		if ($start + $length >= $html_length) { // return all remaining characters
			return substr($str, $real_start);
		} else { //return $length characters
			return substr($str, $real_start, $chars[max($start, 0) + $length][1] - $real_start);
		}
	} else { //negative $length. Omit $length characters from end
		return substr($str, $real_start, $chars[$html_length + $length][1] - $real_start);
	}
}

function counterValue() {
	static $counter = 0;
	$counter = $counter + 1;
	return $counter;
}

function getUsersPasswordInfo(){
	global $adb;
	$sql = "SELECT user_name, user_hash FROM ottocrat_users WHERE deleted=?";
	$result = $adb->pquery($sql, array(0));
	$usersList = array();
	for ($i=0; $i<$adb->num_rows($result); $i++) {
		$userList['name'] = $adb->query_result($result, $i, "user_name");
		$userList['hash'] = $adb->query_result($result, $i, "user_hash");
		$usersList[] = $userList;
	}
	return $usersList;
}

?>
