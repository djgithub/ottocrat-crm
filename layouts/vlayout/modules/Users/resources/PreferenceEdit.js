/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

Users_Edit_Js("Users_PreferenceEdit_Js",{
	
	/**
	 * Function to register change event for currency seperator
	 */
	registerChangeEventForCurrencySeperator : function(){
		var form = jQuery('form');
		jQuery('[name="currency_decimal_separator"]',form).on('change',function(e){
			var element = jQuery(e.currentTarget);
			var selectedValue = element.val();
			var groupingSeperatorValue = jQuery('[name="currency_grouping_separator"]',form).data('selectedValue');
			if(groupingSeperatorValue == selectedValue){
				var message = app.vtranslate('JS_DECIMAL_SEPERATOR_AND_GROUPING_SEPERATOR_CANT_BE_SAME');
				var params = {
					text: message,
					type: 'error'
				};
				Ottocrat_Helper_Js.showMessage(params);
				var previousSelectedValue = element.data('selectedValue');
				element.find('option').removeAttr('selected');
				element.find('option[value="'+previousSelectedValue+'"]').attr('selected','selected');
				element.trigger("liszt:updated");
			}else{
				element.data('selectedValue',selectedValue);
			}
		})
		jQuery('[name="currency_grouping_separator"]',form).on('change',function(e){
			var element = jQuery(e.currentTarget);
			var selectedValue = element.val();
			var decimalSeperatorValue = jQuery('[name="currency_decimal_separator"]',form).data('selectedValue');
			if(decimalSeperatorValue == selectedValue){
				var message = app.vtranslate('JS_DECIMAL_SEPERATOR_AND_GROUPING_SEPERATOR_CANT_BE_SAME');
				var params = {
					text: message,
					type: 'error'
				};
				Ottocrat_Helper_Js.showMessage(params);
				var previousSelectedValue = element.data('selectedValue');
				element.find('option').removeAttr('selected');
				element.find('option[value="'+previousSelectedValue+'"]').attr('selected','selected');
				element.trigger("liszt:updated");
			}else{
				element.data('selectedValue',selectedValue);
			}
		})
	}
},{
	
	/**
	 * register Events for my preference
	 */
	registerEvents : function(){
		this._super();
		Users_PreferenceEdit_Js.registerChangeEventForCurrencySeperator();
	}
});