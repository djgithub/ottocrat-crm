<?php

/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Ottocrat_Announcement_Model extends Ottocrat_Base_Model {
    
    const tableName  = 'ottocrat_announcement';
    
    
    public function save() {
        $db = PearDatabase::getInstance();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentDate = date('Y-m-d H:i:s');
        $checkQuery = 'SELECT 1 FROM '.self::tableName.' WHERE creatorid=?';
        $result = $db->pquery($checkQuery,array($currentUser->getId()));
        if($db->num_rows($result) > 0) {
            $query = 'UPDATE '.self::tableName.' SET announcement=?,time=? WHERE creatorid=?';
            $params = array($this->get('announcement'),$db->formatDate($currentDate, true),$currentUser->getId());
        }else{
            $query = 'INSERT INTO '.self::tableName.' VALUES(?,?,?,?)';
            $params = array($currentUser->getId(),$this->get('announcement'),'announcement',$db->formatDate($currentDate, true));
        }
        $db->pquery($query,$params);
    }
    
    public static function getInstanceByCreator(Users_Record_Model $user) {
        $db = PearDatabase::getInstance();
        $query = 'SELECT * FROM '.self::tableName.' WHERE creatorid=?';
        $result = $db->pquery($query,array($user->getId()));
        $instance = new self();
        if($db->num_rows($result) > 0) {
            $row = $db->query_result_rowdata($result,0);
            $instance->setData($row);
        }
        return $instance;
    }
}