
//Keyboard Events
new Ext.KeyMap(document, [
    {
        key : Ext.EventObject.F5,
        fn  : function(keycode, e) {
        if (! e.ctrlKey) {
            if (Ext.isIE) {
                // IE6 doesn't allow cancellation of the F5 key, so trick it into
                // thinking some other key was pressed (backspace in this case)
                e.browserEvent.keyCode = 8;
            }
            e.stopEvent();
            document.location = document.location;
            } else {
                Ext.Msg.alert('Refresh', 'You clicked: CTRL-F5');
        }
    }
},
{
    key: Ext.EventObject.DELETE,
    fn: function(k,e){
        iGrid = Ext.getCmp('actionGrid');
        rowSelected = iGrid.getSelectionModel().getSelected();
    }
}
]);

var store;
var cmodel;
var actionGrid;
var viewport;
var smodel;
var newButton;
var editButton;
var deleteButton;
var contextMenu;
var comboStatusStore;
var pageSize;
var dateFormat;

Ext.onReady(function(){
    
    
    var lanMsgDisplay = 'Inboxes {0} - {1} Of {2}';    
    var lanMsgNoDisplay= 'No Inboxes to show';
    //var lanEditInbox ='EDIT INBOX';
    var lanActionInbox ='Actions inbox';    
    var lanActionEdit ='EDIT ACTION';
    
    var lanActionNew ='Create new action';
    var lanActionNew2 ='New action';    
    var lanActionName ='Action name';
    var lanActionDescription ='Action Description';
    var lanSelectPlug ='Select a plugin...';
    var lanSelectPlug2 ='Select plugin';    
    var lanSelectFunction ='Select a function...';
    var lanSelectFunction2 ='Select function';    
    var lanFunctionHas ='The function has no parameters';
    var lanSelectRow ="Select rows to affect";
    var lanSelectRowAf ="Select a row affected...";
    var lanParam ="Parameters Function";
    var lanSave = 'Save';
    var lanCancel = 'Cancel';
    var lanPleaseEnter="Please Enter a Name Action";
    var lanPleaseDes="Please Enter a Description Action";
    var lanPleaseSelPlug="Please select a plugin";
    var lanPleaseSelFunction="Please select a function";
    var lanActionSuc = "The Action was created succesfully!";
    var lanActionUpd="The action inbox was updated succesfully!";   
    var lanEditInbox2 ='Edit Inbox';    
    var lanCreateInbox='Create new inbox';
    var lanInboxName = 'Inbox Name';
    var lanInboxDescription = 'Inbox Description';   
    var lanNewInboxPlease ="Please enter a name Inbox";
    var lanNewDescriptionPlease ="Please Enter a Description Inbox";    
    var lanNewInbox="New Inbox";
    var lanAlreadyAction="Already exists with that name Action";
  
   
    var lanActionRem ="Do you want to remove this Action?";
    var lanActionRemSuc="The action was removed succesfully!";
    
    
    
    
    if(language == 'fr')
	{
	    lanMsgDisplay = 'Inboxes {0} - {1} sur {2}';
	    lanMsgNoDisplay = 'Pas de Inboxes pour montrer';
	    lanEditInbox = 'Modifier Inbox';
	    lanCreateInbox = 'Créer un nouveau Inbox';
	    lanInboxName= 'Inbox Nom';
	    lanInboxDescription = 'Description de Inbox';
	    lanNewInboxPlease = "S'il vous pla\u00EEt entrer un nom Inbox";
	    lanNewDescriptionPlease="S'il vous pla\u00EEt entrez une Description Inbox";
	    
	    lanNewInbox="Nouvelle Inbox";
	    lanAlreadyAction="Existe d\u00E9j\u00E0 avec ce nom action";
	   
	    lanEditInbox2 ="Modifier Inbox";
	   
	    lanActionInbox ='Actions inbox';
	    lanActionEdit ='EDITER ACTION';
	    lanActionNew ='Cr\u00E9er une nouvelle action';
	    lanActionName ='Action nom';
	    lanActionDescription ="Description de L'action";
        lanSelectPlug ='S\u00E9lectionner un plugin...';
	    lanSelectFunction ='S\u00E9lectionner une fonction...';
	    lanSelectFunction2 ='S\u00E9lectionner fonction';
	    
	    lanFunctionHas ="La fonction n'a pas de param\u00E9tres";
	    lanSelectRow ="S\u00E9lectionnez les lignes d'affecter";
	    lanSelectRowAf ="S\u00E9lectionnez une ligne affect\u00E9e ...";
	    lanParam ="Param\u00E8tres Fonction";
	    lanSelectPlug2 ='S\u00E9lectionner plugin';
	    lanPleaseEnter="S'il vous pla\u00EEt Entrez un nom d'action";
	    lanPleaseDes="S'il vous pla\u00EEt Entrez une description action";
	    lanPleaseSelPlug="S'il vous pla\u00EEt choisir un plugin";
	    lanPleaseSelFunction="S'il vous pla\u00EEt s\u00E9lectionner une fonction";
	    lanActionSuc = "L'action a \u00E9t\u00E9 cr\u00E9\u00E9 avec succ\u00E8s!";
	    lanActionNew2 ='Nouvelle action';
	    lanActionUpd="La bo\u00EEte de r\u00E9ception d'action a \u00E9t\u00E9 mis \u00E0 jour avec succ\u00E8s!";
	    lanActionRem ="Voulez-vous supprimer cette action?";
	    lanActionRemSuc="L'action a \u00E9t\u00E9 retir\u00E9 avec succ\u00E8s!";
	    
	    lanSave = 'Sauver';
            lanCancel = 'Annuler';
    
    
    
    
	}
	
    
    /////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////
    
    
//Do Nothing Function
DoNothing = function(){};

//Open Edit Group Form
EditInboxWindow = function(){  
  var rowSelected = actionGrid.getSelectionModel().getSelected();
  //console.log(rowSelected);
  editForm.getForm().reset();
  editForm.getForm().findField('actionnamefield1').setValue(rowSelected.data.NAME);
  editForm.getForm().findField('actiondescfield1').setValue(rowSelected.data.DESCRIPTION);
  editForm.getForm().findField('actionPmfunction').setValue(rowSelected.data.PM_FUNCTION);
  editForm.getForm().findField('fnparametersfield1').setValue(rowSelected.data.PARAMETERS_FUNCTION);
  editForm.getForm().findField('idEditPlugins').setValue(rowSelected.data.NAME_PLUGIN);
  editForm.getForm().findField('idtypeActionEdit').setValue(rowSelected.data.ROWS_AFFECT_ID);
  var idPlugin = rowSelected.data.NAME_PLUGIN;
	functionsStore.load({
		params : {
			'idPlugin' :idPlugin
		}
	});
  win = new Ext.Window({
    autoHeight: true,
    width: 450,
    title: lanActionEdit,
    closable: false,
    modal: true,
    autoDestroy : true,
    id: 'w',
    items: [editForm]
  });
  win.show();
  

};




//Open New User Form
NewInboxAction = function(){
    newForm.getForm().reset();
    newForm.getForm().items.items[0].focus('',500);
    wInbox = new Ext.Window({
        autoHeight : true,
        width	   : 450,
        title      : lanActionNew,
        closable   : false,
        autodestroy: true,
        modal      : true,
        id         : 'w',
        items      : [newForm]
    });
    
    wInbox.show();
};

//Close Popup Window
CloseWindow = function(){
    Ext.getCmp('w').hide();
    functionsStore.removeAll();
};
functionsStore = new Ext.data.Store({
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
	//functionsStore.load();

pluginStore = new Ext.data.Store({
	proxy : new Ext.data.HttpProxy({url: 'listFunctions.php?plugin=0'}),
	reader : new Ext.data.JsonReader({
		root   : 'data',
		fields : [
			{name : 'ID'},
			{name : 'NAME'}
		]
	})
});
pluginStore.load();

var typeActionStore =	[
            		['',	' -- SELECT ALL -- '],
            		['one','Affect One row'],
                    ['oneMore','Affect One and More rows'],
            		['multiple','Affect Multiple rows'],
                    ['none','Affect None row']
];

editForm = new Ext.FormPanel({
	
    url   : 'actionInbox_Ajax?action=editAction',
    frame : true,
    labelWidth : 150,
    items :[
        {
        	id:'actionnamefield1', 
        	xtype: 'textfield', 
        	fieldLabel: lanActionName, 
        	name: 'name', 
        	width: 250, 
        	//allowBlank: false,
        	disabled: true
        } ,  {
        	id:'actiondescfield1', 
        	xtype: 'textfield', 
        	fieldLabel: lanActionDescription, 
        	name: 'name', 
        	autoCreate: {tag: 'textarea', type: 'text', size: '20', style: "width:300px;height:60px;", autocomplete: "off", maxlength: '90'},
        	width: 250 
        	//allowBlank: false
        }, {
            xtype      : 'combo',
            id         : 'idEditPlugins',
            name       : 'plugins',
            fieldLabel : '<span style="color: red">*</span>'+lanSelectPlug2,
            hiddenName : 'plugins',
            typeAhead  : true,
            mode       : 'local',
            width	   : 250, 
            store      : pluginStore,
            listeners  : {
        		select : function(combo, record) {
						store.setBaseParam('idEditPlugins', combo.getRawValue());
						var idPlugin = combo.getRawValue();
						
						functionsStore.load({
							params : {
								'idPlugin' :combo.getRawValue()
							}
						})
				} 
				
            },
            displayField  :'NAME',
            valueField    :'ID',
            //allowBlank    : false,
            triggerAction : 'all',
            emptyText     : 'Select a Plugin...',
            selectOnFocus :true
           
         } ,
         {
        	id:'actionPmfunction', 
        	xtype: 'combo', 
        	fieldLabel: '<span style="color: red">*</span>'+lanSelectFunction2, 
        	name: 'pmfunction', 
        	mode  : 'local',
            store : functionsStore,
        	width : 250, 
        	allowBlank: false,
        	displayField  :'NAME',
            valueField    :'ID',
            //allowBlank    : false,
            triggerAction : 'all',
            emptyText     : 'Select a Function...',
            selectOnFocus :true,
            listeners  : {
               	select: function(combo, record, index) {
		        	parameters = record.get('PARAMETERS_FUNCTION');
					if(parameters.length)
					{
						parameters = parameters.split(" ");
						Ext.getCmp('fnparametersfield1').setValue(parameters);
					}
					else
						Ext.getCmp('fnparametersfield1').setValue('The function has no parameters');
		       }
        	}
        } , {
            xtype      		: 'combo',
            id         		: 'idtypeActionEdit',
            name       		: 'typeActionEdit',
            fieldLabel 		:  lanSelectRow,
            typeAhead  		: true,
            width	   		: 250, 
            mode       		: 'local',
            store      		: typeActionStore,
            displayField  	:'NAME',
            valueField    	:'ID',
            allowBlank    	: false,
            triggerAction 	: 'all',
            emptyText     	: lanSelectRowAf
           
         } , {                                                                                              
            xtype: 'textarea',
            id:'fnparametersfield1', 
        	  fieldLabel: lanParam, 
        	  name: 'namedesc', 
        	  width: 250, 
        	  //allowBlank: false,
        	  disabled: true,
            hidden: false
        }
    ],
    buttons: [
        {text : lanSave , handler:  saveEditInbox},
        {text : lanCancel, handler: CloseWindow}
    ]
});


newForm = new Ext.FormPanel({
    url   : 'actionInbox_Ajax?action=newAction',
    frame : true,
    labelWidth : 150,
    items :[
            {
            	id:'actionnamefield', 
            	xtype: 'textfield', 
            	fieldLabel: '<span style="color: red">*</span>'+lanActionName, 
            	name: 'name', 
            	width: 250, 
            	maxLength: 30,
            	autoCreate: {tag: 'input', type: 'text', size: '20', autocomplete: 'off', maxlength: '25'},
            	enableKeyEvents: true, 
            	listeners: {
                    keyup: function(val, e){
                        result = val.getValue();
                        text = Ext.util.Format.uppercase(result);
                        text =  text.replace(/(^\s*)|(\s*$)/g,""); // delete spaces in text
                        val.setValue(text);
                    } , 
                    keypress: function(val, e){
                        result = val.getValue();
                        text = Ext.util.Format.uppercase(result);
                        text =  text.replace(/(^\s*)|(\s*$)/g,""); // delete spaces in text
                        val.setValue(text);
                    }
                  }
            	//allowBlank: false   
             } , {
            	id:'actiondescfield', 
            	xtype: 'textarea', 
            	fieldLabel: '<span style="color: red">*</span>'+lanActionDescription, 
            	name: 'namedesc', 
            	width: 250, 
            	maxLength: 150,
            	autoCreate: {tag: 'textarea', type: 'text', style: "width:300px;height:60px;", autocomplete: "off", maxlength: '90'}
            	//allowBlank: false
            } , {
                xtype      : 'combo',
                id         : 'idPlugins',
                name       : 'plugins',
                fieldLabel : '<span style="color: red">*</span>'+lanSelectPlug2,
                hiddenName : 'plugins',
                typeAhead  : true,
                width 	   : 250, 
                mode       : 'local',
                store      : pluginStore,
                listeners  : {
            		select : function(combo, record) {
            				store.setBaseParam('idPlugins', combo.getRawValue());
							var idPlugin = combo.getRawValue();
							functionsStore.load({
								params : {
									'idPlugin' :combo.getRawValue()
								}
							})
					} 
					
                } ,
                displayField  :'NAME',
                valueField    :'ID',
                allowBlank    : false,
                triggerAction : 'all',
                emptyText     : 'Select a Plugin...',
                selectOnFocus :true
               
             } , {
                xtype      : 'combo',
                id         : 'idfunctions',
                name       : 'pmfunction',
                fieldLabel : '<span style="color: red">*</span>'+lanSelectFunction2,
                hiddenName : 'pmfunction',
                width 	   : 250, 
                typeAhead  : true,
                mode       : 'local',
                store      : functionsStore,
                listeners  : {
                    beforerender: function(func){
            		},
            		select: function(combo, record, index) {
            			parameters = record.get('PARAMETERS_FUNCTION');
            			if(parameters.length)
            			{
            				parameters = parameters.split(" ");
            				Ext.getCmp('fnparametersfield').setValue(parameters);
            			}
            			else
            				Ext.getCmp('fnparametersfield').setValue('The function has no parameters');
            				
                    } 
                },
                displayField  :'NAME',
                valueField    :'ID',
                allowBlank    : false,
                triggerAction : 'all',
                emptyText     : 'Select a Function...',
                selectOnFocus :true
               
              } , {
                  xtype      : 'combo',
                  id         : 'idtypeAction',
                  name       : 'typeAction',
                  fieldLabel : lanSelectRow,
                  hiddenName : 'typeAction',
                  typeAhead  : true,
                  width      : 250, 
                  mode       : 'local',
                  store      : typeActionStore,
                  displayField  :'NAME',
                  valueField    :'ID',
                  allowBlank    : false,
                  triggerAction : 'all',
                  emptyText     : lanSelectRowAf
                 
                } , {                                                                                              
                  xtype: 'textarea',
                  id:'fnparametersfield', 
              	  fieldLabel: lanParam, 
              	  name: 'namedesc', 
              	  width: 250, 
              	  allowBlank: false,
              	  disabled: true,
                  hidden: false
              }
    ],
    buttons: [
        {text : lanSave, handler: saveNewAction},
        {text : lanCancel, handler: CloseWindow}
    ]
});

function saveNewAction() {
	
    var action = newForm.getForm().findField('actionnamefield').getValue();
    var description = newForm.getForm().findField('actiondescfield').getValue();
    var pmfunction = newForm.getForm().findField('idfunctions').getValue();
    var parametersFunction = newForm.getForm().findField('fnparametersfield').getValue();
    var pluginName = newForm.getForm().findField('idPlugins').getValue();
    var rowsAffect = newForm.getForm().findField('idtypeAction').getValue();
    if(!action.length)
    {
    	Ext.Msg.alert(_('ID_ERROR'),lanPleaseEnter);
    	return 0;
    }	
    if(!description.length)
    {
    	Ext.Msg.alert(_('ID_ERROR'),lanPleaseDes);
    	return 0;
    }
    
    if(!pluginName)
    {
    	Ext.Msg.alert(_('ID_ERROR'),lanPleaseSelPlug);
    	return 0;
    }	
    if(!pmfunction)
    {
    	Ext.Msg.alert(_('ID_ERROR'),lanPleaseSelFunction);
    	return 0;
    }	
    Ext.Ajax.request({
        url    : 'actionInbox_Ajax.php',
        method : 'POST',
        params : {
    		operation : 'saveNewAction',
            action  : action,
            description   : description,
            pmFunction : pmfunction,
            parametersFunction : parametersFunction,
            pluginName : pluginName,
            rowsAffect : rowsAffect
        },
        success: function(xhr,params) {
        	viewport.getEl().mask(_('ID_PROCESSING'));
        	if (xhr.responseText == 'true'){
        		
        		message = '';
        		DoSearch();
                deleteButton.disable(); //Disable Delete Button
                editButton.disable();
                PMExt.notify("New Action",lanActionSuc);
                viewport.getEl().unmask();
                functionsStore.removeAll();
                CloseWindow();
			}
			else{
				message = xhr.responseText;
				viewport.getEl().unmask();
				newForm.getForm().findField('actionnamefield').setValue("");
				Ext.Msg.alert(
						_('ID_ERROR'),
						lanAlreadyAction+' : '+action);
			}
        },
        failfure: function(xhr,params) {
        	alert('Failure!\n'+xhr.responseText);
            viewport.getEl().unmask();
        }
    });
}

 function saveEditInbox() {
	 rowSelected = actionGrid.getSelectionModel().getSelected();
    viewport.getEl().mask(_('ID_PROCESSING'));
    Ext.Ajax.request({
        url    : 'actionInbox_Ajax.php',
        method : 'POST',
        params : {
            operation : 'saveEditAction',
            ID : rowSelected.data.ID,
            actionName :  editForm.getForm().findField('actionnamefield1').getValue(),
            actionDescription : editForm.getForm().findField('actiondescfield1').getValue(),
            pmFunction : editForm.getForm().findField('actionPmfunction').getValue(),
            parametersFunction : editForm.getForm().findField('fnparametersfield1').getValue(),
            rowsAffect : editForm.getForm().findField('idtypeActionEdit').getValue()
        },
        success: function(xhr,params) {
            DoSearch();
            deleteButton.disable(); //Disable Delete Button
            editButton.disable();
            PMExt.notify("Action Inbox",lanActionUpd);
            viewport.getEl().unmask();
            CloseWindow();
            editForm.getForm().reset(); //Set empty form to next use
            newForm.getForm().reset(); //Set empty form to next use
        },
        failfure: function(xhr,params) {
            alert('Failure!\n'+xhr.responseText);
            viewport.getEl().unmask();
        }
    });
};

//Delete User Action
DeleteInboxAction = function(){
    Ext.Msg.confirm(_('ID_CONFIRM'), lanActionRem,
    function(btn, text){
        if (btn=="yes"){
        rowSelected = actionGrid.getSelectionModel().getSelected();
        viewport.getEl().mask(_('ID_PROCESSING'));
        Ext.Ajax.request({
            url     : 'actionInbox_Ajax.php',
            params  : {operation: 'deleteAction', ID: rowSelected.data.ID},
            success : function(r,o){
                viewport.getEl().unmask();
                DoSearch();
                deleteButton.disable(); //Disable Delete Button
                editButton.disable();
                PMExt.notify("Delete Action", lanActionRemSuc);
            },
                failure : function(){
                viewport.getEl().unmask();
            }
        });

        }
        }
    );
};

//Do Search Function
DoSearch = function(){
  actionGrid.store.load({params: {textFilter: searchText.getValue()}});
};

//Update Page Size Configuration
UpdatePageConfig = function(pageSize){
    Ext.Ajax.request({
        url    : 'actionInbox_Ajax',
        params : {'function':'listAction', size: pageSize}
    });
};

    
    
    
    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    
    
    
    
    
    
    
    
  Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
  Ext.QuickTips.init();

    newButton = new Ext.Action({
        text      : _('ID_NEW'),
        iconCls   : 'button_menu_ext ss_sprite  ss_add',
        handler   : NewInboxAction
    });
    
    editButton = new Ext.Action({
        text     : _('ID_EDIT'),
        iconCls  : 'button_menu_ext ss_sprite  ss_pencil',
        handler  : EditInboxWindow,
        disabled : true
    });

    deleteButton = new Ext.Action({
        text     : _('ID_DELETE'),
        iconCls  : 'button_menu_ext ss_sprite  ss_delete',
        handler  : DeleteInboxAction,
        disabled : true
    });

    searchText = new Ext.form.TextField ({
        id         : 'searchTxt',
        ctCls      :'pm_search_text_field',
        allowBlank : true,
        width      : 100,
        emptyText  : _('ID_ENTER_SEARCH_TERM'),//'enter search term',
        listeners  : {
            specialkey: function(f,e){
                if (e.getKey() == e.ENTER) {
                    DoSearch();
                }
            },
            focus: function(f,e) {
                var row = actionGrid.getSelectionModel().getSelected();
                actionGrid.getSelectionModel().deselectRow(actionGrid.getStore().indexOf(row));
            }
        }
    });

    smodel = new Ext.grid.RowSelectionModel({
        singleSelect : true,
        listeners    :{
            rowselect: function(sm){
    			editButton.enable();
                deleteButton.enable();
            },
            rowdeselect: function(sm){
            	editButton.disable();
                deleteButton.disable();
            }
        }
    });
    var stepsFields = Ext.data.Record.create([{
        name : 'ID',
        type : 'integer'
    },{
        name : 'INBOX',
        type : 'string'
    },{
        name : 'DESCRIPTION',
        type : 'string'
    }]);

    store = new Ext.data.GroupingStore( {
    remoteSort  : true,
    sortInfo    : stepsFields,
    groupField  :'',
    proxy       : new Ext.data.HttpProxy({
        url       : 'actionInbox_Ajax?action=listAction'
    }),
    reader        : new Ext.data.JsonReader( {
        root          : 'data',
        totalProperty : 'root',
        fields        : [
            {name : 'ID'},
            {name : 'NAME'},
            {name : 'DESCRIPTION'},
            {name : 'PM_FUNCTION'},
            {name : 'PARAMETERS_FUNCTION'},
            {name : 'NAME_PLUGIN'},
            {name : 'ROWS_AFFECT'},
            {name : 'ROWS_AFFECT_ID'}
        ]
    })
    });


    storePageSize = new Ext.data.SimpleStore({
        fields   : ['size'],
        data     : [['20'],['30'],['40'],['50'],['100']],
        autoLoad : true
    });

    comboPageSize = new Ext.form.ComboBox({
    typeAhead     : false,
    mode          : 'local',
    triggerAction : 'all',
    store         : storePageSize,
    valueField    : 'size',
    displayField  : 'size',
    width         : 50,
    editable      : false,
    listeners     :{
        select: function(c,d,i){
            UpdatePageConfig(d.data['size']);
            bbarpaging.pageSize = parseInt(d.data['size']);
            bbarpaging.moveFirst();
        }
    }
    });

  comboPageSize.setValue(pageSize);

    bbarpaging = new Ext.PagingToolbar({
        store       : store, // <--grid and PagingToolbar using same store (required)
        displayInfo : true,
        autoHeight  : true,
        displayMsg  : lanMsgDisplay,
        emptyMsg    : lanMsgNoDisplay,
        pageSize    : 50
    }); 
    
    cmodel = new Ext.grid.ColumnModel({
        defaults : {
            width: 50
        },
        columns : [
            {id     : 'ID', dataIndex: 'ID', hidden: true, hideable: false},
            {header : 'Actions Name', dataIndex: 'NAME', width:50, align: 'left', sortable: true, renderer: "Action Name"},
            {header : 'Description', dataIndex: 'DESCRIPTION', width:70, align: 'left', renderer: "Description"},
            {header : 'Pm Function', dataIndex: 'PM_FUNCTION', width:70, align: 'left', renderer: "PmFunctions"},
            {header : 'Parameters' , dataIndex: 'PARAMETERS_FUNCTION',width:50, align : 'left', renderer: "Parameters"},
            {header : 'Plugin' , dataIndex: 'NAME_PLUGIN',width:50, align : 'left', renderer: "Plugin"},
            {header : 'Rows Affect', dataIndex: 'ROWS_AFFECT',width:50, align : 'left' , renderer: "Rows Affect"}
        ]
    });

    actionGrid = new Ext.grid.GridPanel({
        region             : 'center',
        layout             : 'fit',
        id                 : 'actionGrid',
        height             : 100,
        autoWidth          : true,
        stateful           : true,
        stateId            : 'grid',
        enableColumnResize : true,
        enableHdMenu       : true,
        frame              : false,
        columnLines        : false,
        viewConfig         : {
            forceFit:true
        },
        title : lanActionInbox,
        store : store,
        cm    : cmodel,
        sm    : smodel,
        tbar  : [newButton, '-', editButton ,'-', deleteButton],
        bbar  : bbarpaging,
        listeners : {
      		rowdblclick: EditInboxWindow
    },
        view  : new Ext.grid.GroupingView({
            forceFit     : true,
            groupTextTpl : '{text}'
        })
    });

    actionGrid.on('rowcontextmenu',
        function (grid, rowIndex, evt) {
            var sm = grid.getSelectionModel();
            sm.selectRow(rowIndex, sm.isSelected(rowIndex));
        },
        this
    );

    actionGrid.on('contextmenu',
        function (evt) {
            evt.preventDefault();
        },
        this
    );

    actionGrid.store.load();

    viewport = new Ext.Viewport({
        layout     : 'fit',
        autoScroll : false,
        items      : [actionGrid]
    });
});

