var frmDetails;
var frmSumary;

var allowBlackStatus;
var displayPreferences;
var infoMode;
var global = {};
var readMode;
var usernameText;
var previousUsername = '';
var canEdit = true;
var flagPoliciesPassword = false;
var flagValidateUsername = false;
var onlyPassword = false;

//var rendeToPage='document.body';

global.IC_UID        = '';
global.IS_UID        = '';
global.USR_FIRSTNAME = '';
global.aux           = '';
global.usrLastnameAnt = '';
global.usrFirstnameAnt = '';
global.usrLastnameNew = '';
global.usrFirstnameNew = '';
global.usrUid = '';

Ext.onReady(function () {
  Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
  Ext.QuickTips.init();

  if (TYPE == "onlyPassword") {
      onlyPassword = true;
  }

  if (MODE == "edit" || MODE == "") {
      flagPoliciesPassword = true;
  }

  if (USR_UID != "") {
      //Mode edit
      allowBlackStatus = true;

      if (infoMode) {
          //Mode info
          displayPreferences = "display: block;";
          readMode = true;
      } else {
          displayPreferences = "display: none;";
          readMode = false;
          canEdit  = false;
      }
  } else {
      //Mode new
      allowBlackStatus = false;

      displayPreferences = "display: none;";
      readMode = false;
      canEdit  = false;
  }


  var informationFields = new Ext.form.FieldSet({
    title : _('ID_PERSONAL_INFORMATION'),
    items : [

      {
        id         : 'USR_LASTNAME',
        fieldLabel : _('ID_LASTNAME'),
        xtype      : 'textfield',
        width      : 260,
        allowBlank : false,
        hidden     : false,
        enableKeyEvents: true, 
        listeners: {
          blur : function(ob)
          {
              checkLastName();
          },
          keyup: function(val, e){
                        result = val.getValue();
                        text = Ext.util.Format.uppercase(result);
                        val.setValue(text);
                        checkLastName();
          } , 
          keypress: function(val, e){
                        result = val.getValue();
                        text = Ext.util.Format.uppercase(result);
                        val.setValue(text);
                        checkFirstName();
          }
        }
      },
      {
        id         : 'USR_FIRSTNAME',
        fieldLabel : _('ID_FIRSTNAME'),
        xtype      : 'textfield',
        width      : 260,
        hidden     : false,
        allowBlank : false,
        enableKeyEvents: true, 
        listeners: {
          blur : function(ob)
          {
            checkFirstName();
          },
          keyup: function(val, e){
                        result = val.getValue();
                        text = Ext.util.Format.uppercase(result);
                        result.removeAccents();
                        val.setValue(text);
          } , 
          keypress: function(val, e){
                        result = val.getValue();
                        text = Ext.util.Format.uppercase(result);
                        result.removeAccents();
                        val.setValue(text);
          }
        }
      },
      {
        id         : 'USR_USERNAME',
        fieldLabel : _('ID_USER_ID'),
        xtype      : 'textfield',
        width      : 260,
        hidden	   : true,
        allowBlank : false
      },
      {
        xtype: 'label',
        fieldLabel: ' ',
        id:'usernameReview',
        width: 300,
        labelSeparator: ''
      },
      {
        id         : 'USR_EMAIL',
        fieldLabel : _('ID_EMAIL'),
        vtype      : 'email',
        xtype      : 'textfield',
        hidden     : true,
        width      : 260,
        allowBlank : false
      }]
  });

  var passwordFields = new Ext.form.FieldSet({
    title : _('ID_CHANGE_PASSWORD'),
    items : [
      {
        id         : 'USR_NEW_PASS',
        fieldLabel : _('ID_NEW_PASSWORD'),
        xtype      : 'textfield',
        helpText   : 'This is your first name dummy!',
        inputType  : 'password',
        width      : 260,
        allowBlank : allowBlackStatus,
        listeners: {
          blur : function(ob)
          {
            Ext.getCmp('saveB').disable();
            Ext.getCmp('cancelB').disable();
            var spanAjax = '<span style="font: 9px tahoma,arial,helvetica,sans-serif;">';
            var imageAjax = '<img width="13" height="13" border="0" src="/images/ajax-loader.gif">';
            var labelAjax = _('ID_PASSWORD_TESTING');

            Ext.getCmp('passwordReview').setText(spanAjax + imageAjax + labelAjax + '</span>', false);
            Ext.getCmp('passwordReview').setVisible(true);

            var passwordText = this.getValue();

            Ext.Ajax.request({
              url    : 'myProfile_Ajax',
              method:'POST',
              params : {
                'action'        : 'testPassword',
                'PASSWORD_TEXT' : passwordText
              },
              success: function(r,o){
                var resp = Ext.util.JSON.decode(r.responseText);

                if (resp.STATUS) {
                  flagPoliciesPassword = true;
                } else {
                  flagPoliciesPassword = false;
                }

               // ************** validate password ***************
               var styleMessageGreen  ='<span style="color: green; font: 9px tahoma,arial,helvetica,sans-serif;"><img width="13" height="13" border="0" src="/images/dialog-ok-apply.png">';
               var styleMessageYellow  ='<span style="color: #FE9A2E; font: 9px tahoma,arial,helvetica,sans-serif;"><img width="13" height="13" border="0" src="/images/dialog-ok-apply.png">';
               var styleMessageRed = '<span style="color: red; font: 9px tahoma,arial,helvetica,sans-serif;"><img width="13" height="13" border="0" src="/images/delete.png">';
               var endStyle = '</span>';
               
               // ************** end validate password ************
               var expreg = /(?=^.{7,20}$)((?=.*[A-Za-z0-9])(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]))^.*/;
                
                Ext.getCmp('passwordReview').setText(resp.DESCRIPTION, false);
                Ext.getCmp('passwordReview').setVisible(false);

                //if(passwordText.length > 7)
                //{  
                if ((passwordText.match(expreg))) 
                 {

                    //Ext.getCmp("USR_CNF_PASS").getValue ;
                    //document.getElementById('form[__CONFIRM_PASSWORD__]').readOnly = false;
                    var simple = 'Faible s\u00E9curit\u00E9';
                    var medium = 'S\u00E9curit\u00E9 moyenne';
                    var complex = 'Haute s\u00E9curit\u00E9';
                    var rpta = passwordLevel(passwordText);

                    var message = '';
                    //document.getElementById('message').style.display = '';
                    if(rpta < 30)
                    {
                      message = styleMessageRed + simple + endStyle;
                      textColor = '#FA5858';
                    }
                    else
                    {
                      if(rpta < 60)
                      {
                        message = styleMessageYellow + medium + endStyle;
                        textColor = '#B18904';
                      }
                      else
                      {
                        message = styleMessageGreen + complex + endStyle;
                        textColor  = '#8BD672';
                      }
                    }
                    
                    Ext.getCmp('complexityReview').setText(message, false);
                    Ext.getCmp('complexityReview').setVisible(true);
                }
                else
                {
                  var messageError = styleMessageRed+"Merci de respecter le format du mot de passe"+endStyle;
                  if(passwordText != "")
                  {
                   Ext.getCmp('complexityReview').setText("", false);
                   Ext.getCmp('complexityReview').setVisible(false);
                   Ext.getCmp('passwordReview').setText(messageError, false);
                   Ext.getCmp('passwordReview').setVisible(true);

                  return false; 
                  }
                  else
                  {
                     Ext.getCmp('complexityReview').setText("", false);
                     Ext.getCmp('complexityReview').setVisible(false);
                     Ext.getCmp('passwordReview').setText("", false);
                     Ext.getCmp('passwordReview').setVisible(false);  
                     Ext.getCmp('passwordConfirm').setText("");  
                     Ext.getCmp('passwordConfirm').setVisible(false);                  
                  }  
                   
                }
                
                
                Ext.getCmp('saveB').enable();
                Ext.getCmp('cancelB').enable();
              },
              failure: function () {
                Ext.MessageBox.show({
                  title: 'Error',
                  msg: 'Failed to store data',
                  buttons: Ext.MessageBox.OK,
                  animEl: 'mb9',
                  icon: Ext.MessageBox.ERROR
                });
                Ext.getCmp('saveB').enable();
                Ext.getCmp('cancelB').enable();
              }
            });

            

            if (Ext.getCmp('USR_CNF_PASS').getValue() != '') {
              userExecuteEvent(document.getElementById('USR_CNF_PASS'), 'blur');
            }

          }
        }
      },
      {
        xtype: 'label',
        fieldLabel: ' ',
        id:'passwordReview',
        width: 300,
        labelSeparator: ''
      },
      {
        xtype: 'label',
        fieldLabel: ' ',
        id:'complexityReview',
        width: 300,
        labelSeparator: ''
      },
      {
        id         : 'USR_CNF_PASS',
        fieldLabel : _('ID_CONFIRM_PASSWORD'),
        xtype      : 'textfield',
        inputType  : 'password',
        width      : 260,
        allowBlank : allowBlackStatus,
        listeners: {
          blur : function(ob)
          {
            var passwordText    = Ext.getCmp('USR_NEW_PASS').getValue();
            var passwordConfirm = this.getValue();

            if (passwordText != passwordConfirm) {

              var spanErrorConfirm  = '<span style="color: red; font: 9px tahoma,arial,helvetica,sans-serif;">';
              var imageErrorConfirm = '<img width="13" height="13" border="0" src="/images/delete.png">';
             // var labelErrorConfirm = _('ID_NEW_PASS_SAME_OLD_PASS');
              var labelErrorConfirm = "Le mot de passe confirmé est différent de celui saisi initialement.";
              Ext.getCmp('passwordConfirm').setText(spanErrorConfirm + imageErrorConfirm + labelErrorConfirm + '</span>', false);
              if(passwordText != "")
                Ext.getCmp('passwordConfirm').setVisible(true);
              else
              {
                Ext.getCmp('passwordConfirm').setText("");  
                Ext.getCmp('passwordConfirm').setVisible(false);
              }
              this.setValue("");
              Ext.getCmp('saveB').disable();
            } else {
              
              if(this.getValue() !="")
              {
                Ext.getCmp('saveB').enable();
                var messageConf = "mot de passe confirmé";
                var styleMessageGreen  ='<span style="color: green; font: 9px tahoma,arial,helvetica,sans-serif;"><img width="13" height="13" border="0" src="/images/dialog-ok-apply.png">';
                Ext.getCmp('passwordConfirm').setText(styleMessageGreen + messageConf + '</span>', false);
                Ext.getCmp('passwordConfirm').setVisible(true);
              //Ext.getCmp('passwordConfirm').setVisible(false);
              }
            }
          }
        }
      },
      {
        xtype: 'label',
        fieldLabel: ' ',
        id:'passwordConfirm',
        width: 300,
        labelSeparator: ''
      }

    ]
  });

  frmDetails = new Ext.FormPanel({
    id            : 'frmDetails',
    labelWidth    : 150,
    labelAlign    :'left',
    autoScroll    : true,
    fileUpload    : true,
    width         : 600,
    bodyStyle     : 'padding:10px',
    waitMsgTarget : true,
    frame         : true,
    defaults : {
      anchor     : '100%',
      allowBlank : false,
      resizable  : true,
      msgTarget  : 'side',
      align      : 'center'
    },
    items : [
      informationFields,
      passwordFields
    ],
    buttons : [
      {
        text   : 'Enregister',
        id     : 'saveB',
        handler: saveUser
      },
      {
        text    : _('ID_CANCEL'),
        id      : 'cancelB',
        hidden	: true,
        handler : function(){
          if (!infoMode) {
            location.href = 'users_List';
          }
          else{
            frmDetails.hide();
            frmSumary.show();
          }
          //location.href = 'users_List';
        }
        //hidden:readMode
      }
    ]
  });

  /*if (onlyPassword == true)
    frmDetails.remove(informationFields);
  */
  informationFields2 = new Ext.form.FieldSet({
    title : _('ID_PERSONAL_INFORMATION'),
    items : [
      {
        id         : 'USR_FIRSTNAME2',
        fieldLabel : _('ID_FIRSTNAME'),
        xtype      : 'label',
        width      : 260
      },
      {
        id         : 'USR_LASTNAME2',
        fieldLabel : _('ID_LASTNAME'),
        xtype      : 'label',
        width      : 260
      },
      {
        id         : 'USR_USERNAME2',
        fieldLabel : _('ID_USER_ID'),
        xtype      : 'label',
        width      : 260
      },
      {
        id         : 'USR_EMAIL2',
        fieldLabel : _('ID_EMAIL'),
        xtype      : 'label',
        width      : 260
      }
    ]
  });

  passwordFields2 = new Ext.form.FieldSet({
    title : _('ID_PASSWORD'),
    items : [
      {
        id         : 'USR_PASSWORD2',
        fieldLabel : _('ID_PASSWORD'),
        xtype      : 'label',
        width      : 260,
        text       : '  *******************'
      }
    ]
  });

  
  frmSumary = new Ext.FormPanel({
    id            : 'frmSumary',
    labelWidth    : 320,
    labelAlign    : 'right',
    autoScroll    : true,
    fileUpload    : true,
    width         : 800,
    //height:1000,
    bodyStyle     : 'padding:10px',
    waitMsgTarget : true,
    frame         : true,
    items         : [
      informationFields2,
      passwordFields2
           ],
    buttons : [
      {
        text    : _('ID_EDIT'),
        handler : editUser,
        hidden  : canEdit
      }
    ]
  });

  if (USR_UID != "") {
      //Mode edit
      loadUserData();
  } else {
      //Mode new
      loadData();
  }

  if (infoMode) {
      document.body.appendChild(defineUserPanel());
      frmSumary.render('users-panel');
  } else {
      frmDetails.render(document.body);
  }

  Ext.getCmp('passwordReview').setVisible(false);
  Ext.getCmp('passwordConfirm').setVisible(false);
  Ext.getCmp('usernameReview').setVisible(false);
  Ext.getCmp('complexityReview').setVisible(false);

  var spanAjax  = '<span style="font: 9px tahoma,arial,helvetica,sans-serif;">';
  var imageAjax = '<img width="13" height="13" border="0" src="/images/ajax-loader.gif">';
  var labelAjax = _('ID_PASSWORD_TESTING');

  Ext.getCmp('passwordReview').setText(spanAjax + imageAjax + labelAjax + '</span>', false);

  var labelAjax = _('ID_USERNAME_TESTING');

  Ext.getCmp('usernameReview').setText(spanAjax + imageAjax + labelAjax + '</span>', false);
});

  function passwordLevel (p){
  l = 0;
  v1 = 'aeiou1234567890';
  v2 = 'AEIOUbcdfghjklmnpqrst';
  v3 = 'vxyzBCDFGHJKLMNPQRST';
  v4 = 'VXYZ$@#';
  for (i = 0; i < p.length; i++){
    if (v1.indexOf(p[i]) != -1) l += 1;
    else if (v2.indexOf(p[i]) != -1) l += 2;
    else if (v3.indexOf(p[i]) != -1) l += 3;
    else if (v4.indexOf(p[i]) != -1) l += 4;
    else l += 5;
  }
  l *= 3;
  if(l > 100)l = 100;
  return l;
}

