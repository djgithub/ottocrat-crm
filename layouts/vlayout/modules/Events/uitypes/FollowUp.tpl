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
{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
{assign var="currentDate" value=Ottocrat_Date_UIType::getDisplayDateValue('')}
{assign var="time" value=Ottocrat_Time_UIType::getDisplayTimeValue(null)}
{assign var="currentTimeInOttocratFormat" value=Ottocrat_Time_UIType::getTimeValueInAMorPM($time)}
{if $COUNTER eq 2}
</tr><tr class="{if !($SHOW_FOLLOW_UP)}hide {/if}followUpContainer massEditActiveField">
	{assign var=COUNTER value=1}
{else}
	{assign var=COUNTER value=$COUNTER+1}
{/if}
{assign var=FOLLOW_UP_LABEL value={vtranslate('LBL_HOLD_FOLLOWUP_ON',$MODULE)}}
<td class="fieldLabel">
	<label class="muted pull-right marginRight10px">
		<input name="followup" type="checkbox" class="alignTop" {if $FOLLOW_UP_STATUS} checked{/if}/>
		{$FOLLOW_UP_LABEL}
	</label>	
</td>
{$FIELD_INFO['label'] = {$FOLLOW_UP_LABEL}}
<td class="fieldValue">
	<div>
		<div class="input-append row-fluid">
			<div class="span10 row-fluid date">
				<input name="followup_date_start" type="text" class="span9 dateField" data-date-format="{$dateFormat}" type="text"  data-fieldinfo= '{Ottocrat_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'
					   value="{if !empty($FOLLOW_UP_DATE)}{$FOLLOW_UP_DATE}{else}{$currentDate}{/if}" data-validation-engine="validate[funcCall[Ottocrat_greaterThanDependentField_Validator_Js.invokeValidation]]" />
				<span class="add-on"><i class="icon-calendar"></i></span>
			</div>	
		</div>		
	</div>
	<div>
		<div class="input-append time">
			<input type="text" name="followup_time_start" class="timepicker-default input-small" 
				   value="{if !empty($FOLLOW_UP_TIME)}{$FOLLOW_UP_TIME}{else}{$currentTimeInOttocratFormat}{/if}" />
			<span class="add-on cursorPointer">
				<i class="icon-time"></i>
			</span>
		</div>	
	</div>
</td>
<td></td><td></td>