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
{assign var="FIELD_INFO" value=Ottocrat_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var=CURRENCY_LIST value=$FIELD_MODEL->getCurrencyList()}
<select class="chzn-select" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Ottocrat_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}'>
{foreach item=CURRENCY_NAME key=CURRENCY_ID from=$CURRENCY_LIST}
	<option value="{$CURRENCY_ID}" data-picklistvalue= '{$CURRENCY_ID}' {if $FIELD_MODEL->get('fieldvalue') eq $CURRENCY_ID} selected {/if}>{vtranslate($CURRENCY_NAME, $MODULE)}</option>
{/foreach}
</select>
{/strip}