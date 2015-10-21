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
require_once('include/database/PearDatabase.php');

$customviews = Array(Array('viewname'=>'All',
			   'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
			   'cvmodule'=>'Leads','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Hot Leads',
			   'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
			   'cvmodule'=>'Leads','stdfilterid'=>'','advfilterid'=>'0'),

		     Array('viewname'=>'This Month Leads',
			   'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
			   'cvmodule'=>'Leads','stdfilterid'=>'0','advfilterid'=>''),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'Accounts','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Prospect Accounts',
                           'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Accounts','stdfilterid'=>'','advfilterid'=>'1'),

		     Array('viewname'=>'New This Week',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Accounts','stdfilterid'=>'1','advfilterid'=>''),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'Contacts','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Contacts Address',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Contacts','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Todays Birthday',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Contacts','stdfilterid'=>'2','advfilterid'=>''),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'Potentials','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Potentials Won',
                           'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Potentials','stdfilterid'=>'','advfilterid'=>'2'),

		     Array('viewname'=>'Prospecting',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Potentials','stdfilterid'=>'','advfilterid'=>'3'),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'HelpDesk','stdfilterid'=>'','advfilterid'=>''),

	             Array('viewname'=>'Open Tickets',
                           'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
                           'cvmodule'=>'HelpDesk','stdfilterid'=>'','advfilterid'=>'4'),

		     Array('viewname'=>'High Prioriy Tickets',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'HelpDesk','stdfilterid'=>'','advfilterid'=>'5'),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'Quotes','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Open Quotes',
                           'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Quotes','stdfilterid'=>'','advfilterid'=>'6'),

		     Array('viewname'=>'Rejected Quotes',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Quotes','stdfilterid'=>'','advfilterid'=>'7'),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Calendar','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Emails','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Invoice','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Documents','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'PriceBooks','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Products','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'PurchaseOrder','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'SalesOrder','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Vendors','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Faq','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Campaigns','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
			  'cvmodule'=>'Webmails','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'Drafted FAQ',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                          'cvmodule'=>'Faq','stdfilterid'=>'','advfilterid'=>'8'),

		    Array('viewname'=>'Published FAQ',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
			  'cvmodule'=>'Faq','stdfilterid'=>'','advfilterid'=>'9'),

	            Array('viewname'=>'Open Purchase Orders',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                          'cvmodule'=>'PurchaseOrder','stdfilterid'=>'','advfilterid'=>'10'),

	            Array('viewname'=>'Received Purchase Orders',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                          'cvmodule'=>'PurchaseOrder','stdfilterid'=>'','advfilterid'=>'11'),

		    Array('viewname'=>'Open Invoices',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
			  'cvmodule'=>'Invoice','stdfilterid'=>'','advfilterid'=>'12'),

		    Array('viewname'=>'Paid Invoices',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
			  'cvmodule'=>'Invoice','stdfilterid'=>'','advfilterid'=>'13'),

	            Array('viewname'=>'Pending Sales Orders',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                          'cvmodule'=>'SalesOrder','stdfilterid'=>'','advfilterid'=>'14'),
		    );


