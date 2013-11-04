
function getDocHeight(element) {
    if (!element) 
        var D = document;
    else
        var D = document.getElementById(element);
    
    return Math.max(
        Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
        Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
        Math.max(D.body.clientHeight, D.documentElement.clientHeight)
    );
}

function openOutputsDocuments(appUid, delIndex, action)
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

function redirect(idInbox){
    var requestFile = 'listBeneficiaireNewTab.php?idInbox=' + idInbox; 
    location.href = requestFile;
}


function windowTabs(idField,urlData,appNumber)
{               
    var adaptiveHeight = getDocHeight() - 50;
        window.swFrame= ''; 
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: false,
            title: 'Liste des formulaires',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelForms',
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,        
                    items:[{
                      id: 'Forms',
                      title: 'Form List (Case#: '+appNumber+')',
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: adaptiveHeight+'px'}
                      //width:screenWidth

                      }
                    ],
                    listeners: {
                        tabchange: function(panel,newTab){
                            panel.ownerCt.doLayout();
                            var activeTab = panel.getActiveTab();                           
                            if(activeTab.id == "iframe-DynaForms" && window.swFrame != '')
                            {  
                                newTab.doAutoLoad();
                                
                            }
                            if(activeTab.id == "iframe-Explication Statut" && window.swFrame != '')
                            {  
                                newTab.doAutoLoad();
                               
                            }
                        },
                      afterrender: function(panel){                     
                        panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){  
            urlData = "../convergenceList/controlUserCases.php";      
            Ext.Ajax.request({
                url : urlData,
                params : {
                    appUid  : idField
                }
            });    
            Ext.getCmp('gridNewTab').store.reload(); 
        });
}

function viewForms(appUid,accessComment){
    if (!accessComment)
        accessComment = 0;
    
    var adaptiveHeight = getDocHeight() - 50;
    urlData = "../convergenceList/DynaformsListener.php?actionType=view&appUid=" + appUid + "&adaptiveHeight="+adaptiveHeight +"&accessComment="+accessComment;             
    appNumb = '2';
    windowTabs(appUid,urlData,appNumb);
}

/*function editForms(appUid,accessComment){ 
    if (!accessComment)
        accessComment = 0;
    
    var adaptiveHeight = getDocHeight() - 50;
    urlData = "../convergenceList/DynaformsListener.php?actionType=edit&appUid=" + appUid + "&adaptiveHeight="+adaptiveHeight +"&accessComment="+accessComment;             
    appNumb = '2';
    windowTabs(appUid,urlData,appNumb);
}*/

function editFormsWithTag(appUid,tag){  
    var adaptiveHeight = getDocHeight() - 50;
    urlData = "../convergenceList/actions/editformwithTag.php?actionType=edit&tag=" + tag +"&appUid=" + appUid + "&adaptiveHeight="+adaptiveHeight;                 
    appNumb = '2';
    windowTabs(appUid,urlData,appNumb);
}

function classerNPAI(annuleFlagg, callback) {

    if (!annuleFlagg) { annuleFlagg = 0; } 
    if (!callback) {
      callback = '';
    }

    idField = myApp.addTab_inside();                    
    urlData = "../convergenceList/actions/npaiFlag.php";
    
    test = Ext.MessageBox.show({
        msg: 'Mise à jour des données, veuillez patienter...',
        progressText: 'En cours...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
        });
    Ext.Ajax.request({
           url : urlData,
           params : {
            array  : idField,
            todo: annuleFlagg,
            callback: callback
           },
           success: function (result, request) {
             var response = Ext.util.JSON.decode(result.responseText);
             if (response.success) {
                test.hide();
                        
                         Ext.MessageBox.show({
                    title: 'Résultat du traitement',
                            msg : response.messageinfo,
                            width : 500,
                            fn : function() {Ext.getCmp('gridNewTab').store.reload();},
                            icon: Ext.MessageBox.INFO
                        });
                        
              
             }
             else {
                test.hide();
               PMExt.warning(_('ID_WARNING'), response.message);
             }
             //Ext.getCmp('gridNewTab').store.reload();             
           },
           failure: function (result, request) {
            test.hide();
            Ext.getCmp('gridNewTab').store.reload();                
           }
         });
}
function exportInbox() {

    IdInbox = myApp.getIdInbox();    
    idField = myApp.addTab_inside();
    sFieldName = Ext.getCmp('_fieldName').getValue();
    if(sFieldName == 'ALL') {
        sFieldValue = Ext.getCmp('_fieldInputGeneral').getValue();
    }else{
        sFieldValue = Ext.getCmp('_fieldInputSpecific').getValue();
    }
    
    urlData = "../convergenceList/actions/ActionNewExportInbox.php";
    
    post(urlData, {array: idField, sFieldValue: sFieldValue, sFieldName: sFieldName, IdInbox: IdInbox});
}

function exportInboxNpai(npaiOrAdr, fileType, callback) {

    IdInbox = myApp.getIdInbox();   
    if (!npaiOrAdr)
        npaiOrAdr = 'npai';
    if (!fileType)
        fileType = 'csv';
    if (!callback)
        callback = '';
    idField = myApp.addTab_inside();
    sFieldName = Ext.getCmp('_fieldName').getValue();
    if (sFieldName == 'ALL') {
        sFieldValue = Ext.getCmp('_fieldInputGeneral').getValue();
    } else {
        sFieldValue = Ext.getCmp('_fieldInputSpecific').getValue();
    }
    urlData = "../convergenceList/actions/exportInboxNpai.php";
    post(urlData, {array: idField, sFieldValue: sFieldValue, sFieldName: sFieldName, type: npaiOrAdr, ext: fileType, IdInbox: IdInbox, callback: callback});
}

function explicationStatut(appUid, callback) {

    if (!callback)
        callback = '';
    idField = myApp.addTab_inside();                    
    urlData = "../convergenceList/actions/explicationStatut.php";
    
    test = Ext.MessageBox.show({
              msg : 'Chargement, Veuillez patienter ...',
              progressText : 'Chargement ...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
        });
    Ext.Ajax.request({
           url : urlData,
           params : {
             array: idField,
             callback: callback,
             app_uid: appUid
           },
           success: function (result, request) {
             var response = Ext.util.JSON.decode(result.responseText);
             if (response.success) {
                test.hide();
                        
                         Ext.MessageBox.show({
                            title : 'Explication du statut - Dossier n°'+response.num_dossier,
                            msg : response.messageinfo,
                            width : 400,                            
                            icon: Ext.MessageBox.INFO
                        });
                        
              
             }
             else {
                test.hide();
               PMExt.warning(_('ID_WARNING'), response.message);
             }
             //Ext.getCmp('gridNewTab').store.reload();             
           },
           failure: function (result, request) {
            test.hide();
            Ext.getCmp('gridNewTab').store.reload();                
           }
         });
}

function explicationStatutRmb(appUid){

    idField = myApp.addTab_inside();
    urlData = "../convergenceList/actions/explicationStatutRmb.php";

    test = Ext.MessageBox.show({
              msg : 'Chargement, Veuillez patienter ...',
              progressText : 'Chargement ...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
        });
    Ext.Ajax.request({
           url : urlData,
           params : {
             array  : idField
           },
           success: function (result, request) {
             var response = Ext.util.JSON.decode(result.responseText);
             if (response.success) {
                test.hide();

                         Ext.MessageBox.show({
                            title : 'Explication du statut - Dossier '+response.num_dossier,
                            msg : response.messageinfo,
                            width : 400,
                            icon: Ext.MessageBox.INFO
                        });


             }
             else {
                test.hide();
               PMExt.warning(_('ID_WARNING'), response.message);
             }
             //Ext.getCmp('gridNewTab').store.reload();
           },
           failure: function (result, request) {
            test.hide();
            Ext.getCmp('gridNewTab').store.reload();
           }
         });
}

