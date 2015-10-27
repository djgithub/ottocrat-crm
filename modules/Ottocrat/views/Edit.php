<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

Class Ottocrat_Edit_View extends Ottocrat_Index_View {
    protected $record = false;
	function __construct() {
		parent::__construct();
	}
	
	public function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'EditView', $record);

		if(!$recordPermission) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function process(Ottocrat_Request $request) {

		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();

		$username=$_SESSION['username'];

		$add_contact_flg	=	0;
		$add_lead_flg		=	0;
		$add_opp_flg		=	0;
		$add_org_flg		=	0;
		$add_file_flg 		= 0;

		if($username!='') {
			global $adb, $WP_USER,$WP_DB,$WP_PASSWORD,$OT_USER,$OT_DB,$OT_PASSWORD;
			$adb->disconnect();
			$adb->resetSettings('mysqli', 'localhost', $WP_DB, $WP_USER, $WP_PASSWORD);
			$Query = "SELECT p.* FROM logicint_tlrsite.tbl_user u inner join logicint_tlrsite.tbl_packagemaster p on u.package_id=p.package_id  WHERE username='$username'";
			$adb->checkConnection();
			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$Result = $adb->pquery($Query, array());
			$package_id = $adb->query_result($Result, 0, "package_id");
			$maxlead_cnt = $adb->query_result($Result, 0, "leads");
			$maxcontact_cnt = $adb->query_result($Result, 0, "contacts");
			$maxopp_cnt = $adb->query_result($Result, 0, "opportunities");
			$maxorg_cnt = $adb->query_result($Result, 0, "organizations");
			$maxfile_size = $adb->query_result($Result, 0, "file_storage");
			$storage_type = $adb->query_result($Result, 0, "file_storage_type");
		if($storage_type=='MB')
			$maxfile_size=$maxfile_size*1000000;
		else	if($storage_type=='GB')
				$maxfile_size=$maxfile_size*1073741824;
			$adb->disconnect();
			$adb->resetSettings('mysqli', 'localhost', $OT_DB, $OT_USER, $OT_PASSWORD);


			$cloofQuery = "select (SELECT count(*)  FROM ottocrat_contactdetails) as contactcnt, (SELECT count(*)  FROM ottocrat_leaddetails) as
 leadcnt,(SELECT count(*)  FROM ottocrat_potential) as oppcnt,(SELECT count(*)  FROM ottocrat_account) as orgcnt,
 (SELECT sum(filesize)   FROM ottocrat_notes) as filesize ";
			$adb->checkConnection();
			$adb->database->SetFetchMode(ADODB_FETCH_ASSOC);
			$cloofResult = $adb->pquery($cloofQuery, array());
			$contact_cnt = $adb->query_result($cloofResult, 0, "contactcnt");
			$lead_cnt = $adb->query_result($cloofResult, 0, "leadcnt");
			$opp_cnt = $adb->query_result($cloofResult, 0, "oppcnt");
			$org_cnt = $adb->query_result($cloofResult, 0, "orgcnt");
			$file_size = $adb->query_result($cloofResult, 0, "filesize");



			if ($maxcontact_cnt > $contact_cnt || $maxcontact_cnt == 0) $add_contact_flg = 1;
			if ($maxlead_cnt > $lead_cnt || $maxlead_cnt == 0) $add_lead_flg = 1;
			if ($maxopp_cnt > $opp_cnt || $maxopp_cnt == 0) $add_opp_flg = 1;
			if ($maxorg_cnt > $org_cnt || $maxorg_cnt == 0) $add_org_flg = 1;
			if ($maxfile_size > $file_size || $file_size == 0) $add_file_flg = 1;


		}else
		{

			$add_contact_flg = 1;
			$add_lead_flg 		= 1;
			$add_opp_flg 		= 1;
			$add_org_flg 		= 1;
			$add_file_flg 		= 1;
		}

		$add_record_flg=false;
		if($moduleName=='Contacts')
			$add_record_flg=$add_contact_flg;
		if($moduleName=='Leads')
			$add_record_flg=$add_lead_flg;
		if($moduleName=='Potentials')
			$add_record_flg=$add_opp_flg;
		if($moduleName=='Accounts')
			$add_record_flg=$add_org_flg;
		if($moduleName=='Documents')
			$add_record_flg=$add_file_flg;

		$record = $request->get('record');
		if(!$add_record_flg & $record=='')
		{
			$module_url="index.php?module=$moduleName&view=List";
			header('Location:'.Ottocrat_Request::encryptLink($module_url));
			exit();
		}


        if(!empty($record) && $request->get('isDuplicate') == true) {
            $recordModel = $this->record?$this->record:Ottocrat_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('MODE', '');

			//While Duplicating record, If the related record is deleted then we are removing related record info in record model
			$mandatoryFieldModels = $recordModel->getModule()->getMandatoryFieldModels();
			foreach ($mandatoryFieldModels as $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$fieldName = $fieldModel->get('name');
					if (Ottocrat_Util_Helper::checkRecordExistance($recordModel->get($fieldName))) {
						$recordModel->set($fieldName, '');
					}
				}
			}  
        }else if(!empty($record)) {
            $recordModel = $this->record?$this->record:Ottocrat_Record_Model::getInstanceById($record, $moduleName);
            $viewer->assign('RECORD_ID', $record);
            $viewer->assign('MODE', 'edit');
        } else {
            $recordModel = Ottocrat_Record_Model::getCleanInstance($moduleName);
            $viewer->assign('MODE', '');
        }
        if(!$this->record){
            $this->record = $recordModel;
        }
        
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

		foreach($requestFieldList as $fieldName=>$fieldValue){
			$fieldModel = $fieldList[$fieldName];
			$specialField = false;
			// We collate date and time part together in the EditView UI handling 
			// so a bit of special treatment is required if we come from QuickCreate 
			if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'time_start' && !empty($fieldValue)) { 
				$specialField = true; 
				// Convert the incoming user-picked time to GMT time 
				// which will get re-translated based on user-time zone on EditForm 
				$fieldValue = DateTimeField::convertToDBTimeZone($fieldValue)->format("H:i"); 
                
			}
            
            if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'date_start' && !empty($fieldValue)) { 
                $startTime = Ottocrat_Time_UIType::getTimeValueWithSeconds($requestFieldList['time_start']);
                $startDateTime = Ottocrat_Datetime_UIType::getDBDateTimeValue($fieldValue." ".$startTime);
                list($startDate, $startTime) = explode(' ', $startDateTime);
                $fieldValue = Ottocrat_Date_UIType::getDisplayDateValue($startDate);
            }
			if($fieldModel->isEditable() || $specialField) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$recordStructureInstance = Ottocrat_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Ottocrat_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$picklistDependencyDatasource = Ottocrat_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$isRelationOperation = $request->get('relationOperation');

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
			$viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
		}
		
		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Ottocrat_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT', vglobal('upload_maxsize'));
		$viewer->view('EditView.tpl', $moduleName);
	}
}