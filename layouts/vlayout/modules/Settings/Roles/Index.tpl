{*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************}
{strip}
<div class="container-fluid">
	<div class="widget_header row-fluid">
		<div class="span8">
			<h3>{vtranslate($MODULE, $QUALIFIED_MODULE)}</h3>
	</div>	
	</div>
	<hr>
    Roles represent the hierarchical position of the individual users (employees) of your organization.
    <ul>
    <li>It plays a vital role in <a id="roleaccess"><b>controlling record access</b></a>.When sharing access for a module is set to private, a user's role in the role hierarchy determines what records he/she can access.</li>
    <li>A user can only view own records (i.e., records assigned to that user), and records assigned to users with a lower role.</li>
    <li>Role holds a position in a company (ex:sales manager) and can be assigned to multiple users of same functionality.</li>
    <li>A role can also be assigned to multiple profiles.This can come handy if same person holds two different positions.For instance, 'Rahul' is a Sales manager, but he also participates in support operations; therefore, you could create a role called 'sales and support manager' and assign both support and sales profiles to him.</li>
    <li>Each role also specifies who they report to, creating a hierarchy.</li>
    </ul>

    <div class="clearfix treeView">
		<ul>
			<li data-role="{$ROOT_ROLE->getParentRoleString()}" data-roleid="{$ROOT_ROLE->getId()}">
				<div class="toolbar-handle">
					<a href="javascript:;" class="btn btn-inverse draggable droppable">{$ROOT_ROLE->getName()}</a>
					<div class="toolbar" title="{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}">
						&nbsp;<a href="{$ROOT_ROLE->getCreateChildUrl()}" data-url="{$ROOT_ROLE->getCreateChildUrl()}" data-action="modal"><span class="icon-plus-sign"></span></a>
					</div>
				</div>
				{assign var="ROLE" value=$ROOT_ROLE}
				{include file=vtemplate_path("RoleTree.tpl", "Settings:Roles")}
			</li>
		</ul>
	</div>
</div>
{/strip}