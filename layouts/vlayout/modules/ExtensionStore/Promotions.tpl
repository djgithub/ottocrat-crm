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
    <div class="banner-container">
        <div class="row-fluid"></div>
        <div class="banner">
            <ul class="bxslider">
                {foreach $PROMOTIONS as $PROMOTION}
                    <li>
                        {assign var=SUMMARY value=$PROMOTION->get('summary')}
                        {assign var=EXTENSION_NAME value=$PROMOTION->get('label')}
                        {if is_numeric($SUMMARY)}
                            {assign var=LOCATION_URL value=$PROMOTION->getLocationUrl($SUMMARY, $EXTENSION_NAME)}
                        {else}
                            {assign var=LOCATION_URL value={$SUMMARY}}
                        {/if}
                        <a onclick="window.open('{$LOCATION_URL}')"><img src="{if $PROMOTION->get('bannerURL')}{$PROMOTION->get('bannerURL')}{/if}" title="{$PROMOTION->get('label')}" /></a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
{/strip}