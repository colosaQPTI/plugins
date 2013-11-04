
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

	var titleGrid = 'Duplicate Rules Column List:';
	var select = 'Select';
	var copy = ' Copy';
	var process = 'Process';
	var delet = ' Delete';
	if(language == 'fr')
	{
		titleGrid = 'D\u00E9doublonnage Rules Liste des Colonnes:';
		select = 'S\u00E9lectionner';
		copy = ' Copier';
		delet = ' Supprimer';
		process = 'Processus';
	}
	
	var TableComboStore = new Ext.data.Store({
		proxy : new Ext.data.HttpProxy({url: 'ajaxDedoublonTableCombo.php?TYPE=DedoublonTableCombo'}),
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
	
	var store = new Ext.data.JsonStore({
			url           : 'columnVariablesProcess.php?id=' + rolID,
			root          : 'data',
			totalProperty : 'total', 
			remoteSort    : true,
			autoWidth     : true,
			fields        : ['FLD_UID', 'FLD_DESCRIPTION', 'FIELD_NAME', 'ROL_CODE','RATIO_FIELD','FIELD_REPLACE','COLOR' ,'ID_TABLE', 
			                 {name: 'CD_INCLUDE_OPTION', type: 'bool', 
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
		fieldLabel    : '<span style="color: red">*</span>' + select + ' ' + 'Table',
		emptyText     : select + 'Table...',
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
			}  
		}
		
	});
	
	var ProcessCombo = new Ext.form.ComboBox({
		valueField    : 'ID',
		displayField  : 'NAME',
		id            : 'idProcessCombo',
		fieldLabel    : '<span style="color: red">*</span>'+select + ' '+process,
		emptyText     : select + ' ' + process + '..',
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
		 		buttonAddRow = Ext.getCmp('idButtonAddRow');
		 		buttonAddRow.setDisabled(false);
		 		buttonDelRow = Ext.getCmp('idButtonDelRow');
		 		buttonDelRow.setDisabled(false);
		 		
		 		var idProcess = combo.getValue();
		 		store.removeAll();
				loadComboTable(idProcess);
			}  
		}
		
	});
	
	
	loadComboTable = function (ID_PROCESS)  {
		 Ext.Ajax.request({
		        method: "POST",
		        params : {
		          "idProcess" : ID_PROCESS
		        },
		        //url : '../ProductionAS400/ajaxDedoublonageConfig.php?configOption=dedoublonage' 
		        url : '../ProductionAS400/columnVariablesProcess.php?configOption=dedoublonage'
		        ,
		        success : function(result) {
			          var data = Ext.util.JSON.decode(result.responseText);
			          
			            if (data.success && data.data.length > 0) {
			            	
			            	idProcess  = Ext.getCmp('idProcessCombo').getValue();
			            	
			            	if(data.data[0].CD_ID_TABLE!='')
			            	{
			            		Ext.getCmp('idTableCombo').setValue(data.data[0].CD_ID_TABLE);
			            		Ext.getCmp('idTableCombo').setRawValue(data.data[0].CD_ID_TABLE);
			            	}
			            	else
			            	{
			            		console.log("ererr");
			            		Ext.getCmp('idTableCombo').setValue('');
			            		Ext.getCmp('idTableCombo').setRawValue('Select a Table...');
			            	}	
				        	  store.load({
				        		  params : {
									//'idTable' 	: idTable,
									'idProcess' : idProcess
									}
				        	  });
				        	  
			          }
			          else
		        	  {
			        	  Ext.Msg.show({  
			        		    title: 'Information',  
			        		    msg: 'Aucune variable de processus!',  
			        		    buttons: Ext.Msg.OK,  
			        		    icon: Ext.Msg.INFO, 
			        		    fn: this.callback  
			        		});
			        	  
		        	  }
			        }
		 })
	 };
	 
	
    // / --------- Head ----------- ///
	
	 var enable = true;
	
	 
    var FieldPanelToolBars = new Ext.FormPanel({            
    	bodyCssClass: 'frameRules', // Css Production
    	frame		: false,
    	width		: 200,
 		labelStyle  : 'font-weight:bold;',
 	    labelAlign  : 'right',
 	    border      : false, 
 		height      : 50,  
 		width	    : 900,
 	    defaults    : {allowBlank: false , border: false , bodyCssClass: 'frameRules'},
 	    items       : [
		{
			xtype: 'panel',  
			layout : 'column',
			border : false,
			bodyCssClass: 'frameRules',
			defaults:{
			    border:false,
			    bodyCssClass: 'frameRules'
			},
			labelWidth: 150,
			items  :[
	                {   // column #1
	                    columnWidth: .32,
	                    layout: 'form',
	                    border: false,
	                    items: [ ProcessCombo ] // close items for first column
	                } ,{   // column #1
	                    columnWidth: .32,
	                    layout: 'form',
	                    border: false,
	                    items: [ TableCombo ] // close items for second column
	                } , { 
	                    columnWidth: .10,
	                    items: [ {
	    	    			xtype	: 'button',
	    	             	text    : copy + ' Field ', 
	    	    			id      : 'idButtonAddRow',
	    	    			disabled: true,
	    	    			//icon    : '/plugin/ProductionAS400/duplicate.png', 
	    	    			iconCls : 'button_menu_ext ss_copy',
	    	    			tooltip : 'Edit',
	    	    			
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
	        	    				 					RATIO_FIELD : row.RATIO_FIELD,
	        	    				 					INNER_JOIN : row.INNER_JOIN,
	        	    				 					FIELD_REPLACE : row.FIELD_REPLACE,
	        	    				 					ALIAS_TABLE : row.ALIAS_TABLE,
	        	    				 					ID_TABLE : row.ID_TABLE,
	        	    				 					COLOR : '',
	        	    				 					CD_INCLUDE_OPTION : row.CD_INCLUDE_OPTION
	        	    				 					});
	        	    			
	        	    			store.insert(numberRow+1,newRow);
	        	    			gridConfiguration.startEditing(numberRow+1, 2);
	    	            	}
	                 } ] // close items for second column
	                } , {
	                    columnWidth: .10,
	                    layout: 'form',
	                    border: false,
	                    items: [{
	    	    			xtype	: 'button',
	    	             	text    : delet + ' Field ', 
	    	    			id      : 'idButtonDelRow',
	    	    			disabled: true,
	    	    			//icon    : '/plugin/ProductionAS400/delete_item.png', 
	    	    			iconCls :'button_menu_ext ss_del',
	    	    			//iconCls : 'delField',
	    	    			tooltip : 'Edit',
	    	    			
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
	              } , {
	            	  columnWidth: .10,
	                    layout: 'form',
	                    border: false,
	                    items: [{
							xtype : 'button',
							text    :_('ID_SAVE'),
					        iconCls :'button_menu_ext ss_save',
							id      : 'addup',
							width : 70,
					        handler : function() {
								dataGridreview();
							}
						}]
	              }
	            ]
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
 	   	dataIndex: 'CD_INCLUDE_OPTION',
 	   	id: 'check',
 	   	flex: 1,
 	   	width: 10,
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
    
    var ratioField =  new Ext.form.TextField ({
		allowBlank : true,
		height     : 30,
		disabled   : false,
		anchor     : '100%'
    });
    
     var typeAS = new Ext.form.ComboBox({
		valueField    : 'ID',
		displayField  : 'NAME',
		id            : 'idtypeAS',
		fieldLabel    : '<span style="color: red">*</span>Choose Type ',
		typeAhead     : true,
		triggerAction : 'all',
		editable      : true,
		mode          : 'local',
		width         : 200,
		autoHeight	  : true,
		listWidth	  : 250,
		allowBlank    : false,
		disabled      : false,
		defaultValue  : "String",
		value         : "String",
		store: new Ext.data.SimpleStore({
	            fields: ["ID", "NAME"],
	            data : [["String","String"],
	                    ["Integer","Integer"]
	                   ]
	        }),
		listeners   :{
	           beforerender : function(combo){
	              combo.setValue("String");
	              Ext.getCmp('idtypeAS').setValue("String");
	           },
	           load: function () {
	               var combo = Ext.getCmp('idtypeAS');
	                combo.setValue("String");
	            }
    	}
    });
     Ext.getCmp('idtypeAS').setValue("String");
    
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
    		width     : 20,
    		sortable  : true,
    		dataIndex : 'FLD_DESCRIPTION',
    		editor	  : description
    	},  checkColumnInclude, 
    	
    	{
    		header    : "Ratio",
    		dataIndex : 'RATIO_FIELD', 
    		width     : 10,
    		sortable  : true,
    		editor    : ratioField
		
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
    		hidden	  : true,
    		editor    : typeAS
		}]
    
    });
    
    var props = function (){}
    var dateRow= Ext.data.Record.create([{
        name: 'FLD_UID' 
    }]);
	var gridConfiguration = new Ext.grid.EditorGridPanel({
		store 		   : store,
		columnLines	   : true,
		id 			   : 'grid',
		ddGroup		   :'gridDD',
		enableDragDrop : true,
		cm 			   : gridcolumns,
		tbar           : FieldPanelToolBars,
		columnLines    : true,
		plugins        : [checkColumnInclude],
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
		
		if(!idTable.length)
		{
			Ext.MessageBox.alert('Error', 'You must select a table!');
    		return 0;	
		}
		/*var joinConfig = Ext.getCmp('idQuery').getValue();
		var tokenCsv = Ext.getCmp('idToken').getValue();
		var configWhere = Ext.getCmp('idWhere').getValue();
			*/		
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
	        		/*joinConfig : joinConfig,
	        		tokenCsv : tokenCsv,
	        		configWhere : configWhere*/
	            },
	            url : '../ProductionAS400/configurationDedoublonage_Save.php',
	            success : function(save) {
	                var data = Ext.decode(save.responseText);
	                var url  = data.success;
	                gridConfiguration.getStore().commitChanges();
	                gridConfiguration.getStore().reload();
	                Ext.MessageBox.hide();
	                Ext.getCmp('idFieldName').setValue('');
	                var idTable   = Ext.getCmp('idTableCombo').getValue();
	                /*/var innerJoin = Ext.getCmp('idQuery').getValue();
					if(idTable == '')
						idTable = idpmTable;*/
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
					if(record.get('CD_INCLUDE_OPTION') == true)
					{	
						var required  = 0;
						var idTable      = record.get('ID_TABLE');
						var idProcess 	 = Ext.getCmp('idProcessCombo').getValue();
						var idField = record.get('FLD_UID');
						//var innerJoin  = Ext.getCmp('idQuery').getValue();
						var descripField = record.get('FLD_DESCRIPTION');
						var ratio = record.get('RATIO_FIELD');
						var fieldName = record.get('FIELD_NAME');
						/*var aliasTable  = record.get('ALIAS_TABLE');
						if(aliasTable == undefined || aliasTable == '')
							aliasTable 	= '';
						*/
						
						var j = 0;
						var item = {
							"value"        : i,
							"idTable"      : idTable,
							"idProcess"	   : idProcess,
							"idField"      : idField,
							//"innerJoin"    : innerJoin,
							"descripField" : descripField,
							"ratio"	   	   : ratio,
							"fieldName"    : fieldName
	    				};
						i++;
						miArray.push(item);
	    			}
					else
						nothing = 0;
	    		}
	    });
		var idProcess 	 = Ext.getCmp('idProcessCombo').getValue();	
		//myJSON = JSON.stringify({miArray: miArray});

		//if(miArray.length != 0){
			myJSON= Ext.util.JSON.encode(miArray);
			
			saveDataField(myJSON, idProcess)
		//}
		/*else
		{
			alert('Select Items please');
		}*/
		
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
