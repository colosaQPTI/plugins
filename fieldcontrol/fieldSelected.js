
Ext.onReady(function() 
{
	var CustomName = 'Custom Column';
	var titleGrid = 'Inbox Column List:';
	var select = 'Select'
	// Variables for language
	var CustomColumnsTitle = 'Custom Columns';
	var CustomColumnsNew = 'New Select Query';
	var CustomColumnsEdit = 'Edit Select Query';
	var CustomColumnsRemove = 'Remove Select Query';
	var CustomColAdd = 'Add Select Query';
	var CustomColParameters = 'Parameters Select';
	var CustomColAddParam = 'Add Parameter';
	var CustomColEmpty = 'There are no actions to display';
	var CustomColEmpty2 = 'No actions to display';
	var Action = 'Actions';
	var ActionAdd = 'Add actions to inbox';
	var ActionEdit = 'Edit actions to inbox';
	var ActionRemove = 'Remove actions to inbox';
	var ActionSave = 'Save actions';
	var ActionFields = 'By fields';
	var ActionNewInbox = 'New actions of inbox';
	var ActionParamSent = 'Parameters sent function';
	var ActionAddInbox = 'Add actions of inbox';
	var MsgSave = "The data was saved sucessfully!";
	var MsgRemove = "The data was removed sucessfully!";
	var Save ="Save";
	var Cancel ="Cancel";
	var ActionRemInbox = 'Remove actions of inbox';
	var ActionEditInbox ="Edit Actions of Inbox";
	var ActionInboxTitle="Action Inbox";
	var MsgOperation = 'The operation completed sucessfully!';
	var MsgOpError = 'The operation was not completed sucessfully!';
	var MsgSelectItem='Select Items please';
	var lanSaveConditions='Save Conditions';
	var lanDisplaying = 'Displaying {0} - {1} of {2}';
	var lanActionRemTitle ='Remove Select Query of Inbox';
	var lanActionDelPar = "Delete Parameter";
	var lanAddWhere = "Add Where to Inbox";
	var lanEditWhere = "Edit Where to Inbox";
	var lanRemWhere = "Remove Where to Inbox";
	var lanAddConfig = "&nbsp; Add Config Users";
	var lanPLeaseWhere= "Please type the Where Statement";
	var lanSaveQuery= "Save Query";
	var lanRemConfirm= "Do you want to remove this where statement?";
	var lanConfigUser ="Config User Where:";
	var lanQueryReq="the query is required!";
	var lanAddEdit = "Add & Edit Join";
	var lanPleaseQuery ="Please type the Query Statement:";
	
	if(language == 'fr')
	{
		CustomName = 'Colonne Personnalis\u00E9e';
		titleGrid = 'Inbox Liste des Colonnes:';
		select = 'S\u00E9lectionner';
		CustomColumnsTitle= 'Colonnes personnalis\u00E9es';
		CustomColumnsNew= 'Nouveau Select Query';
		CustomColumnsEdit= 'Edition Select Query';
		CustomColumnsRemove= 'Supprimer Select Query';
	    CustomColAdd = 'Ajouter Select Query';
		CustomColParameters = 'Param\u00E8tres Select';
		CustomColAddParam = 'Ajouter un param\u00E8tre';
		CustomColEmpty = "Il n'y a aucune action \u00E0 afficher";
		CustomColEmpty2 = 'Aucune action \u00E0 afficher';
		ActionAdd= "Ajouter actions de Inbox";
		ActionEdit = "Editer actions de Inbox";
		ActionRemove= "Supprimer actions de Inbox";
		ActionSave= "Sauver actions";
		ActionFields = "Par champs";
		ActionNewInbox = "Nouvelle action de inbox";
		ActionParamSent = "Param\u00E8tres envoy\u00E9s fonction";
		ActionAddInbox="Ajouter action de inbox";
		MsgSave="Les donn\u00E9es ont \u00E9t\u00E9 enregistr\u00E9es avec succ\u00E8s!";
		Save="Sauver";
		Cancel ="Annuler";
		ActionRemInbox= "Supprimer action de inbox";
		MsgRemove="Les donn\u00E9es ont \u00E9t\u00E9 supprim\u00E9es avec succ\u00E8s!";
		ActionEditInbox ="Editer actions de Inbox";
		ActionInboxTitle ="Action Inbox";
		MsgOperation="L'op\u00E9ration s'est termin\u00E9e avec succ\u00E8s!";
		MsgOpError="L'op\u00E9ration n'a pas été compl\u00E9t\u00E9e avec succ\u00E8s!";
		MsgSelectItem="S\u00E9lectionnez les Articles veuillez";
		lanSaveConditions ="Sauver conditions";
		lanDisplaying = 'Affichage {0} - {1} sur {2}';
		lanActionRemTitle = "Supprimer Select Query de Inbox";
		lanActionDelPar = "Supprimer Param\u00E8tre";
		lanAddWhere = "Ajouter Where au Inbox";
		lanEditWhere = "Editer Where au Inbox";
		lanRemWhere = "Supprimer Where au Inbox";
		lanAddConfig = "&nbsp; Ajouter des utilisateurs config";
		lanPLeaseWhere= "S'il vous pla\u00EEt taper le instruction Where";
		lanSaveQuery= "Sauver Query";
		lanRemConfirm= "Voulez-vous supprimer cette d\u00E9claration Where?";
		lanConfigUser ="Config utilisateur Where";
		lanQueryReq="La Query est nécessaire!";
		lanAddEdit = "Ajouter & Edit Join";
		lanPleaseQuery ="S'il vous pla\u00EEt entrez le Query Statement:";
		
	}	
	var tooltipRenderer = function(data, metadata, record, rowIndex, columnIndex,store) 
	{
		metadata.attr = 'ext:qtip="' + data + '" style="white-space: normal; "';
		return data;
	};
	
	function redirect(href) {
	        parent.location.href = href;
	}
	
	function addTooltip(value, metadata, record, rowIndex, colIndex, store){
	    metadata.attr = 'ext:qtip="' + value + '"';
	    return value;
	}

	var TableComboStore = new Ext.data.Store({
		proxy : new Ext.data.HttpProxy({url: 'ajaxTableCombo.php?TYPE=TableCombo'}),
		reader : new Ext.data.JsonReader({
			root   : 'data',
			fields : [
				{name : 'ID'},
				{name : 'NAME'},
				{name : 'INNER_JOIN'}
			]
		})
	});
	TableComboStore.load();
	
	var InboxComboStore = new Ext.data.Store({
		
		proxy : new Ext.data.HttpProxy({url: 'ajaxInboxCombo.php?rolID=' + rolID}),
		reader : new Ext.data.JsonReader({
			root   : 'data',
			fields : [
				{name : 'ID'},
				{name : 'NAME'}
			]
		})
			
	});
	InboxComboStore.load();
	
	 var functionsStore = new Ext.data.Store({
			proxy : new Ext.data.HttpProxy({url: 'listFunctions.php'}),
			reader : new Ext.data.JsonReader({
				root   : 'data',
				fields : [
					{name : 'ID'},
					{name : 'NAME'},
					{name : 'PARAMETERS_FUNCTION'}
				]
			})
		});
	 functionsStore.load();
	
	 var store = new Ext.data.JsonStore({
			url           : 'ajaxListRoles.php?rolID=' + rolID,
			root          : 'data',
			totalProperty : 'total', 
			remoteSort    : true,
			autoWidth     : true,
			fields        : ['ADD_TAB_NAME','FLD_UID', 'FLD_DESCRIPTION', 'FIELD_NAME', 'ROL_CODE','INNER_JOIN','FIELD_REPLACE','ID_INBOX', 'ALIAS_TABLE','COLOR' , 'ORDER_BY', 
			                 {name: 'INCLUDE_OPTION', type: 'bool', 
								convert   : function(v){
									return (v === "A" || v === true) ? true : false;
	        					}
			                 },{name: 'HIDDEN_FIELD', type: 'bool', 
			                	 convert   : function(v){
			                	 	return (v === "A" || v === true) ? true : false;
			                 	}
			                 },{name: 'INCLUDE_FILTER', type: 'bool', 
			                	 convert   : function(v){
			                	 	return (v === "A" || v === true) ? true : false;
			                 	}
			                 }]
			});
		//store.load();
		 
	var TableCombo = new Ext.form.ComboBox({
		valueField    : 'ID',
		displayField  : 'NAME',
		id            : 'idTableCombo',
		fieldLabel    : '<span style="color: red">*</span>' + select + ' Table',
		emptyText     : select + ' Table...',
		typeAhead     : true,
		triggerAction : 'all',
		editable      : false,
		mode          : 'local',
		width         : 200,
		allowBlank    : false,
		store         : TableComboStore,
		name		  : 'idTableCombo',
		hiddenName	  : 'idTableCombo',
		disabled      : true,
		selectOnFocus: false,
		listeners     :{
			select : function(combo, record) {
				store.setBaseParam('idTable', combo.getRawValue());
				var idTable = combo.getRawValue();
		 		buttonQuery=Ext.getCmp('idButtonQuery');
		 		buttonQuery.setDisabled(false);
		 		buttonInsertQuery = Ext.getCmp('idButtonInsertQuery');
		 		buttonInsertQuery.setDisabled(false);
		 		buttonWhereQuery = Ext.getCmp('idButtonWhereQuery');
		 		buttonWhereQuery.setDisabled(false);
		 		buttonAction = Ext.getCmp('idActionButton');
		 		buttonAction.setDisabled(false);
		 		buttonConcat = Ext.getCmp('idConcatFields');
		 		buttonConcat.setDisabled(false);
		 		Ext.getCmp('idQuery').setValue('');
		 		//Ext.getCmp('idQuery').setValue(record.data.INNER_JOIN);
		 		idInbox = Ext.getCmp('idInboxCombo').getValue();	
				store.load({
					params : {
						'idTable' :combo.getRawValue(),
						'idInboxData' :idInbox,
						'swinner' : 1
					}
				});
			}  
		}
	});
	
	var InboxCombo = new Ext.form.ComboBox({
		valueField    : 'ID',
		displayField  : 'NAME',
		id            : 'idInboxCombo',
		fieldLabel    : '<span style="color: red">*</span>' + select + ' Inbox',
		emptyText     : select +' Inbox...',
		typeAhead     : true,
		triggerAction : 'all',
		editable      : false,
		mode          : 'local',
		width         : 200,
		allowBlank    : false,
		store         : InboxComboStore,
		name		  : 'idInboxCombo',
		hiddenName	  : 'idInboxCombo',
		selectOnFocus :false,
		listeners     :{
			select : function(combo, record) {
				TableCombo.setDisabled(false);
		 		TableComboAux=Ext.getCmp('idTableCombo');
		 		TableComboAux.clearValue();
		 		var idTable = TableComboAux.getValue();
		 		Ext.getCmp('grid').render();
		 		var idInbox = combo.getValue();	
		 		store.removeAll();
				loadComboTable(idInbox);
			}  
		}
		
	});
	loadComboTable = function (ID_INBOX)  {
		 Ext.Ajax.request({
		        method: "POST",
		        params : {
		          "idInbox" : ID_INBOX
		        },
		        url : '../fieldcontrol/ajaxListRoles.php?rolID=' + rolID
		        ,
		        success : function(result) {
			          var data = Ext.util.JSON.decode(result.responseText);
			            if (data.success && data.data.length > 0) {
			        	  var idTable = data.data[0].ID;
			        	  if(idTable != '')
			        	  {
			        		  buttonQuery=Ext.getCmp('idButtonQuery');
			        		  buttonQuery.setDisabled(false);
			        		  buttonInsertQuery=Ext.getCmp('idButtonInsertQuery');
			        		  buttonInsertQuery.setDisabled(false);
			        		  buttonWhereQuery=Ext.getCmp('idButtonWhereQuery');
			  		 		  buttonWhereQuery.setDisabled(false);
			  		 		  buttonAction = Ext.getCmp('idActionButton');
			  		 		  buttonAction.setDisabled(false);
			  		 		  buttonAction = Ext.getCmp('idConcatFields');
			  		 		  buttonAction.setDisabled(false);		  		 		
					 		
			        	  }
			        	 
			        	  Ext.getCmp('idTableCombo').setValue(data.data[0].ID);
			        	  Ext.getCmp('idQuery').setValue(data.data[0].INNER_JOIN);
			        	  Ext.getCmp('idTableCombo').setRawValue(data.data[0].NAME);
			        	  idInbox 	 = Ext.getCmp('idInboxCombo').getValue();	
			        	  store.load({
			        		  params : {
								'idTable' 	  :idTable,
								'idInboxData' :idInbox
								},
								callback: function(records, operation, success) {
					            	var error = store.reader.jsonData.response;
					            	var self = this;
					            	if(success == true)
					            		Ext.MessageBox.hide();
					            	else
					            		Ext.MessageBox.alert('Error', error);
					            }
			        	  })
			          }
			          else
		        	  {
		        		  buttonQuery=Ext.getCmp('idButtonQuery');
		        		  buttonQuery.setDisabled(true); 
		        		  buttonInsertQuery=Ext.getCmp('idButtonInsertQuery');
		        		  buttonInsertQuery.setDisabled(false);
		        	  }
			        }
		 })
	 };
	 
	
    // / --------- Head ----------- ///
	
	 var enable = true;
	 saveFields = new Ext.Action({
	        text    :_('ID_SAVE'),
	        iconCls :'button_menu_ext ss_save',
			id      : 'addup',
	        handler : dataGridreview
	 });

	 whereQuery = new Ext.Action({
	        text     : 'Where Query',
	        id      : 'idButtonWhereQuery',
			icon    : '/plugin/fieldcontrol/insert_query.png', 
	        handler  : Fn_LoadWhereInbox,
	        disabled : true
	 });   
	 ActionButton = new Ext.Action({
	      text     : 'Actions',
	      id	   : 'idActionButton',
	      iconCls  : 'button_menu_ext ss_sprite  ss_action',
	      tooltip  : 'Management actions that will have the inbox',
	      handler  : Fn_LoadActionsInbox,
	      disabled : true
	  });
	 
	 ConcatFields = new Ext.Action({
	      text     : CustomName,
	      id	   : 'idConcatFields',
	      iconCls  : 'button_menu_ext ss_sprite  ss_sql',
	      tooltip  : 'Management actions that will have the inbox',
	      handler  : Fn_ConcatFields,
	      disabled : true
	  });
	 
    var FieldPanelToolBars = new Ext.FormPanel({            
		frame      : true,
		labelAlign : 'center',
		labelStyle : 'font-weight:bold;',  
		height     : 95,  
		items      : [{
			layout  	: {
				type    : 'table',
				colspan : 4
		    },
		    defaults 	: {
		    	padding  : 10,  
		    	border 	 : false
			},
          	border		: false,
          	labelWidth 	: 120, 
          	items: [{
				rowspan  : 2,
				xtype    : 'panel',
				defaults : {
					"width" : 220
				},
				layout : "form",
				border : false,
				items  : [
				    InboxCombo, TableCombo
				]
			},
			{
				rowspan  : 2,
				xtype    : 'panel',
				defaults : {
					"width" : 220,
					"height": 50,
					border  : false
				},
				layout : "form",
				border : false,
                items: [{
                	labelAlign	: 'right',
                	fieldLabel	: 'Join',
				    xtype 		: 'textarea',
				    id			: 'idQuery',     
				    disabled	: true,
				    width 		: 220,
				    forceSelection: true,       
				    emptyText	: 'Insert query...',  
				    triggerAction: 'all',      
				    editable	:false				    
                }]
			},
			{          
				rowspan: 2,
				defaults: {
					"width": 15,
                    border : false
				},
				items: 
					[{
		             	xtype	: 'button',
		             	text    :' Add Join ', 
		    			id      : 'idButtonInsertQuery',
		    			disabled: true,
		    			icon    : '/plugin/fieldcontrol/insert_query.png', 
		    			iconCls :'addQuery',
		    			handler : function() {							
							var innerJoin = Ext.getCmp('idQuery').getValue();
							popupQuery();
		            	}
					} , {
		             	xtype	: 'button',
		             	text    :' Execute <br> Query ', 
		    			id      : 'idButtonQuery',
		    			disabled: true,
		    			icon    : '/plugin/fieldcontrol/fieldSelected1.png', 
		    			iconCls : 'executeQuery',
		    			tooltip : 'Edit',
		    			handler : function() {
							
							var innerJoin = Ext.getCmp('idQuery').getValue();
							var idTable   = Ext.getCmp('idTableCombo').getValue();
							if(idTable == '')
								idTable = idpmTable;
							if(innerJoin != '')
							{
								FieldName.setDisabled(false);
								executeInner(innerJoin, idTable);
							}
							else
							{
								FieldName.setDisabled(true);
							}
		            	} 
		             }
				]
				
        	}]
		
      	}]
	});
  
//End Head
    
    var pager = new Ext.PagingToolbar({
        store       : store, 
        displayInfo : true,
        autoHeight  : true,
        displayMsg  : 'Accounts {0} - {1} Of {2}',
        emptyMsg    : 'No Accounts to show',
        pageSize    : 500
       });	
        

    var checkColumnInclude = new Ext.grid.CheckColumn({
    	header: 'Include ?',
 	   	dataIndex: 'INCLUDE_OPTION',
 	   	id: 'check',
 	   	flex: 1,
 	   	width: 10,
 	   	processEvent: function () { return false; }
 	});
    
    var checkColumnHidden = new Ext.grid.CheckColumn({
    	header: 'Hidden?',
    	dataIndex: 'HIDDEN_FIELD',
    	id: 'checkHidden',
    	flex: 1,
    	width: 8,
    	processEvent: function () { return false; }
  	}); 
    
    var checkColumnFilter = new Ext.grid.CheckColumn({
   	   header: 'Filter?',
   	   dataIndex: 'INCLUDE_FILTER',
   	   id: 'checkFilter',
   	   flex: 1,
   	   width: 8,
   	   processEvent: function () { return false; }
   	});
   
    var checkColumnConcat = new Ext.grid.CheckColumn({
    	header: 'Concat ?',
 	   	dataIndex: 'INCLUDE_OPTION',
 	   	id: 'check',
 	   	flex: 1,
 	   	width: 10,
 	   	processEvent: function () { return false; }
 	});
    
    var orderByStore = new Ext.data.SimpleStore({
        fields: ['ID', 'NAME'],
        data: [[' ', ' NONE '], ['ASC',' ASC '], 
            ['DESC',' DESC ']],
        autoLoad: true 
    });    

    var checkSelect5 = new Ext.grid.CheckboxSelectionModel();
       
    var inner =  new Ext.form.TextArea ({
    	allowBlank : true,
    	height     : 50,
    	disabled   : false,
    	ref		   : 'inner',
    	anchor     : '100%',
    	listWidth	  : 250
	});
    
    var parameters =  new Ext.form.TextArea ({
		allowBlank : true,
		height     : 50,
		disabled   : true,
		anchor     : '100%'
    });
    
    var description =  new Ext.form.TextField ({
		allowBlank : true,
		height     : 50,
		disabled   : false,
		anchor     : '100%'
    });
    

    var orderBy = new Ext.form.ComboBox({
		valueField    : 'ID',
		displayField  : 'NAME',
		id            : 'idOrderBy',
		emptyText     : 'Select order...',
		typeAhead     : true,
		triggerAction : 'all',
		editable      : true,
		mode          : 'local',
		width         : 200,
		listWidth	  : 250,
		allowBlank    : false,
		disabled      : false,
		store         : orderByStore
    });
     
    var clearField = {
    		xtype: 'button',
    		icon : '/images/icons_silk/calendar_x_button.png',                        
    		width : '10',
    		handler: function(){
    			Ext.getCmp('startDate').reset();	                                                        
    		}
    };
    
    var FieldNameStore = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({url: 'ajaxInnerJoin.php?rolID=' + rolID}),
        
        reader: new Ext.data.JsonReader({
            root: 'data',
            fields: [
                {name: 'ID'},
                {name: 'NAME'}
            ]
        })
    });
    
    var FieldName = new Ext.form.ComboBox({
		valueField    : 'ID',
		displayField  : 'NAME',
		id            : 'idFieldName',
		fieldLabel    : '<span style="color: red">*</span>Choose Fields ',
		emptyText     : 'Select a Field...',
		typeAhead     : true,
		triggerAction : 'all',
		editable      : true,
		mode          : 'local',
		width         : 200,
		allowBlank    : false,
		disabled      :true,
		store         : FieldNameStore
    });
   
    var gridcolumns = new Ext.grid.ColumnModel({
		defaults : {
    		width : 20,
    		sortable : true
    	},
    	columns : [
    	{
    		header    : "#",
    		width     : 5,
    		sortable  : true,
    		hidden    : true,
    		dataIndex : 'FLD_UID'
    	}, {
    		header    : "Field Name",
    		width     : 15,
    		sortable  : true,
    		dataIndex : 'FIELD_NAME'
    	}, {
    		header    : "Field Description",
    		width     : 15,
    		sortable  : true,
    		dataIndex : 'FLD_DESCRIPTION',
    		editor	  : description
    	},  checkColumnInclude, checkColumnHidden, checkColumnFilter, 
    	{
    		header    : "Order By",
    		dataIndex : 'ORDER_BY', 
    		width     : 10,
    		sortable  : true,
    		editor    : orderBy,
    		hidden    : false
		
    	},
    	{
    		header    : "Table",
    		dataIndex : 'ADD_TAB_NAME', 
    		width     : 15,
    		sortable  : true,
    		hidden	  : true
		
    	}]
    
    });
    
	var gridInbox = new Ext.grid.EditorGridPanel({
		store 		   : store,
		columnLines	   : true,
		id 			   : 'grid',
		ddGroup		   :'gridDD',
		enableDragDrop : true,
		cm 			   : gridcolumns,
		tbar           : FieldPanelToolBars,
		plugins        : [checkColumnInclude, checkColumnHidden, checkColumnFilter],
		title          : titleGrid,
		stateId        : 'grid',
		border         : false,
		loadMask       : true,
		autoShow       : true, 
		autoFill       : true,
		nocache        : true,
		stateful       : true,
		animCollapse   : true,
		enableDragDrop : true,
		stripeRows     : true,
		ddGroup        : 'gridDD',
		bbar		   : pager,
		selModel       : new Ext.grid.RowSelectionModel({singleSelect : true}),
		listeners      : {  //drag and drop
			"render": {
		  		scope: this,
		  		fn: function(grid) {
					var ddrow = new Ext.dd.DropTarget(grid.container, {
						ddGroup : 'gridDD',
						copy:false,
						notifyDrop : function(dd, e, data){
							var ds = grid.store;
							var sm = gridInbox.getSelectionModel();
		                    var rows = sm.getSelections();
		                    if(dd.getDragData(e)) {
		                    	var cindex=dd.getDragData(e).rowIndex;
		                        if(typeof(cindex) != "undefined") {
		                        	for(i = 0; i <  rows.length; i++) {
		                        		ds.remove(ds.getById(rows[i].id));
		                            }
		                            ds.insert(cindex,data.selections);
		                            sm.clearSelections();
		                         }
		                    }
						}
		         }) 
				//store.load();
		      }
		   }
		},
		
		viewConfig     : {
			forceFit     :true,
			scrollOffset : 2,
			sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
            sortAscText	 : '  Ascending ',
            sortDescText : '  Ascending ',
            getRowClass  : function (row, index, rowParams, ds) {
                
                Ext.each(row.get('COLOR'), function(color, index) {
    			 	rowParams.tstyle += "background-color:" + color + ';';
    			});
               
            } 
		   
		}
	});
	
	// back roles
	BackToRoles = function(){
		location.href = 'groupsRoles';
	}; 
	
	backButton = new Ext.Action({
		text: _('ID_BACK'),
		iconCls: 'button_menu_ext ss_sprite ss_arrow_redo',
		handler: BackToRoles
	});
	
    northPanel = new Ext.Panel({
    	region: 'north',
    	xtype: 'panel',
    	tbar: ['<b>'+_('ID_ROLE') + ' : ' + rolName +'</b>',{xtype: 'tbfill'},backButton]
    });
    
	var fieldInboxPanel = new Ext.Panel({
		autoWidth    : true,
		height       : 550,
		layout       : 'fit',
		autoScroll	 : true,
		items        : [
			gridInbox
		],
		tbar           : [saveFields, '-',whereQuery, '-',ActionButton, '-', ConcatFields]
		
	});
	
	tabsPanelField = new Ext.Panel({
       	region: 'center',
    	activeTab: 0,
    	items:[fieldInboxPanel]
    });
	
	
	var viewport = new Ext.Viewport({
		layout : 'border',
		items  : [northPanel, tabsPanelField]
	});

	function popupQuery()
	{
		formQuery.getForm().reset();
		var textQuery = Ext.getCmp('idQuery').getValue();
		formQuery.getForm().findField('queryfield').setValue(textQuery);
		  
	    wQuery = new Ext.Window({
	        title       : lanAddEdit,
	        closeAction : 'hide',
		    autoDestroy : true,
		    maximizable : true,     
	        modal       : true,
	        id          : 'popupQuery',
	        width 	    : 600,
		    height 	    : 312,   
		    closable    : true,
			constrain   : true,
			autoScroll  : true,
	        items       : [formQuery],
	        layout      : 'fit'
	    });
	    
	    wQuery.show();
	}

	 
	//Close Popup Window
	CloseWindow = function(){
	    Ext.getCmp('popupQuery').hide();
	};
	
	formQuery = new Ext.FormPanel({
		frame 	: true,
		items 	:[{
			id			:'queryfield', 
			xtype		: 'textarea', 
			fieldLabel	: lanPleaseQuery, 
			name		: 'nameQuery', 
			width		: 450,
			height		: 200,
			disabled	: false
		}],
		buttons	: [
		    {text : Save , handler:  saveQuery},
		    {text : Cancel, handler: CloseWindow}
		]
	});
	
	function saveQuery() {
		 textQuery = formQuery.getForm().findField('queryfield').getValue();
		 Ext.getCmp('idQuery').setValue(textQuery);
		 var idTable   = Ext.getCmp('idTableCombo').getValue();
         
		 if(idTable == '')
			idTable = idpmTable;
		 if(textQuery == ''){
		 store.load({
				params: {
					inner   : textQuery,
					idTable : idTable,
					idInboxData : idInbox,
					swinner : 1
	            },
	            callback: function(records, operation, success) {
	            	var error = store.reader.jsonData.response;
	            	var self = this;
	            	if(success == true)
	            		Ext.MessageBox.hide();
	            	else
	            		Ext.MessageBox.alert('Error', error);
	            }
	        });
		 }
		 CloseWindow();
		 
	
	};


	function saveDataField(myArray, idInbox)
	{  
		var rowModel = gridInbox.getSelectionModel().getSelected();
		var idTable   = Ext.getCmp('idTableCombo').getValue();
	    var sw = 0;  	
	        Ext.MessageBox.show({
				msg          : 'Save data..',
				progressText : 'send...',
				width        : 300,
				wait         : true,
				waitConfig   : {
	                interval : 200
	            }
	        });

	        Ext.Ajax.request({
	            params : {        
	        		myArray : myArray,
	        		idRoles : rolID,
	        		idInbox : idInbox,
	        		idTable	: idTable
	            },
	            url : '../fieldcontrol/permissionsField_Save.php',
	            success : function(save) {
	                var data = Ext.decode(save.responseText);
	                var url  = data.success;
	                gridInbox.getStore().commitChanges();
	                gridInbox.getStore().reload();
	                Ext.MessageBox.hide();
	                Ext.getCmp('idFieldName').setValue('');
	                var idTable   = Ext.getCmp('idTableCombo').getValue();
	                var innerJoin = Ext.getCmp('idQuery').getValue();
					if(idTable == '')
						idTable = idpmTable;
					 store.load({
							params: {
								inner   : innerJoin,
								idTable : idTable,
								idInboxData : idInbox
				            },
				            callback: function(records, operation, success) {
				            	var error = store.reader.jsonData.response;
				            	var self = this;
				            	if(success == true)
				            	{
				            		Ext.MessageBox.hide();
				            		verificationFields(idInbox, rolID, myArray);
				            	}
				            	else
				            		Ext.MessageBox.alert('Error', error);
				            }
				        });
	            },
	            failure : function() {
	            	if(sw == 0)
	            	{
	            		Ext.MessageBox.alert('Error', 'The operation was not completed sucessfully!');
	            		sw = 1;
	            	}
	            }
	     });
	};
	
	function dataGridreview()
	{
		var fieldTable = '';
		var swD     = 0;
		var i       = 0;
		var miArray = new Array ();
		var myJSON  = '';
		store.each(function(record)  
		{  
			record.fields.each(function(field) 
			{ 
				var fieldValue = record.get(field.name);  
				
				if(fieldTable != record.get('ADD_TAB_NAME') + record.get('FLD_UID'))
				{
					fieldTable = record.get('ADD_TAB_NAME') + record.get('FLD_UID');
					idField = record.get('FLD_UID');
					if(record.get('INCLUDE_OPTION') == true)
					{	
						var hiddenField  = 0;
						var filterField  = 0;
						var idTable      = record.get('ADD_TAB_NAME');
						var idInbox 	 = Ext.getCmp('idInboxCombo').getValue();
						var idRoles      = rolID;
						if(swD == 0)
							var innerJoin  = Ext.getCmp('idQuery').getValue();
						else
							var innerJoin  = '';
						var fieldReplace = record.get('FIELD_REPLACE');
						var descripField = record.get('FLD_DESCRIPTION');
						if(fieldReplace == undefined || innerJoin == '')
							fieldReplace 	= '';
						if(record.get('HIDDEN_FIELD') == true)
							hiddenField = 1;
						if(record.get('INCLUDE_FILTER') == true)
							filterField = 1;
						var aliasTable  = record.get('ALIAS_TABLE');
						if(aliasTable == undefined || aliasTable == '')
							aliasTable 	= '';
						var orderBy  = record.get('ORDER_BY');
						if(orderBy == undefined || orderBy == '')
							orderBy 	= '';
						swD ++;
						var j = 0;
						var item = {
							"value"        : i,
							"idTable"      : idTable,
							"idRoles"      : rolID,
							"idField"      : idField,
							"innerJoin"    : innerJoin,
							"fieldReplace" : fieldReplace,
							"idInbox"	   : idInbox,
							"descripField" : descripField,
							"hiddenField"  : hiddenField,
							"filterField"  : filterField,
							"aliasTable"   : aliasTable,
							"orderBy"      : orderBy
	    				};
						i++;
						miArray.push(item);
	    			}
	    		}
	    			
	    	}); 
	    });
		var idInbox 	 = Ext.getCmp('idInboxCombo').getValue();	
		if(miArray.length != 0){
			myJSON= Ext.util.JSON.encode(miArray);
			saveDataField(myJSON, idInbox);
			
		}
		else
		{
			alert('Select Items please');
		}
		
	}
	
	////////// Execute Join /////////////////    
	function executeInner(inner, idTable)
	{ 
		FieldName.setDisabled(false);
		idInbox = Ext.getCmp('idInboxCombo').getValue();	  	
		FieldNameStore.load({
			params: {
				idInboxData : idInbox,	
				inner   : inner,
				idTable : idTable
            }
        });
		
		Ext.MessageBox.show({
			msg			: 'ExecuteQuery, please wait',
	        progressText: 'Saving...',
	        width		:300,
	        wait		:true,
	        waitConfig	: {interval:200},						                    
	        animEl		: 'mb7'
	    });	
		store.load({
			params: {
				inner   : inner,
				idTable : idTable,
				idInboxData : idInbox
            },
            callback: function(records, operation, success) {
            	var error = store.reader.jsonData.response;
            	var self = this;
            	if(success == true)
            		Ext.MessageBox.hide();
            	else
            		Ext.MessageBox.alert('Error', error);
            }
        });
      
	}
	////////// End execute Join /////////////////   
	
	///////// verification  repeated fields ///////
	
	function verificationFields(idInbox, rolID, myArray)
	{
		var aliasTable =  new Ext.form.TextField ({
				allowBlank : true,
				height     : 50,
				disabled   : false,
				anchor     : '100%',
				enableKeyEvents: true, 
            	listeners: {
                    keyup: function(val, e){
                        result = val.getValue();
                        text = Ext.util.Format.uppercase(result);
                        text =  text.replace(/(^\s*)|(\s*$)/g,""); // delete spaces in text
                        text = text.replace(/[\W]/g,"");		//remove special characters
                        val.setValue(text);
                    } , 
                    keypress: function(val, e){
                        result = val.getValue();
                        text = Ext.util.Format.uppercase(result);
                        text =  text.replace(/(^\s*)|(\s*$)/g,""); // delete spaces in text
                        text = text.replace(/[\W]/g,""); // remove special characters
                        val.setValue(text);
                    }
                  }
		  });
		
		var FieldsRepeat_popup_store = new Ext.data.JsonStore({
			url : 'ajaxConcatFields.php?actionInbox_id='+idInbox+'&rolID='+rolID + '&type=verify' + '&dataVerify=' + myArray,
			root : 'data',
			totalProperty : 'total',
			autoWidth : true,
			fields : [ 'ID', 'FIELD_NAME', 'ID_TABLE', 'ALIAS_TABLE', 'ALIAS_FIELD']
		});
		FieldsRepeat_popup_store.load();  
			
		var fieldRepeat_popup_cm = new Ext.grid.ColumnModel([
			       {
			            header		: "Field Name",
			            dataIndex	: 'FIELD_NAME'
					} , {
			            header		: "Table",
			            dataIndex	: 'ID_TABLE'
					} , { 
						header		: "Alias Field",
						dataIndex	: 'ALIAS_FIELD',
						editor		: aliasTable
					} 
				]);
		fieldRepeat_popup_cm.defaultSortable= true;	
		
		
		var fieldsRepeat_popup_grid = new Ext.grid.EditorGridPanel({
			store			: FieldsRepeat_popup_store,
			cm				: fieldRepeat_popup_cm,
			stripeRows		: true,
			autoScroll		: true,
			height 			: 200,
			width 			: 550,
			id			 	:'fieldsRepeat_popup_grid',
			viewConfig 		: {
				forceFit 		: true,
				scrollOffset 	: 0
			}
		});	
		
		add_FieldsRepeat_popup_form = new Ext.FormPanel({
			id: 'add_FieldsRepeat_popup_form',								  
			labelAlign: 'top',
			bodyStyle:'padding:5px 5px 5px 10px',
			autoScroll:true,
			items: [fieldsRepeat_popup_grid]							
		});	
				    
	     FieldsRepeat_popup_window = new Ext.Window({ 
	    	 closeAction : 'hide',
	    	 autoDestroy : true,
	    	 maximizable: true,      
	    	 id: 'FieldsRepeat_popup_window',
	    	 title: 'Fields Repeat ',	           
	    	 width : 500,
	    	 height : 300,            
	    	 modal : true,
	    	 closable:true,
	    	 constrain:true,
	    	 autoScroll:true,
	    	 items : add_FieldsRepeat_popup_form,
	    	 layout: 'fit',
	    	 buttons: [{
		            text: Save,
		            type: 'submit',
		            scope: this,
		            handler: function() {   
	    		 		var arrayFieldsRepeat = new Array ();
	    		 		var totGrid = FieldsRepeat_popup_store.getCount();  
	    		 		var i = 0;
	    		 		var rpta  = 0;
	    		 		var j = 1;
	    		 		FieldsRepeat_popup_store.each(function(record)  
	    	    				{  
	    	    		 			var fielName 		= record.get('FIELD_NAME');
	    	    		 			var aliasField 	 	= record.get('ALIAS_FIELD');
	    	    		 			var aliasTable		= record.get('ALIAS_TABLE');
	    	    		 			var idFieldTable	= record.get('ID');
	    	    		 			
	    	    		 			if(aliasField == '')
	    	    		 			{
	    	    		 				alert("Register alias field in row " + j);
	    	    		 				return 0;
	    	    		 			}
	    	    		 			j++;
	    	    				});
	    		 		FieldsRepeat_popup_store.each(function(record)  
	    				{  
	    		 			var fielName 		= record.get('FIELD_NAME');
	    		 			var aliasField 	 	= record.get('ALIAS_FIELD');
	    		 			var aliasTable		= record.get('ALIAS_TABLE');
	    		 			var idFieldTable	= record.get('ID');
	    		 				
	    		 			if(aliasField != '')
							{	
	    		 				parameters = aliasTable + '.' + fielName + ' AS ' + aliasField;
								Ext.getCmp('add_FieldsRepeat_popup_form').form.submit({
									method: 'POST',
									url: 'SaveQueryInbox.php?method=add&ID=' + idInbox,
									params : {
			                        	parameters 	: parameters,
			                        	fields		: fielName,
			                        	rolID 		: rolID,
			                        	idInbox 	: idInbox,
			                        	idFieldTable: idFieldTable
			                    	},
			                    	success: function(f, a) {                                                
			                    		var data = Ext.decode(a.response.responseText);    
			                    		if(data.success == true){ 
			                    			rpta = 1;
			                    			if(totGrid == i)
			    	    		 			{
			    	    		 				if(rpta == 0)
			    			    		 			alert("Register alias field");
			    	    		 				else
			    	    		 				{
			    	    		 					FieldsRepeat_popup_store.load();

			    	    		 			    	FieldsRepeat_popup_window.close();
			    	    		 			    	var idTable = Ext.getCmp('idTableCombo').getValue();
			    	    		 				 	var idInbox = Ext.getCmp('idInboxCombo').getValue();
			    	    		 				 	Ext.getCmp('grid').store.removeAll();
			    	    		 				 	Ext.getCmp('grid').store.load({
			    	    		 			 			idInbox : idInbox,
			    	    		 			 			idTable : idTable 
			    	    		 			 		});
			    	    		 					Ext.MessageBox.show({                            
					                    				msg : 'The data was saved sucessfully!',
					                    				buttons : Ext.MessageBox.OK,
					                    				icon : Ext.MessageBox.INFO
					                    			});
			    	    		 				}
			    	    		 			}
			                    			
			                    	 }                        
			                    	},            
			                    	failure: function(f, a) { 
			                    		f.markInvalid(a.result.errors);
			                    	}            
								})
							}
	    		 			else
	    		 			{
	    		 				if(rpta == 0 && totGrid == i)
	    		 					alert("Register alias field in row " + i);
	    		 			}

    		 				i++;
							
	    				});
	    		 		
		            }
		        },{
		            text: Cancel,            
		            handler: function (){                
		                Ext.getCmp('FieldsRepeat_popup_window').close();
		            }
		        }]
	     });	
	     
	     FieldsRepeat_popup_store.on('load', function(ds){ 
             varRecordCount= FieldsRepeat_popup_store.getCount(); 
             if(varRecordCount != 0)
    	     {
    	    	 FieldsRepeat_popup_window.show();
    	    	 FieldsRepeat_popup_window.toFront();	
    	     }
	     }); 
	     
	}
	///////// End verification  repeated fields ///////
    
