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
	{if $MODULE eq 'Groups'}
	<div>{vtranslate('Groups Desc', $QUALIFIED_MODULE)}</div>
	{/if}
    {if $MODULE eq 'PickListDependency'}
        <div>{vtranslate('LBL_PICKLIST_DEPENDENCY_TEXT', $QUALIFIED_MODULE)}</div>
    {/if}

    {if $MODULE eq 'Profiles'}
    <div>
    Profiles provide you the fine grained access control to Ottocrat CRM. Profiles can be used to regulate, or completely disable user's access on modules, fields, and other actions (eg.Import).
   <ul> <li>With profiles you can set the user privileges to delete, create/edit or view data.</li>
       <li>	Like <b><i>Sharing Access</i></b>, profiles also play vital role in ensuring security by limiting the activities on records.Please note that the *settings of the global privileges are always superior to the other privilege settings.</li
       <li>	Roles are based on profiles.One or more profiles can be linked to Roles.</li>
       <li>	<b>Ottocrat CRM</b> comes with a set of pre-defined profiles(ex: 'Administrator') which you can use and change but not delete.</li>
    </div>
    {/if}

    <div class="row-fluid">
		<span class="span8 btn-toolbar">
			{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
			<button class="btn addButton" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
					{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"' {/if}>
				<i class="icon-plus"></i>&nbsp;
				<strong>{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}</strong>
			</button>
			{/foreach}
		</span>
		<span class="span4 btn-toolbar">
			{include file='ListViewActions.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
		</span>
	</div>
	<div class="clearfix"></div>
	<div class="listViewContentDiv" id="listViewContents">
{/strip}