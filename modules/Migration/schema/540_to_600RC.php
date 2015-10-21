<?php
/*+********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *********************************************************************************/

if (!defined('OTTOCRAT_UPGRADE')) die('Invalid entry point');

vimport('~~include/utils/utils.php');
vimport('~~modules/com_ottocrat_workflow/include.inc');
vimport('~~modules/com_ottocrat_workflow/tasks/VTEntityMethodTask.inc');
vimport('~~modules/com_ottocrat_workflow/VTEntityMethodManager.inc');
vimport('~~include/Webservices/Utils.php');
vimport('~~modules/Users/Users.php');

if(defined('OTTOCRAT_UPGRADE')) {
	//Collating all module package updates here
	updateVtlibModule('Import', 'packages/ottocrat/mandatory/Import.zip');
	updateVtlibModule('MailManager', 'packages/ottocrat/mandatory/MailManager.zip');
	updateVtlibModule('Mobile', 'packages/ottocrat/mandatory/Mobile.zip');
	updateVtlibModule('ModTracker', 'packages/ottocrat/mandatory/ModTracker.zip');
	updateVtlibModule('Services', "packages/ottocrat/mandatory/Services.zip");
	updateVtlibModule('ServiceContracts', "packages/ottocrat/mandatory/ServiceContracts.zip");
	updateVtlibModule('WSAPP', 'packages/ottocrat/mandatory/WSAPP.zip');
	updateVtlibModule('Assets', 'packages/ottocrat/optional/Assets.zip');
	updateVtlibModule('CustomerPortal', 'packages/ottocrat/optional/CustomerPortal.zip');
	updateVtlibModule('ModComments', "packages/ottocrat/optional/ModComments.zip");
	updateVtlibModule('Projects', "packages/ottocrat/optional/Projects.zip");
	updateVtlibModule('RecycleBin', 'packages/ottocrat/optional/RecycleBin.zip');
	updateVtlibModule('SMSNotifier', "packages/ottocrat/optional/SMSNotifier.zip");
	updateVtlibModule("Webforms","packages/ottocrat/optional/Webforms.zip");
	installVtlibModule('Google', 'packages/ottocrat/optional/Google.zip');
	installVtlibModule('EmailTemplates', 'packages/ottocrat/optional/EmailTemplates.zip');

	// updated language packs.

	updateVtlibModule('PT Brasil', 'packages/ottocrat/optional/BrazilianLanguagePack_bz_bz.zip');
	updateVtlibModule('British English', 'packages/ottocrat/optional/BritishLanguagePack_br_br.zip');
	updateVtlibModule('Dutch', 'packages/ottocrat/optional/Dutch.zip');
	updateVtlibModule('Deutsch', 'packages/ottocrat/optional/Deutsch.zip');
	updateVtlibModule('French', 'packages/ottocrat/optional/French.zip');
	updateVtlibModule('Hungarian', 'packages/ottocrat/optional/Hungarian.zip');
	updateVtlibModule('Mexican Spanish', 'packages/ottocrat/optional/MexicanSpanishLanguagePack_es_mx.zip');
	updateVtlibModule('Spanish', 'packages/ottocrat/optional/Spanish.zip');
	installVtlibModule('Italian', 'packages/ottocrat/optional/ItalianLanguagePack_it_it.zip');
	installVtlibModule('RomanianLanguagePack_rm_rm', 'packages/ottocrat/optional/RomanianLanguagePack_rm_rm.zip');
	installVtlibModule('Turkce', 'packages/ottocrat/optional/TurkishLanguagePack_tr_tr.zip');
	installVtlibModule('Russian', 'packages/ottocrat/optional/Russian.zip');
	installVtlibModule('Polish', 'packages/ottocrat/optional/PolishLanguagePack_pl_pl.zip');
	installVtlibModule('Russian', 'packages/ottocrat/optional/Russian.zip');
}

if(!defined('INSTALLATION_MODE')) {
	Migration_Index_View::ExecuteQuery('ALTER TABLE com_ottocrat_workflows ADD COLUMN filtersavedinnew int(1)', array());
}

Migration_Index_View::ExecuteQuery('UPDATE com_ottocrat_workflows SET filtersavedinnew = 5', array());

// Core workflow schema dependecy introduced in 6.1.0
$adb=PearDatabase::getInstance();
$result = $adb->pquery("show columns from com_ottocrat_workflows like ?", array('schtypeid'));
if (!($adb->num_rows($result))) { $adb->pquery("ALTER TABLE com_ottocrat_workflows ADD schtypeid INT(10)", array()); }
$result = $adb->pquery("show columns from com_ottocrat_workflows like ?", array('schtime'));
if (!($adb->num_rows($result))) { $adb->pquery("ALTER TABLE com_ottocrat_workflows ADD schtime TIME", array()); }
$result = $adb->pquery("show columns from com_ottocrat_workflows like ?", array('schdayofmonth'));
if (!($adb->num_rows($result))) {$adb->pquery("ALTER TABLE com_ottocrat_workflows ADD schdayofmonth VARCHAR(100)", array());}
$result = $adb->pquery("show columns from com_ottocrat_workflows like ?", array('schdayofweek'));
if (!($adb->num_rows($result))) {$adb->pquery("ALTER TABLE com_ottocrat_workflows ADD schdayofweek VARCHAR(100)", array());}
$result = $adb->pquery("show columns from com_ottocrat_workflows like ?", array('schannualdates'));
if (!($adb->num_rows($result))) {$adb->pquery("ALTER TABLE com_ottocrat_workflows ADD schannualdates VARCHAR(100)", array());}
$result = $adb->pquery("show columns from com_ottocrat_workflows like ?", array('nexttrigger_time'));
if (!($adb->num_rows($result))) {$adb->pquery("ALTER TABLE com_ottocrat_workflows ADD nexttrigger_time DATETIME", array());}

if(!defined('INSTALLATION_MODE')) {
	Migration_Index_View::ExecuteQuery("CREATE TABLE IF NOT EXISTS com_ottocrat_workflow_tasktypes (
					id int(11) NOT NULL,
					tasktypename varchar(255) NOT NULL,
					label varchar(255),
					classname varchar(255),
					classpath varchar(255),
					templatepath varchar(255),
					modules text(500),
					sourcemodule varchar(255)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8", array());

	$taskTypes = array();
	$defaultModules = array('include' => array(), 'exclude'=>array());
	$createToDoModules = array('include' => array("Leads","Accounts","Potentials","Contacts","HelpDesk","Campaigns","Quotes","PurchaseOrder","SalesOrder","Invoice"), 'exclude'=>array("Calendar", "FAQ", "Events"));
	$createEventModules = array('include' => array("Leads","Accounts","Potentials","Contacts","HelpDesk","Campaigns"), 'exclude'=>array("Calendar", "FAQ", "Events"));

	$taskTypes[] = array("name"=>"VTEmailTask", "label"=>"Send Mail", "classname"=>"VTEmailTask", "classpath"=>"modules/com_ottocrat_workflow/tasks/VTEmailTask.inc", "templatepath"=>"com_ottocrat_workflow/taskforms/VTEmailTask.tpl", "modules"=>$defaultModules, "sourcemodule"=>'');
	$taskTypes[] = array("name"=>"VTEntityMethodTask", "label"=>"Invoke Custom Function", "classname"=>"VTEntityMethodTask", "classpath"=>"modules/com_ottocrat_workflow/tasks/VTEntityMethodTask.inc", "templatepath"=>"com_ottocrat_workflow/taskforms/VTEntityMethodTask.tpl", "modules"=>$defaultModules, "sourcemodule"=>'');
	$taskTypes[] = array("name"=>"VTCreateTodoTask", "label"=>"Create Todo", "classname"=>"VTCreateTodoTask", "classpath"=>"modules/com_ottocrat_workflow/tasks/VTCreateTodoTask.inc", "templatepath"=>"com_ottocrat_workflow/taskforms/VTCreateTodoTask.tpl", "modules"=>$createToDoModules, "sourcemodule"=>'');
	$taskTypes[] = array("name"=>"VTCreateEventTask", "label"=>"Create Event", "classname"=>"VTCreateEventTask", "classpath"=>"modules/com_ottocrat_workflow/tasks/VTCreateEventTask.inc", "templatepath"=>"com_ottocrat_workflow/taskforms/VTCreateEventTask.tpl", "modules"=>$createEventModules, "sourcemodule"=>'');
	$taskTypes[] = array("name"=>"VTUpdateFieldsTask", "label"=>"Update Fields", "classname"=>"VTUpdateFieldsTask", "classpath"=>"modules/com_ottocrat_workflow/tasks/VTUpdateFieldsTask.inc", "templatepath"=>"com_ottocrat_workflow/taskforms/VTUpdateFieldsTask.tpl", "modules"=>$defaultModules, "sourcemodule"=>'');
	$taskTypes[] = array("name"=>"VTCreateEntityTask", "label"=>"Create Entity", "classname"=>"VTCreateEntityTask", "classpath"=>"modules/com_ottocrat_workflow/tasks/VTCreateEntityTask.inc", "templatepath"=>"com_ottocrat_workflow/taskforms/VTCreateEntityTask.tpl", "modules"=>$defaultModules, "sourcemodule"=>'');
	$taskTypes[] = array("name"=>"VTSMSTask", "label"=>"SMS Task", "classname"=>"VTSMSTask", "classpath"=>"modules/com_ottocrat_workflow/tasks/VTSMSTask.inc", "templatepath"=>"com_ottocrat_workflow/taskforms/VTSMSTask.tpl", "modules"=>$defaultModules, "sourcemodule"=>'SMSNotifier');

	foreach ($taskTypes as $taskType) {
		VTTaskType::registerTaskType($taskType);
	}
}


Migration_Index_View::ExecuteQuery("CREATE TABLE IF NOT EXISTS ottocrat_shorturls (
					id int(11) NOT NULL AUTO_INCREMENT,
					uid varchar(50) DEFAULT NULL,
					handler_path varchar(400) DEFAULT NULL,
					handler_class varchar(100) DEFAULT NULL,
					handler_function varchar(100) DEFAULT NULL,
					handler_data varchar(255) DEFAULT NULL,
					PRIMARY KEY (id),
					KEY uid (uid)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8", array());

$moduleInstance = Ottocrat_Module::getInstance('Potentials');
$block = Ottocrat_Block::getInstance('LBL_OPPORTUNITY_INFORMATION', $moduleInstance);

$forecast_field = new Ottocrat_Field();
$forecast_field->name = 'forecast_amount';
$forecast_field->label = 'Forecast Amount';
$forecast_field->table ='ottocrat_potential';
$forecast_field->column = 'forecast_amount';
$forecast_field->columntype = 'decimal(25,4)';
$forecast_field->typeofdata = 'N~O';
$forecast_field->uitype = '71';
$forecast_field->masseditable = '0';
$block->addField($forecast_field);

global $adb;
$workflowManager = new VTWorkflowManager($adb);
$taskManager = new VTTaskManager($adb);

$potentailsWorkFlow = $workflowManager->newWorkFlow("Potentials");
$potentailsWorkFlow->test = '';
$potentailsWorkFlow->description = "Calculate or Update forecast amount";
$potentailsWorkFlow->executionCondition = VTWorkflowManager::$ON_EVERY_SAVE;
$potentailsWorkFlow->defaultworkflow = 1;
$workflowManager->save($potentailsWorkFlow);

$task = $taskManager->createTask('VTUpdateFieldsTask', $potentailsWorkFlow->id);
$task->active = true;
$task->summary = 'update forecast amount';
$task->field_value_mapping = '[{"fieldname":"forecast_amount","valuetype":"expression","value":"amount * probability / 100"}]';
$taskManager->saveTask($task);

// Change default Sales Man rolename to Sales Person
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_role SET rolename=? WHERE rolename=? and roleid=?", array('Sales Person', 'Sales Man', 'H5'));

if(!defined('INSTALLATION_MODE')) {
	$picklistResult = $adb->pquery('SELECT distinct fieldname FROM ottocrat_field WHERE uitype IN (15,33)', array());
	$numRows = $adb->num_rows($picklistResult);
	for($i=0; $i<$numRows; $i++) {
		$fieldName = $adb->query_result($picklistResult,$i,'fieldname');
		$query = 'ALTER TABLE ottocrat_'.$fieldName.' ADD COLUMN sortorderid INT(1)';
		Migration_Index_View::ExecuteQuery($query, array());
	}
}

$invoiceModuleInstance = Ottocrat_Module::getInstance('Invoice');
$fieldInstance = Ottocrat_Field::getInstance('invoicestatus', $invoiceModuleInstance);
$fieldInstance->setPicklistValues( Array ('Cancel'));

// Email Reporting - added default email reports.

$sql = "INSERT INTO ottocrat_reportfolder (FOLDERNAME,DESCRIPTION,STATE) VALUES(?,?,?)";
$params = array('Email Reports', 'Email Reports', 'SAVED');
Migration_Index_View::ExecuteQuery($sql, $params);

$reportmodules = Array(
	Array('primarymodule' => 'Contacts', 'secondarymodule' => 'Emails'),
	Array('primarymodule' => 'Accounts', 'secondarymodule' => 'Emails'),
	Array('primarymodule' => 'Leads', 'secondarymodule' => 'Emails'),
	Array('primarymodule' => 'Vendors', 'secondarymodule' => 'Emails')
);

$reports = Array(
	Array('reportname' => 'Contacts Email Report',
		'reportfolder' => 'Email Reports',
		'description' => 'Emails sent to Contacts',
		'reporttype' => 'tabular',
		'sortid' => '', 'stdfilterid' => '', 'advfilterid' => '0'),
	Array('reportname' => 'Accounts Email Report',
		'reportfolder' => 'Email Reports',
		'description' => 'Emails sent to Organizations',
		'reporttype' => 'tabular',
		'sortid' => '', 'stdfilterid' => '', 'advfilterid' => '0'),
	Array('reportname' => 'Leads Email Report',
		'reportfolder' => 'Email Reports',
		'description' => 'Emails sent to Leads',
		'reporttype' => 'tabular',
		'sortid' => '', 'stdfilterid' => '', 'advfilterid' => '0'),
	Array('reportname' => 'Vendors Email Report',
		'reportfolder' => 'Email Reports',
		'description' => 'Emails sent to Vendors',
		'reporttype' => 'tabular',
		'sortid' => '', 'stdfilterid' => '', 'advfilterid' => '0')
);

$selectcolumns = Array(
	Array('ottocrat_contactdetails:lastname:Contacts_Last_Name:lastname:V',
		'ottocrat_contactdetails:email:Contacts_Email:email:E',
		'ottocrat_activity:subject:Emails_Subject:subject:V',
		'ottocrat_email_track:access_count:Emails_Access_Count:access_count:V'),
	Array('ottocrat_account:accountname:Accounts_Account_Name:accountname:V',
		'ottocrat_account:phone:Accounts_Phone:phone:V',
		'ottocrat_account:email1:Accounts_Email:email1:E',
		'ottocrat_activity:subject:Emails_Subject:subject:V',
		'ottocrat_email_track:access_count:Emails_Access_Count:access_count:V'),
	Array('ottocrat_leaddetails:lastname:Leads_Last_Name:lastname:V',
		'ottocrat_leaddetails:company:Leads_Company:company:V',
		'ottocrat_leaddetails:email:Leads_Email:email:E',
		'ottocrat_activity:subject:Emails_Subject:subject:V',
		'ottocrat_email_track:access_count:Emails_Access_Count:access_count:V'),
	Array('ottocrat_vendor:vendorname:Vendors_Vendor_Name:vendorname:V',
		'ottocrat_vendor:glacct:Vendors_GL_Account:glacct:V',
		'ottocrat_vendor:email:Vendors_Email:email:E',
		'ottocrat_activity:subject:Emails_Subject:subject:V',
		'ottocrat_email_track:access_count:Emails_Access_Count:access_count:V'),
);

$advfilters = Array(
	Array(
		Array(
			'columnname' => 'ottocrat_email_track:access_count:Emails_Access_Count:access_count:V',
			'comparator' => 'n',
			'value' => ''
		)
	)
);

foreach ($reports as $key => $report) {
	$queryid = Migration_Index_View::insertSelectQuery();
	$sql = 'SELECT MAX(folderid) AS count FROM ottocrat_reportfolder';
	$result = $adb->query($sql);
	$folderid = $adb->query_result($result, 0, 'count');
	Migration_Index_View::insertReports($queryid, $folderid, $report['reportname'], $report['description'], $report['reporttype']);
	Migration_Index_View::insertSelectColumns($queryid, $selectcolumns[$key]);
	Migration_Index_View::insertReportModules($queryid, $reportmodules[$key]['primarymodule'], $reportmodules[$key]['secondarymodule']);
	if(isset($advfilters[$report['advfilterid']])) {
		Migration_Index_View::insertAdvFilter($queryid, $advfilters[$report['advfilterid']]);
	}
}

// TODO : need to review this after adding report sharing feature
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_report SET sharingtype='Public'", array());
//End.

//Currency Decimal places handling
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_account MODIFY COLUMN annualrevenue decimal(25,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_leaddetails MODIFY COLUMN annualrevenue decimal(25,5)", array());
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_field SET typeofdata='N~O' WHERE fieldlabel='Annual Revenue' and typeofdata='I~O'",array());

Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_currency_info MODIFY COLUMN conversion_rate decimal(12,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_productcurrencyrel MODIFY COLUMN actual_price decimal(28,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_productcurrencyrel MODIFY COLUMN converted_price decimal(28,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_pricebookproductrel MODIFY COLUMN listprice decimal(27,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_inventoryproductrel MODIFY COLUMN listprice decimal(27,5)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_inventoryproductrel MODIFY COLUMN discount_amount decimal(27,5)", array());

$currencyField = new CurrencyField($value);
$result = $adb->pquery("SELECT fieldname,tablename,columnname FROM ottocrat_field WHERE uitype IN (?,?)",array('71','72'));
$count = $adb->num_rows($result);
for($i=0;$i<$count;$i++) {
	$fieldName = $adb->query_result($result,$i,'fieldname');
	$tableName = $adb->query_result($result,$i,'tablename');
	$columnName = $adb->query_result($result,$i,'columnname');

	$tableAndColumnSize = array();
	$tableInfo = $adb->database->MetaColumns($tableName);
	foreach ($tableInfo as $column) {
		$max_length = $column->max_length;
		$scale = $column->scale;

		$tableAndColumnSize[$tableName][$column->name]['max_length'] = $max_length;
		$tableAndColumnSize[$tableName][$column->name]['scale'] = $scale;
	}
	if(!empty($tableAndColumnSize[$tableName][$columnName]['scale'])) {
		$decimalsToChange = $currencyField->maxNumberOfDecimals - $tableAndColumnSize[$tableName][$columnName]['scale'];
		if($decimalsToChange != 0) {
			$maxlength = $tableAndColumnSize[$tableName][$columnName]['max_length'] + $decimalsToChange;
			$decimalDigits = $tableAndColumnSize[$tableName][$columnName]['scale'] + $decimalsToChange;

			Migration_Index_View::ExecuteQuery("ALTER TABLE " .$tableName." MODIFY COLUMN ".$columnName." decimal(?,?)", array($maxlength, $decimalDigits));
		}
	}
}

$moduleInstance = Ottocrat_Module::getInstance('Users');
$currencyBlock = Ottocrat_Block::getInstance('LBL_CURRENCY_CONFIGURATION', $moduleInstance);

$currency_decimals_field = new Ottocrat_Field();
$currency_decimals_field->name = 'no_of_currency_decimals';
$currency_decimals_field->label = 'Number Of Currency Decimals';
$currency_decimals_field->table ='ottocrat_users';
$currency_decimals_field->column = 'no_of_currency_decimals';
$currency_decimals_field->columntype = 'VARCHAR(2)';
$currency_decimals_field->typeofdata = 'V~O';
$currency_decimals_field->uitype = 16;
$currency_decimals_field->defaultvalue = '2';
$currency_decimals_field->sequence = 6;
$currency_decimals_field->helpinfo = "<b>Currency - Number of Decimal places</b> <br/><br/>".
		"Number of decimal places specifies how many number of decimals will be shown after decimal separator.<br/>".
		"<b>Eg:</b> 123.00";
$currencyBlock->addField($currency_decimals_field);
$currency_decimals_field->setPicklistValues(array("1","2","3","4","5"));
//Currency Decimal places handling - END

$inventoryModules = array('Invoice','SalesOrder','PurchaseOrder','Quotes');
$actions = array('Import','Export');

for($i = 0; $i < count($inventoryModules); $i++) {
	$moduleName = $inventoryModules[$i];
	$moduleInstance = Ottocrat_Module::getInstance($moduleName);

	$blockInstance = new Ottocrat_Block();

	$blockInstance->label = 'LBL_ITEM_DETAILS';
	$blockInstance->sequence = '5';
	$blockInstance->showtitle = '0';

	$moduleInstance->addBlock($blockInstance);

	foreach ($actions as $actionName) {
		Ottocrat_Access::updateTool($moduleInstance, $actionName, true, '');
	}
}

$itemFieldsName = array('productid','quantity','listprice','comment','discount_amount','discount_percent','tax1','tax2','tax3');
$itemFieldsLabel = array('Item Name','Quantity','List Price','Item Comment','Item Discount Amount','Item Discount Percent','Tax1','Tax2','Tax3');
$itemFieldsTypeOfData = array('V~M','V~M','V~M','V~O','V~O','V~O','V~O','V~O','V~O');
$itemFieldsDisplayType = array('10','7','19','19','7','7','83','83','83');

for($i=0; $i<count($inventoryModules); $i++) {
	$moduleName = $inventoryModules[$i];
	$moduleInstance = Ottocrat_Module::getInstance($moduleName);
	$blockInstance = Ottocrat_Block::getInstance('LBL_ITEM_DETAILS',$moduleInstance);

	$relatedmodules = array('Products','Services');

	for($j=0;$j<count($itemFieldsName);$j++) {
		$field = new Ottocrat_Field();

		$field->name = $itemFieldsName[$j];
		$field->label = $itemFieldsLabel[$j];
		$field->column = $itemFieldsName[$j];
		$field->table = 'ottocrat_inventoryproductrel';
		$field->uitype = $itemFieldsDisplayType[$j];
		$field->typeofdata = $itemFieldsTypeOfData[$j];
		$field->readonly = '0';
		$field->displaytype = '5';
		$field->masseditable = '0';

		$blockInstance->addField($field);

		if($itemFieldsName[$j] == 'productid') {
			$field->setRelatedModules($relatedmodules);
		}
	}
}

// Register a new actor type for LineItem API
vtws_addActorTypeWebserviceEntityWithoutName('LineItem', 'include/Webservices/LineItem/OttocratLineItemOperation.php', 'OttocratLineItemOperation', array());

$webserviceObject = OttocratWebserviceObject::fromName($adb,'LineItem');
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_tables(webservice_entity_id,table_name) VALUES (?,?)", array($webserviceObject->getEntityId(), 'ottocrat_inventoryproductrel'));

$fieldTypeId = $adb->getUniqueID("ottocrat_ws_entity_fieldtype");
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_fieldtype(fieldtypeid,table_name, field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId, 'ottocrat_inventoryproductrel', 'productid',"reference"));
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Products'));

$fieldTypeId = $adb->getUniqueID("ottocrat_ws_entity_fieldtype");
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_fieldtype(fieldtypeid,table_name, field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId, 'ottocrat_inventoryproductrel', 'id',"reference"));
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Invoice'));
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'SalesOrder'));
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'PurchaseOrder'));
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Quotes'));

$fieldTypeId = $adb->getUniqueID("ottocrat_ws_entity_fieldtype");
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId,'ottocrat_inventoryproductrel', 'incrementondel',"autogenerated"));

