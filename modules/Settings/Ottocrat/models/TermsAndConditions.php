<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Ottocrat_TermsAndConditions_Model extends Ottocrat_Base_Model{
    
    const tableName = 'ottocrat_inventory_tandc';
    
    public function getText(){
        return $this->get('tandc');
    }
    
    public function setText($text){
        return $this->set('tandc',$text);
    }
    
    public function getType(){
        return "Inventory";
    }
    
    public function save() {
        $db = PearDatabase::getInstance();
        $query = 'SELECT 1 FROM '.self::tableName;
        $result = $db->pquery($query,array());
        if($db->num_rows($result) > 0) {
            $query = 'UPDATE '.self::tableName.' SET tandc=?';
            $params = array($this->getText());
        }else{
            $query = 'INSERT INTO '.self::tableName.' (id,type,tandc) VALUES(?,?,?)';
            $params = array($db->getUniqueID(self::tableName, $this->getType(), $this->getText()));
        }
        $result = $db->pquery($query, $params);
    }
    
    public static function getInstance() {
        $db = PearDatabase::getInstance();
        $query = 'SELECT tandc FROM '.self::tableName;
        $result = $db->pquery($query,array());
        $instance = new self();
        if($db->num_rows($result) > 0) {
            $text = $db->query_result($result,0,'tandc');
            $instance->setText($text);
        }
        return $instance;
    }
}