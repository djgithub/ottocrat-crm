<?php
/* +***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 * *********************************************************************************** */

class Google_Utils_Helper {
    
    const settings_table_name = 'ottocrat_google_sync_settings'; 
    const fieldmapping_table_name = 'ottocrat_google_sync_fieldmapping'; 

    /**
     * Updates the database with syncronization times
     * @param <sting> $sourceModule module to which sync time should be stored
     * @param <date> $modifiedTime Max modified time of record that are sync 
     */
    public static function updateSyncTime($sourceModule, $modifiedTime = false) {
        $db = PearDatabase::getInstance();
        self::intialiseUpdateSchema();
        $user = Users_Record_Model::getCurrentUserModel();
        if (!$modifiedTime) {
            $modifiedTime = self::getSyncTime($sourceModule);
        }
        if (!self::getSyncTime($sourceModule)) {
            if ($modifiedTime) {
                $db->pquery('INSERT INTO ottocrat_google_sync (googlemodule,user,synctime,lastsynctime) VALUES (?,?,?,?)', array($sourceModule, $user->id, $modifiedTime, date('Y-m-d H:i:s')));
            }
        } else {
            $db->pquery('UPDATE ottocrat_google_sync SET synctime = ?,lastsynctime = ? WHERE user=? AND googlemodule=?', array($modifiedTime, date('Y-m-d H:i:s'), $user->id, $sourceModule));
        }
    }

    /**
     *  Creates sync table if not exists
     */
    private static function intialiseUpdateSchema() {
        if (!Ottocrat_Utils::CheckTable('ottocrat_google_sync')) {
            Ottocrat_Utils::CreateTable('ottocrat_google_sync', '(googlemodule varchar(50),user int(10), synctime datetime,lastsynctime datetime)', true);
        }
    }

    /**
     *  Gets the max Modified time of last sync records
     *  @param <sting> $sourceModule modulename to which sync time should return
     *  @return <date> max Modified time of last sync records OR <boolean> false when date not present  
     */
    public static function getSyncTime($sourceModule) {
        $db = PearDatabase::getInstance();
        self::intialiseUpdateSchema();
        $user = Users_Record_Model::getCurrentUserModel();
        $result = $db->pquery('SELECT synctime FROM ottocrat_google_sync WHERE user=? AND googlemodule=?', array($user->id, $sourceModule));
        if ($result && $db->num_rows($result) > 0) {
            $row = $db->fetch_array($result);
            return $row['synctime'];
        } else {
            return false;
        }
    }

    /**
     *  Gets the last syncronazation time 
     *  @param <sting> $sourceModule modulename to which sync time should return
     *  @return <date> last syncronazation time OR <boolean> false when date not present  
     */
    public static function getLastSyncTime($sourceModule) {
        $db = PearDatabase::getInstance();
        self::intialiseUpdateSchema();
        $user = Users_Record_Model::getCurrentUserModel();
        $result = $db->pquery('SELECT lastsynctime FROM ottocrat_google_sync WHERE user=? AND googlemodule=?', array($user->id, $sourceModule));
        if ($result && $db->num_rows($result) > 0) {
            $row = $db->fetch_array($result);
            return $row['lastsynctime'];
        } else {
            return false;
        }
    }

    /**
     *  Get the callback url for a module
     * @global type $site_URL
     * @param <object> $request
     * @param <array> $options any extra parameter add to url
     * @return string callback url
     */
    static function getCallbackUrl($request, $options = array()) {
        global $site_URL;

        $callback = rtrim($site_URL, '/') . "/".Ottocrat_Request:: encryptLink("index.php?module=" . $request['module'] . "&view=List&sourcemodule=" . $request['sourcemodule']);
        foreach ($options as $key => $value) {
            $callback.="&" . $key . "=" . $value;
        }
        return $callback;
    }
    
    static function hasSettingsForUser($userId) {
        $db = PearDatabase::getInstance();
        $sql = 'SELECT 1 FROM ' . self::settings_table_name . ' WHERE user = ?';
        $result = $db->pquery($sql, array($userId));
        if($db->num_rows($result) > 0){
            return true;
        }
        return false;
    }

    static function saveSettings($request) {
        $db = PearDatabase::getInstance();
        $user = Users_Record_Model::getCurrentUserModel();
        $userId = $user->getId();
        $source_module = $request->get('sourcemodule');
        $google_group = $request->get('google_group');
        $sync_direction = $request->get('sync_direction');
        if(Google_Utils_Helper::hasSettingsForUser($userId)) {
            $sql = 'UPDATE ' . self::settings_table_name . ' SET clientgroup = ?, direction = ?';
            $params = array($google_group,$sync_direction);
            $db->pquery($sql, $params);
        } else {
            $sql = 'INSERT INTO ' . self::settings_table_name . ' VALUES (?,?,?,?)';
            $params = array($userId,$source_module,$google_group,$sync_direction);
            $db->pquery($sql, $params);
        }
    }
    