function listeTitreDemande(num_dossier)
{               
        var adaptiveHeight = getDocHeight() - 50;
        
        var _dblColumns = new Array();
        var _dblFields = new Array();
        var storeTitre;
        var  _CLOSE = 'Fermer';
        var _WINTITLE_DOUBLON = "Liste des titres de ce dossier";
        
        column = {id : 'PRESTAID',header: 'Presta ID',width : 20,dataIndex : 'PRESTAID',hidden: true};        
        _dblColumns.push(column);
        _dblFields.push({name: 'PRESTAID'});
        
        column = {id : 'DMDAPPUID',header: 'demande ID',width : 20,dataIndex : 'DMDAPPUID',hidden: true};        
        _dblColumns.push(column);
        _dblFields.push({name: 'DMDAPPUID'});
        
        
        column = {id : 'UID',header: '#',width : 20,dataIndex : 'UID',hidden: true};        
        _dblColumns.push(column);
        _dblFields.push({name: 'APP_UID'});
        
        column = {id : 'THEMATIQUE_LABEL',header: '#',width : 20,dataIndex : 'THEMATIQUE_LABEL',hidden: true};        
        _dblColumns.push(column);
        _dblFields.push({name: 'THEMATIQUE_LABEL'});
        
        column = {
            id : 'NUM_DOSSIER',
            header: 'N&deg; Dossier',
            width : 80,
            dataIndex : 'NUM_DOSSIER',
            renderer  : function(value, meta, record) {
                var dmdID = record.data.DMDAPPUID;
                if (value != null)
                    return '<a href="#" onclick="viewForms(\''+dmdID+'\',1)">'+value+'</a>';
                else
                    return '';
            },
            hidden: false
        };        
        _dblColumns.push(column);
        _dblFields.push({name: 'NUM_DOSSIER'});
        
        column = {
            id : 'COMPLEMENT_CHQ',
            header: 'Type',
            width : 120,
            dataIndex : 'COMPLEMENT_CHQ',
            renderer  : function(value, meta, record) {
                var thema = record.data.THEMATIQUE_LABEL;
                if (value == '1')
                    return 'Ch&eacute;quier compl&eacute;mentaire';
                else
                    return thema;
            },
            hidden: false
        };        
        _dblColumns.push(column);
        _dblFields.push({name: 'COMPLEMENT_CHQ'});
        
        column = {
                id        : 'BCONSTANTE',
                header    : 'Num&eacute;ro ch&eacute;quier',
                width     : 120,//30,req
                dataIndex : 'BCONSTANTE'
                
        };  
        _dblColumns.push(column);  
        _dblFields.push({name: 'BCONSTANTE'});
        
        
        column = {
                id        : 'NUM_TITRE',
                header    : 'Num&eacute;ro du titre',
                width     : 120,//30,req
                dataIndex : 'NUM_TITRE'
                
        };  
        _dblColumns.push(column);  
        _dblFields.push({name: 'NUM_TITRE'});
        
        column = {
                id        : 'VN_TITRE',
                header    : 'Valeur',
                width     : 100,
                renderer  : function(value){
                    
                    return value+' &euro;';
                },
                dataIndex : 'VN_TITRE'
                
        };  
        _dblColumns.push(column);  
        _dblFields.push({name: 'VN_TITRE'});
        
        column = {
                id        : 'DEBUT_VALIDITE',
                header    : 'D&eacute;but de validit&eacute;',
                width     : 120,//30,req
                dataIndex : 'DEBUT_VALIDITE'
                
        };  
        _dblColumns.push(column);  
        _dblFields.push({name: 'DEBUT_VALIDITE'});
        
        column = {
                id        : 'FIN_VALIDITE',
                header    : 'Fin de validit&eacute;',
                width     : 120,//30,req
                dataIndex : 'FIN_VALIDITE'
                
        };  
        _dblColumns.push(column);  
        _dblFields.push({name: 'FIN_VALIDITE'});
        
        column = {
                id        : 'ANNULE',
                header    : 'Annul&eacute; ?',
                width     : 100,
                renderer: function(value) {
                    
                    if (value == 1)
                        value = 'Oui';
                    else
                        value = 'Non'
                    
                    return value;
                },
                dataIndex : 'ANNULE'
                
        };  
        _dblColumns.push(column);  
        _dblFields.push({name: 'ANNULE'});
        
        column = {
                id        : 'REPRODUCTION',
                header    : 'NB de reproduction',
                width     : 120,//30,req
                dataIndex : 'REPRODUCTION'
                
        };  
        _dblColumns.push(column);  
        _dblFields.push({name: 'REPRODUCTION'});
       
        column = {
                id        : 'PRESTATAIRE',
                header    : 'Prestataire',
                width     : 120,
                renderer  : function(value, meta, record) {
                    var prestaID = record.data.PRESTAID;
                    if (value != null)
                        return '<a href="#" onclick="viewForms(\''+prestaID+'\',1)">'+value+'</a>';
                    else
                        return '';
                },
                dataIndex : 'RAISONSOCIALE'
                
                
        };  
        _dblColumns.push(column);  
        _dblFields.push({name: 'RAISONSOCIALE'});
        
        column = {
                id        : 'VILLEPRESTA',
                header    : 'Ville prestataire',
                width     : 120,
                dataIndex : 'VILLE'
                
        };  
        _dblColumns.push(column);  
        _dblFields.push({name: 'VILLE'});

        column = {
        id: 'DATE_RMB',
                header    : 'Date Remboursement',
                width     : 120,
        dataIndex: 'DATE_RMB'
                
        };  
        _dblColumns.push(column);  
    _dblFields.push({name: 'DATE_RMB'});
        
        storeTitre = new Ext.data.JsonStore({
            url : '../convergenceList/actions/listeTitreDossier.php?num_dossier=' + num_dossier,
            root : 'data',
            totalProperty : 'total',
            autoWidth : true,
            
            fields : _dblFields
        }); 
        storeTitre.load();
        
        var cmTitre = new Ext.grid.ColumnModel({
            defaults : {
                width : 20,
                sortable : true
            },
            columns : _dblColumns
        });
        cmTitre.defaultSortable= true;  
        
        var gridTitre = new Ext.grid.GridPanel({
        store       : storeTitre,
        cm          : cmTitre,
        stripeRows  : true,
        columnLines : true,
        autoScroll  : true,
        autoWidth   : true,
        stateful    : true,
        id          : 'gridTitre',
        layout      : 'fit' ,
        viewConfig  : {
            forceFit : false,
            emptyText: ( _('ID_NO_RECORDS_FOUND') )
        },
        /*bbar: new Ext.PagingToolbar({
            pageSize: 300,
            store: storeTitre,
            displayInfo: true,
            displayMsg: _('ID_DISPLAY_ITEMS') + ' &nbsp; ',
            emptyMsg: _('ID_DISPLAY_EMPTY')
        }),*/
        listeners: {
            render: function(grid) {
                
            } ,

            afterrender: function(){
                       
            },

            cellcontextmenu : function(grid, rowIndex, cellIndex, event) {
      
                   
            } 
        },
        tbar : [{
            text: _CLOSE,
            iconCls: 'button_menu_ext ss_sprite ss_accept',
            handler: function() {
                winTitre.close();
            }   
        }]
        });     
        ///////////// end grid  
        winTitre = new Ext.Window({
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: true,
            id: 'winDoublon',
            title: _WINTITLE_DOUBLON,
            width : 900,
            height : 400,
            modal : true,
            closable:true,
            constrain:true,
            autoScroll:true,
            layout: 'fit',
            items: gridTitre
        });     
        
        winTitre.show();
        //winTitre.maximize();
        winTitre.toFront(); 
        
       
}
function listeTitreRmb(idRmb)
{
    var adaptiveHeight = getDocHeight() - 50;

    var _dblColumns = new Array();
    var _dblFields = new Array();
    var storeRmb;
    var _CLOSE = 'Fermer';
    var _WINTITLE_DOUBLON = "Liste des titres pour ce fichier de remboursement";

    column = {id: 'PRESTAID', header: 'Presta ID', width: 20, dataIndex: 'PRESTAID', hidden: true};
    _dblColumns.push(column);
    _dblFields.push({name: 'PRESTAID'});

    column = {id: 'DMDAPPUID', header: 'demande ID', width: 20, dataIndex: 'DMDAPPUID', hidden: true};
    _dblColumns.push(column);
    _dblFields.push({name: 'DMDAPPUID'});


    column = {id: 'UID', header: '#', width: 20, dataIndex: 'UID', hidden: true};
    _dblColumns.push(column);
    _dblFields.push({name: 'APP_UID'});

    column = {id: 'THEMATIQUE_LABEL', header: '#', width: 20, dataIndex: 'THEMATIQUE_LABEL', hidden: true};
    _dblColumns.push(column);
    _dblFields.push({name: 'THEMATIQUE_LABEL'});

    column = {
        id: 'NUM_DOSSIER',
        header: 'N&deg; Dossier',
        width: 80,
        dataIndex: 'NUM_DOSSIER',
        renderer: function(value, meta, record) {
            var dmdID = record.data.DMDAPPUID;
            if (value != null)
                return '<a href="#" onclick="viewForms(\'' + dmdID + '\',1)">' + value + '</a>';
            else
                return '';
        },
        hidden: false
    };
    _dblColumns.push(column);
    _dblFields.push({name: 'NUM_DOSSIER'});

    column = {
        id: 'COMPLEMENT_CHQ',
        header: 'Type',
        width: 250,
        dataIndex: 'COMPLEMENT_CHQ',
        renderer: function(value, meta, record) {
            var thema = record.data.THEMATIQUE_LABEL;
            if (value == '1')
                return 'Ch&eacute;quier compl&eacute;mentaire';
            else
                return thema;
        },
        hidden: false
    };
    _dblColumns.push(column);
    _dblFields.push({name: 'COMPLEMENT_CHQ'});

    column = {
        id: 'BCONSTANTE',
        header: 'Num&eacute;ro ch&eacute;quier',
        width: 120, //30,req
        dataIndex: 'BCONSTANTE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'BCONSTANTE'});


    column = {
        id: 'NUM_TITRE',
        header: 'Num&eacute;ro du titre',
        width: 120, //30,req
        dataIndex: 'NUM_TITRE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'NUM_TITRE'});

    column = {
        id: 'VN_TITRE',
        header: 'Valeur',
        width: 100,
        renderer: function(value) {

            return value + ' &euro;';
        },
        dataIndex: 'VN_TITRE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'VN_TITRE'});

    column = {
        id: 'TITLE',
        header: 'Etat du remboursement',
        width: 150, //30,req
        dataIndex: 'TITLE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'TITLE'});

    column = {
        id: 'DEBUT_VALIDITE',
        header: 'D&eacute;but de validit&eacute;',
        width: 120, //30,req
        dataIndex: 'DEBUT_VALIDITE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'DEBUT_VALIDITE'});

    column = {
        id: 'FIN_VALIDITE',
        header: 'Fin de validit&eacute;',
        width: 120, //30,req
        dataIndex: 'FIN_VALIDITE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'FIN_VALIDITE'});

    column = {
        id: 'ANNULE',
        header: 'Annul&eacute; ?',
        width: 100,
        renderer: function(value) {

            if (value == 1)
                value = 'Oui';
            else
                value = 'Non'

            return value;
        },
        dataIndex: 'ANNULE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'ANNULE'});

    column = {
        id: 'REPRODUCTION',
        header: 'NB de reproduction',
        width: 120, //30,req
        dataIndex: 'REPRODUCTION'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'REPRODUCTION'});

    column = {
        id: 'PRESTATAIRE',
        header: 'Prestataire',
        width: 120,
        renderer: function(value, meta, record) {
            var prestaID = record.data.PRESTAID;
            if (value != null)
                return '<a href="#" onclick="viewForms(\'' + prestaID + '\',1)">' + value + '</a>';
            else
                return '';
        },
        dataIndex: 'RAISONSOCIALE'


    };
    _dblColumns.push(column);
    _dblFields.push({name: 'RAISONSOCIALE'});

    column = {
        id: 'VILLEPRESTA',
        header: 'Ville prestataire',
        width: 120,
        dataIndex: 'VILLE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'VILLE'});

    column = {
        id: 'DATE_RMB',
        header: 'Date Remboursement',
        width: 120,
        dataIndex: 'DATE_RMB'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'DATE_RMB'});

    storeRmb = new Ext.data.JsonStore({
        url: '../convergenceList/actions/listeTitreRmb.php?idRmb=' + idRmb,
        root: 'data',
        totalProperty: 'total',
        autoWidth: true,
        fields: _dblFields
    });
    storeRmb.load();

    var cmRmb = new Ext.grid.ColumnModel({
        defaults: {
            width: 20,
            sortable: true
        },
        columns: _dblColumns
    });
    cmRmb.defaultSortable = true;

    var gridTitreRmb = new Ext.grid.GridPanel({
        store: storeRmb,
        cm: cmRmb,
        stripeRows: true,
        columnLines: true,
        autoScroll: true,
        autoWidth: true,
        stateful: true,
        id: 'gridTitreRmb',
        layout: 'fit',
        viewConfig: {
            forceFit: false,
            emptyText: (_('ID_NO_RECORDS_FOUND'))
        },
        listeners: {
            render: function(grid) {

            },
            afterrender: function() {

            },
            cellcontextmenu: function(grid, rowIndex, cellIndex, event) {


            }
        },
        tbar: [{
                text: _CLOSE,
                iconCls: 'button_menu_ext ss_sprite ss_accept',
                handler: function() {
                winTitreRmb.close();
                }
            }]
    });
    ///////////// end grid
    winTitreRmb = new Ext.Window({
        closeAction: 'hide',
        autoDestroy: true,
        maximizable: true,
        id: 'winDoublon',
        title: _WINTITLE_DOUBLON,
        width: 900,
        height: 400,
        modal: true,
        closable: true,
        constrain: true,
        autoScroll: true,
        layout: 'fit',
        items: gridTitreRmb
    });

    winTitreRmb.show();
    //winTitre.maximize();
    winTitreRmb.toFront();


}

function Forcerlademande(){

    idField = myApp.addTab_inside();                    
    urlData = "../convergenceList/actions/forceDemande.php?array=" + idField;
    Ext.MessageBox.show({
        msg: 'Mise à jour des données, veuillez patienter...',
        progressText: 'En cours...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
        });
    Ext.Ajax.request({
           url : urlData,
           params : {
             array  : idField               
           },
           success: function (result, request) {
             var response = Ext.util.JSON.decode(result.responseText);
             if (response.success) {
                Ext.MessageBox.hide();
               alert(response.messageinfo);
             }
             else {
                Ext.MessageBox.hide();
               PMExt.warning(_('ID_WARNING'), response.message);
             }
             Ext.getCmp('gridNewTab').store.reload();               
           },
           failure: function (result, request) {
            Ext.MessageBox.hide();
            Ext.getCmp('gridNewTab').store.reload();                
           }
         });
}

function cancelVoucher() {
    changed = changeEtatStatutConfirm('Etes-vous sur de vouloir annuler ce bon de réduction ?', 11, function(){
        idField = myApp.addTab_inside();
        urlData = "../convergenceList/actions/cancelVoucher.php?array=" + idField + "&statut=" + statut;
        Ext.MessageBox.show({
            msg: 'Mise à jour des données, veuillez patienter...',
            progressText: 'En cours...',
            width: 300,
            wait: true,
            waitConfig: {
                interval: 200
            }
        });
        Ext.Ajax.request({
            url: urlData,
            params: {
                array: idField,
                statut: statut
            },
            success: function(result, request) {
                var response = Ext.util.JSON.decode(result.responseText);
                if (response.success) {
                    Ext.MessageBox.hide();
                    alert(response.messageinfo);
                    Ext.MessageBox.show({
                        msg: response.messageinfo,
                        progressText: 'En cours...',
                        width: 300,
                        wait: true,
                        waitConfig: {
                            interval: 200
                        }
                    });
                }
                else {
                    Ext.MessageBox.hide();
                    PMExt.warning(_('ID_WARNING'), response.message);
                }
                Ext.getCmp('gridNewTab').store.reload();
            },
            failure: function(result, request) {
                Ext.MessageBox.hide();
                Ext.getCmp('gridNewTab').store.reload();
            }
        });
    });
}

function changeEtatStatutConfirm(message, statut, callback) {
    Ext.MessageBox.show({
        title:'Confirmation',
        msg: message,
        width:300,
        buttons:Ext.MessageBox.YESNO,
        fn:function(btn) {
            if(btn == 'yes') {
                changeEtatStatut(statut);
                if(callback) {
                    callback();
                }
            }
        }
    });
}

function changeEtatStatut(statut) {

    idField = myApp.addTab_inside();
    urlData = "../convergenceList/actions/changeEtatStatut.php?array=" + idField + "&statut=" + statut;
    Ext.MessageBox.show({
        msg: 'Mise à jour des données, veuillez patienter...',
        progressText: 'En cours...',
        width: 300,
        wait: true,
        waitConfig: {
            interval: 200
        }
    });
    Ext.Ajax.request({
        url: urlData,
        params: {
            array: idField,
            statut: statut
        },
        success: function(result, request) {
            var response = Ext.util.JSON.decode(result.responseText);
            if (response.success) {
                Ext.MessageBox.hide();
                alert(response.messageinfo);
            }
            else {
                Ext.MessageBox.hide();
                PMExt.warning(_('ID_WARNING'), response.message);
            }
            Ext.getCmp('gridNewTab').store.reload();
        },
        failure: function(result, request) {
            Ext.MessageBox.hide();
            Ext.getCmp('gridNewTab').store.reload();
        }
    });
}

function Voirlecourrier(doc_uid_list) {

    /*Gary: I added this this was not working*/
    idField = myApp.addTab_inside();                    
    urlData = "../convergenceList/actions/exportCourrier.php";    
    post(urlData, {idFile: idField, doc_uid_list: doc_uid_list});
    
    /*
    idField = myApp.addTab_inside();              
    urlData = "../convergenceList/actions/exportCourrier.php?idFile=" + idField;    
    location.href = urlData;
    */
    
    /*Gary: I added this this was not working*/

  /*idField = myApp.addTab_inside();
    var data = JSON.parse(idField);
    var miArray = new Array();

    for(var i=0;i < data.length;i++) {
        miArray.push(data[i].APP_UID);
    }

    urlData = "../convergenceList/actions/exportCourrier.php?idFile=" + miArray;
    location.href = urlData;*/
    /*Ext.Ajax.request({
           url : urlData,
           params : {
             array  : idField               
           },
           success: function (result, request) {
             var response = Ext.util.JSON.decode(result.responseText);
             if (response.success) {                
               alert(response.messageinfo);
             }
             else {             
               PMExt.warning(_('ID_WARNING'), response.message);
             }           
           },
           failure: function (result, request) {            
           }
    });*/
}

function ExportDocument(docid){

    idField = myApp.addTab_inside();                    
    urlData = "../convergenceList/actions/exportDocument.php";    
    post(urlData, {idFile : idField, iddoc : docid});

}

function exporterCSVFileF(type) {

    if (!type) type = 'npai';

    idField = myApp.addTab_inside();                    
    urlData = "../convergenceList/actions/exportData.php?type=" + type;
    location.href = urlData;
}
function exporterRMHFileF() {

    idField = myApp.addTab_inside();
    urlData = "../convergenceList/actions/exportRMHFile.php";
    post(urlData, {items: idField});
}

function exportDossierListeProd(){
    
    idField = myApp.addTab_inside();                    
    urlData = "../convergenceList/actions/exportDossierListeProd.php";    
    post(urlData, {items : idField});
}

function DownloadFileXLS(app_uid,num_dossier) {
    
    var Docid = "";
    var randomic = Math.ceil(Math.random() * 100);
    Ext.Ajax.request({
        url: '../aquitaineProject/ajaxDocument.php',
        method: "POST",
        params: {"app_uid": app_uid},           
        success:function (result, request) {
          dataDoc = Ext.util.JSON.decode(result.responseText).data;
          if(dataDoc[0]!=undefined)
          {
              Docid = dataDoc[0].APP_DOC_UID;
              requestFile = '../aquitaineProject/cases_ShowDocument?a=' + Docid + '&r=' + randomic + '&nd='+num_dossier;
              redirectShowDocument(requestFile);
          }else{
              Ext.Msg.show({
                title: 'Information',
                msg : 'Excel generated no Case: '+num_dossier,
                buttons: Ext.Msg.OK,
                icon: Ext.Msg.INFO
              });
          }
        },
        failure:function (result, request) {
          Ext.Msg.show({
            title: 'Error',
            msg : 'Failure data load.',
            buttons: Ext.Msg.OK,
            icon: Ext.Msg.ERROR
          }); 
        }
      });
    
    
   
}
function redirectShowDocument(href) {
    parent.location.href = href;
}
  
/* Function to make a post instead of GET url*/
function post(URL, PARAMS) {
    var temp=document.createElement("form");
    temp.action=URL;
    temp.method="POST";
    temp.style.display="none";
    for(var x in PARAMS) {
        var opt=document.createElement("textarea");
        opt.name=x;
        opt.value=PARAMS[x];
        temp.appendChild(opt);
    }
    document.body.appendChild(temp);
    temp.submit();
    return temp;
}
  
function reproductionCheque(annuleFlag){

    if (!annuleFlag) { annuleFlag = 0; } 

    idField = myApp.addTab_inside();                    
    urlData = "../convergenceList/actions/reproductionCheque.php";  
    
    test = Ext.MessageBox.show({
        msg: 'Mise à jour des données, veuillez patienter...',
        progressText: 'En cours...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
        });
    Ext.Ajax.request({
           url : urlData,
           params : {
             array  : idField,
                     todo : annuleFlag
           },
           success: function (result, request) {
             var response = Ext.util.JSON.decode(result.responseText);
             if (response.success) {
                test.hide();
                        
                         Ext.MessageBox.show({
                            title : 'RÔøΩsultat du traitement',
                            msg : response.messageinfo,
                            width : 500,
                            fn : function() {Ext.getCmp('gridNewTab').store.reload();},
                            icon: Ext.MessageBox.INFO
                        });
                        
              
             }
             else {
                test.hide();
               PMExt.warning(_('ID_WARNING'), response.message);
             }
             //Ext.getCmp('gridNewTab').store.reload();             
           },
           failure: function (result, request) {
            test.hide();
            Ext.getCmp('gridNewTab').store.reload();                
           }
         });
    
    
}

function VoirLesDemandesProd(app_uid){
             
        var adaptiveHeight = getDocHeight() - 50;
        
        var _dmdProdColumns = new Array();
        var _dmdProdFields = new Array();
        var storeDmdProd;
        var  _CLOSE = 'Fermer';
        var _WINTITLE_DMDPROD = "Liste des dossiers de cette production";
        
        column = {id : 'APP_UID',header: 'ID Demande',width : 20,dataIndex : 'APP_UID',hidden: true};        
        _dmdProdColumns.push(column);
        _dmdProdFields.push({name: 'APP_UID'});
        
        column = {id : 'NUM_DOSSIER',header: 'N&deg; Dossier AS400',width : 100,dataIndex : 'NUM_DOSSIER',hidden: false};        
        _dmdProdColumns.push(column);
        _dmdProdFields.push({name: 'NUM_DOSSIER'});
        
        column = {
            id : 'NUM_DOSSIER_COMPLEMENT',
            header: 'N&deg; Dossier',
            width : 100,
            renderer  : function(value, meta, record) {
                var demandID = record.data.APP_UID;
                if (value != null)
                    return '<a href="#" onclick="viewForms(\''+demandID+'\',1)">'+value+'</a>';
                else
                    return '<a href="#" onclick="viewForms(\''+demandID+'\',1)">'+record.data.NUM_DOSSIER+'</a>';
                
            },
            dataIndex : 'NUM_DOSSIER_COMPLEMENT',

            hidden: false
        };        
        _dmdProdColumns.push(column);
        _dmdProdFields.push({name: 'NUM_DOSSIER_COMPLEMENT'});
        
        column = {
                id        : 'PRENOM',
                header    : 'Pr&eacute;nom',
                width     : 120,//30,req
                dataIndex : 'PRENOM'
                
        };  
        _dmdProdColumns.push(column);  
        _dmdProdFields.push({name: 'PRENOM'});
        
        
        column = {
                id        : 'NOM',
                header    : 'Nom',
                width     : 120,//30,req
                dataIndex : 'NOM'
                
        };  
        _dmdProdColumns.push(column);  
        _dmdProdFields.push({name: 'NOM'});
        
        column = {
                id        : 'REPRODUCTION_CHQ',
                header    : 'A reproduire ?',
                width     : 80,
                renderer  : function(value){
                    if (value != 'O' )
                        return 'Non';
                    else
                        return 'Oui';
                },
                dataIndex : 'REPRODUCTION_CHQ'
                
        };  
        _dmdProdColumns.push(column);  
        _dmdProdFields.push({name: 'REPRODUCTION_CHQ'});
        
        column = {
                id        : 'NPAI',
                header    : 'PND ?',
                width     : 80,//30,req
                renderer  : function(value){
                    if (value != 'O' )
                        return 'Non';
                    else
                        return 'Oui';
                },
                dataIndex : 'NPAI'
                
        };  
        _dmdProdColumns.push(column);  
        _dmdProdFields.push({name: 'NPAI'});
        
        column = {
                id        : 'LABEL',
                header    : 'Type',
                width     : 220,//30,req
                dataIndex : 'LABEL'
                
        };  
        _dmdProdColumns.push(column);  
        _dmdProdFields.push({name: 'LABEL'});
        
        column = {
                id        : 'BCONSTANTE',
                header    : 'Ch&eacute;quier',
                width     : 100,
                dataIndex : 'BCONSTANTE'
                
        };  
        _dmdProdColumns.push(column);  
        _dmdProdFields.push({name: 'BCONSTANTE'});
        
        
        storeDmdProd = new Ext.data.JsonStore({
            url : '../convergenceList/actions/ListDemandeProd.php?app_uid=' + app_uid,
            root : 'data',
            totalProperty : 'total',
            autoWidth : true,
            
            fields : _dmdProdFields
        }); 
        storeDmdProd.load();
        
        var cmDmdProd = new Ext.grid.ColumnModel({
            defaults : {
                width : 20,
                sortable : true
            },
            columns : _dmdProdColumns
        });
        cmDmdProd.defaultSortable= true;  
        
        var gridDmdProd = new Ext.grid.GridPanel({
        store       : storeDmdProd,
        cm          : cmDmdProd,
        stripeRows  : true,
        columnLines : true,
        autoScroll  : true,
        autoWidth   : true,
        stateful    : true,
        id          : 'gridDmdProd',
        layout      : 'fit' ,
        viewConfig  : {
            forceFit : false,
            emptyText: ( _('ID_NO_RECORDS_FOUND') )
        },
        /*bbar: new Ext.PagingToolbar({
            pageSize: 300,
            store: storeTitre,
            displayInfo: true,
            displayMsg: _('ID_DISPLAY_ITEMS') + ' &nbsp; ',
            emptyMsg: _('ID_DISPLAY_EMPTY')
        }),*/
        listeners: {
            render: function(grid) {
                
            } ,

            afterrender: function(){
                       
            },

            cellcontextmenu : function(grid, rowIndex, cellIndex, event) {
      
                   
            } 
        },
        tbar : [{
            text: _CLOSE,
            iconCls: 'button_menu_ext ss_sprite ss_accept',
            handler: function() {
                winDmdProd.close();
            }   
        }]
        });     
        ///////////// end grid  
        winDmdProd = new Ext.Window({
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: true,
            id: 'winDmdProd',
            title: _WINTITLE_DMDPROD,
            width : 900,
            height : 400,
            modal : true,
            closable:true,
            constrain:true,
            autoScroll:true,
            layout: 'fit',
            items: gridDmdProd
        });     
        
        winDmdProd.show();
        //winTitre.maximize();
        winDmdProd.toFront(); 
}


function VoirLesDemandesProdEntite(app_uid, entity_label, callback) {

    var adaptiveHeight = getDocHeight() - 50;

    var _dmdProdColumns = new Array();
    var _dmdProdFields = new Array();
    var storeDmdProd;
    var _CLOSE = 'Fermer';
    var _WINTITLE_DMDPROD = "Liste des dossiers de cette production";
    var _ENTITY_NAME = entity_label;
    
    column = {id: 'APP_UID', header: 'ID Demande', width: 20, dataIndex: 'APP_UID', hidden: true};
    _dmdProdColumns.push(column);
    _dmdProdFields.push({name: 'APP_UID'});

    column = {id: 'NUM_DOSSIER', header: 'N&deg; Dossier AS400', width: 100, dataIndex: 'NUM_DOSSIER', hidden: true};
    _dmdProdColumns.push(column);
    _dmdProdFields.push({name: 'NUM_DOSSIER'});

    column = {
        id: 'NUM_DOSSIER_COMPLEMENT',
        header: 'N&deg; Dossier',
        width: 100,
        renderer: function(value, meta, record) {
            var demandID = record.data.APP_UID;
            if (value != null)
                return '<a href="#" onclick="viewForms(\'' + demandID + '\',1)">' + value + '</a>';
            else
                return '<a href="#" onclick="viewForms(\'' + demandID + '\',1)">' + record.data.NUM_DOSSIER + '</a>';

        },
        dataIndex: 'NUM_DOSSIER_COMPLEMENT',
        hidden: false
    };
    _dmdProdColumns.push(column);
    _dmdProdFields.push({name: 'NUM_DOSSIER_COMPLEMENT'});


    column = {
        id: 'ENTITE',
        header: _ENTITY_NAME,
        width: 255, //30,req
        dataIndex: 'ENTITE'

    };
    _dmdProdColumns.push(column);
    _dmdProdFields.push({name: 'ENTITE'});

    column = {
        id: 'REPRODUCTION_CHQ',
        header: 'A reproduire ?',
        width: 80,
        renderer: function(value) {
            if (value != 'O')
                return 'Non';
            else
                return 'Oui';
        },
        dataIndex: 'REPRODUCTION_CHQ'

    };
    _dmdProdColumns.push(column);
    _dmdProdFields.push({name: 'REPRODUCTION_CHQ'});

    column = {
        id: 'NPAI',
        header: 'PND ?',
        width: 80, //30,req
        renderer: function(value) {
            if (value != 'O')
                return 'Non';
            else
                return 'Oui';
        },
        dataIndex: 'NPAI'

    };
    _dmdProdColumns.push(column);
    _dmdProdFields.push({name: 'NPAI'});

    column = {
        id: 'LABEL',
        header: 'Type',
        width: 220, //30,req
        dataIndex: 'LABEL'

    };
    _dmdProdColumns.push(column);
    _dmdProdFields.push({name: 'LABEL'});

    column = {
        id: 'BCONSTANTE',
        header: 'Ch&eacute;quier',
        width: 100,
        dataIndex: 'BCONSTANTE'

    };
    _dmdProdColumns.push(column);
    _dmdProdFields.push({name: 'BCONSTANTE'});


    storeDmdProd = new Ext.data.JsonStore({
        url: '../convergenceList/actions/ListDemandeProd.php?app_uid=' + app_uid + '&callback=' + callback,
        root: 'data',
        totalProperty: 'total',
        autoWidth: true,
        fields: _dmdProdFields
    });
    storeDmdProd.load();

    var cmDmdProd = new Ext.grid.ColumnModel({
        defaults: {
            width: 20,
            sortable: true
        },
        columns: _dmdProdColumns
    });
    cmDmdProd.defaultSortable = true;

    var gridDmdProd = new Ext.grid.GridPanel({
        store: storeDmdProd,
        cm: cmDmdProd,
        stripeRows: true,
        columnLines: true,
        autoScroll: true,
        autoWidth: true,
        stateful: true,
        id: 'gridDmdProd',
        layout: 'fit',
        viewConfig: {
            forceFit: false,
            emptyText: (_('ID_NO_RECORDS_FOUND'))
        },
        /*bbar: new Ext.PagingToolbar({
         pageSize: 300,
         store: storeTitre,
         displayInfo: true,
         displayMsg: _('ID_DISPLAY_ITEMS') + ' &nbsp; ',
         emptyMsg: _('ID_DISPLAY_EMPTY')
         }),*/
        listeners: {
            render: function(grid) {

            },
            afterrender: function() {

            },
            cellcontextmenu: function(grid, rowIndex, cellIndex, event) {


            }
        },
        tbar: [{
                text: _CLOSE,
                iconCls: 'button_menu_ext ss_sprite ss_accept',
                handler: function() {
                    winDmdProd.close();
                }
            }]
    });
    ///////////// end grid
    winDmdProd = new Ext.Window({
        closeAction: 'hide',
        autoDestroy: true,
        maximizable: true,
        id: 'winDmdProd',
        title: _WINTITLE_DMDPROD,
        width: 900,
        height: 400,
        modal: true,
        closable: true,
        constrain: true,
        autoScroll: true,
        layout: 'fit',
        items: gridDmdProd
    });

    winDmdProd.show();
    //winTitre.maximize();
    winDmdProd.toFront();
}

function ActioncreateNewComplementCH(uidForm,app_uid)
{               
        urlData = "../convergenceList/actions/ActioncreateNewComplementCH?task="+uidForm+"&uid="+app_uid;
        
        var adaptiveHeight = getDocHeight() - 50;
        
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            autoHeight : true,
            title: 'Creation',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelForms',                    
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,  
                    items:[{
                      id: 'Forms',
                      title: 'Nouveau',                      
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: adaptiveHeight+'px'}                      

                      }
                    ],
                    listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
                      },
                      afterrender: function(panel){                     
                        //panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){            
            Ext.getCmp('gridNewTab').store.reload();
        });
}

function ActioncreateNewCase(uidForm)
{               
        urlData = "../convergenceList/actions/ActioncreateNewCase?task="+uidForm;
        
        var adaptiveHeight = getDocHeight() - 50;
        
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            autoHeight : true,
            title: 'Creation',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelForms',                    
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,  
                    items:[{
                      id: 'Forms',
                      title: 'Nouveau',                      
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: adaptiveHeight+'px'}                      

                      }
                    ],
                    listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
                      },
                      afterrender: function(panel){                     
                        //panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){            
            Ext.getCmp('gridNewTab').store.reload();
        });
}