$adb->getUniqueID("ottocrat_inventoryproductrel");
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_inventoryproductrel_seq SET id=(select max(lineitem_id) from ottocrat_inventoryproductrel);",array());
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_ws_entity SET handler_path='include/Webservices/LineItem/OttocratInventoryOperation.php',handler_class='OttocratInventoryOperation' where name in ('Invoice','Quotes','PurchaseOrder','SalesOrder');",array());

$purchaseOrderTabId = getTabid("PurchaseOrder");

$purchaseOrderAddressInformationBlockId = getBlockId($purchaseOrderTabId, "LBL_ADDRESS_INFORMATION");

$invoiceTabId = getTabid("Invoice");
$invoiceTabIdAddressInformationBlockId = getBlockId($invoiceTabId, "LBL_ADDRESS_INFORMATION");
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET block=? where tabid=? and block=?;',
		array($invoiceTabIdAddressInformationBlockId,$invoiceTabId,$purchaseOrderAddressInformationBlockId));

vtws_addActorTypeWebserviceEntityWithName('Tax',
		'include/Webservices/LineItem/OttocratTaxOperation.php',
		'OttocratTaxOperation', array('fieldNames'=>'taxlabel', 'indexField'=>'taxid', 'tableName'=>'ottocrat_inventorytaxinfo'), true);

$webserviceObject = OttocratWebserviceObject::fromName($adb,'Tax');
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_tables(webservice_entity_id,table_name) VALUES (?,?)",array($webserviceObject->getEntityId(),'ottocrat_inventorytaxinfo'));

vtws_addActorTypeWebserviceEntityWithoutName('ProductTaxes',
		'include/Webservices/LineItem/OttocratProductTaxesOperation.php',
		'OttocratProductTaxesOperation', array());

$webserviceObject = OttocratWebserviceObject::fromName($adb,'ProductTaxes');
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_tables(webservice_entity_id,table_name) VALUES (?,?)",array($webserviceObject->getEntityId(),'ottocrat_producttaxrel'));

$fieldTypeId = $adb->getUniqueID("ottocrat_ws_entity_fieldtype");

Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId,'ottocrat_producttaxrel', 'productid',"reference"));
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Products'));

$fieldTypeId = $adb->getUniqueID("ottocrat_ws_entity_fieldtype");
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_fieldtype(fieldtypeid,table_name,field_name,fieldtype) VALUES (?,?,?,?);", array($fieldTypeId,'ottocrat_producttaxrel', 'taxid',"reference"));
Migration_Index_View::ExecuteQuery("INSERT INTO ottocrat_ws_entity_referencetype(fieldtypeid,type) VALUES (?,?)",array($fieldTypeId,'Tax'));

//--
//Changed Columns Display in List view of Leads
$leadsFirstName = 'ottocrat_leaddetails:firstname:firstname:Leads_First_Name:V';
$leadsLastName = 'ottocrat_leaddetails:lastname:lastname:Leads_Last_Name:V';
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_cvcolumnlist SET columnname=? WHERE cvid=? AND columnindex=?", array($leadsFirstName, '1', '1'));
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_cvcolumnlist SET columnname=? WHERE cvid=? AND columnindex=?", array($leadsLastName, '1', '2'));

//Changed the Currency Symbol of Moroccan, Dirham to DH
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_currencies SET currency_symbol=? WHERE currency_name=? AND currency_code=?", array('DH', 'Moroccan, Dirham', 'MAD'));

//Changing picklist values for sales stage of opportunities
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_sales_stage SET sales_stage=? WHERE sales_stage=?", array('Proposal or Price Quote', 'Proposal/Price Quote'));
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_sales_stage SET sales_stage=? WHERE sales_stage=?", array('Negotiation or Review', 'Negotiation/Review'));

//Updating the new picklist values of sales stage in opportunities for migration instances
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_potential SET sales_stage=? WHERE sales_stage=?", array('Proposal or Price Quote', 'Proposal/Price Quote'));
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_potential SET sales_stage=? WHERE sales_stage=?", array('Negotiation or Review', 'Negotiation/Review'));

//Updating Sales Stage History in opportunities related list for migration instances
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_potstagehistory SET stage=? WHERE stage=?", array('Proposal or Price Quote', 'Proposal/Price Quote'));
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_potstagehistory SET stage=? WHERE stage=?", array('Negotiation or Review', 'Negotiation/Review'));

//Updating the sales stage picklist values of opportunities in picklist dependency setup for migration instances
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_picklist_dependency SET sourcevalue=? WHERE sourcevalue=?", array('Proposal or Price Quote', 'Proposal/Price Quote'));
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_picklist_dependency SET sourcevalue=? WHERE sourcevalue=?", array('Negotiation or Review', 'Negotiation/Review'));

//Internationalized the description for webforms
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_settings_field SET description=? WHERE description=?", array('LBL_WEBFORMS_DESCRIPTION', 'Allows you to manage Webforms'));

Migration_Index_View::ExecuteQuery('CREATE TABLE IF NOT EXISTS ottocrat_crmsetup(userid INT(11) NOT NULL, setup_status INT(2))', array());
if (!defined('INSTALLATION_MODE')) {
	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_crmsetup(userid, setup_status) SELECT id, 1 FROM ottocrat_users', array());
}

$discountResult = Migration_Index_View::ExecuteQuery('SELECT * FROM ottocrat_selectcolumn WHERE columnname LIKE "ottocrat_inventoryproductrel:discount:%" ORDER BY columnindex', array());
$num_rows = $adb->num_rows($discountResult);

for ($i=0; $i<$num_rows; $i++) {
	$columnIndex = $adb->query_result($discountResult, $i, 'columnindex');
    $columnName = $adb->query_result($discountResult, $i, 'columnname');
    $queryId = $adb->query_result($discountResult, $i, 'queryid');

    $updatedColumnName = str_replace(':discount:', ':discount_amount:', $columnName);
    $updateQuery = 'UPDATE ottocrat_selectcolumn SET columnname = ? WHERE columnindex = ? and queryid = ?';
    $updateParams = array($updatedColumnName, $columnIndex,$queryId);

	Migration_Index_View::ExecuteQuery($updateQuery, $updateParams);
}

Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_ws_referencetype VALUES (?,?)', array(31,'Campaigns'));

$moduleInstance = Ottocrat_Module::getInstance('Users');
$currencyBlock = Ottocrat_Block::getInstance('LBL_CURRENCY_CONFIGURATION', $moduleInstance);
$truncateTrailingZeros = new Ottocrat_Field();

$truncateTrailingZeros->name = 'truncate_trailing_zeros';
$truncateTrailingZeros->label = 'Truncate Trailing Zeros';
$truncateTrailingZeros->table ='ottocrat_users';
$truncateTrailingZeros->column = 'truncate_trailing_zeros';
$truncateTrailingZeros->columntype = 'varchar(3)';
$truncateTrailingZeros->typeofdata = 'V~O';
$truncateTrailingZeros->uitype = 56;
$truncateTrailingZeros->sequence = 7;
$truncateTrailingZeros->defaultvalue = 0;
$truncateTrailingZeros->helpinfo = "<b> Truncate Trailing Zeros </b> <br/><br/>".
    "It truncated trailing 0s in any of Currency, Decimal and Percentage Field types<br/><br/>".
    "<b>Ex:</b><br/>".
    "If value is 89.00000 then <br/>".
    "decimal and Percentage fields were shows 89<br/>".
    "currency field type - shows 89.00<br/>";
$currencyBlock->addField($truncateTrailingZeros);

Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_productcurrencyrel MODIFY COLUMN actual_price decimal(28,8)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_productcurrencyrel MODIFY COLUMN converted_price decimal(28,8)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_pricebookproductrel MODIFY COLUMN listprice decimal(27,8)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_inventoryproductrel MODIFY COLUMN listprice decimal(27,8)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_inventoryproductrel MODIFY COLUMN discount_amount decimal(27,8)", array());

$currencyField = new CurrencyField($value);
$result = Migration_Index_View::ExecuteQuery("SELECT tablename,columnname FROM ottocrat_field WHERE uitype IN (?,?)",array('71','72'));
$count = $adb->num_rows($result);
for($i=0;$i<$count;$i++) {
	$tableName = $adb->query_result($result,$i,'tablename');
	$columnName = $adb->query_result($result,$i,'columnname');
	Migration_Index_View::ExecuteQuery("ALTER TABLE " .$tableName." MODIFY COLUMN ".$columnName." decimal(?,?)", array(25, 8));
}

Migration_Index_View::ExecuteQuery('DELETE FROM ottocrat_no_of_currency_decimals WHERE no_of_currency_decimalsid=?', array(1));

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET uitype=?, typeofdata=? WHERE fieldname=?',array(71, 'N~O', 'listprice'));

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET typeofdata=? WHERE fieldname=?',array('N~O', 'quantity'));

//--
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET typeofdata=?, uitype =?, fieldlabel=? WHERE fieldname =? and tablename=?', array('N~O', 71, 'Discount', 'discount_amount', 'ottocrat_inventoryproductrel'));

//deleting default workflows
Migration_Index_View::ExecuteQuery("delete from com_ottocrat_workflowtasks where task_id=?", array(11));
Migration_Index_View::ExecuteQuery("delete from com_ottocrat_workflowtasks where task_id=?", array(12));

// Creating Default workflows
$workflowManager = new VTWorkflowManager($adb);
$taskManager = new VTTaskManager($adb);

// Events workflow when Send Notification is checked
$eventsWorkflow = $workflowManager->newWorkFlow("Events");
$eventsWorkflow->test = '[{"fieldname":"sendnotification","operation":"is","value":"true:boolean"}]';
$eventsWorkflow->description = "Workflow for Events when Send Notification is True";
$eventsWorkflow->executionCondition = VTWorkflowManager::$ON_EVERY_SAVE;
$eventsWorkflow->defaultworkflow = 1;
$workflowManager->save($eventsWorkflow);

