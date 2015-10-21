<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

function GetRelatedList($module,$relatedmodule,$focus,$query,$button,$returnset,$id='',$edit_val='',$del_val='',$skipActions=false) {
	return array( 'query' => $query , 'entries' => array() );
}

/**
 * Function that returns Activity History Query
 * @return <String>
 */
function GetHistory($parentmodule,$query,$id){
    return array('query' => $query);
}
?>