function actionModifyAdresse(uidForm, app_uid)
{               
        urlData = "../convergenceList/actions/actionModifyAdresse?task=" + uidForm + "&uid=" + app_uid;
        var adaptiveHeight = getDocHeight() - 50;
        
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: false,
            title: 'Editer l\'adresse :',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelForms',                    
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,        
                    items:[{
                      id: 'Forms',
                      title: 'Nouveau',                      
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: adaptiveHeight+'px'}                      

                      }
                    ],
                    listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
                      },
                      afterrender: function(panel){                     
                        //panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){            
            Ext.getCmp('gridNewTab').store.reload();
        });
}

function actionDateExpedition(uidForm,app_uid)
{               
        urlData = "../convergenceList/actions/actionDateExpedition?task="+uidForm+"&uid="+app_uid;
        var adaptiveHeight = getDocHeight() - 50;
        
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: false,
            title: 'Date d\'expÔøΩdition :',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelForms',                    
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,        
                    items:[{
                      id: 'Forms',
                      title: 'Nouveau',                      
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: adaptiveHeight+'px'}                      

                      }
                    ],
                    listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
                      },
                      afterrender: function(panel){                     
                        //panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){            
            Ext.getCmp('gridNewTab').store.reload();
        });
}

function actionDateVirement(uidForm,app_uid)
{               
        urlData = "../convergenceList/actions/actionDateVirement?task="+uidForm+"&uid="+app_uid;
        var adaptiveHeight = getDocHeight() - 50;
        
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: false,
            title: 'Date de virement :',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelForms',                    
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,        
                    items:[{
                      id: 'Forms',
                      title: 'Nouveau',                      
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: adaptiveHeight+'px'}                      

                      }
                    ],
                    listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
                      },
                      afterrender: function(panel){                     
                        //panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){            
            Ext.getCmp('gridNewTab').store.reload();
        });
}

function actionNewRmbt(uidForm,app_uid,num_dossier)
{               
        urlData = "../convergenceList/actions/actionNewRmbt?task="+uidForm+"&uid="+app_uid+"&num_dossier="+num_dossier;
        var adaptiveHeight = getDocHeight() - 50;
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: false,
            title: 'Nouveau remboursement :',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelForms',                    
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,        
                    items:[{
                      id: 'Forms',
                      title: 'Nouveau',                      
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: adaptiveHeight+'px'}                      

                      }
                    ],
                    listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
                      },
                      afterrender: function(panel){                     
                        //panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){            
            Ext.getCmp('gridNewTab').store.reload();
        });
}

function actionDeleteCases(canBeDeletedFunc){
    
    arrayAPPUID = myApp.addTab_inside();                    
        urlData = "../convergenceList/actions/actionDeleteCases.php";
        
    PMExt.confirm(_('ID_CONFIRM'), "Voulez-vous vraiment supprimer ce(s) dossier(s) ?", function() {
        
        Ext.MessageBox.show({
              msg : 'Chargement, Veuillez patienter ...',
              progressText : 'Chargement ...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
        });
        Ext.Ajax.request({
               url : urlData,
               params : {
                 array  : arrayAPPUID,
                 pmTableId  :table,
                 canBeDeletedFunc : canBeDeletedFunc
               },
               success: function (result, request) {
                 var response = Ext.util.JSON.decode(result.responseText);
                 if (response.success) {
                    Ext.MessageBox.hide();
                   alert(response.messageinfo);
                 }
                 else {
                    Ext.MessageBox.hide();
                   PMExt.warning(_('ID_WARNING'), response.message);
                 }
                 Ext.getCmp('gridNewTab').store.reload();               
               },
               failure: function (result, request) {
                Ext.MessageBox.hide();
                Ext.getCmp('gridNewTab').store.reload();                
               }
             });
    });    
}
function actionCompletDelFullCases(){

    arrayAPPUID = myApp.addTab_inside();
        urlData = "../convergenceList/actions/actionCompletDelFullCases.php";

        PMExt.confirm(_('ID_CONFIRM'),"Voulez vous continuer ? (action dev Oblady)", function(){

        Ext.MessageBox.show({
              msg : 'Chargement, Veuillez patienter ...',
              progressText : 'Chargement ...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
        });
        Ext.Ajax.request({
               url : urlData,
               params : {
                 array  : arrayAPPUID,
                 pmTableId  :table
               },
               success: function (result, request) {
                 var response = Ext.util.JSON.decode(result.responseText);
                 if (response.success) {
                    Ext.MessageBox.hide();
                   alert(response.messageinfo);
                 }
                 else {
                    Ext.MessageBox.hide();
                   PMExt.warning(_('ID_WARNING'), response.message);
                 }
                 Ext.getCmp('gridNewTab').store.reload();
               },
               failure: function (result, request) {
                Ext.MessageBox.hide();
                Ext.getCmp('gridNewTab').store.reload();
               }
             });
    });
}

function actionRestartCases(){
    
    arrayAPPUID = myApp.addTab_inside();                    
    urlData = "../convergenceList/actions/actionRestartCases.php";
    PMExt.confirm(_('ID_CONFIRM'),"Do you like to restart all these Cases?", function(){
        Ext.MessageBox.show({
              msg : 'RedÔøΩmarrage, Veuillez patienter ...',
              progressText : 'Chargement ...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
        });
        Ext.Ajax.request({
               url : urlData,
               params : {
                 array  : arrayAPPUID,
                 pmTableId  :table              
               },
               success: function (result, request) {
                 var response = Ext.util.JSON.decode(result.responseText);
                 if (response.success) {
                    Ext.MessageBox.hide();
                   alert(response.messageinfo);
                 }
                 else {
                    Ext.MessageBox.hide();
                   PMExt.warning(_('ID_WARNING'), response.message);
                 }
                 Ext.getCmp('gridNewTab').store.reload();               
               },
               failure: function (result, request) {
                Ext.MessageBox.hide();
                Ext.getCmp('gridNewTab').store.reload();                
               }
             });
    });    
}

function caseGralInfo(appUid,num_dossier,table){    
    
        if (!table) table = 'PMT_DEMANDES';
        var adaptiveHeight = getDocHeight() - 50;
    
    //urlData = "../convergenceList/actionGeneralInfo.php?appUid=" + appUid +"&adaptiveHeight="+adaptiveHeight+"&num_dossier="+num_dossier;    
    urlData = "../convergenceList/casesHistoryDynaformPage_Ajax.php?actionAjax=HistoryLog&APP_UID=" + appUid +"&adaptiveHeight="+adaptiveHeight+"&num_dossier="+num_dossier+"&table="+table;    
        
        
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: false,
            title: 'Historique du dossier :',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelcaseGralInfo',                    
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,        
                    items:[{
                      id: 'GralInfo',
                      title: 'Historique',                      
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: adaptiveHeight+'px'}                      

                      }
                    ],
                    listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
                      },
                      afterrender: function(panel){                     
                        //panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){            
            Ext.getCmp('gridNewTab').store.reload();
        });
}

function searchDoublon(uidRequest){
    
    urlData = "../convergenceList/actions/searchDoublon.php?idR=" + uidRequest;    
    location.href = urlData;

}
 
