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
<div class="container-fluid">
	<div class="widget_header row-fluid">
		<h3>{vtranslate($MODULE, $QUALIFIED_MODULE)}</h3>
	</div>
	<hr>
    {if $MODULE eq 'Users'}
        <div>User represent employee of your organization who perform the day-to-day activities using the CRM. <b>Ottocrat</b> features two types of users: Administrator, has access to register new users, assign applications, lock users etc; and User, has limited access and can only use applications assigned by admin.When adding a user you should select the role.Privileges of a user depends upon the role you would assign.You can also set admin privileges; it then,gives access to Settings page.User privileges are not shown.User information cannot be visible to other users.Access privileges of a user also depends upon settings enabled in <b><i>Sharing Access</i></b>.</div>
    {/if}
	<div class="row-fluid">
		<span class="span4 btn-toolbar">
            <span class="btn-group listViewMassActions">
                {if $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
                    <button class="btn dropdown-toggle" data-toggle="dropdown"><strong>{vtranslate('LBL_ACTIONS', $MODULE)}</strong>&nbsp;&nbsp;<i class="caret"></i></button>
                    <ul class="dropdown-menu">
                        {foreach item=LISTVIEW_ADVANCEDACTIONS from=$LISTVIEW_LINKS['LISTVIEW']}
                            <li id="{$MODULE}_listView_advancedAction_{Ottocrat_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_ADVANCEDACTIONS->getLabel())}"><a {if stripos($LISTVIEW_ADVANCEDACTIONS->getUrl(), 'javascript:')===0} href="javascript:void(0);" onclick='{$LISTVIEW_ADVANCEDACTIONS->getUrl()|substr:strlen("javascript:")};'{else} href='{$LISTVIEW_ADVANCEDACTIONS->getUrl()}' {/if}>{vtranslate($LISTVIEW_ADVANCEDACTIONS->getLabel(), $MODULE)}</a></li>
                        {/foreach}
                    </ul>
                {/if}
            </span>
			{if $ADD_USER_FLAG}
			{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
			<span class="btn-group">
			<button class="btn addButton" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
					{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"' {/if}>
				<i class="icon-plus"></i>&nbsp;
				<strong>{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}</strong>
			</button>
			</span>
			{/foreach}{/if}
		</span>
        <div class="span4 btn-toolbar">
            <select class="select2" id="usersFilter" name="status" style="min-width:350px;">
                <option value="Active">{vtranslate('LBL_ACTIVE_USERS', $QUALIFIED_MODULE)}</option>
                <option value="Inactive">{vtranslate('LBL_INACTIVE_USERS', $QUALIFIED_MODULE)}</option>
            </select>
        </div>
		<span class="span4 btn-toolbar">
			{include file='ListViewActions.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
		</span>
	</div>
	<div class="listViewContentDiv" id="listViewContents">
{/strip}
