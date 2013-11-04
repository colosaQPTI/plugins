//Keyboard Events
new Ext.KeyMap(document, [{
    key : Ext.EventObject.F5,
    fn: function(keycode, e) {
        if (! e.ctrlKey) {
           if (Ext.isIE) {
                // IE6 doesn't allow cancellation of the F5 key, so trick it into
                // thinking some other key was pressed (backspace in this case)
                e.browserEvent.keyCode = 8;
            }
            e.stopEvent();
            document.location = document.location;
        }else{
            Ext.Msg.alert('Refresh', 'You clicked: CTRL-F5');
        }
    }
},
     {
       key: Ext.EventObject.DELETE,
       fn: function(k,e){
         iGrid = Ext.getCmp('infoGrid');
         rowSelected = iGrid.getSelectionModel().getSelected();
         if (rowSelected){
           DeleteButtonAction();
         }
       }
     }
     ]);

var store;
var cmodel;
var infoGrid;
var viewport;
var smodel;

var deleteButton;
var contextMenu;
var pageSize;
var w;
var ID_INBOX;


Ext.onReady(function(){
       
	var lanRemInbox = 'Remove relation Inbox';
	var lanActions = 'Actions'
	var lanWhereQuery = 'Where Query';
	var lanSave ="Save";
	var lanCancel ="Cancel";
	var lanDisplaying = 'Displaying {0} - {1} of {2}';
	var lanDisplayInbox = 'No Inbox to show';
	var lanRelInbox = 'Relation Inbox Group';
	var lanSelectPlease = 'Select Items please';
	var lanMsgOperation = 'The operation completed sucessfully!';
	var lanMsgOpError = 'The operation was not completed sucessfully!';
	var lanCustomColEmpty = 'There are no actions to display';
	var lanCustomColAddParam = 'Add Parameter';
	var lanActionNewInbox = 'New actions of inbox';
	var lanActionParamSent = 'Parameters sent function';
	var lanActionParamSent2 = 'Parameters Sent of Function';
	var ActionAdd = 'Add actions of inbox';
	var ActionAdd2 = 'Add Actions to Inbox';
	var CustomColEmpty2 = 'No actions to display';
	
	
	var lanMsgSave = "The data was saved sucessfully!";
	var lanNewAction ="New Action of Inbox";
	var lanParamHas = "The function has no parameters";
	var lanEditAction = 'Edit Actions of Inbox';
	var lanEditAction2 = 'Edit actions to Inbox';
	
	
	var lanRemAction = 'Edit Actions of Inbox';
	var lanMsgRemove = "The data was removed sucessfully!";
	var lanSelectVendor ="You must select a Vendor to Remove.";
	var lanSorry ="Sorry...";
	var lanEnabledShow ="Enabled show inbox";
	var lanMsgConfirm= "Do you want to remove this relation inbox? ";
	var ActionRemove = 'Remove actions to inbox';
	var ActionSave = 'Save actions';
	
	
	
	// Variables for language
	var CustomColumnsTitle = 'Custom Columns';
	var CustomColumnsNew = 'New Select Query';
	var CustomColumnsEdit = 'Edit Select Query';
	var CustomColumnsRemove = 'Remove Select Query';
	var CustomColAdd = 'Add Select Query';
	var CustomColParameters = 'Parameters Select';
	
	
	
	
	
	var Action = 'Actions';
	var ActionEdit = 'Edit actions to inbox';
	
	var ActionFields = 'By fields';
	
	
	var ActionAddInbox = 'Add actions of inbox';
	

	
	var ActionRemInbox = 'Remove actions of inbox';
	var ActionEditInbox ="Edit Actions of Inbox";
	var ActionInboxTitle="Action Inbox";
	

	var MsgSelectItem='Select Items please';
	var lanSaveConditions='Save Conditions';

	var lanActionRemTitle ='Remove Select Query of Inbox';
	var lanActionDelPar = "Delete Parameter";
	
	
	if(language == 'fr')
	{
	        lanRemInbox = 'Supprimer relation Inbox';
		lanActions = 'Actions';
		lanWhereQuery = 'Where Query';
		lanSave="Sauver";
		lanCancel ="Annuler";
		lanDisplaying = 'Affichage {0} - {1} sur {2}';
		lanDisplayInbox="Aucune Inbox pour montrer";
		lanRelInbox = 'Relation Inbox Groupe';
		lanSelectPlease = 'Sélectionnez \u00E9l\u00E9ments veuillez';
		lanMsgOperation="L'op\u00E9ration s'est termin\u00E9e avec succ\u00E8s!";
	        lanMsgOpError="L'op\u00E9ration n'a pas été compl\u00E9t\u00E9e avec succ\u00E8s!";
		lanCustomColEmpty = "Il n'y a aucune action \u00E0 afficher";
		lanCustomColAddParam = 'Ajouter un param\u00E8tre';
		lanActionNewInbox = "Nouvelle action de inbox";
		lanActionParamSent = "Param\u00E8tres envoy\u00E9s fonction";
		lanActionParamSent2 = "Param\u00E8tres envoy\u00E9s de la fonction";
		ActionAdd= "Ajouter actions de Inbox";
		ActionAdd2="Ajouter actions au Inbox";
		lanMsgSave="Les donn\u00E9es ont \u00E9t\u00E9 enregistr\u00E9es avec succ\u00E8s!";
		lanNewAction ="Nouvelle action de Inbox";
		lanParamHas = "La fonction n'a pas de param\u00E8tres";
		lanEditAction = 'Editer Actions de Inbox';
		lanEditAction2 = 'Editer actions au Inbox';
		lanRemAction = 'Supprimer Actions de Inbox';
		lanMsgRemove="Les donn\u00E9es ont \u00E9t\u00E9 supprim\u00E9es avec succ\u00E8s!";
		lanSelectVendor ="Vous devez s\u00E9lectionner un fournisseur pour le retirer.";
	        lanSorry="D\u00E9sol\u00E9...";
	        lanEnabledShow ="Show Enabled inbox";
		lanMsgConfirm= "Voulez-vous supprimer ce relation inbox? ";
	        ActionRemove= "Supprimer actions au Inbox";
	        ActionSave= "Sauver actions";
	        CustomColEmpty2 = 'Aucune action \u00E0 afficher';
	    
		CustomName = 'Colonne Personnalis\u00E9e';
		titleGrid = 'Inbox Liste des Colonnes:';
		select = 'S\u00E9lectionner';
		CustomColumnsTitle= 'Colonnes personnalis\u00E9es';
		CustomColumnsNew= 'Nouveau Select Query';
		CustomColumnsEdit= 'Edition Select Query';
		CustomColumnsRemove= 'Supprimer Select Query';
	        CustomColAdd = 'Ajouter Select Query';
		CustomColParameters = 'Param\u00E8tres Select';
		
		
		
		
		ActionEdit = "Editer actions de Inbox";
		
		
		ActionFields = "Par champs";
		
		
		ActionAddInbox="Ajouter action de inbox";
		
		
		ActionRemInbox= "Supprimer action de inbox";
		
		ActionEditInbox ="Editer actions de Inbox";
		ActionInboxTitle ="Action Inbox";
		
		
		MsgSelectItem="S\u00E9lectionnez les Articles veuillez";
		lanSaveConditions ="Sauver conditions";
		
		lanActionRemTitle = "Supprimer Select Query de Inbox";
		lanActionDelPar = "Supprimer Param\u00E8tre";
		
	}
    
    
    ///////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////
    
//Funtion Handles Context Menu Opening
onMessageContextMenu = function (grid, rowIndex, e) {
  e.stopEvent();
  var coords = e.getXY();
  contextMenu.showAt([coords[0], coords[1]]);
};

////////////////////////////////// Save Drag and Drop //////////////////////
	function Fn_SaveDragAndDrop()
	{
		var idField = '';
		var swD     = 0;
		var i       = 0;
		var arrayRelation = new Array ();
		var myJSON  = '';
		store.each(function(record)  
		{  
			var idInbox      = record.get('ID_INBOX');
			var inboxDescription      = record.get('INBOX_DESCRIPTION');
			var idRoles      = rolID;
			var item = {
				"value"        : i,
				"idRoles"      : rolID,
				"idInbox"	   : idInbox
			};
			i++;
			arrayRelation.push(item);
	    	 
	    });
		if(arrayRelation.length != 0){
			myJSON= Ext.util.JSON.encode(arrayRelation);
			saveDataRelation(myJSON);
		}
		else
		{
			alert(lanSelectPlease);
		}
	}

//////////////////////////////////// Save Data Inbox Relation Roles ////////////////////////////
	function saveDataRelation(arrayRelation)
	{  
	      Ext.MessageBox.show({ msg: _('ID_PROCESSING'), wait:true,waitConfig: {interval:200} });
	       //gName = rowSelected.data.ID_INBOX;
	       var enabledInbox = Ext.getCmp('enabled_inbox_rol').getValue();
	       var sw_pm_inbox = 0;
	       if(enabledInbox)
	       		sw_pm_inbox = 1;
	       
	       Ext.Ajax.request({
	        url: 'inboxRelation_Ajax',
	        params: {
	        		action: 'saveDragDropRelation',
	        		arrayRelation : arrayRelation,
	          		rolID :  rolID,
	          		sw_pm_inbox : sw_pm_inbox
	          		},
	        success: function(r,o){
	          		Ext.MessageBox.hide();
	          	          
	          PMExt.notify("Drag And Drop",lanMsgOperation);
	        },
	        failure: function(){
	        	Ext.MessageBox.alert('Error',lanMsgOpError);
	        	Ext.MessageBox.hide();
	        }
	      });
	       reloadGrid();	
	}

//////////////////////////////// Load Action Inbox //////////////////////////////////////////////////
function Fn_LoadActionsInbox(){		
	var rowModel = infoGrid.getSelectionModel().getSelected();
	 if (rowModel) {
         var ID = rowModel.data.ID;
         var ID_INBOX = rowModel.data.ID_INBOX;
         
	    function add_ActionInbox_popup(){
	    	
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
				width : 165,
				height : 130,
				id:'gridCenter',
				viewConfig : {
		          forceFit : true,
		          scrollOffset : 0,
		          emptyText: lanCustomColEmpty
		       },
				tbar : [{
						text: lanCustomColAddParam,
						cls : 'x-btn-text-icon',
						icon : '/images/ext/default/tree/drop-add.gif',
						handler: function(){
									add_ParametersAction();
						}	
					}]
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
			        fieldLabel: lanActionNewInbox,
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
							}
							else
								Ext.getCmp('helpparametersfield').setValue('The function has no parameters');
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
							title : lanActionParamSent,
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
						            id:'helpparametersfield', 
						        	fieldLabel: lanActionParamSent2, 
						        	name: 'helpparams', 
						        	width: 150,
						        	height: 50,
						        	allowBlank: false,
						        	disabled: true,
						            hidden: false
						        } , gridParametersAction]
							} ]
					} ,  {                                                                                              
				            xtype: 'textarea',
				            id:'parametersfield', 
				        	  fieldLabel: lanActionParamSent, 
				        	  name: 'parameters', 
				        	  width: 250, 
				        	  allowBlank: true,
				        	  disabled: false,
				            hidden: false
				        }
					  ]							
				});		
				
				add_ActionInbox_popup_window = new Ext.Window({
				title: ActionAdd,
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
		            text: lanSave,
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
		                            msg : lanMsgSave,
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
		            text: lanCancel,            
		            handler: function (){                
		                Ext.getCmp('add_ActionInbox_popup_window').close();
		            }
		        }]
				});	
				
				add_ActionInbox_popup_window.show();
				add_ActionInbox_popup_window.toFront();
		}
         
         // edit Actions repeat
         function edit_ActionInbox_popup(){
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
			        fieldLabel: lanNewAction,
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
								Ext.getCmp('helpId').setValue(lanParamHas);
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
				          emptyText: lanCustomColEmpty
				       },
						tbar : [{
								text: lanCustomColAddParam,
								cls : 'x-btn-text-icon',
								icon : '/images/ext/default/tree/drop-add.gif',
								handler: function(){
											add_ParametersAction();
								}	
							}]
					});
				edit_ActionInbox_popup_form = new Ext.FormPanel({
				id: 'popupActionEdit',								  
				labelAlign: 'top',
				bodyStyle:'padding:5px 5px 5px 10px',
				autoScroll:true,
				items: [edit_ActionInbox_popup_field,
						    {
							id : 'idParameters',
							title : lanActionParamSent,
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
						        	fieldLabel: lanActionParamSent2, 
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
				        	  fieldLabel: lanActionParamSent, 
				        	  name: 'parameters', 
				        	  width: 250, 
				        	  allowBlank: true,
				        	  disabled: false,
				            hidden: false
				        }
					  ]							
				});		
				
				edit_ActionInbox_popup_window = new Ext.Window({
				title: lanEditAction,
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
		            text: lanSave,
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
	                            msg : lanMsgSave,
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
		            text: lanCancel,            
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

         function add_ParametersAction(){
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

  		function remove_ActionInbox_popup(){
			if(ActionInbox_popup_grid.selModel.getCount() == 1) {
				var rowModel = ActionInbox_popup_grid.getSelectionModel().getSelected();
				
		  	    if (rowModel) {
		  	    	var sm = ActionInbox_popup_grid.getSelectionModel();
		            var sel = sm.getSelected();
		            if (sm.hasSelection()) {
		            	
		            	  Ext.Msg.show({
				                title : lanRemAction,
				                buttons : Ext.MessageBox.YESNOCANCEL,
				                msg : lanRemAction+' : ' + rowModel.data.NAME + ' ?',
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
							                            msg : lanMsgRemove,
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
							                            msg : lanMsgRemove,
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
				Ext.MessageBox.alert(lanSorry,lanSelectVendor);
			}
		 }
	
		}
  		
  		function saveActions_DragAndDrop()
  		{
  			//'ID', 'NAME', 'DESCRIPTION','PM_FUNCTION','PARAMETERS_FUNCTION','PARAMETERS_FUNCTION_AUX','ID_ACTION','SENT_FUNCTION_PARAMETERS'
  			rowSelected = infoGrid.getSelectionModel().getSelected();
  			var i  = 0;
  			var arrayActionsInbox = new Array ();
  			var myJSON  = '';
  			
  			ActionInbox_popup_store.each(function(record)  
  			{  
  				var idInbox      			= rowSelected.data.ID_INBOX; // 
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
  				alert(lanSelectPlease);
  			}
  		}
  		
////////////////////////////////////Save Data Actions Inbox ////////////////////////////
  		function saveDataActionsInbox(arrayActionsInbox)
  		{  
  			rowSelected = infoGrid.getSelectionModel().getSelected();
  			var ID = rowSelected.data.ID;
  			var ID_INBOX = rowSelected.data.ID_INBOX;
  			
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
		                         msg : lanMsgOperation,
		                         buttons : Ext.MessageBox.OK,
		                         icon : Ext.MessageBox.INFO
		                }); 
  		         },
  		        failure: function(){
  		        	Ext.MessageBox.alert('Error',lanMsgOpError);
  		        	Ext.MessageBox.hide();
  		        }
  		      });
  		       Ext.getCmp('ActionInbox_popup_grid').getStore().reload(); 
  		       
  		}
         
        
			var ActionInbox_popup_store = new Ext.data.JsonStore({
		        url : 'ajaxActionInboxPopup.php?actionInbox_id='+ID_INBOX+'&rolID='+rolID,
		        root : 'data',
		        totalProperty : 'total',
		        autoWidth : true,
		         fields : [ 'ID', 'NAME', 'DESCRIPTION','PM_FUNCTION','PARAMETERS_FUNCTION','PARAMETERS_FUNCTION_AUX','ID_ACTION','SENT_FUNCTION_PARAMETERS']
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
				} 
				]);
		    ActionInbox_popup_cm.defaultSortable= true;	
		
		    var ActionInbox_popup_grid = new Ext.grid.GridPanel({
				store			: ActionInbox_popup_store,
				cm				:ActionInbox_popup_cm,
				stripeRows		: true,
				autoScroll		:true,
				id			 	:'ActionInbox_popup_grid',
				ddGroup		   	:'gridDDactions',
				enableDragDrop 	: true,
				viewConfig 		: {
		          forceFit 		: true,
		          scrollOffset 	: 0,
		          emptyText		: lanCustomColEmpty
		       },
				bbar			: new Ext.PagingToolbar({
			          pageSize: 50,
			          store: ActionInbox_popup_store,
			          displayInfo: true,
			          displayMsg: lanDisplaying,
			          emptyMsg: CustomColEmpty2
				}),
				tbar 			: [{
						text: ActionAdd2,
						cls : 'x-btn-text-icon',
						icon : '/images/ext/default/tree/drop-add.gif',
						handler: function(){
									add_ActionInbox_popup();
							}
						}, {
							text: lanEditAction2,
							cls : 'x-btn-text-icon',
							icon : '/images/edit-table.png',
							handler: function() {
								edit_ActionInbox_popup();
					        	}		
						} , {
							text: ActionRemove,
							cls : 'x-btn-text-icon',
							icon : '/images/delete-16x16.gif',
							handler: function(){
										remove_ActionInbox_popup();
							}
						} , {
							text: ActionSave,
							cls : 'x-btn-text-icon',
							icon : '/images/ok.png',
							tooltip  : 'Add drag and drop',
							handler: function(){
									saveActions_DragAndDrop();
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
				}
		});	
		    
         ActionInbox_popup_window = new Ext.Window({
			   	closeAction : 'hide',
	            autoDestroy : true,
	            maximizable: true,        
	            title: 'Action Inbox ',	          
	            width : 600,
	            height : 312,            
	            modal : true,
	            closable:true,
				constrain:true,
				autoScroll:true,
				items : ActionInbox_popup_grid,
				layout: 'fit'
				});				
         		ActionInbox_popup_window.show();
         		ActionInbox_popup_window.toFront();			
	 } else {
	       msgBox('Error');
	    }
}
//////////////////////////////// End Load Action Inbox //////////////////////////////////////////////////

//////////////////////////////// Where Action Inbox //////////////////////////////////////////////////////

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
		fieldLabel: "Please type the Where Statement", 
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
		text: 'Save Query',
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
				alert("the query is required!");
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

function Fn_LoadWhereInbox(){
	var rowModel = infoGrid.getSelectionModel().getSelected();
	if (rowModel) {
		UID = rowModel.data.ID;
		ID_INBOX = rowModel.data.ID_INBOX;
		ROLE_CODE = rolID;
		var WhereInbox_popup_store = new Ext.data.JsonStore({
			url : 'ajaxWhereInboxPopup.php?actionInbox_id='+ID_INBOX+'&rolID='+rolID,	
			root : 'data',
			totalProperty : 'total',
			autoWidth : true,
			fields : [ 'IWHERE_UID', 'IWHERE_QUERY', 'IWHERE_IID_INBOX','IWHERE_ROLE_CODE']
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
				text: 'Add Where to Inbox',
				cls : 'x-btn-text-icon',
				icon : '/images/ext/default/tree/drop-add.gif',
				handler: function(){
					add_WhereInbox_popup(ID_INBOX,ROLE_CODE);
				}
			}, {
				text: 'Edit Where to Inbox',
				cls : 'x-btn-text-icon',
				icon : '/images/edit-table.png',
				handler: function() {
					var gridWhereInbox = Ext.getCmp('WhereInbox_popup_grid');
					var rowSelected = gridWhereInbox.getSelectionModel().getSelected();
					var ID_WHERE = rowSelected.data.IWHERE_UID;
					var QUERY_WHERE = rowSelected.data.IWHERE_QUERY;
					edit_WhereInbox_popup(ID_WHERE,QUERY_WHERE,ID_INBOX,ROLE_CODE);
				}		
			} , {
				text: 'Remove Where to Inbox',
				cls : 'x-btn-text-icon',
				icon : '/images/delete-16x16.gif',
				handler: function(){
					var gridWhereInbox = Ext.getCmp('WhereInbox_popup_grid');
					var rowSelected = gridWhereInbox.getSelectionModel().getSelected();
					var ID_WHERE = rowSelected.data.IWHERE_UID;
					PMExt.confirm(_('ID_CONFIRM'),"Do you want to remove this where statement?", function(){
					   	Ext.MessageBox.show({ msg: _('ID_PROCESSING'), wait:true,waitConfig: {interval:200} });
					   	Ext.Ajax.request({
						url: 'SaveWhereInbox.php?ID=' + ID_INBOX,
						params:{
				        		whereaction: 'remove',
				        		whereIDField : ID_WHERE			        		
				        },
						success: function(response) {						
							Ext.MessageBox.hide();					
							Ext.getCmp('WhereInbox_popup_grid').getStore().reload();						
						}				
						});     
					});					
				}
			}]
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
        WhereInbox_popup_window.toFront();        	
	}

}


//////////////////////////////// End Where Action Inbox //////////////////////////////////////////////////



//Close Popup Window
CloseWindow = function(){
  Ext.getCmp('w').hide();
};
//Delete Button Action
DeleteButtonAction = function(){  
  Ext.Msg.confirm(_('ID_CONFIRM'), lanMsgConfirm,

    function(btn, text){
    if (btn=="yes"){
      rowSelected = infoGrid.getSelectionModel().getSelected();
      //viewport.getEl().mask(_('ID_PROCESSING'));
  		Ext.MessageBox.show({ msg: _('ID_PROCESSING'), wait:true,waitConfig: {interval:200} });
       gName = rowSelected.data.ID_INBOX;
       Ext.Ajax.request({
        url: 'inboxRelation_Ajax',
        params: {
        		action: 'deleteRelation',
          		name  :  gName,
          		rolID: rolID
          		},
        success: function(r,o){
          			Ext.MessageBox.hide();//viewport.getEl().unmask();
          
          deleteButton.disable(); //Disable Delete Button
          
          PMExt.notify("Remove Inbox","The relation inbox was deleted succesfully!");
        },
        failure: function(){
        	Ext.MessageBox.hide();//viewport.getEl().unmask();
        }
      });
       reloadGrid();
      
    }
  });
};
   
//Update Page Size Configuration
UpdatePageConfig = function(pageSize){
  Ext.Ajax.request({
  url    : 'inboxRelation_Ajax',
  params : {
	  		action:'RelationList', 
	  		rolID: rolID,
	  		size: pageSize
	  		}
  });
};
reloadGrid = function(){
	Ext.getCmp('infoGrid').getStore().reload();
}
    
    
    
    
    
    ///////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////
    ////////////////////////
    ////////////////////////
    
    
    
    
    
  Ext.QuickTips.init();

  pageSize = parseInt(10);

  deleteButton = new Ext.Action({
        text     : lanRemInbox,
        iconCls  : 'button_menu_ext ss_sprite  ss_delete',
        handler  : DeleteButtonAction,
        disabled : true
    });

  
  ActionButton = new Ext.Action({
      text     : lanActions,
      iconCls  : 'button_menu_ext ss_sprite  ss_action',
      tooltip  : 'Management actions that will have the inbox',
      handler  : Fn_LoadActionsInbox,
      disabled : true
  });

  whereButton = new Ext.Action({
      text     : lanWhereQuery,
      iconCls  : 'button_menu_ext ss_sprite  ss_action',
      tooltip  : 'Add the where statements to the general query',
      handler  : Fn_LoadWhereInbox,
      disabled : true
  });
  
  saveButton = new Ext.Action({
      text     : lanSave,
      iconCls  : 'button_menu_ext ss_sprite  ss_save', 
      tooltip  : 'Add drag and drop',
      handler  : Fn_SaveDragAndDrop,
      disabled : false
  });
  
  var sw_checked = true;
  
  if(SW_INBOX == 1)
  	sw_checked = true;
  else
  	sw_checked = false;
  
  var checkboxTool = {
                    xtype: 'checkbox',
                    name: 'enabled_inbox_rol',
                    boxLabel: lanEnabledShow,
                    id : 'enabled_inbox_rol',
                    checked: sw_checked
                    };
    	
    
    contextMenu = new Ext.menu.Menu({
        items : [deleteButton,'-',ActionButton,'-',saveButton]
    });
    
  smodel = new Ext.grid.RowSelectionModel({
    singleSelect : true,
    listeners    :{
      rowselect: function(sm){        
        deleteButton.enable();
        ActionButton.enable();
        whereButton.enable();
      },
      rowdeselect: function(sm){        
        deleteButton.disable();
        ActionButton.disable();
        whereButton.disable();
      }
    }
  });

 
  store = new Ext.data.GroupingStore( {
	    proxy       : new Ext.data.HttpProxy({
	      url       : 'inboxRelation_Ajax?action=RelationList&rolID='+rolID
	    }),
	    reader : new Ext.data.JsonReader( {
	      root: 'data',
	      fields : [
	                {name : 'ID'},
	                {name : 'ID_INBOX'},
	                {name : 'INBOX_DESCRIPTION'}
	                ]
	    })
	  });
  
 

  cmodel = new Ext.grid.ColumnModel({
    defaults: {
      width: 50,
      sortable: true
    },
    viewConfig: {
      cls:"x-grid-empty",
      emptyText: (TRANSLATIONS.ID_NO_RECORDS_FOUND)
    }
    ,
    columns: [
              {id:'ID', dataIndex: 'ID', hidden:true, hideable:false},
              {header: 'INBOX', dataIndex: 'ID_INBOX', width: 90, align:'left'},
              {header: 'DESCRIPTION', dataIndex: 'INBOX_DESCRIPTION', width: 175, align:'left'}
              ]
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
      displayMsg  : lanDisplaying,
      emptyMsg    : lanDisplayInbox,
      pageSize    : pageSize,
      items       : ['-',_('ID_PAGE_SIZE')+':',comboPageSize]
  }); 

  infoGrid = new Ext.grid.GridPanel({
    region             : 'center',
    layout             : 'fit',
    id                 : 'infoGrid',
    ddGroup		   	   :'gridDD',
	enableDragDrop 	   : true,
    height             : 100,
    autoWidth          : true,
    stateful           : true,
    stateId            : 'grid',
    enableColumnResize : true,
    enableHdMenu       : true,
    frame              : false,
    columnLines        : false,
    viewConfig         : {
      forceFit :true
    },
    title     : lanRelInbox,
    store     : store,
    cm        : cmodel,
    sm        : smodel,
    tbar      : [deleteButton,'-',ActionButton,  '-',saveButton,'-',checkboxTool],
    bbar      : bbarpaging,
    listeners : {
    	"render": {
  		scope: this,
  		fn: function(grid) {
			var ddrow = new Ext.dd.DropTarget(grid.container, {
				ddGroup : 'gridDD',
				copy:false,
				notifyDrop : function(dd, e, data){
					var ds = grid.store;
					var sm = infoGrid.getSelectionModel();
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
    view: new Ext.grid.GroupingView({
      forceFit     :true,
      groupTextTpl : '{text}'
    })
  });

  infoGrid.on('rowcontextmenu',
      function (grid, rowIndex, evt) {
    var sm = grid.getSelectionModel();
    sm.selectRow(rowIndex, sm.isSelected(rowIndex));
  },
  this
  );

  infoGrid.on('contextmenu', function(evt){evt.preventDefault();}, this);
  infoGrid.addListener('rowcontextmenu',onMessageContextMenu, this);
  infoGrid.store.load();

//back roles
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
  
	var inboxPanel = new Ext.Panel({
		auotWidth    : true,
		height       : 550,
		layout       : 'fit',
		autoScroll	 : true,
		items        : [
		       infoGrid
		]
	});
	
	tabsPanelInbox = new Ext.Panel({
     	region: 'center',
  	activeTab: 0,
  	items:[inboxPanel]
  });
	
	var viewport = new Ext.Viewport({
		layout : 'border',
		items  : [northPanel, tabsPanelInbox]
	});

});


