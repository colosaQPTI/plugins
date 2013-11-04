
Ext.onReady(function(){      
	Ext.QuickTips.init();
	
	function viewDynaformsLog(data){
		
                var rowModel = gridDynaformsLog.getSelectionModel().getSelected();
		
                if (!rowModel) rowModel = data;
                
                 if(rowModel)
		 {                 
		   if (rowModel.data.HLOG_ACTION == "Modification" && rowModel.data.HLOG_CHILD_APP_UID != "0")
                       HLOG_APP_UID = rowModel.data.HLOG_CHILD_APP_UID;		   
                   else
                       HLOG_APP_UID = rowModel.data.HLOG_APP_UID;		   
		   
            ADAPTIVEHEIGHT = ADAPTIVEHEIGHT - 50;
                   urlData = "../convergenceList/DynaformsListener.php?actionType=view&appUid=" + HLOG_APP_UID + "&adaptiveHeight="+ADAPTIVEHEIGHT;	
                   
                   var TabPanel = new Ext.TabPanel({                  
                      id: 'tabPanelForms',                      
                      deferredRender:false,                   
                      defaults:{autoScroll: true},
                      defaultType:"iframepanel",
                      activeTab: 1,
                      enableTabScroll: true,        
                      items:[{
                        id: 'Forms',
                        title: 'History',                      
                        frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                        defaultSrc : urlData,
                        loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                        bodyStyle:{height: ADAPTIVEHEIGHT+'px'}
                        }
                      ],
                      listeners: {                      
                      afterrender: function(panel){                     
                        panel.hideTabStripItem(0);                      
                      }
                    }
                              
          });
		   var winViewInfo = new Ext.Window({
	            id:'winViewInfo',	            
	            closable: true,
	            closeAction : 'hide',
	            autoDestroy : true,
	            maximizable: false,
	            title: 'Case General Information:',               
	            modal: true,
	            loadMask : true,
	            items : [TabPanel]
	    	});
	    	winViewInfo.show();
	    	winViewInfo.maximize();
		 } 
		 else
		 { 
		   alert('Error');
		 }		
	}		    

	var storeDynaformsLog = new Ext.data.JsonStore({
	    url : 'actions/ajaxDynaformsLog.php?APP_UID='+APP_UID+'&NUM_DOSSIER='+NUM_DOSSIER+'&TABLE='+TABLE,
	    root : 'data',
	    totalProperty : 'total', // <--- total de registros a paginar
	    //remoteSort : true,
	    autoWidth : true,
	    fields : ['HLOG_APP_UID', 'HLOG_CHILD_APP_UID', 'HLOG_DATECREATED', 'HLOG_USER_UID', 'APP_NUMBER', 'USERCREATOR', 'HLOG_ACTION', 'HLOG_STATUS']
	 });
  	storeDynaformsLog.load();
	var pager = new Ext.PagingToolbar({
	    store : storeDynaformsLog, // <--grid and PagingToolbar using same store (required)
	    displayInfo : true,
	    autoHeight : true,
	    displayMsg : 'Accounts {0} - {1} of {2}',
	    emptyMsg : 'No Accounts to show',
	    pageSize : 20
	 });
	var gridDynaformsLog = new Ext.grid.GridPanel({
	    store : storeDynaformsLog,
	    cm : new Ext.grid.ColumnModel({
	      defaults : {
	        width : 20,
	        sortable : true
	      },
	      columns : [	      
	      {
	        header : "#",
	        width : 5,
                hidden : true,
	        sortable : true,
	        dataIndex : 'HLOG_APP_UID'
	      },{
	        header : "#2",
	        width : 5,
                hidden : true,
	        sortable : true,
	        dataIndex : 'HLOG_CHILD_APP_UID'
	      },{
	        header : "Action",
	        width : 20,
	        sortable : true,
	        dataIndex : 'HLOG_ACTION'
	      },{
	    	header : "Effectué par",
	        width : 20,
	        sortable : true,
	        dataIndex : 'USERCREATOR'
	      },{
	        header : "Date",
	        width : 20,
	        sortable : true,
	        dataIndex : 'HLOG_DATECREATED'
	      }, {
	        header : "Etat du dossier",
	        width : 10,
	        sortable : true,
	        dataIndex : 'HLOG_STATUS'
	      },
                {
                    xtype: 'actioncolumn',
                    width: 50,
                    items: [{
                        icon : '/images/cases_torevise.png',
                        tooltip: 'Voir cette version',
                        handler: function(grid, rowIndex, colIndex) {
                            viewDynaformsLog(storeDynaformsLog.getAt(rowIndex));
                        }
                    }]
                }]
	    }),
	    bbar : pager,	    
	    border : false,
	    loadMask : true,
	    stripeRows : true,
	    autoShow : true,
	    title : 'History List',
	    autoFill : true,
	    nocache : true,
	    autoWidth : true,
	    height : 800,
	    stateful : true,
	    animCollapse : true,
	    stateId : 'grid',
	    listeners : {
	      rowdblclick : viewDynaformsLog
	    },
	    viewConfig : {
	      forceFit : true,
	      scrollOffset : 0
	    }
	  });
	var viewport = new Ext.Viewport({
	    layout : 'fit',
	    items : [gridDynaformsLog]
  	});
	
});