    static function saveFieldMappings($sourceModule, $fieldMappings) {
        $db = PearDatabase::getInstance();
        $user = Users_Record_Model::getCurrentUserModel();
        $sql = 'SELECT 1 FROM ' . self::fieldmapping_table_name . ' WHERE user = ?';
        $res = $db->pquery($sql,array($user->getId()));
        $sqlParams = array();
        if($db->num_rows($res)) {
            $sql = 'DELETE FROM ' . self::fieldmapping_table_name . ' WHERE user = ?';
            $db->pquery($sql,array($user->getId()));
        }
        $sql = 'INSERT INTO ' . self::fieldmapping_table_name . ' (ottocrat_field,google_field,google_field_type,google_custom_label,user) VALUES ';
        foreach($fieldMappings as $fieldMap) {
            $fieldMap['user'] = $user->getId();
            $values = '(' . generateQuestionMarks($fieldMap) . '), ';
            $params = array();
            foreach($fieldMap as $field) {
               $params[] = $field;
            }
            $sqlParams = array_merge($sqlParams,$params);
            $sql .= $values;
        }
        $sql = rtrim($sql,', ');
        $db->pquery($sql,$sqlParams);
    }
    
    static function getSelectedContactGroupForUser($user = false) {
        if(!$user) $user = Users_Record_Model::getCurrentUserModel();
        $userId = $user->getId();
        if(!Google_Utils_Helper::hasSettingsForUser($userId)) {
            return ''; // defaults to all - other contacts groups
        } else {
            $db = PearDatabase::getInstance();
            $sql = 'SELECT clientgroup FROM ' . self::settings_table_name . ' WHERE user = ?';
            $result = $db->pquery($sql, array($userId));
            return $db->query_result($result, 0, 'clientgroup');
        }
    }
    
    static function getSyncDirectionForUser($user = false) {
        if(!$user) $user = Users_Record_Model::getCurrentUserModel();
        if(!Google_Utils_Helper::hasSettingsForUser($user->getId())) {
            return '11'; // defaults to bi-directional sync
        } else {
            $db = PearDatabase::getInstance();
            $sql = 'SELECT direction FROM ' . self::settings_table_name . ' WHERE user = ?';
            $result = $db->pquery($sql, array($user->getId()));
            return $db->query_result($result, 0, 'direction');
        }
    }
    
    static function getFieldMappingForUser($user = false) {
        if(!$user) $user = Users_Record_Model::getCurrentUserModel();
        $db = PearDatabase::getInstance();
        $fieldmapping = array(
            'salutationtype' => array(
                'google_field_name' => 'gd:namePrefix',
                'google_field_type' => '',
                'google_custom_label' => ''
            ),
            'firstname' => array(
                'google_field_name' => 'gd:givenName',
                'google_field_type' => '',
                'google_custom_label' => ''
            ),
            'lastname' => array(
                'google_field_name' => 'gd:familyName',
                'google_field_type' => '',
                'google_custom_label' => ''
            ),
            'title' => array(
                'google_field_name' => 'gd:orgTitle',
                'google_field_type' => '',
                'google_custom_label' => ''
            ),
            'account_id' => array(
                'google_field_name' => 'gd:orgName',
                'google_field_type' => '',
                'google_custom_label' => ''
            ),
            'birthday' => array(
                'google_field_name' => 'gContact:birthday',
                'google_field_type' => '',
                'google_custom_label' => ''
            ),
            'email' => array(
                'google_field_name' => 'gd:email',
                'google_field_type' => 'home',
                'google_custom_label' => ''
            ),
            'secondaryemail' => array(
                'google_field_name' => 'gd:email',
                'google_field_type' => 'work',
                'google_custom_label' => ''
            ),
            'mobile' => array(
                'google_field_name' => 'gd:phoneNumber',
                'google_field_type' => 'mobile',
                'google_custom_label' => ''
            ),
            'phone' => array(
                'google_field_name' => 'gd:phoneNumber',
                'google_field_type' => 'work',
                'google_custom_label' => ''
            ),
            'homephone' => array(
                'google_field_name' => 'gd:phoneNumber',
                'google_field_type' => 'home',
                'google_custom_label' => ''
            ),
            'mailingaddress' => array(
                'google_field_name' => 'gd:structuredPostalAddress',
                'google_field_type' => 'home',
                'google_custom_label' => ''
            ),
            'otheraddress' => array(
                'google_field_name' => 'gd:structuredPostalAddress',
                'google_field_type' => 'work',
                'google_custom_label' => ''
            ),
            'description' => array(
                'google_field_name' => 'content',
                'google_field_type' => '',
                'google_custom_label' => ''
            )  
        );
        $sql = 'SELECT ottocrat_field,google_field,google_field_type,google_custom_label FROM ' . self::fieldmapping_table_name . ' WHERE user = ?';
        $result = $db->pquery($sql,array($user->getId()));
        for($i=0;$i<$db->num_rows($result);$i++) {  
            $row = $db->fetch_row($result);
            $fieldmapping[$row['ottocrat_field']] = array(
                'google_field_name' => $row['google_field'],
                'google_field_type' => $row['google_field_type'],
                'google_custom_label' => $row['google_custom_label']
            );
        }
        return $fieldmapping;
    }
}
