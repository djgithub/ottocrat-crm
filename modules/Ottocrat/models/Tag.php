<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/libraries/freetag/freetag.class.php');

class Ottocrat_Tag_Model extends Ottocrat_Base_Model {

	private $_freetag = false;

	static $TAG_FETCH_LIMIT = 100;

	function __construct() {
		$this->_freetag = new freetag();
	}

	/**
	 * Function saves a tag to database
	 */
	public function save() {
		$this->_freetag->tag_object($this->get('userid'), $this->get('record'), $this->get('tagname'), $this->get('module'));
	}

	/**
	 * Function deletes a tag from database
	 */
	public function delete() {
		$db = PearDatabase::getInstance();
		$db->database->Execute("DELETE FROM ottocrat_freetagged_objects WHERE tag_id = ".$this->get('tag_id')." AND
		object_id in	(". $this->get('record').")");
		/*$db->pquery('DELETE FROM ottocrat_freetagged_objects WHERE tag_id = ? AND object_id in (?)',
				array($this->get('tag_id'), $this->get('record')));*/
	}

	/**
	 * Function returns the tags
	 * @param type $userId
	 * @param type $module
	 * @param type $record
	 * @return type
	 */
	public static function getAll($userId = NULL, $module = "", $record = NULL) {
		$tag = new self();
		return $tag->_freetag->get_tag_cloud_tags(self::$TAG_FETCH_LIMIT, $userId, $module, $record);
	}
	
	public static function getTaggedRecords($tagId) {
		$recordModels = array();
		if(!empty($tagId)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT ottocrat_crmentity.* FROM ottocrat_freetags 
				INNER JOIN ottocrat_freetagged_objects ON ottocrat_freetags.id = ottocrat_freetagged_objects.tag_id 
				INNER JOIN ottocrat_crmentity ON ottocrat_freetagged_objects.object_id=ottocrat_crmentity.crmid 
					AND ottocrat_crmentity.deleted=0 
				WHERE tag_id = ?", array($tagId));
			$rows = $db->num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$row = $db->query_result_rowdata($result, $i);
				$recordModel = Ottocrat_Record_Model::getCleanInstance($row['setype']);
				$recordModel->setData($row);
				$recordModel->setId($row['crmid']);
				$recordModels[$row['setype']][] = $recordModel;
			}
		}
		return $recordModels;
	}
}

?>
