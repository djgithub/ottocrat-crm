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
    <span class="span2">
        <img src="{vimage_path('summary_Tickets.png')}" class="summaryImg" />
    </span>
    <span class="span10 margin0px">
        <span class="row-fluid">
            <h4 class="recordLabel" title="{$RECORD->getName()}">
                {foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
                    {if $FIELD_MODEL->getPermissions()}
                        <span class="{$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
                    {/if}
                {/foreach}
            </h4>
        </span>
        {assign var=PRIORITY value=$RECORD->get('ticketpriorities')}
        {if !empty($PRIORITY)}
            <span class="row-fluid">
                <span class="muted">{vtranslate('Priority',$MODULE_NAME)} - </span>
                {$RECORD->getDisplayValue('ticketpriorities')}
            </span>
        {/if}
    </span>
{/strip}