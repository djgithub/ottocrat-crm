<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Home_Module_Model extends Ottocrat_Module_Model {

	/**
	 * Function returns the default view for the Home module
	 * @return <String>
	 */
	public function getDefaultViewName() {
		return 'DashBoard';
	}

	/**
	 * Function returns latest comments across CRM
	 * @param <Ottocrat_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getComments($pagingModel) {
		$db = PearDatabase::getInstance();

		$nonAdminAccessQuery = Users_Privileges_Model::getNonAdminAccessControlQuery('ModComments');

		$result = $db->pquery('SELECT *, ottocrat_crmentity.createdtime AS createdtime, ottocrat_crmentity.smownerid AS smownerid,
						crmentity2.crmid AS parentId, crmentity2.setype AS parentModule FROM ottocrat_modcomments
						INNER JOIN ottocrat_crmentity ON ottocrat_modcomments.modcommentsid = ottocrat_crmentity.crmid
							AND ottocrat_crmentity.deleted = 0
						INNER JOIN ottocrat_crmentity crmentity2 ON ottocrat_modcomments.related_to = crmentity2.crmid
							AND crmentity2.deleted = 0
						 '.$nonAdminAccessQuery.'
						ORDER BY ottocrat_crmentity.crmid DESC LIMIT ?, ?',
				array($pagingModel->getStartIndex(), $pagingModel->getPageLimit()));

		$comments = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['related_to'])){
				$commentModel = Ottocrat_Record_Model::getCleanInstance('ModComments');
				$commentModel->setData($row);
				$time = $commentModel->get('createdtime');
				$comments[$time] = $commentModel;
			}
		}

		return $comments;
	}

        /**
	 * Function returns part of the query to  fetch only  activity
	 * @param <String> $type - comments, updates or all
	 * @return <String> $query 
	 */
          public function getActivityQuery($type)
        {
             if($type == 'updates'){
                 $query=' AND module != "ModComments" ';
		return $query;	
             }
            
        }

        
	/**
	 * Function returns comments and recent activities across CRM
	 * @param <Ottocrat_Paging_Model> $pagingModel
	 * @param <String> $type - comments, updates or all
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type=false) {
		if(empty($type)) {
			$type = 'all';
		}
		//TODO: need to handle security
		$comments = array();
		if( $type == 'comments') {
			$modCommentsModel = Ottocrat_Module_Model::getInstance('ModComments'); 
			if($modCommentsModel->isPermitted('DetailView')){
				$comments = $this->getComments($pagingModel);
			}
			if($type == 'comments') {
				return $comments;
			}
		}
		//As getComments api is used to get comment infomation,no need of getting
		//comment information again,so avoiding from modtracker
               //updateActivityQuery api is used to update a query to fetch a only activity
		
                else if($type == 'updates' || $type == 'all' )
                {
                     $db = PearDatabase::getInstance();
                     $queryforActivity= $this->getActivityQuery($type);
                     $result = $db->pquery('SELECT ottocrat_modtracker_basic.*
								FROM ottocrat_modtracker_basic
								INNER JOIN ottocrat_crmentity ON ottocrat_modtracker_basic.crmid = ottocrat_crmentity.crmid
								AND deleted = 0 ' .  $queryforActivity .'
								ORDER BY ottocrat_modtracker_basic.id DESC LIMIT ?, ?',array($pagingModel->getStartIndex(), $pagingModel->getPageLimit()));

                     $history = array();
		     for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$moduleName = $row['module'];
			$recordId = $row['crmid'];
			if(Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId)){
				$modTrackerRecorModel = new ModTracker_Record_Model();
				$modTrackerRecorModel->setData($row)->setParent($recordId, $moduleName);
				$time = $modTrackerRecorModel->get('changedon');
				$history[$time] = $modTrackerRecorModel;
			      }
		    }  
                    
                    return $history;
                }
		return false;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <String> $mode - upcoming/overdue mode
	 * @param <Ottocrat_Paging_Model> $pagingModel - $pagingModel
	 * @param <String> $user - all/userid
	 * @param <String> $recordId - record id
	 * @return <Array>
	 */
	function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Ottocrat_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Ottocrat_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$query = "SELECT ottocrat_crmentity.crmid, ottocrat_crmentity.smownerid, ottocrat_crmentity.setype, ottocrat_activity.* FROM ottocrat_activity
					INNER JOIN ottocrat_crmentity ON ottocrat_crmentity.crmid = ottocrat_activity.activityid
					LEFT JOIN ottocrat_groups ON ottocrat_groups.groupid = ottocrat_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

		$query .= " WHERE ottocrat_crmentity.deleted=0
					AND (ottocrat_activity.activitytype NOT IN ('Emails'))
					AND (ottocrat_activity.status is NULL OR ottocrat_activity.status NOT IN ('Completed', 'Deferred'))
					AND (ottocrat_activity.eventstatus is NULL OR ottocrat_activity.eventstatus NOT IN ('Held'))";

		if ($mode === 'upcoming') {
			$query .= " AND CASE WHEN ottocrat_activity.activitytype='Task' THEN due_date >= '$currentDate' ELSE CONCAT(due_date,' ',time_end) >= '$nowInDBFormat' END";
		} elseif ($mode === 'overdue') {
			$query .= " AND CASE WHEN ottocrat_activity.activitytype='Task' THEN due_date < '$currentDate' ELSE CONCAT(due_date,' ',time_end) < '$nowInDBFormat' END";
		}

		$params = array();
		if($user != 'all' && $user != '') {
			if($user === $currentUser->id) {
				$query .= " AND ottocrat_crmentity.smownerid = ?";
				$params[] = $user;
			}
		}

		$query .= " ORDER BY date_start, time_start LIMIT ?, ?";
		$params[] = $pagingModel->getStartIndex();
		$params[] = $pagingModel->getPageLimit()+1;

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$model = Ottocrat_Record_Model::getCleanInstance('Calendar');
			$model->setData($row);
            if($row['activitytype'] == 'Task'){
                $due_date = $row["due_date"];
                $dayEndTime = "23:59:59";
                $EndDateTime = Ottocrat_Datetime_UIType::getDBDateTimeValue($due_date." ".$dayEndTime);
                $dueDateTimeInDbFormat = explode(' ',$EndDateTime);
                $dueTimeInDbFormat = $dueDateTimeInDbFormat[1];
                $model->set('time_end',$dueTimeInDbFormat);
            }
			$model->setId($row['crmid']);
			$activities[] = $model;
		}

		$pagingModel->calculatePageRange($activities);
		if($numOfRows > $pagingModel->getPageLimit()){
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $activities;
	}
}