$cvcolumns = Array(Array('ottocrat_leaddetails:lead_no:lead_no:Leads_Lead_No:V',
						 'ottocrat_leaddetails:lastname:lastname:Leads_Last_Name:V',
                         'ottocrat_leaddetails:firstname:firstname:Leads_First_Name:V',
                         'ottocrat_leaddetails:company:company:Leads_Company:V',
			 'ottocrat_leadaddress:phone:phone:Leads_Phone:V',
                         'ottocrat_leadsubdetails:website:website:Leads_Website:V',
                         'ottocrat_leaddetails:email:email:Leads_Email:V',
			 'ottocrat_crmentity:smownerid:assigned_user_id:Leads_Assigned_To:V'),

	           Array('ottocrat_leaddetails:firstname:firstname:Leads_First_Name:V',
                         'ottocrat_leaddetails:lastname:lastname:Leads_Last_Name:V',
                         'ottocrat_leaddetails:company:company:Leads_Company:V',
                         'ottocrat_leaddetails:leadsource:leadsource:Leads_Lead_Source:V',
                         'ottocrat_leadsubdetails:website:website:Leads_Website:V',
                         'ottocrat_leaddetails:email:email:Leads_Email:V'),

		   Array('ottocrat_leaddetails:firstname:firstname:Leads_First_Name:V',
                         'ottocrat_leaddetails:lastname:lastname:Leads_Last_Name:V',
                         'ottocrat_leaddetails:company:company:Leads_Company:V',
                         'ottocrat_leaddetails:leadsource:leadsource:Leads_Lead_Source:V',
                         'ottocrat_leadsubdetails:website:website:Leads_Website:V',
                         'ottocrat_leaddetails:email:email:Leads_Email:V'),

		  		 Array('ottocrat_account:account_no:account_no:Accounts_Account_No:V',
				 		'ottocrat_account:accountname:accountname:Accounts_Account_Name:V',
                         'ottocrat_accountbillads:bill_city:bill_city:Accounts_City:V',
                         'ottocrat_account:website:website:Accounts_Website:V',
                         'ottocrat_account:phone:phone:Accounts_Phone:V',
                         'ottocrat_crmentity:smownerid:assigned_user_id:Accounts_Assigned_To:V'),

		   Array('ottocrat_account:accountname:accountname:Accounts_Account_Name:V',
			 'ottocrat_account:phone:phone:Accounts_Phone:V',
			 'ottocrat_account:website:website:Accounts_Website:V',
			 'ottocrat_account:rating:rating:Accounts_Rating:V',
			 'ottocrat_crmentity:smownerid:assigned_user_id:Accounts_Assigned_To:V'),

		   Array('ottocrat_account:accountname:accountname:Accounts_Account_Name:V',
                         'ottocrat_account:phone:phone:Accounts_Phone:V',
                         'ottocrat_account:website:website:Accounts_Website:V',
                         'ottocrat_accountbillads:bill_city:bill_city:Accounts_City:V',
                         'ottocrat_crmentity:smownerid:assigned_user_id:Accounts_Assigned_To:V'),

		   Array('ottocrat_contactdetails:contact_no:contact_no:Contacts_Contact_Id:V',
		   			'ottocrat_contactdetails:firstname:firstname:Contacts_First_Name:V',
                         'ottocrat_contactdetails:lastname:lastname:Contacts_Last_Name:V',
                         'ottocrat_contactdetails:title:title:Contacts_Title:V',
						 'ottocrat_contactdetails:accountid:account_id:Contacts_Account_Name:V',
                         'ottocrat_contactdetails:email:email:Contacts_Email:V',
                         'ottocrat_contactdetails:phone:phone:Contacts_Office_Phone:V',
			 'ottocrat_crmentity:smownerid:assigned_user_id:Contacts_Assigned_To:V'),

		   Array('ottocrat_contactdetails:firstname:firstname:Contacts_First_Name:V',
                         'ottocrat_contactdetails:lastname:lastname:Contacts_Last_Name:V',
                         'ottocrat_contactaddress:mailingstreet:mailingstreet:Contacts_Mailing_Street:V',
                         'ottocrat_contactaddress:mailingcity:mailingcity:Contacts_Mailing_City:V',
                         'ottocrat_contactaddress:mailingstate:mailingstate:Contacts_Mailing_State:V',
			 'ottocrat_contactaddress:mailingzip:mailingzip:Contacts_Mailing_Zip:V',
			 'ottocrat_contactaddress:mailingcountry:mailingcountry:Contacts_Mailing_Country:V'),

		   Array('ottocrat_contactdetails:firstname:firstname:Contacts_First_Name:V',
                 'ottocrat_contactdetails:lastname:lastname:Contacts_Last_Name:V',
                 'ottocrat_contactdetails:title:title:Contacts_Title:V',
                 'ottocrat_contactdetails:accountid:account_id:Contacts_Account_Name:V',
                 'ottocrat_contactdetails:email:email:Contacts_Email:V',
				 'ottocrat_contactsubdetails:otherphone:otherphone:Contacts_Other_Phone:V',
				 'ottocrat_crmentity:smownerid:assigned_user_id:Contacts_Assigned_To:V'),

		   Array('ottocrat_potential:potential_no:potential_no:Potentials_Potential_No:V',
 	   			 'ottocrat_potential:potentialname:potentialname:Potentials_Potential_Name:V',
                 'ottocrat_potential:related_to:related_to:Potentials_Related_To:V',
                 'ottocrat_potential:sales_stage:sales_stage:Potentials_Sales_Stage:V',
                 'ottocrat_potential:leadsource:leadsource:Potentials_Lead_Source:V',
                 'ottocrat_potential:closingdate:closingdate:Potentials_Expected_Close_Date:D',
                 'ottocrat_crmentity:smownerid:assigned_user_id:Potentials_Assigned_To:V'),

	       Array('ottocrat_potential:potentialname:potentialname:Potentials_Potential_Name:V',
	             'ottocrat_potential:related_to:related_to:Potentials_Related_To:V',
	             'ottocrat_potential:amount:amount:Potentials_Amount:N',
	             'ottocrat_potential:closingdate:closingdate:Potentials_Expected_Close_Date:D',
	             'ottocrat_crmentity:smownerid:assigned_user_id:Potentials_Assigned_To:V'),

		   Array('ottocrat_potential:potentialname:potentialname:Potentials_Potential_Name:V',
                 'ottocrat_potential:related_to:related_to:Potentials_Related_To:V',
                 'ottocrat_potential:amount:amount:Potentials_Amount:N',
                 'ottocrat_potential:leadsource:leadsource:Potentials_Lead_Source:V',
                 'ottocrat_potential:closingdate:closingdate:Potentials_Expected_Close_Date:D',
                 'ottocrat_crmentity:smownerid:assigned_user_id:Potentials_Assigned_To:V'),

		   Array(//'ottocrat_crmentity:crmid::HelpDesk_Ticket_ID:I',
		   				'ottocrat_troubletickets:ticket_no:ticket_no:HelpDesk_Ticket_No:V',
			 'ottocrat_troubletickets:title:ticket_title:HelpDesk_Title:V',
                         'ottocrat_troubletickets:parent_id:parent_id:HelpDesk_Related_To:V',
                         'ottocrat_troubletickets:status:ticketstatus:HelpDesk_Status:V',
                         'ottocrat_troubletickets:priority:ticketpriorities:HelpDesk_Priority:V',
                         'ottocrat_crmentity:smownerid:assigned_user_id:HelpDesk_Assigned_To:V'),

		   Array('ottocrat_troubletickets:title:ticket_title:HelpDesk_Title:V',
                         'ottocrat_troubletickets:parent_id:parent_id:HelpDesk_Related_To:V',
                         'ottocrat_troubletickets:priority:ticketpriorities:HelpDesk_Priority:V',
                         'ottocrat_troubletickets:product_id:product_id:HelpDesk_Product_Name:V',
                         'ottocrat_crmentity:smownerid:assigned_user_id:HelpDesk_Assigned_To:V'),

		   Array('ottocrat_troubletickets:title:ticket_title:HelpDesk_Title:V',
                         'ottocrat_troubletickets:parent_id:parent_id:HelpDesk_Related_To:V',
                         'ottocrat_troubletickets:status:ticketstatus:HelpDesk_Status:V',
                         'ottocrat_troubletickets:product_id:product_id:HelpDesk_Product_Name:V',
                         'ottocrat_crmentity:smownerid:assigned_user_id:HelpDesk_Assigned_To:V'),

		   Array('ottocrat_quotes:quote_no:quote_no:Quotes_Quote_No:V',
			 'ottocrat_quotes:subject:subject:Quotes_Subject:V',
                         'ottocrat_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                         'ottocrat_quotes:potentialid:potential_id:Quotes_Potential_Name:V',
						 'ottocrat_quotes:accountid:account_id:Quotes_Account_Name:V',
                         'ottocrat_quotes:total:hdnGrandTotal:Quotes_Total:V',
			 'ottocrat_crmentity:smownerid:assigned_user_id:Quotes_Assigned_To:V'),

		   Array('ottocrat_quotes:subject:subject:Quotes_Subject:V',
                         'ottocrat_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                         'ottocrat_quotes:potentialid:potential_id:Quotes_Potential_Name:V',
						'ottocrat_quotes:accountid:account_id:Quotes_Account_Name:V',
                         'ottocrat_quotes:validtill:validtill:Quotes_Valid_Till:D',
			 'ottocrat_crmentity:smownerid:assigned_user_id:Quotes_Assigned_To:V'),

		   Array('ottocrat_quotes:subject:subject:Quotes_Subject:V',
                         'ottocrat_quotes:potentialid:potential_id:Quotes_Potential_Name:V',
						'ottocrat_quotes:accountid:account_id:Quotes_Account_Name:V',
                         'ottocrat_quotes:validtill:validtill:Quotes_Valid_Till:D',
                         'ottocrat_crmentity:smownerid:assigned_user_id:Quotes_Assigned_To:V'),

		   Array('ottocrat_activity:status:taskstatus:Calendar_Status:V',
                         'ottocrat_activity:activitytype:activitytype:Calendar_Type:V',
                         'ottocrat_activity:subject:subject:Calendar_Subject:V',
                         'ottocrat_seactivityrel:crmid:parent_id:Calendar_Related_to:V',
                         'ottocrat_activity:date_start:date_start:Calendar_Start_Date:D',
                         'ottocrat_activity:due_date:due_date:Calendar_End_Date:D',
                         'ottocrat_crmentity:smownerid:assigned_user_id:Calendar_Assigned_To:V'),

		   Array('ottocrat_activity:subject:subject:Emails_Subject:V',
       			 'ottocrat_emaildetails:to_email:saved_toid:Emails_To:V',
                 	 'ottocrat_activity:date_start:date_start:Emails_Date_Sent:D'),

		   Array('ottocrat_invoice:invoice_no:invoice_no:Invoice_Invoice_No:V',
                         'ottocrat_invoice:subject:subject:Invoice_Subject:V',
                         'ottocrat_invoice:salesorderid:salesorder_id:Invoice_Sales_Order:V',
                         'ottocrat_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
                         'ottocrat_invoice:total:hdnGrandTotal:Invoice_Total:V',
                         'ottocrat_crmentity:smownerid:assigned_user_id:Invoice_Assigned_To:V'),

		  Array('ottocrat_notes:note_no:note_no:Notes_Note_No:V',
		  				'ottocrat_notes:title:notes_title:Notes_Title:V',
                        'ottocrat_notes:filename:filename:Notes_File:V',
                        'ottocrat_crmentity:modifiedtime:modifiedtime:Notes_Modified_Time:DT',
		  				'ottocrat_crmentity:smownerid:assigned_user_id:Notes_Assigned_To:V'),

		  Array('ottocrat_pricebook:pricebook_no:pricebook_no:PriceBooks_PriceBook_No:V',
					  'ottocrat_pricebook:bookname:bookname:PriceBooks_Price_Book_Name:V',
                        'ottocrat_pricebook:active:active:PriceBooks_Active:V',
                        'ottocrat_pricebook:currency_id:currency_id:PriceBooks_Currency:V'),

		  Array('ottocrat_products:product_no:product_no:Products_Product_No:V',
		  		'ottocrat_products:productname:productname:Products_Product_Name:V',
                        'ottocrat_products:productcode:productcode:Products_Part_Number:V',
                        'ottocrat_products:commissionrate:commissionrate:Products_Commission_Rate:V',
			'ottocrat_products:qtyinstock:qtyinstock:Products_Quantity_In_Stock:V',
                        'ottocrat_products:qty_per_unit:qty_per_unit:Products_Qty/Unit:V',
                        'ottocrat_products:unit_price:unit_price:Products_Unit_Price:V'),

		  Array('ottocrat_purchaseorder:purchaseorder_no:purchaseorder_no:PurchaseOrder_PurchaseOrder_No:V',
                        'ottocrat_purchaseorder:subject:subject:PurchaseOrder_Subject:V',
                        'ottocrat_purchaseorder:vendorid:vendor_id:PurchaseOrder_Vendor_Name:V',
                        'ottocrat_purchaseorder:tracking_no:tracking_no:PurchaseOrder_Tracking_Number:V',
						'ottocrat_purchaseorder:total:hdnGrandTotal:PurchaseOrder_Total:V',
                        'ottocrat_crmentity:smownerid:assigned_user_id:PurchaseOrder_Assigned_To:V'),

	          Array('ottocrat_salesorder:salesorder_no:salesorder_no:SalesOrder_SalesOrder_No:V',
                        'ottocrat_salesorder:subject:subject:SalesOrder_Subject:V',
						'ottocrat_salesorder:accountid:account_id:SalesOrder_Account_Name:V',
                        'ottocrat_salesorder:quoteid:quote_id:SalesOrder_Quote_Name:V',
                        'ottocrat_salesorder:total:hdnGrandTotal:SalesOrder_Total:V',
                        'ottocrat_crmentity:smownerid:assigned_user_id:SalesOrder_Assigned_To:V'),

	          Array('ottocrat_vendor:vendor_no:vendor_no:Vendors_Vendor_No:V',
			  'ottocrat_vendor:vendorname:vendorname:Vendors_Vendor_Name:V',
			'ottocrat_vendor:phone:phone:Vendors_Phone:V',
			'ottocrat_vendor:email:email:Vendors_Email:V',
                        'ottocrat_vendor:category:category:Vendors_Category:V'),




		 Array(//'ottocrat_faq:id::Faq_FAQ_Id:I',
		 		'ottocrat_faq:faq_no:faq_no:Faq_Faq_No:V',
		       'ottocrat_faq:question:question:Faq_Question:V',
		       'ottocrat_faq:category:faqcategories:Faq_Category:V',
		       'ottocrat_faq:product_id:product_id:Faq_Product_Name:V',
		       'ottocrat_crmentity:createdtime:createdtime:Faq_Created_Time:DT',
                       'ottocrat_crmentity:modifiedtime:modifiedtime:Faq_Modified_Time:DT'),
		      //this sequence has to be maintained
		 Array('ottocrat_campaign:campaign_no:campaign_no:Campaigns_Campaign_No:V',
		 		'ottocrat_campaign:campaignname:campaignname:Campaigns_Campaign_Name:V',
		       'ottocrat_campaign:campaigntype:campaigntype:Campaigns_Campaign_Type:N',
		       'ottocrat_campaign:campaignstatus:campaignstatus:Campaigns_Campaign_Status:N',
		       'ottocrat_campaign:expectedrevenue:expectedrevenue:Campaigns_Expected_Revenue:V',
		       'ottocrat_campaign:closingdate:closingdate:Campaigns_Expected_Close_Date:D',
		       'ottocrat_crmentity:smownerid:assigned_user_id:Campaigns_Assigned_To:V'),


		 Array('subject:subject:subject:Subject:V',
		       'from:fromname:fromname:From:N',
		       'to:tpname:toname:To:N',
		       'body:body:body:Body:V'),

		 Array ('ottocrat_faq:question:question:Faq_Question:V',
		 	'ottocrat_faq:status:faqstatus:Faq_Status:V',
			'ottocrat_faq:product_id:product_id:Faq_Product_Name:V',
			'ottocrat_faq:category:faqcategories:Faq_Category:V',
			'ottocrat_crmentity:createdtime:createdtime:Faq_Created_Time:DT'),

		 Array( 'ottocrat_faq:question:question:Faq_Question:V',
			 'ottocrat_faq:answer:faq_answer:Faq_Answer:V',
			 'ottocrat_faq:status:faqstatus:Faq_Status:V',
			 'ottocrat_faq:product_id:product_id:Faq_Product_Name:V',
			 'ottocrat_faq:category:faqcategories:Faq_Category:V',
			 'ottocrat_crmentity:createdtime:createdtime:Faq_Created_Time:DT'),

		 Array(	 'ottocrat_purchaseorder:subject:subject:PurchaseOrder_Subject:V',
			 'ottocrat_purchaseorder:postatus:postatus:PurchaseOrder_Status:V',
			 'ottocrat_purchaseorder:vendorid:vendor_id:PurchaseOrder_Vendor_Name:V',
			 'ottocrat_crmentity:smownerid:assigned_user_id:PurchaseOrder_Assigned_To:V',
			 'ottocrat_purchaseorder:duedate:duedate:PurchaseOrder_Due_Date:V'),

		 Array ('ottocrat_purchaseorder:subject:subject:PurchaseOrder_Subject:V',
			 'ottocrat_purchaseorder:vendorid:vendor_id:PurchaseOrder_Vendor_Name:V',
			 'ottocrat_crmentity:smownerid:assigned_user_id:PurchaseOrder_Assigned_To:V',
			 'ottocrat_purchaseorder:postatus:postatus:PurchaseOrder_Status:V',
			 'ottocrat_purchaseorder:carrier:carrier:PurchaseOrder_Carrier:V',
			 'ottocrat_poshipads:ship_street:ship_street:PurchaseOrder_Shipping_Address:V'),

		 Array(  'ottocrat_invoice:invoice_no:invoice_no:Invoice_Invoice_No:V',
		 	 'ottocrat_invoice:subject:subject:Invoice_Subject:V',
			 'ottocrat_invoice:accountid:account_id:Invoice_Account_Name:V',
			 'ottocrat_invoice:salesorderid:salesorder_id:Invoice_Sales_Order:V',
			 'ottocrat_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
			 'ottocrat_crmentity:smownerid:assigned_user_id:Invoice_Assigned_To:V',
			 'ottocrat_crmentity:createdtime:createdtime:Invoice_Created_Time:DT'),

		 Array(	 'ottocrat_invoice:invoice_no:invoice_no:Invoice_Invoice_No:V',
			 'ottocrat_invoice:subject:subject:Invoice_Subject:V',
			 'ottocrat_invoice:accountid:account_id:Invoice_Account_Name:V',
			 'ottocrat_invoice:salesorderid:salesorder_id:Invoice_Sales_Order:V',
			 'ottocrat_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
			 'ottocrat_invoiceshipads:ship_street:ship_street:Invoice_Shipping_Address:V',
			 'ottocrat_crmentity:smownerid:assigned_user_id:Invoice_Assigned_To:V'),

		 Array(	 'ottocrat_salesorder:subject:subject:SalesOrder_Subject:V',
			 'ottocrat_salesorder:accountid:account_id:SalesOrder_Account_Name:V',
			 'ottocrat_salesorder:sostatus:sostatus:SalesOrder_Status:V',
			 'ottocrat_crmentity:smownerid:assigned_user_id:SalesOrder_Assigned_To:V',
			 'ottocrat_soshipads:ship_street:ship_street:SalesOrder_Shipping_Address:V',
			 'ottocrat_salesorder:carrier:carrier:SalesOrder_Carrier:V'),

                  );



