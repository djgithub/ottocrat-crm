<?php
/*+**********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: ottocrat CRM Open source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_DraftFolder_Model extends MailManager_Folder_Model {

	public function hasPrevPage() {
		return ($this->mPageStart <= $this->mCount  && ($this->mPageCurrent > 0));
	}

	public function hasNextPage() {
		return ($this->mPageEnd < $this->mCount);
	}

	public function pageInfo() {
		$s = max(1, $this->mPageCurrent * $this->mPageLimit+1);
		$e = min($s+$this->mPageLimit-1, $this->mCount);
		$t = $this->mCount;
		return sprintf("%s - %s ".vtranslate('LBL_OF')." %s", $s, $e, $t);
	}
}
?>
