/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/
Ottocrat_Base_Validator_Js("Ottocrat_Email_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var emailInstance = new Ottocrat_Email_Validator_Js();
		emailInstance.setElement(field);
		var response = emailInstance.validate();
		if(response != true){
			return emailInstance.getError();
		}
	}
},{

	/**
	 * Function to validate the email field data
	 */
	validate: function(){
		var fieldValue = this.getFieldValue();
		return this.validateValue(fieldValue);
	},
	/**
	 * Function to validate the email field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validateValue : function(fieldValue){
		var emailFilter = /^[_/a-zA-Z0-9*]+([!"#$%&'()*+,./:;<=>?\^_`{|}~-]?[a-zA-Z0-9/_/-])*@[a-zA-Z0-9]+([\_\-\.]?[a-zA-Z0-9]+)*\.([\-\_]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)?$/;
		var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/ ;

		if (!emailFilter.test(fieldValue)) {
			var errorInfo = app.vtranslate('JS_PLEASE_ENTER_VALID_EMAIL_ADDRESS');
			this.setError(errorInfo);
			return false;

		} else if (fieldValue.match(illegalChars)) {
			var errorInfo = app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS');
			this.setError(errorInfo);
			return false;
		}
        return true;
	}
});

Ottocrat_Base_Validator_Js("Ottocrat_PositiveNumber_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var positiveNumberInstance = new Ottocrat_PositiveNumber_Validator_Js();
		positiveNumberInstance.setElement(field);
		var response = positiveNumberInstance.validate();
		if(response != true){
			return positiveNumberInstance.getError();
		}
	}

},{

	/**
	 * Function to validate the Positive Numbers
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var fieldValue = this.getFieldValue();
		var negativeRegex= /(^[-]+\d+)$/ ;
		if(isNaN(fieldValue) || fieldValue < 0 || fieldValue.match(negativeRegex)){
			var errorInfo = app.vtranslate('JS_ACCEPT_POSITIVE_NUMBER');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
})

Ottocrat_Base_Validator_Js("Ottocrat_Integer_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var integerInstance = new Ottocrat_Integer_Validator_Js();
		integerInstance.setElement(field);
		var response = integerInstance.validate();
		if(response != true){
			return integerInstance.getError();
		}
	}
},{

	/**
	 * Function to validate the Integre field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var fieldValue = this.getFieldValue();
		var integerRegex= /(^[-+]?\d+)$/ ;
		var decimalIntegerRegex = /(^[-+]?\d?).\d+$/ ;
		if ((!fieldValue.match(integerRegex))) {
			if(!fieldValue.match(decimalIntegerRegex)){
				var errorInfo = app.vtranslate("JS_PLEASE_ENTER_INTEGER_VALUE");
				this.setError(errorInfo);
				return false;
			} else {
				return true;
			}
		} else{
			return true;
		}
	}
})

Ottocrat_PositiveNumber_Validator_Js("Ottocrat_Percentage_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var percentageInstance = new Ottocrat_Percentage_Validator_Js();
		percentageInstance.setElement(field);
		var response = percentageInstance.validate();
		if(response != true){
			return percentageInstance.getError();
		}
	}

},{

	/**
	 * Function to validate the percentage field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var response = this._super();
		if(response != true){
			return response;
		}else{
			var fieldValue = this.getFieldValue();
			if (fieldValue > 100) {
				var errorInfo = app.vtranslate('JS_PERCENTAGE_VALUE_SHOULD_BE_LESS_THAN_100');
				this.setError(errorInfo);
				return false;
			}
			return true;
		}
	}
});

Ottocrat_Base_Validator_Js('Ottocrat_Url_Validator_Js',{},{

	/**
	 * Function to validate the Url
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var fieldValue = this.getFieldValue();
		var regexp = /(^|\s)((https?:\/\/)?[\w-]+(\.[\w-]+)+\.?(:\d+)?(\/\S*)?)/gi;
		var result = regexp.test(fieldValue);
		if (!result ) {
			var errorInfo = app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS');//"Please enter valid url";
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
})

Ottocrat_Base_Validator_Js("Ottocrat_MultiSelect_Validator_Js",{

	invokeValidation : function(field, rules, i, options) {
		var validatorInstance = new Ottocrat_MultiSelect_Validator_Js ();
		validatorInstance.setElement(field);
		var result = validatorInstance.validate();
		if(result == true){
			return result;
		} else {
			return validatorInstance.getError();
		}
	}
},{
	/**
	 * Function to validate the Multi select
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var fieldInstance = this.getElement();
		var selectElementValue = fieldInstance.val();
		if(selectElementValue == null){
			var errorInfo = app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}

})


Ottocrat_Email_Validator_Js ("Ottocrat_MultiEmails_Validator_Js",{
	invokeValidation : function(field) {
		var validatorInstance = new Ottocrat_MultiEmails_Validator_Js ();
		validatorInstance.setElement(field);
		var result = validatorInstance.validate();
		if(!result){
			return validatorInstance.getError();
		}
	}
},{
	/**
	 * Function to validate the Multi select
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var fieldInstance = this.getElement();
		var fieldInstanceValue = fieldInstance.val();
		if(fieldInstanceValue != ''){
			var emailsArr = fieldInstanceValue.split(',');
			var i;
			for (i = 0; i < emailsArr.length; ++i) {
				var result =  this.validateValue(emailsArr[i]);
				if(result == false){
					return result;
				}
			}
			return true;
		}
	}

});

Ottocrat_PositiveNumber_Validator_Js("Ottocrat_GreaterThanZero_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){

		var GreaterThanZeroInstance = new Ottocrat_GreaterThanZero_Validator_Js();
		GreaterThanZeroInstance.setElement(field);
		var response = GreaterThanZeroInstance.validate();
		if(response != true){
			return GreaterThanZeroInstance.getError();
		}
	}

},{

	/**
	 * Function to validate the Positive Numbers and greater than zero value
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){

		var response = this._super();
		if(response != true){
			return response;
		}else{
			var fieldValue = this.getFieldValue();
			if(fieldValue == 0){
				var errorInfo = app.vtranslate('JS_VALUE_SHOULD_BE_GREATER_THAN_ZERO');
				this.setError(errorInfo);
				return false;
			}
		}
		return true;
	}
})

Ottocrat_PositiveNumber_Validator_Js("Ottocrat_WholeNumber_Validator_Js",{

    /**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var instance = new Ottocrat_WholeNumber_Validator_Js();
		instance.setElement(field);
		var response = instance.validate();
		if(response != true){
			return instance.getError();
		}
	}
},{

	/**
	 * Function to validate the Positive Numbers and whole Number
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var response = this._super();
		if(response != true){
			return response;
		}
		var field = this.getElement();
		var fieldValue = this.getFieldValue();
		var fieldData = field.data();
		var fieldInfo = fieldData.fieldinfo;
		if((fieldValue % 1) != 0){
			if(! jQuery.isEmptyObject(fieldInfo)){
				var errorInfo = app.vtranslate('INVALID_NUMBER_OF')+" "+fieldInfo.label;
			} else {
				var errorInfo = app.vtranslate('INVALID_NUMBER');
			}
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
})

Ottocrat_Base_Validator_Js("Ottocrat_lessThanToday_Validator_Js",{},{

	/**
	 * Function to validate the birthday field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldInfo = fieldData.fieldinfo;
		var fieldValue = this.getFieldValue();
		try{
			var fieldDateInstance = Ottocrat_Helper_Js.getDateInstance(fieldValue,fieldDateFormat);
		}
		catch(err){
			this.setError(err);
			return false;
		}
		fieldDateInstance.setHours(0,0,0,0);
		var todayDateInstance = new Date();
		todayDateInstance.setHours(0,0,0,0);
		var comparedDateVal =  todayDateInstance - fieldDateInstance;
		if(comparedDateVal <= 0){
			var errorInfo = fieldInfo.label+" "+app.vtranslate('JS_SHOULD_BE_LESS_THAN_CURRENT_DATE');
			this.setError(errorInfo);
			return false;
		}
                return true;
	}
})

Ottocrat_Base_Validator_Js("Ottocrat_lessThanOrEqualToToday_Validator_Js",{},{

	/**
	 * Function to validate the datesold field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldInfo = fieldData.fieldinfo;
		var fieldValue = this.getFieldValue();
		try{
			var fieldDateInstance = Ottocrat_Helper_Js.getDateInstance(fieldValue,fieldDateFormat);
		}
		catch(err){
			this.setError(err);
			return false;
		}
		fieldDateInstance.setHours(0,0,0,0);
		var todayDateInstance = new Date();
		todayDateInstance.setHours(0,0,0,0);
		var comparedDateVal =  todayDateInstance - fieldDateInstance;
		if(comparedDateVal < 0){
			var errorInfo = fieldInfo.label+" "+app.vtranslate('JS_SHOULD_BE_LESS_THAN_OR_EQUAL_TO')+" "+app.vtranslate('JS_CURRENT_DATE');
			this.setError(errorInfo);
			return false;
		}
                return true;
	}
})

Ottocrat_Base_Validator_Js('Ottocrat_greaterThanOrEqualToToday_Validator_Js',{},{

	/**
	 * Function to validate the dateinservice field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldInfo = fieldData.fieldinfo;
		var fieldValue = this.getFieldValue();
		try{
			var fieldDateInstance = Ottocrat_Helper_Js.getDateInstance(fieldValue,fieldDateFormat);
		}
		catch(err){
			this.setError(err);
			return false;
		}
		fieldDateInstance.setHours(0,0,0,0);
		var todayDateInstance = new Date();
		todayDateInstance.setHours(0,0,0,0);
		var comparedDateVal =  todayDateInstance - fieldDateInstance;
		if(comparedDateVal > 0){
			var errorInfo = fieldInfo.label+" "+app.vtranslate('JS_SHOULD_BE_GREATER_THAN_OR_EQUAL_TO')+" "+app.vtranslate('JS_CURRENT_DATE');;
			this.setError(errorInfo);
			return false;
		}
        return true;
	}
})

Ottocrat_Base_Validator_Js("Ottocrat_greaterThanDependentField_Validator_Js",{
	
	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var fieldForValidation = field[0];
		if(jQuery(fieldForValidation).attr('name') == 'followup_date_start'){
			var dependentFieldList = new Array('date_start');
		}
		var instance = new Ottocrat_greaterThanDependentField_Validator_Js();
		instance.setElement(field);
		var response = instance.validate(dependentFieldList);
		if(response != true){
			return instance.getError();
		}
	}
},{

	/**
	 * Function to validate the birthday field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(dependentFieldList){
		var field = this.getElement();
		var fieldInfo = field.data('fieldinfo');
		var fieldLabel;
		if(typeof fieldInfo == "undefined"){
			fieldLabel = jQuery(field).attr('name');
		}else{
			fieldLabel = fieldInfo.label;
		}
		var contextFormElem = field.closest('form');
		for(var i=0; i<dependentFieldList.length; i++){
			var dependentField = dependentFieldList[i];
			var dependentFieldInContext = jQuery('input[name='+dependentField+']',contextFormElem);
			if(dependentFieldInContext.length > 0){
				var dependentFieldLabel = dependentFieldInContext.data('fieldinfo').label;
				var fieldDateInstance = this.getDateTimeInstance(field);
				var dependentFieldDateInstance = this.getDateTimeInstance(dependentFieldInContext);
				var comparedDateVal =  fieldDateInstance - dependentFieldDateInstance;
				if(comparedDateVal < 0){
					var errorInfo = fieldLabel+' '+app.vtranslate('JS_SHOULD_BE_GREATER_THAN_OR_EQUAL_TO')+' '+dependentFieldLabel+'';
					this.setError(errorInfo);
					return false;
				}
			}
		}
        return true;
	},

	getDateTimeInstance : function(field) {
		var dateFormat = field.data('dateFormat');
		var fieldValue = field.val();
		try{
			var dateTimeInstance = Ottocrat_Helper_Js.getDateInstance(fieldValue,dateFormat);
		}
		catch(err){
			this.setError(err);
			return false;
		}
		return dateTimeInstance;
	}
})

Ottocrat_Base_Validator_Js("Ottocrat_futureEventCannotBeHeld_Validator_Js",{},{

	/**
	 * Function to validate event status , which cannot be held for future events
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(dependentFieldList){
		var field = this.getElement();
		var fieldLabel = field.data('fieldinfo').label;
                var status = field.val();
		var contextFormElem = field.closest('form');
		for(var i=0; i<dependentFieldList.length; i++){
			var dependentField = dependentFieldList[i];
			var dependentFieldInContext = jQuery('input[name='+dependentField+']',contextFormElem);
			if(dependentFieldInContext.length > 0){
				var dependentFieldLabel = dependentFieldInContext.data('fieldinfo').label;
				var todayDateInstance = new Date();
                                var dateFormat = dependentFieldInContext.data('dateFormat');
                                var time = jQuery('input[name=time_start]',contextFormElem);
                                var fieldValue = dependentFieldInContext.val()+" "+time.val();
				var dependentFieldDateInstance = Ottocrat_Helper_Js.getDateInstance(fieldValue,dateFormat);
				var comparedDateVal =  todayDateInstance - dependentFieldDateInstance;
				if(comparedDateVal < 0 && status == "Held"){
					var errorInfo = fieldLabel+' '+app.vtranslate('JS_FUTURE_EVENT_CANNOT_BE_HELD')+' '+dependentFieldLabel+'';
					this.setError(errorInfo);
					return false;
				}
			}
		}
        return true;
	}
})

Ottocrat_Base_Validator_Js("Ottocrat_lessThanDependentField_Validator_Js",{},{

	/**
	 * Function to validate the birthday field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(dependentFieldList){
		var field = this.getElement();
		var fieldLabel = field.data('fieldinfo').label;
		var contextFormElem = field.closest('form');
		//No need to validate if value is empty
        if(field.val().length == 0) {
            return;
        }
		for(var i=0; i<dependentFieldList.length; i++){
			var dependentField = dependentFieldList[i];
			var dependentFieldInContext = jQuery('input[name='+dependentField+']',contextFormElem);
			if(dependentFieldInContext.length > 0){
				var dependentFieldLabel = dependentFieldInContext.data('fieldinfo').label;
				var fieldDateInstance = this.getDateTimeInstance(field);
				//No need to validate if value is empty
				if(dependentFieldInContext.val().length == 0) {
					continue;
				}
				var dependentFieldDateInstance = this.getDateTimeInstance(dependentFieldInContext);
				var comparedDateVal =  fieldDateInstance - dependentFieldDateInstance;
				if(comparedDateVal > 0){
					var errorInfo = fieldLabel+' '+app.vtranslate('JS_SHOULD_BE_LESS_THAN_OR_EQUAL_TO')+' '+dependentFieldLabel+'';
					this.setError(errorInfo);
					return false;
				}
			}
		}
        return true;
	},

	getDateTimeInstance : function(field) {
		var dateFormat = field.data('dateFormat');
		var fieldValue = field.val();
		try{
			var dateTimeInstance = Ottocrat_Helper_Js.getDateInstance(fieldValue,dateFormat);
		}
		catch(err){
			this.setError(err);
			return false;
		}
		return dateTimeInstance;
	}
})

Ottocrat_Base_Validator_Js('Ottocrat_Currency_Validator_Js',{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var currencyValidatorInstance = new Ottocrat_Currency_Validator_Js();
		currencyValidatorInstance.setElement(field);
		var response = currencyValidatorInstance.validate();
		if(response != true){
			return currencyValidatorInstance.getError();
		}
	}
},{

	/**
	 * Function to validate the Currency Field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var response = this._super();
		if(response != true){
			return response;
		}
		var field = this.getElement();
		var fieldValue = this.getFieldValue();
		var fieldData = field.data();

		var strippedValue = fieldValue.replace(fieldData.decimalSeperator, '');
        var spacePattern = /\s/;
        if(spacePattern.test(fieldData.decimalSeperator) || spacePattern.test(fieldData.groupSeperator))
            strippedValue = strippedValue.replace(/ /g, '');
		var errorInfo;
        
        if(fieldData.groupSeperator =="$"){
            fieldData.groupSeperator="\\$"
        }
        var regex = new RegExp(fieldData.groupSeperator,'g');
        strippedValue = strippedValue.replace(regex, '');
		//Note: Need to review if we should allow only positive values in currencies
		/*if(strippedValue < 0){
			var errorInfo = app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS');//"currency value should be greater than or equal to zero";
			this.setError(errorInfo);
			return false;
		}*/
		if(isNaN(strippedValue)){
			errorInfo = app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS');
			this.setError(errorInfo);
			return false;
		}
		if(strippedValue < 0){
			errorInfo = app.vtranslate('JS_ACCEPT_POSITIVE_NUMBER');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
})