$task = $taskManager->createTask('VTEmailTask', $eventsWorkflow->id);
$task->active = true;
$task->summary = 'Send Notification Email to Record Owner';
$task->recepient = "\$(assigned_user_id : (Users) email1)";
$task->subject = "Event :  \$subject";
$task->content = '$(assigned_user_id : (Users) last_name) $(assigned_user_id : (Users) first_name) ,<br/>'
        . '<b>Activity Notification Details:</b><br/>'
        . 'Subject             : $subject<br/>'
        . 'Start date and time : $date_start  $time_start ( $(general : (__OttocratMeta__) dbtimezone) ) <br/>'
        . 'End date and time   : $due_date  $time_end ( $(general : (__OttocratMeta__) dbtimezone) ) <br/>'
        . 'Status              : $eventstatus <br/>'
        . 'Priority            : $taskpriority <br/>'
        . 'Related To          : $(parent_id : (Leads) lastname) $(parent_id : (Leads) firstname) $(parent_id : (Accounts) accountname) '
                                . '$(parent_id : (Potentials) potentialname) $(parent_id : (HelpDesk) ticket_title)'
                                . ' $(parent_id : (Campaigns) campaignname) <br/>'
        . 'Contacts List       : $(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname) <br/>'
        . 'Location            : $location <br/>'
        . 'Description         : $description';
$taskManager->saveTask($task);

// Calendar workflow when Send Notification is checked
$calendarWorkflow = $workflowManager->newWorkFlow("Calendar");
$calendarWorkflow->test = '[{"fieldname":"sendnotification","operation":"is","value":"true:boolean"}]';
$calendarWorkflow->description = "Workflow for Calendar Todos when Send Notification is True";
$calendarWorkflow->executionCondition = VTWorkflowManager::$ON_EVERY_SAVE;
$calendarWorkflow->defaultworkflow = 1;
$workflowManager->save($calendarWorkflow);

$task = $taskManager->createTask('VTEmailTask', $calendarWorkflow->id);
$task->active = true;
$task->summary = 'Send Notification Email to Record Owner';
$task->recepient = "\$(assigned_user_id : (Users) email1)";
$task->subject = "Task :  \$subject";
$task->content = '$(assigned_user_id : (Users) last_name) $(assigned_user_id : (Users) first_name) ,<br/>'
        . '<b>Task Notification Details:</b><br/>'
        . 'Subject : $subject<br/>'
        . 'Start date and time : $date_start  $time_start ( $(general : (__OttocratMeta__) dbtimezone) ) <br/>'
        . 'End date and time   : $due_date ( $(general : (__OttocratMeta__) dbtimezone) ) <br/>'
        . 'Status              : $taskstatus <br/>'
        . 'Priority            : $taskpriority <br/>'
        . 'Related To          : $(parent_id : (Leads) lastname) $(parent_id : (Leads) firstname) $(parent_id : (Accounts) accountname) '
                                . '$(parent_id : (Potentials) potentialname) $(parent_id : (HelpDesk) ticket_title)'
                                . ' $(parent_id : (Campaigns) campaignname) <br/>'
        . 'Contacts List       : $(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname) <br/>'
        . 'Description         : $description';
$taskManager->saveTask($task);

global $current_user;
$adb = PearDatabase::getInstance();
$user = new Users();
$current_user = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());

$allTabIdResult = Migration_Index_View::ExecuteQuery('SELECT tabid, name FROM ottocrat_tab', array());
$noOfTabs = $adb->num_rows($allTabIdResult);
$allTabIds = array();
for($i=0; $i<$noOfTabs; ++$i) {
	$tabId = $adb->query_result($allTabIdResult, $i, 'tabid');
	$tabName = $adb->query_result($allTabIdResult, $i, 'name');
	$allTabIds[$tabName] = $tabId;
}

//Adding status field for project task

$moduleInstance = Ottocrat_Module::getInstance('ProjectTask');
$blockInstance = Ottocrat_Block::getInstance('LBL_PROJECT_TASK_INFORMATION', $moduleInstance);
$fieldInstance = new Ottocrat_Field();
$fieldInstance->name = 'projecttaskstatus';
$fieldInstance->label = 'Status';
$fieldInstance->uitype = 15;
$fieldInstance->quickcreate = 0;
$blockInstance->addField($fieldInstance);

$pickListValues = array('--None--', 'Open', 'In Progress', 'Completed', 'Deferred', 'Canceled ');

$fieldInstance->setPicklistValues($pickListValues);

//Dashboard schema changes
Ottocrat_Utils::CreateTable('ottocrat_module_dashboard_widgets', '(id INT(19) NOT NULL AUTO_INCREMENT, linkid INT(19), userid INT(19), filterid INT(19),				title VARCHAR(100), data VARCHAR(500) DEFAULT "[]", PRIMARY KEY(id))');
$potentials = Ottocrat_Module::getInstance('Potentials');
$potentials->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Potentials&view=ShowWidget&name=History','', '1');
$potentials->addLink('DASHBOARDWIDGET', 'Upcoming Activities', 'index.php?module=Potentials&view=ShowWidget&name=CalendarActivities','', '2');
$potentials->addLink('DASHBOARDWIDGET', 'Funnel', 'index.php?module=Potentials&view=ShowWidget&name=GroupedBySalesStage','', '3');
$potentials->addLink('DASHBOARDWIDGET', 'Potentials by Stage', 'index.php?module=Potentials&view=ShowWidget&name=GroupedBySalesPerson','', '4');
$potentials->addLink('DASHBOARDWIDGET', 'Pipelined Amount', 'index.php?module=Potentials&view=ShowWidget&name=PipelinedAmountPerSalesPerson','', '5');
$potentials->addLink('DASHBOARDWIDGET', 'Total Revenue', 'index.php?module=Potentials&view=ShowWidget&name=TotalRevenuePerSalesPerson','', '6');
$potentials->addLink('DASHBOARDWIDGET', 'Top Potentials', 'index.php?module=Potentials&view=ShowWidget&name=TopPotentials','', '7');
//$potentials->addLink('DASHBOARDWIDGET', 'Forecast', 'index.php?module=Potentials&view=ShowWidget&name=Forecast','', '8');
$potentials->addLink('DASHBOARDWIDGET', 'Overdue Activities', 'index.php?module=Potentials&view=ShowWidget&name=OverdueActivities','', '9');

$accounts = Ottocrat_Module::getInstance('Accounts');
$accounts->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Accounts&view=ShowWidget&name=History','', '1');
$accounts->addLink('DASHBOARDWIDGET', 'Upcoming Activities', 'index.php?module=Accounts&view=ShowWidget&name=CalendarActivities','', '2');
$accounts->addLink('DASHBOARDWIDGET', 'Overdue Activities', 'index.php?module=Accounts&view=ShowWidget&name=OverdueActivities','', '3');

$contacts = Ottocrat_Module::getInstance('Contacts');
$contacts->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Contacts&view=ShowWidget&name=History','', '1');
$contacts->addLink('DASHBOARDWIDGET', 'Upcoming Activities', 'index.php?module=Contacts&view=ShowWidget&name=CalendarActivities','', '2');
$contacts->addLink('DASHBOARDWIDGET', 'Overdue Activities', 'index.php?module=Contacts&view=ShowWidget&name=OverdueActivities','', '3');

$leads = Ottocrat_Module::getInstance('Leads');
$leads->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Leads&view=ShowWidget&name=History','', '1');
$leads->addLink('DASHBOARDWIDGET', 'Upcoming Activities', 'index.php?module=Leads&view=ShowWidget&name=CalendarActivities','', '2');
//$leads->addLink('DASHBOARDWIDGET', 'Leads Created', 'index.php?module=Leads&view=ShowWidget&name=LeadsCreated','', '3');
$leads->addLink('DASHBOARDWIDGET', 'Leads by Status', 'index.php?module=Leads&view=ShowWidget&name=LeadsByStatus','', '4');
$leads->addLink('DASHBOARDWIDGET', 'Leads by Source', 'index.php?module=Leads&view=ShowWidget&name=LeadsBySource','', '5');
$leads->addLink('DASHBOARDWIDGET', 'Leads by Industry', 'index.php?module=Leads&view=ShowWidget&name=LeadsByIndustry','', '6');
$leads->addLink('DASHBOARDWIDGET', 'Overdue Activities', 'index.php?module=Leads&view=ShowWidget&name=OverdueActivities','', '7');

$helpDesk = Ottocrat_Module::getInstance('HelpDesk');
$helpDesk->addLink('DASHBOARDWIDGET', 'Tickets by Status', 'index.php?module=HelpDesk&view=ShowWidget&name=TicketsByStatus','', '1');
$helpDesk->addLink('DASHBOARDWIDGET', 'Open Tickets', 'index.php?module=HelpDesk&view=ShowWidget&name=OpenTickets','', '2');

$home = Ottocrat_Module::getInstance('Home');
$home->addLink('DASHBOARDWIDGET', 'History', 'index.php?module=Home&view=ShowWidget&name=History','', '1');
$home->addLink('DASHBOARDWIDGET', 'Upcoming Activities', 'index.php?module=Home&view=ShowWidget&name=CalendarActivities','', '2');
$home->addLink('DASHBOARDWIDGET', 'Funnel', 'index.php?module=Potentials&view=ShowWidget&name=GroupedBySalesStage','', '3');
$home->addLink('DASHBOARDWIDGET', 'Potentials by Stage', 'index.php?module=Potentials&view=ShowWidget&name=GroupedBySalesPerson','', '4');
$home->addLink('DASHBOARDWIDGET', 'Pipelined Amount', 'index.php?module=Potentials&view=ShowWidget&name=PipelinedAmountPerSalesPerson','', '5');
$home->addLink('DASHBOARDWIDGET', 'Total Revenue', 'index.php?module=Potentials&view=ShowWidget&name=TotalRevenuePerSalesPerson','', '6');
$home->addLink('DASHBOARDWIDGET', 'Top Potentials', 'index.php?module=Potentials&view=ShowWidget&name=TopPotentials','', '7');
//$home->addLink('DASHBOARDWIDGET', 'Forecast', 'index.php?module=Potentials&view=ShowWidget&name=Forecast','', '8');

//$home->addLink('DASHBOARDWIDGET', 'Leads Created', 'index.php?module=Leads&view=ShowWidget&name=LeadsCreated','', '9');
$home->addLink('DASHBOARDWIDGET', 'Leads by Status', 'index.php?module=Leads&view=ShowWidget&name=LeadsByStatus','', '10');
$home->addLink('DASHBOARDWIDGET', 'Leads by Source', 'index.php?module=Leads&view=ShowWidget&name=LeadsBySource','', '11');
$home->addLink('DASHBOARDWIDGET', 'Leads by Industry', 'index.php?module=Leads&view=ShowWidget&name=LeadsByIndustry','', '12');
$home->addLink('DASHBOARDWIDGET', 'Overdue Activities', 'index.php?module=Home&view=ShowWidget&name=OverdueActivities','', '13');

$home->addLink('DASHBOARDWIDGET', 'Tickets by Status', 'index.php?module=HelpDesk&view=ShowWidget&name=TicketsByStatus','', '13');
$home->addLink('DASHBOARDWIDGET', 'Open Tickets', 'index.php?module=HelpDesk&view=ShowWidget&name=OpenTickets','', '14');

//Calendar and Events module clean up
$calendarTabId = getTabid('Calendar');
$eventTabId = getTabid('Events');
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_blocks SET blocklabel ="LBL_DESCRIPTION_INFORMATION" WHERE blockid=20',array());
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET displaytype=1 WHERE fieldname="location" AND tabid = ?', array($calendarTabId));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET displaytype=1 WHERE fieldname="visibility" AND tabid = ?', array($eventTabId));

$eventBlockId = getBlockId($eventTabId, 'LBL_EVENT_INFORMATION');
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET block = ? WHERE block = 41', array($eventBlockId));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_blocks SET blocklabel = "LBL_REMINDER_INFORMATION", show_title = 0 WHERE blockid = 40',array());
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_blocks SET blocklabel = "LBL_DESCRIPTION_INFORMATION", show_title = 0 WHERE blockid = 41',array());
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET block = 41 WHERE fieldname = "description" AND tabid = ?',array($eventTabId));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET block = ? WHERE fieldname = "contact_id" AND tabid = ?', array($eventBlockId, $eventTabId));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET displaytype = 3 WHERE fieldname = ? AND tabid = ?', array('notime', $eventTabId));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET displaytype = 3 WHERE fieldname = ? AND tabid = ?', array('duration_hours', $eventTabId));

$projectTabId = getTabid('Project');
$projectTaskTabId = getTabid('ProjectTask');
$projectMilestoneTabId = getTabid('ProjectMilestone');
$contactsTabId = getTabid('Contacts');
$accountsTabId = getTabid('Accounts');
$helpDeskTabId = getTabid('HelpDesk');

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_relatedlists SET actions=? WHERE tabid in(?,?) and related_tabid in (?,?,?)',
        array('add', $helpDeskTabId, $projectTabId, $calendarTabId, $projectTaskTabId,  $projectMilestoneTabId));

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_relatedlists SET actions=? WHERE tabid in(?, ?) and related_tabid in (?)',
        array('add', $contactsTabId, $accountsTabId, $projectTabId));

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET presence = 1 WHERE tabid = ? AND fieldname = ?', array($helpDeskTabId, 'comments'));
$faqTabId = getTabid('Faq');
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET presence = 1 WHERE tabid = ? AND fieldname = ?', array($faqTabId, 'comments'));

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_users SET truncate_trailing_zeros = ?', array(1));