function defineUserPanel()
{
  var isIE           = ( navigator.userAgent.indexOf('MSIE')>0 ) ? true : false;
  var eDivPanel      = document.createElement("div");
  var eDivUsersPanel = document.createElement("div");
  eDivPanel.setAttribute('id', 'panel');
  eDivUsersPanel.setAttribute('id', 'users-panel');

  if (isIE) {
    eDivPanel.style.setAttribute('text-align','center');
    eDivPanel.style.setAttribute('margin','0px 0px');
    eDivUsersPanel.style.setAttribute('width','800px');
    eDivUsersPanel.style.setAttribute('margin','0px auto');
    eDivUsersPanel.style.setAttribute('text-align','left');
  } else {
    eDivPanel.style.setProperty('text-align','center',null);
    eDivPanel.style.setProperty('margin','0px 0px',null);
    eDivUsersPanel.style.setProperty('width','800px',null);
    eDivUsersPanel.style.setProperty('margin','0px auto',null);
    eDivUsersPanel.style.setProperty('text-align','left',null);
  }

  eDivPanel.appendChild(eDivUsersPanel);

  return eDivPanel;
}

function editUser()
{
    document.body.appendChild(defineUserPanel());
    frmDetails.render('users-panel');

    frmSumary.hide();
    frmDetails.show();
}

