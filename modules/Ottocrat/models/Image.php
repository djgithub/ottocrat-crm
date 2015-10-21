<?php
/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Ottocrat Image Model Class
 */
class Ottocrat_Image_Model extends Ottocrat_Base_Model {

	/**
	 * Function to get the title of the Image
	 * @return <String>
	 */
	public function getTitle(){
		return $this->get('title');
	}

	/**
	 * Function to get the alternative text for the Image
	 * @return <String>
	 */
	public function getAltText(){
		return $this->get('alt');
	}

	/**
	 * Function to get the Image file path
	 * @return <String>
	 */
	public function getImagePath(){
		return Ottocrat_Theme::getImagePath($this->get('imagename'));
	}

	/**
	 * Function to get the Image file name
	 * @return <String>
	 */
	public function getImageFileName(){
		return $this->get('imagename');
	}

}