//deleted the id column from the All filter
Migration_Index_View::ExecuteQuery("DELETE FROM ottocrat_cvcolumnlist WHERE cvid IN
			(SELECT cvid FROM ottocrat_customview WHERE viewname='All' AND entitytype NOT IN
				('Emails','Calendar','ModComments','ProjectMilestone','Project','SMSNotifier','PBXManager','Webmails'))
			AND columnindex = 0", array());

// Added indexes for Modtracker Module to improve performance
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_modtracker_basic ADD INDEX crmidx (crmid)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_modtracker_basic ADD INDEX idx (id)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_modtracker_detail ADD INDEX idx (id)', array());

// Ends

require_once 'modules/com_ottocrat_workflow/VTEntityMethodManager.inc';
$emm = new VTEntityMethodManager($adb);
$emm->addEntityMethod("ModComments","CustomerCommentFromPortal","modules/ModComments/ModCommentsHandler.php","CustomerCommentFromPortal");
$emm->addEntityMethod("ModComments","TicketOwnerComments","modules/ModComments/ModCommentsHandler.php","TicketOwnerComments");

require_once 'modules/com_ottocrat_workflow/VTWorkflowManager.inc';
require_once 'modules/com_ottocrat_workflow/VTTaskManager.inc';
$workflowManager = new VTWorkflowManager($adb);
$taskManager = new VTTaskManager($adb);

$commentsWorkflow = $workflowManager->newWorkFlow("ModComments");
$commentsWorkflow->test = '[{"fieldname":"related_to : (HelpDesk) ticket_title","operation":"is not empty","value":""}]';
$commentsWorkflow->description = "Workflow for comments on Tickets";
$commentsWorkflow->executionCondition = VTWorkflowManager::$ON_FIRST_SAVE;
$commentsWorkflow->defaultworkflow = 1;
$workflowManager->save($commentsWorkflow);

$task = $taskManager->createTask('VTEntityMethodTask', $commentsWorkflow->id);
$task->active = true;
$task->summary = 'Customer commented from Portal';
$task->methodName = "CustomerCommentFromPortal";
$taskManager->saveTask($task);

$task1 = $taskManager->createTask('VTEntityMethodTask', $commentsWorkflow->id);
$task1->active = true;
$task1->summary = 'Notify Customer when commenting on a Ticket';
$task1->methodName = "TicketOwnerComments";
$taskManager->saveTask($task1);
// Ends

Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_links MODIFY column linktype VARCHAR(50)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_links MODIFY column linklabel VARCHAR(50)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_links MODIFY column handler_class VARCHAR(50)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_links MODIFY column handler VARCHAR(50)', array());
//--
//Add ModComments to HelpDesk and Faq module
require_once 'modules/ModComments/ModComments.php';
ModComments::addWidgetTo(array("HelpDesk", "Faq"));
global $current_user, $OTTOCRAT_BULK_SAVE_MODE;
$OTTOCRAT_BULK_SAVE_MODE = true;

$stopLoop = false;
$pageCount = 0;
do {
	$ticketComments = Migration_Index_View::ExecuteQuery(sprintf('SELECT * FROM ottocrat_ticketcomments ORDER BY commentid ASC LIMIT %s,%s', $pageCount*1000, 1000),  array());
	$rows = $adb->num_rows($ticketComments);
	if (empty($rows)) {
		$stopLoop = true;
		break;
	}
	for($i=0; $i<$rows; $i++) {
		$modComments = CRMEntity::getInstance('ModComments');
		$modComments->column_fields['commentcontent'] = decode_html($adb->query_result($ticketComments, $i, 'comments'));
		$ownerId = $adb->query_result($ticketComments, $i, 'ownerid');
		$current_user->id = $ownerId;
		$modComments->column_fields['assigned_user_id'] = $modComments->column_fields['creator'] = $ownerId;
		$modComments->column_fields['createdtime'] = $adb->query_result($ticketComments, $i, 'createdtime');
		$modComments->column_fields['modifiedtime'] = $adb->query_result($ticketComments, $i, 'createdtime');
		$modComments->column_fields['related_to'] = $adb->query_result($ticketComments, $i, 'ticketid');
		$modComments->save('ModComments');
		Migration_Index_View::ExecuteQuery('UPDATE ottocrat_crmentity SET modifiedtime = ?, smcreatorid = ?, modifiedby = ? WHERE crmid = ?',
			array($modComments->column_fields['createdtime'], $ownerId, $ownerId, $modComments->id));
	}
	++$pageCount;
} while (!$stopLoop);

$stopLoop = false;
$pageCount = 0;
do {
	$faqComments = Migration_Index_View::ExecuteQuery(sprintf('SELECT * FROM ottocrat_faqcomments ORDER BY commentid ASC LIMIT %s, %s', $pageCount*1000, 1000), array());
	$rows = $adb->num_rows($faqComments);
	if (empty($rows)) {
		$stopLoop = true;
		break;
	}
	for($i=0; $i<$rows; $i++) {
		$modComments = CRMEntity::getInstance('ModComments');
		$modComments->column_fields['commentcontent'] = decode_html($adb->query_result($faqComments, $i, 'comments'));
		$modComments->column_fields['assigned_user_id'] = $modComments->column_fields['creator'] = Users::getActiveAdminId();
		$modComments->column_fields['createdtime'] = $adb->query_result($faqComments, $i, 'createdtime');
		$modComments->column_fields['modifiedtime'] = $adb->query_result($faqComments, $i, 'createdtime');
		$modComments->column_fields['related_to'] = $adb->query_result($faqComments, $i, 'faqid');
		$modComments->save('ModComments');
		Migration_Index_View::ExecuteQuery('UPDATE ottocrat_crmentity SET modifiedtime = ?, smcreatorid = ?, modifiedby = ? WHERE crmid = ?',
			array($modComments->column_fields['createdtime'], '6', '6', $modComments->id));
	}
	++$pageCount;
} while (!$stopLoop);

$OTTOCRAT_BULK_SAVE_MODE = false;

// Added label column in ottocrat_crmentity table for easier lookup - Also added Event handler to update the label on save of a record
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_crmentity ADD COLUMN label varchar(255)", array());

// To avoid infinite-loop if we not able fix label for non-entity/special modules.
$lastMaxCRMId = 0;
do {
	$rs = $adb->pquery("SELECT crmid,setype FROM ottocrat_crmentity INNER JOIN ottocrat_tab ON ottocrat_tab.name=ottocrat_crmentity.setype WHERE label IS NULL AND crmid > ? LIMIT 500", array($lastMaxCRMId));
	if (!$adb->num_rows($rs)) {
		break;
	}
	while ($row = $adb->fetch_array($rs)) {
		/**
		 * TODO: Optimize underlying API to cache re-usable data, for speedy data.
		 */
		$labelInfo = getEntityName($row['setype'], array(intval($row['crmid'])), true);

		if ($labelInfo) {
			$label = $labelInfo[$row['crmid']];
			Migration_Index_View::ExecuteQuery('UPDATE ottocrat_crmentity SET label=? WHERE crmid=? AND setype=?',
						array($label, $row['crmid'], $row['setype']));
		}

		if (intval($row['crmid']) > $lastMaxCRMId) {
			$lastMaxCRMId = intval($row['crmid']);
		}
	}
	$rs = null;
	unset($rs);
} while(true);

Migration_Index_View::ExecuteQuery('CREATE INDEX ottocrat_crmentity_labelidx ON ottocrat_crmentity(label)', array());

$homeModule = Ottocrat_Module::getInstance('Home');
Ottocrat_Event::register($homeModule, 'ottocrat.entity.aftersave', 'Ottocrat_RecordLabelUpdater_Handler', 'modules/Ottocrat/RecordLabelUpdater.php');

$moduleInstance = Ottocrat_Module::getInstance('ModComments');
$customer = Ottocrat_Field::getInstance('customer', $moduleInstance);
if (!$customer) {
	$customer = new Ottocrat_Field();
	$customer->name = 'customer';
	$customer->label = 'Customer';
	$customer->uitype = '10';
	$customer->displaytype = '3';
	$blockInstance = Ottocrat_Block::getInstance('LBL_MODCOMMENTS_INFORMATION', $moduleInstance);
	$blockInstance->addField($customer);
	$customer->setRelatedModules(array('Contacts'));
}

$moduleInstance = Ottocrat_Module::getInstance('Potentials');
$filter = Ottocrat_Filter::getInstance('All', $moduleInstance);
$fieldInstance = Ottocrat_Field::getInstance('amount', $moduleInstance);
$filter->addField($fieldInstance,6);


if(file_exists('modules/ModTracker/ModTrackerUtils.php')) {
	require_once 'modules/ModTracker/ModTrackerUtils.php';
	$modules = $adb->pquery('SELECT * FROM ottocrat_tab WHERE isentitytype = 1', array());
	$rows = $adb->num_rows($modules);
	for($i=0; $i<$rows; $i++) {
		$tabid=$adb->query_result($modules, $i, 'tabid');
		ModTrackerUtils::modTrac_changeModuleVisibility($tabid, 'module_enable');
	}
}

$operationId = vtws_addWebserviceOperation('retrieve_inventory', 'include/Webservices/LineItem/RetrieveInventory.php', 'vtws_retrieve_inventory', 'GET');
vtws_addWebserviceOperationParam($operationId, 'id', 'String', 1);

$moduleInstance = Ottocrat_Module::getInstance('Events');
$tabId = getTabid('Events');

// Update/Increment the sequence for the succeeding blocks of Events module, with starting sequence 3
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_blocks SET sequence = sequence+1 WHERE tabid=? AND sequence >= 3',
											array($tabId));

// Create Recurrence Information block
$recurrenceBlock = new Ottocrat_Block();
$recurrenceBlock->label = 'LBL_RECURRENCE_INFORMATION';
$recurrenceBlock->sequence = 3;
$moduleInstance->addBlock($recurrenceBlock);

$blockId = getBlockId($tabId, 'LBL_RECURRENCE_INFORMATION');
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET block=? WHERE fieldname=? and tabid=?', array($blockId, 'recurringtype', $tabId));

// Update/Increment the sequence for the succeeding blocks of Users module, with starting sequence 2
$moduleInstance = Ottocrat_Module::getInstance('Users');
$tabId = getTabid('Users');
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_blocks SET sequence = sequence+1 WHERE tabid=? AND sequence >= 2', array($tabId));

// Create Calendar Settings block
$calendarSettings = new Ottocrat_Block();
$calendarSettings->label = 'LBL_CALENDAR_SETTINGS';
$calendarSettings->sequence = 2;
$moduleInstance->addBlock($calendarSettings);

$calendarSettings = Ottocrat_Block::getInstance('LBL_CALENDAR_SETTINGS', $moduleInstance);

$dayOfTheWeek = new Ottocrat_Field();
$dayOfTheWeek->name = 'dayoftheweek';
$dayOfTheWeek->label = 'Starting Day of the week';
$dayOfTheWeek->table ='ottocrat_users';
$dayOfTheWeek->column = 'dayoftheweek';
$dayOfTheWeek->columntype = 'varchar(100)';
$dayOfTheWeek->typeofdata = 'V~O';
$dayOfTheWeek->uitype = 16;
$dayOfTheWeek->sequence = 2;
$dayOfTheWeek->defaultvalue = 'Sunday';
$calendarSettings->addField($dayOfTheWeek);
$dayOfTheWeek->setPicklistValues(array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'));

$defaultCallDuration = new Ottocrat_Field();
$defaultCallDuration->name = 'callduration';
$defaultCallDuration->label = 'Default Call Duration';
$defaultCallDuration->table ='ottocrat_users';
$defaultCallDuration->column = 'callduration';
$defaultCallDuration->columntype = 'varchar(100)';
$defaultCallDuration->typeofdata = 'V~O';
$defaultCallDuration->uitype = 16;
$defaultCallDuration->sequence = 3;
$defaultCallDuration->defaultvalue = 5;
$calendarSettings->addField($defaultCallDuration);
$defaultCallDuration->setPicklistValues(array('5','10','30','60','120'));

$otherEventDuration = new Ottocrat_Field();
$otherEventDuration->name = 'othereventduration';
$otherEventDuration->label = 'Other Event Duration';
$otherEventDuration->table ='ottocrat_users';
$otherEventDuration->column = 'othereventduration';
$otherEventDuration->columntype = 'varchar(100)';
$otherEventDuration->typeofdata = 'V~O';
$otherEventDuration->uitype = 16;
$otherEventDuration->sequence = 4;
$otherEventDuration->defaultvalue = 5;
$calendarSettings->addField($otherEventDuration);
$otherEventDuration->setPicklistValues(array('5','10','30','60','120'));

$blockId = getBlockId($tabId, 'LBL_CALENDAR_SETTINGS');
$sql = 'UPDATE ottocrat_field SET block = ? , displaytype = ? WHERE tabid = ? AND tablename = ? AND columnname in (?,?,?,?,?,?)';
Migration_Index_View::ExecuteQuery($sql, array($blockId, 1, $tabId, 'ottocrat_users', 'time_zone','activity_view','reminder_interval','date_format','start_hour', 'hour_format'));

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET uitype = ? WHERE tabid = ? AND tablename = ? AND columnname in (?,?)',
		array(16, $tabId, 'ottocrat_users', 'hour_format', 'start_hour'));

$fieldid = getFieldid($tabId, 'hour_format');
$hour_format = Ottocrat_Field::getInstance($fieldid, $moduleInstance);
$hour_format->setPicklistValues(array(12,24));

$fieldid = getFieldid($tabId, 'start_hour');
$start_hour = Ottocrat_Field::getInstance($fieldid, $moduleInstance);
$start_hour->setPicklistValues(array('00:00','01:00','02:00','03:00','04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00'
								,'12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00'));

//update hour_format value in existing customers
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_users SET hour_format = ? WHERE hour_format = ? OR hour_format = ?', array(12, 'am/pm', ''));

//add user default values
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_users SET dayoftheweek = ?, callduration = ?, othereventduration = ?, start_hour = ? ', array('Sunday', 5, 5, '00:00'));

$moduleInstance = Ottocrat_Module::getInstance('Events');
$tabId = getTabid('Events');

// Update/Increment the sequence for the succeeding blocks of Events module, with starting sequence 4
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_blocks SET sequence = sequence+1 WHERE tabid=? AND sequence >= 4', array($tabId));

// Create Recurrence Information block
$recurrenceBlock = new Ottocrat_Block();
$recurrenceBlock->label = 'LBL_RELATED_TO';
$recurrenceBlock->sequence = 4;
$moduleInstance->addBlock($recurrenceBlock);

$blockId = getBlockId($tabId, 'LBL_RELATED_TO');

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET block=? WHERE fieldname IN (?,?) and tabid=?', array($blockId, 'contact_id','parent_id', $tabId));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET displaytype=1 WHERE fieldname=? and tabid=?',array('recurringtype',$tabId));

// END 2012.12.02

// //////////////////////////////////////////////
$inventoryModules = array(
    'Invoice' => array('LBL_INVOICE_INFORMATION', 'ottocrat_invoice', 'invoiceid'),
    'SalesOrder' => array('LBL_SO_INFORMATION', 'ottocrat_salesorder', 'salesorderid'),
    'PurchaseOrder' => array('LBL_PO_INFORMATION', 'ottocrat_purchaseorder', 'purchaseorderid'),
    'Quotes' => array('LBL_QUOTE_INFORMATION', 'ottocrat_quotes', 'quoteid')
);

foreach ($inventoryModules as $module => $details) {
    $tableName = $details[1];
    $moduleInstance = Ottocrat_Module::getInstance($module);
    $block = Ottocrat_Block::getInstance($details[0], $moduleInstance);

    $preTaxTotalField = new Ottocrat_Field();
    $preTaxTotalField->name = 'pre_tax_total';
    $preTaxTotalField->label = 'Pre Tax Total';
    $preTaxTotalField->table = $tableName;
    $preTaxTotalField->column = 'pre_tax_total';
    $preTaxTotalField->columntype = 'decimal(25,8)';
    $preTaxTotalField->typeofdata = 'N~O';
    $preTaxTotalField->uitype = '72';
    $preTaxTotalField->masseditable = '1';
    $preTaxTotalField->displaytype = '3';
    $block->addField($preTaxTotalField);

    $tableId = $details[2];

    $result = $adb->pquery("SELECT $tableId, subtotal, s_h_amount, discount_percent, discount_amount FROM $tableName", array());
    $numOfRows = $adb->num_rows($result);

    for ($i = 0; $i < $numOfRows; $i++) {
        $id = $adb->query_result($result, $i, $tableId);
        $subTotal = (float) $adb->query_result($result, $i, "subtotal");
        $shAmount = (float) $adb->query_result($result, $i, "s_h_amount");
        $discountAmount = (float) $adb->query_result($result, $i, "discount_amount");
        $discountPercent = (float) $adb->query_result($result, $i, "discount_percent");

        if ($discountPercent != '0') {
            $discountAmount = ($subTotal * $discountPercent) / 100;
        }
        $preTaxTotalValue = $subTotal + $shAmount - $discountAmount;

        Migration_Index_View::ExecuteQuery("UPDATE $tableName set pre_tax_total = ? WHERE $tableId = ?", array($preTaxTotalValue, $id));
    }
}

$moduleInstance = Ottocrat_Module::getInstance('Users');

$calendarSettings = Ottocrat_Block::getInstance('LBL_CALENDAR_SETTINGS', $moduleInstance);
$calendarsharedtype = new Ottocrat_Field();
$calendarsharedtype->name = 'calendarsharedtype';
$calendarsharedtype->label = 'Calendar Shared Type';
$calendarsharedtype->table ='ottocrat_users';
$calendarsharedtype->column = 'calendarsharedtype';
$calendarsharedtype->columntype = 'varchar(100)';
$calendarsharedtype->typeofdata = 'V~O';
$calendarsharedtype->uitype = 16;
$calendarsharedtype->sequence = 2;
$calendarsharedtype->displaytype = 3;
$calendarsharedtype->defaultvalue = 'Public';
$calendarSettings->addField($calendarsharedtype);
$calendarsharedtype->setPicklistValues(array('public','private','seletedusers'));

$allUsers = get_user_array(false);
foreach ($allUsers as $id => $name) {
    $query = 'select sharedid from ottocrat_sharedcalendar where userid=?';
    $result = $adb->pquery($query, array($id));
	$count = $adb->num_rows($result);
    if($count > 0){
		Migration_Index_View::ExecuteQuery('UPDATE ottocrat_users SET calendarsharedtype = ? WHERE id = ?', array('selectedusers', $id));
    }else{
		Migration_Index_View::ExecuteQuery('UPDATE ottocrat_users SET calendarsharedtype = ? WHERE id = ? ', array('public', $id));
        foreach ($allUsers as $sharedid => $name) {
            if($sharedid != $id){
                $sql = "INSERT INTO ottocrat_sharedcalendar VALUES (?,?)";
                Migration_Index_View::ExecuteQuery($sql, array($id, $sharedid));
            }
        }
    }
}

// Add Key Metrics widget.
$homeModule = Ottocrat_Module::getInstance('Home');
$homeModule->addLink('DASHBOARDWIDGET', 'Key Metrics', 'index.php?module=Home&view=ShowWidget&name=KeyMetrics');

$moduleArray = array('Accounts' => 'LBL_ACCOUNT_INFORMATION', 'Contacts' => 'LBL_CONTACT_INFORMATION', 'Potentials' => 'LBL_OPPORTUNITY_INFORMATION');
foreach ($moduleArray as $module => $block) {
    $moduleInstance = Ottocrat_Module::getInstance($module);
    $blockInstance = Ottocrat_Block::getInstance($block, $moduleInstance);

    $field = new Ottocrat_Field();
    $field->name = 'isconvertedfromlead';
    $field->label = 'Is Converted From Lead';
    $field->uitype = 56;
    $field->column = 'isconvertedfromlead';
    $field->displaytype = 2;
    $field->defaultvalue = 'no';
    $field->columntype = 'varchar(3)';
    $field->typeofdata = 'C~O';
    $blockInstance->addField($field);
}

$homeModule = Ottocrat_Module::getInstance('Home');
$homeModule->addLink('DASHBOARDWIDGET', 'Mini List', 'index.php?module=Home&view=ShowWidget&name=MiniList');

$moduleInstance = Ottocrat_Module::getInstance('Users');
$moreInfoBlock = Ottocrat_Block::getInstance('LBL_MORE_INFORMATION', $moduleInstance);

$viewField = new Ottocrat_Field();
$viewField->name = 'default_record_view';
$viewField->label = 'Default Record View';
$viewField->table ='ottocrat_users';
$viewField->column = 'default_record_view';
$viewField->columntype = 'VARCHAR(10)';
$viewField->typeofdata = 'V~O';
$viewField->uitype = 16;
$viewField->defaultvalue = 'Summary';

$moreInfoBlock->addField($viewField);
$viewField->setPicklistValues(array('Summary', 'Detail'));

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_users SET default_record_view = ?', array('Summary'));

$InvoiceInstance = Ottocrat_Module::getInstance('Invoice');
Ottocrat_Event::register($InvoiceInstance, 'ottocrat.entity.aftersave', 'InvoiceHandler', 'modules/Invoice/InvoiceHandler.php');