function saveUser()
{

  if (!flagPoliciesPassword) {
    if (Ext.getCmp('USR_NEW_PASS').getValue() == '') {
      //Ext.Msg.alert( _('ID_ERROR'), "_('ID_PASSWD_REQUIRED')");
    } else {
      Ext.Msg.alert( _('ID_ERROR'), Ext.getCmp('passwordReview').html);
    }
    //return false;
  }

  var newPass  = frmDetails.getForm().findField('USR_NEW_PASS').getValue();
  var confPass = frmDetails.getForm().findField('USR_CNF_PASS').getValue();

  usrFirstnameNew = frmDetails.getForm().findField('USR_FIRSTNAME').getValue();
  usrLastnameNew = frmDetails.getForm().findField('USR_LASTNAME').getValue();
  if (confPass === newPass) {
    Ext.getCmp('frmDetails').getForm().submit({
      url    : 'myProfile_Ajax',
      params : {
        action   : 'saveUser',
        USR_UID  : USR_UID,
        USR_CITY : global.IS_UID

      },
      waitMsg : _('ID_SAVING'),
      timeout : 36000,
      success : function (obj, resp) {
    	  
    	  a  = resp.result;
    	  if(a.success == true)
    	  {
    		  if(a.msg){
    			  var message = resp.result.msg;
    	          Ext.Msg.alert(_('ID_WARNING'), '<strong>'+message+'</strong>');
    	      }
    		  //frmDetails.hide();
    		  frmDetails.render(document.body);
    		  //frmDetails.show();
    	  }
        document.location.href = document.location.href;
        /*if (!infoMode) {
          location.href = 'users_List';
        } else {
         location.href = '../users/myInfo?type=reload';
        }*/
        loadUserData();
      },
      failure : function (obj, resp) {
    	  
        if (typeof resp.result  == "undefined")
        {
          Ext.Msg.alert(_('ID_ERROR'), _('ID_SOME_FIELDS_REQUIRED'));
        } else{
          if (resp.result.msg){
            var message = resp.result.msg.split(',');
            Ext.Msg.alert(_('ID_WARNING'), '<strong>'+message[0]+'<strong><br/><br/>'+message[1]+'<br/><br/>'+message[2]);
          }

          if (resp.result.fileError) {
            Ext.Msg.alert(_('ID_ERROR'), _('ID_FILE_TOO_BIG'));
          }

          if (resp.result.error) {
            Ext.Msg.alert(_('ID_ERROR'), resp.result.error);
          }
        }
      }
    });

    // SAVE USER IN CASES
    urlData = "../fieldcontrol/myProfileUpdateCases.php";
      /*Ext.MessageBox.show({
              msg : 'Chargement, Veuillez patienter ...',
              progressText : 'Chargement ...',
              width : 300,
              wait : true,
              waitConfig : {
                interval : 200
              }
        });*/
    Ext.Ajax.request({
           url : urlData,
           params : {
             USR_LASTNAMEORIG : usrLastnameAnt,
             USR_FIRSTNAMEORIG : usrFirstnameAnt,
             USR_LASTNAMENEW : usrLastnameNew,
             USR_FIRSTNAMENEW : usrFirstnameNew,
             USR_UID : USR_UID
           },
           success: function (result, request) {
             /*var response = Ext.util.JSON.decode(result.responseText);
             if (response.success) {
                Ext.MessageBox.hide();
               //alert(response.message);*/
             //}
             //else {
                /*Ext.MessageBox.hide();
               PMExt.warning(_('ID_WARNING'), response.message);*/
            },
           failure: function (result, request) {
            //Ext.MessageBox.hide();
           }
         });

  }
  else {
    var messageErrorMatch = "Veuilliez saisir la confirmation de votre mot de passe";
    //Ext.Msg.show(messageErrorMatch);
    Ext.MessageBox.show({
                  title: 'Error',
                  msg: 'Veuilliez saisir la confirmation de votre mot de passe',
                  buttons: Ext.MessageBox.OK,
                  animEl: 'mb9',
                  icon: Ext.MessageBox.ERROR
                });
  }
}

