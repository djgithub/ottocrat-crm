<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

class Ottocrat_Datetime_UIType extends Ottocrat_Date_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/DateTime.tpl';
	}
	
	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
		return $dateValue = self::getDisplayDateTimeValue($value);
	}
	
	/**
	 * Function to get Date and Time value for Display
	 * @param <type> $date
	 * @return <String>
	 */
	public static function getDisplayDateTimeValue($date) {
            $date = new DateTimeField($date);
            $dateValue = $date->getDisplayDateTimeValue();
            list($dateInUserFormat, $timeInUserFormat) = explode(' ', $dateValue);

            $currentUser = Users_Record_Model::getCurrentUserModel();
            if ($currentUser->get('hour_format') == '12')
                $timeInUserFormat = Ottocrat_Time_UIType::getTimeValueInAMorPM($timeInUserFormat);

            return $dateInUserFormat . ' ' . $timeInUserFormat;
        }

    /**
	 * Function to get Date and Time value for Display
	 * @param <type> $date
	 * @return <String>
	 */
	public static function getDBDateTimeValue($date) {
		$date = new DateTimeField($date);
		return $date->getDBInsertDateTimeValue();
	}
	
	/**
	 * Function to get the datetime value in user preferred hour format
	 * @param <type> $dateTime
	 * @return <String> date and time with hour format
	 */
	public static function getDateTimeValue($dateTime){
		return Ottocrat_Util_Helper::convertDateTimeIntoUsersDisplayFormat($dateTime);
	}
}