//nondoublon
function nonDoublon()
{
    idField = myApp.addTab_inside();
    _AppUids = Ext.util.JSON.decode(idField);
    
     Ext.each(_AppUids, function(record){
       
         urlData = "../convergenceList/actions/nonDoublon.php?AppUid=" +  record.APP_UID;
        Ext.MessageBox.show({
            msg: 'Mise à jour des données, veuillez patienter...',
            progressText: 'En cours...',
            width : 200,
            wait : true,
            waitConfig : {
                interval : 100
            }
        });
        Ext.Ajax.request({
            url : urlData,
            params : {
                AppUid  : record.APP_UID            
            },
            success: function (result, request) {
                var response = Ext.util.JSON.decode(result.responseText);
                if (response.success) {
                
                        Ext.MessageBox.hide();
                    
                }
                else {
                    Ext.MessageBox.hide();
                    PMExt.warning(_('ID_WARNING'), response.message);
                }
                            
            },
            failure: function (result, request) {
                Ext.MessageBox.hide();
                //Ext.getCmp('gridNewTab').store.reload();              
            }
        });
     });
    
    var field=idField.replace("},{","}] , {[");
    field=field.split(" , ");

    Ext.getCmp('gridNewTab').store.reload();    
   
}
function NouveauRemboursement(demandID,uidForm)
{                         
        urlData = "../convergenceList/actions/actionNewRmbt.php?uid="+demandID+"&task="+uidForm;
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: false,
            title: 'Creation:',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelForms',                    
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,        
                    items:[{
                      id: 'Forms',
                      title: 'Nouveau Remboursement',                      
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: '450px'}                      

                      }
                    ],
                    listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
                      },
                      afterrender: function(panel){                     
                        //panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){            
            Ext.getCmp('gridNewTab').store.reload();
        });
} 
function EditStartForm(appid,uidForm)
{                           
        urlData = "../convergenceList/actions/actionEditStartForm?appid="+appid+"&task="+uidForm;
        var adaptiveHeight = getDocHeight() - 50;
        
        var win2 = new Ext.Window({
            id:'win2',
            closable: true,
            closeAction : 'hide',
            autoDestroy : true,
            maximizable: false,
            title: 'Creation:',               
            modal: true,
            loadMask : true,
            items : [{
                id: 'PanelForms',    
                xtype:'panel',
                items:[{
                    xtype:"tabpanel",
                    id: 'tabPanelForms',                    
                    deferredRender:false,                   
                    defaults:{autoScroll: true},
                    defaultType:"iframepanel",
                    activeTab: 0,
                    enableTabScroll: true,        
                    items:[{
                      id: 'Forms',
                      title: 'Edition des donnÔøΩes',                       
                      frameConfig:{name:'openCaseFrame', id:'openCaseFrame'},
                      defaultSrc : urlData,
                      loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                      bodyStyle:{height: adaptiveHeight+'px'}                      

                      }
                    ],
                    listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
                      },
                      afterrender: function(panel){                     
                        //panel.hideTabStripItem(0);                      
                      },                      
                      render : function(panel){
                        Ext.each([this.el, this[this.collapseEl]] ,
                        function( elm ) {                           
                          elm.setVisibilityMode(Ext.Element.VISIBILITY).originalDisplay ='visible';
                        });
                      }
                    }
                }]              
            }]
        });

        win2.show();
        win2.maximize();
        win2.on('hide',function(){            
            Ext.getCmp('gridNewTab').store.reload();
        });
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Import CSV, create case and autoderivate 
function importCSV (_uidTask){
    var _MSG_ERROR_CONFIG_ACTION_CSV = 'Votre configuration est incorect, vérifiez les paramètres svp.';
      if(typeof(_uidTask) == "undefined") {
        PMExt.info(_('ID_INFO'), _MSG_ERROR_CONFIG_ACTION_CSV);
        return true;
      }
      var _dblIdMainGrid          = myApp.getIdMainGrid();
      var pathPluginActionsPhp    = '../convergenceList/actions/actionCSV'; 
      var _LBL_ITEMCBO_COLUMN     = 'Column';
      var _isCheckedFirstLineAs   = 'off';
      var _isCheckedAdd           = 'on';
      var _isCheckedDeleteAdd     = 'off';
      var _isCheckedEditAdd       = 'off';
      var _isCheckedTruncateAdd   = 'off';
      var _isCheckedOption        = 'add';
      var _SELECT_OPTION          = 'Select...' ;  

    var _USE_FIRSTLINE_AS = 'La première ligne contient les entêtes';
    var _USE_ADD = 'Importer et ajouter';
    var _USE_DELETE_ADD = 'Supprimer puis importer';
    var _USE_EDIT_ADD = 'Importer et mettre à jour';
    var _USE_TRUNCATE_ADD = 'Truncate puis importer';
    var _WINTITLE_MATCHDATA = 'Configurer le mapping Nom du champ - Colonne CSV';
    var _IMPORT_CREATE_CASES = 'Importer et créer';
    var _UPOLADING_FILE = 'chargement du fichier...';
    var _FIELD_NAME_PROCESS = 'Nom du champ (Formulaire)';
    var _COLUMN_CSV = 'Colonne (Fichier CSV)';
    var _COLUMN_TYPE = 'Type de champ';
    var _DATA_SAVED_OK = 'Données sauvegardées avec succés!';
    var _MSG_CASE_CREATED = ' cas se sont terminés avec succés.';
    var _OPERATION_NO_COMPLETED = 'Un problème a été rencontré, l\'import ne s\'est peut être pas effectué complètement !';
    var _MSG_ERROR = 'Une erreur est survenue lors de l\'import!';
    var _MSG_TITLE_MESSAGE      = 'Message';
    var _MSG_IMPORT_LOAD_DATA_SUCCESSFULLY = 'Chargement des paramètres réussi';
    var _MSG_TITLE_CREATE_DERIVATE_CASES = 'Cas créé';
    var _CSV_FILE = 'Fichier CSV';
    var _MSG_TITLE_SAVE_CONFIG_CSV = 'Mémoriser le mapping CSV';
    var _MSG_SAVE_CONFIG_CSV = 'Configuration sauvegardée!';
    var _MSG_TITLE_SAVE_RESET_CSV = "Réinitialiser le mapping";
    var _RESET_SAVED_OK = "Mapping réinitialisé";
    var _DELETE_EDIT_FIELD = "Supprimer le champs";
    var _REQUIRED_COLUMN = "Champ requis";
    var hiddenDeleteEdit       = true;
    var _dblIdInbox = myApp.getIdInbox();
    var winMatchData;
    var waitLoading = {};
      waitLoading.show = function() {
        var mask = Ext.getBody().mask(_("ID_SAVING"), 'x-mask-loading', false);
        mask.setStyle('z-index', Ext.WindowMgr.zseed + 1000);
      };
      waitLoading.hide = function() {
        Ext.getBody().unmask();
      };
      
    var radiosGroup = new Ext.form.RadioGroup({   
        
       columns: 1, //display the radiobuttons in two columns   
       items: [   
            {
            boxLabel: _USE_ADD,
               name: 'radioGroupOption',
               checked: true,
               id: 'add',
               listeners: {
              'change' : function(){
                _isCheckedOption    = 'add';
                hiddenDeleteEdit    = true;
               }
              }
            },
            { 
             boxLabel: _USE_DELETE_ADD,
             name: 'radioGroupOption',
             checked: false,
             id: 'deleteAdd',
             listeners: {
                 'change' : function(){
                _isCheckedOption    = 'deleteAdd';
                _DELETE_EDIT_FIELD  = "Delete Field";
                hiddenDeleteEdit    = false;
                 }
              }
            },   
            { 
             boxLabel: _USE_EDIT_ADD,
             name: 'radioGroupOption',
             checked: false,
             id: 'editAdd',
             listeners: {
                 'change' : function(){
                   _isCheckedOption    = 'editAdd';
                    _DELETE_EDIT_FIELD = "Clé logique pour modifier";
                   hiddenDeleteEdit    = false;
                 }
             }
            },
            { 
             boxLabel: _USE_TRUNCATE_ADD,
             name: 'radioGroupOption',
             checked: false,
             id: 'truncateAdd',
             listeners: {
                 'change' : function(){
                   _isCheckedOption    = 'truncateAdd';
                   hiddenDeleteEdit    = true;
                 }
             }
            }  
              
       ],
       listeners: {
                change: function(el,val) {
                   // console.log(val);
                    _isCheckedOption = val.id;
                    if(_isCheckedOption == 'deleteAdd')
                    {
                        _DELETE_EDIT_FIELD  = "Delete Field";
                        hiddenDeleteEdit    = false;
                    }
                    if(_isCheckedOption == 'editAdd')
                    {
                        _DELETE_EDIT_FIELD = "Clé logique pour modifier";
                        hiddenDeleteEdit    = false;
                    }
                    if(_isCheckedOption == 'add')
                    {
                        hiddenDeleteEdit    = true;
                    }
                    if(_isCheckedOption == 'truncateAdd')
                    {
                        hiddenDeleteEdit    = true;
                    }
                    console.log(_isCheckedOption);
                }
            } 
   });    
      var w = new Ext.Window({
        title       : '',
        width       : 440,
        height      : 250,
        modal       : true,
        autoScroll  : false,
        maximizable : false,
        resizable   : false,
        items: [
          new Ext.FormPanel({
            id         :'uploader',
            fileUpload : true,
            width      : 420,
            frame      : true,
            title      : _('ID_IMPORT_DATA_CSV'),
            autoHeight : false,
            bodyStyle  : 'padding: 10px 10px 0 10px;',
            labelWidth : 80,
            defaults   : {
                anchor     : '90%',
                allowBlank : false,
                msgTarget  : 'side'
            },
            items : [{
                xtype      : 'fileuploadfield',
                id         : 'csv-file',
                emptyText  : _('ID_SELECT_FILE'),//'Select a file',
                fieldLabel : _CSV_FILE,
                name       : 'form[CSV_FILE]',
                buttonText : '',
                buttonCfg  : {
                    iconCls: 'upload-icon'
                }
            },
            {
              xtype: 'checkbox',
              fieldLabel: '',
              boxLabel: _USE_FIRSTLINE_AS,
              name: 'chkFirstRow',
              checked: false,
              listeners: {
                  'check': function(checkbox, checked) {
                    _isCheckedFirstLineAs = (checked)?'on':'off'; 
                    Ext.getCmp('hdnCheckedFirstRow').setValue(_isCheckedFirstLineAs);
                  }
              }
            },radiosGroup,
            {
              xtype : 'hidden',
              name  : 'form[FIRSTLINE_ISHEADER]',
              id    : 'hdnCheckedFirstRow',
              value : 'off'
            }
            ],
            buttons : [{
                text     : _('ID_UPLOAD'),
                handler  : function(){
                  var filePath = Ext.getCmp('csv-file').getValue();
                  var fileType = filePath.substring(filePath.lastIndexOf('.') + 1).toLowerCase();
                  if(fileType =='csv' ){
                    var uploader  = Ext.getCmp('uploader');

                    if(uploader.getForm().isValid()){
                      uploader.getForm().submit({
                        url: pathPluginActionsPhp + '?option=getDataCSV',
                        waitMsg  : _UPOLADING_FILE,
                        scope: this,
                        success  : function(o, resp){
                          w.close();
                          var dataCSV = Ext.util.JSON.decode(resp.response.responseText);
                          if(typeof(dataCSV.success)!= 'undefined' && dataCSV.success === true){
                            
                            var _dataForCboFieldCSV = new Array();
                            var _numCol = 0, lenColumns=0;
                            var _itemsCboCSV = new Array();

                            var child = new Array();
                            child.push(_SELECT_OPTION);
                            child.push(_SELECT_OPTION);                        
                            _itemsCboCSV.push(child);                     

                            if(_isCheckedFirstLineAs =='on'){ //with header
                              Ext.iterate(dataCSV.data[0], function(key, value) {
                                  var child = new Array();
                                  child.push(key);
                                  child.push(key.toUpperCase());
                                  _itemsCboCSV.push(child); 
                                  lenColumns++;
                              });
                            }else{
                              Ext.iterate(dataCSV.data[0], function(key, value) {
                                  var child = new Array();
                                  child.push(_LBL_ITEMCBO_COLUMN + ' ' + lenColumns);
                                        child.push(_LBL_ITEMCBO_COLUMN + ' ' + lenColumns + ' (' +key + '...)');
                                  _itemsCboCSV.push(child); 
                                  lenColumns++;
                              });
                            }
                   
                            var storeMatchData = new Ext.data.JsonStore({
                                url           : pathPluginActionsPhp + '?option=getDataMatch&' + '&tableName=' + table +'&idInbox=' +_dblIdInbox +'&firstLineHeader=' +_isCheckedFirstLineAs,
                                root          : 'data',
                                totalProperty : 'total', 
                                remoteSort    : true,
                                autoWidth     : true,
                                            fields: ['FIELD_NAME', 'FIELD_DESC', 'COLUMN_CSV', 'COLUMN_TYPE', 'DELETE_EDIT_FIELD', 'REQ_COLUMN']
                            });

                            Ext.Ajax.request({
                              url: pathPluginActionsPhp,
                              method: "POST",
                              params: {'option': 'getDataMatch', 'tableName': table, 'idInbox' : _dblIdInbox ,'firstLineHeader' : _isCheckedFirstLineAs },
                              success:function (result, request) {
                                var resp = Ext.util.JSON.decode(result.responseText);
                                if(typeof(resp.success)!= 'undefined' && resp.success === true){
                                  storeMatchData.loadData(Ext.util.JSON.decode(result.responseText));
                                  PMExt.notify(_MSG_TITLE_MESSAGE,_MSG_IMPORT_LOAD_DATA_SUCCESSFULLY);
                                }else{
                                  PMExt.warning(_('ID_ERROR'), resp.message);
                                } 
                              },
                              failure:function (result, request) {
                                var resp = Ext.util.JSON.decode(result.responseText);
                                PMExt.error(_('ID_ERROR'), _MSG_ERROR);
                              }
                            });
                            var pager = new Ext.PagingToolbar({
                                store       : storeMatchData, 
                                displayInfo : true,
                                autoHeight  : true,
                                displayMsg  : _('ID_DISPLAY_ITEMS') + ' &nbsp; ',
                                emptyMsg    : _('ID_DISPLAY_EMPTY'),
                                pageSize    : 500
                            });  

                            var cboFieldCSV = new Ext.form.ComboBox({
                                valueField    : 'ID',
                                displayField  : 'NAME',
                                id            : 'cboFieldCSV',
                                typeAhead     : true,
                                triggerAction : 'all',
                                editable      : true,
                                mode          : 'local',
                                anchor        : '95%',
                                allowBlank    : false,
                                disabled      : false,
                                selectOnFocus : true,
                                store: new Ext.data.SimpleStore({
                                          fields  : ["ID", "NAME"],
                                          data    : _itemsCboCSV        
                                })
                            });
                             
                            var checkColumnInclude = new Ext.grid.CheckColumn({
                                header: _DELETE_EDIT_FIELD + " ?",
                                dataIndex: 'DELETE_EDIT_FIELD',
                                id: 'check',
                                flex: 1,
                                width: 10,
                                checked: false,
                                hidden: hiddenDeleteEdit,
                                processEvent: function () { return false; }
                            });
                            var checkColumnRequired = new Ext.grid.CheckColumn({
                                header: _REQUIRED_COLUMN + " ?",
                                dataIndex: 'REQ_COLUMN',
                                id: 'req_check',
                                flex: 1,
                                width: 10,
                                checked: false,
                                hidden: hiddenDeleteEdit,
                                processEvent: function() {
                                    return false;
                                }
                            });
                            var typeAS = new Ext.form.ComboBox({
                                valueField: 'ID',
                                displayField: 'NAME',
                                id: 'idtypeAS',
                                fieldLabel: '<span style="color: red">*</span>Choose Type ',
                                typeAhead: true,
                                triggerAction: 'all',
                                editable: true,
                                mode: 'local',
                                width: 200,
                                autoHeight: true,
                                listWidth: 250,
                                allowBlank: false,
                                disabled: false,
                                defaultValue: "String",
                                value: "String",
                                store: new Ext.data.SimpleStore({
                                    fields: ["ID", "NAME"],
                                    data: [["String", "Chaine"],
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
                                        ["Decimal", "Décimal"],
                                        ["mail", "E-mail"],
                                        ["Telephone", "Téléphone"],
                                        ["AI", "Actif / Inactif"],
                                        ["cp", "Code postal"],
                                        ["Yesno", "O ou N"],
                                        ["OuiNon", "Oui / Non"],
                                        ["binaire", "0 ou 1"],
                                        ["NCommande", "Numéro Commande"],
                                        ["codeOper", "Code Opération"],
                                        ["Ignore", "Ignorer cette donnée"]
                                    ]
                                }),
                                listeners: {
                                    beforerender: function(combo) {
                                        combo.setValue("String");
                                        Ext.getCmp('idtypeAS').setValue("String");
                                    },
                                    load: function() {
                                        var combo = Ext.getCmp('idtypeAS');
                                        combo.setValue("String");
                                    }
                                }
                            });
                            Ext.getCmp('idtypeAS').setValue("String");
                            var gridcolumns = new Ext.grid.ColumnModel({
                              defaults : {
                                  sortable : true
                              },
                              columns : [new Ext.grid.RowNumberer(),
                              {
                                dataIndex : 'FIELD_NAME',
                                width     : 5,
                                hidden    : true
                              },
                              {
                                header    : '<span style="color:green;">'+_FIELD_NAME_PROCESS + '</span>',
                                width     : 25,
                                sortable  : true,
                                dataIndex : 'FIELD_DESC'
                              },
                              {
                                header    : '<span style="color:blue;">'+_COLUMN_CSV+'</span>',
                                width     : 15,
                                sortable  : true,
                                dataIndex : 'COLUMN_CSV',
                                editor: cboFieldCSV
                            }, {
                                header: '<span style="color:red;">' + _COLUMN_TYPE + '</span>',
                                dataIndex: 'COLUMN_TYPE',
                                width: 15,
                                sortable: true,
                                editor: typeAS
                            }, checkColumnRequired, checkColumnInclude ]
                            });

                            var gridMatchData = new Ext.grid.EditorGridPanel({
                              store           : storeMatchData,
                              columnLines     : true,
                              id              : 'gridMatchData',
                              cm              : gridcolumns,
                            plugins: [checkColumnInclude, checkColumnRequired],
                              tbar : [{
                                text  : _IMPORT_CREATE_CASES,
                                cls   : 'x-btn-text-icon',
                                icon  : '/images/ext/default/tree/drop-yes.gif',
                                handler: function() {
                                    var _dblFieldsCustom    = new Array ();
                                    var _jsonFieldsCustom   = '';
                                    storeMatchData.each(function(record)  {  
                                      if(typeof(record.get('COLUMN_CSV')) != "undefined" && record.get('COLUMN_CSV') != _SELECT_OPTION){
                                        var item = {
                                            "FIELD_NAME"   : record.get('FIELD_NAME'),
                                            "COLUMN_CSV": record.get('COLUMN_CSV'),
                                                                "REQUIRED_COLUMN": record.get('REQ_COLUMN'),
                                            "COLUMN_TYPE": record.get('COLUMN_TYPE')
                                        };
                                        _dblFieldsCustom.push(item);
                                      }
                                    });
                                    
                                    _jsonFieldsCustom= Ext.util.JSON.encode(_dblFieldsCustom); 
                                    
                                    var _jsonFieldsDeleteEdit   = '';
                                    if(_isCheckedOption != 'add') 
                                    {
                                        var _dblFieldsDeleteEdit    = new Array ();
                                        storeMatchData.each(function(record)  {  
                                            if(typeof(record.get('COLUMN_CSV')) != "undefined" && record.get('COLUMN_CSV') != _SELECT_OPTION && record.get('DELETE_EDIT_FIELD') == true ){
                                                var itemDeleteEdit = {
                                                        "CSV_FIELD_NAME"   : record.get('FIELD_NAME'),
                                                        "CSV_COLUMN": record.get('COLUMN_CSV'),
                                                                    "TYPE_COLUMN": record.get('COLUMN_TYPE'),
                                                                    "CSV_PIVOT_EDIT": record.get('DELETE_EDIT_FIELD'),
                                                                    "REQUIRED_COLUMN": record.get('REQ_COLUMN')
                                                                };
                                                _dblFieldsDeleteEdit.push(itemDeleteEdit);
                                            }
                                      
                                        });
                                        if(_dblFieldsDeleteEdit.length > 0)
                                            _jsonFieldsDeleteEdit = Ext.util.JSON.encode(_dblFieldsDeleteEdit); 
                                      
                                    }
                                    //console.log(_isCheckedOption);
                                    
                                    if(_isCheckedOption == 'add' || (_isCheckedOption != 'add' && _jsonFieldsDeleteEdit != '' ) || _isCheckedOption == 'truncateAdd' )
                                    {
                                        waitLoading.show();
                                        Ext.Ajax.request({
                                            params : {        
                                                matchFields : _jsonFieldsCustom,
                                                uidTask     : _uidTask,
                                                tableName   : table,
                                                option      : 'importCreateCase',
                                                firstLineHeader : _isCheckedFirstLineAs,
                                                radioOption : _isCheckedOption,
                                                dataEditDelete : _jsonFieldsDeleteEdit,
                                                idInbox     : _dblIdInbox
                                            },
                                            url : pathPluginActionsPhp,
                                            success : function(result, request) {
                                                waitLoading.hide();
                                                var resp=Ext.util.JSON.decode(result.responseText);
                                                if(typeof(resp.success) != 'undefined' && resp.success === true){
                                                    var totCases = (typeof(resp.totalCases) != 'undefined')?resp.totalCases:0;
                                                    PMExt.notify(_MSG_TITLE_CREATE_DERIVATE_CASES, totCases + ' ' + _MSG_CASE_CREATED);
                                                    winMatchData.close();
                                                }else{
                                                    PMExt.warning(_('ID_ERROR'), resp.message);
                                                }
                                            },
                                            failure : function() {
                                                waitLoading.hide();
                                                PMExt.warning(_('ID_ERROR'), _OPERATION_NO_COMPLETED);
                                            }
                                        });
                                    }
                                    else
                                    {
                                        alert("Select "+_DELETE_EDIT_FIELD);
                                    }
                                } 
                              },
                              '-',
                              {
                                text: _('ID_CANCEL'),
                                iconCls: 'button_menu_ext ss_sprite ss_cancel',
                                handler: function() {winMatchData.close();}
                              },
                              '-',
                              {
                                  text: 'Save Configuration CSV',
                                  iconCls :'button_menu_ext cvrgl_configCSV',
                                    handler: function() {
                                        var _dblFieldsCustom    = new Array ();
                                        var _jsonFieldsCustom   = '';                                       
                                        storeMatchData.each(function(record)  {  
                                        if (typeof(record.get('COLUMN_CSV')) != "undefined" && record.get('COLUMN_CSV') != _SELECT_OPTION) {
                                            var item = {
                                                "CSV_FIELD_NAME"   : record.get('FIELD_NAME'),
                                                "CSV_COLUMN": record.get('COLUMN_CSV'),
                                                                "TYPE_COLUMN": record.get('COLUMN_TYPE'),
                                                                "CSV_PIVOT_EDIT": record.get('DELETE_EDIT_FIELD'),
                                                                "REQUIRED_COLUMN": record.get('REQ_COLUMN')
                                            };
                                            _dblFieldsCustom.push(item);
                                          }
                                        });
                                        
                                        _jsonFieldsCustom = Ext.util.JSON.encode(_dblFieldsCustom); 
                                                                           
                                        waitLoading.show();
                                        
                                        Ext.Ajax.request({
                                            params : {        
                                              matchFields : _jsonFieldsCustom,
                                              idInbox     : _dblIdInbox,
                                              option      : 'saveConfigCSV',
                                              firstLineHeader : _isCheckedFirstLineAs,
                                              radioOption : _isCheckedOption
                                            },
                                            url : pathPluginActionsPhp,
                                            success : function(result, request) {
                                             waitLoading.hide();
                                             var resp=Ext.util.JSON.decode(result.responseText);
                                             if(typeof(resp.success) != 'undefined' && resp.success === true){
                                                 PMExt.notify(_MSG_TITLE_SAVE_CONFIG_CSV, _MSG_SAVE_CONFIG_CSV);
                                                 //winMatchData.close();
                                              }else{
                                                PMExt.warning(_('ID_ERROR'), resp.message);
                                             }
                                            },
                                            failure : function() {
                                              waitLoading.hide();
                                              PMExt.warning(_('ID_ERROR'), _OPERATION_NO_COMPLETED);
                                            }
                                        });
                                    } 
                                  },
                                  '-',
                                  {
                                      text: 'Reset Configuration',
                                      iconCls :'button_menu_ext cvrgl_reset',
                                      handler: function() {
                                            waitLoading.show();
                                            Ext.Ajax.request({
                                                params : {        
                                                idInbox : _dblIdInbox,
                                                tableName   : table
                                                },
                                                url : '../convergenceList/actions/actionCSV.php?option=resetConfigCSV',
                                                success : function(result, request) {
                                                  waitLoading.hide();
                                                  var resp=Ext.util.JSON.decode(result.responseText);
                                                   if(typeof(resp.success)!= 'undefined' && resp.success ==true){
                                                    //winConfigDoublon.close();
                                                       storeMatchData.load();
                                                       PMExt.notify(_MSG_TITLE_SAVE_RESET_CSV, _RESET_SAVED_OK);
                                                   }else{
                                                      PMExt.error(_('ID_ERROR'), resp.message);
                                                   }
                                                },
                                                failure : function() {
                                                  waitLoading.show();
                                                  PMExt.error(_('ID_ERROR'), _OPERATION_NO_COMPLETED);
                                                }
                                            });
                                          }
                                  }
                              ],
                              columnLines    : true,
                              clicksToEdit   : 1,
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
                              bbar           : pager,
                              selModel       : new Ext.grid.RowSelectionModel({singleSelect : true}),
                              viewConfig     : {
                                forceFit     : true,
                                scrollOffset : 2,
                                emptyText    : ( _('ID_NO_RECORDS_FOUND')),
                                sm           : new Ext.grid.RowSelectionModel({singleSelect:true})
                              }
                            });

                            winMatchData = new Ext.Window({
                                closeAction  : 'hide',
                                autoDestroy  : true,
                                maximizable  : true,
                                id           : 'winMatchData',
                                title        : _WINTITLE_MATCHDATA,
                                width        : 900,
                                height       : 400,
                                modal        : true,
                                closable     : true,
                                constrain    : true,
                                autoScroll   : true,
                                layout       : 'fit',
                                items        : gridMatchData
                            });     
                            winMatchData.show();
                            winMatchData.on('hide',function(){
                              if(Ext.getCmp(_dblIdMainGrid)) Ext.getCmp(_dblIdMainGrid).getStore().reload();
                            });
                          }else{

                          }
                        }, ///success
                        failure: function(o, resp){
                          w.close();
                          PMExt.error(_('ID_ERROR'), _MSG_ERROR);
                        }
                      });
                    }
                  } else {
                    Ext.MessageBox.show({ 
                      title   : '', 
                      msg     : _('ID_INVALID_EXTENSION') + ' ' + fileType,
                      buttons : Ext.MessageBox.OK,
                      animEl  : 'mb9', 
                      fn      : function(){},
                      icon    : Ext.MessageBox.ERROR
                    });
                  }
                }
            },{
              text    : TRANSLATIONS.ID_CANCEL,
              handler : function(){
                w.close();
              }
            }]
          })
        ]
      });
      w.show();
}

