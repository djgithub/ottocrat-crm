<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class RecycleBin_Module_Model extends Ottocrat_Module_Model {
	

	/**
	 * Function to get the url for list view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return Ottocrat_Request:: encryptLink('index.php?module='.$this->get('name').'&view='.$this->getListViewName());
	}
	
	/**
	 * Function to get the list of listview links for the module
	 * @return <Array> - Associate array of Link Type to List of Ottocrat_Link_Model instances
	 */
	public function getListViewLinks() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$privileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$basicLinks = array();
		if($currentUserModel->isAdminUser()) {
			$basicLinks = array(
					array(
						'linktype' => 'LISTVIEWBASIC',
						'linklabel' => 'LBL_EMPTY_RECYCLEBIN',
						'linkurl' => 'javascript:RecycleBin_List_Js.emptyRecycleBin("'.Ottocrat_Request:: encryptLink('index.php?module='.$this->get('name').'&action=RecycleBinAjax').'")',
						'linkicon' => ''
					)
				);
		} 

		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Ottocrat_Link_Model::getInstanceFromValues($basicLink);
		}

		return $links;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Ottocrat_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions() {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$massActionLinks = array();
		if($currentUserModel->isAdminUser()) {
			$massActionLinks[] = array(
					'linktype' => 'LISTVIEWMASSACTION',
					'linklabel' => 'LBL_DELETE',
					'linkurl' => 'javascript:RecycleBin_List_Js.deleteRecords("'.Ottocrat_Request:: encryptLink('index.php?module='.$this->get('name').'&action=RecycleBinAjax').'")',
					'linkicon' => ''
			);
		}

			$massActionLinks[] = array(
					'linktype' => 'LISTVIEWMASSACTION',
					'linklabel' => 'LBL_RESTORE',
					'linkurl' => 'javascript:RecycleBin_List_Js.restoreRecords("'.Ottocrat_Request:: encryptLink('index.php?module='.$this->get('name').'&action=RecycleBinAjax').'")',
					'linkicon' => ''
			);
		

		foreach($massActionLinks as $massActionLink) {
			$links[] = Ottocrat_Link_Model::getInstanceFromValues($massActionLink);
		}
		
		return $links;
	}

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Ottocrat_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = Ottocrat_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getDefaultUrl(),
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Ottocrat_Link_Model::getInstanceFromValues($quickLink);
		}
		return $links;
	}
	
	/**
	 * Function to get all entity modules
	 * @return <array>
	 */
	public function getAllModuleList(){
		$moduleModels = parent::getEntityModules();
		$restrictedModules = array('Emails', 'ProjectMilestone', 'ModComments', 'Rss', 'Portal', 'Integration', 'PBXManager', 'Dashboard', 'Home');
		foreach($moduleModels as $key => $moduleModel){
			if(in_array($moduleModel->getName(),$restrictedModules) || $moduleModel->get('isentitytype') != 1){
				unset($moduleModels[$key]);
			}
		}
		return $moduleModels;
	}
	
	/**
	 * Function to delete the reccords perminently in vitger CRM database
	 */
	public function emptyRecycleBin(){
		$db = PearDatabase::getInstance(); 
		$getIdsQuery='SELECT crmid from ottocrat_crmentity WHERE deleted=?';
		$resultIds=$db->pquery($getIdsQuery,array(1));
		$recordIds=array();
		if($db->num_rows($resultIds)){
			for($i=0;$i<$db->num_rows($resultIds);$i++){
				$recordIds[$i]=$db->query_result($resultIds,$i,'crmid');
			}
		}
		$this->deleteFiles($recordIds);
		$db->query('DELETE FROM ottocrat_crmentity WHERE deleted = 1');
		$db->query('DELETE FROM ottocrat_relatedlists_rb');
		
		return true;
	}
	
	/**
	 * Function to deleted the records perminently in CRM
	 * @param type $reocrdIds
	 */
	public function deleteRecords($recordIds){
	        $db = PearDatabase::getInstance(); 
		//Delete the records in ottocrat crmentity and relatedlists.
		$query = 'DELETE FROM ottocrat_crmentity WHERE deleted = ? and crmid in('.generateQuestionMarks($recordIds).')';
		$db->pquery($query, array(1, $recordIds));
		
		$query = 'DELETE FROM ottocrat_relatedlists_rb WHERE entityid in('.generateQuestionMarks($recordIds).')';
		$db->pquery($query, array($recordIds));

		// Delete entries of attachments from ottocrat_attachments and ottocrat_seattachmentsrel
		$this->deleteFiles($recordIds);
		// TODO - Remove records from module tables and other related stores.
	}

	/**Function to delete files from CRM.
	 *@param type $recordIds
	 */

	public function deleteFiles($recordIds){
		$db = PearDatabase::getInstance(); 
		$getAttachmentsIdQuery='SELECT * FROM ottocrat_seattachmentsrel WHERE crmid in('.generateQuestionMarks($recordIds).')';
		$result=$db->pquery($getAttachmentsIdQuery,array($recordIds));
		$attachmentsIds=array();
		if($db->num_rows($result)){
			for($i=0;$i<($db->num_rows($result));$i++){
			$attachmentsIds[$i]=$db->query_result($result,$i,'attachmentsid');
			}
		}
		if(!empty($attachmentsIds)){
                        $deleteRelQuery='DELETE FROM ottocrat_seattachmentsrel WHERE crmid in('.generateQuestionMarks($recordIds).')';
                        $db->pquery($deleteRelQuery,array($recordIds));
                        $attachmentsLocation=array();
                        $getPathQuery='SELECT * FROM ottocrat_attachments WHERE attachmentsid in ('.generateQuestionMarks($attachmentsIds).')';
                        $pathResult=$db->pquery($getPathQuery,array($attachmentsIds));
                        if($db->num_rows($pathResult)){
                                for($i=0;$i<($db->num_rows($pathResult));$i++){
                                        $attachmentsLocation[$i]=$db->query_result($pathResult,$i,'path');
                                        $attachmentName=$db->query_result($pathResult,$i,'name');
                                        $attachmentId=$db->query_result($pathResult,$i,'attachmentsid');
                                        $fileName=$attachmentsLocation[$i].$attachmentId.'_'.$attachmentName;
                                        if(file_exists($fileName)){
                                                chmod($fileName,0750);
                                                unlink($fileName);
                                        }
                                }
                        }
                        $deleteAttachmentQuery='DELETE FROM ottocrat_attachments WHERE attachmentsid in ('.generateQuestionMarks($attachmentsIds).')';
                        $db->pquery($deleteAttachmentQuery,array($attachmentsIds));
                }
	}

	/**
	 * Function to restore the deleted records.
	 * @param type $sourceModule
	 * @param type $recordIds
	 */
	public function restore($sourceModule, $recordIds){
		$focus = CRMEntity::getInstance($sourceModule);
		for($i=0;$i<count($recordIds);$i++) {
			if(!empty($recordIds[$i])) {
				$focus->restore($sourceModule, $recordIds[$i]);
			}
		}
	}
        
          public function getDeletedRecordsTotalCount() {  
                $db = PearDatabase::getInstance();  
                $totalCount = $db->pquery('select count(*) as count from ottocrat_crmentity where deleted=1',array());  
                return $db->query_result($totalCount, 0, 'count');  
        }
}