////////////////////////////////Where Action Inbox //////////////////////////////////////////////////////

	WhereInbox_popup_form = new Ext.FormPanel({
		id: 'WhereInbox_popup_form',								  
		labelAlign: 'top',
		bodyStyle:'padding:5px 5px 5px 10px',
		autoScroll:true,
		items: [{
			xtype: 'textfield',
			id:'whereaction', 
			fieldLabel: "Where Field",		
			width: 250, 
			allowBlank: true,
			disabled: false,
			hidden: true		
		},{
			xtype: 'textfield',
			id:'whereIDField', 
			fieldLabel: "Where Field",		
			width: 250, 
			allowBlank: true,
			disabled: false,
			hidden: true		
		},{
			xtype: 'textarea',
			id:'whereTxaField', 
			fieldLabel: lanPLeaseWhere, 
			name: 'parameters', 
			width: 550,
			height: 200, 
			allowBlank: true,
			disabled: false,
			hidden: false		
		}],
		buttonAlign: 'center',
		buttons: [
		          {		
		        	  text: lanSaveQuery,
		        	  handler: function(){
		        	  if (Ext.getCmp('whereTxaField').getValue() != '') {
		        		  Ext.MessageBox.show({ msg: _('ID_PROCESSING'), wait:true,waitConfig: {interval:200} });
		        		  Ext.Ajax.request({
		        			  url: 'SaveWhereInbox.php?ID=' + ID_INBOX,
		        			  params:{
		        			  	whereaction: Ext.getCmp('whereaction').getValue(),
		        			  	whereIDField : Ext.getCmp('whereIDField').getValue(),
		        			  	whereTxaField : Ext.getCmp('whereTxaField').getValue(),
		        			  	rolID : rolID
		        		  	 },
		        		  	 success: function(response) {
		        		  		 Ext.getCmp('whereTxaField').setValue('');
		        		  		 Ext.MessageBox.hide();					
		        		  		 Ext.getCmp('WhereInbox_popup_grid').getStore().reload();
		        		  		 Ext.getCmp('WhereInbox_popup_windowF').hide();
		        		  	 }				
		        		  });
		        	  } 
		        	  else {
		        		  alert(lanQueryReq);
		        		  return false;				
		        	  }

		          }		
		          }]
	});

	var add_WhereInbox_popup = function(ID_INBOX,ROLE_CODE){

		Ext.getCmp('whereaction').setValue('add');

		WhereInbox_popup_windowF = new Ext.Window({
			id:'WhereInbox_popup_windowF',
			closeAction : 'hide',
			autoDestroy : true,
			maximizable: true,        
			title: 'Where Form',	          
			width : 600,
			height : 312,            
			modal : true,
			closable:true,
			constrain:true,
			autoScroll:true,
			items : [WhereInbox_popup_form],
			layout: 'fit'
		});

		WhereInbox_popup_windowF.show();
		WhereInbox_popup_windowF.toFront();

		WhereInbox_popup_windowF.on('hide',function(){			
			Ext.getCmp('whereaction').setValue('');
			Ext.getCmp('whereTxaField').setValue('');
			Ext.getCmp('whereIDField').setValue('');
		});

	}
	
	var edit_WhereInbox_popup = function(ID_WHERE,QUERY_WHERE,ID_INBOX,ROLE_CODE){

		Ext.getCmp('whereIDField').setValue(ID_WHERE);
		Ext.getCmp('whereTxaField').setValue(QUERY_WHERE);
		Ext.getCmp('whereaction').setValue('edit');

		WhereInbox_popup_windowF = new Ext.Window({
			id:'WhereInbox_popup_windowF',
			closeAction : 'hide',
			autoDestroy : true,
			maximizable: true,        
			title: 'Where Form',	          
			width : 600,
			height : 312,            
			modal : true,
			closable:true,
			constrain:true,
			autoScroll:true,
			items : [WhereInbox_popup_form],
			layout: 'fit'
		});

	WhereInbox_popup_windowF.show();
	WhereInbox_popup_windowF.toFront();

	WhereInbox_popup_windowF.on('hide',function(){			
		Ext.getCmp('whereaction').setValue('');
		Ext.getCmp('whereTxaField').setValue('');
		Ext.getCmp('whereIDField').setValue('');
	});

	}

	var WhereInbox_popup_cm = new Ext.grid.ColumnModel([
	new Ext.grid.RowNumberer(),
	{
		header: "Where",
		dataIndex: 'IWHERE_QUERY'
	}
	]);
	WhereInbox_popup_cm.defaultSortable= true;

	function Fn_LoadWhereInbox()
	{
		ID_INBOX =  Ext.getCmp('idInboxCombo').getValue();	;
		idTable   = Ext.getCmp('idTableCombo').getValue();
			ROLE_CODE = rolID;
			var WhereInbox_popup_store = new Ext.data.JsonStore({
				url : 'ajaxWhereInboxPopup.php?actionInbox_id='+ID_INBOX+'&rolID='+rolID,	
				root : 'data',
				totalProperty : 'total',
				autoWidth : true,
				fields : [ 'IWHERE_UID', 'IWHERE_QUERY', 'IWHERE_IID_INBOX','IWHERE_ROLE_CODE','TABLE_NAME']
			});
			WhereInbox_popup_store.load();

			var WhereInbox_popup_bbar = new Ext.PagingToolbar({
			pageSize: 50,
			store: WhereInbox_popup_store,
			displayInfo: true,
			displayMsg: lanDisplaying,
			emptyMsg: "No where queries to display"
			});

			var WhereInbox_popup_grid = new Ext.grid.GridPanel({
				store: WhereInbox_popup_store,
				cm:WhereInbox_popup_cm,
				stripeRows: true,
				autoScroll:true,
				id:'WhereInbox_popup_grid',
				viewConfig : {
					forceFit : true,
					scrollOffset : 0,
					emptyText: 'There are no actions to display'
				},
				bbar: WhereInbox_popup_bbar,
				tbar : [{
					text: lanAddWhere,
					cls : 'x-btn-text-icon',
					icon : '/images/ext/default/tree/drop-add.gif',
					handler: function(){
						add_WhereInbox_popup(ID_INBOX,ROLE_CODE);
					}
				}, {
					text: lanEditWhere,
					cls : 'x-btn-text-icon',
					icon : '/images/edit-table.png',
					id : 'editWhere',
					disabled : true,
					handler: function() {
						var gridWhereInbox = Ext.getCmp('WhereInbox_popup_grid');
						var rowSelected = gridWhereInbox.getSelectionModel().getSelected();
						var ID_WHERE = rowSelected.data.IWHERE_UID;
						var QUERY_WHERE = rowSelected.data.IWHERE_QUERY;
						var tableName = rowSelected.data.TABLE_NAME;
						if(tableName != 'PMT_INBOX_WHERE_USER')
						{
							edit_WhereInbox_popup(ID_WHERE,QUERY_WHERE,ID_INBOX,ROLE_CODE);
						}
					}		
				} , {
					text: lanRemWhere,
					cls : 'x-btn-text-icon',
					icon : '/images/delete-16x16.gif',
					disabled : true,
					id : 'removeWhere',
					handler: function(){
						var gridWhereInbox = Ext.getCmp('WhereInbox_popup_grid');
						var rowSelected = gridWhereInbox.getSelectionModel().getSelected();
						var ID_WHERE = rowSelected.data.IWHERE_UID;
						var tableName = rowSelected.data.TABLE_NAME;
						PMExt.confirm(_('ID_CONFIRM'),lanRemConfirm, function(){
						   	Ext.MessageBox.show({ msg: _('ID_PROCESSING'), wait:true,waitConfig: {interval:200} });
						   	Ext.Ajax.request({
							url: 'SaveWhereInbox.php?ID=' + ID_INBOX,
							params:{
					        		whereaction  : 'remove',
					        		whereIDField : ID_WHERE,
					        		whereTable   : tableName
					        },
							success: function(response) {						
								Ext.MessageBox.hide();					
								Ext.getCmp('WhereInbox_popup_grid').getStore().reload();						
							}				
							});     
						});					
					}
				} ,  {
					text: lanAddConfig,
					cls : 'x-btn-text-icon',
					icon : '/images/add.png',
					handler: function(){
						configUsers_where(ID_INBOX,idTable);
					}
				}
				],
				sm: new Ext.grid.RowSelectionModel({
				      selectSingle: false,
				      listeners:{
						rowselect: function(sm,index,record){
							//console.log(record.data);
							var tableName = record.data.TABLE_NAME;
							if(tableName != 'PMT_INBOX_WHERE_USER')
							{
								buttonEditWhere = Ext.getCmp('editWhere');
								buttonEditWhere.setDisabled(false);
								buttonRemoveWhere = Ext.getCmp('removeWhere');
								buttonRemoveWhere.setDisabled(false);
							}
							else
							{
								buttonEditWhere = Ext.getCmp('editWhere');
								buttonEditWhere.setDisabled(true);
								buttonRemoveWhere = Ext.getCmp('removeWhere');
								buttonRemoveWhere.setDisabled(false);
							}
				        }
				      }
				})
			});

			WhereInbox_popup_window = new Ext.Window({
			closeAction : 'hide',
		    autoDestroy : true,
		    maximizable: true,        
		    title: 'Where Inbox ',	          
		    width : 600,
		    height : 312,            
		    modal : true,
		    closable:true,
			constrain:true,
			autoScroll:true,
			items : [WhereInbox_popup_grid],
			layout: 'fit'
			});
			WhereInbox_popup_window.show();
			WhereInbox_popup_window.maximize();
	        WhereInbox_popup_window.toFront();        	
		

	}


	//////////////////////////////// End Where Action Inbox //////////////////////////////////////////////////
	