$POInstance = Ottocrat_Module::getInstance('PurchaseOrder');
Ottocrat_Event::register($POInstance, 'ottocrat.entity.aftersave', 'PurchaseOrderHandler', 'modules/PurchaseOrder/PurchaseOrderHandler.php');

$InvoiceBlockInstance = Ottocrat_Block::getInstance('LBL_INVOICE_INFORMATION', $InvoiceInstance);
$field1 = Ottocrat_Field::getInstance('received', $InvoiceInstance);
if (!$field1) {
    $field1 = new Ottocrat_Field();
    $field1->name = 'received';
    $field1->label = 'Received';
    $field1->table = 'ottocrat_invoice';
    $field1->uitype = 72;
    $field1->displaytype = 3;
    $field1->typeofdata = 'N~O';
    $field1->defaultvalue = 0;
    $InvoiceBlockInstance->addField($field1);
}
$field2 = Ottocrat_Field::getInstance('balance', $InvoiceInstance);
if (!$field2) {
    $field2 = new Ottocrat_Field();
    $field2->name = 'balance';
    $field2->label = 'Balance';
    $field1->table = 'ottocrat_invoice';
    $field2->uitype = 72;
    $field2->typeofdata = 'N~O';
    $field2->defaultvalue = 0;
    $field2->displaytype = 3;
    $InvoiceBlockInstance->addField($field2);
}

$POBlockInstance = Ottocrat_Block::getInstance('LBL_PO_INFORMATION', $POInstance);
$field3 = Ottocrat_Field::getInstance('paid', $POInstance);
if (!$field3) {
    $field3 = new Ottocrat_Field();
    $field3->name = 'paid';
    $field3->label = 'Paid';
    $field3->table = 'ottocrat_purchaseorder';
    $field3->uitype = 72;
    $field3->displaytype = 3;
    $field3->typeofdata = 'N~O';
    $field3->defaultvalue = 0;
    $POBlockInstance->addField($field3);
}
$field4 = Ottocrat_Field::getInstance('balance', $POInstance);
if (!$field4) {
    $field4 = new Ottocrat_Field();
    $field4->name = 'balance';
    $field4->label = 'Balance';
    $field4->table = 'ottocrat_purchaseorder';
    $field4->uitype = 72;
    $field4->typeofdata = 'N~O';
    $field4->defaultvalue = 0;
    $field4->displaytype = 3;
    $POBlockInstance->addField($field4);
}


$sqltimelogTable = "CREATE TABLE ottocrat_sqltimelog ( id integer, type VARCHAR(10),
					data text, started decimal(18,2), ended decimal(18,2), loggedon datetime)";

Migration_Index_View::ExecuteQuery($sqltimelogTable, array());


$moduleName = 'PurchaseOrder';
$emm = new VTEntityMethodManager($adb);
$emm->addEntityMethod($moduleName,"UpdateInventory","include/InventoryHandler.php","handleInventoryProductRel");

$vtWorkFlow = new VTWorkflowManager($adb);
$poWorkFlow = $vtWorkFlow->newWorkFlow($moduleName);
$poWorkFlow->description = "Update Inventory Products On Every Save";
$poWorkFlow->defaultworkflow = 1;
$poWorkFlow->executionCondition = 3;
$vtWorkFlow->save($poWorkFlow);

$tm = new VTTaskManager($adb);
$task = $tm->createTask('VTEntityMethodTask', $poWorkFlow->id);
$task->active = true;
$task->summary = "Update Inventory Products";
$task->methodName = "UpdateInventory";
$tm->saveTask($task);

// Add Tag Cloud widget.
$homeModule = Ottocrat_Module::getInstance('Home');
$homeModule->addLink('DASHBOARDWIDGET', 'Tag Cloud', 'index.php?module=Home&view=ShowWidget&name=TagCloud');

// Schema changed for capturing Dashboard widget positions
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_module_dashboard_widgets ADD COLUMN position VARCHAR(50)',array());

$moduleInstance = Ottocrat_Module::getInstance('Contacts');
if($moduleInstance) {
	$moduleInstance->addLink('LISTVIEWSIDEBARWIDGET','Google Contacts',
		'module=Google&view=List&sourcemodule=Contacts', '','', '');
}

$moduleInstance = Ottocrat_Module::getInstance('Calendar');
if($moduleInstance) {
	$moduleInstance->addLink('LISTVIEWSIDEBARWIDGET','Google Calendar',
		'module=Google&view=List&sourcemodule=Calendar', '','', '');
}

Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_cvadvfilter MODIFY comparator VARCHAR(20)', array());
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_cvadvfilter SET comparator = ? WHERE comparator = ?', array('next120days', 'next120day'));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_cvadvfilter SET comparator = ? WHERE comparator = ?', array('last120days', 'last120day'));

Migration_Index_View::ExecuteQuery("UPDATE ottocrat_relatedlists SET actions = ? WHERE tabid = ? AND related_tabid IN (?, ?)",
	array('ADD', getTabid('Project'), getTabid('ProjectTask'), getTabid('ProjectMilestone')));

Migration_Index_View::ExecuteQuery("UPDATE ottocrat_field SET typeofdata = ? WHERE columnname = ? AND tablename = ?", array("V~O", "company", "ottocrat_leaddetails"));

if(Ottocrat_Utils::CheckTable('ottocrat_cron_task')) {
	Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_cron_task MODIFY COLUMN laststart INT(11) UNSIGNED',Array());
	Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_cron_task MODIFY COLUMN lastend INT(11) UNSIGNED',Array());
}

if(Ottocrat_Utils::CheckTable('ottocrat_cron_log')) {
	Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_cron_log MODIFY COLUMN start INT(11) UNSIGNED',Array());
   	Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_cron_log MODIFY COLUMN end INT(11) UNSIGNED',Array());
}

require_once 'vtlib/Ottocrat/Cron.php';
Ottocrat_Cron::deregister('ScheduleReports');
// END 2013.02.18

// Start 2013.03.19
// Mail Converter schema changes
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_mailscanner ADD COLUMN timezone VARCHAR(10) default NULL', array());
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_mailscanner SET timezone=? WHERE server LIKE ? AND timezone IS NULL', array('-8:00', '%.gmail.com'));

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_report SET state=?', array('CUSTOM'));

Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_relcriteria MODIFY value VARCHAR(512)", array());
Migration_Index_View::ExecuteQuery("ALTER TABLE ottocrat_cvadvfilter MODIFY value VARCHAR(512)", array());
// End 2013.03.19

// Start 2013.04.23
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_sqltimelog MODIFY started DECIMAL(20,6)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_sqltimelog MODIFY ended DECIMAL(20,6)', array());

//added Assests tab in contact
$assetsModuleInstance = Ottocrat_Module::getInstance('Assets');
$contactModule = Ottocrat_Module::getInstance('Contacts');
$contactModule->setRelatedList($assetsModuleInstance, '', false, 'get_dependents_list');
// End 2013.04.23

// Start 2013.04.30
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_users MODIFY signature TEXT', array());
//Adding column to store the state of short cut settings fields
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_settings_field ADD COLUMN pinned int(1) DEFAULT 0',array());

$defaultPinnedFields = array('LBL_USERS','LBL_LIST_WORKFLOWS','VTLIB_LBL_MODULE_MANAGER','LBL_PICKLIST_EDITOR');
$defaultPinnedSettingFieldQuery = 'UPDATE ottocrat_settings_field SET pinned=1 WHERE name IN ('.generateQuestionMarks($defaultPinnedFields).')';
Migration_Index_View::ExecuteQuery($defaultPinnedSettingFieldQuery,$defaultPinnedFields);

Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_profile ADD COLUMN directly_related_to_role int(1) DEFAULT 0',array());

$blockId = getSettingsBlockId('LBL_STUDIO');
$result = $adb->pquery('SELECT max(sequence) as maxSequence FROM ottocrat_settings_field WHERE blockid=?', array($blockId));
$sequence = 0;
if($adb->num_rows($result) > 0 ) {
	$sequence = $adb->query_result($result,0,'maxSequence');
}

$fieldId = $adb->getUniqueID('ottocrat_settings_field');
$query = "INSERT INTO ottocrat_settings_field (fieldid, blockid, name, iconpath, description, " .
		"linkto, sequence) VALUES (?,?,?,?,?,?,?)";
$layoutEditoLink = 'index.php?module=LayoutEditor&parent=Settings&view=Index';
$params = array($fieldId, $blockId, 'LBL_EDIT_FIELDS', '', 'LBL_LAYOUT_EDITOR_DESCRIPTION', $layoutEditoLink, $sequence);
Migration_Index_View::ExecuteQuery($query, $params);

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_role SET rolename = ? WHERE rolename = ? AND depth = ?', array('Organization', 'Organisation', 0));


//Create a new table to support custom fields in Documents module
$adb->query("CREATE TABLE IF NOT EXISTS ottocrat_notescf (notesid INT(19), FOREIGN KEY fk_1_ottocrat_notescf(notesid) REFERENCES ottocrat_notes(notesid) ON DELETE CASCADE);");

if(!defined('INSTALLATION_MODE')) {
	Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_salutationtype ADD COLUMN sortorderid INT(1)', array());
}

$summaryFields = array(
	'Accounts'	=> array('assigned_user_id', 'email1', 'phone', 'bill_city', 'bill_country', 'website'),
	'Contacts'	=> array('assigned_user_id', 'email', 'phone', 'mailingcity', 'mailingcountry'),
	'Leads'		=> array('assigned_user_id', 'email', 'phone', 'city', 'country', 'leadsource'),
	'HelpDesk'	=> array('assigned_user_id', 'ticketstatus', 'parent_id', 'ticketseverities', 'description'),
	'Potentials'=> array('assigned_user_id', 'amount', 'sales_stage', 'closingdate'),
	'Project'	=> array('assigned_user_id', 'targetenddate'));

foreach ($summaryFields as $moduleName => $fieldsList) {
	$updateQuery = 'UPDATE ottocrat_field SET summaryfield = 1
						WHERE fieldname IN ('.generateQuestionMarks($fieldsList) .') AND tabid = '. getTabid($moduleName);
	Migration_Index_View::ExecuteQuery($updateQuery, $fieldsList);
}

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET defaultvalue=? WHERE tablename=? AND fieldname= ?', array('Active', 'ottocrat_users', 'status'));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET defaultvalue=? WHERE tablename=? AND fieldname= ?', array('12', 'ottocrat_users', 'hour_format'));

// Adding users field into all the available profiles, this is used in email templates
// when non-admin sends an email with users field in the template
$module = 'Users';
$user = new $module();
$activeAdmin = Users::getActiveAdminId();
$user->retrieve_entity_info($activeAdmin, $module);
$handler = vtws_getModuleHandlerFromName($module, $user);
$meta = $handler->getMeta();
$moduleFields = $meta->getModuleFields();

$userAccessbleFields = array();
$skipFields = array(98,115,116,31,32);
foreach ($moduleFields as $fieldName => $webserviceField) {
	if($webserviceField->getFieldDataType() == 'string' || $webserviceField->getFieldDataType() == 'email' || $webserviceField->getFieldDataType() == 'phone') {
		if(!in_array($webserviceField->getUitype(), $skipFields) && $fieldName != 'asterisk_extension'){
			$userAccessbleFields[$webserviceField->getFieldId()] .= $fieldName;
		}
	}
}

$tabId = getTabid($module);
$query = 'SELECT profileid FROM ottocrat_profile';
$result = $adb->pquery($query, array());

for($i=0; $i<$adb->num_rows($result); $i++) {
	$profileId = $adb->query_result($result, $i, 'profileid');
	$sql = 'SELECT fieldid FROM ottocrat_profile2field WHERE profileid = ? AND tabid = ?';
	$fieldsResult = $adb->pquery($sql, array($profileId, $tabId));
	$profile2Fields = array();
	$rows = $adb->num_rows($fieldsResult);
	for($j=0; $j<$rows; $j++) {
		array_push($profile2Fields, $adb->query_result($fieldsResult, $j, 'fieldid'));
	}
	foreach ($userAccessbleFields as $fieldId => $fieldName) {
		if(!in_array($fieldId, $profile2Fields)){
			$insertQuery = 'INSERT INTO ottocrat_profile2field(profileid,tabid,fieldid,visible,readonly) VALUES(?,?,?,?,?)';
			Migration_Index_View::ExecuteQuery($insertQuery, array($profileId,$tabId,$fieldId,0,0));
		}
	}
}

//Added user field in ottocrat_def_org_field table
$sql = 'SELECT fieldid FROM ottocrat_def_org_field WHERE tabid = ?';
$result1 = $adb->pquery($sql, array($tabId));
$def_org_fields = array();
$defRows = $adb->num_rows($result1);
for($j=0; $j<$defRows; $j++) {
	array_push($def_org_fields, $adb->query_result($result1, $j, 'fieldid'));
}
foreach ($userAccessbleFields as $fieldId => $fieldName) {
	if(!in_array($fieldId, $def_org_fields)){
		$insertQuery = 'INSERT INTO ottocrat_def_org_field(tabid,fieldid,visible,readonly) VALUES(?,?,?,?)';
		Migration_Index_View::ExecuteQuery($insertQuery, array($tabId,$fieldId,0,0));
	}
}

//need to recreate user_privileges files as lot of user fields are added in this script and user_priviliges files are not updated
require_once('modules/Users/CreateUserPrivilegeFile.php');
createUserPrivilegesfile('1');

//Remove '--None--'/'None' from all the picklist values.
$sql = 'SELECT fieldname FROM ottocrat_field WHERE uitype IN(?,?,?,?)';
$result = $adb->pquery($sql, array(15,16,33,55));
$num_rows = $adb->num_rows($result);
for($i=0; $i<$num_rows; $i++){
	$fieldName = $adb->query_result($result, $i, 'fieldname');
	$checkTable = $adb->pquery('SHOW TABLES LIKE "ottocrat_'.$fieldName.'"', array());
	if($adb->num_rows($checkTable) > 0) {
		$query = "DELETE FROM ottocrat_$fieldName WHERE $fieldName = ? OR $fieldName = ?";
		Migration_Index_View::ExecuteQuery($query, array('--None--', 'None'));
	}
}

$potentials = Ottocrat_Module::getInstance('Potentials');
$potentials->addLink('DASHBOARDWIDGET', 'Funnel Amount', 'index.php?module=Potentials&view=ShowWidget&name=FunnelAmount','', '10');
$home = Ottocrat_Module::getInstance('Home');
$home->addLink('DASHBOARDWIDGET', 'Funnel Amount', 'index.php?module=Potentials&view=ShowWidget&name=FunnelAmount','', '10');

// Enable Sharing-Access for Vendors
$vendorInstance = Ottocrat_Module::getInstance('Vendors');
$vendorAssignedToField = Ottocrat_Field::getInstance('assigned_user_id', $vendorInstance);
if (!$vendorAssignedToField) {
	$vendorBlock = Ottocrat_Block::getInstance('LBL_VENDOR_INFORMATION', $vendorInstance);

	$vendorAssignedToField = new Ottocrat_Field();
	$vendorAssignedToField->name = 'assigned_user_id';
	$vendorAssignedToField->label = 'Assigned To';
	$vendorAssignedToField->table = 'ottocrat_crmentity';
	$vendorAssignedToField->column = 'smownerid';
	$vendorAssignedToField->uitype = 53;
	$vendorAssignedToField->typeofdata = 'V~M';
	$vendorBlock->addField($vendorAssignedToField);

	$vendorAllFilter = Ottocrat_Filter::getInstance('All', $vendorInstance);
	$vendorAllFilter->addField($vendorAssignedToField, 5);
}

// Allow Sharing access and role-based security for Vendors
Ottocrat_Access::deleteSharing($vendorInstance);
Ottocrat_Access::initSharing($vendorInstance);
Ottocrat_Access::allowSharing($vendorInstance);
Ottocrat_Access::setDefaultSharing($vendorInstance);

Ottocrat_Module::syncfile();

// Add Email Opt-out for Leads
$leadsInstance = Ottocrat_Module::getInstance('Leads');
$leadsOptOutField= Ottocrat_Field::getInstance('emailoptout', $leadsInstance);

if (!$leadsOptOutField) {
	$leadsOptOutField = new Ottocrat_Field();
	$leadsOptOutField->name = 'emailoptout';
	$leadsOptOutField->label = 'Email Opt Out';
	$leadsOptOutField->table = 'ottocrat_leaddetails';
	$leadsOptOutField->column = $leadsOptOutField->name;
	$leadsOptOutField->columntype = 'VARCHAR(3)';
	$leadsOptOutField->uitype = 56;
	$leadsOptOutField->typeofdata = 'C~O';

	$leadsInformationBlock = Ottocrat_Block::getInstance('LBL_LEAD_INFORMATION', $leadsInstance);
	$leadsInformationBlock->addField($leadsOptOutField);

	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_leaddetails SET emailoptout=0 WHERE emailoptout IS NULL', array());
}

$module = Ottocrat_Module::getInstance('Home');
$module->addLink('DASHBOARDWIDGET', 'Notebook', 'index.php?module=Home&view=ShowWidget&name=Notebook');

Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_module_dashboard_widgets MODIFY data TEXT',array());

