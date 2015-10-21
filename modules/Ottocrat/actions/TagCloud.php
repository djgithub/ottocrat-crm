<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_TagCloud_Action extends Ottocrat_Action_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
		$this->exposeMethod('getTags');
	}

	function checkPermission(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Ottocrat_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if(!$permission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
		return true;
	}

	public function process(Ottocrat_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function saves a tag for a record
	 * @param Ottocrat_Request $request
	 */
	public function save(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$tagModel = new Ottocrat_Tag_Model();
		$tagModel->set('userid', $currentUser->id);
		$tagModel->set('record', $request->get('record'));
		$tagModel->set('tagname', decode_html($request->get('tagname')));
		$tagModel->set('module', $request->getModule());
		$tagModel->save();

		$taggedInfo = Ottocrat_Tag_Model::getAll($currentUser->id, $request->getModule(), $request->get('record'));
		$response = new Ottocrat_Response();
		$response->setResult($taggedInfo);
		$response->emit($taggedInfo);
	}

	/**
	 * Function deleted a tag
	 * @param Ottocrat_Request $request
	 */
	public function delete(Ottocrat_Request $request) {
		$tagModel = new Ottocrat_Tag_Model();
		$tagModel->set('record', $request->get('record'));
		$tagModel->set('tag_id', $request->get('tag_id'));
		$tagModel->delete();
	}

	/**
	 * Function returns list of tage for the record
	 * @param Ottocrat_Request $request
	 */
	public function getTags(Ottocrat_Request $request) {

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');
		$module = $request->getModule();

		$tags = Ottocrat_Tag_Model::getAll($currentUser->id, $module, $record);
		#print_r($tags);
		$response = new Ottocrat_Response();
		$response->setResult($tags);
		$response->emit($tags);
	}
}
