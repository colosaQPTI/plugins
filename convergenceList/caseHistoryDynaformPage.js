function ajaxPostRequest(url, callback_function, id){
      var d = new Date();
      var time = d.getTime();
      url= url + '&nocachetime='+time;
      var return_xml=false;    
      var http_request = false;
      
        if (window.XMLHttpRequest){ // Mozilla, Safari,...
          http_request = new XMLHttpRequest();
         
            if (http_request.overrideMimeType){
              http_request.overrideMimeType('text/xml'); 
            }
        }
        else if (window.ActiveXObject){// IE
          try{
            http_request = new ActiveXObject("Msxml2.XMLHTTP");
          } 
          catch (e){
              try{
                http_request = new ActiveXObject("Microsoft.XMLHTTP");
              }
              catch (e){
               
              }
          }
        }
        
        if (!http_request){
          alert('This browser is not supported.');
          return false;
        }
        
        http_request.onreadystatechange = function(){
            if (http_request.readyState == 4){
                if (http_request.status == 200){
                    if (return_xml){
                      eval(callback_function + '(http_request.responseXML)');
                    }
                    else{		               	
                      eval(callback_function + '(http_request.responseText, \''+id+'\')');			
                    }
                } 
                else{
                  alert('Error found on request:(Code: ' + http_request.status + ')');
                }
            }
        }
      http_request.open('GET', url, true);
      http_request.send(null);
     
    }   
    
    var processesGrid;
    var store;
    
    
    new Ext.KeyMap(
      document,
      {
        key: Ext.EventObject.F5,
        fn: function(keycode, e){
            if (! e.ctrlKey){
              if (Ext.isIE)
                  e.browserEvent.keyCode = 8;
              e.stopEvent();
              document.location = document.location;
            }
            else{
              Ext.Msg.alert('Refresh', 'You clicked: CTRL-F5');
            }

        }
      }
    );

    Ext.onReady(function(){
      
      Ext.QuickTips.init();      
      if(parent.Ext.getCmp('tabPanelForms')){
      var TabPanel = parent.Ext.getCmp('tabPanelForms');
      }      
      else{        
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
                        defaultSrc : 'casesHistoryDynaformPage_Ajax.php?actionAjax=HistoryLog&APP_UID='+APP_UID,
                        loadMask:{msg:_('ID_LOADING_GRID')+'...'},
                        bodyStyle:{height: ADAPTIVEHEIGHT+'px'}
                        }
                      ],
                      listeners: {
                      tabchange: function(panel){
                        panel.ownerCt.doLayout();
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
                              
          });

          var viewportTab = new Ext.Viewport({
            layout : 'fit',
            items : [TabPanel]
          });          
      }
      for(var i = 0; i < DATALABELS.length; i++)
      {
    	  //var id_+DATALABELS[i]['NAME_LABEL'] = DATALABELS[i]['DESCRIPTION'];
    	  var data = ("var id_" + DATALABELS[i]['NAME_LABEL'] + "= '" + DATALABELS[i]['DESCRIPTION']) + "';";
    	  eval(data);
      }       
      for(var i=0;i<DYNAFORMSLIST.length;i++){
        var tabTitle = DYNAFORMSLIST[i]['DYN_TITLE'];
        var DYN_UID=DYNAFORMSLIST[i]['DYN_UID'];   
        var url = 'casesHistoryDynaformPage_Ajax.php?ACTIONTYPE='+ACTIONTYPE+'&actionAjax=historyDynaformGridPreview'+'&DYN_UID='+DYN_UID+'&APP_UID='+APP_UID+'&PRO_UID='+PRO_UID+'&CURRENTDATETIME='+CURRENTDATETIME;
        fn_add_tab(tabTitle,url,TabPanel);          
      }      
      
      //ajout des commentaires
      if (SHOWCOMMENT == 1) {
    	  fn_add_tab(id_Commentaires,'viewCaseNotes.php?APP_UID='+APP_UID,TabPanel);
    	    
    	  fn_add_tab(id_Explicatio_Statut,'actions/explicationStatut.php?APP_UID='+APP_UID+'&ADAPTIVEHEIGHT='+ADAPTIVEHEIGHT,TabPanel);
      }
      // ajout nico pour les statut remboursement
      if (SHOWCOMMENT == 2) {
    	  fn_add_tab(id_Commentaires,'viewCaseNotes.php?APP_UID='+APP_UID,TabPanel);

    	  fn_add_tab(id_Explicatio_Statut,'actions/explicationStatutRmb.php?APP_UID='+APP_UID+'&ADAPTIVEHEIGHT='+ADAPTIVEHEIGHT,TabPanel);
      }
      
      TabPanel.setActiveTab(1);
      
    
    //not work
    /*TabPanel.on('beforetabchange', function(panel,newTab,currentTab){
        if (newTab.title == 'Explication Statut')
            newTab.doLayout();
        
    });*/
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
     var screenWidth = (PMExt.getBrowser().screen.width-140).toString() + 'px';
    function fn_add_tab(sName,sUrl,TabPanel)
    {   
    	      
        TabPanel.add({
          id: 'iframe-' + sName,      
          title: sName,
          frameConfig:{name: sName + 'Frame', id: sName + 'Frame'},
          defaultSrc : '../convergenceList/' + sUrl,        
          //iconCls: sIcon,
          loadMask:{msg:'Chargement ...'},
          //autoWidth: true,
          autoHeigth: true,
          closable: false,
          autoScroll: true,       
          //bodyStyle:{height: (PMExt.getBrowser().screen.height-30) + 'px', overflow:'auto'},
          bodyStyle:{height: ADAPTIVEHEIGHT+'px'},
          width:screenWidth
        }).show();
        
        TabPanel.doLayout();      
        fn_open_frames();

    }
    
    
    });        
 
 