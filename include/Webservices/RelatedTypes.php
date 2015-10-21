<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

function vtws_relatedtypes($elementType, $user) {
    global $adb, $log;

    $allowedTypes = vtws_listtypes(null, $user);

    $webserviceObject = OttocratWebserviceObject::fromName($adb, $elementType);
    $handlerPath  = $webserviceObject->getHandlerPath();
    $handlerClass = $webserviceObject->getHandlerClass();

    require_once $handlerPath;
    $handler = new $handlerClass($webserviceObject, $user, $adb, $log);
    $meta = $handler->getMeta();
    $tabid = $meta->getTabId();

    $sql = "SELECT ottocrat_relatedlists.label, ottocrat_tab.name, ottocrat_tab.isentitytype FROM ottocrat_relatedlists 
            INNER JOIN ottocrat_tab ON ottocrat_tab.tabid=ottocrat_relatedlists.related_tabid 
            WHERE ottocrat_relatedlists.tabid=? AND ottocrat_tab.presence = 0";

    $params = array($tabid);
    $rs = $adb->pquery($sql, $params);

    $return = array('types' => array(), 'information' => array());

    while ($row = $adb->fetch_array($rs)) {
        if (in_array($row['name'], $allowedTypes['types'])) {
            $return['types'][] = $row['name'];
            // There can be same module related under different label - so label is our key.
            $return['information'][$row['label']] = array(
                'name' => $row['name'],
                'label'=> $row['label'],
                'isEntity' => $row['isentitytype']
            );
        }
    }

	return $return;
}

