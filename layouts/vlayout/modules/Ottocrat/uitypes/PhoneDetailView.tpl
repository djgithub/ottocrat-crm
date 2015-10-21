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

{* To check current user has permission to make outbound call. If so, make all the detail view phone fields as links to call *}
{assign var=MODULE value='PBXManager'}
{assign var=MODULEMODEL value=Ottocrat_Module_Model::getInstance($MODULE)}
{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
{if $MODULEMODEL and $MODULEMODEL->isActive() and $FIELD_VALUE}
    {assign var=PERMISSION value=PBXManager_Server_Model::checkPermissionForOutgoingCall()}
    {if $PERMISSION}
        {assign var=PHONE_FIELD_VALUE value=$FIELD_VALUE}
        {assign var=PHONE_NUMBER value=$PHONE_FIELD_VALUE|regex_replace:"/[-()\s]/":""}
        <a class="phoneField" data-value="{$PHONE_NUMBER}" record="{$RECORD->getId()}" onclick="Ottocrat_PBXManager_Js.registerPBXOutboundCall('{$PHONE_NUMBER}',{$RECORD->getId()})">{$FIELD_MODEL->get('fieldvalue')}</a>
    {else}
        {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
    {/if}
{else}
    {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
{/if}