$cvstdfilters = Array(Array('columnname'=>'ottocrat_crmentity:modifiedtime:modifiedtime:Leads_Modified_Time',
                            'datefilter'=>'thismonth',
                            'startdate'=>'2005-06-01',
                            'enddate'=>'2005-06-30'),

		      Array('columnname'=>'ottocrat_crmentity:createdtime:createdtime:Accounts_Created_Time',
                            'datefilter'=>'thisweek',
                            'startdate'=>'2005-06-19',
                            'enddate'=>'2005-06-25'),

		      Array('columnname'=>'ottocrat_contactsubdetails:birthday:birthday:Contacts_Birthdate',
                            'datefilter'=>'today',
                            'startdate'=>'2005-06-25',
                            'enddate'=>'2005-06-25')
                     );

$cvadvfilters = Array(
                	Array(
               			 Array('columnname'=>'ottocrat_leaddetails:leadstatus:leadstatus:Leads_Lead_Status:V',
		                      'comparator'=>'e',
        		              'value'=>'Hot'
                     			)
                     	 ),
		      		Array(
                          Array('columnname'=>'ottocrat_account:account_type:accounttype:Accounts_Type:V',
                                'comparator'=>'e',
                                 'value'=>'Prospect'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'ottocrat_potential:sales_stage:sales_stage:Potentials_Sales_Stage:V',
                                  'comparator'=>'e',
                                  'value'=>'Closed Won'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'ottocrat_potential:sales_stage:sales_stage:Potentials_Sales_Stage:V',
                                  'comparator'=>'e',
                                  'value'=>'Prospecting'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'ottocrat_troubletickets:status:ticketstatus:HelpDesk_Status:V',
                                  'comparator'=>'n',
                                  'value'=>'Closed'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'ottocrat_troubletickets:priority:ticketpriorities:HelpDesk_Priority:V',
                                  'comparator'=>'e',
                                  'value'=>'High'
                                 )
                           ),
				     Array(
	                        Array('columnname'=>'ottocrat_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                                  'comparator'=>'n',
                                  'value'=>'Accepted'
                                 ),
						    Array('columnname'=>'ottocrat_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                                  'comparator'=>'n',
                                  'value'=>'Rejected'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'ottocrat_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                                  'comparator'=>'e',
                                  'value'=>'Rejected'
                                 )
			 ),

			Array(
                          Array('columnname'=>'ottocrat_faq:status:faqstatus:Faq_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Draft'
                                 )
			 ),

			Array(
                          Array('columnname'=>'ottocrat_faq:status:faqstatus:Faq_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Published'
                                 )
			 ),

			Array(
                          Array('columnname'=>'ottocrat_purchaseorder:postatus:postatus:PurchaseOrder_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Created, Approved, Delivered'
                                 )
			 ),

			Array(
                          Array('columnname'=>'ottocrat_purchaseorder:postatus:postatus:PurchaseOrder_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Received Shipment'
                                 )
			 ),

			Array(
                          Array('columnname'=>'ottocrat_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Created, Approved, Sent'
                                 )
			 ),

			Array(
                          Array('columnname'=>'ottocrat_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Paid'
                                 )
			 ),

			Array(
                          Array('columnname'=>'ottocrat_salesorder:sostatus:sostatus:SalesOrder_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Created, Approved'
                                 )
			 )

                     );