////////////////////////////////Load Action Inbox //////////////////////////////////////////////////
	 
    
	function Fn_LoadActionsInbox()
	{	
		var ID = Ext.getCmp('idInboxCombo').getValue();
	    var ID_INBOX = Ext.getCmp('idInboxCombo').getValue();
		var ActionInbox_popup_store = new Ext.data.JsonStore({
		        url : 'ajaxActionInboxPopup.php?actionInbox_id='+ID_INBOX+'&rolID='+rolID,
		        root : 'data',
		        totalProperty : 'total',
		        autoWidth : true,
		         fields : [ 'ID', 'NAME', 'DESCRIPTION','PM_FUNCTION','PARAMETERS_FUNCTION','PARAMETERS_FUNCTION_AUX','ID_ACTION','SENT_FUNCTION_PARAMETERS', 'BY_FIELDS']
		    });
		ActionInbox_popup_store.load();  
			
		var ActionInbox_popup_cm = new Ext.grid.ColumnModel([
			       {
			            header: "Name",
			            dataIndex: 'NAME'
					} , {
			            header: "Description",
			            dataIndex: 'DESCRIPTION'
					} , { 
						header: "PmFunction",
						dataIndex: 'PM_FUNCTION'
					} , {
						header: "Parameters",
						dataIndex: 'PARAMETERS_FUNCTION'
					} , {
						header: "By Fields",
						dataIndex: 'BY_FIELDS'
					} 
				]);
		ActionInbox_popup_cm.defaultSortable= true;	
			
		var ActionInbox_popup_grid = new Ext.grid.GridPanel({
					store			: ActionInbox_popup_store,
					cm				: ActionInbox_popup_cm,
					stripeRows		: true,
					autoScroll		: true,
					id			 	:'ActionInbox_popup_grid',
					ddGroup		   	:'gridDDactions',
					enableDragDrop 	: true, 
					viewConfig 		: {
			          forceFit 		: true,
			          scrollOffset 	: 0,
			          emptyText		: CustomColEmpty
			       },
					bbar			: new Ext.PagingToolbar({
				          pageSize: 50,
				          store: ActionInbox_popup_store,
				          displayInfo: true,
				          displayMsg: lanDisplaying,
				          emptyMsg: CustomColEmpty2
					}),
					tbar 			: [{
								text: ActionAdd,
								cls : 'x-btn-text-icon',
								icon : '/images/ext/default/tree/drop-add.gif',
								handler: function(){
									add_ActionInbox_popup(ID_INBOX);
								}
							}, {
								text	: ActionEdit,
								cls 	: 'x-btn-text-icon',
								icon 	: '/images/edit-table.png',
								id		: 'editActionsInbox',
								disabled: true,
								handler	: function() {
									edit_ActionInbox_popup(ID_INBOX);
								}		
							} , {
								text	: ActionRemove,
								cls 	: 'x-btn-text-icon',
								icon 	: '/images/delete-16x16.gif',
								id		: 'removeActionsInbox',
								disabled: true,
								handler	: function(){
											remove_ActionInbox_popup(ActionInbox_popup_grid);
								}
							} , {
								text: ActionSave,
								cls : 'x-btn-text-icon',
								icon : '/images/ok.png',
								tooltip  : 'Add drag and drop',
								handler: function(){
										saveActions_DragAndDrop(ActionInbox_popup_store);
								}
							} , {
								text	: ActionFields,
								cls 	: 'x-btn-text-icon',
								icon 	: '/images/checkedsmall.gif',
								id		: 'byFieldActionsInbox',
								disabled: true,
								handler	: function(){
										byFields_Action(ActionInbox_popup_grid, ID_INBOX);
								}
							}],
					listeners      : {  //drag and drop
						"render": {
					  		scope: this,
					  		fn: function(grid) {
								var ddrow = new Ext.dd.DropTarget(grid.container, {
									ddGroup : 'gridDDactions',
									copy:false,
									notifyDrop : function(dd, e, data){
										var ds = grid.store;
										var sm = ActionInbox_popup_grid.getSelectionModel();
					                    var rows = sm.getSelections();
					                    if(dd.getDragData(e)) {
					                    	var cindex=dd.getDragData(e).rowIndex;
					                        if(typeof(cindex) != "undefined") {
					                        	for(i = 0; i <  rows.length; i++) {
					                        		ds.remove(ds.getById(rows[i].id));
					                            }
					                            ds.insert(cindex,data.selections);
					                            sm.clearSelections();
					                         }
					                    }
									}
					         }) 
							//store.load();
					      }
					   }
					},
					sm: new Ext.grid.RowSelectionModel({
					      selectSingle: false,
					      listeners:{
					        selectionchange: function(sm){
								buttonEditAction = Ext.getCmp('editActionsInbox');
								buttonEditAction.setDisabled(false);
								buttonEditRemove = Ext.getCmp('removeActionsInbox');
								buttonEditRemove.setDisabled(false);
								buttonEditByField = Ext.getCmp('byFieldActionsInbox');
								buttonEditByField.setDisabled(false);
					        }
					      }
					})
		});	
			    
		ActionInbox_popup_window = new Ext.Window({ 
			closeAction : 'hide',
			autoDestroy : true,
			maximizable: true,        
			title: 'Action Inbox ',	           
			width : 800,
			height : 500,            
			modal : true,
			closable:true,
			constrain:true,
			autoScroll:true,
			items : ActionInbox_popup_grid,
			layout: 'fit'
		});				
		ActionInbox_popup_window.show();
		ActionInbox_popup_window.maximize();
		ActionInbox_popup_window.toFront();			
		
	}
	
	function add_ActionInbox_popup(ID_INBOX){
    	
		// add Grid parameters Action
		
		var gridParametersAction_store = new Ext.data.JsonStore({
	        url : 'FieldsInboxRoles_Ajax.php?actionInbox_id='+ID_INBOX+'&rolID='+rolID,
	        root : 'data',
	        totalProperty : 'total',
	        autoWidth : true,
	         fields : [ 'ID', 'FIELD_NAME', 'FUNCTIONS']
	    });
		gridParametersAction_store.load();  
	    var gridParametersAction_cm = new Ext.grid.ColumnModel([
	       {
	            header: "Name",
	            dataIndex: 'FIELD_NAME'
		   }  
		]);
	    gridParametersAction_cm.defaultSortable = true;	
		
	    var gridParametersAction = new Ext.grid.GridPanel({
			store: gridParametersAction_store,
			cm:gridParametersAction_cm,
			sm: new Ext.grid.RowSelectionModel({
			      selectSingle: false,
			      listeners:{
			        selectionchange: function(sm){
			          console.log('select');
			        }
			      }
			    }),
			stripeRows: true,
			autoScroll:true,
			width : 170,
			height : 130,
			id:'gridCenter',
			viewConfig : {
	          forceFit : true,
	          scrollOffset : 0,
	          emptyText: CustomColEmpty 

	       },
	       tbar : [{
					text: CustomColAddParam,
					cls : 'x-btn-text-icon',
					icon : '/images/ext/default/tree/drop-add.gif',
					handler: function(){
								add_ParametersAction();
					}	
	       }],
	       listeners: { rowdblclick: add_ParametersAction}
		});
		
	    var add_ActionInbox_popup_store = new Ext.data.JsonStore({
		        url : 'actionInbox_Ajax.php?action=listAction',
		        root : 'data',
		        totalProperty : 'total',
		        autoWidth : true,
		        fields : [
		        	{name: 'ID'},
					{name: 'NAME'},
					{name: 'DESCRIPTION'},
					{name: 'PM_FUNCTION'},
					{name: 'PARAMETERS_FUNCTION'}
				]
		    });
		  	add_ActionInbox_popup_store.load();  
		  	
			var add_ActionInbox_popup_field =  new Ext.form.ComboBox({
		        id : 'add_ActionInbox_popup_field',
		        fieldLabel: ActionNewInbox,
		        name: 'ACTION_ID',
		        maxLength: 45,
				allowBlank: false,
		        anchor : '98%',
		        mode: 'local',                    
		        triggerAction : 'all',
		        store: add_ActionInbox_popup_store,
		        valueField: 'ID',
		        hiddenName: 'ID',
		        displayField: 'NAME',
		        listeners  : {
	               	select: function(combo, record, index) {
			        	parameters = record.get('PARAMETERS_FUNCTION');
			        	if(parameters.length)
						{
							parameters = parameters.split(" ");
							Ext.getCmp('helpparametersfield').setValue(parameters);
							var elemParameters = Ext.getCmp('idParameters');
							Ext.getCmp('idParameters').checkbox.dom.checked = 1;
							elemParameters.expand();
						}
						else
						{
							Ext.getCmp('helpparametersfield').setValue('The function has no parameters');
							Ext.getCmp('idParameters').checkbox.dom.checked = 0;
							var elemParameters = Ext.getCmp('idParameters');
							elemParameters.collapse();
						}
			       }
	        	}
		        	
			});					
			
			    // grid.render('div_form');
			add_ActionInbox_popup_form = new Ext.FormPanel({
			id: 'add_ActionInbox_popup_form',								  
			labelAlign: 'top',
			bodyStyle:'padding:5px 5px 5px 10px',
			autoScroll:true,
			items: [add_ActionInbox_popup_field,
			        {
						id : 'idParameters',
						title : ActionParamSent,
						xtype : 'fieldset',
						checkboxToggle : true,
						autoHeight : true,
						defaults : {
							width : 380
						},
						defaultType : 'textfield',
						collapsed : false,
						items : [ {
							xtype : 'compositefield',
							hideLabel : true,
							layout : 'fit',
							labelWidth : 100,
							items : [  {                                                                                              
					            xtype: 'textfield',
					            id:'helpparametersfield', 
					        	fieldLabel: ActionParamSent, 
					        	name: 'helpparams', 
					        	width: 160,
					        	height: 50,
					        	allowBlank: false,
					        	disabled: true,
					            hidden: false,
					            bodyStyle:'padding:5px 5px 5px 10px'
					        } , gridParametersAction]
						} ]
			        } ,  {                                                                                              
			            xtype: 'textarea',
			            id:'parametersfield', 
			        	  fieldLabel: ActionParamSent, 
			        	  name: 'parameters', 
			        	  width: 300, 
			        	  allowBlank: true,
			        	  disabled: false,
			            hidden: false
			        }
				]							
			});		
			
			add_ActionInbox_popup_window = new Ext.Window({
			title: ActionAddInbox,
			id:'add_ActionInbox_popup_window',
			width: 380,
			autoHeight: true,
			autoScroll:true,
			closable:true,
			modal:true,
			constrain:true,
			plain:true,
			layout: 'form',
			items: [add_ActionInbox_popup_form],
			buttons: [{
	            text: Save,
	            type: 'submit',
	            scope: this,
	            handler: function() {   
				
				var idAction = Ext.getCmp('add_ActionInbox_popup_field').getValue();
				var nameAction = Ext.getCmp('add_ActionInbox_popup_field').getRawValue();
				var parameters = Ext.getCmp('parametersfield').getValue();
				var sentParameters = Ext.getCmp('idParameters').checkbox.dom.checked?1:0;
				
				Ext.getCmp('add_ActionInbox_popup_form').form.submit({
	                    method: 'POST',
	                    url: 'SaveActionInbox.php?method=add&ID=' + ID_INBOX,
	                    params : {
	                        idAction : idAction ,
	                        nameAction : nameAction ,
	                        parameters : parameters,
	                        rolID : rolID,
	                        idInbox : ID_INBOX,
	                        sentParameters : sentParameters
	                    },
	                    success: function(f, a) {                                                
	                        var data = Ext.decode(a.response.responseText);                        
	                        if(data.success == true){ 
	                          Ext.MessageBox.show({                            
	                            msg : MsgSave,
	                            buttons : Ext.MessageBox.OK,
	                            icon : Ext.MessageBox.INFO
	                          });
	                          Ext.getCmp('ActionInbox_popup_grid').getStore().reload();
	                          Ext.getCmp('add_ActionInbox_popup_window').close();
	                        }                        
	                    },            
	                    failure: function(f, a) { 
	                        f.markInvalid(a.result.errors);
	                    }            
	                })
	            }
	        },{
	            text: Cancel,            
	            handler: function (){                
	                Ext.getCmp('add_ActionInbox_popup_window').close();
	            }
	        }]
			});	
			
			add_ActionInbox_popup_window.show();
			add_ActionInbox_popup_window.toFront();
			
			var elemParameters = Ext.getCmp('idParameters');
			elemParameters.collapse();
	}
     
     // edit Actions repeat
     function edit_ActionInbox_popup(ID_INBOX){
		  	var edit_ActionInbox_popup_store = new Ext.data.JsonStore({
		        url : 'actionInbox_Ajax.php?action=listAction',
		        root : 'data',
		        totalProperty : 'total',
		        autoWidth : true,
		        fields : [
		        	{name: 'ID'},
					{name: 'NAME'},
					{name: 'DESCRIPTION'},
					{name: 'PM_FUNCTION'},
					{name: 'PARAMETERS_FUNCTION'}
				]
		    });
		  	edit_ActionInbox_popup_store.load();  
		  
				var edit_ActionInbox_popup_field =  new Ext.form.ComboBox({
		        id : 'comboEditAction',
		        fieldLabel: ActionNewInbox,
		        name: 'ACTION_ID',
		        maxLength: 45,
				allowBlank: false,
		        anchor : '98%',
		        mode: 'local',                    
		        triggerAction : 'all',
		        store: edit_ActionInbox_popup_store,
		        valueField: 'ID',
		        hiddenName: 'ID',
		        displayField: 'NAME',
		        listeners  : {
	               	select: function(combo, record, index) {
			        	parameters = record.get('PARAMETERS_FUNCTION');
						if(parameters.length)
						{
							parameters = parameters.split(" ");
							Ext.getCmp('helpId').setValue(parameters);
						}
						else
							Ext.getCmp('helpId').setValue('The function has no parameters');
			       }
	        	}
		        	
			});					
			
			// add Grid parameters Action
			
				var gridParametersAction_store = new Ext.data.JsonStore({
			        url : 'FieldsInboxRoles_Ajax.php?actionInbox_id='+ID_INBOX+'&rolID='+rolID,
			        root : 'data',
			        totalProperty : 'total',
			        autoWidth : true,
			         fields : [ 'ID', 'FIELD_NAME', 'FUNCTIONS']
			    });
				gridParametersAction_store.load();  
			    var gridParametersAction_cm = new Ext.grid.ColumnModel([
			       {
			            header: "Name",
			            dataIndex: 'FIELD_NAME'
				   }  
				]);
			    gridParametersAction_cm.defaultSortable = true;	
				
			    var gridParametersActionEdit = new Ext.grid.GridPanel({
					store: gridParametersAction_store,
					cm:gridParametersAction_cm,
					sm: new Ext.grid.RowSelectionModel({
					      selectSingle: false,
					      listeners:{
					        selectionchange: function(sm){
					          console.log('select');
					        }
					      }
					    }),
					stripeRows: true,
					autoScroll:true,
					width : 165,
					height : 120,
					region : 'left',
					id:'gridCenter',
					viewConfig : {
			          forceFit : true,
			          scrollOffset : 0,
			          emptyText: CustomColEmpty
			       },
			       tbar : [{
							text: CustomColAddParam,
							cls : 'x-btn-text-icon',
							icon : '/images/ext/default/tree/drop-add.gif',
							handler: function(){
										add_ParametersAction();
							}	
			       }],
			       listeners: { rowdblclick: add_ParametersAction}
				});
			edit_ActionInbox_popup_form = new Ext.FormPanel({
			id: 'popupActionEdit',								  
			labelAlign: 'top',
			bodyStyle:'padding:5px 5px 5px 10px',
			autoScroll:true,
			items: [edit_ActionInbox_popup_field,
					    {
						id : 'idParameters',
						title : ActionParamSent,
						xtype : 'fieldset',
						checkboxToggle : true,
						autoHeight : true,
						defaults : {
							width : 320
						},
						defaultType : 'textfield',
						collapsed : false,
						items : [ {
							xtype : 'compositefield',
							hideLabel : true,
							layout : 'fit',
							labelWidth : 100,
							items : [  {                                                                                              
					            xtype: 'textfield',
					            id:'helpId', 
					        	fieldLabel: "Parameters Sent of Function", 
					        	name: 'helpparams', 
					        	width: 150,
					        	height: 50,
					        	allowBlank: false,
					        	disabled: true,
					            hidden: false
					        } , gridParametersActionEdit]
						} ]
				} ,  {                                                                                              
			            xtype: 'textarea',
			            id:'parametersfield', 
			        	  fieldLabel: ActionParamSent, 
			        	  name: 'parameters', 
			        	  width: 250, 
			        	  allowBlank: true,
			        	  disabled: false,
			            hidden: false
			        }
				  ]							
			});		
			
			edit_ActionInbox_popup_window = new Ext.Window({
			title: ActionEditInbox,
			id:'edit_ActionInbox_popup_window',
			width: 380,
			autoHeight: true,
			autoScroll:true,
			closable:true,
			modal:true,
			constrain:true,
			plain:true,
			layout: 'form',
			items: [edit_ActionInbox_popup_form],
			buttons: [{
	            text: Save,
	            type: 'submit',
	            scope: this,
	            handler: function() {   
				
				var gridActionsInbox = Ext.getCmp('ActionInbox_popup_grid');
				var rowSelected = gridActionsInbox.getSelectionModel().getSelected();
				var ActionInbox_ID = rowSelected.data.ID;
				var idAction = rowSelected.data.ID_ACTION;
				var nameAction = Ext.getCmp('comboEditAction').getRawValue();
				var pmFunction = rowSelected.data.PM_FUNCTION;
				var parameters = Ext.getCmp('parametersfield').getValue();
				var sentParameters = Ext.getCmp('idParameters').checkbox.dom.checked?1:0;
				
				Ext.getCmp('popupActionEdit').form.submit({
                    method: 'POST',
                    url: 'SaveActionInbox.php?method=edit&ID=' + ID_INBOX,
                    params : {
                        idAction : idAction ,
                        nameAction : nameAction ,
                        parameters : parameters,
                        rolID : rolID,
                        actionInboxID : ActionInbox_ID,
                        sentParameters: sentParameters
                    },
                    success: function(f, a) {                                                
                        var data = Ext.decode(a.response.responseText);                        
                        if(data.success == true){ 
                          Ext.MessageBox.show({                            
                            msg : MsgSave,
                            buttons : Ext.MessageBox.OK,
                            icon : Ext.MessageBox.INFO
                          });
                          Ext.getCmp('ActionInbox_popup_grid').getStore().reload();
                          Ext.getCmp('edit_ActionInbox_popup_window').close();
                        }                        
                    },            
                    failure: function(f, a) { 
                        f.markInvalid(a.result.errors);
                    }            
                })
	            }
	        },{
	            text: Cancel,            
	            handler: function (){                
	                Ext.getCmp('edit_ActionInbox_popup_window').close();
	            }
	        }]
			});	
			
			edit_ActionInbox_popup_window.show();
			edit_ActionInbox_popup_window.toFront();
			var gridActionsInbox = Ext.getCmp('ActionInbox_popup_grid');
			var rowSelected = gridActionsInbox.getSelectionModel().getSelected();
			Ext.getCmp('comboEditAction').setValue(rowSelected.data.NAME);
			Ext.getCmp('helpId').setValue(rowSelected.data.PARAMETERS_FUNCTION_AUX);
			Ext.getCmp('parametersfield').setValue(rowSelected.data.PARAMETERS_FUNCTION);
			sentFunctionParameters = rowSelected.data.SENT_FUNCTION_PARAMETERS;
			var elemParameters = Ext.getCmp('idParameters');
			if(sentFunctionParameters == 0)
				elemParameters.collapse();
	}

     function add_ParametersAction()
     {
    	 var casesGrid_ = Ext.getCmp('gridCenter');
    	 var rowSelected = casesGrid_.getSelectionModel().getSelected();
    	 var textParameters = "";
    	 parameters = rowSelected.data.FIELD_NAME;
    	 textParameters = Ext.getCmp('parametersfield').getValue();
    	 if(textParameters.length)
    		 textParameters = textParameters+","+parameters;
    	 else
    		 textParameters = parameters;
		  		 
		 Ext.getCmp('parametersfield').setValue(textParameters);
	}

	function remove_ActionInbox_popup(ActionInbox_popup_grid)
	{
		if(ActionInbox_popup_grid.selModel.getCount() == 1) {
			var rowModel = ActionInbox_popup_grid.getSelectionModel().getSelected();
			
	  	    if (rowModel) {
	  	    	var sm = ActionInbox_popup_grid.getSelectionModel();
	            var sel = sm.getSelected();
	            if (sm.hasSelection()) {
	            	
	            	  Ext.Msg.show({
			                title : ActionRemInbox,
			                buttons : Ext.MessageBox.YESNOCANCEL,
			                msg : ActionRemInbox+' : ' + rowModel.data.NAME + ' ?',
			                fn : function(btn) {
			                  if (btn == 'yes') {
			                      var ID = rowModel.data.ID;
			          			
			                      Ext.Ajax.request({
			                    	  url : '../fieldcontrol/SaveActionInbox.php?method=remove',
									  params : {
			                    	  		ID : ID
			                      		},
			                      		success: function(f, a) {                                                
					                        var data = Ext.decode(f.response.responseText);
					                        var url = data.success; 
					                          if (url == true) {
					                            Ext.MessageBox.show({                            
						                            msg : MsgRemove,
						                            buttons : Ext.MessageBox.OK,
						                            icon : Ext.MessageBox.INFO
						                         });    
					                            Ext.getCmp('ActionInbox_popup_grid').getStore().reload();                   
					                          } else {
					                            Ext.MessageBox.alert("Error");
					                          }                       
					                    }, 
			                      		success : function(resp) {
					                          var data = Ext.decode(resp.responseText);
					                          var url = data.success; 
					                          if (url == true) {
					                            Ext.MessageBox.show({                            
						                            msg : MsgRemove,
						                            buttons : Ext.MessageBox.OK,
						                            icon : Ext.MessageBox.INFO
						                         });    
					                            Ext.getCmp('ActionInbox_popup_grid').getStore().reload();                   
					                          } else {
					                            Ext.MessageBox.alert("Error");
					                          }
					                        }
			                        
			                      });
			                  }
			                }
			              });
	            
	            } else {
	            	Ext.MessageBox.alert('Error');
	            }
		
	  	    }else {
	  	    	Ext.MessageBox.alert('Sorry...','You must select a Vendor to Remove.');
	  	    }
		}

	}
	
	function saveActions_DragAndDrop(ActionInbox_popup_store)
	{
			//'ID', 'NAME', 'DESCRIPTION','PM_FUNCTION','PARAMETERS_FUNCTION','PARAMETERS_FUNCTION_AUX','ID_ACTION','SENT_FUNCTION_PARAMETERS'
			var i  = 0;
			var arrayActionsInbox = new Array ();
			var myJSON  = '';
			
			ActionInbox_popup_store.each(function(record)  
			{  
				var idInbox      			= Ext.getCmp('idInboxCombo').getValue(); // 
				var name         			= record.get('NAME'); 
				var description 			= record.get('DESCRIPTION');
				var pmFunction 	 			= record.get('PM_FUNCTION');
				var parametersFunction 	 	= record.get('PARAMETERS_FUNCTION');
				var parametersFunctionAux 	= record.get('PARAMETERS_FUNCTION_AUX');
				var idAction			 	= record.get('ID_ACTION');
				var sentFunctionParameters 	= record.get('SENT_FUNCTION_PARAMETERS');
				var idRoles      = rolID;
				
				var item = {
					"value"         : i,
					"idRoles"       : rolID,
					"idInbox"	    : idInbox,
					"name"			: name,
					"description"	: description,
					"pmFunction"	: pmFunction,
					"parametersFunction" : parametersFunction,
					"parametersFunctionAux" : parametersFunctionAux,
					"idAction" 		: idAction,
					"sentFunctionParameters" : sentFunctionParameters
					
				};
				i++;
				arrayActionsInbox.push(item);
		    });
			
			if(arrayActionsInbox.length != 0){
				myJSON= Ext.util.JSON.encode(arrayActionsInbox);
				saveDataActionsInbox(myJSON);
			}
			else
			{
				alert(MsgSelectItem);
			}
		}
		
