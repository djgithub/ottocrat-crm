<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************ */

function vtws_create($elementType, $element, $user) {

    $types = vtws_listtypes(null, $user);
    if (!in_array($elementType, $types['types'])) {
        throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to perform the operation is denied");
    }

    global $log, $adb;

    // Cache the instance for re-use
	if(!isset($vtws_create_cache[$elementType]['webserviceobject'])) {
		$webserviceObject = OttocratWebserviceObject::fromName($adb,$elementType);
		$vtws_create_cache[$elementType]['webserviceobject'] = $webserviceObject;
	} else {
		$webserviceObject = $vtws_create_cache[$elementType]['webserviceobject'];
	}
	// END			

    $handlerPath = $webserviceObject->getHandlerPath();
    $handlerClass = $webserviceObject->getHandlerClass();

    require_once $handlerPath;

    $handler = new $handlerClass($webserviceObject, $user, $adb, $log);
    $meta = $handler->getMeta();
    if ($meta->hasWriteAccess() !== true) {
        throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to write is denied");
    }

    $referenceFields = $meta->getReferenceFieldDetails();
    foreach ($referenceFields as $fieldName => $details) {
        if (isset($element[$fieldName]) && strlen($element[$fieldName]) > 0) {
            $ids = vtws_getIdComponents($element[$fieldName]);
            $elemTypeId = $ids[0];
            $elemId = $ids[1];
            $referenceObject = OttocratWebserviceObject::fromId($adb, $elemTypeId);
            if (!in_array($referenceObject->getEntityName(), $details)) {
                throw new WebServiceException(WebServiceErrorCode::$REFERENCEINVALID,
                        "Invalid reference specified for $fieldName");
            }
			if ($referenceObject->getEntityName() == 'Users') {
				if(!$meta->hasAssignPrivilege($element[$fieldName])) {
                    throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Cannot assign record to the given user");
				}
			}
            if (!in_array($referenceObject->getEntityName(), $types['types']) && $referenceObject->getEntityName() != 'Users') {
                throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED,
                        "Permission to access reference type is denied" . $referenceObject->getEntityName());
            }
        } else if ($element[$fieldName] !== NULL) {
            unset($element[$fieldName]);
        }
    }


    if ($meta->hasMandatoryFields($element)) {

        $ownerFields = $meta->getOwnerFields();
        if (is_array($ownerFields) && sizeof($ownerFields) > 0) {
            foreach ($ownerFields as $ownerField) {
                if (isset($element[$ownerField]) && $element[$ownerField] !== null &&
                        !$meta->hasAssignPrivilege($element[$ownerField])) {
                    throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Cannot assign record to the given user");
                }
            }
        }
        $entity = $handler->create($elementType, $element);
        VTWS_PreserveGlobal::flush();
        return $entity;
    } else {

        return null;
    }
}
?>