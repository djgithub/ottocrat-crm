/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

Ottocrat_Detail_Js("Contacts_Detail_Js",{},{
	
	/**
	 * Function to register recordpresave event
	 */
	registerRecordPreSaveEvent : function(form){
		var thisInstance = this;
		var primaryEmailField = jQuery('[name="email"]');
		if(typeof form == 'undefined') {
			form = this.getForm();
		}

		form.on(this.fieldPreSave,'[name="portal"]', function(e, data) {
			var portalField = jQuery(e.currentTarget);
			
			var primaryEmailValue = primaryEmailField.val();
			var isAlertAlreadyShown = jQuery('.ui-pnotify').length;
					
			
			if(portalField.is(':checked')){
				if(primaryEmailField.length == 0){
					if(isAlertAlreadyShown <= 0) {
						Ottocrat_Helper_Js.showPnotify(app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS'));
					}
					e.preventDefault();
				} 
				if(primaryEmailValue == ""){
					if(isAlertAlreadyShown <= 0) {
						Ottocrat_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER'));
					}
					e.preventDefault();
 				} 
			}
		})
	},
	
	/**
	 * Function which will register all the events
	 */
    registerEvents : function() {
		var form = this.getForm();
		this._super();
		this.registerRecordPreSaveEvent(form);
	}
})