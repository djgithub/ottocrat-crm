<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

include_once 'include/Webservices/QueryRelated.php';

function vtws_retrieve_related($id, $relatedType, $relatedLabel, $user) {
    $query = 'SELECT * FROM ' . $relatedType;
    return vtws_query_related($query, $id, $relatedLabel, $user);
}
