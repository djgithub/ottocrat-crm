/*+***********************************************************************************
 * The contents of this file are subject to the ottocrat CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  ottocrat CRM Open Source
 * The Initial Developer of the Original Code is ottocrat.
 * Portions created by ottocrat are Copyright (C) ottocrat.
 * All Rights Reserved.
 *************************************************************************************/

PriceBooks_Detail_Js("Products_Detail_Js",{},{
	
	/**
	 * Function to register event for image graphics
	 */
	registerEventForImageGraphics : function(){
		var imageContainer = jQuery('#imageContainer');
		imageContainer.cycle({ 
			fx:    'curtainX', 
			sync:  false, 
			speed:1000,
			timeout:20
		 });
		 imageContainer.find('img').on('mouseenter',function(){
			 imageContainer.cycle('pause');
		 }).on('mouseout',function(){
			 imageContainer.cycle('resume');
		 })
	},
	
	/**
	 * Function to register event for select button click on pricebooks in Products related list
	 */
	registerEventForSelectRecords : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', 'button[data-modulename="PriceBooks"]', function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Products_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.showSelectRelationPopup();
		});
	},
	
	/**
	 * Function to register events
	 */
	registerEvents : function(){
		this._super();
		this.registerEventForImageGraphics();
	}
})