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
	<input type="hidden" name="page" value="{$PAGING_MODEL->get('page')}" />
	<input type="hidden" name="pageLimit" value="{$PAGING_MODEL->get('limit')}" />
	{if $RELATED_MODULE && $RELATED_RECORDS}
		{assign var=FILENAME value=$RELATED_MODULE|cat:"SummaryWidgetContents.tpl"}
		{include file=$FILENAME|vtemplate_path:$MODULE RELATED_RECORDS=$RELATED_RECORDS}
	{/if}
{/strip}