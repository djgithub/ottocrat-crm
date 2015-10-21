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
{strip}
    <div id="popupPageContainer" class="contentsDiv">
        <div class="paddingLeftRight10px">{include file='PopupSearch.tpl'|@vtemplate_path:$MODULE_NAME}
            <form id="popupPage" action="javascript:void(0)">
                <div id="popupContents">{include file='ProductPriceBookPopupContents.tpl'|@vtemplate_path:$PARENT_MODULE}</div>
            </form>
        </div>
        <input type="hidden" class="triggerEventName" value="{$smarty.request.triggerEventName}"/>
    </div>
</div>
{/strip}