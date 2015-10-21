<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_ListUI5_View extends Settings_Ottocrat_UI5Embed_View {

	protected function getUI5EmbedURL(Ottocrat_Request $request) {
        $module = $request->getModule();
        if($module == 'EmailTemplate') {
            return Ottocrat_Request:: encryptLink('index
            .php?module=Settings&action=listemailtemplates&parenttab=Settings');
        }
	}

}
