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
{if $ERROR} 
    Error, please retry setting the password!! 
{else} 
<h4>Loading .... </h4>
<form class="form-horizontal" name="login" id="login" method="post" action="../../../index.php?module=Users&action=Login&mode=reset">
	<input type=hidden name="username" value="{$USERNAME}" >
	<input type=hidden name="password" value="{$PASSWORD}" >
</form>
<script type="text/javascript">
    function autoLogin () {
        var form = document.getElementById("login");
        form.submit();
    }
    window.onload = autoLogin;
</script>
{/if}