Ottocrat_Base_Validator_Js("Ottocrat_ReferenceField_Validator_Js",{},{

	/**
	 * Function to validate the Positive Numbers and whole Number
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var field = this.getElement();
		var parentElement = field.closest('.fieldValue');
		var referenceField = parentElement.find('.sourceField');
		var referenceFieldValue = referenceField.val();
		var fieldInfo = referenceField.data().fieldinfo;
		if(referenceFieldValue == ""){
			var errorInfo = app.vtranslate('JS_REQUIRED_FIELD');
			this.setError(errorInfo);
			return false;
		}
		return true;
	}
})

Ottocrat_Integer_Validator_Js("Ottocrat_Double_Validator_Js",{},{

	/**
	 * Function to validate the Decimal field data
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var response = this._super();
		if(response == false){
			var fieldValue = this.getFieldValue();
			var doubleRegex= /(^[-+]?\d+)\.\d+$/ ;
			if (!fieldValue.match(doubleRegex)) {
				var errorInfo = app.vtranslate("JS_PLEASE_ENTER_DECIMAL_VALUE");
				this.setError(errorInfo);
				return false;
			}
			return true;
		}
		return response;
	}
})

Ottocrat_Base_Validator_Js("Ottocrat_Date_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var dateValidatorInstance = new Ottocrat_Date_Validator_Js();
		dateValidatorInstance.setElement(field);
		var response = dateValidatorInstance.validate();
		if(response != true){
			return dateValidatorInstance.getError();
		}
		return response;
	}

},{

	/**
	 * Function to validate the Positive Numbers and whole Number
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldValue = this.getFieldValue();
		try{
			Ottocrat_Helper_Js.getDateInstance(fieldValue,fieldDateFormat);
		}
		catch(err){
			var errorInfo = app.vtranslate("JS_PLEASE_ENTER_VALID_DATE");
			this.setError(errorInfo);
			return false;
		}
		return true;
	}


})

Ottocrat_Base_Validator_Js("Ottocrat_Time_Validator_Js",{

	/**
	 * Function which invokes field validation
	 * @param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation : function(field, rules, i, options) {
		var validatorInstance = new Ottocrat_Time_Validator_Js();
		validatorInstance.setElement(field);
		var result = validatorInstance.validate();
		if(result == true){
			return result;
		} else {
			return validatorInstance.getError();
		}
	}

},{

	/**
	 * Function to validate the Time Fields
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var fieldValue = this.getFieldValue();
		var time = fieldValue.replace(fieldValue.match(/[AP]M/i),'');
		var timeValue = time.split(":");
		if(isNaN(timeValue[0]) && isNaN(timeValue[1])) {
			var errorInfo = app.vtranslate("JS_PLEASE_ENTER_VALID_TIME");
			this.setError(errorInfo);
			return false;
		}
		return true;
	}

})

//Calendar Specific validators
// We have placed it here since quick create will not load module specific validators

Ottocrat_greaterThanDependentField_Validator_Js("Calendar_greaterThanDependentField_Validator_Js",{},{

	getDateTimeInstance : function(field) {
        var form = field.closest('form');
		if(field.attr('name') == 'date_start') {
			var timeField = form.find('[name="time_start"]');
            var timeFieldValue = timeField.val();
		}else if(field.attr('name') == 'due_date') {
			var timeField = form.find('[name="time_end"]');
            if(timeField.length > 0) {
                var timeFieldValue = timeField.val();
            }else{
                //Max value for the day
                timeFieldValue = '11:59 PM';
            }
		}

		var dateFieldValue = field.val()+" "+ timeFieldValue;
        var dateFormat = field.data('dateFormat');
        return Ottocrat_Helper_Js.getDateInstance(dateFieldValue,dateFormat);
	}

});

Ottocrat_Base_Validator_Js('Calendar_greaterThanToday_Validator_Js',{},{

	/**
	 * Function to validate the birthday field
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var field = this.getElement();
		var fieldData = field.data();
		var fieldDateFormat = fieldData.dateFormat;
		var fieldInfo = fieldData.fieldinfo;
		var fieldValue = this.getFieldValue();
		try{
			var fieldDateInstance = Ottocrat_Helper_Js.getDateInstance(fieldValue,fieldDateFormat);
		}
		catch(err){
			this.setError(err);
			return false;
		}
		fieldDateInstance.setHours(0,0,0,0);
		var todayDateInstance = new Date();
		todayDateInstance.setHours(0,0,0,0);
		var comparedDateVal =  todayDateInstance - fieldDateInstance;
		if(comparedDateVal >= 0){
			var errorInfo = fieldInfo.label+" "+app.vtranslate('JS_SHOULD_BE_GREATER_THAN_CURRENT_DATE');
			this.setError(errorInfo);
			return false;
		}
        return true;
	}
})

Ottocrat_Base_Validator_Js("Calendar_RepeatMonthDate_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var repeatMonthDateValidatorInstance = new Calendar_RepeatMonthDate_Validator_Js();
		repeatMonthDateValidatorInstance.setElement(field);
		var response = repeatMonthDateValidatorInstance.validate();
		if(response != true){
			return repeatMonthDateValidatorInstance.getError();
		}
	}

},{

	/**
	 * Function to validate the Positive Numbers and whole Number
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var fieldValue = this.getFieldValue();

		if((parseInt(parseFloat(fieldValue))) != fieldValue || fieldValue == '' || parseInt(fieldValue) > '31' || parseInt(fieldValue) <= 0) {
			var result = app.vtranslate('JS_NUMBER_SHOULD_BE_LESS_THAN_32');
			this.setError(result);
			return false;
		}
		return true;
	}
})

Ottocrat_WholeNumber_Validator_Js("Ottocrat_WholeNumberGreaterThanZero_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){

		var WholeNumberGreaterThanZero = new Ottocrat_WholeNumberGreaterThanZero_Validator_Js();
		WholeNumberGreaterThanZero.setElement(field);
		var response = WholeNumberGreaterThanZero.validate();
		if(response != true){
			return WholeNumberGreaterThanZero.getError();
		}
	}

},{

	/**
	 * Function to validate the Positive Numbers and greater than zero value
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){

		var response = this._super();
		if(response != true){
			return response;
		}else{
			var fieldValue = this.getFieldValue();
			if(fieldValue == 0){
				var errorInfo = app.vtranslate('JS_VALUE_SHOULD_BE_GREATER_THAN_ZERO');
				this.setError(errorInfo);
				return false;
			}
		}
		return true;
	}
})
Ottocrat_Base_Validator_Js("Ottocrat_AlphaNumeric_Validator_Js",{

	/**
	 *Function which invokes field validation
	 *@param accepts field element as parameter
	 * @return error if validation fails true on success
	 */
	invokeValidation: function(field, rules, i, options){
		var alphaNumericInstance = new Ottocrat_AlphaNumeric_Validator_Js();
		alphaNumericInstance.setElement(field);
		var response = alphaNumericInstance.validate();
		if(response != true){
			return alphaNumericInstance.getError();
		}
	}

},{

	/**
	 * Function to validate the Positive Numbers
	 * @return true if validation is successfull
	 * @return false if validation error occurs
	 */
	validate: function(){
		var field = this.getElement();
		var fieldValue = field.val();
		var alphaNumericRegex = /^[a-z0-9 _-]*$/i;
		if (!fieldValue.match(alphaNumericRegex)) {
			var errorInfo = app.vtranslate("JS_CONTAINS_ILLEGAL_CHARACTERS");
			this.setError(errorInfo);
			return false;
		}
        return true;
	}
})