foreach($customviews as $key=>$customview)
{
	$queryid = insertCustomView($customview['viewname'],$customview['setdefault'],$customview['setmetrics'],$customview['cvmodule'],$customview['status'],$customview['userid']);
	insertCvColumns($queryid,$cvcolumns[$key]);

	if(isset($cvstdfilters[$customview['stdfilterid']]))
	{
		$i = $customview['stdfilterid'];
		insertCvStdFilter($queryid,$cvstdfilters[$i]['columnname'],$cvstdfilters[$i]['datefilter'],$cvstdfilters[$i]['startdate'],$cvstdfilters[$i]['enddate']);
	}
	if(isset($cvadvfilters[$customview['advfilterid']]))
	{
		insertCvAdvFilter($queryid,$cvadvfilters[$customview['advfilterid']]);
	}
}

	/** to store the details of the customview in ottocrat_customview table
	  * @param $viewname :: Type String
	  * @param $setdefault :: Type Integer
	  * @param $setmetrics :: Type Integer
	  * @param $cvmodule :: Type String
	  * @returns  $customviewid of the stored custom view :: Type integer
	 */
function insertCustomView($viewname,$setdefault,$setmetrics,$cvmodule,$status,$userid)
{
	global $adb;

	$genCVid = $adb->getUniqueID("ottocrat_customview");

	if($genCVid != "")
	{

		$customviewsql = "insert into ottocrat_customview(cvid,viewname,setdefault,setmetrics,entitytype,status,userid) values(?,?,?,?,?,?,?)";
		$customviewparams = array($genCVid, $viewname, $setdefault, $setmetrics, $cvmodule, $status, $userid);
		$customviewresult = $adb->pquery($customviewsql, $customviewparams);
	}
	return $genCVid;
}

	/** to store the custom view columns of the customview in ottocrat_cvcolumnlist table
	  * @param $cvid :: Type Integer
	  * @param $columnlist :: Type Array of columnlists
	 */
