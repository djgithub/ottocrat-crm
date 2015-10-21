{*<!--
/*********************************************************************************
 ** The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *********************************************************************************/
-->*}
<div class="row-fluid paddingLeftRight10px">
    {foreach from=$RECORDS item=RECORD key=type }
        {if $type eq 'ottocrat'}
            <div class='row-fluid'>
                <span class="span12"><b>{vtranslate('LBL_UPDATES_CRM',$MODULE_NAME)}</b></span>
                <div class="row-fluid"><span class="span7 "> {vtranslate('LBL_ADDED',$MODULE_NAME)} :</span><span class='span5 '>{$RECORD['create']} </span></div>
                <div class="row-fluid"><span class="span7 "> {vtranslate('LBL_UPDATED',$MODULE_NAME)} :</span> <span class='span5 '>{$RECORD['update']} </span></div>
                <div class="row-fluid"><span class="span7 "> {vtranslate('LBL_DELETED',$MODULE_NAME)} :</span> <span class='span5 '>{$RECORD['delete']} </span></div>
                {if $RECORD['more']}
					<div class="row-fluid"><span style='background:#FFFBCF;' class="span11" title="{vtranslate('LBL_MORE_OTTOCRAT',$MODULE_NAME)}">{vtranslate('LBL_MORE_OTTOCRAT',$MODULE_NAME)}</span>
                {/if}    
            </div>
         {else}
            <div class='row-fluid'> 
                <span class="span12"><b>{vtranslate('LBL_UPDATES_GOOGLE',$MODULE_NAME)}</b></span>
                <div class="row-fluid"><span class="span7 "> {vtranslate('LBL_ADDED',$MODULE_NAME)} :</span><span class='span5 '>{$RECORD['create']} </span></div>
                <div class="row-fluid"><span class="span7 "> {vtranslate('LBL_UPDATED',$MODULE_NAME)} :</span> <span class='span5 '>{$RECORD['update']} </span></div>
                <div class="row-fluid"><span class="span7 "> {vtranslate('LBL_DELETED',$MODULE_NAME)} :</span> <span class='span5 '>{$RECORD['delete']} </span></div>
                {if $RECORD['more']}
					<div class="row-fluid"><span style='background:#FFFBCF;' class="span11" title="{vtranslate('LBL_MORE_GOOGLE',$MODULE_NAME)}">{vtranslate('LBL_MORE_GOOGLE',$MODULE_NAME)}</span>
                {/if}
            </div>
         {/if}   
    {/foreach}
    <div class='row-fluid'>
        {if $SYNCTIME}<p class="muted span12"><small title="{Ottocrat_Util_Helper::formatDateTimeIntoDayString($SYNCTIME)}">{vtranslate('LBL_SYNCRONIZED',$MODULE_NAME)} : {Ottocrat_Util_Helper::formatDateDiffInStrings($SYNCTIME)}</small></p>{/if}
    </div>
{if $NORECORDS}
        <input type="hidden" value='yes' id ='norefresh'/>
{/if}      
</div>
      
    
