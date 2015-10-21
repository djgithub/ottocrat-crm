<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/
class Products extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name = 'ottocrat_products';
	var $table_index= 'productid';
    var $column_fields = Array();

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('ottocrat_productcf','productid');

	var $tab_name = Array('ottocrat_crmentity','ottocrat_products','ottocrat_productcf');

	var $tab_name_index = Array('ottocrat_crmentity'=>'crmid','ottocrat_products'=>'productid','ottocrat_productcf'=>'productid','ottocrat_seproductsrel'=>'productid','ottocrat_producttaxrel'=>'productid');



	// This is the list of ottocrat_fields that are in the lists.
	var $list_fields = Array(
		'Product Name'=>Array('products'=>'productname'),
		'Part Number'=>Array('products'=>'productcode'),
		'Commission Rate'=>Array('products'=>'commissionrate'),
		'Qty/Unit'=>Array('products'=>'qty_per_unit'),
		'Unit Price'=>Array('products'=>'unit_price')
	);
	var $list_fields_name = Array(
		'Product Name'=>'productname',
		'Part Number'=>'productcode',
		'Commission Rate'=>'commissionrate',
		'Qty/Unit'=>'qty_per_unit',
		'Unit Price'=>'unit_price'
	);

	var $list_link_field= 'productname';

	var $search_fields = Array(
		'Product Name'=>Array('products'=>'productname'),
		'Part Number'=>Array('products'=>'productcode'),
		'Unit Price'=>Array('products'=>'unit_price')
	);
	var $search_fields_name = Array(
		'Product Name'=>'productname',
		'Part Number'=>'productcode',
		'Unit Price'=>'unit_price'
	);

    var $required_fields = Array(
            'productname'=>1
    );

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();
	var $def_basicsearch_col = 'productname';

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'productname';
	var $default_sort_order = 'ASC';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to ottocrat_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'productname', 'assigned_user_id');
	 // Josh added for importing and exporting -added in patch2
    var $unit_price;

	/**	Constructor which will set the column_fields in this object
	 */
	function Products() {
		$this->log =LoggerManager::getLogger('product');
		$this->log->debug("Entering Products() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Products');
		$this->log->debug("Exiting Product method ...");
	}

	function save_module($module)
	{
		//Inserting into product_taxrel table
		if($_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates')
		{
			$this->insertTaxInformation('ottocrat_producttaxrel', 'Products');
			$this->insertPriceInformation('ottocrat_productcurrencyrel', 'Products');
		}

		// Update unit price value in ottocrat_productcurrencyrel
		$this->updateUnitPrice();
		//Inserting into attachments
		$this->insertIntoAttachment($this->id,'Products');

	}

	/**	function to save the product tax information in ottocrat_producttaxrel table
	 *	@param string $tablename - ottocrat_tablename to save the product tax relationship (producttaxrel)
	 *	@param string $module	 - current module name
	 *	$return void
	*/
	function insertTaxInformation($tablename, $module)
	{
		global $adb, $log;
		$log->debug("Entering into insertTaxInformation($tablename, $module) method ...");
		$tax_details = getAllTaxes();

		$tax_per = '';
		//Save the Product - tax relationship if corresponding tax check box is enabled
		//Delete the existing tax if any
		if($this->mode == 'edit')
		{
			for($i=0;$i<count($tax_details);$i++)
			{
				$taxid = getTaxId($tax_details[$i]['taxname']);
				$sql = "delete from ottocrat_producttaxrel where productid=? and taxid=?";
				$adb->pquery($sql, array($this->id,$taxid));
			}
		}
		for($i=0;$i<count($tax_details);$i++)
		{
			$tax_name = $tax_details[$i]['taxname'];
			$tax_checkname = $tax_details[$i]['taxname']."_check";
			if($_REQUEST[$tax_checkname] == 'on' || $_REQUEST[$tax_checkname] == 1)
			{
				$taxid = getTaxId($tax_name);
				$tax_per = $_REQUEST[$tax_name];
				if($tax_per == '')
				{
					$log->debug("Tax selected but value not given so default value will be saved.");
					$tax_per = getTaxPercentage($tax_name);
				}

				$log->debug("Going to save the Product - $tax_name tax relationship");

				$query = "insert into ottocrat_producttaxrel values(?,?,?)";
				$adb->pquery($query, array($this->id,$taxid,$tax_per));
			}
		}

		$log->debug("Exiting from insertTaxInformation($tablename, $module) method ...");
	}

	/**	function to save the product price information in ottocrat_productcurrencyrel table
	 *	@param string $tablename - ottocrat_tablename to save the product currency relationship (productcurrencyrel)
	 *	@param string $module	 - current module name
	 *	$return void
	*/
	function insertPriceInformation($tablename, $module)
	{
		global $adb, $log, $current_user;
		$log->debug("Entering into insertPriceInformation($tablename, $module) method ...");
		//removed the update of currency_id based on the logged in user's preference : fix 6490

		$currency_details = getAllCurrencies('all');

		//Delete the existing currency relationship if any
		if($this->mode == 'edit' && $_REQUEST['action'] !== 'MassEditSave')
		{
			for($i=0;$i<count($currency_details);$i++)
			{
				$curid = $currency_details[$i]['curid'];
				$sql = "delete from ottocrat_productcurrencyrel where productid=? and currencyid=?";
				$adb->pquery($sql, array($this->id,$curid));
			}
		}

		$product_base_conv_rate = getBaseConversionRateForProduct($this->id, $this->mode);
		$currencySet = 0;
		//Save the Product - Currency relationship if corresponding currency check box is enabled
		for($i=0;$i<count($currency_details);$i++)
		{
			$curid = $currency_details[$i]['curid'];
			$curname = $currency_details[$i]['currencylabel'];
			$cur_checkname = 'cur_' . $curid . '_check';
			$cur_valuename = 'curname' . $curid;

			$requestPrice = CurrencyField::convertToDBFormat($_REQUEST['unit_price'], null, true);
			$actualPrice = CurrencyField::convertToDBFormat($_REQUEST[$cur_valuename], null, true);
			if($_REQUEST[$cur_checkname] == 'on' || $_REQUEST[$cur_checkname] == 1)
			{
				$conversion_rate = $currency_details[$i]['conversionrate'];
				$actual_conversion_rate = $product_base_conv_rate * $conversion_rate;
				$converted_price = $actual_conversion_rate * $requestPrice;

				$log->debug("Going to save the Product - $curname currency relationship");

				$query = "insert into ottocrat_productcurrencyrel values(?,?,?,?)";
				$adb->pquery($query, array($this->id,$curid,$converted_price,$actualPrice));

				// Update the Product information with Base Currency choosen by the User.
				if ($_REQUEST['base_currency'] == $cur_valuename) {
					$currencySet = 1;
					$adb->pquery("update ottocrat_products set currency_id=?, unit_price=? where productid=?", array($curid, $actualPrice, $this->id));
				}
			}
			if(!$currencySet){
				$curid = fetchCurrency($current_user->id);
				$adb->pquery("update ottocrat_products set currency_id=? where productid=?", array($curid, $this->id));
			}
		}

		$log->debug("Exiting from insertPriceInformation($tablename, $module) method ...");
	}

	function updateUnitPrice() {
		$prod_res = $this->db->pquery("select unit_price, currency_id from ottocrat_products where productid=?", array($this->id));
		$prod_unit_price = $this->db->query_result($prod_res, 0, 'unit_price');
		$prod_base_currency = $this->db->query_result($prod_res, 0, 'currency_id');

		$query = "update ottocrat_productcurrencyrel set actual_price=? where productid=? and currencyid=?";
		$params = array($prod_unit_price, $this->id, $prod_base_currency);
		$this->db->pquery($query, $params);
	}

	function insertIntoAttachment($id,$module)
	{
		global  $log,$adb;
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;
		foreach($_FILES as $fileindex => $files)
		{
			if($files['name'] != '' && $files['size'] > 0)
			{
			      if($_REQUEST[$fileindex.'_hidden'] != '')
				      $files['original_name'] = vtlib_purify($_REQUEST[$fileindex.'_hidden']);
			      else
				      $files['original_name'] = stripslashes($files['name']);
			      $files['original_name'] = str_replace('"','',$files['original_name']);
				$file_saved = $this->uploadAndSaveFile($id,$module,$files);
			}
		}

		//Updating image information in main table of products
		$existingImageSql = 'SELECT name FROM ottocrat_seattachmentsrel INNER JOIN ottocrat_attachments ON
								ottocrat_seattachmentsrel.attachmentsid = ottocrat_attachments.attachmentsid LEFT JOIN ottocrat_products ON
								ottocrat_products.productid = ottocrat_seattachmentsrel.crmid WHERE ottocrat_seattachmentsrel.crmid = ?';
		$existingImages = $adb->pquery($existingImageSql,array($id));
		$numOfRows = $adb->num_rows($existingImages);
		$productImageMap = array();

		for ($i = 0; $i < $numOfRows; $i++) {
			$imageName = $adb->query_result($existingImages, $i, "name");
			array_push($productImageMap, decode_html($imageName));
		}
		$commaSeperatedFileNames = implode(",", $productImageMap);

		$adb->pquery('UPDATE ottocrat_products SET imagename = ? WHERE productid = ?',array($commaSeperatedFileNames,$id));

		//Remove the deleted ottocrat_attachments from db - Products
		if($module == 'Products' && $_REQUEST['del_file_list'] != '')
		{
			$del_file_list = explode("###",trim($_REQUEST['del_file_list'],"###"));
			foreach($del_file_list as $del_file_name)
			{
				$attach_res = $adb->pquery("select ottocrat_attachments.attachmentsid from ottocrat_attachments inner join ottocrat_seattachmentsrel on ottocrat_attachments.attachmentsid=ottocrat_seattachmentsrel.attachmentsid where crmid=? and name=?", array($id,$del_file_name));
				$attachments_id = $adb->query_result($attach_res,0,'attachmentsid');

				$del_res1 = $adb->pquery("delete from ottocrat_attachments where attachmentsid=?", array($attachments_id));
				$del_res2 = $adb->pquery("delete from ottocrat_seattachmentsrel where attachmentsid=?", array($attachments_id));
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}



	/**	function used to get the list of leads which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_leads($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_leads(".$id.") method ...");
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

		$query = "SELECT ottocrat_leaddetails.leadid, ottocrat_crmentity.crmid, ottocrat_leaddetails.firstname, ottocrat_leaddetails.lastname, ottocrat_leaddetails.company, ottocrat_leadaddress.phone, ottocrat_leadsubdetails.website, ottocrat_leaddetails.email, case when (ottocrat_users.user_name not like \"\") then ottocrat_users.user_name else ottocrat_groups.groupname end as user_name, ottocrat_crmentity.smownerid, ottocrat_products.productname, ottocrat_products.qty_per_unit, ottocrat_products.unit_price, ottocrat_products.expiry_date
			FROM ottocrat_leaddetails
			INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_leaddetails.leadid
			INNER JOIN ottocrat_leadaddress ON ottocrat_leadaddress.leadaddressid = ottocrat_leaddetails.leadid
			INNER JOIN ottocrat_leadsubdetails ON ottocrat_leadsubdetails.leadsubscriptionid = ottocrat_leaddetails.leadid
			INNER JOIN ottocrat_seproductsrel ON ottocrat_seproductsrel.crmid=ottocrat_leaddetails.leadid
			INNER JOIN ottocrat_products ON ottocrat_seproductsrel.productid = ottocrat_products.productid
			INNER JOIN ottocrat_leadscf ON ottocrat_leaddetails.leadid = ottocrat_leadscf.leadid
			LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_leads method ...");
		return $return_value;
	}

	/**	function used to get the list of accounts which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_accounts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_accounts(".$id.") method ...");
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

		$query = "SELECT ottocrat_account.accountid, ottocrat_crmentity.crmid, ottocrat_account.accountname, ottocrat_accountbillads.bill_city, ottocrat_account.website, ottocrat_account.phone, case when (ottocrat_users.user_name not like \"\") then ottocrat_users.user_name else ottocrat_groups.groupname end as user_name, ottocrat_crmentity.smownerid, ottocrat_products.productname, ottocrat_products.qty_per_unit, ottocrat_products.unit_price, ottocrat_products.expiry_date
			FROM ottocrat_account
			INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_account.accountid
			INNER JOIN ottocrat_accountbillads ON ottocrat_accountbillads.accountaddressid = ottocrat_account.accountid
            LEFT JOIN ottocrat_accountshipads ON ottocrat_accountshipads.accountaddressid = ottocrat_account.accountid
			INNER JOIN ottocrat_seproductsrel ON ottocrat_seproductsrel.crmid=ottocrat_account.accountid
			INNER JOIN ottocrat_products ON ottocrat_seproductsrel.productid = ottocrat_products.productid
			INNER JOIN ottocrat_accountscf ON ottocrat_account.accountid = ottocrat_accountscf.accountid
			LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_accounts method ...");
		return $return_value;
	}

	/**	function used to get the list of contacts which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		$query = "SELECT ottocrat_contactdetails.firstname, ottocrat_contactdetails.lastname, ottocrat_contactdetails.title, ottocrat_contactdetails.accountid, ottocrat_contactdetails.email, ottocrat_contactdetails.phone, ottocrat_crmentity.crmid, case when (ottocrat_users.user_name not like \"\") then ottocrat_users.user_name else ottocrat_groups.groupname end as user_name, ottocrat_crmentity.smownerid, ottocrat_products.productname, ottocrat_products.qty_per_unit, ottocrat_products.unit_price, ottocrat_products.expiry_date,ottocrat_account.accountname
			FROM ottocrat_contactdetails
			INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_contactdetails.contactid
			INNER JOIN ottocrat_seproductsrel ON ottocrat_seproductsrel.crmid=ottocrat_contactdetails.contactid
			INNER JOIN ottocrat_contactaddress ON ottocrat_contactdetails.contactid = ottocrat_contactaddress.contactaddressid
			INNER JOIN ottocrat_contactsubdetails ON ottocrat_contactdetails.contactid = ottocrat_contactsubdetails.contactsubscriptionid
			INNER JOIN ottocrat_customerdetails ON ottocrat_contactdetails.contactid = ottocrat_customerdetails.customerid
			INNER JOIN ottocrat_contactscf ON ottocrat_contactdetails.contactid = ottocrat_contactscf.contactid
			INNER JOIN ottocrat_products ON ottocrat_seproductsrel.productid = ottocrat_products.productid
			LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_account ON ottocrat_account.accountid = ottocrat_contactdetails.accountid
			WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}


	/**	function used to get the list of potentials which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_potential.potentialid, ottocrat_crmentity.crmid,
			ottocrat_potential.potentialname, ottocrat_account.accountname, ottocrat_potential.related_to, ottocrat_potential.contact_id,
			ottocrat_potential.sales_stage, ottocrat_potential.amount, ottocrat_potential.closingdate,
			case when (ottocrat_users.user_name not like '') then $userNameSql else
			ottocrat_groups.groupname end as user_name, ottocrat_crmentity.smownerid,
			ottocrat_products.productname, ottocrat_products.qty_per_unit, ottocrat_products.unit_price,
			ottocrat_products.expiry_date FROM ottocrat_potential
			INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_potential.potentialid
			INNER JOIN ottocrat_seproductsrel ON ottocrat_seproductsrel.crmid = ottocrat_potential.potentialid
			INNER JOIN ottocrat_products ON ottocrat_seproductsrel.productid = ottocrat_products.productid
			INNER JOIN ottocrat_potentialscf ON ottocrat_potential.potentialid = ottocrat_potentialscf.potentialid
			LEFT JOIN ottocrat_account ON ottocrat_potential.related_to = ottocrat_account.accountid
			LEFT JOIN ottocrat_contactdetails ON ottocrat_potential.contact_id = ottocrat_contactdetails.contactid
			LEFT JOIN ottocrat_users ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_opportunities method ...");
		return $return_value;
	}

	/**	function used to get the list of tickets which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'product_id','readwrite') == '0') {
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
		$query = "SELECT  case when (ottocrat_users.user_name not like \"\") then $userNameSql else ottocrat_groups.groupname end as user_name, ottocrat_users.id,
			ottocrat_products.productid, ottocrat_products.productname,
			ottocrat_troubletickets.ticketid,
			ottocrat_troubletickets.parent_id, ottocrat_troubletickets.title,
			ottocrat_troubletickets.status, ottocrat_troubletickets.priority,
			ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
			ottocrat_crmentity.modifiedtime, ottocrat_troubletickets.ticket_no
			FROM ottocrat_troubletickets
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_troubletickets.ticketid
			LEFT JOIN ottocrat_products
				ON ottocrat_products.productid = ottocrat_troubletickets.product_id
			LEFT JOIN ottocrat_ticketcf ON ottocrat_troubletickets.ticketid = ottocrat_ticketcf.ticketid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0
			AND ottocrat_products.productid = ".$id;

		$log->debug("Exiting get_tickets method ...");

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}

	/**	function used to get the list of activities which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_activities($id)
	{
		global $log, $singlepane_view;
		$log->debug("Entering get_activities(".$id.") method ...");
		global $app_strings;

		require_once('modules/Calendar/Activity.php');

        	//if($this->column_fields['contact_id']!=0 && $this->column_fields['contact_id']!='')
        	$focus = new Activity();

		$button = '';

		if($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&return_id='.$id;


		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_contactdetails.lastname,
			ottocrat_contactdetails.firstname,
			ottocrat_contactdetails.contactid,
			ottocrat_activity.*,
			ottocrat_seactivityrel.crmid as parent_id,
			ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid,
			ottocrat_crmentity.modifiedtime,
			$userNameSql,
			ottocrat_recurringevents.recurringtype
			FROM ottocrat_activity
			INNER JOIN ottocrat_seactivityrel
				ON ottocrat_seactivityrel.activityid = ottocrat_activity.activityid
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid=ottocrat_activity.activityid
			LEFT JOIN ottocrat_cntactivityrel
				ON ottocrat_cntactivityrel.activityid = ottocrat_activity.activityid
			LEFT JOIN ottocrat_contactdetails
				ON ottocrat_contactdetails.contactid = ottocrat_cntactivityrel.contactid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			LEFT OUTER JOIN ottocrat_recurringevents
				ON ottocrat_recurringevents.activityid = ottocrat_activity.activityid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			WHERE ottocrat_seactivityrel.crmid=".$id."
			AND (activitytype != 'Emails')";
		$log->debug("Exiting get_activities method ...");
		return GetRelatedList('Products','Calendar',$focus,$query,$button,$returnset);
	}

	/**	function used to get the list of quotes which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_crmentity.*,
			ottocrat_quotes.*,
			ottocrat_potential.potentialname,
			ottocrat_account.accountname,
			ottocrat_inventoryproductrel.productid,
			case when (ottocrat_users.user_name not like '') then $userNameSql
				else ottocrat_groups.groupname end as user_name
			FROM ottocrat_quotes
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_quotes.quoteid
			INNER JOIN ottocrat_inventoryproductrel
				ON ottocrat_inventoryproductrel.id = ottocrat_quotes.quoteid
			LEFT OUTER JOIN ottocrat_account
				ON ottocrat_account.accountid = ottocrat_quotes.accountid
			LEFT OUTER JOIN ottocrat_potential
				ON ottocrat_potential.potentialid = ottocrat_quotes.potentialid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
            LEFT JOIN ottocrat_quotescf
                ON ottocrat_quotescf.quoteid = ottocrat_quotes.quoteid
			LEFT JOIN ottocrat_quotesbillads
				ON ottocrat_quotesbillads.quotebilladdressid = ottocrat_quotes.quoteid
			LEFT JOIN ottocrat_quotesshipads
				ON ottocrat_quotesshipads.quoteshipaddressid = ottocrat_quotes.quoteid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0
			AND ottocrat_inventoryproductrel.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_quotes method ...");
		return $return_value;
	}

	/**	function used to get the list of purchase orders which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_crmentity.*,
			ottocrat_purchaseorder.*,
			ottocrat_products.productname,
			ottocrat_inventoryproductrel.productid,
			case when (ottocrat_users.user_name not like '') then $userNameSql
				else ottocrat_groups.groupname end as user_name
			FROM ottocrat_purchaseorder
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_purchaseorder.purchaseorderid
			INNER JOIN ottocrat_inventoryproductrel
				ON ottocrat_inventoryproductrel.id = ottocrat_purchaseorder.purchaseorderid
			INNER JOIN ottocrat_products
				ON ottocrat_products.productid = ottocrat_inventoryproductrel.productid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
            LEFT JOIN ottocrat_purchaseordercf
                ON ottocrat_purchaseordercf.purchaseorderid = ottocrat_purchaseorder.purchaseorderid
			LEFT JOIN ottocrat_pobillads
				ON ottocrat_pobillads.pobilladdressid = ottocrat_purchaseorder.purchaseorderid
			LEFT JOIN ottocrat_poshipads
				ON ottocrat_poshipads.poshipaddressid = ottocrat_purchaseorder.purchaseorderid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0
			AND ottocrat_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_purchase_orders method ...");
		return $return_value;
	}

	/**	function used to get the list of sales orders which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_crmentity.*,
			ottocrat_salesorder.*,
			ottocrat_products.productname AS productname,
			ottocrat_account.accountname,
			case when (ottocrat_users.user_name not like '') then $userNameSql
				else ottocrat_groups.groupname end as user_name
			FROM ottocrat_salesorder
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_salesorder.salesorderid
			INNER JOIN ottocrat_inventoryproductrel
				ON ottocrat_inventoryproductrel.id = ottocrat_salesorder.salesorderid
			INNER JOIN ottocrat_products
				ON ottocrat_products.productid = ottocrat_inventoryproductrel.productid
			LEFT OUTER JOIN ottocrat_account
				ON ottocrat_account.accountid = ottocrat_salesorder.accountid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
            LEFT JOIN ottocrat_salesordercf
                ON ottocrat_salesordercf.salesorderid = ottocrat_salesorder.salesorderid
            LEFT JOIN ottocrat_invoice_recurring_info
                ON ottocrat_invoice_recurring_info.start_period = ottocrat_salesorder.salesorderid
			LEFT JOIN ottocrat_sobillads
				ON ottocrat_sobillads.sobilladdressid = ottocrat_salesorder.salesorderid
			LEFT JOIN ottocrat_soshipads
				ON ottocrat_soshipads.soshipaddressid = ottocrat_salesorder.salesorderid
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id = ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0
			AND ottocrat_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_salesorder method ...");
		return $return_value;
	}

	/**	function used to get the list of invoices which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'ottocrat_users.first_name', 'last_name' => 'ottocrat_users.last_name'), 'Users');
		$query = "SELECT ottocrat_crmentity.*,
			ottocrat_invoice.*,
			ottocrat_inventoryproductrel.quantity,
			ottocrat_account.accountname,
			case when (ottocrat_users.user_name not like '') then $userNameSql
				else ottocrat_groups.groupname end as user_name
			FROM ottocrat_invoice
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_invoice.invoiceid
			LEFT OUTER JOIN ottocrat_account
				ON ottocrat_account.accountid = ottocrat_invoice.accountid
			INNER JOIN ottocrat_inventoryproductrel
				ON ottocrat_inventoryproductrel.id = ottocrat_invoice.invoiceid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
            LEFT JOIN ottocrat_invoicecf
                ON ottocrat_invoicecf.invoiceid = ottocrat_invoice.invoiceid
			LEFT JOIN ottocrat_invoicebillads
				ON ottocrat_invoicebillads.invoicebilladdressid = ottocrat_invoice.invoiceid
			LEFT JOIN ottocrat_invoiceshipads
				ON ottocrat_invoiceshipads.invoiceshipaddressid = ottocrat_invoice.invoiceid
			LEFT JOIN ottocrat_users
				ON  ottocrat_users.id=ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0
			AND ottocrat_inventoryproductrel.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_invoices method ...");
		return $return_value;
	}

	/**	function used to get the list of pricebooks which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_product_pricebooks($id, $cur_tab_id, $rel_tab_id, $actions=false)
	{
		global $log,$singlepane_view,$currentModule;
		$log->debug("Entering get_product_pricebooks(".$id.") method ...");

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
		require_once("modules/$related_module/$related_module.php");
		$focus = new $related_module();
		$singular_modname = vtlib_toSingular($related_module);

		$button = '';
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes' && isPermitted($currentModule,'EditView',$id) == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_TO'). " ". getTranslatedString($related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"AddProductToPriceBooks\";this.form.module.value=\"$currentModule\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_TO'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		if($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&return_id='.$id;


		$query = "SELECT ottocrat_crmentity.crmid,
			ottocrat_pricebook.*,
			ottocrat_pricebookproductrel.productid as prodid
			FROM ottocrat_pricebook
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_pricebook.pricebookid
			INNER JOIN ottocrat_pricebookproductrel
				ON ottocrat_pricebookproductrel.pricebookid = ottocrat_pricebook.pricebookid
			INNER JOIN ottocrat_pricebookcf
				ON ottocrat_pricebookcf.pricebookid = ottocrat_pricebook.pricebookid
			WHERE ottocrat_crmentity.deleted = 0
			AND ottocrat_pricebookproductrel.productid = ".$id;
		$log->debug("Exiting get_product_pricebooks method ...");

		$return_value = GetRelatedList($currentModule, $related_module, $focus, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	/**	function used to get the number of vendors which are related to the product
	 *	@param int $id - product id
	 *	@return int number of rows - return the number of products which do not have relationship with vendor
	 */
	function product_novendor()
	{
		global $log;
		$log->debug("Entering product_novendor() method ...");
		$query = "SELECT ottocrat_products.productname, ottocrat_crmentity.deleted
			FROM ottocrat_products
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_products.productid
			WHERE ottocrat_crmentity.deleted = 0
			AND ottocrat_products.vendor_id is NULL";
		$result=$this->db->pquery($query, array());
		$log->debug("Exiting product_novendor method ...");
		return $this->db->num_rows($result);
	}

	/**
	* Function to get Product's related Products
	* @param  integer   $id      - productid
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

		if($actions && $this->ismember_check() === 0) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('".Ottocrat_Request:: encryptLink("index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab")."','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />".
					"<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT ottocrat_products.productid, ottocrat_products.productname,
			ottocrat_products.productcode, ottocrat_products.commissionrate,
			ottocrat_products.qty_per_unit, ottocrat_products.unit_price,
			ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid
			FROM ottocrat_products
			INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_products.productid
			INNER JOIN ottocrat_productcf
				ON ottocrat_products.productid = ottocrat_productcf.productid
			LEFT JOIN ottocrat_seproductsrel ON ottocrat_seproductsrel.crmid = ottocrat_products.productid AND ottocrat_seproductsrel.setype='Products'
			LEFT JOIN ottocrat_users
				ON ottocrat_users.id=ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_groups
				ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_seproductsrel.productid = $id ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/**
	* Function to get Product's related Products
	* @param  integer   $id      - productid
	* returns related Products record in array format
	*/
	function get_parent_products($id)
	{
		global $log, $singlepane_view;
                $log->debug("Entering get_products(".$id.") method ...");

		global $app_strings;

		$focus = new Products();

		$button = '';

		if(isPermitted("Products",1,"") == 'yes')
		{
			$button .= '<input title="'.$app_strings['LBL_NEW_PRODUCT'].'" accessyKey="F" class="button" onclick="this.form.action.value=\'EditView\';this.form.module.value=\'Products\';this.form.return_module.value=\'Products\';this.form.return_action.value=\'DetailView\'" type="submit" name="button" value="'.$app_strings['LBL_NEW_PRODUCT'].'">&nbsp;';
		}
		if($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&is_parent=1&return_id='.$id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&is_parent=1&return_id='.$id;

		$query = "SELECT ottocrat_products.productid, ottocrat_products.productname,
			ottocrat_products.productcode, ottocrat_products.commissionrate,
			ottocrat_products.qty_per_unit, ottocrat_products.unit_price,
			ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid
			FROM ottocrat_products
			INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_products.productid
			INNER JOIN ottocrat_seproductsrel ON ottocrat_seproductsrel.productid = ottocrat_products.productid AND ottocrat_seproductsrel.setype='Products'
			INNER JOIN ottocrat_productcf ON ottocrat_products.productid = ottocrat_productcf.productid

			WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_seproductsrel.crmid = $id ";

		$log->debug("Exiting get_products method ...");
		return GetRelatedList('Products','Products',$focus,$query,$button,$returnset);
	}

	/**	function used to get the export query for product
	 *	@param reference $where - reference of the where variable which will be added with the query
	 *	@return string $query - return the query which will give the list of products to export
	 */
	function create_export_query($where)
	{
		global $log, $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Products", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list FROM ".$this->table_name ."
			INNER JOIN ottocrat_crmentity
				ON ottocrat_crmentity.crmid = ottocrat_products.productid
			LEFT JOIN ottocrat_productcf
				ON ottocrat_products.productid = ottocrat_productcf.productid
			LEFT JOIN ottocrat_vendor
				ON ottocrat_vendor.vendorid = ottocrat_products.vendor_id";

		$query .= " LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";
		$query .= " LEFT JOIN ottocrat_users ON ottocrat_crmentity.smownerid = ottocrat_users.id AND ottocrat_users.status='Active'";
		$query .= $this->getNonAdminAccessControlQuery('Products',$current_user);
		$where_auto = " ottocrat_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to check if the product is parent of any other product
	*/
	function isparent_check(){
		global $adb;
		$isparent_query = $adb->pquery(getListQuery("Products")." AND (ottocrat_products.productid IN (SELECT productid from ottocrat_seproductsrel WHERE ottocrat_seproductsrel.productid = ? AND ottocrat_seproductsrel.setype='Products'))",array($this->id));
		$isparent = $adb->num_rows($isparent_query);
		return $isparent;
	}

	/** Function to check if the product is member of other product
	*/
	function ismember_check(){
		global $adb;
		$ismember_query = $adb->pquery(getListQuery("Products")." AND (ottocrat_products.productid IN (SELECT crmid from ottocrat_seproductsrel WHERE ottocrat_seproductsrel.crmid = ? AND ottocrat_seproductsrel.setype='Products'))",array($this->id));
		$ismember = $adb->num_rows($ismember_query);
		return $ismember;
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

		$rel_table_arr = Array("HelpDesk"=>"ottocrat_troubletickets","Products"=>"ottocrat_seproductsrel","Attachments"=>"ottocrat_seattachmentsrel",
				"Quotes"=>"ottocrat_inventoryproductrel","PurchaseOrder"=>"ottocrat_inventoryproductrel","SalesOrder"=>"ottocrat_inventoryproductrel",
				"Invoice"=>"ottocrat_inventoryproductrel","PriceBooks"=>"ottocrat_pricebookproductrel","Leads"=>"ottocrat_seproductsrel",
				"Accounts"=>"ottocrat_seproductsrel","Potentials"=>"ottocrat_seproductsrel","Contacts"=>"ottocrat_seproductsrel",
				"Documents"=>"ottocrat_senotesrel",'Assets'=>'ottocrat_assets',);

		$tbl_field_arr = Array("ottocrat_troubletickets"=>"ticketid","ottocrat_seproductsrel"=>"crmid","ottocrat_seattachmentsrel"=>"attachmentsid",
				"ottocrat_inventoryproductrel"=>"id","ottocrat_pricebookproductrel"=>"pricebookid","ottocrat_seproductsrel"=>"crmid",
				"ottocrat_senotesrel"=>"notesid",'ottocrat_assets'=>'assetsid');

		$entity_tbl_field_arr = Array("ottocrat_troubletickets"=>"product_id","ottocrat_seproductsrel"=>"crmid","ottocrat_seattachmentsrel"=>"crmid",
				"ottocrat_inventoryproductrel"=>"productid","ottocrat_pricebookproductrel"=>"productid","ottocrat_seproductsrel"=>"productid",
				"ottocrat_senotesrel"=>"crmid",'ottocrat_assets'=>'product');

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
		$log->debug("Exiting transferRelatedRecords...");
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryplanner) {
		global $current_user;
		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("ottocrat_crmentityProducts",array("ottocrat_groupsProducts","ottocrat_usersProducts","ottocrat_lastModifiedByProducts"));
		$matrix->setDependency("ottocrat_products",array("innerProduct","ottocrat_crmentityProducts","ottocrat_productcf","ottocrat_vendorRelProducts"));
		//query planner Support  added
		if (!$queryplanner->requireTable('ottocrat_products', $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module,$secmodule,"ottocrat_products","productid", $queryplanner);
		if ($queryplanner->requireTable("innerProduct")){
		    $query .= " LEFT JOIN (
				    SELECT ottocrat_products.productid,
						    (CASE WHEN (ottocrat_products.currency_id = 1 ) THEN ottocrat_products.unit_price
							    ELSE (ottocrat_products.unit_price / ottocrat_currency_info.conversion_rate) END
						    ) AS actual_unit_price
				    FROM ottocrat_products
				    LEFT JOIN ottocrat_currency_info ON ottocrat_products.currency_id = ottocrat_currency_info.id
				    LEFT JOIN ottocrat_productcurrencyrel ON ottocrat_products.productid = ottocrat_productcurrencyrel.productid
				    AND ottocrat_productcurrencyrel.currencyid = ". $current_user->currency_id . "
			    ) AS innerProduct ON innerProduct.productid = ottocrat_products.productid";
		}
		if ($queryplanner->requireTable("ottocrat_crmentityProducts")){
		    $query .= " left join ottocrat_crmentity as ottocrat_crmentityProducts on ottocrat_crmentityProducts.crmid=ottocrat_products.productid and ottocrat_crmentityProducts.deleted=0";
		}
		if ($queryplanner->requireTable("ottocrat_productcf")){
		    $query .= " left join ottocrat_productcf on ottocrat_products.productid = ottocrat_productcf.productid";
		}
    		if ($queryplanner->requireTable("ottocrat_groupsProducts")){
		    $query .= " left join ottocrat_groups as ottocrat_groupsProducts on ottocrat_groupsProducts.groupid = ottocrat_crmentityProducts.smownerid";
		}
		if ($queryplanner->requireTable("ottocrat_usersProducts")){
		    $query .= " left join ottocrat_users as ottocrat_usersProducts on ottocrat_usersProducts.id = ottocrat_crmentityProducts.smownerid";
		}
		if ($queryplanner->requireTable("ottocrat_vendorRelProducts")){
		    $query .= " left join ottocrat_vendor as ottocrat_vendorRelProducts on ottocrat_vendorRelProducts.vendorid = ottocrat_products.vendor_id";
		}
		if ($queryplanner->requireTable("ottocrat_lastModifiedByProducts")){
		    $query .= " left join ottocrat_users as ottocrat_lastModifiedByProducts on ottocrat_lastModifiedByProducts.id = ottocrat_crmentityProducts.modifiedby ";
		}
        if ($queryplanner->requireTable("ottocrat_createdbyProducts")){
			$query .= " left join ottocrat_users as ottocrat_createdbyProducts on ottocrat_createdbyProducts.id = ottocrat_crmentityProducts.smcreatorid ";
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
			"HelpDesk" => array("ottocrat_troubletickets"=>array("product_id","ticketid"),"ottocrat_products"=>"productid"),
			"Quotes" => array("ottocrat_inventoryproductrel"=>array("productid","id"),"ottocrat_products"=>"productid"),
			"PurchaseOrder" => array("ottocrat_inventoryproductrel"=>array("productid","id"),"ottocrat_products"=>"productid"),
			"SalesOrder" => array("ottocrat_inventoryproductrel"=>array("productid","id"),"ottocrat_products"=>"productid"),
			"Invoice" => array("ottocrat_inventoryproductrel"=>array("productid","id"),"ottocrat_products"=>"productid"),
			"Leads" => array("ottocrat_seproductsrel"=>array("productid","crmid"),"ottocrat_products"=>"productid"),
			"Accounts" => array("ottocrat_seproductsrel"=>array("productid","crmid"),"ottocrat_products"=>"productid"),
			"Contacts" => array("ottocrat_seproductsrel"=>array("productid","crmid"),"ottocrat_products"=>"productid"),
			"Potentials" => array("ottocrat_seproductsrel"=>array("productid","crmid"),"ottocrat_products"=>"productid"),
			"Products" => array("ottocrat_products"=>array("productid","product_id"),"ottocrat_products"=>"productid"),
			"PriceBooks" => array("ottocrat_pricebookproductrel"=>array("productid","pricebookid"),"ottocrat_products"=>"productid"),
			"Documents" => array("ottocrat_senotesrel"=>array("crmid","notesid"),"ottocrat_products"=>"productid"),
		);
		return $rel_tables[$secmodule];
	}

	function deleteProduct2ProductRelation($record,$return_id,$is_parent){
		global $adb;
		if($is_parent==0){
			$sql = "delete from ottocrat_seproductsrel WHERE crmid = ? AND productid = ?";
			$adb->pquery($sql, array($record,$return_id));
		} else {
			$sql = "delete from ottocrat_seproductsrel WHERE crmid = ? AND productid = ?";
			$adb->pquery($sql, array($return_id,$record));
		}
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;
		//Backup Campaigns-Product Relation
		$cmp_q = 'SELECT campaignid FROM ottocrat_campaign WHERE product_id = ?';
		$cmp_res = $this->db->pquery($cmp_q, array($id));
		if ($this->db->num_rows($cmp_res) > 0) {
			$cmp_ids_list = array();
			for($k=0;$k < $this->db->num_rows($cmp_res);$k++)
			{
				$cmp_ids_list[] = $this->db->query_result($cmp_res,$k,"campaignid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'ottocrat_campaign', 'product_id', 'campaignid', implode(",", $cmp_ids_list));
			$this->db->pquery('INSERT INTO ottocrat_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//we have to update the product_id as null for the campaigns which are related to this product
		$this->db->pquery('UPDATE ottocrat_campaign SET product_id=0 WHERE product_id = ?', array($id));

		$this->db->pquery('DELETE from ottocrat_seproductsrel WHERE productid=? or crmid=?',array($id,$id));

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Calendar') {
			$sql = 'DELETE FROM ottocrat_seactivityrel WHERE crmid = ? AND activityid = ?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Leads' || $return_module == 'Contacts' || $return_module == 'Potentials') {
			$sql = 'DELETE FROM ottocrat_seproductsrel WHERE productid = ? AND crmid = ?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Vendors') {
			$sql = 'UPDATE ottocrat_products SET vendor_id = ? WHERE productid = ?';
			$this->db->pquery($sql, array(null, $id));
		} elseif($return_module == 'Accounts') {
			$sql = 'DELETE FROM ottocrat_seproductsrel WHERE productid = ? AND (crmid = ? OR crmid IN (SELECT contactid FROM ottocrat_contactdetails WHERE accountid=?))';
			$param = array($id, $return_id,$return_id);
			$this->db->pquery($sql, $param);
		} else {
			$sql = 'DELETE FROM ottocrat_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Leads' || $with_module == 'Accounts' ||
					$with_module == 'Contacts' || $with_module == 'Potentials' || $with_module == 'Products'){
				$query = $adb->pquery("SELECT * from ottocrat_seproductsrel WHERE crmid=? and productid=?",array($crmid, $with_crmid));
				if($adb->num_rows($query)==0){
					$adb->pquery("insert into ottocrat_seproductsrel values (?,?,?)", array($with_crmid, $crmid, $with_module));
				}
			}
			else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

}
?>
