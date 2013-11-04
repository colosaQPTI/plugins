
Ext.onReady(function() 
{
	var numberRow;
	
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
	var titleGrid = 'Column List:';
	var process = 'Process';
	var select = 'Select';
	var task = 'Task';
	var copy = ' Copy';
	var delet = ' Delete';
	if(language == 'fr')
	{
		titleGrid = 'Liste des Colonnes:';
		select = 'S\u00E9lectionner';
		process = 'Processus';
		task = 'T\u00E2che';
		copy = ' Copier';
		delet = ' Supprimer';
	}
	  
	var TableComboStore = new Ext.data.Store({
		proxy : new Ext.data.HttpProxy({url: 'ajaxReporTableCombo.php?TYPE=reportTableCombo'}),
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
	
	var ProcessComboStore = new Ext.data.Store({
		
		proxy : new Ext.data.HttpProxy({url: 'ajaxProcessCombo.php?Type=ProcessCombo'}),
		reader : new Ext.data.JsonReader({
			root   : 'data',
			fields : [
				{name : 'ID'},
				{name : 'NAME'}
			]
		})
			
	});
	ProcessComboStore.load();
	
	var TaskComboStore = new Ext.data.Store({
			
			proxy : new Ext.data.HttpProxy({url: 'ajaxTaskCombo.php?Type=TaskCombo'}),
			reader : new Ext.data.JsonReader({
				root   : 'data',
				fields : [
					{name : 'ID'},
					{name : 'NAME'}
				]
			})
				
		});
	TaskComboStore.load();
	
	var store = new Ext.data.JsonStore({
			url           : 'ajaxListConfiguration.php?id=' + rolID,
			root          : 'data',
			totalProperty : 'total', 
			//remoteSort    : true,
			autoWidth     : true,
			fields        : ['ADD_TAB_NAME','FLD_UID', 'FLD_DESCRIPTION', 'FIELD_NAME', 'ROL_CODE','LENGTH_FIELD','AS400_TYPE','INNER_JOIN','FIELD_REPLACE','ID_INBOX', 'ALIAS_TABLE','ID_TABLE','COLOR' , 'CONSTANT',
			                 {name: 'INCLUDE_OPTION', type: 'bool', 
								convert   : function(v){
									return (v === "A" || v === true) ? true : false;
	        					}
			                 },{name: 'REQUIRED', type: 'bool', 
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
		fieldLabel    : '<span style="color: red">*</span>Select Table',
		emptyText     : 'Select a Table...',
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
		 		buttonWhereConfig = Ext.getCmp('idButtonWhereConfig');
		 		buttonWhereConfig.setDisabled(false);
		 		buttonAddRow = Ext.getCmp('idButtonAddRow');
		 		buttonAddRow.setDisabled(false);
		 		buttonAddUp = Ext.getCmp('addup');
		 		buttonAddUp.setDisabled(false);
		 		buttonDelRow = Ext.getCmp('idButtonDelRow');
		 		buttonDelRow.setDisabled(false);
		 		Ext.getCmp('idQuery').setValue(record.data.INNER_JOIN);
		 		idProcess = Ext.getCmp('idProcessCombo').getValue();	
		 		store.load({
					params : {
						'idTable' :combo.getRawValue(),
						'idProcess' :idProcess
					}
				})
			}  
		}
		
	});
	
	var ProcessCombo = new Ext.form.ComboBox({
		valueField    : 'ID',
		displayField  : 'NAME',
		id            : 'idProcessCombo',
		fieldLabel    : '<span style="color: red">*</span>' + select + ' ' + process,
		emptyText     : select + ' ' + process + '...',
		typeAhead     : true,
		triggerAction : 'all',
		editable      : false,
		mode          : 'local', 
		width         : 200,
		allowBlank    : false,
		store         : ProcessComboStore,
		name		  : 'idProcessCombo',
		hiddenName	  : 'idProcessCombo',
		selectOnFocus :false,
		listeners     :{
			select : function(combo, record) {
				TableCombo.setDisabled(false);
		 		TableComboAux=Ext.getCmp('idTableCombo');
		 		TableComboAux.clearValue();
		 		var idTable = TableComboAux.getValue();
		 		Ext.getCmp('grid').render();
		 		var idProcess = combo.getValue();
		 		TaskComboAux=Ext.getCmp('idTaskCombo');
		 		TaskComboAux.clearValue();
		 		store.removeAll();
				TableComboStore.removeAll();
				TableComboStore.load({
					params : {
					'idProcess' :idProcess
					}
				});
				loadComboTable(idProcess);
			}  
		}
		
	});
	
	var TaskCombo = new Ext.form.ComboBox({
		valueField    : 'ID',
		displayField  : 'NAME',
		id            : 'idTaskCombo',
		fieldLabel    : select + ' ' + task,
		emptyText     : select + ' ' + task + '...',
		typeAhead     : true,
		triggerAction : 'all',
		editable      : false,
		mode          : 'local', 
		width         : 200,
		allowBlank    : true,
		store         : TaskComboStore,
		name		  : 'idTaskCombo',
		hiddenName	  : 'idTaskCombo',
		selectOnFocus :false,
		listeners     :{
			select : function(combo, record) {
				store.setBaseParam('idTask', combo.getRawValue());
				var idTask = combo.getRawValue();
			}  
		}
	});
	
	loadComboTable = function (ID_PROCESS)  {
		
		Ext.getCmp('idQuery').setValue('');
		Ext.getCmp('idWhere').setValue('');
		Ext.getCmp('idToken').setValue('');
		 Ext.Ajax.request({
		        method: "POST",
		        params : {
		          "idProcess" : ID_PROCESS
		        },
		        url : '../ProductionAS400/ajaxListConfiguration.php?configOption=doublonB' 
		        ,
		        success : function(result) {
			          var data = Ext.util.JSON.decode(result.responseText);
			            if (data.success && data.data.length > 0) {
			            	if(data.data[0].SW == 1)
			            	{
			            	  var idTable = data.data[0].ID;
				        	  if(idTable != '')
				        	  {
				        		  buttonQuery=Ext.getCmp('idButtonQuery');
				        		  buttonQuery.setDisabled(false);
				        		  buttonInsertQuery=Ext.getCmp('idButtonInsertQuery');
				        		  buttonInsertQuery.setDisabled(false);
				        		  buttonWhereConfig=Ext.getCmp('idButtonWhereConfig');
				  		 		  buttonWhereConfig.setDisabled(false);
				  		 		  buttonAddRow = Ext.getCmp('idButtonAddRow');
				  		 		  buttonAddRow.setDisabled(false);
				  		 		  buttonAddUp = Ext.getCmp('addup');
				  		 		  buttonAddUp.setDisabled(false);
				  		 		  buttonDelRow = Ext.getCmp('idButtonDelRow');
						 		  buttonDelRow.setDisabled(false);
						 		
				        	  }
				        	 
				        	  Ext.getCmp('idTableCombo').setValue(data.data[0].ID);
				        	  Ext.getCmp('idTableCombo').setRawValue(data.data[0].NAME);
				        	  Ext.getCmp('idTaskCombo').setValue(data.data[0].TASK_UID);
				        	  Ext.getCmp('idTaskCombo').setRawValue(data.data[0].TASK_NAME);
				        	  Ext.getCmp('idQuery').setValue(data.data[0].INNER_JOIN);
				        	  Ext.getCmp('idWhere').setValue(data.data[0].CONFIG_WHERE);
				        	  Ext.getCmp('idToken').setValue(data.data[0].TOKEN_CSV);
				        	  idProcess  = Ext.getCmp('idProcessCombo').getValue();	
				        	  store.load({
				        		  params : {
									'idTable' 	: idTable,
									'idProcess' : idProcess
									}
				        	  })
			            	}
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
	 /*saveFields = new Ext.Action({
	        text    :'Save',
	        iconCls :'button_menu_ext ss_save',
			id      : 'addup',
	        handler : dataGridreview
	 });
	 */
	 
    var FieldPanelToolBars = new Ext.FormPanel({            
    	bodyCssClass: 'frameForm',
        frame: false,
        width: 200,
		labelStyle : 'font-weight:bold;',
		labelWidth: 130,
	    labelAlign: 'right',
		height     : 95,  
		items      : [{
			layout  	: {
				type    : 'table',
				border  : false,
				frame   : false,
				colspan : 7,
				bodyCssClass: 'frameForm'
		    },
		    bodyCssClass: 'frameForm',
		    defaults 	: {
		    	padding  : 3,  
		    	border 	 : false,
		    	bodyCssClass: 'frameForm'
			},
          	items: [{
				rowspan  : 2,
				xtype    : 'panel',
				defaults : {
					"width" : 220,
					bodyCssClass: 'frameForm'
				},
				layout : "form",
				border : false,
				bodyCssClass: 'frameForm',
				items  : [
				    ProcessCombo, TaskCombo , TableCombo
				]
			},
			{
				xtype    : 'panel',
				defaults : {
					"width" : 220,
					"height": 50,
					border  : false
				},
				layout : "form",
				border : false,
				labelWidth: 85,
				bodyCssClass: 'frameForm',
                items: [{
                	labelAlign	: 'right',
                	fieldLabel	: 'Add Join',
				    xtype 		: 'textarea',
				    id			: 'idQuery',     
				    disabled	: true,
				    width 		: 170,
				    forceSelection: true,       
				    emptyText	: 'Insert Join...',  
				    triggerAction: 'all',      
				    editable	:false
				    
                }]
			},
			{          
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
		    			icon    : '/plugin/ProductionAS400/insert_query.png', 
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
		    			icon    : '/plugin/ProductionAS400/fieldSelected1.png', 
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
		             }]
				
        	} , {
					xtype    : 'panel',
					defaults : {
						"width" : 150,
						"height": 25,
						border  : false
					},
					layout : "form",
					border : false,
					labelWidth: 85,
	                items: [{
	                	labelAlign	: 'right',
	                	fieldLabel	: 'Add Where',
					    xtype 		: 'textfield',
					    id			: 'idWhere',     
					    disabled	: true,
					    forceSelection: true,       
					    emptyText	: 'Insert Where...',  
					    triggerAction: 'all',      
					    editable	: false
	                	} , {
	                	labelAlign	: 'right',
	                	fieldLabel	: 'TokenCSV',
					    xtype 		: 'textfield',
					    id			: 'idToken',     
					    disabled	: false,
					    forceSelection: true,       
					    emptyText	: 'Insert Token...',  
					    triggerAction: 'all',      
					    editable	: true
					    
	                }]
				} , {          
					rowspan: 2,
					defaults: {
						"width": 80,
						"height": 25,
	                    border : false
					},
					items: 
						[{
			             	xtype	: 'button',
			             	text    :' Where ', 
			    			id      : 'idButtonWhereConfig',
			    			disabled: true,
			    			width : 80,
			    			align : 'center',
			    			icon    : '/plugin/ProductionAS400/where_query.png', 
			    			iconCls :'addWhere',
			    			handler : function() {
								popupWhere();
			            		}
			            	} , {
							xtype : 'button',
							text    :_('ID_SAVE'),
					        iconCls :'button_menu_ext ss_save',
							id      : 'addup',
							disabled: true,
							width : 80,
					        handler : function() {
								dataGridreview();
							}
						}]
				} , {
					rowspan : 2,
					defaults: {
						"width": 10,
						border : false
					} ,
					items:
						[{
					    			xtype	: 'button',
					             	text    : '&nbsp;' + copy +' <br> &nbsp; Field ', 
					    			id      : 'idButtonAddRow',
					    			disabled: true,
					    			icon    : '/plugin/ProductionAS400/duplicate.png', 
					    			iconCls : 'copyField',
					    			tooltip : 'Edit',
					    			width 	: 80,
					    			handler : function() {
										
										if(numberRow==undefined)
										{
											Ext.Msg.alert('Selection', 'You must select an item in the grid to copy!');	
											return 0;
										}
										
										var rec = gridConfiguration.getStore().getAt(numberRow);
				    					var row = rec.data;
				    	    			GridLength = Ext.getCmp('grid').getStore().data.length;
				    	    			var newRow = new dateRow({ ADD_TAB_NAME : row.ADD_TAB_NAME,
				    	    									FLD_UID : '' , 
				    	    				 					FLD_DESCRIPTION : '',
				    	    				 					FIELD_NAME : row.FIELD_NAME,
				    	    				 					ROL_CODE : row.ROL_CODE,
				    	    				 					LENGTH_FIELD : row.LENGTH_FIELD,
				    	    				 					AS400_TYPE : row.AS400_TYPE,
				    	    				 					INNER_JOIN : row.INNER_JOIN,
				    	    				 					FIELD_REPLACE : row.FIELD_REPLACE,
				    	    				 					ID_INBOX : row.ID_INBOX,
				    	    				 					ALIAS_TABLE : row.ALIAS_TABLE,
				    	    				 					ID_TABLE : row.ID_TABLE,
				    	    				 					COLOR : '',
				    	    				 					INCLUDE_OPTION : row.INCLUDE_OPTION,
				    	    				 					CONSTANT : row.CONSTANT
				    	    				 					});
				    	    			
				    	    			store.insert(numberRow+1,newRow);
				    	    			gridConfiguration.startEditing(numberRow+1, 2);
					            	}
				             } , {
					    			xtype	: 'button',
					             	text    : '&nbsp;' + delet + '<br> &nbsp; Field ', 
					    			id      : 'idButtonDelRow',
					    			disabled: true,
					    			icon    : '/plugin/ProductionAS400/delete_item.png', 
					    			iconCls : 'delField',
					    			tooltip : 'Edit',
					    			width   : 80,
					    			handler : function() {
										
										if(numberRow==undefined)
										{
											Ext.Msg.alert('Selection', 'You must select an Item to remove!');	
											return 0;
										}
										
				    					PMExt.confirm('Confirmation', 'Are you sure you want to delete the item?', function() {
				    					    var records = Ext.getCmp('grid').getSelectionModel().getSelections();
				    					    var rec = gridConfiguration.getStore().getAt(numberRow);
					    					var row = rec.data;
					    					if(!row.FLD_UID)
					    						Ext.each(records, Ext.getCmp('grid').store.remove, Ext.getCmp('grid').store);
					    					else
					    					{
					    						alert('Unable to delete the selected item!');	
												return 0;
											}
					    						
				    					});   
				    					
					            	}
				             }]
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
    
    var checkColumnRequired = new Ext.grid.CheckColumn({
    	header: 'Required?',
    	dataIndex: 'REQUIRED',
    	id: 'checkRequired',
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
    
    var LengthField =  new Ext.form.TextField ({
		allowBlank : true,
		height     : 30,
		disabled   : false,
		anchor     : '100%'
    });
    
    var ConstantField = new Ext.form.TextField ({
    	allowBlank : true,
    	height	   : 30,
    	disabled   : false,
    	anchor	   : '100%'
    });
    
    Ext.util.Format.comboRenderer = function(combo){
	return function(value){
		var record = combo.findRecord(combo.valueField, value);
		
		return record ? record.get(combo.displayField) : combo.valueNotFoundText;
	    }
	} 
	
	var typeAsComboStore = new Ext.data.SimpleStore({
	fields: ['ID', 'NAME'],
	data: [["String","Chaine"],
        ["strSecure", "Chaine sans accents"],
        ["strSecureL", "Sans accents en minuscule"],
        ["strSecureU", "Sans accents en majuscule"],
        ["Integer", "Entier"],
        ["Ymd", "Date AAAAMMJJ"],
        ["Y.m.d", "Date AAAA.MM.JJ"],
        ["Y-m-d", "Date AAAA-MM-JJ"],
        ["dmY", "Date JJMMAAAA"],
        ["d.m.Y", "Date JJ.MM.AAAA"],
        ["d-m-Y", "Date JJ-MM-AAAA"],
        ["ymd", "Date AAMMJJ"],
        ["y.m.d", "Date AA.MM.JJ"],
        ["y-m-d", "Date AA-MM-JJ"],
        ["dmy", "Date JJMMAA"],
        ["d.m.y", "Date JJ.MM.AA"],
        ["d-m-y", "Date JJ-MM-AA"],
        ["Decimal", "Decimal"],
        ["Telephone", "Telephone"],
            ["AI", "Actif / Inactif"],
            ["cp", "Code postal"],
            ["Yesno", "O ou N"],
            ["OuiNon", "Oui / Non"],
            ["binaire", "0 ou 1"],
            ["NCommande", "Numéro Commande"],
	                    ["codeOper","Code Opération"],
	                    ["Ignore","Ignorer cette donnée"]],
	autoLoad: true 
	});
				
	var typeAS = new Ext.form.ComboBox({
		valueField    : 'ID',
		displayField  : 'NAME',
		id            : 'idtypeAS',
		fieldLabel    : '<span style="color: red">*</span>Choose Type',
		typeAhead     : true,
		triggerAction : 'all',
		editable      : false,
		mode          : 'local',
		anchor	      : '95%',
		hidden	      : false,
		hideLabel     : false,
		width         : 200,
		allowBlank    : false,
		msgTarget	  : 'side',
		store         : typeAsComboStore,
		name		  : 'idtypeASCombo',
		hiddenName	  : 'idtypeASCombo', 
		disabled      : false,
		selectOnFocus : true,
		forceSelection: true,
		listeners   :{
	           beforerender : function(combo){
	              combo.setValue("String");
	              Ext.getCmp('idtypeAS').setValue("Chaine");
	           },
	           load: function () {
	               var combo = Ext.getCmp('idtypeAS');
	                combo.setValue("String");
	            }
		}
	});
	Ext.getCmp('idtypeAS').setValue("String");
	//Ext.getCmp('idtypeAS').setRawValue("Chaine");
   
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
    	},  checkColumnInclude, 
    	
    	{
    		header    : "Length",
    		dataIndex : 'LENGTH_FIELD', 
    		width     : 15,
    		sortable  : true,
    		editor    : LengthField
		
    	},
    	{
    		header    : "Table",
    		dataIndex : 'ID_TABLE', 
    		width     : 15,
    		sortable  : true,
    		hidden	  : true
		
    	},{
    		header    : "Type AS400",
    		dataIndex : 'AS400_TYPE', 
    		width     : 15,
    		sortable  : true,
    		editor    : typeAS,
		renderer  : Ext.util.Format.comboRenderer(typeAS)
		}, checkColumnRequired 
		, {
    		header    : "Constant",
    		dataIndex : 'CONSTANT', 
    		width     : 10,
    		sortable  : true,
    		editor    : ConstantField
		
    	}]
    
    });
    
    var props = function (){}
    var dateRow= Ext.data.Record.create([{
        name: 'FLD_UID' 
    }]);
	var gridConfiguration = new Ext.grid.EditorGridPanel({
		store 		   : store,
		columnLines	   : true,
		id 		   : 'grid',
		ddGroup		   :'gridDD',
		enableDragDrop 	   : true,
		cm 		   : gridcolumns,
		tbar           : FieldPanelToolBars,
		columnLines    : true,
		plugins        : [checkColumnInclude,checkColumnRequired],
		title          : 'Production ' + titleGrid,
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
		selModel       : new Ext.grid.RowSelectionModel({
			singleSelect : true,
			listeners    :{
	            rowselect: function(sm, rowIndex , record){
					numberRow = rowIndex;
					
	            }
		      }
			}),
		listeners      : {  //drag and drop
			"render": {
		  		scope: this,
		  		fn: function(grid) {
					var ddrow = new Ext.dd.DropTarget(grid.container, {
						ddGroup : 'gridDD',
						copy:false,
						notifyDrop : function(dd, e, data){
							var ds = grid.store;
							var sm = gridConfiguration.getSelectionModel();
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
    	tbar: ['<b>'+_('ID_ROLE') + ' : ' + rolID +'</b>',{xtype: 'tbfill'},backButton]
    });
    
	var fieldInboxPanel = new Ext.Panel({
		autoWidth    : true,
		height       : 550,
		layout       : 'fit',
		autoScroll	 : true,
		items        : [
			gridConfiguration
		]
		//tbar           : [saveFields]
		
	});
	
	tabsPanelField = new Ext.Panel({
       	region: 'center',
    	activeTab: true,
    	items:[fieldInboxPanel]
    });
	
	var viewport = new Ext.Viewport({
		layout : 'border',
		items  : [ tabsPanelField]
	});

	function popupQuery()
	{
		
		formQuery.getForm().reset();
		var textQuery = Ext.getCmp('idQuery').getValue();
		formQuery.getForm().findField('queryfield').setValue(textQuery);
		  
	    wQuery = new Ext.Window({
	        title       : "Add & Edit Query",
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
			fieldLabel	: "Please type the Query Statement", 
			name		: 'nameQuery', 
			width		: 450,
			height		: 200,
			disabled	: false
		}],
		buttons	: [
		    {text : "Save" , handler:  saveQuery},
		    {text : "Cancel", handler: CloseWindow}
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
					idProcess : Ext.getCmp('idProcessCombo').getValue(),
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


	// popup Where
	function popupWhere()
	{		
		formWhere.getForm().reset();
		var textWhere = Ext.getCmp('idWhere').getValue();
		formWhere.getForm().findField('wherefield').setValue(textWhere);
		  
	    wWhere = new Ext.Window({
	        title       : "Add & Edit Where",
	        closeAction : 'hide',
		    autoDestroy : true,
		    maximizable : true,     
	        modal       : true,
	        id          : 'popupWhere',
	        width 	    : 600,
		    height 	    : 312, 
		    closable    : true,
			constrain   : true,
			autoScroll  : true,
	        items       : [formWhere],
	        layout      : 'fit'
	    });
	    
	    wWhere.show();
	}

	//Close Popup Window Where
	CloseWindowWhere = function(){
	    Ext.getCmp('popupWhere').hide();
	};
	
	formWhere = new Ext.FormPanel({
		frame 	: true,
		items 	:[{
			id			:'wherefield', 
			xtype		: 'textarea', 
			fieldLabel	: "Please type the Where Statement", 
			name		: 'nameWhere', 
			width		: 450,
			height		: 200,
			disabled	: false
		}],
		buttons	: [
		    {text : "Save" , handler:  saveWhere},
		    {text : "Cancel", handler: CloseWindowWhere}
		]
	});
	
	function saveWhere() {
		 textWhere = formWhere.getForm().findField('wherefield').getValue();
		 Ext.getCmp('idWhere').setValue(textWhere);
		 var idTable   = Ext.getCmp('idTableCombo').getValue();
         
		 if(idTable == '')
			idTable = idpmTable;
		 if(textWhere == ''){
		 store.load({
				params: {
					inner   : textWhere,
					idTable : idTable,
					idProcess : idProcess,
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
		 CloseWindowWhere();
		 
	};
	
	function saveDataField(myArray, idProcess)
	{
		
		//return 0;
		var rowModel = gridConfiguration.getSelectionModel().getSelected();
		var idTable = Ext.getCmp('idTableCombo').getValue();
		var tableName = Ext.getCmp('idTableCombo').getRawValue();
		var joinConfig = Ext.getCmp('idQuery').getValue();
		var tokenCsv = Ext.getCmp('idToken').getValue();
		var configWhere = Ext.getCmp('idWhere').getValue();
		var idTask = Ext.getCmp('idTaskCombo').getValue();
			
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
	        		idProcess : idProcess,
	        		idTable : idTable,
	        		tableName : tableName,
	        		joinConfig : joinConfig,
	        		tokenCsv : tokenCsv,
	        		configWhere : configWhere,
	        		idTask : idTask
	            },
	            url : '../ProductionAS400/configurationProductionAS_Save.php',
	            success : function(save) {
	                var data = Ext.decode(save.responseText);
	                var url  = data.success;
	                gridConfiguration.getStore().commitChanges();
	                gridConfiguration.getStore().reload();
	                Ext.MessageBox.hide();
	                Ext.getCmp('idFieldName').setValue('');
	                var idTable   = Ext.getCmp('idTableCombo').getValue();
	                var innerJoin = Ext.getCmp('idQuery').getValue();
					if(idTable == '')
						idTable = idpmTable;
					/* store.load({
							params: {
								inner   : innerJoin,
								idTable : idTable,
								idProcess : idProcess
				            },
				            callback: function(records, operation, success) {
				            	var error = store.reader.jsonData.response;
				            	var self = this;
				            	if(success == true)
				            		Ext.MessageBox.hide();
				            	else
				            		Ext.MessageBox.alert('Error', error);
				            }
				        });*/
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
		var idField = '';
		var swD     = 0;
		var i       = 0;
		var miArray = new Array ();
		var myJSON  = '';
		store.each(function(record)  
		{  
			if(record.get('FLD_UID') == '')
				record.set('FLD_UID' , record.get('FIELD_NAME'));
				
				if(idField != record.get('FLD_UID'))
				{
					var idField = record.get('FLD_UID');
					if(record.get('INCLUDE_OPTION') == true)
					{	
						var required  = 0;
						var idTable      = record.get('ID_TABLE');
						var idProcess 	 = Ext.getCmp('idProcessCombo').getValue();
						var idField = record.get('FLD_UID');
						var innerJoin  = Ext.getCmp('idQuery').getValue();
						var descripField = record.get('FLD_DESCRIPTION');
						var length = record.get('LENGTH_FIELD');
						var fieldName = record.get('FIELD_NAME');
						var as400Type = record.get('AS400_TYPE');
						if(record.get('REQUIRED') == true)
							required = 'yes';
						else
							required = 'no';
						var constant = record.get('CONSTANT');
						var aliasTable  = record.get('ALIAS_TABLE');
						if(aliasTable == undefined || aliasTable == '')
							aliasTable 	= '';
						
						
						var j = 0;
						var item = {
							"value"        : i,
							"idTable"      : idTable,
							"idProcess"	   : idProcess,
							"idField"      : idField,
							"innerJoin"    : innerJoin,
							"descripField" : descripField,
							"length"	   : length,
							"fieldName"    : fieldName,
							"as400Type"    : as400Type,
							"required"	   : required,
							"constant"	   : constant
	    				};
						i++;
						miArray.push(item);
	    			}
					else
						nada = 0;
	    		}
	    });
		var idProcess 	 = Ext.getCmp('idProcessCombo').getValue();	
		//myJSON = JSON.stringify({miArray: miArray});

		if(miArray.length != 0){
			myJSON= Ext.util.JSON.encode(miArray);
			
			saveDataField(myJSON, idProcess)
		}
		else
		{
			alert('Select Items please');
		}
		
		
		
	}
	    
	function executeInner(inner, idTable)
	{ 
		FieldName.setDisabled(false);
		idInbox = Ext.getCmp('idProcessCombo').getValue();	  	
		FieldNameStore.load({
			params: {
				idProcess : idInbox,	
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
				idProcess : idInbox
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

});
