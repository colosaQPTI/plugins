///////////////////////////////////////////////////////////////////////
// @@ listBeneficiareNewTab.js
// @@ Recent change : May 10
///////////////////////////////////////////////////////////////////////

var action = "todo";
var myApp = {};
document.write('<link rel="stylesheet" type="text/css" href="/plugin/convergenceList/styleconverg.css"/>');
Ext.onReady(function()
{
	var tooltipRenderer = function(data, metadata, record, rowIndex, columnIndex,store) 
	{
		metadata.attr = 'ext:qtip="' + data + '" style="white-space: normal; "';
		return data;
	};
    _columns    = new Array();columnAlign = 'left';
    _fields     = new Array();// Dynamic Assignation
    _filters    = new Array();
    _itemsFilters   = new Array();
	_idProperty = '__index__';  
	       
	_columns.push({                   
		id     : _idProperty,           
		hidden : true                   
	});                               
                                 
	_fields.push({name: _idProperty});

	var i;
	var j = 0;
	//var idInbox = tableDef[0].ID_INBOX;
	var nameTab = tableDef[0].DESCRIPTION_INBOX;
	var sw = 0;
	var f = 1;
	_itemsFilters.push(new Array('ALL', 'Tous les champs...'));
	for ( i=0;  i < tableDef.length;  i++) 
	{		
		if(j == 0)
		{
			if(arrayActions.length > 0){ // Verify if I have action then you will see the checkbox column
				var checkSelect = new Ext.grid.CheckboxSelectionModel({
					checkOnly: false,
					singleSelect: false,
					sortable: false,
					dataIndex: 'visible',
					width: 20,
					listeners:{
			        	selectionchange: function(sm){
			          		enableDisableMenuOption();
			         
			        	}
			      	}
				});
				column = checkSelect;
	  		  
	  		    column.editor = {
	  		      			xtype : 'displayfield',
	  		      			style : 'font-size:11px; padding-left:7px'		      
	  		      	}
	  		    _columns.push(column);
	  		}
	  		    
			_fields.push({name: tableDef[i].FIELD_NAME});
	  		
			if(_idProperty == '' && tableDef[i].FLD_KEY) {
				_idProperty = tableDef[i].FIELD_NAME;
			}
			j = 1;
		}   
		if(tableDef[i].HIDDEN_FIELD == 1)
			var hiddenField = true;
		else
			var hiddenField = false;
		    
		column = {
		      id        : tableDef[i].FIELD_NAME,
		      header    : tableDef[i].DESCRIPTION,
		      dataIndex : tableDef[i].FIELD_NAME,
		      width     : 130,
		      align     : columnAlign,
		      hidden    : hiddenField,
		      renderer  : tooltipRenderer
		};
		    
		column.editor = {
				xtype : 'displayfield',
				style : 'font-size:11px; padding-left:7px'		      
		}
		_columns.push(column);
		    
		_fields.push({name: tableDef[i].FIELD_NAME});
		
		if(_idProperty == '' && tableDef[i].FLD_KEY) {
			_idProperty = tableDef[i].FIELD_NAME;
		}
		    
		idInbox = tableDef[i].ID_INBOX;

		    ////// Filters 

		if(tableDef[i].INCLUDE_FILTER==1 && tableDef[i].HIDDEN_FIELD == 0){
		    	column = {
			      id        : tableDef[i].FIELD_NAME,
			      header    : tableDef[i].DESCRIPTION,
			      dataIndex : tableDef[i].FIELD_NAME,
			      width     : 130,
			      align     : columnAlign
			    };
		    	_filters.push(column);
		    	_itemsFilters.push(new Array(tableDef[i].FIELD_NAME,tableDef[i].DESCRIPTION));
		}
		    ////// End filters
		
	}	
	    // End Dynamic Assignation
   
	var UserCombo = new Ext.data.JsonStore({
		url : 'ajaxFiltersProcess.php?Type=UserCombo',
		root : 'data',
		fields : [{
			name : 'USR_UID'
		}, {
			name : 'USER'
		}]
	});		   
		   
	var textSearch = {                        
			xtype : 'textfield',
			id: '_fieldInputGeneral',                  
			forceSelection: true,       
			width : 200,
			cls:'converg_blur',
			listeners: {
				specialkey: function(f,e){
					if (e.getKey() == e.ENTER) {
						searchRecords(); 
					}
				},
				focus: function(){
					Ext.fly('_fieldInputGeneral').replaceClass('converg_blur', 'converg_focus');
				},
				blur: function(){
					Ext.fly('_fieldInputGeneral').replaceClass('converg_focus', 'converg_blur');
				} 

			}
	}
		   
	var btnResetSearch = {          
			xtype: 'button',
		    //text:'Clear',
			width : '30',
			iconCls : 'cvrgl_reset',
			handler: function(){
				Ext.getCmp('_fieldInputSpecific').reset();	
				Ext.getCmp('_fieldInputGeneral').reset();	
				gridInboxDinamicStore.setBaseParam('fieldInputSpecific', Ext.getCmp('_fieldInputSpecific').getValue());
				gridInboxDinamicStore.setBaseParam('fieldInputGeneral', Ext.getCmp('_fieldInputGeneral').getValue());
				gridInboxDinamicStore.load(); 
		  	}
	}
 
	var btnSearch = {          
			xtype: 'button',
			text : _('ID_SEARCH'),
			width : 30,
			iconCls : 'button_menu_ext ss_sprite converg_search',
			listeners : {
				click : {
					fn : function(combo, value) {
						searchRecords();
					}
				}
			}
	}		   	
		
	comboUsers = {
			xtype : 'combo',
			id : 'UserId',
			fieldLabel : 'User',
			name : 'UserId',
			anchor : '95%',
			valueField : 'USR_UID',
			store : UserCombo,
			displayField : 'USER',
			forceSelection : true,
			emptyText : 'All User ...',
			triggerAction : 'all',
			value : 'All Users',
			editable : false,
			listeners:{
				select:{
					fn:function(combo, value){
						gridInboxDinamicStore.setBaseParam('USER', Ext.getCmp('UserId').getValue());
						gridInboxDinamicStore.load();                      
					}               
				}                        
			}
	}

 
	var storeGenericFileds = new Ext.data.JsonStore({
		url : 'ajaxFiltersProcess.php?Type=UserCombo',
		root : 'data',
		fields : [{
			name : 'USR_UID'
		}, {
			name : 'USER'
		}]
	});     

	//////Filters

	var storeCustom = new Ext.data.JsonStore({
		url : 'ajaxFiltersProcess.php?Type=custom&idTable=' + table + '&idInbox=' + idInbox,
		root : 'data',
		fields : [{name : 'ID'}, {name : 'DESCRIPTION'}]
	}); 

	var comboFilters = {
		xtype : 'combo',
		id : '_fieldName',
		name : '_fieldName',
		valueField : 'FIELD_NAME',
		store : _itemsFilters,
		displayField : 'FIELD_NAME',
		forceSelection : true,
		triggerAction : 'all',
		style:'margin-right:4px;',
		emptyText:'Search...',
		value : 'ALL',
		width:170,
		editable : false,
		listeners:{
			select:{
				fn:function(combo, value){
					var itemSelected=Ext.getCmp('_fieldName').getValue();
					Ext.getCmp('_fieldInputGeneral').reset();
					Ext.getCmp('_fieldInputSpecific').reset();
					if(itemSelected != 'ALL'){
						Ext.getCmp('_fieldInputGeneral').hide();
						storeCustom.setBaseParam('fieldName', itemSelected);
						Ext.getCmp('_fieldInputSpecific').show();
					}
					else{
						Ext.getCmp('_fieldInputGeneral').show();	
						Ext.getCmp('_fieldInputSpecific').hide();
					}
				}               
			}                        
		}
	};
	var suggestFilters = { 
	      xtype : 'combo',
	      fieldLabel : 'Search',
	      id : '_fieldInputSpecific',
	      name : '_fieldInputSpecific',
	      valueField : 'ID',
	      displayField : 'DESCRIPTION',
	      typeAhead : false,
	      loadingtext : 'Search...',
	      store : storeCustom,
	      width : 200,
	      emptyText : '',
	      minChars : 1,
	      anchor : '100%',
	      cls:'converg_blur',
	      pagesize : 0,
	      hideTrigger : true,
	      hidden:true,
	      editable : true,
	      triggerAction : 'all',
	      listeners:{
	          select:{
	            fn:function(combo, item){
	            	if(item && item.data && item.data.DESCRIPTION) {
	            		var __fieldValue = item.data.DESCRIPTION;
						Ext.getCmp('_fieldInputGeneral').reset();
						gridInboxDinamicStore.setBaseParam('fieldInputGeneral', Ext.getCmp('_fieldInputGeneral').getValue());	
						gridInboxDinamicStore.setBaseParam('fieldName', Ext.getCmp('_fieldName').getValue());
						gridInboxDinamicStore.setBaseParam('fieldInputSpecific', __fieldValue);
						gridInboxDinamicStore.load(); 
					}
	            }          
	          },
	          specialkey: function(f,e){
	        	  
					if (e.getKey() == e.ENTER) {
						console.log(f);
						if( f.lastQuery !=  f.lastSelectionText )
							if( f.lastSelectionText ==  f.value && f.value != '')
								__fieldValue	= f.lastSelectionText;
							else
								__fieldValue	= f.lastQuery;
						else 
							var __fieldValue	= f.lastQuery;
						
						f.lastSelectionText = '';
						Ext.getCmp('_fieldInputGeneral').reset();
						gridInboxDinamicStore.setBaseParam('fieldInputGeneral', Ext.getCmp('_fieldInputGeneral').getValue());	
						gridInboxDinamicStore.setBaseParam('fieldName', Ext.getCmp('_fieldName').getValue());
						gridInboxDinamicStore.setBaseParam('fieldInputSpecific', __fieldValue);
						gridInboxDinamicStore.load(); 
						
					}
	          },
			  focus: function(){
				Ext.fly('_fieldInputSpecific').replaceClass('converg_blur', 'converg_focus');
	          },
	          blur: function(){
	        	  Ext.fly('_fieldInputSpecific').replaceClass('converg_focus', 'converg_blur');
	          }
	         
	      }
	      
	  };
	///////// End Section Filters

	myApp.addTab_inside = function()
	{	
		var miArray = new Array(); 
		var i = 0;
		var idField = '';				 
		if(checkSelect) 	
		{
			checkSelect.each(function(record)  
	    	{  
	    		var item= {};
	    		record.fields.each(function(field) 
	    		{ 	    				    				    			
	    			var fieldValue = record.get(field.name);
	    			var fieldName = field.name;	    				    		  
	    			
	    		  item[fieldName] = fieldValue;
	    			i++;	    			
	    			
	    		}); 
	    		miArray.push(item);	    		
	    	});
		}				
		var jsonText = Ext.util.JSON.encode(miArray);		 		 
		 return jsonText;
		 
	}
	myApp.getIdInbox = function(){	
		var _idInbx = (idInbox)? idInbox: '';
		return _idInbx; 
	}
	myApp.getIdMainGrid = function(){	
		return 'gridNewTab'; 
	}
	myApp.getProUid = function(){	
		var _proUid = (proUid)? proUid: '';
		return _proUid;  
	}
			    	
	var optionMenuReassignGlobal = {};
	optionMenuReassignGlobal.APP_UID = "";
	optionMenuReassignGlobal.DEL_INDEX = "";
					  
	optionMenuReassign = new Ext.Action({
		text: _('ID_REASSIGN'),
		iconCls: 'ICON_CASES_TO_REASSIGN',
		handler: function() {
		var casesGrid_ = Ext.getCmp('gridNewTab');
		var rowSelected = casesGrid_.getSelectionModel().getSelected();
		if( rowSelected ){
			var rowAllJsonArray = casesGrid_.store.reader.jsonData.data;
			var rowSelectedIndex = casesGrid_.getSelectionModel().lastActive;
			var rowSelectedJsonArray = rowAllJsonArray[rowSelectedIndex];
								    
			var TAS_UID = rowSelectedJsonArray.TAS_UID;
			var USR_UID = rowSelectedJsonArray.USR_UID;
								    
			var APP_UID = rowSelectedJsonArray.APP_UID;
			var DEL_INDEX = rowSelectedJsonArray.DEL_INDEX;

			optionMenuReassignGlobal.APP_UID = APP_UID;
			optionMenuReassignGlobal.DEL_INDEX = DEL_INDEX;								      			     						
			
			var win = new Ext.Window({
				title: '',
				width: 450,
				height: 280,
				layout:'fit',
				autoScroll:true,
				modal: true,
				maximizable: false,
				items: [gridInboxDinamic]
			});
			win.show();
		}
	}
	});
	
	menuItemsA = new Array();
	for (i=0; i<arrayActions.length; i++) {
		
		var showHidden = (arrayActions[i].ROWS_AFFECT == 'none')? false:true;
		var optionMenu = new Ext.Action({
			text: arrayActions[i].DESCRIPTION,
		    iconCls: 'x-tree-node-icon ss_application_form',
		    id : i + 1,
		    checked: true,
		    handler:  actionDinamic,
		    hidden:showHidden
		});
		menu =  optionMenu;
		menuItemsA.push(menu);
		
	}
	menuItems = '';
	var actionsMenuI = '';
	if(arrayActions.length > 0){
		menuItems = menuItemsA;
		actionsMenuI = {
		      xtype: 'tbsplit',
		      text: _('ID_ACTIONS'),
		      menu: menuItems
		}		
	}
	
	
	contextMenuItems = new Array();
	for (i=0; i<menuItems.length; i++) {
	    contextMenuItems.push(menuItems[i]);
	}
	var messageContextMenu = new Ext.menu.Menu({
	    id: 'messageContextMenu',
	    items: contextMenuItems
	});

	 /*var searchForm = new Ext.Toolbar({
	    width: '100%',
	    autoHeight: true,
	    defaults:{
	    	style:'padding-left:3px;',
	    	labelStyle:'font-weight:blod;'
	    },
	    items: [	      
	      actionsMenuI,		
	      '->',	      
	      '<b>'+_('ID_SEARCH')+':</b> &nbsp;',
	      comboFilters,
	      {xtype: 'tbspacer', width:15},
	      textSearch,
	      suggestFilters,
	      {xtype: 'tbspacer', width:25},
          btnSearch,
          '-',
          btnResetSearch
	    ]
	});*/
	
	var messagePager = 'No Accounts to show';
 	var messageConfig = '<div align="center"><b> ** Aucun r&#233;sultat trouv&#233; ** </b></div>';
 	var displayMsg  = 'Accounts {0} - {1} Of {2}';
	
	if(filterSearch == '1'){

			var searchForm = new Ext.Toolbar({
	    width: '100%',
	    autoHeight: true,
	    defaults:{
	    	style:'padding-left:3px;',
	    	labelStyle:'font-weight:blod;'
	    },
	    items: [	      
	      actionsMenuI,		
	      '->',	      
	      '<b>'+_('ID_SEARCH')+':</b> &nbsp;',
	      comboFilters,
	      {xtype: 'tbspacer', width:15},
	      textSearch,
	      suggestFilters,
	      {xtype: 'tbspacer', width:25},
          btnSearch,
          '-',
          btnResetSearch
	    ]
	});
	 	
	}

	if(filterSearch == '0'){
		messagePager = '';
		messageConfig = '';
		displayMsg = '';
		var searchForm = new Ext.Toolbar({
	    width: '100%',
	    autoHeight: true,
	    defaults:{
	    	style:'padding-left:3px;',
	    	labelStyle:'font-weight:blod;'
	    },
	    items: [	      
	      actionsMenuI
	    ]
	});
	 	
	}	
		
	var ToolForm = new Ext.FormPanel({
			//labelAlign : 'top',
		    buttonAlign : 'center',
		    frame : false,    
		    bodyStyle : 'padding:0px 0px 0',
		    width : '100%',
		    layout:'fit',
		   // height : 100,
		   // anchorTo: 'center',
		    border : false,
		    items : [searchForm]
		});

	
	var gridInboxDinamicStore = new Ext.data.JsonStore({
	    id:'gridInboxDinamicStore',
	    url : 'ajaxInboxDinamic.php?idTable=' + table + '&idInbox=' + idInbox,
	    root : 'data',
	    totalProperty : 'total', 
	    remoteSort : true,
	    autoWidth : true,
	    fields : _fields,
	    listeners: {
        	load: function(store, records, success) {
        		//modif by req (April 9th)
        		if(this.reader && this.reader.jsonData && this.reader.jsonData.success_req && this.reader.jsonData.success_req == 'error'){
        			Ext.MessageBox.show({                            
                        msg : 'ERROR:' + this.reader.jsonData.message, //'An error occurred in the search. Check with the administrator.',
                        buttons : Ext.MessageBox.OK,
                        icon : Ext.MessageBox.ERROR
                     });
        		}
        	}   
    	} 
	});
	gridInboxDinamicStore.load();
	 
	var pager = new Ext.PagingToolbar({
        store       : gridInboxDinamicStore, 
        displayInfo : true,
        autoHeight  : true,
        displayMsg  : displayMsg,
        emptyMsg    : messagePager,
        pageSize: 100
       });
	

	var gridInboxDinamic = new Ext.grid.GridPanel({
		store : gridInboxDinamicStore,
		cm : new Ext.grid.ColumnModel({
			defaults : {
		    	width : 20,
		    	sortable : true
		    },
		    columns : _columns
		 
		}),
		sm: checkSelect,
		bbar : pager,
		tbar: ToolForm,//reportsPanelToolBars
		border : false,
		loadMask : true,
		stripeRows : true,
		autoShow : true,
		autoFill : true,
		nocache : true,
		autoWidth : true,
		listeners:{
        	selectionchange: function(sm){
          		enableDisableMenuOption();
         
        	}
      	},
		id: 'gridNewTab',
		title: 'test',
		height : 800,
		stateful : true,
		animCollapse : true,
		stateId : 'grid',
		
		viewConfig : {
			forceFit : true,
		    scrollOffset : 0,
		    emptyText: messageConfig
		}
	});
	

	var tabs= new Ext.TabPanel({ 
		activeTab       : 0,
        id              : 'myNewTPanel',
        enableTabScroll : true,
        resizeTabs      : true,
		enableTabScroll :  true, //hacemos que sean recorridas 
		items:[gridInboxDinamic], 
		tbar: ToolForm
	}); 

    if (filterSearch == '1') {
        var displayNewPanel = new Ext.Panel({
            width: 650,
            height: 300,
            layout: 'hbox',
            title: nameInbox,
            defaults: {flex: 1}, //auto stretch
            layoutConfig: {align: 'stretch'},
            items: [
                gridInboxDinamic
            ]
        });
    }
    if (filterSearch == '0') {
        var displayNewPanel = new Ext.Panel({
            width: 650,
            height: 300,
            layout: 'hbox',
            //title		 : nameInbox,
            defaults: {flex: 1}, //auto stretch
            layoutConfig: {align: 'stretch'},
            items: [
                gridInboxDinamic
            ]
        });
    }

	var viewport = new Ext.Viewport({
		layout : 'fit',
		items : [displayNewPanel]
	});
	
	function searchRecords(){
		var __fieldName	= Ext.getCmp('_fieldName').getValue();
		
		if(__fieldName != 'ALL'){
			Ext.getCmp('_fieldInputGeneral').reset();
			gridInboxDinamicStore.setBaseParam('fieldInputGeneral', Ext.getCmp('_fieldInputGeneral').getValue());	
			gridInboxDinamicStore.setBaseParam('fieldName', __fieldName);
			gridInboxDinamicStore.setBaseParam('fieldInputSpecific', Ext.getCmp('_fieldInputSpecific').getValue());
		}
		else{
			Ext.getCmp('_fieldInputSpecific').reset();	
			gridInboxDinamicStore.setBaseParam('fieldInputSpecific', Ext.getCmp('_fieldInputSpecific').getValue());
			gridInboxDinamicStore.setBaseParam('fieldInputGeneral', Ext.getCmp('_fieldInputGeneral').getValue());	
		}
		
		gridInboxDinamicStore.load(); 
	}

	function actionDinamic (item, checked) {
		for (i=0; i<arrayActions.length; i++){ 
			if(item.id == i + 1)
			{	var parameters=arrayActions[i].PARAMETERS_FUNCTION ;
				var functionAction=arrayActions[i].ID_PM_FUNCTION ;
				var total = 0;
				if(parameterSend != ''){
					var parameterSend =  parameters.split(",");
					total = parameterSend.length;
				}
				var parameterSendValue = new Array();
				var rowModel = gridInboxDinamic.getSelectionModel().getSelected();

				if (rowModel && arrayActions[i].ROWS_AFFECT != 'none') {
					b = '';
					for (j=0; j < total; j++){
						fn = parameterSend[j];
						if(fn != ''){
							parameterValue = rowModel.get(fn)
							if(parameterValue == undefined)
								parameterValue = (parameterSend[j]); 
							if(j == 0){
								a = " value" + j + " = '" + parameterValue + "'";
								b = a;
							}
							else{
								a = " value" + j + " = '" + parameterValue + "'";
								b = b +  "," + a  ;
							}
						}
						parameterFunction= "eval(functionAction)(" + b + ");";
						
					}
					var fn = functionAction;
					eval(parameterFunction);

				} else if(arrayActions[i].ROWS_AFFECT == 'none'){
					b = '';
					for (j=0; j < total; j++){
						fn = parameterSend[j];
						if(fn != ''){
							parameterValue = fn;
							if(j == 0){
								a = " value" + j + " = '" + parameterValue + "'";
								b = a;
							}
							else{
								a = " value" + j + " = '" + parameterValue + "'";
								b = b +  "," + a  ;
							}
						}
						parameterFunction= "eval(functionAction)(" + b + ");";
					}
					var fn = functionAction;
					eval(parameterFunction);
				}else {
					msgBox(_('ID_INFORMATION'), _('ID_SELECT_ONE_AT_LEAST')); 
				}
			}
		}
	};
	function enableDisableMenuOption_old()
	{
		var idMenuoption = contextMenuItems.items;
		var rows = gridInboxDinamic.getSelectionModel().getSelections();
		var rowModel = gridInboxDinamic.getSelectionModel().getSelected();
	  	if(rowModel){
	  		var appUid  = rowModel.data.APP_UID;
	  	}
	  	for (i=0; i<arrayActions.length; i++)
		{ 	
			if( rows.length == 1 ) 
			{
				if(contextMenuItems[i].itemId== i + 1 )
				{
					if(arrayActions[i].ROWS_AFFECT == 'multiple')
						contextMenuItems[i].setDisabled(true);
					else
						contextMenuItems[i].setDisabled(false);
				}	
			}
			else
			{
				if(contextMenuItems[i].itemId== i + 1 )
				{
					if(arrayActions[i].ROWS_AFFECT == 'one')
						contextMenuItems[i].setDisabled(true);
					else
						contextMenuItems[i].setDisabled(false);
				}	
			}
		}
		     
	}
	function enableDisableMenuOption(){
		var rows = gridInboxDinamic.getSelectionModel().getSelections();
	  	var sLen ='',sLen2='0xFreq'; 
	  	if(rows.length == 0) {
			sLen ='none';
		}if(rows.length == 1) {
			sLen ='one'; sLen2='oneMore';
		}else if(rows.length > 1){
			sLen ='multiple';sLen2='oneMore';
		}
	  	for (i=0; i<arrayActions.length; i++){ 	
	  		for (k=0; k<menuItems.length; k++) {
			    if(contextMenuItems[k].itemId== i + 1 ){ 
			    	
			    	if((arrayActions[i].ROWS_AFFECT == sLen) || (arrayActions[i].ROWS_AFFECT == sLen2) )
			    	{
			    		var conditionField = verificateConditionAction(arrayActions[i].NAME_ACTION);
			    		//console.log(conditionField);
				    	if(conditionField == 1)
				    		contextMenuItems[k].setHidden(false);
				    	else
				    		contextMenuItems[k].setHidden(true);
			    	}
					else
						contextMenuItems[k].setHidden(true);	
				}
			}
		}     
	}
	function dataGridreview()
	{
		var miArray = new Array();  
		var i = 0;
		var idField = '';
		if(checkSelect) 	
		{
			checkSelect.each(function(record)  
	    	{  
	    		record.fields.each(function(field) 
	    		{ 
	    			var selections = gridInboxDinamic.getSelectionModel().getSelections();
	    			var fieldValue = record.get(field.name);
	    			var item = {
					        "value": fieldValue
					    };
	    			i++;
	    			miArray.push(item);
	    			
	    		}); 
	    	});
		}
		return myJSON = Ext.util.JSON.encode({miArray: miArray});
		// printData(myJSON);
		 
	}
	
	function printData(idField)
	{
		var idField = dataGridreview();
		var win = new Ext.Window({
			closable: true,
			maximizable: true,
			title: 'Details of Item:',	
			width:450,
			height: 280,
			modal: true,
			loadMask : true,
			items : [{
				xtype : "component",
				id    : 'iframe-win1',  // Add id	
				loadMask : true,
				autoEl : {
					tag : "iframe",
				    frameborder : '0',
					width: '100%',
					height: '100%',				        
				    loadMask : true			            			             
				}
			}],
			buttons:[{
				text:'Close Panel',
				handler: function(){
					win.destroy();
				}
			}]
		});
		if(idField != '')
		{	
			win.show();
			Ext.getDom('iframe-win1').src = "../convergenceList/printData.php?array=" + idField;
		}
		else
		{
			win.show();
			Ext.getDom('iframe-win1').html = 'test';
		}
						
	}
		
	function outputDocuments()
	{
		var win = new Ext.Window({
						
		closable: true,
		maximizable: true,
		title: 'Output Documents:',	
		width:650,
		height: 480,
		loadMask : true,
		items : [{
			xtype : "component",
			id    : 'iframe-output',  // Add id	
			loadMask : true,
			autoEl : {
				tag : "iframe",
				frameborder : '0',
				width: '100%', 
				height: '100%',				        
				loadMask : true			            			            
			}
		}],
		buttons:[{
			text:'Close Panel',
			handler: function(){
				win.destroy();
			}
		}]
		});
		win.show();
		Ext.getDom('iframe-output').src = "../appProxy/requestOpenSummary";	
	}
		
	function msgBox(title, msg, type)
	{
		if( typeof('type') == 'undefined' )
			type = 'info';

		switch(type){
		case 'error':
			icon = Ext.MessageBox.ERROR;
			break;
		case 'info':
		default:
			icon = Ext.MessageBox.INFO;
			break;
		}
		Ext.Msg.show({
			title: title,
			msg: msg,
			fn: function(){},
			animEl: 'elId',
			icon: icon,
			buttons: Ext.MessageBox.OK
		});
	}
		  
	var openOutputsDocuments = function(appUid, delIndex, action)
	{
		if (summaryWindowOpened) {
		      return;
		}
		summaryWindowOpened = true;
		    Ext.Ajax.request({
		      url : '../appProxy/requestOpenSummary',
		      params : {
		        appUid  : appUid,
		        delIndex: delIndex,
		        action: action
		      },
		      success: function (result, request) {
		        var response = Ext.util.JSON.decode(result.responseText);
		        if (response.success) {
		          var sumaryInfPanel = PMExt.createInfoPanel('../appProxy/getSummary', {appUid: appUid, delIndex: delIndex, action: action});
		          sumaryInfPanel.setTitle(_('ID_GENERATE_INFO'));

		          var summaryWindow = new Ext.Window({
		            title:'Output Document',
		            layout: 'fit',
		            width: 600,
		            height: 450,
		            resizable: true,
		            closable: true,
		            modal: true,
		            autoScroll:true,
		            constrain: true,
		            keys: {
		              key: 27,
		              fn: function() {
		                summaryWindow.close();
		              }
		            }
		          });

		          var tabs = new Array();
		         
		          tabs.push({title: Ext.util.Format.capitalize(_('ID_GENERATED_DOCUMENTS')), bodyCfg: {
		            tag: 'iframe',
		            id: 'summaryIFrame',
		            src: '../cases/ajaxListener?action=generatedDocumentsSummary',
		            style: {border: '0px none',height: '450px'},
		            onload: ''
		          }});
		          var summaryTabs = new Ext.TabPanel({
		            activeTab: 0,
		            items: tabs
		          });
		          summaryWindow.add(summaryTabs);
		          summaryWindow.doLayout();
		          summaryWindow.show();
		        }
		        else {
		          PMExt.warning(_('ID_WARNING'), response.message);
		        }
		        summaryWindowOpened = false;
		      },
		      failure: function (result, request) {
		        summaryWindowOpened = false;
		      }
		    });
	}
	
	function fn_open_frames()
	{
		var aIframes = document.getElementsByTagName('iframe');
		for(var con = 0; con <= aIframes.length ; con++)
		{
			if(aIframes[con] != undefined)
			{
				document.getElementById(aIframes[con].id).style.width = '100%';
				document.getElementById(aIframes[con].id).style.height = '100%';			
			}	
		}	
	}
	
	function verificateConditionAction(nameAction)
	{
		var swCondition = 1;
		var swVerificate = 1;
		var swField = '';
		var parameterSendValue = new Array();
		var rowModel = gridInboxDinamic.getSelectionModel().getSelected();
		if(checkSelect) 	
		{
			checkSelect.each(function(record)  
	    	{  
				
				record.fields.each(function(field) 
	    		{ 
					
					for(var i = 0; i< arrayConditionActions.length; i++)
					{  
						nameActionCondition = arrayConditionActions[i].NAME_ACTION;
						if (rowModel &&  nameActionCondition == nameAction) {
							fn = arrayConditionActions[i].FLD_UID;
						   
							if(fn != '' && field.name == fn)
							{
								//console.log(field);
								//console.log(record.get(field.name));
								var parameterValue = record.get(field.name);
								var condition = arrayConditionActions[i].OPERATOR;
								var parameters = arrayConditionActions[i].PARAMETERS_BY_FIELD;
								var conditionAux = condition;
								var parametersAux = '';
								if(conditionAux == 'IN')
								{
									var parameterSend =  parameters.split(",");
									total = parameterSend.length;
									condition = '==';
									for (j=0; j < total; j++)
									{
										fn = parameterSend[j];
										
										if( j==0 )
										{
											parametersAux  = '"' + parameterValue + '"' + condition + '"' + fn.trim() ;
										}
										else
										{
											parametersAux =  '"' + parameterValue + '"' +condition + '"' + fn.trim() +  '" || ' + parametersAux ;
										}
									}
									
									var evaluation = 'if( ' + parametersAux + '") swCondition = 1; else swCondition = 0;' ;
									//console.log(evaluation);
								}
								else
								{
									var evaluation = 'if( "' + parameterValue + '"' +condition + '"' + parameters + '") swCondition = 1; else swCondition = 0;' ;
									//console.log(evaluation);
								}
                                                                
								eval(evaluation);
							}
							else
								swField = 0;
							if(swCondition == 0 && swVerificate == 1)
							{
								swVerificate = 0;
								swField = 1;
							}
							
						}
					
						
					}
	    			
	    		}); 
	    	});
		}
		
		return swVerificate;
	}
	
	function isset(val)  
	{  
	    if (typeof val !== 'undefined' && val != null)   
	    {  
	        return true;  
	    }  
	  
	    return false;  
	}  
	

});