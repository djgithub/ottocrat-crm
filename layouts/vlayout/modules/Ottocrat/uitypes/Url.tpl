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
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
<input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="text" class="input-large validate[custom[url]]" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Ottocrat_Base_Validator_Js.invokeValidation]]"
 value="{$FIELD_MODEL->get('fieldvalue')}" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}/>
{/strip}