//Load data
function loadData()
{
    
}

//Load data for Edit mode
function loadUserData()
{
    Ext.Ajax.request({
        url: "myProfile_Ajax",
        method: "POST",
        params: {
            "action": "userData",
            USR_UID: USR_UID
        },
        waitMsg: _("ID_UPLOADING_PROCESS_FILE"),
        success: function (r, o) {
            Ext.getCmp("USR_USERNAME").setValue("");          
            var data = Ext.util.JSON.decode(r.responseText);
            usrLastnameAnt = data.user.USR_LASTNAME;
            usrFirstnameAnt = data.user.USR_FIRSTNAME;

            Ext.getCmp("frmDetails").getForm().setValues({
                USR_FIRSTNAME : data.user.USR_FIRSTNAME,
                USR_LASTNAME  : data.user.USR_LASTNAME,
                USR_USERNAME  : data.user.USR_USERNAME,
                USR_EMAIL     : data.user.USR_EMAIL
            });

            if (infoMode) {
                Ext.getCmp("USR_FIRSTNAME2").setText(data.user.USR_FIRSTNAME);
                Ext.getCmp("USR_LASTNAME2").setText(data.user.USR_LASTNAME);
                Ext.getCmp("USR_USERNAME2").setText(data.user.USR_USERNAME);
                Ext.getCmp("USR_EMAIL2").setText(data.user.USR_EMAIL);
            } else {
                //
            }

            previousUsername = Ext.getCmp("USR_USERNAME").getValue();
        },
        failure: function (r, o) {
            //viewport.getEl().unmask();
        }
    });
}

