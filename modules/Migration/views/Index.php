<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Migration_Index_View extends Ottocrat_Basic_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
		$this->exposeMethod('applyDBChanges');
	}

	public function checkPermission(Ottocrat_Request $request){
		return true;
	}

	public function process(Ottocrat_Request $request) {
                // Override error reporting to production mode
                version_compare(PHP_VERSION, '5.5.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT); 
            
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	protected function step1(Ottocrat_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULENAME', $moduleName);
		$viewer->view('MigrationStep1.tpl', $moduleName);
	}

	protected function step2(Ottocrat_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE', $moduleName);
		$viewer->view('MigrationStep2.tpl', $moduleName);
	}


	public function preProcess(Ottocrat_Request $request, $display = true) {
		parent::preProcess($request, false);
	}

	public function getHeaderCss(Ottocrat_Request $request) {
		$headerCssInstances = array();
		$cssFileNames = array(
			'~/layouts/vlayout/modules/Migration/css/style.css',
			'~/layouts/vlayout/modules/Migration/css/mkCheckbox.css',
			'~/libraries/bootstrap/css/bootstrap-responsive.css',
			'~/libraries/bootstrap/css/bootstrap.min.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		return $headerCssInstances;
	}

	public function getHeaderScripts(Ottocrat_Request $request) {
		$headerScriptInstances = array();
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.$moduleName.resources.Index"
			);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function applyDBChanges(){
		$migrationModuleModel = Migration_Module_Model::getInstance();

		$getAllowedMigrationVersions = $migrationModuleModel->getAllowedMigrationVersions();
		$getDBVersion = str_replace(array('.', ' '),'', $migrationModuleModel->getDBVersion());
		$getLatestSourceVersion = str_replace(array('.', ' '),'', $migrationModuleModel->getLatestSourceVersion());
		$migrateVersions = array();
		foreach($getAllowedMigrationVersions as $getAllowedMigrationVersion) {
			foreach($getAllowedMigrationVersion as $version => $label) {
				if(strcasecmp($version, $getDBVersion) == 0 || $reach == 1) {
					$reach = 1;
					$migrateVersions[] = $version;
				}
			}
		}
		$migrateVersions[] = $getLatestSourceVersion;

		$patchCount  = count($migrateVersions);

		define('OTTOCRAT_UPGRADE', true);

		for($i=0; $i<$patchCount; $i++){
			$filename =  "modules/Migration/schema/".$migrateVersions[$i]."_to_".$migrateVersions[$i+1].".php";
			if(is_file($filename)) {
				if(!defined('INSTALLATION_MODE')) {
					echo "<table class='config-table'><tr><th><span><b><font color='red'>".$migrateVersions[$i]." ==> ".$migrateVersions[$i+1]." Database changes -- Starts. </font></b></span></th></tr></table>";
					echo "<table class='config-table'>";
				}
				$_i_statesaved = $i;
				include($filename);
				$i = $_i_statesaved;
				if(!defined('INSTALLATION_MODE')) {
					echo "<table class='config-table'><tr><th><span><b><font color='red'>".$migrateVersions[$i]." ==> ".$migrateVersions[$i+1]." Database changes -- Ends.</font></b></span></th></tr></table>";
				}
			} else if(isset($migrateVersions[$patchCount+1])){
				echo "<table class='config-table'><tr><th><span><b><font color='red'> There is no Database Changes from ".$migrateVersions[$i]." ==> ".$migrateVersions[$i+1]."</font></b></span></th></tr></table>";
			}
		}

		//update ottocrat version in db
		$migrationModuleModel->updateOttocratVersion();
		// To carry out all the necessary actions after migration
		$migrationModuleModel->postMigrateActivities();
	}

	public static function ExecuteQuery($query, $params){
		$adb = PearDatabase::getInstance();
		$status = $adb->pquery($query, $params);
		if(!defined('INSTALLATION_MODE')) {
			$query = $adb->convert2sql($query, $params);
			if(is_object($status)) {
				echo '<tr><td width="80%"><span>'.$query.'</span></td><td style="color:green">Success</td></tr>';
			} else {
				echo '<tr><td width="80%"><span>'.$query.'</span></td><td style="color:red">Failure</td></tr>';
			}
		}
		return $status;
	}

	public static function insertSelectQuery() {
		global $adb;
		$genQueryId = $adb->getUniqueID("ottocrat_selectquery");
		if ($genQueryId != "") {
			$iquerysql = "insert into ottocrat_selectquery (QUERYID,STARTINDEX,NUMOFOBJECTS) values (?,?,?)";
			self::ExecuteQuery($iquerysql, array($genQueryId, 0, 0));
		}
		return $genQueryId;
	}

	public static function insertSelectColumns($queryid, $columnname) {
		if ($queryid != "") {
			for ($i = 0; $i < count($columnname); $i++) {
				$icolumnsql = "insert into ottocrat_selectcolumn (QUERYID,COLUMNINDEX,COLUMNNAME) values (?,?,?)";
				self::ExecuteQuery($icolumnsql, array($queryid, $i, $columnname[$i]));
			}
		}
	}

	public static function insertReports($queryid, $folderid, $reportname, $description, $reporttype) {
		if ($queryid != "") {
			$ireportsql = "insert into ottocrat_report (REPORTID,FOLDERID,REPORTNAME,DESCRIPTION,REPORTTYPE,QUERYID,STATE) values (?,?,?,?,?,?,?)";
			$ireportparams = array($queryid, $folderid, $reportname, $description, $reporttype, $queryid, 'SAVED');
			self::ExecuteQuery($ireportsql, $ireportparams);
		}
	}

	public static function insertReportModules($queryid, $primarymodule, $secondarymodule) {
		if ($queryid != "") {
			$ireportmodulesql = "insert into ottocrat_reportmodules (REPORTMODULESID,PRIMARYMODULE,SECONDARYMODULES) values (?,?,?)";
			self::ExecuteQuery($ireportmodulesql, array($queryid, $primarymodule, $secondarymodule));
		}
	}

	public static function insertAdvFilter($queryid, $filters) {
		if ($queryid != "") {
			$columnIndexArray = array();
			foreach ($filters as $i => $filter) {
				$irelcriteriasql = "insert into ottocrat_relcriteria(QUERYID,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE) values (?,?,?,?,?)";
				self::ExecuteQuery($irelcriteriasql, array($queryid, $i, $filter['columnname'], $filter['comparator'], $filter['value']));
				$columnIndexArray[] = $i;
			}
			$conditionExpression = implode(' and ', $columnIndexArray);
			self::ExecuteQuery('INSERT INTO ottocrat_relcriteria_grouping VALUES(?,?,?,?)', array(1, $queryid, '', $conditionExpression));
		}
	}
	
		/**
	 * Function to transform workflow filter of old look in to new look
	 * @param <type> $conditions
	 * @return <type>
	 */
	public static function transformAdvanceFilterToWorkFlowFilter($conditions) {
		$wfCondition = array();

		if(!empty($conditions)) {
			$previousConditionGroupId = 0;
			foreach($conditions as $condition) {

				$fieldName = $condition['fieldname'];
				$fieldNameContents = explode(' ', $fieldName);
				if (count($fieldNameContents) > 1) {
					$fieldName = '('. $fieldName .')';
				}

				$groupId = $condition['groupid'];
				if (!$groupId) {
					$groupId = 0;
				}

				$groupCondition = 'or';
				if ($groupId === $previousConditionGroupId || count($conditions) === 1) {
					$groupCondition = 'and';
				}

				$joinCondition = 'or';
				if (isset ($condition['joincondition'])) {
					$joinCondition = $condition['joincondition'];
				} elseif($groupId === 0) {
					$joinCondition = 'and';
				}

				$value = $condition['value'];
				switch ($value) {
					case 'false:boolean'	: $value = 0;	break;
					case 'true:boolean'		: $value = 1;	break;
					default					: $value;		break;
				}

				$wfCondition[] = array(
						'fieldname' => $fieldName,
						'operation' => $condition['operation'],
						'value' => $value,
						'valuetype' => 'rawtext',
						'joincondition' => $joinCondition,
						'groupjoin' => $groupCondition,
						'groupid' => $groupId
				);
				$previousConditionGroupId = $groupId;
			}
		}
		return $wfCondition;
	}
}