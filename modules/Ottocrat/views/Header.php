<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * ************************************************************************************/

abstract class Ottocrat_Header_View extends Ottocrat_View_Controller {

	function __construct() {
		parent::__construct();
	}

	//Note : To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/*function preProcessParentTplName(Ottocrat_Request $request) {
		return parent::preProcessTplName($request);
	}*/

	/**
	 * Function to determine file existence in relocated module folder (under ottocrat6)
	 * @param String $fileuri
	 * @return Boolean
	 *
	 * Utility function to manage the backward compatible file load
	 * which are registered for 5.x modules (and now provided for 6.x as well).
	 */
	protected function checkFileUriInRelocatedMouldesFolder($fileuri) {
		list ($filename, $query) = explode('?', $fileuri);

		// prefix the base lookup folder (relocated file).
		if (strpos($filename, 'modules') === 0) {
			$filename = $filename;
		}

        return file_exists($filename);
	}

	/**
	 * Function to get the list of Header Links
	 * @return <Array> - List of Ottocrat_Link_Model instances
	 */
	function getHeaderLinks() {
		$appUniqueKey = vglobal('application_unique_key');
		$ottocratCurrentVersion = vglobal('ottocrat_current_version');
		$site_URL = vglobal('site_URL');

		$userModel = Users_Record_Model::getCurrentUserModel();
		$userEmail = $userModel->get('email1');

		$headerLinks = array();
				// Note: This structure is expected to generate side-bar feedback button.
		/*	array (
				'linktype' => 'HEADERLINK',
				'linklabel' => 'LBL_FEEDBACK',
				'linkurl' => "javascript:window.open('http://ottocrat.com/products/crm/od-feedback/index.php?version=".$ottocratCurrentVersion.
					"&email=".$userEmail."&uid=".$appUniqueKey.
					"&ui=6','feedbackwin','height=400,width=550,top=200,left=300')",
				'linkicon' => 'info.png',
				'childlinks' => array(
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_DOCUMENTATION',
						'linkurl' => 'https://wiki.ottocrat.com/ottocrat6/index.php/Main_Page',
						'linkicon' => '',
						'target' => '_blank'
					),
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_VIDEO_TUTORIAL',
						'linkurl' => 'https://www.ottocrat.com/crm/videos',
						'linkicon' => '',
						'target' => '_blank'
					),
					// Note: This structure is expected to generate side-bar feedback button.
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_FEEDBACK',
						'linkurl' => "javascript:window.open('http://ottocrat.com/products/crm/od-feedback/index.php?version=".$ottocratCurrentVersion.
							"&email=".$userEmail."&uid=".$appUniqueKey.
							"&ui=6','feedbackwin','height=400,width=550,top=200,left=300')",
						'linkicon' => '',
					)
				)
			)
		);*/
		
		if($userModel->isAdminUser()) {
			$crmSettingsLink = array(
				'linktype' => 'HEADERLINK',
				'linklabel' => 'LBL_CRM_SETTINGS',
				'linkurl' => '',
				'linkicon' => 'setting.png',
				'childlinks' => array(
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_CRM_SETTINGS',
						'linkurl' => '?'.Ottocrat_Request::encryptLink("module=Ottocrat&parent=Settings&view=Index"),
						'linkicon' => '',
					),
                                        array(), // separator 
                                        array ( 
                                                'linktype' => 'HEADERLINK', 
                                                'linklabel' => 'LBL_MANAGE_USERS', 
                                                'linkurl' => '?'.Ottocrat_Request::encryptLink	("module=Users&parent=Settings&view=List"),
                                                'linkicon' => '', 
                                       ),
				)
			);
			array_push($headerLinks, $crmSettingsLink);
		}
		$userPersonalSettingsLinks = array(
				'linktype' => 'HEADERLINK',
				'linklabel' => $userModel->getDisplayName(),
				'linkurl' => '',
				'linkicon' => '',
				'childlinks' => array(
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_MY_PREFERENCES',
						'linkurl' => $userModel->getPreferenceDetailViewUrl(),
						'linkicon' => '',
					),
					array(), // separator
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_SIGN_OUT',
					//	'linkurl' => '?module=Users&parent=Settings&action=Logout',
						'linkurl' =>'?'.Ottocrat_Request::encryptLink("module=Users&parent=Settings&action=Logout") ,
						'linkicon' => '',
					)
				)
			);
		array_push($headerLinks, $userPersonalSettingsLinks);
		$headerLinkInstances = array();

		$index = 0;
		foreach($headerLinks as  $headerLink) {
			$headerLinkInstance = Ottocrat_Link_Model::getInstanceFromValues($headerLink);
			foreach($headerLink['childlinks'] as $childLink) {
				$headerLinkInstance->addChildLink(Ottocrat_Link_Model::getInstanceFromValues($childLink));
			}
			$headerLinkInstances[$index++] = $headerLinkInstance;
		}
		$headerLinks = Ottocrat_Link_Model::getAllByType(Ottocrat_Link::IGNORE_MODULE, array('HEADERLINK'));
		foreach($headerLinks as $headerType => $headerLinks) {
			foreach($headerLinks as $headerLink) {
				$headerLinkInstances[$index++] = Ottocrat_Link_Model::getInstanceFromLinkObject($headerLink);
			}
		}
		return $headerLinkInstances;
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Ottocrat_Request $request
	 * @return <Array> - List of Ottocrat_JsScript_Model instances
	 */
	function getHeaderScripts(Ottocrat_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$headerScripts = Ottocrat_Link_Model::getAllByType(Ottocrat_Link::IGNORE_MODULE, array('HEADERSCRIPT'));
		foreach($headerScripts as $headerType => $headerScripts) {
			foreach($headerScripts as $headerScript) {
				if ($this->checkFileUriInRelocatedMouldesFolder($headerScript->linkurl)) {
					$headerScriptInstances[] = Ottocrat_JsScript_Model::getInstanceFromLinkObject($headerScript);
				}
			}
		}
		return $headerScriptInstances;
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param Ottocrat_Request $request
	 * @return <Array> - List of Ottocrat_CssScript_Model instances
	 */
	function getHeaderCss(Ottocrat_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);
		$headerCss = Ottocrat_Link_Model::getAllByType(Ottocrat_Link::IGNORE_MODULE, array('HEADERCSS'));
		$selectedThemeCssPath = Ottocrat_Theme::getStylePath();
		//TODO : check the filename whether it is less or css and add relative less
		$isLessType = (strpos($selectedThemeCssPath, ".less") !== false)? true:false;
		$cssScriptModel = new Ottocrat_CssScript_Model();
		$headerCssInstances[] = $cssScriptModel->set('href', $selectedThemeCssPath)
									->set('rel',
											$isLessType?
											Ottocrat_CssScript_Model::LESS_REL :
											Ottocrat_CssScript_Model::DEFAULT_REL);

		foreach($headerCss as $headerType => $cssLinks) {
			foreach($cssLinks as $cssLink) {
				if ($this->checkFileUriInRelocatedMouldesFolder($cssLink->linkurl)) {
					$headerCssInstances[] = Ottocrat_CssScript_Model::getInstanceFromLinkObject($cssLink);
				}
			}
		}
		return $headerCssInstances;
	}

	/**
	 * Function to get the Announcement
	 * @return Ottocrat_Base_Model - Announcement
	 */
	function getAnnouncement() {
		//$announcement = Ottocrat_Cache::get('announcement', 'value');
		$model = new Ottocrat_Base_Model();
		//if(!$announcement) {
			$announcement = get_announcements();
				//Ottocrat_Cache::set('announcement', 'value', $announcement);
		//}
		return $model->set('announcement', $announcement);
	}

}
