{*<!--
/*********************************************************************************
** The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *
 ********************************************************************************/
-->*}

<button type="submit" name="next"  class="btn btn-success"
		onclick="return ImportJs.uploadAndParse();"><strong>{'LBL_NEXT_BUTTON_LABEL'|@vtranslate:$MODULE}</strong></button>
&nbsp;&nbsp;
<a name="cancel" class="cursorPointer cancelLink" value="{'LBL_CANCEL'|@vtranslate:$MODULE}" onclick="location.href='{php}echo Ottocrat_Request::encryptLink('index.php?module={$FOR_MODULE}&view=List');{/php}'">
		{'LBL_CANCEL'|@vtranslate:$MODULE}
</a>