$linkIdResult = $adb->pquery('SELECT linkid FROM ottocrat_links WHERE ottocrat_links.linklabel="Notebook"', array());
$noteBookLinkId = $adb->query_result($linkIdResult, 0, 'linkid');

$result = $adb->pquery('SELECT ottocrat_homestuff.stufftitle, ottocrat_homestuff.userid, ottocrat_notebook_contents.contents FROM
						ottocrat_homestuff INNER JOIN ottocrat_notebook_contents on ottocrat_notebook_contents.notebookid = ottocrat_homestuff.stuffid
						WHERE ottocrat_homestuff.stufftype = ?', array('Notebook'));

for($i=0; $i<$adb->num_rows($result); $i++) {
	$noteBookTitle = $adb->query_result($result, $i, 'stufftitle');
	$userId = $adb->query_result($result, $i, 'userid');
	$noteBookContent = $adb->query_result($result, $i, 'contents');
	$query = 'INSERT INTO ottocrat_module_dashboard_widgets(linkid, userid, filterid, title, data) VALUES(?,?,?,?,?)';
	$params= array($noteBookLinkId,$userId,0,$noteBookTitle,$noteBookContent);
	Migration_Index_View::ExecuteQuery($query, $params);
}

$moduleInstance = Ottocrat_Module::getInstance('ModComments');
$modCommentsUserId = Ottocrat_Field::getInstance("userid", $moduleInstance);
$modCommentsReasonToEdit = Ottocrat_Field::getInstance("reasontoedit", $moduleInstance);

if(!$modCommentsUserId){
	$blockInstance = Ottocrat_Block::getInstance('LBL_MODCOMMENTS_INFORMATION', $moduleInstance);
	$userId = new Ottocrat_Field();
	$userId->name = 'userid';
	$userId->label = 'UserId';
	$userId->uitype = '10';
	$userId->displaytype = '3';
	$blockInstance->addField($userId);
}
if(!$modCommentsReasonToEdit){
	$blockInstance = Ottocrat_Block::getInstance('LBL_MODCOMMENTS_INFORMATION', $moduleInstance);
	$reasonToEdit = new Ottocrat_Field();
	$reasonToEdit->name = 'reasontoedit';
	$reasonToEdit->label = 'ReasonToEdit';
	$reasonToEdit->uitype = '19';
	$reasonToEdit->displaytype = '1';
	$blockInstance->addField($reasonToEdit);
}

Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_invoice MODIFY balance decimal(25,8)',array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_invoice MODIFY received decimal(25,8)',array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_purchaseorder MODIFY balance decimal(25,8)',array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_purchaseorder MODIFY paid decimal(25,8)',array());

$labels = array('LBL_ADD_NOTE', 'Add Note');
$sql = 'UPDATE ottocrat_links SET handler = ?, handler_class = ?, handler_path = ? WHERE linklabel IN (?, ?)';
Migration_Index_View::ExecuteQuery($sql, array('isLinkPermitted', 'Documents', 'modules/Documents/Documents.php', $labels));

$sql = 'UPDATE ottocrat_links SET handler = ?, handler_class = ?, handler_path = ? WHERE linklabel = ?';
Migration_Index_View::ExecuteQuery($sql, array('isLinkPermitted', 'ProjectTask', 'modules/ProjectTask/ProjectTask.php', 'Add Project Task'));

Migration_Index_View::ExecuteQuery('DELETE FROM ottocrat_settings_field WHERE name=?', array('EMAILTEMPLATES'));

$tabIdList = array();
$tabIdList[] = getTabid('Invoice');
$tabIdList[] = getTabid('PurchaseOrder');

$query = 'SELECT fieldid FROM ottocrat_field WHERE (fieldname=? or fieldname=? or fieldname=? ) AND tabid IN ('.generateQuestionMarks($tabIdList).')';
$result = $adb->pquery($query, array('received', 'paid', 'balance',$tabIdList));
$numrows = $adb->num_rows($result);

for ($i = 0; $i < $numrows; $i++) {
	$fieldid = $adb->query_result($result, $i, 'fieldid');
	$query = 'Update ottocrat_profile2field set readonly = 0 where fieldid=?';
	Migration_Index_View::ExecuteQuery($query, array($fieldid));
}

$actions = array('Import','Export');
$moduleInstance = Ottocrat_Module::getInstance('Calendar');
foreach ($actions as $actionName) {
	Ottocrat_Access::updateTool($moduleInstance, $actionName, true, '');
}

//Update leads salutation value of none to empty value
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_leaddetails SET salutation='' WHERE salutation = ?", array('--None--'));

//Update contacts salutation value of none to empty value
Migration_Index_View::ExecuteQuery("UPDATE ottocrat_contactdetails SET salutation='' WHERE salutation = ?", array('--None--'));
// END 2013-06-25

// Start 2013-09-24
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_eventhandlers SET handler_path = ? WHERE handler_class = ?',
				array('modules/Ottocrat/handlers/RecordLabelUpdater.php', 'Ottocrat_RecordLabelUpdater_Handler'));

$inventoryModules = array('Invoice','Quotes','PurchaseOrder','SalesOrder');
foreach ($inventoryModules as $key => $moduleName) {
	$moduleInstance = Ottocrat_Module::getInstance($moduleName);
	$focus = CRMEntity::getInstance($moduleName);
	$blockInstance = Ottocrat_Block::getInstance('LBL_ITEM_DETAILS',$moduleInstance);

	$field = new Ottocrat_Field();
	$field->name = 'hdnS_H_Percent';
	$field->label = 'S&H Percent';
	$field->column = 's_h_percent';
	$field->table = $focus->table_name;
	$field->uitype = 1;
	$field->typeofdata = 'N~O';
	$field->readonly = '0';
	$field->displaytype = '5';
	$field->masseditable = '0';
	$field->quickcreate = '0';
	$field->columntype = 'INT(11)';
	$blockInstance->addField($field);
}

Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_invoice_recurring_info ADD PRIMARY KEY (salesorderid)',array());

$result = $adb->pquery('SELECT task_id FROM com_ottocrat_workflowtasks WHERE workflow_id IN
						(SELECT workflow_id FROM com_ottocrat_workflows WHERE module_name IN (?, ?))
						AND summary = ?', array('Calendar', 'Events', 'Send Notification Email to Record Owner'));
$numOfRows = $adb->num_rows($result);
require_once 'modules/com_ottocrat_workflow/tasks/VTSendNotificationTask.inc';
for($i=0; $i<$numOfRows; $i++) {
	$tm = new VTTaskManager($adb);
	$task = $tm->retrieveTask($adb->query_result($result, $i, 'task_id'));

	$sendNotificationTask = new VTSendNotificationTask();
	$properties = get_object_vars($task);
	foreach ($properties as $propertyName => $propertyValue) {
		$sendNotificationTask->$propertyName = str_replace('$(general : (__OttocratMeta__) dbtimezone)', '$(general : (__OttocratMeta__) usertimezone)', $propertyValue);
	}
	$tm->saveTask($sendNotificationTask);
}

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET masseditable = ? where fieldname = ? and tabid = ?',
			array('1', 'accountname', getTabid('Accounts')));

$result = $adb->pquery('SELECT taxname FROM ottocrat_shippingtaxinfo', array());
$numOfRows = $adb->num_rows($result);
$shippingTaxes = array();
$tabIds = array();
for ($i = 0; $i < $numOfRows; $i++) {
	$shippingTaxName = $adb->query_result($result, $i, 'taxname');
	array_push($shippingTaxes, $shippingTaxName);
}

$modules = array('Invoice','Quotes','PurchaseOrder','SalesOrder');
$tabIdQuery = 'SELECT tabid FROM ottocrat_tab where name IN ('.generateQuestionMarks($modules).')';
$tabIdRes = $adb->pquery($tabIdQuery,$modules);
$num_rows = $adb->num_rows($tabIdRes);
for ($i = 0; $i < $num_rows; $i++) {
	$tabIds[] = $adb->query_result($tabIdRes,0,'tabid');
}

$query = 'DELETE FROM ottocrat_field WHERE tabid IN (' . generateQuestionMarks($tabIds) . ') AND fieldname IN (' . generateQuestionMarks($shippingTaxes) . ')';
Migration_Index_View::ExecuteQuery($query, array_merge($tabIds, $shippingTaxes));

$entityModules = Ottocrat_Module_Model::getEntityModules();

foreach($entityModules as $moduleModel) {
	$crmInstance = CRMEntity::getInstance($moduleModel->getName());
	$tabId = $moduleModel->getId();
	$defaultRelatedFields = $crmInstance->list_fields_name;
	$updateQuery = 'UPDATE ottocrat_field SET summaryfield=1  where tabid=? and fieldname IN ('.generateQuestionMarks($defaultRelatedFields).')';
	Migration_Index_View::ExecuteQuery($updateQuery,  array_merge(array($tabId), array_values($defaultRelatedFields)));
}

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_currencies SET currency_name = ? where currency_name = ? and currency_code = ?',
		array('Hong Kong, Dollars', 'LvHong Kong, Dollars', 'HKD'));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_currency_info SET currency_name = ? where currency_name = ?',
		array('Hong Kong, Dollars', 'LvHong Kong, Dollars'));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET defaultvalue=1 WHERE fieldname = ?',array("filestatus"));
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_role ADD allowassignedrecordsto INT(2) NOT NULL DEFAULT 1', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_assets MODIFY datesold date', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_assets MODIFY dateinservice date', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_assets MODIFY serialnumber varchar(200)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_assets MODIFY account int(19)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE com_ottocrat_workflowtask_queue ADD COLUMN task_contents text', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE com_ottocrat_workflowtask_queue DROP INDEX com_ottocrat_workflowtask_queue_idx',array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_mailscanner_ids modify column messageid varchar(512)' , array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_mailscanner_ids add index scanner_message_ids_idx (scannerid, messageid)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_mailscanner_folders add index folderid_idx (folderid)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_leaddetails add index email_idx (email)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_contactdetails add index email_idx (email)', array());
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_account add index email_idx (email1, email2)', array());

$moduleInstance = Ottocrat_Module::getInstance('Users');
$blockInstance = Ottocrat_Block::getInstance('LBL_MORE_INFORMATION',$moduleInstance);

$field = new Ottocrat_Field();
$field->name = 'leftpanelhide';
$field->label = 'Left Panel Hide';
$field->column = 'leftpanelhide';
$field->table = 'ottocrat_users';
$field->uitype = 56;
$field->typeofdata = 'V~O';
$field->readonly = 1;
$field->displaytype = 1;
$field->masseditable = 1;
$field->quickcreate = 1;
$field->defaultvalue = 0;
$field->columntype = 'VARCHAR(3)';
$blockInstance->addField($field);

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_users SET leftpanelhide = ?', array(0));
$potentialModule = Ottocrat_Module::getInstance('Potentials');
$block = Ottocrat_Block::getInstance('LBL_OPPORTUNITY_INFORMATION', $potentialModule);

$relatedToField = Ottocrat_Field::getInstance('related_to', $potentialModule);
$relatedToField->unsetRelatedModules(array('Contacts'));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET typeofdata = ? WHERE fieldid = ?', array('V~O', $relatedToField->id));

$contactField = Ottocrat_Field::getInstance('contact_id', $potentialModule);
if(!$contactField) {
	$contactField = new Ottocrat_Field();
	$contactField->name = 'contact_id';
	$contactField->label = 'Contact Name';
	$contactField->uitype = '10';
	$contactField->column = 'contact_id';
	$contactField->table = 'ottocrat_potential';
	$contactField->columntype = 'INT(19)';
	$block->addField($contactField);
	$contactField->setRelatedModules(array('Contacts'));
	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET summaryfield=1 WHERE fieldid = ?', array($contactField->id));
}

$lastPotentialId = 0;
do {
	$result = $adb->pquery("SELECT potentialid ,related_to FROM ottocrat_potential WHERE potentialid > ? LIMIT 500",
			array($lastPotentialId));
	if (!$adb->num_rows($result)) break;

	while ($row = $adb->fetch_array($result)) {
		$relatedTo = $row['related_to'];
		$potentialId = $row['potentialid'];

		$relatedToType = getSalesEntityType($relatedTo);
		if($relatedToType != 'Accounts') {
			Migration_Index_View::ExecuteQuery('UPDATE ottocrat_potential SET contact_id = ?, related_to = null WHERE potentialid = ?',
					array($relatedTo, $potentialId));
		}
		if (intval($potentialId) > $lastPotentialId) {
			$lastPotentialId = intval($row['potentialid']);
		}
		unset($relatedTo);
	}
	unset($result);
} while(true);

$filterResult = $adb->pquery('SELECT * FROM ottocrat_cvadvfilter WHERE columnname like ?',
		array('ottocrat_potential:related_to:related_to:Potentials_Related_%'));
$rows = $adb->num_rows($filterResult);
for($i=0; $i<$rows; $i++) {
	$cvid = $adb->query_result($filterResult, $i, 'cvid');
	$columnIndex = $adb->query_result($filterResult, $i, 'columnindex');
	$comparator = $adb->query_result($filterResult, $i, 'comparator');
	$value = $adb->query_result($filterResult, $i, 'value');

	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_cvadvfilter SET groupid = 2, column_condition = ? WHERE cvid = ?', array('or', $cvid));
	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_cvadvfilter_grouping SET groupid = 2 WHERE cvid = ?', array($cvid));

	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_cvadvfilter(cvid,columnindex,columnname,comparator,value,groupid,column_condition)
		VALUES(?,?,?,?,?,?,?)', array($cvid, ++$columnIndex,'ottocrat_potential:contact_id:contact_id:Potentials_Contact_Name:V',
			$comparator, $value, 2, ''));
}
unset($filterResult);

$filterColumnList = $adb->pquery('SELECT * FROM ottocrat_cvcolumnlist WHERE columnname like ?',
		array('ottocrat_potential:related_to:related_to:Potentials_Related_%'));
$filterColumnRows = $adb->num_rows($filterColumnList);
for($j=0; $j<$filterColumnRows; $j++) {
	$cvid = $adb->query_result($filterColumnList, $j, 'cvid');
	$filterResult = $adb->pquery('SELECT MAX(columnindex) AS maxcolumn FROM ottocrat_cvcolumnlist WHERE cvid = ?', array($cvid));
	$maxColumnIndex = $adb->query_result($filterResult, 0, 'maxcolumn');
	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_cvcolumnlist(cvid,columnindex,columnname) VALUES (?,?,?)', array($cvid, ++$maxColumnIndex,
		'ottocrat_potential:contact_id:contact_id:Potentials_Contact_Name:V'));
	unset($filterResult);
}
unset($filterColumnList);

$reportColumnResult = $adb->pquery('SELECT * FROM ottocrat_selectcolumn WHERE columnname = ?',
		array('ottocrat_potential:related_to:Potentials_Related_To:related_to:V'));
$reportColumnRows = $adb->num_rows($reportColumnResult);

for($k=0; $k<$reportColumnRows; $k++) {
	$reportId = $adb->query_result($reportColumnResult, $k, 'queryid');
	$filterResult = $adb->pquery('SELECT MAX(columnindex) AS maxcolumn FROM ottocrat_selectcolumn WHERE queryid = ?', array($reportId));
	$maxColumnIndex = $adb->query_result($filterResult, 0, 'maxcolumn');
	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_selectcolumn(queryid,columnindex,columnname) VALUES (?,?,?)', array($reportId,
		++$maxColumnIndex, 'ottocrat_potential:contact_id:Potentials_Contact_Name:contact_id:V'));
	unset($filterResult);
}
unset($reportColumnResult);

$filterResult = $adb->pquery('SELECT * FROM ottocrat_relcriteria WHERE columnname = ?',
					array('ottocrat_potential:related_to:Potentials_Related_To:related_to:V'));
$rows = $adb->num_rows($filterResult);
for($i=0; $i<$rows; $i++) {

	$reportId = $adb->query_result($filterResult, $i, 'queryid');
	$columnIndex = $adb->query_result($filterResult, $i, 'columnindex');
	$comparator = $adb->query_result($filterResult, $i, 'comparator');
	$value = $adb->query_result($filterResult, $i, 'value');

	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_relcriteria SET groupid = 2, column_condition = ? WHERE queryid = ?', array('or', $reportId));
	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_relcriteria_grouping SET groupid = 2 WHERE queryid = ?', array($reportId));

	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_relcriteria(queryid,columnindex,columnname,comparator,value,groupid,column_condition)
		VALUES(?,?,?,?,?,?,?)', array($reportId, ++$columnIndex,'ottocrat_potential:contact_id:Potentials_Contact_Name:contact_id:V',
			$comparator, $value, 2, ''));
}
unset($filterResult);

$ticketsModule = Ottocrat_Module::getInstance('HelpDesk');
$ticketsBlock = Ottocrat_Block::getInstance('LBL_TICKET_INFORMATION', $ticketsModule);

$relatedToField = Ottocrat_Field::getInstance('parent_id', $ticketsModule);
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET uitype = 10 WHERE fieldid = ?', array($relatedToField->id));
$relatedToField->setRelatedModules(array('Accounts'));

