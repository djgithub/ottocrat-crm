{************************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************}
{strip}
	<div class="tagsContainer" id="tagCloud">
		{foreach from=$TAGS[1] item=TAG_ID key=TAG_NAME}
			<a class="tagName cursorPointer" data-tagid="{$TAG_ID}" rel="{$TAGS[0][$TAG_NAME]}">{$TAG_NAME}</a>&nbsp;		
		{/foreach}
	</div>
{/strip}	