function userExecuteEvent(element, event)
{
    if (document.createEventObject) {
        //IE
        var evt = document.createEventObject();

        return element.fireEvent("on" + event, evt)
    } else {
        //Firefox + Others
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent(event, true, true); //event type,bubbling,cancelable

        return !element.dispatchEvent(evt);
    }
}

String.prototype.removeAccents = function ()
{
  var __r = 
  {
    'À':'A','Á':'A','Â':'A','Ã':'A','Ä':'A','Å':'A','Æ':'E',

    'È':'E','É':'E','Ê':'E','Ë':'E',

    'Ì':'I','Í':'I','Î':'I',

    'Ò':'O','Ó':'O','Ô':'O','Ö':'O',

    'Ù':'U','Ú':'U','Û':'U','Ü':'U',

    'Ñ':'N'
  };
  
  return this.replace(/[ÀÁÂÃÄÅÆÈÉÊËÌÍÎÒÓÔÖÙÚÛÜÑ]/gi, function(m)
  {
    var ret = __r[m.toUpperCase()];
    if (m === m.toLowerCase())
      ret = ret.toLowerCase();
    return ret;
  });

};

function disableCtrlKeyCombination(evt)
{
  var e = window.event || evt;
  if (e.ctrlKey) { 
      switch(e.keyCode) { 
        case 67: //Ctrl-C -- copy --- 
        case 86: //Ctrl-V -- paste --- 
        case 88: //Ctrl-X -- cut --- 
        e.keyCode = 0; 
        return false; 

        default: 
        break; 
    }
  }
}