$contactField = Ottocrat_Field::getInstance('contact_id', $ticketsModule);
if(!$contactField) {
	$contactField = new Ottocrat_Field();
	$contactField->name = 'contact_id';
	$contactField->label = 'Contact Name';
	$contactField->table = 'ottocrat_troubletickets';
	$contactField->column = 'contact_id';
	$contactField->columntype = 'INT(19)';
	$contactField->uitype = '10';
	$ticketsBlock->addField($contactField);

	$contactField->setRelatedModules(array('Contacts'));
	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET summaryfield = 1 WHERE fieldid = ?', array($contactField->id));
}

$lastTicketId = 0;
do {
	$ticketsResult = $adb->pquery("SELECT ticketid ,parent_id FROM ottocrat_troubletickets WHERE ticketid > ?
						LIMIT 500", array($lastTicketId));
	if (!$adb->num_rows($ticketsResult)) break;

	while ($row = $adb->fetch_array($ticketsResult)) {
		$parent = $row['parent_id'];
		$ticketId = $row['ticketid'];

		$parentType = getSalesEntityType($parent);
		if($parentType != 'Accounts') {
			Migration_Index_View::ExecuteQuery('UPDATE ottocrat_troubletickets SET contact_id = ?, parent_id = null WHERE ticketid = ?',
					array($parent, $ticketId));
		}
		if (intval($ticketId) > $lastTicketId) {
			$lastTicketId = intval($row['ticketid']);
		}
		unset($parent);
	}
	unset($ticketsResult);
} while(true);

$ticketFilterResult = $adb->pquery('SELECT * FROM ottocrat_cvadvfilter WHERE columnname like ?',
						array('ottocrat_troubletickets:parent_id:parent_id:HelpDesk_Related%'));
$rows = $adb->num_rows($ticketFilterResult);
for($i=0; $i<$rows; $i++) {
	$cvid = $adb->query_result($ticketFilterResult, $i, 'cvid');
	$columnIndex = $adb->query_result($ticketFilterResult, $i, 'columnindex');
	$comparator = $adb->query_result($ticketFilterResult, $i, 'comparator');
	$value = $adb->query_result($ticketFilterResult, $i, 'value');

	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_cvadvfilter SET groupid = 2, column_condition = ? WHERE cvid = ?', array('or', $cvid));
	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_cvadvfilter_grouping SET groupid = 2 WHERE cvid = ?', array($cvid));

	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_cvadvfilter(cvid,columnindex,columnname,comparator,value,groupid,column_condition)
		VALUES(?,?,?,?,?,?,?)', array($cvid, ++$columnIndex,'ottocrat_troubletickets:contact_id:contact_id:HelpDesk_Contact_Name:V',
			$comparator, $value, 2, ''));
}
unset($ticketFilterResult);

$filterColumnList = $adb->pquery('SELECT * FROM ottocrat_cvcolumnlist WHERE columnname like ?',
		array('ottocrat_troubletickets:parent_id:parent_id:HelpDesk_Related_%'));
$filterColumnRows = $adb->num_rows($filterColumnList);
for($j=0; $j<$filterColumnRows; $j++) {
	$cvid = $adb->query_result($filterColumnList, $j, 'cvid');
	$filterResult = $adb->pquery('SELECT MAX(columnindex) AS maxcolumn FROM ottocrat_cvcolumnlist WHERE cvid = ?', array($cvid));
	$maxColumnIndex = $adb->query_result($filterResult, 0, 'maxcolumn');
	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_cvcolumnlist(cvid,columnindex,columnname) VALUES (?,?,?)', array($cvid, ++$maxColumnIndex,
		'ottocrat_troubletickets:contact_id:contact_id:HelpDesk_Contact_Name:V'));
	unset($filterResult);
}
unset($filterColumnList);

$reportColumnResult = $adb->pquery('SELECT * FROM ottocrat_selectcolumn WHERE columnname like ?',
		array('ottocrat_troubletickets:parent_id:HelpDesk_Related_To:parent_id%'));
$reportColumnRows = $adb->num_rows($reportColumnResult);
for($k=0; $k<$reportColumnRows; $k++) {
	$reportId = $adb->query_result($reportColumnResult, $k, 'queryid');
	$filterResult = $adb->pquery('SELECT MAX(columnindex) AS maxcolumn FROM ottocrat_selectcolumn WHERE queryid = ?', array($reportId));
	$maxColumnIndex = $adb->query_result($filterResult, 0, 'maxcolumn');
	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_selectcolumn(queryid,columnindex,columnname) VALUES (?,?,?)', array($reportId,
		++$maxColumnIndex, 'ottocrat_troubletickets:contact_id:HelpDesk_Contact_Name:contact_id:V'));
	unset($filterResult);
}
unset($reportColumnResult);

$filterResult = $adb->pquery('SELECT * FROM ottocrat_relcriteria WHERE columnname like ?',
					array('ottocrat_troubletickets:parent_id:HelpDesk_Related_To:parent_id%'));
$rows = $adb->num_rows($filterResult);
for($i=0; $i<$rows; $i++) {
	$reportId = $adb->query_result($filterResult, $i, 'queryid');
	$columnIndex = $adb->query_result($filterResult, $i, 'columnindex');
	$comparator = $adb->query_result($filterResult, $i, 'comparator');
	$value = $adb->query_result($filterResult, $i, 'value');

	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_relcriteria SET groupid = 2, column_condition = ? WHERE queryid = ?', array('or', $reportId));
	Migration_Index_View::ExecuteQuery('UPDATE ottocrat_relcriteria_grouping SET groupid = 2 WHERE queryid = ?', array($reportId));

	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_relcriteria(queryid,columnindex,columnname,comparator,value,groupid,column_condition)
		VALUES(?,?,?,?,?,?,?)', array($reportId, ++$columnIndex,'ottocrat_troubletickets:contact_id:HelpDesk_Contact_Name:contact_id:V',
			$comparator, $value, 2, ''));
}
unset($filterResult);

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET defaultvalue=? WHERE tablename=? AND fieldname= ? ', array('Active', 'ottocrat_users', 'status'));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET defaultvalue=? WHERE tablename=? AND fieldname= ? ', array('12', 'ottocrat_users', 'hour_format'));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET defaultvalue=? WHERE tablename=? AND fieldname= ? ', array('softed', 'ottocrat_users', 'theme'));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET defaultvalue=? WHERE tablename=? AND fieldname= ? ', array('Monday', 'ottocrat_users', 'dayoftheweek'));
Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_shorturls ADD COLUMN onetime int(5)', array());

$checkQuery = 'SELECT 1 FROM ottocrat_currencies  WHERE currency_name=?';
$checkResult = $adb->pquery($checkQuery,array('Iraqi Dinar'));
if($adb->num_rows($checkResult) <= 0) {
	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_currencies VALUES ('.$adb->getUniqueID("ottocrat_currencies").',"Iraqi Dinar","IQD","ID")',array());
}

$potentialModule = Ottocrat_Module::getInstance('Potentials');
$potentialTabId = getTabid('Potentials');

$contactField = Ottocrat_Field::getInstance('contact_id', $potentialModule);
$relatedToField = Ottocrat_Field::getInstance('related_to', $potentialModule);

$result = $adb->pquery('SELECT sequence,block FROM ottocrat_field WHERE fieldid = ? and tabid = ?', array($relatedToField->id, $potentialTabId));
$relatedToFieldSequence = $adb->query_result($result, 0, 'sequence');
$relatedToFieldBlock = $adb->query_result($result, 0, 'block');

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET sequence = sequence+1 WHERE sequence > ? and tabid = ? and block = ?', array($relatedToFieldSequence, $potentialTabId, $relatedToFieldBlock));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET sequence = ? WHERE fieldid = ?', array($relatedToFieldSequence+1, $contactField->id));

$ticketsModule = Ottocrat_Module::getInstance('HelpDesk');
$ticketsTabId = getTabid('HelpDesk');

$contactField = Ottocrat_Field::getInstance('contact_id', $ticketsModule);
$relatedToField = Ottocrat_Field::getInstance('parent_id', $ticketsModule);

$result = $adb->pquery('SELECT sequence,block FROM ottocrat_field WHERE fieldid = ? and tabid = ?', array($relatedToField->id, $ticketsTabId));
$relatedToFieldSequence = $adb->query_result($result, 0, 'sequence');
$relatedToFieldBlock = $adb->query_result($result, 0, 'block');

Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET sequence = sequence+1 WHERE sequence > ? and tabid = ? and block = ?', array($relatedToFieldSequence, $ticketsTabId, $relatedToFieldBlock));
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_field SET sequence = ? WHERE fieldid = ?', array($relatedToFieldSequence+1, $contactField->id));

$checkQuery = 'SELECT 1 FROM ottocrat_currencies  WHERE currency_name=?';
$checkResult = $adb->pquery($checkQuery,array('Maldivian Ruffiya'));
if($adb->num_rows($checkResult) <= 0) {
	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_currencies VALUES ('.$adb->getUniqueID("ottocrat_currencies").',"Maldivian Ruffiya","MVR","MVR")',array());
}


$result = $adb->pquery('SELECT count(*) AS count FROM ottocrat_emailtemplates', array());
Migration_Index_View::ExecuteQuery('UPDATE ottocrat_emailtemplates_seq SET id = ?', array(1 + ((int)$adb->query_result($result, 0, 'count'))));

$usersInstance = Ottocrat_Module::getInstance('Users');
$blockInstance = Ottocrat_Block::getInstance('LBL_MORE_INFORMATION', $usersInstance);
$usersRowHeightField = Ottocrat_Field::getInstance('rowheight', $usersInstance);
if (!$usersRowHeightField) {
	$field = new Ottocrat_Field();
	$field->name = 'rowheight';
	$field->label = 'Row Height';
	$field->table = 'ottocrat_users';
	$field->uitype = 16;
	$field->typeofdata = 'V~O';
	$field->readonly = 1;
	$field->displaytype = 1;
	$field->masseditable = 1;
	$field->quickcreate = 1;
	$field->columntype = 'VARCHAR(10)';
	$field->defaultvalue = 'medium';
	$blockInstance->addField($field);

	$field->setPicklistValues(array('wide', 'medium', 'narrow'));
}

$moduleInstance = Ottocrat_Module::getInstance('HelpDesk');
$block = Ottocrat_Block::getInstance('LBL_TICKET_INFORMATION', $moduleInstance);
$fromPortal = Ottocrat_Field_Model::getInstance('from_portal', $moduleInstance);

if(!$fromPortal){
    $field = new Ottocrat_Field();
    $field->name = 'from_portal';
    $field->label = 'From Portal';
    $field->table ='ottocrat_ticketcf';
    $field->column = 'from_portal';
    $field->columntype = 'varchar(3)';
    $field->typeofdata = 'C~O';
    $field->uitype = 56;
    $field->displaytype = 3;
    $field->presence = 0;
    $block->addField($field);
}

