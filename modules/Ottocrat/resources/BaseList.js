/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

var Ottocrat_BaseList_Js = {
	/**
	 * Function to get the parameters for paging of records
	 * @return : string - module name
	 */
	getPageRecords : function(params){
		var aDeferred = jQuery.Deferred();
		
		if(typeof params == 'undefined') {
			params = {};
		}

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}

		if(typeof params.view == 'undefined') {
			//Default we will take list ajax
			params.view = 'ListAjax';
		}

		if(typeof params.page == 'undefined') {
			params.page = Ottocrat_BaseList_Js.getCurrentPageNum();
		}

		AppConnector.request(params).then(
			function(data){
				aDeferred.resolve(data);
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
	
	getCurrentPageNum : function() {
		return jQuery('#pageNumber').val();
	}
}
