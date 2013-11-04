
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
    
	var titleUpdate = 'Customize Labels';
	var messageUpdate = 'Data updated';
    var titleGrid = 'Customize Labels';
    var lanMsgDisplay = 'Inboxes {0} - {1} Of {2}';    
    var lanMsgNoDisplay= 'No Inboxes to show';
    if(language == 'fr')
	{
	    lanMsgDisplay = 'Inboxes {0} - {1} sur {2}';
	    lanMsgNoDisplay = 'Pas de Inboxes pour montrer';
	    titleUpdate = 'Customisez \u00C9tiquettes';
	    messageUpdate = 'Donn\u00E9es mises \u00E1 jour';
	    titleGrid = 'Customisez les \u00C9tiquettes';
	}
	
 
    
//Do Nothing Function
DoNothing = function(){};

//Open Edit Group Form
EditInboxWindow = function(){  
  var rowSelected = actionGrid.getSelectionModel().getSelected(); 
  editForm.getForm().reset();
  editForm.getForm().findField('nameLabel').setValue(rowSelected.data.NAME);
  editForm.getForm().findField('descriptioEN').setValue(rowSelected.data.DESCRIPTION_EN);
  editForm.getForm().findField('descriptioFR').setValue(rowSelected.data.DESCRIPTION_FR);
  win = new Ext.Window({
    autoHeight: true,
    width: 450,
    title: 'customize',
    closable: false,
    modal: true,
    autoDestroy : true,
    id: 'w',
    items: [editForm]
  });
  win.show();
  

};

//Close Popup Window
CloseWindow = function(){
    Ext.getCmp('w').hide();
};

editForm = new Ext.FormPanel({
	
    url   : 'ajaxCustomizeField?action=editField',
    frame : true,
    labelWidth : 125,
    items :[
        {
        	id:'nameLabel', 
        	xtype: 'textfield', 
        	fieldLabel: 'Name Label', 
        	name: 'name', 
        	width: 250, 
        	disabled: true
        } ,  {
        	id:'descriptioEN', 
        	fieldLabel: 'Description EN', 
        	xtype: 'textfield', 
        	name: 'name', 
        	width: 250 
        } ,  {
        	id:'descriptioFR', 
        	fieldLabel: 'Description FR', 
        	xtype: 'textfield', 
        	name: 'name', 
        	width: 250 
        }
    ],
    buttons: [
        {text : _('ID_SAVE') , handler:  saveEditInbox},
        {text : _('ID_CANCEL'), handler: CloseWindow}
    ]
});


 function saveEditInbox() {
	 rowSelected = actionGrid.getSelectionModel().getSelected();
    viewport.getEl().mask(_('ID_PROCESSING'));
    Ext.Ajax.request({
        url    : 'ajaxCustomizeField.php',
        method : 'POST',
        params : {
            action : 'editField',
            ID : rowSelected.data.ID,
            fieldName :  editForm.getForm().findField('nameLabel').getValue(),
            descriptionEN : editForm.getForm().findField('descriptioEN').getValue(),
            descriptionFR : editForm.getForm().findField('descriptioFR').getValue()
        },
        success: function(xhr,params) {
            DoSearch();
            editButton.disable();
            PMExt.notify(titleUpdate,messageUpdate);
            viewport.getEl().unmask();
            CloseWindow();
            editForm.getForm().reset(); //Set empty form to next use
        },
        failfure: function(xhr,params) {
            alert('Failure!\n'+xhr.responseText);
            viewport.getEl().unmask();
        }
    });
};



//Do Search Function
DoSearch = function(){
  actionGrid.store.load({params: {textFilter: searchText.getValue()}});
};

//Update Page Size Configuration
UpdatePageConfig = function(pageSize){
    Ext.Ajax.request({
        url    : 'ajaxCustomizeField',
        params : {'function':'listAction', size: pageSize}
    });
};
    
  Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
  Ext.QuickTips.init();

  editButton = new Ext.Action({
        text     : _('ID_EDIT'),
        iconCls  : 'button_menu_ext ss_sprite  ss_pencil',
        handler  : EditInboxWindow,
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
            },
            rowdeselect: function(sm){
            	editButton.disable();
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
        url       : 'ajaxCustomizeField?action=listField'
    }),
    reader        : new Ext.data.JsonReader( {
        root          : 'data',
        totalProperty : 'root',
        fields        : [
            {name : 'ID'},
            {name : 'NAME'},
            {name : 'DESCRIPTION_EN'},
            {name : 'DESCRIPTION_FR'}
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
            {header : 'Name Label', dataIndex: 'NAME', width:50, align: 'left', sortable: true, renderer: "Action Name"},
            {header : 'Description EN', dataIndex: 'DESCRIPTION_EN', width:70, align: 'left', renderer: "Description"},
            {header : 'Description FR', dataIndex: 'DESCRIPTION_FR', width:70, align: 'left', renderer: "Description"}
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
        title : titleGrid,
        store : store,
        cm    : cmodel,
        sm    : smodel,
        tbar  : [editButton],
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

