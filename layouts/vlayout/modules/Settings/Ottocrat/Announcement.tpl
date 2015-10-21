{*<!--
/*********************************************************************************
** The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
<div class="container-fluid" id="AnnouncementContainer">
	<div class="widget_header row-fluid">
		<div class="row-fluid"><h3>{vtranslate('LBL_ANNOUNCEMENTS', $QUALIFIED_MODULE)}</h3></div>
	</div>
	<hr>

    <div class="contents row-fluid">
        <textarea class="announcementContent textarea-autosize boxSizingBorderBox" rows="3" placeholder="{vtranslate('LBL_ENTER_ANNOUNCEMENT_HERE', $QUALIFIED_MODULE)}" style="width:100%">{$ANNOUNCEMENT->get('announcement')}</textarea>
        <div class="row-fluid textAlignCenter">
			<br>
            <button class="btn btn-success saveAnnouncement hide"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
        </div>
    </div>
</div>
{/strip}