<?php

/* +**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Ottocrat_CompanyDetailsSave_Action extends Settings_Ottocrat_Basic_Action {

	public function process(Ottocrat_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Ottocrat_CompanyDetails_Model::getInstance();
		$status = false;

        if ($request->get('organizationname')) {
            $saveLogo = $status = true;
			if(!empty($_FILES['logo']['name'])) {
                $logoDetails = $_FILES['logo'];
                $fileType = explode('/', $logoDetails['type']);
                $fileType = $fileType[1];

                if (!$logoDetails['size'] || !in_array($fileType, Settings_Ottocrat_CompanyDetails_Model::$logoSupportedFormats)) {
                    $saveLogo = false;
                }
				// Check for php code injection
				$imageContents = file_get_contents($_FILES["logo"]["tmp_name"]);
				if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
					$saveLogo = false;
				}
                if ($saveLogo) {
                    $moduleModel->saveLogo();
                }
            }else{
                $saveLogo = true;
            }
			$fields = $moduleModel->getFields();
			foreach ($fields as $fieldName => $fieldType) {
				$fieldValue = $request->get($fieldName);
				if ($fieldName === 'logoname') {
					if (!empty($logoDetails['name'])) {
						$fieldValue = ltrim(basename(" " . $logoDetails['name']));
					} else {
						$fieldValue = $moduleModel->get($fieldName);
					}
				}
				$moduleModel->set($fieldName, $fieldValue);
			}
			$moduleModel->save();
		}

		$reloadUrl = $moduleModel->getIndexViewUrl();
		if ($saveLogo && $status) {

		} else if (!$saveLogo) {
			$reloadUrl .= '&error=LBL_INVALID_IMAGE';
		} else {
			$reloadUrl = $moduleModel->getEditViewUrl() . '&error=LBL_FIELDS_INFO_IS_EMPTY';
		}
		header('Location: ' . $reloadUrl);
	}

        public function validateRequest(Ottocrat_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}