////////////////////////////////////Save Data Actions Inbox ////////////////////////////
		function saveDataActionsInbox(arrayActionsInbox)
		{  
			var ID = Ext.getCmp('idInboxCombo').getValue();
			var ID_INBOX = Ext.getCmp('idInboxCombo').getValue();
			
			
		      Ext.MessageBox.show({ msg: _('ID_PROCESSING'), wait:true,waitConfig: {interval:200} });
		      
		       Ext.Ajax.request({
		        url: '../fieldcontrol/SaveActionInbox.php?method=dragdrop&ID='+ID,
		        params: {
		        		arrayActionsInbox : arrayActionsInbox,
		          		rolID :  rolID,
		          		idInbox : ID_INBOX
		          		},
		        success: function(r,o){
		          		Ext.MessageBox.hide();
		          		//var data = Ext.decode(r.response.responseText);
	  		        //var url = data.success; 
	                //if (url == true) {
	                    Ext.MessageBox.show({                            
	                         msg : MsgOperation,
	                         buttons : Ext.MessageBox.OK,
	                         icon : Ext.MessageBox.INFO
	                }); 
		         },
		        failure: function(){
		        	Ext.MessageBox.alert('Error',MsgOpError);
		        	Ext.MessageBox.hide();
		        }
		      });
		       Ext.getCmp('ActionInbox_popup_grid').getStore().reload(); 
		       
		}
	//////////////////////////////// End Load Action Inbox //////////////////////////////////////////////////
	
	//////////////////////////// By Fields Action  //////////////////////////////////////////////
		function byFields_Action(ActionInbox_popup_grid,ID_INBOX )
		{
			var i  = 0;
			var arrayActionsInbox = new Array ();
			var myJSON  = '';
			
			var rowModel = ActionInbox_popup_grid.getSelectionModel().getSelected();
			
			if(rowModel){
				var nameAction = rowModel.data.NAME
				var idAction = rowModel.data.ID_ACTION;
				var pmFunction = rowModel.data.PM_FUNCTION;

				var checkColumnSelectByFields = new Ext.grid.CheckColumn({
					header		: 'Select?',
					dataIndex	: 'INCLUDE_SELECT',
					id			: 'checkByFields',
					flex		: 1,
					width		: 40,
					processEvent: function () { return false; }
				});
			    
				var parameters =  new Ext.form.TextField ({
					allowBlank 		: true,
					height     		: 50,
					disabled   		: false,
					selectOnFocus 	: true,
					anchor     		: '100%'
				});

				Ext.util.Format.comboRenderer = function(combo){
				    return function(value){
				        var record = combo.findRecord(combo.valueField, value);
				        return record ? record.get(combo.displayField) : combo.valueNotFoundText;
				    }
				} 
				
				var operatorComboStore = new Ext.data.SimpleStore({
			        fields: ['ID', 'NAME'],
			        data: [['==', '='], ['!=',' != '], 
			            ['<',' < '], ['>',' > '], ['IN',' IN ']],
			        autoLoad: true 
			    });
				
				var operatorCombo = new Ext.form.ComboBox({
					valueField    : 'ID',
					displayField  : 'NAME',
					id            : 'idOperatorCombo',
					fieldLabel    : 'Operator',
					typeAhead     : true,
					triggerAction : 'all',
					editable      : false,
					mode          : 'local',
					width         : 200,
					allowBlank    : false,
		            msgTarget	  : 'side',
					store         : operatorComboStore,
					name		  : 'idOperatorCombo',
					hiddenName	  : 'idOperatorCombo', 
					disabled      : false,
					selectOnFocus : true,
					forceSelection: true
					
				});

				var byFields_Action_store = new Ext.data.JsonStore({
				        url : 'ajaxActionInboxPopup.php?actionInbox_id='+ID_INBOX+'&rolID='+rolID + '&idAction=' + idAction,
				        root : 'data',
				        totalProperty : 'total',
				        autoWidth : true,
				        fields : [ 'ID', 'NAME_FIELD', 'DESCRIPTION','PARAMETERS_BY_FIELD','ID_INBOX', 'NAME_ACTION', 'ID_ACTION', 'ID_TABLE','OPERATOR',
				                    {name: 'INCLUDE_SELECT', type: 'bool', 
									convert   : function(v){
										return (v === "A" || v === true) ? true : false;
	        						}
				                 }]
					});
				byFields_Action_store.load();  
					
				var byFields_Action_cm = new Ext.grid.ColumnModel([
				    {
				    	header		: "Name",
				    	dataIndex	: 'NAME_FIELD',
				    	width		: 50
				    } , {
				    	header		: "Description",
				    	dataIndex	: 'DESCRIPTION',
				    	width		: 50
				    } ,checkColumnSelectByFields , 
				    {
				    	header		: "Operator",
				    	dataIndex	: 'OPERATOR',
				    	editor		: operatorCombo,
				    	renderer    : Ext.util.Format.comboRenderer(operatorCombo),
				    	width		: 20
				    },  {
				    	header		: "Parameters",
				    	dataIndex	: 'PARAMETERS_BY_FIELD',
				    	editor		: parameters,
				    	width		: 80
				    },
				    {
				    	header	  	: 'Clean',
				    	xtype     	: 'actioncolumn', 
			    		width     	: 20,
			    		hidden	  	: false,
			    		items     	: [{
			    			iconCls :'button_menu_ext ss_cleardata',
			    			//icon    : '/plugin/fieldcontrol/clear.png', 
			    			tooltip : 'Clean',
			    			handler : function(grid, rowIndex, colIndex) {
								var rec = grid.getStore().getAt(rowIndex);
								rec.set('PARAMETERS_BY_FIELD', '');
								rec.set('OPERATOR', '');
								rec.set('INCLUDE_SELECT', false);
			            	} 
			    		}]
			    	}
				]);
				byFields_Action_cm.defaultSortable= true;	
				
				
				   
				var byFields_Action_grid = new Ext.grid.EditorGridPanel({
					store			: byFields_Action_store,
					cm				: byFields_Action_cm,
					stripeRows		: true,
					autoScroll		: true,
					id			 	: 'byFields_Action_grid',
					viewConfig 		: {
						forceFit 		: true,
						scrollOffset 	: 0,
						emptyText		: CustomColEmpty
					},
					bbar			: new Ext.PagingToolbar({
						pageSize	: 50,
						store		: byFields_Action_store,
						displayInfo	: true,
						displayMsg	: lanDisplaying,
						emptyMsg	: CustomColEmpty2
					}),
					tbar 			: [{
						text		: lanSaveConditions,
						cls 		: 'x-btn-text-icon',
						icon 		: '/images/ok.png',
						tooltip  	: 'Add drag and drop',
						handler		: function(){
							saveByFields(byFields_Action_store, idAction);
						}
					}],
					plugins         : [checkColumnSelectByFields]
				});	
				var byFields = new Ext.Window({
					title			: 'Action:  <b style="color: green">' + nameAction + '</b>',	
			        closeAction 	: 'hide',
				    autoDestroy 	: true,
				    maximizable 	: true,     
			        modal       	: true,
			        id          	: 'popupByFields',
			        width 	    	: 800,
				    height 	    	: 500,   
				    closable    	: true,
					constrain   	: true,
					autoScroll  	: true,
			        items       	: byFields_Action_grid,
			        layout      	: 'fit'
				   /* buttons:[{
						iconCls:'boton-guardar',
						text:'Close Panel',
						handler: function(){
				    		byFields.destroy();
						}
					}]*/
				});
				
				byFields.show();	
				byFields.toFront();	
				
			}
			
				
		}
		
		function saveByFields(byFields_Action_store, idAction)
		{
				var i  = 0;
				var arraybyFieldsAction = new Array ();
				var myJSON  = '';
				var swByFields = 0;
				byFields_Action_store.each(function(record)  
				{  
					//console.log(record);
					if(record.get('INCLUDE_SELECT') == true)
					{
						if(record.get('OPERATOR') != '')
						{
							if(record.get('PARAMETERS_BY_FIELD') != '')
							{
								var idInbox      	= record.get('ID_INBOX'); 
								var nameField       = record.get('NAME_FIELD'); 
								var description 	= record.get('DESCRIPTION');
								var idfield 	 	= record.get('ID');
								var parametersField	= record.get('PARAMETERS_BY_FIELD');
								var nameAction 		= record.get('NAME_ACTION');
								var idAction 	 	= record.get('ID_ACTION');
								var idTable			= record.get('ID_TABLE');
								var idRoles      	= rolID;
								var operator      	= record.get('OPERATOR');
					
								var item = {
										"value"         : i,
										"idRoles"       : rolID,
										"idInbox"	    : idInbox,
										"nameField"		: nameField,
										"idfield"		: idfield,
										"nameAction"	: nameAction,
										"idAction"		: idAction,
										"idTable"		: idTable,
										"parameterField": parametersField,
										"operator"		: operator
						
								};
								i++;
								arraybyFieldsAction.push(item);
							}
							else
							{
								alert("Register parameters");
								swByFields = 1;
							}
						}
						else
						{
							alert("Select Operator");
							swByFields = 1;
						}
					}
			    });
				
				if(arraybyFieldsAction.length != 0){
					myJSON= Ext.util.JSON.encode(arraybyFieldsAction);
					saveDataFieldsActions(myJSON, idAction);
				}
				else
				{
					myJSON = '';
					if(swByFields == 0)
						saveDataFieldsActions(myJSON, idAction);
				}
				
		}
		
		function saveDataFieldsActions(arrayFieldsAction, idAction)
		{  
			
			var ID_INBOX = Ext.getCmp('idInboxCombo').getValue();
			
			
		      Ext.MessageBox.show({ msg: _('ID_PROCESSING'), wait:true,waitConfig: {interval:200} });
		      
		       Ext.Ajax.request({
		        url: '../fieldcontrol/SaveActionInbox.php?method=fieldAction&ID='+idAction,
		        params: {
		        		arrayFieldsAction : arrayFieldsAction,
		          		rolID 		:  rolID,
		          		idInbox 	: ID_INBOX,
		          		idAction 	: idAction
		          		},
		        success: function(r,o){
		          		Ext.MessageBox.hide();
		          		//var data = Ext.decode(r.response.responseText);
	  		        //var url = data.success; 
	                //if (url == true) {
	                    Ext.MessageBox.show({                            
	                         msg : 'The operation completed sucessfully!',
	                         buttons : Ext.MessageBox.OK,
	                         icon : Ext.MessageBox.INFO
	                }); 
	                    Ext.getCmp('popupByFields').hide();
		         },
		        failure: function(){
		        	Ext.MessageBox.alert('Error','The operation was not completed sucessfully!');
		        	Ext.MessageBox.hide();
		        }
		      });
		       Ext.getCmp('ActionInbox_popup_grid').getStore().reload(); 
		       
		      
		       
		}
	////////////////////////////By Fields Action  //////////////////////////////////////////////
		
		

	/////////////////////////////Concat Fields //////////////////////////////////////////////////
		 
	    
		function Fn_ConcatFields()
		{	
			var ID = Ext.getCmp('idInboxCombo').getValue();
			var ID_INBOX = Ext.getCmp('idInboxCombo').getValue();
			var	idTable = Ext.getCmp('idTableCombo').getValue();
			var ConcatFields_store = new Ext.data.JsonStore({
			
			        url : 'ajaxConcatFields.php?idInboxData='+ID_INBOX+'&rolID=' + rolID + '&idTable=' + idTable,
			        root : 'data',
			        totalProperty : 'total',
			        autoWidth : true,
			        fields        : ['ID','ROL_CODE','ID_INBOX', 'QUERY_SELECT', 'FIELD_NAME', 'FIELDS' ]
			    });
				ConcatFields_store.load();  
				
				  var ConcatFields_cm = new Ext.grid.ColumnModel({
						defaults : {
				    		width : 20,
				    		sortable : true
				    	},
				    	columns : [
				    	{
				    		header    : "#",
				    		width     : 5,
				    		sortable  : true,
				    		hidden    : true,
				    		dataIndex : 'ID'
				    	}, {
				    		header    : "Query Select",
				    		width     : 15,
				    		sortable  : true,
				    		dataIndex : 'QUERY_SELECT'
				    	}, {
				    		header    : "New Field",
				    		width     : 15,
				    		sortable  : true,
				    		dataIndex : 'FIELD_NAME'
				    	}]
				    
				    });
				    
				
				    ConcatFields_cm.defaultSortable= true;	
				
				    var ConcatFields_grd = new Ext.grid.GridPanel({
						store			: ConcatFields_store,
						cm				: ConcatFields_cm,
						autoWidth 		: true,
						stripeRows		: true,
						autoScroll		: true,
						id			 	:'ConcatFields_grd',
						ddGroup		   	:'gridDDactions',
						enableDragDrop 	: true,
						viewConfig 		: {
				          forceFit 		: true,
				          scrollOffset 	: 0,
				          emptyText		: CustomColEmpty
				       },
						bbar			: new Ext.PagingToolbar({
					          pageSize		: 50,
					          store			: ConcatFields_store,
					          displayInfo	: true,
					          displayMsg	: lanDisplaying,
					          emptyMsg		: CustomColEmpty2
						}),
						tbar 			: [{
							text	: CustomColumnsNew,
							cls 	: 'x-btn-text-icon',
							icon 	: '/images/ok.png',
							tooltip  : 'Add drag and drop',
							handler	: function(){
								newSelectQuery(ConcatFields_store);
							}
						},
						{
							text	: CustomColumnsEdit,
							cls 	: 'x-btn-text-icon',
							icon 	: '/images/edit-table.png',
							id		: 'editSelectQuery',
							disabled: true,
							handler	: function() {
								editSelectQuery(ConcatFields_grd);
					        }		
						} , {
							text	: CustomColumnsRemove,
							cls 	: 'x-btn-text-icon',
							icon 	: '/images/delete-16x16.gif',
							disabled: true,
							id		: 'removeSelectQuery',
							handler	: function(){
								removeSelectQuery(ConcatFields_grd);
							}
						}],
						plugins     : [checkColumnConcat],
						sm			: new Ext.grid.RowSelectionModel({
						      selectSingle: false,
						      listeners:{
						        selectionchange: function(sm){
									buttonEditQuery = Ext.getCmp('editSelectQuery');
									buttonEditQuery.setDisabled(false);
									buttonRemoveQuery = Ext.getCmp('removeSelectQuery');
									buttonRemoveQuery.setDisabled(false);
						        }
						      }
						})
				});	
				    
				    ConcatFields_popup_window = new Ext.Window({
					   	closeAction : 'hide',
			            autoDestroy : true,
			            maximizable	: true,        
			            title		: CustomColumnsTitle,	          
			            width 		: 800,
			            height 		: 500,            
			            modal 		: true,
			            closable	:true,
						constrain	:true,
						autoScroll	:true,
						items 		: ConcatFields_grd,
						layout		: 'fit'
					});				
		         	ConcatFields_popup_window.show();
		         	ConcatFields_popup_window.maximize();
		         	ConcatFields_popup_window.toFront();		
		}
		
		function newSelectQuery()
		{
			var ID = Ext.getCmp('idInboxCombo').getValue();
			var ID_INBOX = Ext.getCmp('idInboxCombo').getValue();
			var	idTable = Ext.getCmp('idTableCombo').getValue();
			var innerJoin = Ext.getCmp('idQuery').getValue();
			var gridSelectQuey_store = new Ext.data.JsonStore({
		        url : 'ajaxListFieldInbox.php?idInboxData='+ID_INBOX+'&rolID='+rolID + '&idTable=' + idTable ,
		        root : 'data',
		        totalProperty : 'total',
		        autoWidth : true,
		         fields : [ 'ID', 'FIELD_NAME', 'ALIAS_TABLE', 'FLD_UID', 'ID_INBOX', 'ADD_TAB_NAME', 'ROL_CODE', 'FIELDS']
		    });
			gridSelectQuey_store.load();  
		    var gridParametersSelect_cm = new Ext.grid.ColumnModel([
		       {
		            header: "Name",
		            dataIndex: 'FIELD_NAME'
			   }  
			]);
		    gridParametersSelect_cm.defaultSortable = true;	
			
		    var gridParametersSelect = new Ext.grid.GridPanel({
				store: gridSelectQuey_store,
				cm:gridParametersSelect_cm,
				sm: new Ext.grid.RowSelectionModel({
				      selectSingle: false,
				      listeners:{
				        selectionchange: function(sm){
				          //console.log('select');
				        }
				      }
				    }),
				stripeRows: true,
				autoScroll:true,
				width : 300,
				height : 130,
				id:'gridCenter',
				viewConfig : {
		          forceFit : true,
		          scrollOffset : 0,
		          emptyText: CustomColEmpty
		       },
		       tbar : [{
						text: CustomColAddParam,
						cls : 'x-btn-text-icon',
						icon : '/images/ext/default/tree/drop-add.gif',
						handler: function(){
									add_ParametersQuery(gridSelectQueyFields_store);
						}	
		       }],
		       listeners: { rowdblclick: function(grid, rowindex, e){ 
		    	   textParameters = Ext.getCmp('parametersfield').getValue();
		    	   if(textParameters.length == 0)
						 Ext.getCmp('parametersfieldAux').setValue(''); 
		    	   //console.log( Ext.getCmp('parametersfieldAux').getValue());
		    	   add_ParametersQuery(gridSelectQueyFields_store);}
		       }
			});
			
		    var checkColumnHiddenSelect = new Ext.grid.CheckColumn({
		    	header: 'Hidden?',
		    	dataIndex: 'HIDDEN_FIELD',
		    	id: 'checkHiddenSelect',
		    	flex: 1,
		    	width: 8,
		    	processEvent: function () { return false; }
		  	}); 
		    
		    var gridSelectQueyFields_store = new Ext.data.JsonStore({
		        url : 'ajaxListFieldInbox.php?idInboxData='+ID_INBOX+'&rolID='+rolID + '&idTable=' + idTable ,
		        root : 'data',
		        totalProperty : 'total',
		        autoWidth : true,
		         fields : [ 'ID', 'FIELD_NAME', 'ALIAS_TABLE', 'FLD_UID', 'ID_INBOX', 'ADD_TAB_NAME', 'ROL_CODE', 'FIELDS']
		    }); 
		    var gridParametersSelectFields_cm = new Ext.grid.ColumnModel([
		        {
		        	header: "Name",
		        	dataIndex: 'FIELD_NAME',
		        	width: 15
		        }, checkColumnHiddenSelect
		    ]);
		    
		    var gridParametersSelectFields = new Ext.grid.GridPanel({
				store		: gridSelectQueyFields_store,
				cm			: gridParametersSelectFields_cm,
				sm			: new Ext.grid.RowSelectionModel({
				      selectSingle: false,
				      listeners:{
				        selectionchange: function(sm){
				          console.log('select');
				        }
				      }
				    }),
				stripeRows	: true,
				autoScroll	:true,
				width 		: 300,
				height 		: 130,
				id			:'gridCenterSelect',
				plugins     : checkColumnHiddenSelect,
				viewConfig 	: {
		          forceFit 		: true,
		          scrollOffset 	: 0,
		          emptyText		: CustomColEmpty
		       	},
				tbar 		: [{
						text: 'Delete Parameter',
						cls : 'x-btn-text-icon',
						icon : '/images/delete-16x16.gif',
						handler: function(){
									add_ParametersQuery(gridSelectQueyFields_store);
						}	
				}],
				listeners: { rowdblclick: function(grid, rowindex, e){ 
		       		textParameters = Ext.getCmp('parametersfield').getValue();
		       		if(textParameters.length == 0)
						 Ext.getCmp('parametersfieldAux').setValue(''); 
			    	add_ParametersQuery(gridSelectQueyFields_store);}
			    }
			});
		 		
		    add_SelectQuery_popup_form = new Ext.FormPanel({
				id: 'add_SelectQuery_popup_form',								  
				labelAlign: 'top',
				bodyStyle:'padding:5px 5px 5px 10px',
				autoScroll:true,
				items: [
					{
							id : 'idParameters',
							title : CustomColParameters,
							xtype : 'fieldset',
							//checkboxToggle : true,
							autoHeight : true,
							defaults : {
								width : 350
							},
							defaultType : 'textfield',
							collapsed : false,
							items : [ gridParametersSelect ]
										           
					} ,  {                                                                                              
				            xtype: 'textarea',
				            id:'parametersfield', 
				            fieldLabel: CustomColParameters, 
				            name: 'parameters', 
				            width: 350, 
				            allowBlank: true,
				            disabled: false,
				            hidden: false
				   } , {
						id : 'idParametersSelect',
						title : CustomColParameters,
						xtype : 'fieldset',
						//checkboxToggle : true,
						autoHeight : true,
						hidden: true,
						defaults : {
							width : 300
						},
						defaultType : 'textfield',
						collapsed : false,
						items : [ gridParametersSelectFields ]
									           
				} , {                                                                                              
			            	xtype: 'textarea',
			            	id:'parametersfieldAux', 
			            	fieldLabel: CustomColParameters, 
			            	name: 'parametersAux', 
			            	width: 600, 
			            	allowBlank: true,
			            	disabled: false,
			            	hidden: true
			       }
				]							
			});		
			
		    add_SelectQuery_popup_window = new Ext.Window({
				title: CustomColAdd,
				id:'add_SelectQuery_popup_window',
				width: 400,
				autoHeight: true,
				autoScroll:true,
				closable:true,
				modal:true,
				constrain:true,
				plain:true,
				layout: 'form',
				items: [add_SelectQuery_popup_form],
				buttons: [{
		            text: Save,
		            type: 'submit',
		            scope: this,
		            handler: function() {   
					
						var parameters = Ext.getCmp('parametersfield').getValue();
						var parameterAux = Ext.getCmp('parametersfieldAux').getValue();
						if(parameters != '')
						{	
							var dataHidden = editDataFields(gridSelectQueyFields_store);
							
							Ext.getCmp('add_SelectQuery_popup_form').form.submit({
								method: 'POST',
								url: 'SaveQueryInbox.php?method=add&ID=' + ID_INBOX,
								params : {
		                        	parameters 	: parameters,
		                        	fields		: parameterAux,
		                        	rolID 		: rolID,
		                        	idInbox 	: ID_INBOX
		                    	},
		                    	success: function(f, a) {                                                
		                    		var data = Ext.decode(a.response.responseText);                        
		                    		if(data.success == true){ 
		                    			Ext.MessageBox.show({                            
		                    				msg : 'The data was saved sucessfully!',
		                    				buttons : Ext.MessageBox.OK,
		                    				icon : Ext.MessageBox.INFO
		                    			});
		                    			Ext.getCmp('add_SelectQuery_popup_window').close();
		                    			Ext.getCmp('ConcatFields_grd').getStore().reload();
		                          
		                    		}                        
		                    	},            
		                    	failure: function(f, a) { 
		                    		f.markInvalid(a.result.errors);
		                    	}            
							})
						}
						else
							alert("Register parameters select");
		            }
		        },{
		            text: Cancel,            
		            handler: function (){                
		                Ext.getCmp('add_SelectQuery_popup_window').close();
		            }
		        }]
				});	
				
				add_SelectQuery_popup_window.show();
				add_SelectQuery_popup_window.toFront();
		}
		
		function editSelectQuery(ConcatFields_grd)
		{
			var ID = Ext.getCmp('idInboxCombo').getValue();
			var ID_INBOX = Ext.getCmp('idInboxCombo').getValue();
			var	idTable = Ext.getCmp('idTableCombo').getValue();
			var gridSelectQuey_store = new Ext.data.JsonStore({
		        url : 'ajaxListFieldInbox.php?idInboxData='+ID_INBOX+'&rolID='+rolID + '&idTable=' + idTable ,
		        root : 'data',
		        totalProperty : 'total',
		        autoWidth : true,
		        fields : [ 'ID', 'FIELD_NAME', 'ALIAS_TABLE', 'FLD_UID', 'ID_INBOX', 'ADD_TAB_NAME', 'ROL_CODE', 'FIELDS']
		    });
			gridSelectQuey_store.load(); 
			
			var gridParametersSelect_cm = new Ext.grid.ColumnModel([
		       {
		            header: "Name",
		            dataIndex: 'FIELD_NAME'
			   }  
			]);
		    gridParametersSelect_cm.defaultSortable = true;	
			
		    var gridParametersSelect = new Ext.grid.GridPanel({
				store: gridSelectQuey_store,
				cm:gridParametersSelect_cm,
				sm: new Ext.grid.RowSelectionModel({
				      selectSingle: false,
				      listeners:{
				        selectionchange: function(sm){
				          console.log('select');
				        }
				      }
				    }),
				stripeRows: true,
				autoScroll:true,
				width : 300,
				height : 130,
				id:'gridCenter',
				viewConfig : {
		          forceFit : true,
		          scrollOffset : 0,
		          emptyText: CustomColEmpty
		       },
		       tbar : [{
						text: CustomColAddParam,
						cls : 'x-btn-text-icon',
						icon : '/images/ext/default/tree/drop-add.gif',
						handler: function(){
							add_ParametersQuery(gridSelectQuey_store);
						}	
		       }],
		       listeners: { rowdblclick: function(grid, rowindex, e){ 
		    	   textParameters = Ext.getCmp('parametersfield').getValue();
		    	   if(textParameters.length == 0)
						 Ext.getCmp('parametersfieldAux').setValue(''); 
		    	   add_ParametersQuery(gridSelectQuey_store);
		    	   }
		       }
			});
			
		    edit_SelectQuery_popup_form = new Ext.FormPanel({
				id: 'edit_SelectQuery_popup_form',								  
				labelAlign: 'top',
				bodyStyle:'padding:5px 5px 5px 10px',
				autoScroll:true,
				items: [
						    {
							id : 'idParameters',
							title : CustomColParameters,
							xtype : 'fieldset',
							//checkboxToggle : true,
							autoHeight : true,
							defaults : {
								width : 300
							},
							defaultType : 'textfield',
							collapsed : false,
							items : [ gridParametersSelect ]
					} ,  {                                                                                              
				            xtype: 'textarea',
				            id:'parametersfield', 
				            fieldLabel: CustomColParameters,
				            name: 'parameters', 
				            width: 350, 
				            allowBlank: true,
				            disabled: false,
				            hidden: false
				   } ,  {                                                                                              
			            	xtype: 'textarea',
			            	id:'parametersfieldAux', 
			            	fieldLabel: CustomColParameters, 
			            	name: 'parametersAux', 
			            	width: 350, 
			            	allowBlank: true,
			            	disabled: false,
			            	hidden: true
			        }
				]							
			});		
			
		    edit_SelectQuery_popup_window = new Ext.Window({
				title: CustomColumnsEdit,
				id:'edit_SelectQuery_popup_window',
				width: 400,
				autoHeight: true,
				autoScroll:true,
				closable:true,
				modal:true,
				constrain:true,
				plain:true,
				layout: 'form',
				items: [edit_SelectQuery_popup_form],
				buttons: [{
		            text: Save,
		            type: 'submit',
		            scope: this,
		            handler: function() {   
					
						var parameters = Ext.getCmp('parametersfield').getValue();
						var parameterAux = Ext.getCmp('parametersfieldAux').getValue();
						
						Ext.getCmp('edit_SelectQuery_popup_form').form.submit({
		                    method: 'POST',
		                    url: 'SaveQueryInbox.php?method=edit&ID=' + rowSelected.data.ID,
		                    params : {
		                        parameters 	: parameters,
		                        fields		: parameterAux,
		                        rolID 		: rolID,
		                        idInbox 	: ID_INBOX
		                    },
		                    success: function(f, a) {                                                
		                        var data = Ext.decode(a.response.responseText);                        
		                        if(data.success == true){ 
		                          Ext.MessageBox.show({                            
		                            msg : CustomColEmpty,
		                            buttons : Ext.MessageBox.OK,
		                            icon : Ext.MessageBox.INFO
		                          });
		                          Ext.getCmp('edit_SelectQuery_popup_window').close();
		                          Ext.getCmp('ConcatFields_grd').getStore().reload();
		                          
		                        }                        
		                    },            
		                    failure: function(f, a) { 
		                        f.markInvalid(a.result.errors);
		                    }            
		                })
		            }
		        },{
		            text: Cancel,            
		            handler: function (){                
		                Ext.getCmp('edit_SelectQuery_popup_window').close();
		            }
		        }]
				});	
				
				edit_SelectQuery_popup_window.show();
				edit_SelectQuery_popup_window.toFront();
				var rowSelected = ConcatFields_grd.getSelectionModel().getSelected();
				Ext.getCmp('parametersfield').setValue(rowSelected.data.QUERY_SELECT);
				Ext.getCmp('parametersfieldAux').setValue(rowSelected.data.FIELDS);
		}
		
		function removeSelectQuery(ConcatFields_grd)
		{
			if(ConcatFields_grd.selModel.getCount() == 1) {
				var rowModel = ConcatFields_grd.getSelectionModel().getSelected();
				
		  	    if (rowModel) {
		  	    	var sm = ConcatFields_grd.getSelectionModel();
		            var sel = sm.getSelected();
		            if (sm.hasSelection()) {
		            	
		            	  Ext.Msg.show({
				                title : lanActionRemTitle,
				                buttons : Ext.MessageBox.YESNOCANCEL,
				                msg : lanActionRemTitle+' : ' + rowModel.data.QUERY_SELECT + ' ?',
				                fn : function(btn) {
				                  if (btn == 'yes') {
				                      var ID = rowModel.data.ID;
				          			
				                      Ext.Ajax.request({
				                    	  url : '../fieldcontrol/SaveQueryInbox.php?method=remove',
										  params : {
				                    	  		ID : ID
				                      		},
				                      		success: function(f, a) {                                                
						                        var data = Ext.decode(f.response.responseText);
						                        var url = data.success; 
						                          if (url == true) {
						                            Ext.MessageBox.show({                            
							                            msg : MsgRemove,
							                            buttons : Ext.MessageBox.OK,
							                            icon : Ext.MessageBox.INFO
							                         });    
						                            Ext.getCmp('ConcatFields_grd').getStore().reload();                   
						                          } else {
						                            Ext.MessageBox.alert("Error");
						                          }                       
						                    }, 
				                      		success : function(resp) {
						                          var data = Ext.decode(resp.responseText);
						                          var url = data.success; 
						                          if (url == true) {
						                            Ext.MessageBox.show({                            
							                            msg : MsgRemove,
							                            buttons : Ext.MessageBox.OK,
							                            icon : Ext.MessageBox.INFO
							                         });    
						                            Ext.getCmp('ConcatFields_grd').getStore().reload();                   
						                          } else {
						                            Ext.MessageBox.alert("Error");
						                          }
						                        }
				                        
				                      });
				                  }
				                }
				              });
		            
		            } else {
		            	Ext.MessageBox.alert('Error');
		            }
			
		  	    }else {
		  	    	Ext.MessageBox.alert('Sorry...','You must select a Vendor to Remove.');
		  	    }
			}
		}
		
		function add_ParametersQuery(gridSelectQueyFields_store)
		{
			 var casesGrid_ = Ext.getCmp('gridCenter');
	    	 var rowSelected = casesGrid_.getSelectionModel().getSelected();
	    	 var textParameters = "";
	    	 var aliasTable = rowSelected.data.ALIAS_TABLE;
	    	 var idTable = rowSelected.data.ID_TABLE;
	    	 var idFLD = rowSelected.data.FLD_UID;
	    	 parameters = aliasTable+"."+rowSelected.data.FIELD_NAME;
	    	 textParameters = Ext.getCmp('parametersfield').getValue();
	    	 if(textParameters.length)
	    		 textParameters = textParameters+" "+parameters;
	    	 else
	    		 textParameters = parameters;
			  		 
			 Ext.getCmp('parametersfield').setValue(textParameters);	
			
			 var textParameters = "";
			 parameters = rowSelected.data.FIELD_NAME;
			 textParametersAux = Ext.getCmp('parametersfieldAux').getValue();
			 if(textParametersAux.length)
				 textParametersAux = textParametersAux+","+parameters;
			 else
				 textParametersAux = parameters;
			 
			 Ext.getCmp('parametersfieldAux').setValue(textParametersAux);
			 //console.log( Ext.getCmp('parametersfieldAux').getValue());
			 var casesGridField_ = Ext.getCmp('gridCenterSelect');
			 gridSelectQueyFields_store.add(new dateRow({FIELD_NAME: rowSelected.data.FIELD_NAME, FLD_UID: rowSelected.data.FLD_UID, ADD_TAB_NAME: rowSelected.data.ADD_TAB_NAME }));
			 
		}
		
		function editDataFields(gridSelectQueyFields_store)
		{
			var idField = '';
			var swD     = 0;
			var i       = 0;
			var miArray = new Array ();
			var myJSON  = '';
			var casesGrid_ = Ext.getCmp('gridCenter');
	    	var rowSelected = casesGrid_.getSelectionModel().getSelected();
	    	var textParameters = "";
	    	var ID_INBOX = Ext.getCmp('idInboxCombo').getValue();
			gridSelectQueyFields_store.each(function(record)  
			{  
				record.fields.each(function(field) 
				{ 
					var fieldValue = record.get(field.name);  
					
					if(idField != record.get('FLD_UID'))
					{
						idField = record.get('FLD_UID');
						
						if(record.get('HIDDEN_FIELD') == true)
						{	
							var hiddenField  = 1;
							var filterField  = 0;
							var idTable      = record.get('ADD_TAB_NAME');
							var idInbox 	 = Ext.getCmp('idInboxCombo').getValue();
							
							swD ++;
							var j = 0;
							var item = {
								"value"        : i,
								"idTable"      : idTable,
								"idInbox"      : idInbox,
								"idRoles"      : rolID,
								"hiddenField"  : hiddenField,
								"idField"	   : idField
		    				};
							i++;
							miArray.push(item);
		    			}
		    		}
		    			
		    	}); 
		    });
			var idInbox 	 = Ext.getCmp('idInboxCombo').getValue();	
			
			if(miArray.length != 0){
				myJSON= Ext.util.JSON.encode(miArray);
			}
			
			return(myJSON);
		}
		
		
		////////////////////////////Config users where //////////////////////////////////////////////
		function configUsers_where(ID_INBOX, idTable)
		{
			var i  = 0;
			var arrayActionsInbox = new Array ();
			var myJSON  = '';
			
			
				var checkColumnSelectConfigUser = new Ext.grid.CheckColumn({
					header		: 'Select?',
					dataIndex	: 'INCLUDE_SELECT',
					id			: 'checkConfigUser',
					flex		: 1,
					width		: 40,
					processEvent: function () { return false; }
				});
			    
				var parameters =  new Ext.form.TextField ({
					allowBlank 		: true,
					height     		: 50,
					disabled   		: false,
					selectOnFocus 	: true,
					anchor     		: '100%'
				});

				var operatorComboStore = new Ext.data.SimpleStore({
			        fields: ['ID', 'NAME'],
			        data: [['=', '='], ['!=',' != ']],
			        autoLoad: true 
			    });
				
				var operatorCombo = new Ext.form.ComboBox({
					valueField    : 'ID',
					displayField  : 'NAME',
					id            : 'idOperatorCombo',
					fieldLabel    : 'Operator',
					typeAhead     : true,
					triggerAction : 'all',
					editable      : false,
					mode          : 'local',
					width         : 200,
					allowBlank    : false,
		            msgTarget	  : 'side',
					store         : operatorComboStore,
					name		  : 'idOperatorCombo',
					hiddenName	  : 'idOperatorCombo', 
					disabled      : false,
					selectOnFocus : true,
					forceSelection: true
					
				});
				
				Ext.util.Format.comboRenderer = function(combo){
				    return function(value){
				        var record = combo.findRecord(combo.valueField, value);
				        return record ? record.get(combo.displayField) : combo.valueNotFoundText;
				    }
				} 
				
				var FieldNameStore = new Ext.data.Store({
				        proxy: new Ext.data.HttpProxy({url: 'ajaxListUserData.php?type=combo' }),
				        
				        reader: new Ext.data.JsonReader({
				            root: 'data',
				            fields: [
				                {name: 'ID'},
				                {name: 'NAME'}
				            ]
				        })
				    });
				FieldNameStore.load();
				
				FieldNameCombo = new Ext.form.ComboBox({
					    hiddenName    : 'idFieldNameCombo',
					    id            : 'idFieldNameCombo',
					    store         : FieldNameStore,
					    valueField    : 'ID',
					    displayField  : 'NAME',
					    width         : 260,
					    selectOnFocus : true,
					    editable      : false,
					    allowBlank    : false,
					    triggerAction : 'all',
					    mode          : 'local'
					  });


				var configUser_where_store = new Ext.data.JsonStore({
				        url : 'ajaxListUserData.php?idInboxData='+ID_INBOX+'&rolID='+rolID + '&idTable=' + idTable ,
				        root : 'data',
				        totalProperty : 'total',
				        autoWidth : true,
				        fields : [ 'ID', 'FIELD_NAME', 'FLD_DESCRIPTION','PARAMETERS_CONFIG_USER','ID_INBOX', 'ALIAS_TABLE','OPERATOR',
				                   {name: 'INCLUDE_SELECT', type: 'bool', 
										convert   : function(v){
										return (v === "A" || v === true) ? true : false;
    								}
				                  }]
					});
				configUser_where_store.load();  
					
				var configUser_where_cm = new Ext.grid.ColumnModel([
				    {
				    	header		: "Name",
				    	dataIndex	: 'FIELD_NAME',
				    	width		: 50
				    } , {
				    	header		: "Description",
				    	dataIndex	: 'FLD_DESCRIPTION',
				    	width		: 50
				    } ,checkColumnSelectConfigUser , 
				    {
				    	header		: "Operator",
				    	dataIndex	: 'OPERATOR',
				    	editor		: operatorCombo,
				    	renderer    : Ext.util.Format.comboRenderer(operatorCombo),
				    	width		: 20
				    },  {
				    	header		: "Parameters",
				    	dataIndex	: 'PARAMETERS_CONFIG_USER',
				    	editor		: FieldNameCombo,
				    	renderer    : Ext.util.Format.comboRenderer(FieldNameCombo),
				    	width		: 50
				    },
				    {
				    	header	  	: 'Clean',
				    	xtype     	: 'actioncolumn', 
			    		width     	: 20,
			    		hidden	  	: false,
			    		items     	: [{
			    			icon    : '/plugin/fieldcontrol/Clear.png', 
			    			tooltip : 'Clean',
			    			handler : function(grid, rowIndex, colIndex) {
								var rec = grid.getStore().getAt(rowIndex);
								rec.set('PARAMETERS_CONFIG_USER', '');
								rec.set('OPERATOR', '');
								rec.set('INCLUDE_SELECT', false);
			            	} 
			    		}]
			    	}
				]);
				configUser_where_cm.defaultSortable= true;	
				
				
				   
				var configUser_where_grid = new Ext.grid.EditorGridPanel({
					store			: configUser_where_store,
					cm				: configUser_where_cm,
					stripeRows		: true,
					autoScroll		: true,
					id			 	: 'configUser_where_grid',
					viewConfig 		: {
						forceFit 		: true,
						scrollOffset 	: 0,
						emptyText		: CustomColEmpty
					},
					bbar			: new Ext.PagingToolbar({
						pageSize	: 50,
						store		: configUser_where_store,
						displayInfo	: true,
						displayMsg	: lanDisplaying,
						emptyMsg	: CustomColEmpty2
					}),
					tbar 			: [{
						text		: 'Save Configuration ',
						cls 		: 'x-btn-text-icon',
						icon 		: '/images/ok.png',
						tooltip  	: 'Add drag and drop',
						handler		: function(){
							saveConfigUser(configUser_where_store);
						}
					}],
					plugins         : [checkColumnSelectConfigUser]
				});	
				var configUser_window = new Ext.Window({
					title			: lanConfigUser,	
			        closeAction 	: 'hide',
				    autoDestroy 	: true,
				    maximizable 	: true,     
			        modal       	: true,
			        id          	: 'popupconfigUser_window',
			        width 	    	: 800,
				    height 	    	: 500,   
				    closable    	: true,
					constrain   	: true,
					autoScroll  	: true,
			        items       	: configUser_where_grid,
			        layout      	: 'fit'
				});
				
				configUser_window.show();	
				configUser_window.toFront();	
				configUser_where_store.load(); 
		}
		
		function saveConfigUser(configUser_where_store)
		{
				var i  = 0;
				var arraybyFieldsAction = new Array ();
				var myJSON  = '';
				var swByFields = 0;
				configUser_where_store.each(function(record)  
				{  
					//console.log(record);
					if(record.get('INCLUDE_SELECT') == true)
					{
						if(record.get('OPERATOR') != '')
						{
							if(record.get('PARAMETERS_CONFIG_USER') != '')
							{
								var idInbox 	 	= Ext.getCmp('idInboxCombo').getValue();	 
								var fieldName       = record.get('FIELD_NAME'); 
								var description 	= record.get('DESCRIPTION');;
								var parametersField	= record.get('PARAMETERS_CONFIG_USER');
								var idTable			= record.get('ALIAS_TABLE');
								var idRoles      	= rolID;
								var operator      	= record.get('OPERATOR');
					
								var item = {
										"value"         : i,
										"idRoles"       : rolID,
										"idInbox"	    : idInbox,
										"fieldName"		: fieldName,
										"idTable"		: idTable,
										"parameterField": parametersField,
										"operator"		: operator
						
								};
								i++;
								arraybyFieldsAction.push(item);
							}
							else
							{
								alert("Register parameters");
								swByFields = 1;
							}
						}
						else
						{
							alert("Select Operator");
							swByFields = 1;
						}
					}
			    });
				
				if(arraybyFieldsAction.length != 0){
					myJSON= Ext.util.JSON.encode(arraybyFieldsAction);
					saveDataConfigUsers(myJSON);
				}
				else
				{
					myJSON = '';
					saveDataConfigUsers(myJSON);
				}
				
		}
		
		function saveDataConfigUsers(arrayConfigUsers)
		{  
			
			var ID_INBOX = Ext.getCmp('idInboxCombo').getValue();
			
			
		      Ext.MessageBox.show({ msg: _('ID_PROCESSING'), wait:true,waitConfig: {interval:200} });
		      
		       Ext.Ajax.request({
		    	   url: '../fieldcontrol/SaveWhereInbox.php',
		    	   params: {
		    	   		whereaction : 'configUsers',
		    	   		arrayConfigUsers : arrayConfigUsers,
		    	   		rolID 		:  rolID,
		    	   		idInbox 	:  ID_INBOX
		       		},
		       		success: function(r,o){
		       			Ext.MessageBox.hide();
	                    Ext.MessageBox.show({                            
	                         msg : MsgOperation,
	                         buttons : Ext.MessageBox.OK,
	                         icon : Ext.MessageBox.INFO
	                    }); 
	                    Ext.getCmp('popupconfigUser_window').hide();
	                    Ext.getCmp('WhereInbox_popup_grid').getStore().reload();	
		       		},
		       		failure: function(){
		       			Ext.MessageBox.alert('Error',MsgOpError);
		       			Ext.MessageBox.hide();
		       		}
		     	});
		      
		 }
	////////////////////////////By Fields Action  //////////////////////////////////////////////
		
	
		
		 var dateRow= Ext.data.Record.create([{
		        name: 'FIELD_NAME'
		    }]);

	    
});
