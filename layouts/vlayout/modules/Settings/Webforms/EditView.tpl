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
{include file="EditViewBlocks.tpl"|@vtemplate_path:Ottocrat}
<div class="targetFieldsTableContainer">
	{include file="FieldsEditView.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
</div>
<br>	
{include file="EditViewActions.tpl"|@vtemplate_path:Ottocrat}
<div class="row-fluid" style="margin-bottom:150px;"></div>
{/strip}