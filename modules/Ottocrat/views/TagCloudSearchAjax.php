<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_TagCloudSearchAjax_View extends Ottocrat_IndexAjax_View {

	function process(Ottocrat_Request $request) {
		
		$tagId = $request->get('tag_id');
		$taggedRecords = Ottocrat_Tag_Model::getTaggedRecords($tagId);
		
		$viewer = $this->getViewer($request);
		
		$viewer->assign('TAGGED_RECORDS',$taggedRecords);
		$viewer->assign('TAG_NAME',$request->get('tag_name'));
		
		echo $viewer->view('TagCloudResults.tpl', $module, true);
	}
	
	
	
}