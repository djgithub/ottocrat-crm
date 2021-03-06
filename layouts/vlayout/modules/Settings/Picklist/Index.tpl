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
	<div class="listViewPageDiv">
		<div class="listViewTopMenuDiv">
			<h3>{vtranslate('LBL_PICKLIST_EDITOR',$QUALIFIED_MODULE)}</h3>
            <hr>
			<div class="clearfix"></div>
		</div>
		<div class="listViewContentDiv" id="listViewContents" style="padding: 1%;">
			<div>As the name itself says, picklist is a dropdown field with list of options available, within which, only one option can be selected.For instance, Lead status in Leads module. Picklist Editor can be used to customize the picklist values in different modules.Select a role before performing global actions such as add, edit and, delete; as the picklist values vary across roles.</div>
			<br>
			<div class="row-fluid">
				<label class="fieldLabel span3"><strong>{vtranslate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)} </strong></label>
				<div class="span6 fieldValue">
					<select class="chzn-select" id="pickListModules">
						<optgroup>
							<option value="">{vtranslate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}</option>
							{foreach item=PICKLIST_MODULE from=$PICKLIST_MODULES}
								<option {if $SELECTED_MODULE_NAME eq $PICKLIST_MODULE->get('name')} selected="" {/if} value="{$PICKLIST_MODULE->get('name')}">{vtranslate($PICKLIST_MODULE->get('label'),$QUALIFIED_MODULE)}</option>
							{/foreach}	
						</optgroup>
					</select>
				</div>
			</div><br>
			<div id="modulePickListContainer">
				{include file="ModulePickListDetail.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
			</div>

			<div id="modulePickListValuesContainer">
                {if empty($NO_PICKLIST_FIELDS)}
                {include file="PickListValueDetail.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
                {/if}
            </div>
		</div>
	{/strip}	
