/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

Ottocrat_List_Js("Settings_Ottocrat_List_Js",{
	
	triggerDelete : function(event,url){
		event.stopPropagation();
		var instance = Ottocrat_List_Js.getInstance();
		instance.DeleteRecord(url);
	}
},{
	
	/*
	 * Function to register the list view delete record click event
	 */
	DeleteRecord: function(url){
		var thisInstance = this;
		var css = jQuery.extend({'text-align' : 'left'},css);
			
		AppConnector.request(url).then(
			function(data) {
				if(data) {
					app.showModalWindow(data,function(container){
						thisInstance.postDeleteAction(container);
					});
				}
			},
			function(error,err){

			}
		);
	},
	
	/**
	 * Function to load list view after deletion of record from list view
	 */
	postDeleteAction : function(container){
		var thisInstance = this;
		var deleteConfirmForm = jQuery(container).find('#DeleteModal');
		deleteConfirmForm.on('submit', function(e){
			e.preventDefault();
			var deleteActionUrl = deleteConfirmForm.serializeFormData();
			AppConnector.request(deleteActionUrl).then(
				function() {
					app.hideModalWindow();
					var params = {
						text: app.vtranslate('JS_RECORD_DELETED_SUCCESSFULLY')
					};
					Settings_Ottocrat_Index_Js.showMessage(params);
					jQuery('#recordsCount').val('');
					jQuery('#totalPageCount').text('');
					thisInstance.getListViewRecords().then(function(){
						thisInstance.updatePagination();
					});
				},
				function(error,err){
					app.hideModalWindow();
				}
			);
		})
	},
	
	/**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams : function(){
		var module = app.getModuleName();
		var cvId = this.getCurrentCvId();
		var pageCountParams = {
			'module' : module,
			'parent' : "Settings",
			'action' : "ListAjax",
			'mode' : "getPageCount",
			"viewname": cvId
		}
		var sourceModule = jQuery('#moduleFilter').val();
		if(typeof sourceModule != 'undefined'){
			pageCountParams['sourceModule'] = sourceModule;
		}
		return pageCountParams;
	},
	
	registerEvents : function() {
		//this.triggerDisplayTypeEvent();
		this.registerRowClickEvent();
		this.registerHeadersClickEvent();
		this.registerPageNavigationEvents();
		this.registerEventForTotalRecordsCount();
	}
});