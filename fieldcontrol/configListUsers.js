var editor;
var fieldNameData;

Ext.onReady(function() 
{
	var lanMsgEmpty = 'There are no options to display';
	var lanMsgEmpty2= 'No actions to display';
	var lanSaveConfig = 'Save config';
	var lanMsgDisplay = 'Displaying {0} - {1} of {2}';
	var lanMsgOpe = 'The operation completed sucessfully!';
	var lanMsgOpeError= 'The operation was not completed sucessfully!';
	
	if(language == 'fr')
	{
		lanMsgEmpty = "Il n'y a aucune action \u00E0 afficher";
		lanMsgEmpty2 = 'Aucune action \u00E0 afficher';
		lanSaveConfig = 'Sauver config';
		lanMsgDisplay = 'Affichage {0} - {1} sur {2}';
		lanMsgOpe = "L'opération s'est termin\u00E9e avec succ\u00E8s!";
		lanMsgOpeError= "L'op\u00E9ration n'a pas \u00E9t\u00E9 compl\u00E9t\u00E9e avec succ\u00E8s!";
		
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
	
	 var checkColumnHidden = new Ext.grid.CheckColumn({
	    	header: 'Hidden?',
	    	dataIndex: 'HIDDEN_FIELD',
	    	id: 'checkHidden',
	    	flex: 1,
	    	width: 10,
	    	align: 'center',
	    	processEvent: function () { return false; }
	 }); 
	 
	 var checkColumnInclude = new Ext.grid.CheckColumn({
	    	header: 'Include ?',
	 	   	dataIndex: 'INCLUDE_OPTION',
	 	   	id: 'check',
	 	   	flex: 1,
	 	   	width: 10,
	 	    align: 'center',
	 	   	processEvent: function () { return false; }
	 });
	 
	 var description =  new Ext.form.TextField ({
			allowBlank : true,
			height     : 50,
			disabled   : false,
			anchor     : '100%'
	 });

	var ConfigUsers_store = new Ext.data.JsonStore({
	        url : 'ajaxListUserFields.php?type=list',
	        root : 'data',
	        totalProperty : 'total',
	        autoWidth : true,
	        fields : [ 'ID_FIELD', 'FIELD_NAME', 'DESCRIPTION','TABLE','POSITION',
	                   {name: 'INCLUDE_OPTION', type: 'bool', 
							convert   : function(v){
								return (v === "A" || v === true) ? true : false;
							}
	                   },{name: 'HIDDEN_FIELD', type: 'bool', 
            	 			convert   : function(v){
         	 					return (v === "A" || v === true) ? true : false;
          					}
	                   }
	        		]
	    });
	ConfigUsers_store.load();  
	
	var ConfigUsers_cm = new Ext.grid.ColumnModel({
		defaults : {
			width : 20,
			sortable : true
		},
		columns : [
		    {
		    	header: "Field Name",
		    	width: 20,
		    	dataIndex: 'FIELD_NAME'
		    } , {
		    	header    : "Field Description",
	    		width     : 15,
	    		sortable  : true,
	    		dataIndex : 'DESCRIPTION',
	    		editor	  : description
		    } , checkColumnInclude, checkColumnHidden ]
	    
	});
	   

	var ConfigUsers_grid = new Ext.grid.EditorGridPanel({
		store			: ConfigUsers_store,
		cm				: ConfigUsers_cm,
		columnLines	    : true,
		stripeRows		: true,
		autoScroll		: true,
		loadMask        : true,
		autoShow        : true, 
		autoFill        : true,
		nocache         : true,
		stateful        : true,
		animCollapse    : true,
		id			 	:'ConfigUsers_grid',
		ddGroup		   	:'gridDDactions',
		enableDragDrop 	: true, 
		viewConfig 		: {
			forceFit 		: true,
			scrollOffset 	: 0,
			emptyText		: lanMsgEmpty
		},
		plugins        	: [checkColumnHidden,checkColumnInclude],
		bbar			: new Ext.PagingToolbar({
			pageSize	: 50,
			store		: ConfigUsers_store,
			displayInfo	: true,
			displayMsg	: lanMsgDisplay,
			emptyMsg	: lanMsgEmpty2
		}),
		tbar 			: [ {
			text: lanSaveConfig,
			cls : 'x-btn-text-icon',
			icon : '/images/ok.png',
			tooltip  : 'Add drag and drop',
			handler: function(){
				saveConfigUsers_DragAndDrop(ConfigUsers_store);
			}
		}],
		selModel       : new Ext.grid.RowSelectionModel({singleSelect : true}),
		listeners      : {  //drag and drop
			"render": {
				scope: this,
				fn: function(grid) {
					var ddrow = new Ext.dd.DropTarget(grid.container, {
						ddGroup : 'gridDDactions',
						copy:false,
						notifyDrop : function(dd, e, data){
							var ds = grid.store;
							var sm = ConfigUsers_grid.getSelectionModel();
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
				}
			}
		}
	});	
	
	// back users
	BackToUsers = function(){
		  location.href = 'users_List';
	};
	
	backButton = new Ext.Action({
		text : _('ID_BACK'),
		iconCls: 'button_menu_ext ss_sprite ss_arrow_redo',
		handler: BackToUsers
	});
	  
 

	var configUsersPanel = new Ext.Panel({
		autoWidth    : true,
		height       : 550,
		layout       : 'fit',
		autoScroll	 : true,
		items        : [
		                ConfigUsers_grid
		]/*,
		tbar: ['',{xtype: 'tbfill'},backButton]*/
		
	});
	
	  //TABS PANEL
	
	var viewport = new Ext.Viewport({
		layout : 'fit',
		items  : [configUsersPanel]
	});

	/////////////////////////////Config Users //////////////////////////////////////////////////
	
	var idOption =  new Ext.form.TextField ({
		allowBlank : true,
		height     : 50,
		disabled   : false,
		anchor     : '100%'
    });
    
	var description =  new Ext.form.TextField ({
		allowBlank : true,
		height     : 50,
		disabled   : false,
		anchor     : '100%'
    });
   	 
		
	function saveConfigUsers_DragAndDrop(ConfigUsers_store)
	{
		var i  = 0;
		var arrayConfigListUsers = new Array ();
		var myJSON  = '';
			
		ConfigUsers_store.each(function(record)  
		{  
			var hiddenField  = 0;
			if(record.get('INCLUDE_OPTION') == true)
			{	
				var fieldName = record.get('ID_FIELD');
				var description = record.get('DESCRIPTION');
				var table = record.get('TABLE');
				if(record.get('HIDDEN_FIELD') == true)
					hiddenField = 1;	
				var item = {
					"value"        	: i,
					"fieldName"		: fieldName,
					"description"	: description,
					"hiddenField"	: hiddenField,
					"table"			: table
						
				};
				i++;
				arrayConfigListUsers.push(item);
			}
		});
			
		if(arrayConfigListUsers.length != 0){
			myJSON= Ext.util.JSON.encode(arrayConfigListUsers);
			//console.log(myJSON);
			saveDataConfigUsers(myJSON);
		}
		else
		{
			saveDataConfigUsers(myJSON);
		}
			
	} 
	
	function saveDataConfigUsers(arrayConfigListUsers)
	{  
		Ext.MessageBox.show({ msg: _('ID_PROCESSING'), wait:true,waitConfig: {interval:200} });
	      
		Ext.Ajax.request({
			url: '../fieldcontrol/SaveConfigUsers.php?method=dragdropListUsers',
	        params: {
			arrayConfigListUsers : arrayConfigListUsers
			},
	        success: function(r,o){
				Ext.MessageBox.hide();
				Ext.getCmp('ConfigUsers_grid').getStore().reload(); 
				Ext.MessageBox.show({                            
					msg : lanMsgOpe,
					buttons : Ext.MessageBox.OK,
					icon : Ext.MessageBox.INFO
                });
				
	         },
	        failure: function(){
	        	Ext.MessageBox.alert('Error',lanMsgOpeError);
	        	Ext.MessageBox.hide();
	        }
		});
		
	       
	}
	

	    
});
