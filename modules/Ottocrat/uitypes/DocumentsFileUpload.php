<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
class Ottocrat_DocumentsFileUpload_UIType extends Ottocrat_Base_UIType {
	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/DocumentsFileUpload.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <String> $value
	 * @param <Integer> $recordId
	 * @param <Ottocrat_Record_Model>
	 * @return <String>
	 */
	public function getDisplayValue($value, $recordId=false, $recordModel=false) {
		if($recordModel) {
			$fileLocationType = $recordModel->get('filelocationtype');
			$fileStatus = $recordModel->get('filestatus');
			if(!empty($value) && $fileStatus) {
				if($fileLocationType == 'I') {
					$db = PearDatabase::getInstance();
					$fileIdRes = $db->pquery('SELECT attachmentsid FROM ottocrat_seattachmentsrel WHERE crmid = ?', array($recordId));
					$fileId = $db->query_result($fileIdRes, 0, 'attachmentsid');
					if($fileId){
						$value = '<a href="'.Ottocrat_Request:: encryptLink('index.php?module=Documents&action=DownloadFile&record='.$recordId.'&fileid='.$fileId).'"'.
									' title="'.	vtranslate('LBL_DOWNLOAD_FILE', 'Documents').'" >'.$value.'</a>';
					}
				} else {
					$value = '<a href="'.$value.'" target="_blank" title="'. vtranslate('LBL_DOWNLOAD_FILE', 'Documents').'" >'.$value.'</a>';
				}
			}
		}
		return $value;
	}
}