function insertCvColumns($CVid,$columnslist)
{
	global $adb;
	if($CVid != "")
	{
		for($i=0;$i<count($columnslist);$i++)
		{
			$columnsql = "insert into ottocrat_cvcolumnlist (cvid,columnindex,columnname) values(?,?,?)";
			$columnparams = array($CVid, $i, $columnslist[$i]);
			$columnresult = $adb->pquery($columnsql, $columnparams);
		}
	}
}

	/** to store the custom view stdfilter of the customview in ottocrat_cvstdfilter table
	  * @param $cvid :: Type Integer
	  * @param $filtercolumn($tablename:$columnname:$fieldname:$fieldlabel) :: Type String
	  * @param $filtercriteria(filter name) :: Type String
	  * @param $startdate :: Type String
	  * @param $enddate :: Type String
	  * returns nothing
	 */
function insertCvStdFilter($CVid,$filtercolumn,$filtercriteria,$startdate,$enddate)
{
	global $adb;
	if($CVid != "")
	{
		$stdfiltersql = "insert into ottocrat_cvstdfilter(cvid,columnname,stdfilter,startdate,enddate) values (?,?,?,?,?)";
		$stdfilterparams = array($CVid, $filtercolumn, $filtercriteria, $startdate, $enddate);
		$stdfilterresult = $adb->pquery($stdfiltersql, $stdfilterparams);
	}
}

	/** to store the custom view advfilter of the customview in ottocrat_cvadvfilter table
	  * @param $cvid :: Type Integer
	  * @param $filters :: Type Array('columnname'=>$tablename:$columnname:$fieldname:$fieldlabel,'comparator'=>$comparator,'value'=>$value)
	  * returns nothing
	 */

function insertCvAdvFilter($CVid,$filters)
{
	global $adb;
	if($CVid != "")
	{
		$columnIndexArray = array();
		foreach($filters as $i=>$filter)
		{
			$advfiltersql = "insert into ottocrat_cvadvfilter(cvid,columnindex,columnname,comparator,value) values (?,?,?,?,?)";
			$advfilterparams = array($CVid, $i, $filter['columnname'], $filter['comparator'], $filter['value']);
			$advfilterresult = $adb->pquery($advfiltersql, $advfilterparams);
		}
		$conditionExpression = implode(' and ', $columnIndexArray);
		$adb->pquery('INSERT INTO ottocrat_cvadvfilter_grouping VALUES(?,?,?,?)', array(1, $CVid, '', $conditionExpression));
	}
}
?>
