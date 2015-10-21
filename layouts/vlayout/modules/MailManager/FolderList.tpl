{*<!--/************************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/-->*}

{strip}
{if $FOLDERS}
    <div id="foldersList" class="row-fluid">
        <div class="span10">
            <ul class="nav nav-list">
                {foreach item=FOLDER from=$FOLDERS}
                    <li>
                        <a class="mm_folder" id='_mailfolder_{$FOLDER->name()}' href='#{$FOLDER->name()}' onclick="MailManager.clearSearchString(); MailManager.folder_open('{$FOLDER->name()}'); ">{if $FOLDER->unreadCount()}<b>{$FOLDER->name()} ({$FOLDER->unreadCount()})</b>{else}{$FOLDER->name()}{/if}</a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
{/if}
{/strip}