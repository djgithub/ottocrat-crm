<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Ottocrat_UI5Embed_View extends Ottocrat_Index_View {
	
	protected function preProcessDisplay(Ottocrat_Request $request) {}
	
	protected function getUI5EmbedURL(Ottocrat_Request $request) {
		return '../'.Ottocrat_Request:: encryptLink('index.php?action=index&module=' . $request->getModule());
	}
	
	public function process(Ottocrat_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('UI5_URL', $this->getUI5EmbedURL($request));
		$viewer->view('UI5EmbedView.tpl');
	}
	
}