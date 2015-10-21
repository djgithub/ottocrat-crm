<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_ShowTagCloud_View extends Ottocrat_IndexAjax_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showTags');
	}

	function showTags(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');
		if($record) {
			$module = $request->getModule();
			if($module == 'Events'){
				$module = 'Calendar';
			}
			
			vimport('~~/libraries/freetag/freetag.class.php');
			$freeTagInstance = new freetag();
			$maxTagLength = $freeTagInstance->_MAX_TAG_LENGTH;

			$tags = Ottocrat_Tag_Model::getAll($currentUser->id, $module, $record);
			$viewer = $this->getViewer($request);
			
			$viewer->assign('MAX_TAG_LENGTH', $maxTagLength);
			$viewer->assign('TAGS', $tags);
			$viewer->assign('MODULE',$module);
			echo $viewer->view('ShowTagCloud.tpl', $module, true);
		}
	}
}

?>
