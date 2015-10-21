<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Products_Record_Model extends Ottocrat_Record_Model {

	/**
	 * Function to get Taxes Url
	 * @return <String> Url
	 */
	function getTaxesURL() {
		return Ottocrat_Request:: encryptLink('index.php?module=Inventory&action=GetTaxes&record='. $this->getId());
	}

	/**
	 * Function to get available taxes for this record
	 * @return <Array> List of available taxes
	 */
	function getTaxes() {
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT * FROM ottocrat_producttaxrel
					INNER JOIN ottocrat_inventorytaxinfo ON ottocrat_inventorytaxinfo.taxid = ottocrat_producttaxrel.taxid
					INNER JOIN ottocrat_crmentity ON ottocrat_producttaxrel.productid = ottocrat_crmentity.crmid AND ottocrat_crmentity.deleted = 0
					WHERE ottocrat_producttaxrel.productid = ? AND ottocrat_inventorytaxinfo.deleted = 0', array($this->getId()));
		$taxes = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$taxName = $db->query_result($result, $i, 'taxname');
			$tabLabel = $db->query_result($result, $i, 'taxlabel');
			$taxPercentage = $db->query_result($result, $i, 'taxpercentage');
			$taxes[$taxName] = array('percentage'=>$taxPercentage, 'label' => $tabLabel);
		}
		return $taxes;
	}
        
    /**
	 * Function to get values of more currencies listprice
	 * @return <Array> of listprice values
	 */
	static function getListPriceValues($id) {
    	$db = PearDatabase::getInstance();            
        $listPrice = $db->pquery('SELECT * FROM ottocrat_productcurrencyrel WHERE productid	= ?', array($id)); 
        $listpriceValues = array();
        for($i=0; $i<$db->num_rows($listPrice); $i++) {
        	$listpriceValues[$db->query_result($listPrice, $i, 'currencyid')] = $db->query_result($listPrice, $i, 'actual_price');
        }
        return $listpriceValues;
	}

	/**
	 * Function to get subproducts for this record
	 * @return <Array> of subproducts
	 */
	function getSubProducts() {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT ottocrat_products.productid FROM ottocrat_products
			INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_products.productid
			LEFT JOIN ottocrat_seproductsrel ON ottocrat_seproductsrel.crmid = ottocrat_products.productid AND ottocrat_products.discontinued = 1 
                        AND ottocrat_seproductsrel.setype='Products'
			LEFT JOIN ottocrat_users ON ottocrat_users.id=ottocrat_crmentity.smownerid
			LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid
			WHERE ottocrat_crmentity.deleted = 0 AND ottocrat_seproductsrel.productid = ? ", array($this->getId()));

		$subProductList = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$subProductId = $db->query_result($result, $i, 'productid');
			$subProductList[] = Ottocrat_Record_Model::getInstanceById($subProductId, 'Products');
		}

		return $subProductList;
	}

	/**
	 * Function to get Url to Create a new Quote from this record
	 * @return <String> Url to Create new Quote
	 */
	function getCreateQuoteUrl() {
		$quotesModuleModel = Ottocrat_Module_Model::getInstance('Quotes');

		return Ottocrat_Request:: encryptLink("index.php?module=".$quotesModuleModel->getName()."&view=".$quotesModuleModel->getEditViewName()."&product_id=".$this->getId()."&sourceModule=".$this->getModuleName()."&sourceRecord=".$this->getId()."&relationOperation=true");
	}

	/**
	 * Function to get Url to Create a new Invoice from this record
	 * @return <String> Url to Create new Invoice
	 */
	function getCreateInvoiceUrl() {
		$invoiceModuleModel = Ottocrat_Module_Model::getInstance('Invoice');

		return Ottocrat_Request:: encryptLink("index.php?module=".$invoiceModuleModel->getName()."&view=".$invoiceModuleModel->getEditViewName()."&product_id=".$this->getId()."&sourceModule=".$this->getModuleName()."&sourceRecord=".$this->getId()."&relationOperation=true");
	}

	/**
	 * Function to get Url to Create a new PurchaseOrder from this record
	 * @return <String> Url to Create new PurchaseOrder
	 */
	function getCreatePurchaseOrderUrl() {
		$purchaseOrderModuleModel = Ottocrat_Module_Model::getInstance('PurchaseOrder');

		return Ottocrat_Request:: encryptLink("index.php?module=".$purchaseOrderModuleModel->getName()."&view=".$purchaseOrderModuleModel->getEditViewName()."&product_id=".$this->getId()."&sourceModule=".$this->getModuleName()."&sourceRecord=".$this->getId()."&relationOperation=true");
	}

	/**
	 * Function to get Url to Create a new SalesOrder from this record
	 * @return <String> Url to Create new SalesOrder
	 */
	function getCreateSalesOrderUrl() {
		$salesOrderModuleModel = Ottocrat_Module_Model::getInstance('SalesOrder');

		return Ottocrat_Request:: encryptLink("index.php?module=".$salesOrderModuleModel->getName()."&view=".$salesOrderModuleModel->getEditViewName()."&product_id=".$this->getId()."&sourceModule=".$this->getModuleName()."&sourceRecord=".$this->getId()."&relationOperation=true");
	}

	/**
	 * Function get details of taxes for this record
	 * Function calls from Edit/Create view of Inventory Records
	 * @param <Object> $focus
	 * @return <Array> List of individual taxes
	 */
	function getDetailsForInventoryModule($focus) {
		$productId = $this->getId();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$productDetails = getAssociatedProducts($this->getModuleName(), $focus, $productId);

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$convertedPriceDetails = $this->getModule()->getPricesForProducts($currentUserModel->get('currency_id'), array($productId));
		$productDetails[1]['listPrice1'] = number_format($convertedPriceDetails[$productId], $currentUserModel->get('no_of_currency_decimals'),'.','');

		$totalAfterDiscount = $productDetails[1]['totalAfterDiscount1'];
		$productTaxes = $productDetails[1]['taxes'];
		if (!empty ($productDetails)) {
			$taxCount = count($productTaxes);
			$taxTotal = '0';

			for($i=0; $i<$taxCount; $i++) {
				$taxValue = $productTaxes[$i]['percentage'];

				$taxAmount = $totalAfterDiscount * $taxValue / 100;
				$taxTotal = $taxTotal + $taxAmount;

				$productDetails[1]['taxes'][$i]['amount'] = $taxAmount;
				$productDetails[1]['taxTotal1'] = $taxTotal;
			}
			$netPrice = $totalAfterDiscount + $taxTotal;
			$productDetails[1]['netPrice1'] = $netPrice;
			$productDetails[1]['final_details']['hdnSubTotal'] = $netPrice;
			$productDetails[1]['final_details']['grandTotal'] = $netPrice;
		}

		for ($i=1; $i<=count($productDetails); $i++) {
			$productId = $productDetails[$i]['hdnProductId'.$i];
			$productPrices = $this->getModule()->getPricesForProducts($currentUser->get('currency_id'), array($productId), $this->getModuleName());
			$productDetails[$i]['listPrice'.$i] = number_format($productPrices[$productId], $currentUser->get('no_of_currency_decimals'),'.','');
		}
		return $productDetails;
	}

	/**
	 * Function to get Tax Class Details for this record(Product)
	 * @return <Array> List of Taxes
	 */
	public function getTaxClassDetails() {
		$taxClassDetails = $this->get('taxClassDetails');
		if (!empty($taxClassDetails)) {
			return $taxClassDetails;
		}

		$record = $this->getId();
		if (empty ($record)) {
			return $this->getAllTaxes();
		}

		$taxClassDetails = getTaxDetailsForProduct($record, 'available_associated');
		$noOfTaxes = count($taxClassDetails);

		for($i=0; $i<$noOfTaxes; $i++) {
			$taxValue = getProductTaxPercentage($taxClassDetails[$i]['taxname'], $this->getId());
			$taxClassDetails[$i]['percentage'] = $taxValue;
			$taxClassDetails[$i]['check_name'] = $taxClassDetails[$i]['taxname'].'_check';
			$taxClassDetails[$i]['check_value'] = 1;
			//if the tax is not associated with the product then we should get the default value and unchecked
			if($taxValue == '') {
				$taxClassDetails[$i]['check_value'] = 0;
				$taxClassDetails[$i]['percentage'] = getTaxPercentage($taxClassDetails[$i]['taxname']);
			}
		}

		$this->set('taxClassDetails', $taxClassDetails);
		return $taxClassDetails;
	}

	/**
	 * Function to get all taxes
	 * @return <Array> List of taxes
	 */
	public function getAllTaxes() {
		$allTaxesList = $this->get('alltaxes');
		if (!empty($allTaxesList)) {
			return $allTaxesList;
		}

		$allTaxesList = getAllTaxes('available');
		$noOfTaxes = count($allTaxesList);

		for($i=0; $i<$noOfTaxes; $i++) {
			$allTaxesList[$i]['check_name'] = $allTaxesList[$i]['taxname'].'_check';
			$allTaxesList[$i]['check_value'] = 0;
		}

		$this->set('alltaxes', $allTaxesList);
		return $allTaxesList;
	}

	/**
	 * Function to get price details
	 * @return <Array> List of prices
	 */
	public function getPriceDetails() {
		$priceDetails = $this->get('priceDetails');
		if (!empty($priceDetails)) {
			return $priceDetails;
		}
		$priceDetails = getPriceDetailsForProduct($this->getId(), $this->get('unit_price'), 'available', $this->getModuleName());
		$this->set('priceDetails', $priceDetails);
		return $priceDetails;
	}

	/**
	 * Function to get base currency details
	 * @return <Array>
	 */
	public function getBaseCurrencyDetails() {
		$baseCurrencyDetails = $this->get('baseCurrencyDetails');
		if (!empty($baseCurrencyDetails)) {
			return $baseCurrencyDetails;
		}

		$recordId = $this->getId();
		if (!empty($recordId)) {
			$baseCurrency = getProductBaseCurrency($recordId, $this->getModuleName());
		} else {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$baseCurrency = fetchCurrency($currentUserModel->getId());
		}
		$baseCurrencyDetails = array('currencyid' => $baseCurrency);

		$baseCurrencySymbolDetails = getCurrencySymbolandCRate($baseCurrency);
		$baseCurrencyDetails = array_merge($baseCurrencyDetails, $baseCurrencySymbolDetails);
		$this->set('baseCurrencyDetails', $baseCurrencyDetails);

		return $baseCurrencyDetails;
	}

	/**
	 * Function to get Image Details
	 * @return <array> Image Details List
	 */
	public function getImageDetails() {
		$db = PearDatabase::getInstance();
		$imageDetails = array();
		$recordId = $this->getId();

		if ($recordId) {
			$sql = "SELECT ottocrat_attachments.*, ottocrat_crmentity.setype FROM ottocrat_attachments
						INNER JOIN ottocrat_seattachmentsrel ON ottocrat_seattachmentsrel.attachmentsid = ottocrat_attachments.attachmentsid
						INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_attachments.attachmentsid
						WHERE ottocrat_crmentity.setype = 'Products Image' AND ottocrat_seattachmentsrel.crmid = ?";

			$result = $db->pquery($sql, array($recordId));
			$count = $db->num_rows($result);

			for($i=0; $i<$count; $i++) {
				$imageIdsList[] = $db->query_result($result, $i, 'attachmentsid');
				$imagePathList[] = $db->query_result($result, $i, 'path');
				$imageName = $db->query_result($result, $i, 'name');

				//decode_html - added to handle UTF-8 characters in file names
				$imageOriginalNamesList[] = decode_html($imageName);

				//urlencode - added to handle special characters like #, %, etc.,
				$imageNamesList[] = $imageName;
			}

			if(is_array($imageOriginalNamesList)) {
				$countOfImages = count($imageOriginalNamesList);
				for($j=0; $j<$countOfImages; $j++) {
					$imageDetails[] = array(
							'id' => $imageIdsList[$j],
							'orgname' => $imageOriginalNamesList[$j],
							'path' => $imagePathList[$j].$imageIdsList[$j],
							'name' => $imageNamesList[$j]
					);
				}
			}
		}
		return $imageDetails;
	}
	
	/**
	 * Static Function to get the list of records matching the search key
	 * @param <String> $searchKey
	 * @return <Array> - List of Ottocrat_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $module=false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT label, crmid, setype, createdtime FROM ottocrat_crmentity WHERE label LIKE ? AND ottocrat_crmentity.deleted = 0';
		$params = array("%$searchKey%");

		if($module !== false) {
			$query .= ' AND setype = ?';
			if($module == 'Products'){
				$query = 'SELECT label, crmid, setype, createdtime FROM ottocrat_crmentity INNER JOIN ottocrat_products ON 
							ottocrat_products.productid = ottocrat_crmentity.crmid WHERE label LIKE ? AND ottocrat_crmentity.deleted = 0 
							AND ottocrat_products.discontinued = 1 AND setype = ?';
			}else if($module == 'Services'){
				$query = 'SELECT label, crmid, setype, createdtime FROM ottocrat_crmentity INNER JOIN ottocrat_service ON 
							ottocrat_service.serviceid = ottocrat_crmentity.crmid WHERE label LIKE ? AND ottocrat_crmentity.deleted = 0 
							AND ottocrat_service.discontinued = 1 AND setype = ?';
			}
			$params[] = $module;
		}
		//Remove the ordering for now to improve the speed
		//$query .= ' ORDER BY createdtime DESC';

		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$moduleModels = $matchingRecords = $leadIdsList = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);

		for($i=0, $recordsCount = 0; $i<$noOfRows && $recordsCount<100; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
				$row['id'] = $row['crmid'];
				$moduleName = $row['setype'];
				if(!array_key_exists($moduleName, $moduleModels)) {
					$moduleModels[$moduleName] = Ottocrat_Module_Model::getInstance($moduleName);
				}
				$moduleModel = $moduleModels[$moduleName];
				$modelClassName = Ottocrat_Loader::getComponentClassName('Model', 'Record', $moduleName);
				$recordInstance = new $modelClassName();
				$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
				$recordsCount++;
			}
		}
		return $matchingRecords;
	}
	
	/**
	 * Function to get acive status of record
	 */
	public function getActiveStatusOfRecord(){
		$activeStatus = $this->get('discontinued');
		if($activeStatus){
			return $activeStatus;
		}
		$recordId = $this->getId();
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT discontinued FROM ottocrat_products WHERE productid = ?',array($recordId));
		$activeStatus = $db->query_result($result, 'discontinued');
		return $activeStatus;
	}
	
	/**
	 * Function updates ListPrice for Product/Service-PriceBook relation
	 * @param <Integer> $relatedRecordId - PriceBook Id
	 * @param <Integer> $price - listprice
	 * @param <Integer> $currencyId - currencyId
	 */
	function updateListPrice($relatedRecordId, $price, $currencyId) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT * FROM ottocrat_pricebookproductrel WHERE pricebookid = ? AND productid = ?',
				array($relatedRecordId, $this->getId()));
		if($db->num_rows($result)) {
			 $db->pquery('UPDATE ottocrat_pricebookproductrel SET listprice = ? WHERE pricebookid = ? AND productid = ?',
					 array($price, $relatedRecordId, $this->getId()));
		} else {
			$db->pquery('INSERT INTO ottocrat_pricebookproductrel (pricebookid,productid,listprice,usedcurrency) values(?,?,?,?)',
					array($relatedRecordId, $this->getId(), $price, $currencyId));
		}
	}
}
