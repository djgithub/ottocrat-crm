<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/modules/SMSNotifier/SMSNotifier.php');

class SMSNotifier_Record_Model extends Ottocrat_Record_Model {

	public static function SendSMS($message, $toNumbers, $currentUserId, $recordIds, $moduleName) {
		SMSNotifier::sendsms($message, $toNumbers, $currentUserId, $recordIds, $moduleName);
	}

	public function checkStatus() {
		$statusDetails = SMSNotifier::getSMSStatusInfo($this->get('id'));

		$statusColor = $this->getColorForStatus($statusDetails[0]['status']);

		$this->setData($statusDetails[0]);

		return $this;
	}

	public function getCheckStatusUrl() {
		return "index.php?module=".$this->getModuleName()."&view=CheckStatus&record=".$this->getId();
	}

	public function getColorForStatus($smsStatus) {
		if ($smsStatus == 'Processing') {
			$statusColor = '#FFFCDF';
		} elseif ($smsStatus == 'Dispatched') {
			$statusColor = '#E8FFCF';
		} elseif ($smsStatus == 'Failed') {
			$statusColor = '#FFE2AF';
		} else {
			$statusColor = '#FFFFFF';
		}
		return $statusColor;
	}
}