function loadDataStore(option,jsonreg, store, idInbox) {
  var myMask = new Ext.LoadMask(Ext.getBody(), {msg:_('ID_LOADING')});
  var _MSG_ERROR = 'Failure data load.';
  myMask.show();
  Ext.Ajax.request({
    url     : '../convergenceList/actions/doublonData.php',
    method  : "POST",
    params  : {"option": option, "registers": jsonreg,"idInbox":idInbox},           
    success:function (result, request) {
      myMask.hide();
       var resp=Ext.util.JSON.decode(result.responseText);
       if(typeof(resp.success) != 'undefined' && resp.success === true){
        store.loadData(Ext.util.JSON.decode(result.responseText));
       }else{
          PMExt.warning(_('ID_ERROR'), resp.message);
       }
    },
    failure:function (result, request) {
      myMask.hide();
      PMExt.error(_('ID_ERROR'), _MSG_ERROR);
    }
  });
};
function doublon(uidTask, fldNamStat, fldValStat){

  var _MSG_TITLE_MESSAGE  = 'Message';
  var _MSG_ERROR_CONFIG_ACTION_DOUBLON ='The doublon was not configured correctly, check your parameters please.';

  if(typeof(uidTask) === "undefined" || typeof(fldNamStat) === "undefined" || typeof(fldValStat) === "undefined") {
    PMExt.warning(_('ID_WARNING'),_MSG_ERROR_CONFIG_ACTION_DOUBLON);
    return true;
  }
  var  _SAVE_CLOSE                        = 'Save & Close';
  var _COMPLETE_DATA_SELECTION            = 'Complete data selection.';
  var _OPERATION_NO_COMPLETED             = 'The operation was not completed sucessfully!';
  var _MSG_TITLE_SAVE_DOUBLON             = 'Saving and creation of cases.';
  var _OPERATION_COMPLETED_SUCCESSFULLY   = 'Operation completed successfully.';
  var _WINTITLE_DOUBLON                   = "D&eacute;doublonner";//'Doublon';
  var _FAILURE_DATA_LOAD                  = 'Failure data load.';
  var _PREVIOUS_CONFIG_FIELDS_DOUBLON     = 'Previously must configure the fields that will be included in the doublon.';
  var _MSG_ERROR                          = 'An unexpected error occurred.';

  var winDoublon;
  var _dblColumns = new Array();
  var _dblFields  = new Array();
  var _registersOrder = new Array();
  var _jsonReg    = myApp.addTab_inside();
  var _registers  = Ext.util.JSON.decode(_jsonReg);
  var _dblIdInbox = myApp.getIdInbox();
  var _dblIdMainGrid = myApp.getIdMainGrid();
  var _columnsHeader;
  var _nonJsonresponse = new Array();
  var ite = 1;
  var waitLoading = {};
  // req
  var storeDuplicate;
  //
  waitLoading.show = function() {
    var mask = Ext.getBody().mask(_("ID_SAVING"), 'x-mask-loading', false);
    mask.setStyle('z-index', Ext.WindowMgr.zseed + 1000);
  };
  waitLoading.hide = function() {
    Ext.getBody().unmask();
  };

    
  Ext.Ajax.request({
    url     : '../convergenceList/actions/doublonData.php',
    method  : "POST",
    params  : {"option": 'isConfig', "idInbox": _dblIdInbox},           
    success:function (result, request) {
      var result = Ext.util.JSON.decode(result.responseText);
      
      if(result.configured === '1'){
          ///////////////////////////////////////////////////////
          column = {id : 'COL_DOUBLON0',header: '',dataIndex : 'COL_DOUBLON0',hidden:true};
          _dblColumns.push(column);
          _dblFields.push({name: 'COL_DOUBLON0'});
          ///description
          column = {
                  id        : 'COL_DOUBLON0_DESC',
                  header    : '',
                  width     : 250,//30,req
                  dataIndex : 'COL_DOUBLON0_DESC',
                  renderer : function(data, cell, record, rowIndex, columnIndex, store) {
                              cell.style= 'background-color:#DEDEDE;font-weight:bold;';
                              return data;
                  } 
          };  
          _dblColumns.push(column);  
          _dblFields.push({name: 'COL_DOUBLON0_DESC'});
          
          var numSelectedRecords = _registers.length;
        
          //################################################################################# MAKE DEDUBLONER
          if(numSelectedRecords == 1){
              
            var totalDuplicRecords = 0; 
            Ext.Ajax.request({
              url: '../convergenceList/actions/doublonData.php',
              method: "POST",
              params: {'option': 'totalDuplicRec', 'registers':_jsonReg, 'fldNamStat': fldNamStat, 'fldValStat':fldValStat},           
              success:function (result, request) {
                var resp = Ext.util.JSON.decode(result.responseText);
                
                if(typeof(resp.success)!= 'undefined' && resp.success === true){
                  ///// VERIF STATUT PRODUCT
                  totalDuplicRecords = (typeof(resp.total)!= 'undefined')?resp.total:0;
                  
                  if(typeof(resp.existCaseElected)!= 'undefined' && resp.existCaseElected ===true){
                    var _CaseElected = resp.caseElected;

                                    var _msg_make_dublon = totalDuplicRecords + " demande(s) trouvée(s) en doublon(s). <b><br /> La Demande N&deg;" + _CaseElected.APP_NUMBER + " a d&eacute;j&agrave; &eacute;t&eacute; produite</b>. Cette derni&egrave;re sera conserv&eacute; et les doublons correspondants supprim&eacute;. &Ecirc;tes-vous s&ucirc;r de vouloir continuer l&acute;op&eacute;ration?";
                    PMExt.confirm(_('ID_CONFIRM'),_msg_make_dublon, function(){
                      Ext.MessageBox.show({
                          msg : 'wait while eliminating repeated cases...',
                          width : 300,
                          wait : true,
                          waitConfig : {
                            interval : 200
                          }
                      });
                      Ext.Ajax.request({
                         url : '../convergenceList/actions/doublonData.php',
                         params: {'option': 'delCaseDuplic', 'appUidElected':_CaseElected.APP_UID, 'fldNamStat': fldNamStat, 'fldValStat':fldValStat, 'tableName': table},           
                         success: function (result, request) {
                           var response = Ext.util.JSON.decode(result.responseText);
                           if (response.success) {
                             Ext.MessageBox.hide();
                             if(Ext.getCmp(_dblIdMainGrid)) Ext.getCmp(_dblIdMainGrid).getStore().reload();
                             PMExt.notify(_('ID_INFO'), response.numDelCases + ' Cases deleted');
                           }
                           else {
                            Ext.MessageBox.hide();
                             PMExt.warning(_('ID_WARNING'), response.message);
                           }
                         },
                         failure: function (result, request) {
                          Ext.MessageBox.hide();
                          PMExt.error(_('ID_ERROR'), _MSG_ERROR);
                         }
                       });
                    });
                  }
                  ///// END VERIF STATUT PRODUCT
                  else{ ///// DONT'T EXIST STATUT PRODUCT
                  //***********************************************************************************
                      //totalDuplicRecords = totalDuplicRecords >50?50:totalDuplicRecords;
                      
                      for (var iCol =1;iCol<=totalDuplicRecords;iCol++){
                        column = {
                              id        : 'COL_DOUBLON' + ite,
                              header    : 'N&deg; ' + ite,
                              width     :  100,//25,req
                              dataIndex : 'COL_DOUBLON' + ite   
                        };  
                        _dblColumns.push(column);  
                        column = {
                              id        : 'COL_DOUBLON_CHK' + ite,
                              width     : 35, //3, req
                              sortable  : false,
                              resizable : false,
                              dataIndex : 'COL_DOUBLON_CHK'  + ite,
                              align     : 'center',
                              renderer  : function(data, cell, record, rowIndex, columnIndex, store) {
                                          var columnName = (record && record.data)? record.data.COL_DOUBLON0:'';
                                          if (columnIndex == 3)
                                            return "<input type='radio' name= '" + columnName + "' id= 'RB" + rowIndex + columnIndex + "' value='"+data+"' checked='checked'>";
                                          else
                                            return "<input type='radio' name= '" + columnName + "' id= 'RB" + rowIndex + columnIndex + "' value='"+data+"'>";
                                            
                                }   
                        };      
                        _dblColumns.push(column);
                        _dblFields.push({name: 'COL_DOUBLON_CHK' + ite});
                        _dblFields.push({name: 'COL_DOUBLON' + ite});
                        ite ++;        
                      }
                      storeDuplicate = new Ext.data.JsonStore({
                          url : '../convergenceList/actions/doublonData.php?registers=' + _jsonReg + '&option=dataInbox&idInbox='+ _dblIdInbox,
                          root : 'data',
                          totalProperty : 'total',
                          autoWidth : true,
                          fields : _dblFields
                      });      
                      var cmDoublon = new Ext.grid.ColumnModel({
                        defaults : {
                          width : 20,
                          sortable : true
                        },
                        columns : _dblColumns
                      });
                      cmDoublon.defaultSortable= true;  
                      var gridDoblon = new Ext.grid.GridPanel({
                        store       : storeDuplicate,
                        cm          : cmDoublon,
                        stripeRows  : true,
                        columnLines : true,
                        autoScroll  : true,
                        autoWidth   : true,
                        stateful    : true,
                        id          : 'gridDoblon',
                        layout      : 'fit' ,
                        viewConfig  : {
                            forceFit : false,
                            emptyText: ( _('ID_NO_RECORDS_FOUND') )
                        },
                        bbar: new Ext.PagingToolbar({
                          pageSize: 300,
                          store: storeDuplicate,
                          displayInfo: true,
                          displayMsg: _('ID_DISPLAY_ITEMS') + ' &nbsp; ',
                          emptyMsg: _('ID_DISPLAY_EMPTY')
                        }),
                        listeners: {
                            render: function(grid) {
                              gridDoblon.getView().el.select('.x-grid3-header').setStyle('background-color', '#DEDEDE');
                              gridDoblon.getView().el.select('.x-grid3-header').setStyle('border-top', 'solid 1px #FFF');
                              gridDoblon.store.on('load', function(store, records, options){
                                
                                var countColumn = gridDoblon.colModel.getColumnCount();
                                lengthGrid = gridDoblon.getStore().totalLength;
                             
                                for(var j = 0 ; j < lengthGrid ; j++)
                                {
                                    if(grid.store.data.items[j].data['COL_DOUBLON0'] == 'NUM_DOSSIER')
                                    {
                                        var rowNum = gridDoblon.getStore().getAt(j).fields;
                                            var countRow = gridDoblon.getStore().getAt(j).fields.length;
                                             nameChk = 'COL_DOUBLON_CHK';
                                         for (var h = 0; h < countRow ; h++)
                                         {
                                                 idFieldColumn = rowNum.keys[h];
                                                 subColName = idFieldColumn.substring(0,15);
                                                 
                                                 for(var i = 2; i < countColumn; i++){
                                                        
                                                        idColumn = gridDoblon.colModel.getColumnId(i);
                                                        if((idColumn == idFieldColumn) && (subColName != nameChk))
                                                     {
                                                         name = gridDoblon.getStore().getAt(j).data[idFieldColumn];
                                                         gridDoblon.getColumnModel().setColumnHeader(i, 'Dossier N&deg; '+ name);
                                                         break;
                                                     }
                                                            
                                                    }
                                         }
                                        
                                    }
                                }
                                   
                                //gridDoblon.getView().getRow(0).style.display = 'none';      
                                }); 
                              
                              } ,
                             
                             cellcontextmenu : function(grid, rowIndex, cellIndex, event) {
                                 var colName = grid.getColumnModel().getDataIndex(cellIndex);
                                 var nameDesc = 'COL_DOUBLON'+cellIndex+'_DESC';
                                 var nameChk = 'COL_DOUBLON_CHK'+cellIndex;
                                 if(colName != nameDesc && colName != nameChk)
                                 {
                                     event.stopEvent();
                                     var record = grid.getStore().getAt(rowIndex);
                                     
                                       //console.log(record);
                                       var menu = new Ext.menu.Menu({
                                             items: [{
                                                 text: 'Delete Column',
                                                 handler: function() {
                                                 
                                                 //console.log(cellIndex);
                                                 var cell = grid.store.data.items[rowIndex].data[grid.store.data.items[rowIndex].fields.keys[cellIndex]];
                                                    grid.getColumnModel().setHidden(cellIndex, true);
                                                    grid.getColumnModel().setHidden(cellIndex+1, true);
                                                 }
                                             }]
                                         }).showAt(event.xy)
                                     
                                       var lengthGrid = grid.store.data.length;
                                       var cell = grid.store.data.items[rowIndex].data[grid.store.data.items[rowIndex].fields.keys[cellIndex]];
                                      //Ext.MessageBox.alert("debug", String.format("{0}", grid.store.data.items[rowIndex].data[grid.store.data.items[rowIndex].fields.keys[cellIndex]]));
                                 
                                       //grid.getColumnModel().setHidden(cellIndex, true);
                                 }
                           } 
                        },
                        tbar : [{
                          text: _SAVE_CLOSE,
                          iconCls: 'button_menu_ext ss_sprite ss_accept',
                          handler: function() {
                            
                              //Begin hidden Delete Column
                            var hiddenNumDossier = new Array();  
                            var hiddenColumns = new Array();
                            var columns = gridDoblon.colModel.columns;
                            nameChk = 'COL_DOUBLON_CHK';
                            colAux = new Array();
                            lengthGrid = gridDoblon.getStore().totalLength;
                            
                                for(var j = 0 ; j < lengthGrid ; j++)
                                {
                                    if(gridDoblon.store.data.items[j].data['COL_DOUBLON0'] == 'NUM_DOSSIER')
                                    {
                                        var rowNum = gridDoblon.getStore().getAt(j).fields;
                                      var countRow = gridDoblon.getStore().getAt(j).fields.length;
                                    
                                    for (var i = 1; columns.length > i; i++) {
                                        //console.log(columns[i]);
                                        //var cell = grid.store.data.items[rowIndex].data[grid.store.data.items[rowIndex].fields.keys[cellIndex]];
                                         
                                         if (columns[i].hidden == true) {
                                             colName = columns[i].dataIndex;
                                             subColName = colName.substring(0,15);
                                             if(subColName != nameChk)
                                             {   //_registers
                                                 colAux = {'NUM_DOSSIER' : gridDoblon.store.data.items[j].data[colName]};
                                                 hiddenNumDossier.push(colAux);
                                             }
                                         }
                                    }
                                    }
                                }
                            Ext.each(_registers, function(item){
                                for(i = 0;i < hiddenNumDossier.length;i++)
                                {
                                    if(item.NUM_DOSSIER == hiddenNumDossier[i]['NUM_DOSSIER']){
                                        colAux = {'APP_UID' : item.APP_UID,
                                                  'NUM_DOSSIER': item.NUM_DOSSIER
                                                 };
                                        hiddenColumns.push(colAux);
                                    }   
                                }
                            });
                            hiddenColumns = Ext.encode(hiddenColumns);
                            
                            // End hidden Delete Column
                            
                            _columnsHeader =  new Array();
                            gridDoblon.getStore().each(function(rec){ 
                              _columnsHeader.push(rec.get('COL_DOUBLON0'));
                            })
                            var _jsonresponse = '';
                            var found = 1;
                            Ext.each(_columnsHeader, function(item){
                              var radios = Ext.DomQuery.select('input[name='+item+']');
                              found = 1;
                              for (var i = 0; i < radios.length; i++) {
                                  
/*                               if(radios.length > 3)
                                     columnHidden = gridDoblon.colModel.columns[i+3].hidden; //  && columnHidden != true
                                 else
                                     columnHidden = i;*/
                                 
                                  if (radios[i].checked) {
                                      _jsonresponse=_jsonresponse +  ',"'+item +'":"'+radios[i].value+'"';
                                      found = 0;
                                      break;
                                  }
                              }
                              if(found === 1){
                                PMExt.warning(_('ID_WARNING'), _COMPLETE_DATA_SELECTION);
                                return false;
                              }
                            });
                            if(found ==0 ){
                                 waitLoading.show();
                                 _jsonresponse ='{"name":"' + 'req' + '"' + _jsonresponse + '}';
                                 var appUidFirst = (_registers[0]['APP_UID'])?_registers[0]['APP_UID']:'0';
                                 Ext.Ajax.request({
                                    url: '../convergenceList/actions/doublonData.php',
                                    method: "POST",
                                    params: {"option": 'createCase', "dblAppUid": appUidFirst, "appData": _jsonresponse, "uidTask": uidTask, "registers" :_jsonReg , "hiddenColumn" : hiddenColumns , "idInbox": _dblIdInbox},           
                                    success:function (result, request) {
                                        waitLoading.hide();
                                        var resp=Ext.util.JSON.decode(result.responseText);
                                        if(typeof(resp.success)!= 'undefined' && resp.success === true){
                                          winDoublon.close();
                                          PMExt.notify(_MSG_TITLE_SAVE_DOUBLON, _OPERATION_COMPLETED_SUCCESSFULLY);
                                          if(Ext.getCmp(_dblIdMainGrid)) Ext.getCmp(_dblIdMainGrid).getStore().reload();
                                        }else{
                                          PMExt.error(_('ID_ERROR'), resp.message);
                                        }
                                    },
                                    failure:function (result, request) {
                                        waitLoading.hide();
                                        PMExt.error(_('ID_ERROR'), _OPERATION_NO_COMPLETED);
                                    }
                                  });
                              }
                          }   
                        },
                        '-',
                        {
                          text: _('ID_CANCEL'),
                          iconCls: 'button_menu_ext ss_sprite ss_cancel',
                          handler: function() {winDoublon.close();}
                        },
                        '-'
                        ]
                      });     
                      ///////////// end grid  
                      winDoublon = new Ext.Window({
                      closeAction : 'hide',
                      autoDestroy : true,
                      maximizable: true,
                      id: 'winDoublon',
                      title: _WINTITLE_DOUBLON,
                      width : 900,
                      height : 400,
                      modal : true,
                      closable:true,
                      constrain:true,
                      autoScroll:true,
                      layout: 'fit',
                      items: gridDoblon
                      });     
                      winDoublon.show();
                      winDoublon.maximize();
                      winDoublon.toFront(); 
                      loadDataStore('dataInbox',_jsonReg, storeDuplicate, _dblIdInbox);
                  }/////// END DONT'T EXIST STATUT PRODUCT
                  //***********************************************************************************
                }else{
                  PMExt.warning(_('ID_ERROR'), resp.message);
                } 
              },
              failure:function (result, request) {
                PMExt.error(_('ID_ERROR'), _MSG_ERROR);
              }
            });
            
          //################################################################################### NORMAL
          }else{  
              var swField = 0;
              
            Ext.each(_registers, function(item){
                //console.log(item);
              //columns data
              column = {
                    id        : 'COL_DOUBLON' + ite,
                    header    : 'Dossier N¬∞ ' + ite,
                    width     : 100,
                    dataIndex : 'COL_DOUBLON' + ite 
              };  
              _dblColumns.push(column);  
              //columns radio
              column = {
                    id        : 'COL_DOUBLON_CHK' + ite,
                    width     : 35,
                    sortable  : false,
                    resizable : false,
                    dataIndex : 'COL_DOUBLON_CHK'  + ite,
                    align     : 'center',
                    renderer  : function(data, cell, record, rowIndex, columnIndex, store) {
                                var columnName = (record && record.data)? record.data.COL_DOUBLON0:'';
                                swField = 0;
                                if(columnName != 'NUMDOSSIER_HEADER')
                                {   
                                    if (columnIndex == 3)
                                        return "<input type='radio' name= '" + columnName + "' id= 'RB" + rowIndex + columnIndex + "' value='"+data+"' checked='checked'>";
                                    else
                                        return "<input type='radio' name= '" + columnName + "' id= 'RB" + rowIndex + columnIndex + "' value='"+data+"'>";
                                }
                                else
                                    swField = 1;
                  }   
              };  
              if(swField != 1)
              {
                  _dblColumns.push(column);
                  _dblFields.push({name: 'COL_DOUBLON_CHK' + ite});
                  _dblFields.push({name: 'COL_DOUBLON' + ite});
              }
              ite ++;        
            });
            
            storeDuplicate = new Ext.data.JsonStore({
                url : '../convergenceList/actions/doublonData.php?registers=' + _jsonReg + '&option=dataInbox&idInbox='+ _dblIdInbox,
                root : 'data',
                totalProperty : 'total',
                autoWidth : true,
                fields : _dblFields
            });      
            var cmDoublon = new Ext.grid.ColumnModel({
              defaults : {
                width    : 20,
                sortable : true
              },
              columns : _dblColumns,
              listeners: {
                  widthchange: function(cm, colIndex, width) {
                    console.log(colIndex);
                  }
              }
            });
            cmDoublon.defaultSortable = true;  
            var gridDoblon = new Ext.grid.GridPanel({
              store       : storeDuplicate,
              cm          : cmDoublon,
              stripeRows  : true,
              columnLines : true,
              autoScroll  : true,
              id          : 'gridDoblon', 
              viewConfig  : {
                  forceFit : true,
                  scrollOffset : 0,
                  emptyText: ( _('ID_NO_RECORDS_FOUND') )
              },
              bbar: new Ext.PagingToolbar({
                pageSize: 300,
                store: storeDuplicate,
                displayInfo: true,
                displayMsg: _('ID_DISPLAY_ITEMS') + ' &nbsp; ',
                emptyMsg: _('ID_DISPLAY_EMPTY')
              }),
              listeners: {
                  render: function(grid) {
                    gridDoblon.getView().el.select('.x-grid3-header').setStyle('background-color', '#DEDEDE');
                    gridDoblon.getView().el.select('.x-grid3-header').setStyle('border-top', 'solid 1px #FFF');
                    
                    gridDoblon.store.on('load', function(store, records, options){
                        
                        var countColumn = gridDoblon.colModel.getColumnCount();
                         lengthGrid = gridDoblon.getStore().totalLength;
                         
                            for(var j = 0 ; j < lengthGrid ; j++)
                            {
                                if(grid.store.data.items[j].data['COL_DOUBLON0'] == 'NUM_DOSSIER')
                                {
                                    var rowNum = gridDoblon.getStore().getAt(j).fields;
                                    var countRow = gridDoblon.getStore().getAt(j).fields.length;
                                     nameChk = 'COL_DOUBLON_CHK';
                                     for (var h = 0; h < countRow ; h++)
                                     {
                                             idFieldColumn = rowNum.keys[h];
                                             subColName = idFieldColumn.substring(0,15);
                                             
                                             for(var i = 2; i < countColumn; i++){
                                                
                                                idColumn = gridDoblon.colModel.getColumnId(i);
                                                if((idColumn == idFieldColumn) && (subColName != nameChk))
                                                 {
                                                     name = gridDoblon.getStore().getAt(j).data[idFieldColumn];
                                                     gridDoblon.getColumnModel().setColumnHeader(i, 'Dossier N&deg; '+ name);
                                                     break;
                                                 }
                                                    
                                            }
                                     }
                                }
                            }
                         
                        //gridDoblon.getView().getRow(0).style.display = 'none';
                        
                        //gridDoblon.getStore().removeAt(0);
                      }); 
                    
                  },
                   
                   cellcontextmenu : function(grid, rowIndex, cellIndex, event) {
                     
                     var colName = grid.getColumnModel().getDataIndex(cellIndex);
                     var nameDesc = 'COL_DOUBLON0_DESC';
                     var nameChk = 'COL_DOUBLON_CHK';
                     var subColName = colName.substring(0,15);
                     if(colName != nameDesc && subColName != nameChk)
                     {
                         event.stopEvent();
                         var record = grid.getStore().getAt(rowIndex);
                         
                           var menu = new Ext.menu.Menu({
                                 items: [{
                                     text: 'Delete Column',
                                     handler: function() {
                                      grid.getColumnModel().setHidden(cellIndex, true);
                                      grid.getColumnModel().setHidden(cellIndex+1, true);
                                   }
                                 }]
                             }).showAt(event.xy)
                         
                         var lengthGrid = grid.store.data.length;
                         var cell = grid.store.data.items[0].fields.keys[cellIndex];
                          
                         _columnsHeader =  new Array();
                         gridDoblon.getStore().each(function(rec){ 
                           _columnsHeader.push(rec.get('COL_DOUBLON0'));
                         })
                       
                         var _jsonresponse = '';
                         var found = 1;
                         i = 0;
                         var _nonJsonresponse = new Array();
                         Ext.each(_columnsHeader,function(item){
                             var cell = grid.store.data.items[i].fields.keys[cellIndex];
                             _nonJsonresponse = _nonJsonresponse +  ',"'+item +'":"'+grid.store.data.items[i].data[colName]+'"';
                             i++; 
                         });  
                     }
                     
                } 
                  
              },
              tbar : [{
                text: _SAVE_CLOSE,
                iconCls: 'button_menu_ext ss_sprite ss_accept',
                handler: function() {
                
                  //Begin hidden Delete Column
                var hiddenNumDossier = new Array();  
                var hiddenColumns = new Array();
                var columns = gridDoblon.colModel.columns;
                nameChk = 'COL_DOUBLON_CHK';
                colAux = new Array();
                lengthGrid = gridDoblon.getStore().totalLength;
                
                    for(var j = 0 ; j < lengthGrid ; j++)
                    {
                        if(gridDoblon.store.data.items[j].data['COL_DOUBLON0'] == 'NUM_DOSSIER')
                        {
                            var rowNum = gridDoblon.getStore().getAt(j).fields;
                          var countRow = gridDoblon.getStore().getAt(j).fields.length;
                        
                        for (var i = 1; columns.length > i; i++) {
                            //console.log(columns[i]);
                            //var cell = grid.store.data.items[rowIndex].data[grid.store.data.items[rowIndex].fields.keys[cellIndex]];
                             
                             if (columns[i].hidden == true) {
                                 colName = columns[i].dataIndex;
                                 subColName = colName.substring(0,15);
                                 if(subColName != nameChk)
                                 {   //_registers
                                     colAux = {'NUM_DOSSIER' : gridDoblon.store.data.items[j].data[colName]};
                                     hiddenNumDossier.push(colAux);
                                 }
                             }
                        }
                        }
                    }
                Ext.each(_registers, function(item){
                    for(i = 0;i < hiddenNumDossier.length;i++)
                    {
                        if(item.NUM_DOSSIER == hiddenNumDossier[i]['NUM_DOSSIER']){
                            colAux = {'APP_UID' : item.APP_UID,
                                      'NUM_DOSSIER': item.NUM_DOSSIER
                                     };
                            hiddenColumns.push(colAux);
                        }   
                    }
                });
                hiddenColumns = Ext.encode(hiddenColumns);
                
                // End hidden Delete Column
                
                _columnsHeader =  new Array();
                  gridDoblon.getStore().each(function(rec){ 
                    _columnsHeader.push(rec.get('COL_DOUBLON0'));
                  })
                  var _jsonresponse = '';
                  var found = 1;

                Ext.each(_columnsHeader, function(item){
                        var radios = Ext.DomQuery.select('input[name='+item+']');
                          found = 1;
                          for (var i = 0; i < radios.length; i++) {
                             
                              /*if(radios.length > 3)
                                    columnHidden = gridDoblon.colModel.columns[i+3].hidden; //  && columnHidden != true
                                else
                                    columnHidden = i;*/
                              if (radios[i].checked) {
                                  _jsonresponse =_jsonresponse +  ',"'+item +'":"'+radios[i].value+'"';
                                  found = 0;
                                  break;
                              }
                          }
                          if(found === 1){
                            PMExt.warning(_('ID_WARNING'), _COMPLETE_DATA_SELECTION);
                            return false;
                          }
                    
                    });
                  if(found ==0 ){
                       waitLoading.show();
                       _jsonresponse ='{"name":"' + 'req' + '"' + _jsonresponse + '}';
                       var appUidFirst = (_registers[0]['APP_UID'])?_registers[0]['APP_UID']:'0';
                       Ext.Ajax.request({
                          url: '../convergenceList/actions/doublonData.php',
                          method: "POST",
                          params: {"option": 'createCase', "dblAppUid": appUidFirst, "appData": _jsonresponse, "uidTask": uidTask, "registers" :_jsonReg ,"hiddenColumn" : hiddenColumns , "idInbox": _dblIdInbox},           
                          success:function (result, request) {
                              waitLoading.hide();
                              var resp=Ext.util.JSON.decode(result.responseText);
                              if(typeof(resp.success)!= 'undefined' && resp.success === true){
                                winDoublon.close();
                                PMExt.notify(_MSG_TITLE_SAVE_DOUBLON, _OPERATION_COMPLETED_SUCCESSFULLY);
                                if(Ext.getCmp(_dblIdMainGrid)) Ext.getCmp(_dblIdMainGrid).getStore().reload();
                              }else{
                                PMExt.error(_('ID_ERROR'), resp.message);
                              }
                          },
                          failure:function (result, request) {
                              waitLoading.hide();
                              PMExt.error(_('ID_ERROR'), _OPERATION_NO_COMPLETED);
                          }
                       });
                  }
                }   
              },
              '-',
              {
                text: _('ID_CANCEL'),
                iconCls: 'button_menu_ext ss_sprite ss_cancel',
                handler: function() {winDoublon.close();}
              },
              '-'
              ]
            });     
            
            ///////////// end grid    
            winDoublon = new Ext.Window({
              closeAction : 'hide',
              autoDestroy : true,
              maximizable: true,
              id: 'winDoublon',
              title: _WINTITLE_DOUBLON,
              width : 900,
              height : 400,
              modal : true,
              closable:true,
              constrain:true,
              autoScroll:true,
              layout: 'fit',
              items: gridDoblon
            });     
            winDoublon.show();
            winDoublon.maximize();
            winDoublon.toFront(); 
            loadDataStore('dataInbox',_jsonReg, storeDuplicate, _dblIdInbox);

          } // end else row selected >1
        }
        else{
          Ext.Msg.show({
            title: _MSG_TITLE_MESSAGE,
            msg : _PREVIOUS_CONFIG_FIELDS_DOUBLON,
            buttons: Ext.Msg.OK,
            fn: function(choice) {configDoublon();},
            icon: Ext.Msg.INFO
          });  
        }
      //////////////////////////////////////////////////////////////
    },
    failure:function (result, request) {
      PMExt.error(_('ID_ERROR'), _FAILURE_DATA_LOAD);
    }
  });
  
}
////////////////////////////////////////////////////////
function configDoublon(){
      
      var _MSG_TITLE_SAVE_CONFIG_DOUBLON  = "Enregistrer les modifications"; //"Save Changes";
      var _MSG_TITLE_SAVE_RESET_DOUBLON   = "Reset Configuration Doublon"; //"Save Changes";
      var _OPERATION_NO_COMPLETED         = "L'op&eacute;ration n'a pas &eacute;t&eacute; compl&eacute;t&eacute;e avec succ&egrave;s!";//'The operation was not completed sucessfully!';
      var _WINTITLE_CONFIG_DOUBLON        = "D&eacute;doublonner Configuration";//'Doublon Configuration';
      var _DATA_SAVED_OK                  = "Les donn&eacute;es ont &eacute;t&eacute; enregistr&eacute;es avec succ&egrave;s!";//'The data was saved sucessfully!';
      var _TITLE_GRID_COLUMN_LIST         = "Liste des colonnes"; //'COLUMNS LIST';
      var _SELECTED_ITEMS                 = "S&eacute;lectionnez les &eacute;l&eacute;ments merci.";//'Select Items please..';
      var _RESET_SAVED_OK                 = "Reset fields sucessfully";

      _dblIdInbox = myApp.getIdInbox();
      _dblProUid  = myApp.getProUid();
      var winConfigDoublon;
      
      var storeConfigDoublon = new Ext.data.JsonStore({
          url           : '../convergenceList/actions/doublonData.php?option=configDoublon&idInbox=' + _dblIdInbox+ '&proUid=' + _dblProUid,
          root          : 'data',
          totalProperty : 'total', 
          remoteSort    : true,
          autoWidth     : true,
          fields        : ['FIELD_NAME',
                           'FIELD_NAME_UPPER',
                           'FIELD_DESC', 
                           'TYPE',
                           'FIELD_POSITION',
                           'TYPE_CONFIGURATION',
                           'CONFIG_PMTABLE',
                           'CONFIG_FIELD_SHOW',
                           'CONFIG_FIELD_CONDITION',
                           {name: 'FIELD_INCLUDE', type: 'bool',convert:function(v)
                            {return (v === "1" || v === true) ? true : false;}}]
      });
      storeConfigDoublon.load();
      var pager = new Ext.PagingToolbar({
          store       : storeConfigDoublon, 
          displayInfo : true,
          autoHeight  : true,
          displayMsg  : _('ID_DISPLAY_ITEMS') + ' &nbsp; ',
          emptyMsg    : _('ID_DISPLAY_EMPTY'),
          pageSize    : 500
      });  
      
      var checkColumnInclude = new Ext.grid.CheckColumn({
        header    : 'Include ?',
        dataIndex : 'FIELD_INCLUDE',
        id        : 'check',
        flex      : 1,
        width     : 10,
        processEvent: function () { return false; }
      });
       var txtDescription =  new Ext.form.TextField ({
        allowBlank : true,
        height     : 50,
        disabled   : true,
        editable   : false,
        anchor     : '100%'
      });
       
      var gridcolumns = new Ext.grid.ColumnModel({
        defaults : {
            width    : 20,
            sortable : true
        },
        columns : [
        new Ext.grid.RowNumberer(),
        {
          dataIndex : 'FIELD_NAME',
          hidden    :true
        },
        {
          dataIndex : 'TYPE',
          hidden    :true
        },
       {
          header    : "Field Name",
          width     : 15,
          sortable  : true,
          dataIndex : 'FIELD_NAME_UPPER'
        }, {
          header    : "Field Description",
          width     : 15,
          sortable  : true,
          dataIndex : 'FIELD_DESC',
          editor    : txtDescription
        }, checkColumnInclude ,
        {
        header    : "Configuration",
            width     : 10,
            sortable  : true,
            dataIndex : 'TYPE_CONFIGURATION',
            hidden    : false
        } , {
            header    : "Table",
            width     : 10,
            sortable  : true,
            dataIndex : 'CONFIG_PMTABLE',
            hidden    : true
        } , {
            header    : "Field",
            width     : 10,
            sortable  : true,
            hidden    : true,
            dataIndex : 'CONFIG_FIELD_SHOW'
        } , {
            header    : "Condition",
            width     : 10,
            sortable  : true,
            hidden    : true,
            dataIndex : 'CONFIG_FIELD_CONDITION'
        }]
      });
    
      var waitLoading = {};
      waitLoading.show = function() {
        var mask = Ext.getBody().mask(_("ID_SAVING"), 'x-mask-loading', false);
        mask.setStyle('z-index', Ext.WindowMgr.zseed + 1000);
      };
      waitLoading.hide = function() {
        Ext.getBody().unmask();
      };
      var gridFieldCustomDoublon = new Ext.grid.EditorGridPanel({
        store           : storeConfigDoublon,
        //disableSelection: true,
        columnLines     : true,
        id              : 'grid',
        ddGroup         : 'gridDD',
        enableDragDrop  : true,
        cm              : gridcolumns,
        selType         : 'rowmodel',
        clicksToEdit    : 1,
        listeners: {
              "render": {
                scope: this,
                fn: function(grid) {
                    
                    var ddrow = new Ext.dd.DropTarget(grid.container, {
                        ddGroup : 'gridDD',
                        copy:false,
                        notifyDrop : function(dd, e, data){
                            var ds = grid.store;
                            var sm = gridFieldCustomDoublon.getSelectionModel();
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
               });
               
            gridFieldCustomDoublon.store.on('load', function(store, records, options){
                                
              lengthGrid = gridFieldCustomDoublon.getStore().totalLength;
              for(var i=0;i < lengthGrid; i++)
              {
                if(gridFieldCustomDoublon.store.data.items[i].data.FIELD_NAME == 'NUM_DOSSIER')
                {
                    gridFieldCustomDoublon.getView().getRow(i).style.display = 'none';      
                }
              }
              /*Ext.each(records, function(item){
                
                console.log(item);
                });*/
           }); 
                //store.load();
            }
          } 
        },
        tbar : [{
          text: _('ID_SAVE_CHANGES'),
          iconCls :'button_menu_ext ss_save',
          handler: function() {

              var _dblFieldsCustom = new Array ();
              var _jsonFieldsCustom  = '';
              var _dblFlag=false;
              
              storeConfigDoublon.each(function(record)  { 
                var _dblInclude = (record.get('FIELD_INCLUDE') == true) ? '1':'0';
                
                var typeAs = 0;
                
                if(record.get('TYPE_CONFIGURATION') == 'Yes-No')
                    typeAs = 1;
                
                if(record.get('TYPE_CONFIGURATION') == 'Query')
                    typeAs = 2;
                
                if(record.get('FIELD_INCLUDE')) _dblFlag =true;
                var item = {
                    "FIELD_NAME"      : record.get('FIELD_NAME'),
                    "FIELD_DESC"      : record.get('FIELD_DESC'),
                    "FIELD_POSITION"  : record.get('FIELD_POSITION'),
                    "FIELD_INCLUDE"   : _dblInclude,
                    "TYPE_CONFIGURATION"      : typeAs,
                    "CONFIG_PMTABLE"          : record.get('CONFIG_PMTABLE'),
                    "CONFIG_FIELD_SHOW"       : record.get('CONFIG_FIELD_SHOW'),
                    "CONFIG_FIELD_CONDITION"  : record.get('CONFIG_FIELD_CONDITION')
                };
                _dblFieldsCustom.push(item);
              });
              
              if(_dblFlag){
                _jsonFieldsCustom= Ext.util.JSON.encode(_dblFieldsCustom);
                waitLoading.show();
                Ext.Ajax.request({
                    params : {        
                    fieldsDoublon : _jsonFieldsCustom,
                    idInbox : _dblIdInbox
                    },
                    url : '../convergenceList/actions/doublonData.php?option=saveConfigDoublon',
                    success : function(result, request) {
                      waitLoading.hide();
                      var resp=Ext.util.JSON.decode(result.responseText);
                       if(typeof(resp.success)!= 'undefined' && resp.success ==true){
                        winConfigDoublon.close();
                        PMExt.notify(_MSG_TITLE_SAVE_CONFIG_DOUBLON, _DATA_SAVED_OK);
                       }else{
                          PMExt.error(_('ID_ERROR'), resp.message);
                       }
                    },
                    failure : function() {
                      waitLoading.show();
                      PMExt.error(_('ID_ERROR'), _OPERATION_NO_COMPLETED);
                    }
                });
              }
              else{
                PMExt.info(_('ID_INFO'), _SELECTED_ITEMS);
              }///
          } 
        },
        '-',
        {
          text: _('ID_CANCEL'),
          iconCls: 'button_menu_ext ss_sprite ss_cancel',
          handler: function() {winConfigDoublon.close();}
        },
        '-',
        {
              text: 'Reset Configuration',
              iconCls :'button_menu_ext cvrgl_reset',
              handler: function() {
                    waitLoading.show();
                    Ext.Ajax.request({
                        params : {        
                        idInbox : _dblIdInbox,
                        proUid : _dblProUid
                        },
                        url : '../convergenceList/actions/doublonData.php?option=resetConfigDoublon',
                        success : function(result, request) {
                          waitLoading.hide();
                          var resp=Ext.util.JSON.decode(result.responseText);
                           if(typeof(resp.success)!= 'undefined' && resp.success ==true){
                            //winConfigDoublon.close();
                               storeConfigDoublon.load();
                            PMExt.notify(_MSG_TITLE_SAVE_RESET_DOUBLON, _RESET_SAVED_OK);
                           }else{
                              PMExt.error(_('ID_ERROR'), resp.message);
                           }
                        },
                        failure : function() {
                          waitLoading.show();
                          PMExt.error(_('ID_ERROR'), _OPERATION_NO_COMPLETED);
                        }
                    });
                  }
          }///
                
        ],
        columnLines    : true,
        plugins        : checkColumnInclude,
        title          : '<center><b> ' + _TITLE_GRID_COLUMN_LIST + ' </b></center>',
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
        bbar           : pager,
        selModel       : new Ext.grid.RowSelectionModel({
                            singleSelect : true,
                            listeners: {
                                rowselect: function(sm, row, rec) {
                                    Ext.getCmp("gridFormConfig").getForm().loadRecord(rec);
                                    if(rec.get('TYPE_CONFIGURATION') == 0)
                                    {
                                        Ext.getCmp('idTypeConfiguration').setValue(0);
                                        Ext.getCmp('idTypeConfiguration').setRawValue('Select');
                                    }
                                    else
                                        Ext.getCmp('idTypeConfiguration').setValue(rec.get('TYPE_CONFIGURATION'));
                                    
                                    TableCombo=Ext.getCmp('idTableCombo');
                                FieldCombo=Ext.getCmp('idFieldCombo');
                                    FieldConditionCombo=Ext.getCmp('idFieldConditionCombo');
                                    
                                    if(rec.get('TYPE_CONFIGURATION') == 'Query')
                                    {
                                            TableCombo.setVisible(true);
                                            Ext.getCmp('idTableCombo').setValue(rec.get('CONFIG_PMTABLE'));
                                            FieldCombo.setVisible(true);
                                            Ext.getCmp('idFieldCombo').setValue(rec.get('CONFIG_FIELD_SHOW'));
                                            FieldConditionCombo.setVisible(true);
                                            Ext.getCmp('idFieldConditionCombo').setValue(rec.get('CONFIG_FIELD_CONDITION'));
                                    }
                                    else
                                    {
                                            TableCombo.setVisible(false);
                                            TableComboStore.load();
                                            TableCombo.clearValue();
                                            
                                            FieldCombo.setVisible(false);
                                            FieldComboStore.load();
                                            FieldCombo.clearValue();
                                            
                                            FieldConditionCombo.setVisible(false);
                                            FieldComboStore.load();
                                            FieldCombo.clearValue();
                                    }
                                }
                            }
                        }),
        width          : 620,
        height         : 300,  
        viewConfig     : {
          forceFit     : true,
          emptyText    : ( _('ID_NO_RECORDS_FOUND')),
          scrollOffset : 2
        }
      });
      
      var bd = Ext.getBody();
      bd.createChild({tag: 'h2', html: 'Using a Grid with a Form'});

      var TableComboStore = new Ext.data.Store({
            proxy : new Ext.data.HttpProxy({url: '../convergenceList/ajaxTableCombo.php?TYPE=TableCombo'}),
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

        var TableCombo = new Ext.form.ComboBox({
            valueField    : 'ID',
            displayField  : 'NAME',
            id            : 'idTableCombo',
            fieldLabel    : 'Select Table',
            emptyText     : 'Select a Table...',
            typeAhead     : true,
            triggerAction : 'all',
            editable      : false,
            mode          : 'local',
            width         : 200,
            allowBlank    : false,
            store         : TableComboStore,
            name          : 'idTableCombo',
            hiddenName    : 'idTableCombo',
            disabled      : false,
            selectOnFocus : false,
            hidden        : true, 
            listeners     :{
                select : function(combo, record) {
                    FieldCombo.setDisabled(false);
                    FieldConditionCombo.setDisabled(false);
                    FieldComboAux=Ext.getCmp('idFieldCombo');
                    FieldComboConditionAux=Ext.getCmp('idFieldConditionCombo');
                    FieldComboAux.clearValue();
                    FieldComboConditionAux.clearValue();
                    var idTable = combo.getValue(); 
                    FieldComboStore.load({
                        params : {
                        idTable : idTable
                        }
                    });
                }  
            }
        });
        
        
          var FieldComboStore = new Ext.data.Store({
                proxy : new Ext.data.HttpProxy({url: '../convergenceList/ajaxFieldCombo.php?TYPE=FieldCombo'}),
                reader : new Ext.data.JsonReader({
                    root   : 'data',
                    fields : [
                        {name : 'ID'},
                        {name : 'NAME'},
                        {name : 'INNER_JOIN'}
                    ]
                })
            });
            

            var FieldCombo = new Ext.form.ComboBox({
                valueField    : 'ID',
                displayField  : 'NAME',
                id            : 'idFieldCombo',
                fieldLabel    : 'Select Show Field',
                emptyText     : 'Select a Field...',
                typeAhead     : true,
                triggerAction : 'all',
                editable      : false,
                mode          : 'local',
                width         : 200,
                allowBlank    : false,
                store         : FieldComboStore,
                name          : 'idFieldCombo',
                hiddenName    : 'idFieldCombo',
                disabled      : true,
                selectOnFocus : false,
                hidden        : true,
                listeners     :{
                    select : function(combo, record) {
                    }  
                }
            });
            
            var FieldConditionCombo = new Ext.form.ComboBox({
                valueField    : 'ID',
                displayField  : 'NAME',
                id            : 'idFieldConditionCombo',
                fieldLabel    : 'Select Field Condition',
                emptyText     : 'Select a Field Condition...',
                typeAhead     : true,
                triggerAction : 'all',
                editable      : false,
                mode          : 'local',
                width         : 200,
                allowBlank    : false,
                store         : FieldComboStore,
                name          : 'idFieldConditionCombo',
                hiddenName    : 'idFieldConditionCombo',
                disabled      : true,
                selectOnFocus : false,
                hidden        : true,
                listeners     :{
                    select : function(combo, record) {
                    }  
                }
            });
    
        var typeAS = new Ext.form.ComboBox({
            valueField    : 'ID',
            displayField  : 'NAME',
            id            : 'idTypeConfiguration',
            fieldLabel    : 'Type Configuration',
          editable    : false,
            typeAhead     : true,
            triggerAction : 'all',
            mode          : 'local',
            width         : 200,
            autoHeight    : true,
            listWidth     : 250,
            allowBlank    : false,
            disabled      : false,
            defaultValue  : "0",
            value         : "0",
            store: new Ext.data.SimpleStore({
                    fields: ["ID", "NAME"],
                    data : [["0","Select"],
                            ["1","Yes-No"],
                            ["2","Query"]
                           ]
                }),
            listeners   :{
                   load: function () {
                       var combo = Ext.getCmp('idTypeConfiguration');
                        combo.setValue("0");
                        combo.setRawValue("Select");
                        TableCombo.setVisible(false);
                        FieldCombo.setVisible(false);
                        FieldConditionCombo.setVisible(false);
                    } ,
                    select : function(combo, record) {
                        TableCombo=Ext.getCmp('idTableCombo');
                        FieldCombo=Ext.getCmp('idFieldCombo');
                        FieldConditionCombo=Ext.getCmp('idFieldConditionCombo');
                        if(combo.getValue() == '2')
                        {
                            TableCombo.setVisible(true);
                            FieldCombo.setVisible(true);
                            FieldConditionCombo.setVisible(true);
                        }
                        else
                        {
                            TableCombo.setVisible(false);
                            FieldCombo.setVisible(false);
                            FieldConditionCombo.setVisible(false);
                        }
                        
                        
                        
                    }
            }
        });
        
        
        var gridForm = new Ext.FormPanel({
              id: 'gridFormConfig',
              frame: true,
              labelAlign: 'left',
              title: 'Fields Configuration',
              bodyStyle:'padding:5px',
              width: 1020,
              height: 320,  
              layout: 'column',  
              items: [gridFieldCustomDoublon,{
                  columnWidth: 0.9,
                  xtype: 'fieldset',
                  labelWidth: 100,
                  title:'Fields details',
                  defaults: {width: 270, border:false},
                  defaultType: 'textfield',
                  autoHeight: true,
                  bodyStyle: Ext.isIE ? 'padding:0 0 5px 15px;' : 'padding:10px 15px;',
                  border: false,
                  items: [{
                      fieldLabel: 'Name',
                      name: 'FIELD_NAME_UPPER',
                      editable : false,
                      disabled : true
                  },{
                      fieldLabel: 'Description',
                      name: 'FIELD_DESC',
                      editable : false,
                      disabled : true
                  }, {
                      xtype : 'checkbox',
                      header    : 'Include ?',
                      dataIndex : 'FIELD_INCLUDE',
                      id        : 'check',
                      disabled  : true,
                      disabled : true
                 } , typeAS,
                   TableCombo,
                   FieldCombo,
                   FieldConditionCombo,
                 {
                    xtype   : 'button',
                    text    : 'Save Grid',
                    align   : 'center',
                    id      : 'idButtonDelRow',
                    width   : 100,
                    disabled: false,
                    icon    : '/plugin/convergenceList/table_save.png', 
                    //iconCls :'button_menu_ext ss_del',
                    //iconCls : 'delField',
                    tooltip : 'Edit',
                    
                    handler : function() {
                            
                            var record = Ext.getCmp('grid').getSelectionModel().getSelected();
                            if(record.data.FIELD_NAME)
                            {
                                record.set('TYPE_CONFIGURATION' , Ext.getCmp('idTypeConfiguration').getRawValue());
                                if(Ext.getCmp('idTypeConfiguration').getValue() == 2)
                                {   
                                    record.set('CONFIG_PMTABLE' , Ext.getCmp('idTableCombo').getRawValue());
                                    record.set('CONFIG_FIELD_SHOW' , Ext.getCmp('idFieldCombo').getRawValue());
                                    record.set('CONFIG_FIELD_CONDITION' , Ext.getCmp('idFieldConditionCombo').getRawValue());
                                }
                            }
                            else
                            {
                        alert('Veuillez sélectionner une ligne!');
                                return 0;
                            }
                    }
              }]
                  
              }]
          });
        
      winConfigDoublon = new Ext.Window({
          closeAction : 'hide',
          autoDestroy : true,
          maximizable: true,
          plain: true,
          id: 'winConfigDoublon',
          title: _WINTITLE_CONFIG_DOUBLON,
          width : 1120,
          height : 400,
          modal : true,
          closable: true,
          constrain: true,
          autoScroll: true,
          layout: 'fit',
          loadMask : true,
          items: [gridForm],
          listeners:{
              'hide':function(win){
                       //winConfigDoublon.hide();
                       winConfigDoublon.close();
               }
        }
      });     
      winConfigDoublon.show();
      //winConfigDoublon.maximize();
      //winConfigDoublon.on('hide',function(){  
    //});
      //winConfigDoublon.toFront();
      
    }
    // End Doublon

function editForms(appUid,accessComment){   
    if (!accessComment)
        accessComment = 0;
    
    var adaptiveHeight = getDocHeight() - 50;
    urlData = "../convergenceList/DynaformsListenerEdit.php?actionType=edit&appUid=" + appUid + "&adaptiveHeight="+adaptiveHeight +"&accessComment="+accessComment;             
    appNumb = '2';
    windowTabs(appUid,urlData,appNumb);
}



function modificationEnMasse(taskuid, champs){

    if (!champs) { champs = 0; } 

    idField = myApp.addTab_inside();
    _AppUids = Ext.util.JSON.decode(idField);
    
     Ext.each(_AppUids, function(record){
       
        var _dblFieldsCustom    = new Array ();
        var _jsonFieldsCustom   = '';
        var item = {
            "APP_UID"   : record.APP_UID,
            "APP_NUMBER": record.APP_NUMBER
                                        };
       _dblFieldsCustom.push(item);
       _jsonFieldsCustom= Ext.util.JSON.encode(_dblFieldsCustom); 
        urlData = "../convergenceList/actions/massUpdate.php";
     //console.log(_jsonFieldsCustom);
        test = Ext.MessageBox.show({
            msg: 'Mise à jour des données, veuillez patienter...',
            progressText: 'En cours...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
            });
        Ext.Ajax.request({
           url : urlData,
           params : {
             array  : _jsonFieldsCustom,
             taskuid : taskuid,
             champs : champs
           },
           success: function (result, request) {
             var response = Ext.util.JSON.decode(result.responseText);
             if (response.success) {
                test.hide();
                        
                         Ext.MessageBox.show({
                             title : 'Résultat du traitement',
                            msg : response.messageinfo,
                            width : 500,
                            fn : function() {Ext.getCmp('gridNewTab').store.reload();},
                            icon: Ext.MessageBox.INFO
                        });
                        
              
             }
             else {
                test.hide();
               PMExt.warning(_('ID_WARNING'), response.message);
             }
             //Ext.getCmp('gridNewTab').store.reload();             
           },
           failure: function (result, request) {
            test.hide();
            Ext.getCmp('gridNewTab').store.reload();                
           }
        });
     });
}
function actionAddComment(app_uid) { 
    var waitLoading = {};
    var textField1 = new Ext.form.TextArea({
        fieldLabel: 'Saisir un commentaire ',
        xtype: 'textarea',
        id: 'caseNoteText',
        name: 'caseNoteText',
        width: 400,
        grow: false,
        height: 200,
        growMin: 40,
        growMax: 80,
        maxLengthText: 500,
        allowBlank: false,
        selectOnFocus: false,
        enableKeyEvents: false
    });
    waitLoading.show = function() {
        var mask = Ext.getBody().mask(_("ID_SAVING"), 'x-mask-loading', false);
        mask.setStyle('z-index', Ext.WindowMgr.zseed + 1000);
    };
    waitLoading.hide = function() {
        Ext.getBody().unmask();
    };
    var w = new Ext.Window(
   {
        title: 'Ajouter votre commentaire pour ce dossier',
        bodyStyle: 'background-color: #f2f2f2',
        width: 600,
        height: 310,
        modal: true,
        autoScroll: true,
        maximizable: true,
        resizable: true,
        items: [
            {
                    columnWidth: 1,
                    xtype: 'fieldset',
                    labelWidth: 120,
                    defaults: {border: false}, // Default config options for child items
                    defaultType: 'textfield',
            autoHeight: false,
                    border: false,
                    items: [textField1]
                }],
            buttons: [
                {
            text: 'Effacer',
                    iconCls: 'x-btn-text button_menu_ext ss_sprite  ss_delete',
            handler: function()
            {
                textField1.reset();
                    }
                },
                {
                    text: 'Enregistrer',
                    iconCls: 'x-btn-text button_menu_ext ss_sprite ss_add',
            handler: function()
            {
                        urlData = "actions/actionAjaxNotes.php";
                Ext.MessageBox.show(
                        {
                        msg: 'Traitement en cours...',
                        progressText: 'En cours...',
                        width: 300,
                            wait: true,
                    waitConfig:
                            {
                                interval: 200
                            }
                        });
                Ext.Ajax.request(
                        {
                            url: urlData,
                    params:
                            {
                                options: 'save',
                                APP_UID: app_uid,
                                Note: Ext.getCmp('caseNoteText').getValue()
                            },
                    success: function(result, request)
                    {
                                var response = Ext.util.JSON.decode(result.responseText);
                        if (response.success)
                        {
                            //textField1.reset();
                            Ext.MessageBox.hide();
                            w.close();

                                }
                                else {
                                    Ext.MessageBox.hide();
                                PMExt.warning(_('ID_WARNING'), response.message);
                                }

                            },
                    failure: function(result, request)
                    {
                        //textField1.reset();
                                Ext.MessageBox.hide();
                            }
                        });
                    }
                }
            ]});
    w.show();
}
function listeChequierDemande(num_dossier)
{
    var adaptiveHeight = getDocHeight() - 50;

    var _dblColumns = new Array();
    var _dblFields = new Array();
    var storeChequier;
    var _CLOSE = 'Fermer';
    var _WINTITLE_DOUBLON = "Liste des chéquiers de ce dossier";
    
    column = {id: 'DMDAPPUID', header: 'demande ID', width: 20, dataIndex: 'DMDAPPUID', hidden: true};
    _dblColumns.push(column);
    _dblFields.push({name: 'DMDAPPUID'});


    column = {id: 'UID', header: '#', width: 20, dataIndex: 'UID', hidden: true};
    _dblColumns.push(column);
    _dblFields.push({name: 'APP_UID'});

    column = {id: 'THEMATIQUE_LABEL', header: '#', width: 20, dataIndex: 'THEMATIQUE_LABEL', hidden: true};
    _dblColumns.push(column);
    _dblFields.push({name: 'THEMATIQUE_LABEL'});

    column = {
        id: 'NUM_DOSSIER',
        header: 'N&deg; Dossier',
        width: 80,
        dataIndex: 'NUM_DOSSIER',
        renderer: function(value, meta, record) {
            var dmdID = record.data.DMDAPPUID;
            if (value != null)
                return '<a href="#" onclick="viewForms(\'' + dmdID + '\',1)">' + value + '</a>';
            else
                return '';
        },
        hidden: false
    };
    _dblColumns.push(column);
    _dblFields.push({name: 'NUM_DOSSIER'});

    column = {
        id: 'COMPLEMENT_CHQ',
        header: 'Type',
        width: 120,
        dataIndex: 'COMPLEMENT_CHQ',
        renderer: function(value, meta, record) {
            var thema = record.data.THEMATIQUE_LABEL;
            if (value == '1')
                return 'Ch&eacute;quier compl&eacute;mentaire';
            else
                return thema;
        },
        hidden: false
    };
    _dblColumns.push(column);
    _dblFields.push({name: 'COMPLEMENT_CHQ'});

    column = {
        id: 'CHQ',
        header: 'Ch&eacute;quier',
        width: 120, //30,req
        dataIndex: 'CHQ'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'CHQ'});

    column = {
        id: 'BCONSTANTE',
        header: 'Num&eacute;ro ch&eacute;quier',
        width: 120, //30,req
        dataIndex: 'BCONSTANTE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'BCONSTANTE'});

    column = {
        id: 'VN',
        header: 'Valeur',
        width: 100,
        renderer: function(value) {

            return value + ' &euro;';
        },
        dataIndex: 'VN'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'VN'});

    column = {
        id: 'DEBUT_VALIDITE',
        header: 'D&eacute;but de validit&eacute;',
        width: 120, //30,req
        dataIndex: 'DEBUT_VALIDITE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'DEBUT_VALIDITE'});

    column = {
        id: 'FIN_VALIDITE',
        header: 'Fin de validit&eacute;',
        width: 120, //30,req
        dataIndex: 'FIN_VALIDITE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'FIN_VALIDITE'});

    column = {
        id: 'ANNULE',
        header: 'Annul&eacute; ?',
        width: 100,
        renderer: function(value) {

            if (value == 1)
                value = 'Oui';
            else
                value = 'Non'

            return value;
        },
        dataIndex: 'ANNULE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'ANNULE'});

    column = {
        id: 'REPRODUCTION',
        header: 'NB de reproduction',
        width: 120, //30,req
        dataIndex: 'REPRODUCTION'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'REPRODUCTION'});
    
    storeChequier = new Ext.data.JsonStore({
        url: '../convergenceList/actions/listeChequierDossier.php?num_dossier=' + num_dossier,
        root: 'data',
        totalProperty: 'total',
        autoWidth: true,
        fields: _dblFields
    });
    storeChequier.load();

    var cmChequier = new Ext.grid.ColumnModel({
        defaults: {
            width: 20,
            sortable: true
        },
        columns: _dblColumns
    });
    cmChequier.defaultSortable = true;

    var gridChequier = new Ext.grid.GridPanel({
        store: storeChequier,
        cm: cmChequier,
        stripeRows: true,
        columnLines: true,
        autoScroll: true,
        autoWidth: true,
        stateful: true,
        id: 'gridChequier',
        layout: 'fit',
        viewConfig: {
            forceFit: false,
            emptyText: (_('ID_NO_RECORDS_FOUND'))
        },
        /*bbar: new Ext.PagingToolbar({
         pageSize: 300,
         store: storeChequier,
         displayInfo: true,
         displayMsg: _('ID_DISPLAY_ITEMS') + ' &nbsp; ',
         emptyMsg: _('ID_DISPLAY_EMPTY')
         }),*/
        listeners: {
            render: function(grid) {

            },
            afterrender: function() {

            },
            cellcontextmenu: function(grid, rowIndex, cellIndex, event) {


            }
        },
        tbar: [{
                text: _CLOSE,
                iconCls: 'button_menu_ext ss_sprite ss_accept',
                handler: function() {
                    winTitre.close();
                }
            }]
    });
    ///////////// end grid
    winTitre = new Ext.Window({
        closeAction: 'hide',
        autoDestroy: true,
        maximizable: true,
        id: 'winDoublon',
        title: _WINTITLE_DOUBLON,
        width: 900,
        height: 400,
        modal: true,
        closable: true,
        constrain: true,
        autoScroll: true,
        layout: 'fit',
        items: gridChequier
    });

    winTitre.show();
    //winTitre.maximize();
    winTitre.toFront();
}

function listeCarteProduite(app_uid)
{
    var adaptiveHeight = getDocHeight() - 50;

    var _dblColumns = new Array();
    var _dblFields = new Array();
    var storeCarte;
    var _CLOSE = 'Fermer';
    var _NO_PROD = 'Aucune carte produite';
    var _WINTITLE_DOUBLON = "Liste des cartes produites";

    column = {id: 'DMDAPPUID', header: 'demande ID', width: 20, dataIndex: 'DMDAPPUID', hidden: true};
    _dblColumns.push(column);
    _dblFields.push({name: 'DMDAPPUID'});

    /*
     column = {id: 'UID', header: '#', width: 20, dataIndex: 'UID', hidden: true};
     _dblColumns.push(column);
     _dblFields.push({name: 'APP_UID'});
     */

    column = {
        id: 'PORT_ID',
        header: 'N&deg; Porteur Id',
        width: 120,
        dataIndex: 'PORT_ID',
        renderer: function(value, meta, record) {
            var dmdID = record.data.DMDAPPUID;
            if (value != null)
                return '<a href="#" onclick="viewForms(\'' + dmdID + '\',1)">' + value + '</a>';
            else
                return '';
        },
        hidden: false
    };
    _dblColumns.push(column);
    _dblFields.push({name: 'PORT_ID'});

    column = {
        id: 'CARTE_NUM',
        header: 'N&deg; de carte',
        width: 120, //30,req
        dataIndex: 'CARTE_NUM'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'CARTE_NUM'});

    column = {
        id: 'NOM',
        header: 'Nom',
        width: 120, //30,req
        dataIndex: 'NOM'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'NOM'});

    column = {
        id: 'PRENOM',
        header: 'Pr&eacute;nom',
        width: 120,
        dataIndex: 'PRENOM'
    };
    _dblColumns.push(column);
    _dblFields.push({name: 'PRENOM'});

    column = {
        id: 'CARTE_STATUT',
        header: 'Statut',
        width: 120, //30,req
        dataIndex: 'CARTE_STATUT'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'CARTE_STATUT'});

    column = {
        id: 'CARTE_TYPE',
        header: 'Type de carte',
        width: 120, //30,req
        dataIndex: 'CARTE_TYPE'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'CARTE_TYPE'});

    column = {
        id: 'PND',
        header: 'NPAI ?',
        width: 100,
        renderer: function(value) {

            if (value == 1)
                value = 'Oui';
            else
                value = 'Non'

            return value;
        },
        dataIndex: 'PND'

    };
    _dblColumns.push(column);
    _dblFields.push({name: 'PND'});

    storeCarte = new Ext.data.JsonStore({
        url: '../convergenceList/actions/listeCarte.php?app_uid=' + app_uid,
        root: 'data',
        totalProperty: 'total',
        autoWidth: true,
        fields: _dblFields
    });
    storeCarte.load();

    var cmCarte = new Ext.grid.ColumnModel({
        defaults: {
            width: 20,
            sortable: true
        },
        columns: _dblColumns
    });
    cmCarte.defaultSortable = true;

    var gridCarte = new Ext.grid.GridPanel({
        store: storeCarte,
        cm: cmCarte,
        stripeRows: true,
        columnLines: true,
        autoScroll: true,
        autoWidth: true,
        stateful: true,
        id: 'gridCarte',
        layout: 'fit',
        viewConfig: {
            forceFit: false,
            emptyText: (_('NO_PROD'))
        },
        /*bbar: new Ext.PagingToolbar({
         pageSize: 300,
         store: storeCarte,
         displayInfo: true,
         displayMsg: _('ID_DISPLAY_ITEMS') + ' &nbsp; ',
         emptyMsg: _('ID_DISPLAY_EMPTY')
         }),*/
        listeners: {
            render: function(grid) {

            },
            afterrender: function() {

            },
            cellcontextmenu: function(grid, rowIndex, cellIndex, event) {


            }
        },
        tbar: [{
                text: _CLOSE,
                iconCls: 'button_menu_ext ss_sprite ss_accept',
                handler: function() {
                    winTitre.close();
                }
            }]
    });
    ///////////// end grid
    winTitre = new Ext.Window({
        closeAction: 'hide',
        autoDestroy: true,
        maximizable: true,
        id: 'winDoublon',
        title: _WINTITLE_DOUBLON,
        width: 900,
        height: 400,
        modal: true,
        closable: true,
        constrain: true,
        autoScroll: true,
        layout: 'fit',
        items: gridCarte
    });

    winTitre.show();
    //winTitre.maximize();
    winTitre.toFront();
}

function message(mess)
{   
    Ext.Msg.alert('Confirmed', mess, function(btn, text){
        if (btn == 'ok'){
            Ext.getCmp('win2').hide();
        }
    });
    
}