var checkLastName = function() {
  var usrLastname = Ext.getCmp("frmDetails").getForm().getFieldValues().USR_LASTNAME;
  var retUsrLast = usrLastname.removeAccents();
  Ext.getCmp("frmDetails").getForm().setValues({
                USR_LASTNAME : retUsrLast
    });

};

var checkFirstName = function() {
  var usrFirstname = Ext.getCmp("frmDetails").getForm().getFieldValues().USR_FIRSTNAME;
  
  var retUsrFirst = usrFirstname.removeAccents();
  Ext.getCmp("frmDetails").getForm().setValues({
                USR_FIRSTNAME : retUsrFirst
    }); 
};

Ext.intercept(Ext.form.Field.prototype, 'initComponent', function() {
  var fl = this.fieldLabel, h = this.helpText;
  //console.log(this);
   /*var spanAjax = '<span style="font: 9px tahoma,arial,helvetica,sans-serif;">';
            var imageAjax = '<img width="13" height="13" border="0" src="/images/ajax-loader.gif">';
            var labelAjax = _('ID_PASSWORD_TESTING');
  */            
  /*if (h && h !== '' && fl) {
    var spanHelp = '<span style="color:blue;"  ext:qtip="'+h+'">';
    var imgHelp = '<img width="13" height="13" src="/images/dialog-ok-apply.png"></span> ';
    this.fieldLabel = fl+spanHelp+'<img width="13" height="13" src="/images/dialog-ok-apply.png">?</span> ';
  }*/
});

document.onkeydown = disableCtrlKeyCombination;
document.oncontextmenu=new Function("return false");
