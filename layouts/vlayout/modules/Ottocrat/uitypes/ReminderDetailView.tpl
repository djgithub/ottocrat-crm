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
{assign var=REMINDER_VALUES value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId())}
{if $REMINDER_VALUES eq ''}
    {vtranslate('LBL_NO', $MODULE)}
{else}
    {$REMINDER_VALUES}{vtranslate('LBL_BEFORE_EVENT', $MODULE)}
{/if}