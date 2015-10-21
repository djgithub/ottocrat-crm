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
{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
{assign var=EVENTS_MODULE_MODEL value=Ottocrat_Module_Model::getInstance('Events')}
{assign var=EVENT_STATUS_FIELD_MODEL value=$EVENTS_MODULE_MODEL->getField('eventstatus')}
{assign var=EVENT_STAUTS_PICKLIST_VALUES value=$EVENT_STATUS_FIELD_MODEL->getPicklistValues()}
{assign var=PICKLIST_VALUES value=array_merge($FIELD_MODEL->getPicklistValues(),$EVENT_STAUTS_PICKLIST_VALUES)}
{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
    <select class="select2 listSearchContributor" name="{$FIELD_MODEL->get('name')}" multiple style="width:150px;" data-fieldinfo='{$FIELD_INFO|escape}'>
        {foreach item=PICKLIST_LABEL key=PICKLIST_KEY from=$PICKLIST_VALUES}
            <option value="{$PICKLIST_KEY}" {if in_array($PICKLIST_KEY,$SEARCH_VALUES)} selected{/if}>{$PICKLIST_LABEL}</option>
        {/foreach}
    </select>
{/strip}