//Start: Customer - Feature #10254 Configuring all Email notifications including Ticket notifications
$moduleName = 'HelpDesk';
//Start: Moving Entity methods of Comments to Workflows
$result = $adb->pquery('SELECT DISTINCT workflow_id FROM com_ottocrat_workflowtasks WHERE workflow_id IN
				(SELECT workflow_id FROM com_ottocrat_workflows WHERE module_name IN (?) AND defaultworkflow = ?)
				AND task LIKE ?', array('ModComments', 1, '%VTEntityMethodTask%'));
$numOfRows = $adb->num_rows($result);

for ($i = 0; $i < $numOfRows; $i++) {
	$wfs = new VTWorkflowManager($adb);
	$workflowModel = $wfs->retrieve($adb->query_result($result, $i, 'workflow_id'));
	$workflowModel->filtersavedinnew = 6;
	$workflowModel->executionCondition = 3;
	$workflowModel->moduleName = $moduleName;

	$newWorkflowModel = $wfs->newWorkflow($moduleName);
	$workflowProperties = get_object_vars($workflowModel);
	foreach ($workflowProperties as $workflowPropertyName => $workflowPropertyValue) {
		$newWorkflowModel->$workflowPropertyName = $workflowPropertyValue;
	}

	$newConditions = array(
		array('fieldname' => '_VT_add_comment',
			'operation' => 'is added',
			'value' => '',
			'valuetype' => 'rawtext',
			'joincondition' => '',
			'groupjoin' => 'and',
			'groupid' => '0')
	);

	$tm = new VTTaskManager($adb);
	$tasks = $tm->getTasksForWorkflow($workflowModel->id);
	foreach ($tasks as $task) {
		$properties = get_object_vars($task);

		$emailTask = new VTEmailTask();
		$emailTask->executeImmediately = 0;
		$emailTask->summary = $properties['summary'];
		$emailTask->active = $properties['active'];

		switch ($properties['methodName']) {
			case 'CustomerCommentFromPortal' :
				$tm->deleteTask($task->id);

				$newWorkflowConditions = $newConditions;
				$newWorkflowConditions[] = array(
					'fieldname' => 'from_portal',
					'operation' => 'is',
					'value' => '1',
					'valuetype' => 'rawtext',
					'joincondition' => '',
					'groupjoin' => 'and',
					'groupid' => '0'
				);

				unset($newWorkflowModel->id);
				$newWorkflowModel->test = Zend_Json::encode($newWorkflowConditions);
				$newWorkflowModel->description = 'Comment Added From Portal : Send Email to Record Owner';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Comment Added From Portal : Send Email to Record Owner';
				$emailTask->fromEmail = '$(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname)&lt;$(contact_id : (Contacts) email)&gt;';
				$emailTask->recepient = ',$(assigned_user_id : (Users) email1)';
				$emailTask->subject = 'Respond to Ticket ID## $(general : (__OttocratMeta__) recordId) ## in Customer Portal - URGENT';
				$emailTask->content = 'Dear $(assigned_user_id : (Users) last_name) $(assigned_user_id : (Users) first_name),<br><br>
								Customer has provided the following additional information to your reply:<br><br>
								<b>$lastComment</b><br><br>
								Kindly respond to above ticket at the earliest.<br><br>
								Regards<br>Support Administrator';
				$tm->saveTask($emailTask);
				break;


			case 'TicketOwnerComments' :
				$tm->deleteTask($task->id);

				$newConditions[] = array(
					'fieldname' => 'from_portal',
					'operation' => 'is',
					'value' => '0',
					'valuetype' => 'rawtext',
					'joincondition' => '',
					'groupjoin' => 'and',
					'groupid' => '0'
				);

				$newWorkflowConditions = $newConditions;
				$newWorkflowConditions[] = array(
					'fieldname' => '(contact_id : (Contacts) emailoptout)',
					'operation' => 'is',
					'value' => '0',
					'valuetype' => 'rawtext',
					'joincondition' => 'and',
					'groupjoin' => 'and',
					'groupid' => '0'
				);

				$portalCondition = array(
					array('fieldname' => '(contact_id : (Contacts) portal)',
						'operation' => 'is',
						'value' => '0',
						'valuetype' => 'rawtext',
						'joincondition' => 'and',
						'groupjoin' => 'and',
						'groupid' => '0')
				);

				unset($newWorkflowModel->id);
				$newWorkflowModel->test = Zend_Json::encode(array_merge($portalCondition, $newWorkflowConditions));
				$newWorkflowModel->description = 'Comment Added From CRM : Send Email to Contact, where Contact is not a Portal User';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Comment Added From CRM : Send Email to Contact, where Contact is not a Portal User';
				$emailTask->fromEmail = '$(general : (__OttocratMeta__) supportName)&lt;$(general : (__OttocratMeta__) supportEmailId)&gt;';
				$emailTask->recepient = ',$(contact_id : (Contacts) email)';
				$emailTask->subject = '$ticket_no [ Ticket Id : $(general : (__OttocratMeta__) recordId) ] $ticket_title';
				$emailTask->content = 'Dear $(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname),<br><br>
							The Ticket is replied the details are :<br><br>
							Ticket No : $ticket_no<br>
							Status : $ticketstatus<br>
							Category : $ticketcategories<br>
							Severity : $ticketseverities<br>
							Priority : $ticketpriorities<br><br>
							Description : <br>$description<br><br>
							Solution : <br>$solution<br>
							The comments are : <br>
							$allComments<br><br>
							Regards<br>Support Administrator';
				$tm->saveTask($emailTask);

				$portalCondition = array(
					array('fieldname' => '(contact_id : (Contacts) portal)',
						'operation' => 'is',
						'value' => '1',
						'valuetype' => 'rawtext',
						'joincondition' => 'and',
						'groupjoin' => 'and',
						'groupid' => '0')
				);

				unset($newWorkflowModel->id);
				$newWorkflowModel->test = Zend_Json::encode(array_merge($portalCondition, $newWorkflowConditions));
				$newWorkflowModel->description = 'Comment Added From CRM : Send Email to Contact, where Contact is Portal User';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Comment Added From CRM : Send Email to Contact, where Contact is Portal User';
				$emailTask->content = 'Ticket No : $ticket_no<br>
										Ticket Id : $(general : (__OttocratMeta__) recordId)<br>
										Subject : $ticket_title<br><br>
										Dear $(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname),<br><br>
										There is a reply to <b>$ticket_title</b> in the "Customer Portal" at Ottocrat.
										You can use the following link to view the replies made:<br>
										<a href="$(general : (__OttocratMeta__) portaldetailviewurl)">Ticket Details</a><br><br>
										Thanks<br>$(general : (__OttocratMeta__) supportName)';
				$tm->saveTask($emailTask);

				$newConditions[] = array(
					'fieldname' => '(parent_id : (Accounts) emailoptout)',
					'operation' => 'is',
					'value' => '0',
					'valuetype' => 'rawtext',
					'joincondition' => 'and',
					'groupjoin' => 'and',
					'groupid' => '0'
				);

				$workflowModel->test = Zend_Json::encode($newConditions);
				$workflowModel->description = 'Comment Added From CRM : Send Email to Organization';
				$wfs->save($workflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $workflowModel->id;
				$emailTask->summary = 'Comment Added From CRM : Send Email to Organization';
				$emailTask->recepient = ',$(parent_id : (Accounts) email1),';
				$emailTask->content = 'Ticket ID : $(general : (__OttocratMeta__) recordId)<br>Ticket Title : $ticket_title<br><br>
								Dear $(parent_id : (Accounts) accountname),<br><br>
								The Ticket is replied the details are :<br><br>
								Ticket No : $ticket_no<br>
								Status : $ticketstatus<br>
								Category : $ticketcategories<br>
								Severity : $ticketseverities<br>
								Priority : $ticketpriorities<br><br>
								Description : <br>$description<br><br>
								Solution : <br>$solution<br>
								The comments are : <br>
								$allComments<br><br>
								Regards<br>Support Administrator';
				$tm->saveTask($emailTask);

				break;
		}
	}
}
//End: Moved Entity methods of Comments to Workflows
//Start: Moving Entity methods of Tickets to Workflows
$result = $adb->pquery('SELECT DISTINCT workflow_id FROM com_ottocrat_workflowtasks WHERE workflow_id IN
				(SELECT workflow_id FROM com_ottocrat_workflows WHERE module_name IN (?) AND defaultworkflow = ?)
				AND task LIKE ?', array($moduleName, 1, '%VTEntityMethodTask%'));
$numOfRows = $adb->num_rows($result);

for ($i = 0; $i < $numOfRows; $i++) {
	$wfs = new VTWorkflowManager($adb);
	$workflowModel = $wfs->retrieve($adb->query_result($result, $i, 'workflow_id'));
	$workflowModel->filtersavedinnew = 6;

	$tm = new VTTaskManager($adb);
	$tasks = $tm->getTasksForWorkflow($workflowModel->id);
	foreach ($tasks as $task) {
		$properties = get_object_vars($task);

		$emailTask = new VTEmailTask();
		$emailTask->executeImmediately = 0;
		$emailTask->summary = $properties['summary'];
		$emailTask->active = $properties['active'];
		switch ($properties['methodName']) {
			case 'NotifyOnPortalTicketCreation' :
				$oldCondtions = Migration_Index_View::transformAdvanceFilterToWorkFlowFilter(Zend_Json::decode($workflowModel->test));
				$newConditions = array(
					array('fieldname' => 'from_portal',
						'operation' => 'is',
						'value' => '1',
						'valuetype' => 'rawtext',
						'joincondition' => '',
						'groupjoin' => 'and',
						'groupid' => '0')
				);
				$newConditions = array_merge($oldCondtions, $newConditions);

				$workflowModel->test = Zend_Json::encode($newConditions);
				$workflowModel->description = 'Ticket Creation From Portal : Send Email to Record Owner and Contact';
				$wfs->save($workflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $properties['workflowId'];
				$emailTask->summary = 'Notify Record Owner when Ticket is created from Portal';
				$emailTask->fromEmail = '$(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname)&lt;$(general : (__OttocratMeta__) supportEmailId)&gt;';
				$emailTask->recepient = ',$(assigned_user_id : (Users) email1)';
				$emailTask->subject = '[From Portal] $ticket_no [ Ticket Id : $(general : (__OttocratMeta__) recordId) ] $ticket_title';
				$emailTask->content = 'Ticket No : $ticket_no<br>
							  Ticket ID : $(general : (__OttocratMeta__) recordId)<br>
							  Ticket Title : $ticket_title<br><br>
							  $description';
				$tm->saveTask($emailTask);

				$emailTask->id = $properties['id'];
				$emailTask->summary = 'Notify Related Contact when Ticket is created from Portal';
				$emailTask->fromEmail = '$(general : (__OttocratMeta__) supportName)&lt;$(general : (__OttocratMeta__) supportEmailId)&gt;';
				$emailTask->recepient = ',$(contact_id : (Contacts) email)';

				$tm->saveTask($emailTask);
				break;


			case 'NotifyOnPortalTicketComment' :
				$tm->deleteTask($properties['id']);
				Migration_Index_View::ExecuteQuery('DELETE FROM com_ottocrat_workflows WHERE workflow_id = ?', array($workflowModel->id));
				break;


			case 'NotifyParentOnTicketChange' :
				$newWorkflowModel = $wfs->newWorkflow($workflowModel->moduleName);
				$workflowProperties = get_object_vars($workflowModel);
				foreach ($workflowProperties as $workflowPropertyName => $workflowPropertyValue) {
					$newWorkflowModel->$workflowPropertyName = $workflowPropertyValue;
				}

				$oldCondtions = Migration_Index_View::transformAdvanceFilterToWorkFlowFilter(Zend_Json::decode($newWorkflowModel->test));
				$newConditions = array(
					array('fieldname' => 'ticketstatus',
						'operation' => 'has changed to',
						'value' => 'Closed',
						'valuetype' => 'rawtext',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'solution',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'description',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1')
				);
				$newConditions = array_merge($oldCondtions, $newConditions);

				$newAccountCondition = array(
					array('fieldname' => '(parent_id : (Accounts) emailoptout)',
						'operation' => 'is',
						'value' => '0',
						'valuetype' => 'rawtext',
						'joincondition' => 'and',
						'groupjoin' => 'and',
						'groupid' => '0')
				);
				$newWorkflowConditions = array_merge($newAccountCondition, $newConditions);

				unset($newWorkflowModel->id);
				$newWorkflowModel->test = Zend_Json::encode($newWorkflowConditions);
				$newWorkflowModel->description = 'Send Email to Organization on Ticket Update';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->summary = 'Send Email to Organization on Ticket Update';
				$emailTask->fromEmail = '$(general : (__OttocratMeta__) supportName)&lt;$(general : (__OttocratMeta__) supportEmailId)&gt;';
				$emailTask->recepient = ',$(parent_id : (Accounts) email1)';
				$emailTask->subject = '$ticket_no [ Ticket Id : $(general : (__OttocratMeta__) recordId) ] $ticket_title';
				$emailTask->content = 'Ticket ID : $(general : (__OttocratMeta__) recordId)<br>Ticket Title : $ticket_title<br><br>
								Dear $(parent_id : (Accounts) accountname),<br><br>
								The Ticket is replied the details are :<br><br>
								Ticket No : $ticket_no<br>
								Status : $ticketstatus<br>
								Category : $ticketcategories<br>
								Severity : $ticketseverities<br>
								Priority : $ticketpriorities<br><br>
								Description : <br>$description<br><br>
								Solution : <br>$solution<br>
								The comments are : <br>
								$allComments<br><br>
								Regards<br>Support Administrator';

				$emailTask->workflowId = $newWorkflowModel->id;
				$tm->saveTask($emailTask);

				$portalCondition = array(
					array('fieldname' => 'from_portal',
						'operation' => 'is',
						'value' => '0',
						'valuetype' => 'rawtext',
						'joincondition' => '',
						'groupjoin' => 'and',
						'groupid' => '0')
				);

				unset($newWorkflowModel->id);
				$newWorkflowModel->executionCondition = 1;
				$newWorkflowModel->test = Zend_Json::encode(array_merge($newAccountCondition, $portalCondition));
				$newWorkflowModel->description = 'Ticket Creation From CRM : Send Email to Organization';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Ticket Creation From CRM : Send Email to Organization';
				$tm->saveTask($emailTask);

				$newContactCondition = array(
					array('fieldname' => '(contact_id : (Contacts) emailoptout)',
						'operation' => 'is',
						'value' => '0',
						'valuetype' => 'rawtext',
						'joincondition' => 'and',
						'groupjoin' => 'and',
						'groupid' => '0')
				);
				$newConditions = array_merge($newContactCondition, $newConditions);

				$workflowModel->test = Zend_Json::encode($newConditions);
				$workflowModel->description = 'Send Email to Contact on Ticket Update';
				$wfs->save($workflowModel);

				$emailTask->id = $properties['id'];
				$emailTask->workflowId = $properties['workflowId'];
				$emailTask->summary = 'Send Email to Contact on Ticket Update';
				$emailTask->recepient = ',$(contact_id : (Contacts) email)';
				$emailTask->content = 'Ticket ID : $(general : (__OttocratMeta__) recordId)<br>Ticket Title : $ticket_title<br><br>
								Dear $(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname),<br><br>
								The Ticket is replied the details are :<br><br>
								Ticket No : $ticket_no<br>
								Status : $ticketstatus<br>
								Category : $ticketcategories<br>
								Severity : $ticketseverities<br>
								Priority : $ticketpriorities<br><br>
								Description : <br>$description<br><br>
								Solution : <br>$solution<br>
								The comments are : <br>
								$allComments<br><br>
								Regards<br>Support Administrator';

				$tm->saveTask($emailTask);

				unset($newWorkflowModel->id);
				$newWorkflowModel->executionCondition = 1;
				$newWorkflowModel->test = Zend_Json::encode(array_merge($newContactCondition, $portalCondition));
				$newWorkflowModel->description = 'Ticket Creation From CRM : Send Email to Contact';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Ticket Creation From CRM : Send Email to Contact';
				$tm->saveTask($emailTask);
				break;


			case 'NotifyOwnerOnTicketChange' :
				$tm->deleteTask($task->id);

				$newWorkflowModel = $wfs->newWorkflow($workflowModel->moduleName);
				$workflowProperties = get_object_vars($workflowModel);
				foreach ($workflowProperties as $workflowPropertyName => $workflowPropertyValue) {
					$newWorkflowModel->$workflowPropertyName = $workflowPropertyValue;
				}

				$oldCondtions = Migration_Index_View::transformAdvanceFilterToWorkFlowFilter(Zend_Json::decode($newWorkflowModel->test));
				$newConditions = array(
					array('fieldname' => 'ticketstatus',
						'operation' => 'has changed to',
						'value' => 'Closed',
						'valuetype' => 'rawtext',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'solution',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'assigned_user_id',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1'),
					array('fieldname' => 'description',
						'operation' => 'has changed',
						'value' => '',
						'valuetype' => '',
						'joincondition' => 'or',
						'groupjoin' => 'and',
						'groupid' => '1')
				);
				$newConditions = array_merge($oldCondtions, $newConditions);

				unset($newWorkflowModel->id);
				$newWorkflowModel->test = Zend_Json::encode($newConditions);
				$newWorkflowModel->description = 'Send Email to Record Owner on Ticket Update';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Send Email to Record Owner on Ticket Update';
				$emailTask->fromEmail = '$(general : (__OttocratMeta__) supportName)&lt;$(general : (__OttocratMeta__) supportEmailId)&gt;';
				$emailTask->recepient = ',$(assigned_user_id : (Users) email1)';
				$emailTask->subject = 'Ticket Number : $ticket_no $ticket_title';
				$emailTask->content = 'Ticket ID : $(general : (__OttocratMeta__) recordId)<br>Ticket Title : $ticket_title<br><br>
								Dear $(assigned_user_id : (Users) last_name) $(assigned_user_id : (Users) first_name),<br><br>
								The Ticket is replied the details are :<br><br>
								Ticket No : $ticket_no<br>
								Status : $ticketstatus<br>
								Category : $ticketcategories<br>
								Severity : $ticketseverities<br>
								Priority : $ticketpriorities<br><br>
								Description : <br>$description<br><br>
								Solution : <br>$solution
								$allComments<br><br>
								Regards<br>Support Administrator';
				$emailTask->id = '';
				$tm->saveTask($emailTask);

				$portalCondition = array(
					array('fieldname' => 'from_portal',
						'operation' => 'is',
						'value' => '0',
						'valuetype' => 'rawtext',
						'joincondition' => '',
						'groupjoin' => 'and',
						'groupid' => '0')
				);

				unset($newWorkflowModel->id);
				$newWorkflowModel->executionCondition = 1;
				$newWorkflowModel->test = Zend_Json::encode($portalCondition);
				$newWorkflowModel->description = 'Ticket Creation From CRM : Send Email to Record Owner';
				$wfs->save($newWorkflowModel);

				$emailTask->id = '';
				$emailTask->workflowId = $newWorkflowModel->id;
				$emailTask->summary = 'Ticket Creation From CRM : Send Email to Record Owner';
				$tm->saveTask($emailTask);
				break;
		}
	}
}
$em = new VTEventsManager($adb);
$em->registerHandler('ottocrat.entity.aftersave', 'modules/ModComments/ModCommentsHandler.php', 'ModCommentsHandler');
$result = $adb->pquery('SELECT blockid FROM ottocrat_blocks where tabid = ? AND (blocklabel is NULL OR blocklabel = "")', array(getTabid('Emails')));
$numOfRows = $adb->num_rows($result);

$query = 'UPDATE ottocrat_blocks SET blocklabel = CASE blockid ';
for ($i = 0; $i < $numOfRows; $i++) {
	$blockId = $adb->query_result($result, $i, 'blockid');
	$blockLabel = 'Emails_Block' . ($i + 1);
	$query .= "WHEN $blockId THEN '$blockLabel' ";
}
$query .= 'ELSE blocklabel END';
Migration_Index_View::ExecuteQuery($query, array());

$result = $adb->pquery('SELECT task_id FROM com_ottocrat_workflowtasks WHERE workflow_id IN
																	(SELECT workflow_id FROM com_ottocrat_workflows WHERE module_name IN (?, ?))
																	AND task LIKE ?', array('Calendar', 'Events', '%$(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname)%'));
$numOfRows = $adb->num_rows($result);

for ($i = 0; $i < $numOfRows; $i++) {
	$tm = new VTTaskManager($adb);
	$task = $tm->retrieveTask($adb->query_result($result, $i, 'task_id'));

	$emailTask = new VTEmailTask();
	$properties = get_object_vars($task);
	foreach ($properties as $propertyName => $propertyValue) {
		$propertyValue = str_replace('$date_start  $time_start ( $(general : (__OttocratMeta__) usertimezone) ) ', '$date_start', $propertyValue);
		$propertyValue = str_replace('$due_date  $time_end ( $(general : (__OttocratMeta__) usertimezone) )', '$due_date', $propertyValue);
		$propertyValue = str_replace('$due_date ( $(general : (__OttocratMeta__) usertimezone) )', '$due_date', $propertyValue);
		$propertyValue = str_replace('$(contact_id : (Contacts) lastname) $(contact_id : (Contacts) firstname)', '$contact_id', $propertyValue);
		$emailTask->$propertyName = $propertyValue;
	}

	$tm->saveTask($emailTask);
}

$result = $adb->pquery('SELECT 1 FROM ottocrat_currencies WHERE currency_name = ?', array('Ugandan Shilling'));
if(!$adb->num_rows($result)) {
	Migration_Index_View::ExecuteQuery('INSERT INTO ottocrat_currencies (currencyid, currency_name, currency_code, currency_symbol) VALUES(?, ?, ?, ?)',
			array($adb->getUniqueID('ottocrat_currencies'), 'Ugandan Shilling', 'UGX', 'Sh'));
}
$em = new VTEventsManager($adb);
$em->registerHandler('ottocrat.picklist.afterrename', 'modules/Settings/Picklist/handlers/PickListHandler.php', 'PickListHandler');
$em->registerHandler('ottocrat.picklist.afterdelete', 'modules/Settings/Picklist/handlers/PickListHandler.php', 'PickListHandler');

Migration_Index_View::ExecuteQuery('ALTER TABLE ottocrat_inventoryproductrel MODIFY comment varchar(500)', array());

$module = Ottocrat_Module::getInstance('Accounts');
$module->addLink('DETAILVIEWSIDEBARWIDGET', 'Google Map', 'module=Google&view=Map&mode=showMap&viewtype=detail', '', '', '');

// Changes as on 2013.11.29

Migration_Index_View::ExecuteQuery('DELETE FROM ottocrat_settings_field WHERE name=?', array('LBL_BACKUP_SERVER_SETTINGS'));

// Changes ends as on 2013.11.29
Migration_Index_View::ExecuteQuery("CREATE TABLE IF NOT EXISTS ottocrat_faqcf ( 
                                faqid int(19), 
                                PRIMARY KEY (faqid), 
                                CONSTRAINT fk_1_ottocrat_faqcf FOREIGN KEY (faqid) REFERENCES ottocrat_faq(id) ON DELETE CASCADE 
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8", array()); 