<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_SaveWidgetPositions_Action extends Ottocrat_IndexAjax_View {

	public function process(Ottocrat_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		
		$positionsMap = $request->get('positionsmap');
		
		if ($positionsMap) {
			foreach ($positionsMap as $id => $position) {
				list ($linkid, $widgetid) = explode('-', $id);
				if ($widgetid) {
					Ottocrat_Widget_Model::updateWidgetPosition($position, NULL, $widgetid, $currentUser->getId());
				} else {
					Ottocrat_Widget_Model::updateWidgetPosition($position, $linkid, NULL, $currentUser->getId());
				}
			}
		}
		
		$response = new Ottocrat_Response();
		$response->setResult(array('Save' => 'OK'));
		$response->emit();
	}
}
