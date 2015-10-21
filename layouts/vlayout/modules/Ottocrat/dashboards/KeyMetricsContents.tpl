{************************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************}
{strip}
<div style='padding:5px'>
	{foreach item=KEYMETRIC from=$KEYMETRICS}
	<div style='padding:5px'>
		<span class="pull-right">{$KEYMETRIC.count}</span>
		<a href="?module={$KEYMETRIC.module}&view=List&viewname={$KEYMETRIC.id}">{vtranslate($KEYMETRIC.name,$KEYMETRIC.module)}</a>
	</div>	
	{/foreach}
</div>
{/strip}
