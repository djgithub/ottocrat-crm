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
<br>
<center>
	<footer class="noprint">
		<div class="vtFooter">
			<p>
				{vtranslate('POWEREDBY')} {$OTTOCRAT_VERSION} &nbsp;
				&copy; 2004 - {date('Y')}&nbsp&nbsp;
				<a href="//www.ottocrat.com" target="_blank">ottocrat.com</a>
				&nbsp;|&nbsp;
				<a href="#" onclick="window.open('copyright.html','copyright', 'height=115,width=575').moveTo(210,620)">{vtranslate('LBL_READ_LICENSE')}</a>
				&nbsp;|&nbsp;
				<a href="https://www.ottocrat.com/privacy-policy" target="_blank">{vtranslate('LBL_PRIVACY_POLICY')}</a>
			</p>
		</div>
	</footer>
</center>
{include file='JSResources.tpl'|@vtemplate_path}
</div>
