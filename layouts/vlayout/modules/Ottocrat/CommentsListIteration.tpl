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
{if !empty($CHILD_COMMENTS_MODEL)}
<ul class="liStyleNone">
	{foreach item=COMMENT from=$CHILD_COMMENTS_MODEL}
		<li class="commentDetails">
		{include file='CommentThreadList.tpl'|@vtemplate_path COMMENT=$COMMENT}
		{assign var=CHILD_COMMENTS value=$COMMENT->getChildComments()}
		{if !empty($CHILD_COMMENTS)}
			{include file='CommentsListIteration.tpl'|@vtemplate_path CHILD_COMMENTS_MODEL=$COMMENT->getChildComments()}
		{/if}
		</li>
		<br>
	{/foreach}
</ul>
{/if}
{/strip}