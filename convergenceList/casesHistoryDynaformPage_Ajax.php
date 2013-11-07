
<?php
G::loadClass ( 'pmFunctions' );
G::LoadClass ( 'form' );       
$actionAjax = isset($_REQUEST['actionAjax'])?$_REQUEST['actionAjax']: null;
$APP_UID = isset($_GET['APP_UID'])?$_GET['APP_UID']: null;
$FINDEX  = isset($_GET['FINDEX'])?$_GET['FINDEX']:null;
$PRO_UID = isset($_REQUEST['PRO_UID'])?$_REQUEST['PRO_UID']:null;
$TAS_UID = isset($_GET['TAS_UID'])?$_GET['TAS_UID']:null;
$USR_UID = isset($_GET['USR_UID'])?$_GET['USR_UID']:null;
$ACTIONTYPE = isset($_GET['ACTIONTYPE'])?$_GET['ACTIONTYPE']:null;
$ADAPTIVEHEIGHT = isset($_GET['adaptiveHeight'])?$_GET['adaptiveHeight']:null;
$NUM_DOSSIER = isset($_GET['num_dossier'])?$_GET['num_dossier']:null;
$TABLE = isset($_GET['table'])?$_GET['table']:null;  

if($actionAjax=="HistoryLog"){    
    global $G_PUBLISH;        
    $oHeadPublisher =& headPublisher::getSingleton(); 
    G::loadClass('configuration');    
    $conf = new Configurations;
    $oHeadPublisher->assign('APP_UID', $APP_UID);
    $oHeadPublisher->assign('ADAPTIVEHEIGHT', $ADAPTIVEHEIGHT);
    $oHeadPublisher->assign('NUM_DOSSIER', $NUM_DOSSIER);
    $oHeadPublisher->assign('TABLE', $TABLE);
    $oHeadPublisher->addExtJsScript('convergenceList/caseHistoryDynaformLog', true );    //adding a javascript file .js
    $oHeadPublisher->addContent    ('convergenceList/caseHistoryDynaformPage'); //adding a html file  .html.
    $oHeadPublisher->assign('pageSize', $conf->getEnvSetting('casesListRowNumber'));    
    G::RenderPage('publish', 'extJs');
}
if($actionAjax=='historyDynaformGrid_Ajax'){
    require_once ("classes/model/Dynaform.php");
    G::LoadClass('case');
    G::LoadClass("BasePeer" );      
  
    global $G_PUBLISH;
    $oCase = new Cases();      
    $aProcesses = Array();
  
    $query = " SELECT DISTINCT DYN_UID FROM APP_HISTORY 
             WHERE APP_UID = '".$APP_UID."' 
             ORDER BY HISTORY_DATE  ASC
           ";
    $select = executeQuery($query);      
    $o = new Dynaform();
    $aProcesses = array();
   
    foreach($select as $index)
    {
      	 
        $o->setDynUid($index['DYN_UID']);
        $aFields['DYN_TITLE'] = $o->getDynTitle();
        $aFields['DYN_UID'] = $index['DYN_UID'];
        $aFields['EDIT'] = G::LoadTranslation('ID_EDIT');
        $aFields['PRO_UID'] = $PRO_UID;
        $aFields['APP_UID'] = $APP_UID;
        $aFields['TAS_UID'] = $TAS_UID;
        $aProcesses[] = $aFields;            
             
        
    }                         
    $r = '';
    $r->data = $aProcesses;
    $r->totalCount = count($aProcesses);
      
    echo G::json_encode($r);
  
}
    
if($actionAjax=='showDynaformListHistory'){

  //!dataIndex
    $_POST["APP_UID"] = $_REQUEST["APP_UID"];
    $_POST["DYN_UID"] = $_REQUEST["DYN_UID"];
    $_POST["PRO_UID"] = $_REQUEST["PRO_UID"];
    $_POST["TAS_UID"] = $_REQUEST["TAS_UID"];
      
?>
    <link rel="stylesheet" type="text/css" href="/css/classic.css" />
    <style type="text/css">
      html{
        color:black !important; 
      }
       body{
        color:black !important; 
      }
    </style>
    <script language="Javascript">
       globalMd5Return=function(s,raw,hexcase,chrsz){raw=raw||false;hexcase=hexcase||false;chrsz=chrsz||8;function safe_add(x,y){var lsw=(x&0xFFFF)+(y&0xFFFF);var msw=(x>>16)+(y>>16)+(lsw>>16);return(msw<<16)|(lsw&0xFFFF)}function bit_rol(num,cnt){return(num<<cnt)|(num>>>(32-cnt))}function md5_cmn(q,a,b,x,s,t){return safe_add(bit_rol(safe_add(safe_add(a,q),safe_add(x,t)),s),b)}function md5_ff(a,b,c,d,x,s,t){return md5_cmn((b&c)|((~b)&d),a,b,x,s,t)}function md5_gg(a,b,c,d,x,s,t){return md5_cmn((b&d)|(c&(~d)),a,b,x,s,t)}function md5_hh(a,b,c,d,x,s,t){return md5_cmn(b^c^d,a,b,x,s,t)}function md5_ii(a,b,c,d,x,s,t){return md5_cmn(c^(b|(~d)),a,b,x,s,t)}function core_md5(x,len){x[len>>5]|=0x80<<((len)%32);x[(((len+64)>>>9)<<4)+14]=len;var a=1732584193;var b=-271733879;var c=-1732584194;var d=271733878;for(var i=0;i<x.length;i+=16){var olda=a;var oldb=b;var oldc=c;var oldd=d;a=md5_ff(a,b,c,d,x[i+0],7,-680876936);d=md5_ff(d,a,b,c,x[i+1],12,-389564586);c=md5_ff(c,d,a,b,x[i+2],17,606105819);b=md5_ff(b,c,d,a,x[i+3],22,-1044525330);a=md5_ff(a,b,c,d,x[i+4],7,-176418897);d=md5_ff(d,a,b,c,x[i+5],12,1200080426);c=md5_ff(c,d,a,b,x[i+6],17,-1473231341);b=md5_ff(b,c,d,a,x[i+7],22,-45705983);a=md5_ff(a,b,c,d,x[i+8],7,1770035416);d=md5_ff(d,a,b,c,x[i+9],12,-1958414417);c=md5_ff(c,d,a,b,x[i+10],17,-42063);b=md5_ff(b,c,d,a,x[i+11],22,-1990404162);a=md5_ff(a,b,c,d,x[i+12],7,1804603682);d=md5_ff(d,a,b,c,x[i+13],12,-40341101);c=md5_ff(c,d,a,b,x[i+14],17,-1502002290);b=md5_ff(b,c,d,a,x[i+15],22,1236535329);a=md5_gg(a,b,c,d,x[i+1],5,-165796510);d=md5_gg(d,a,b,c,x[i+6],9,-1069501632);c=md5_gg(c,d,a,b,x[i+11],14,643717713);b=md5_gg(b,c,d,a,x[i+0],20,-373897302);a=md5_gg(a,b,c,d,x[i+5],5,-701558691);d=md5_gg(d,a,b,c,x[i+10],9,38016083);c=md5_gg(c,d,a,b,x[i+15],14,-660478335);b=md5_gg(b,c,d,a,x[i+4],20,-405537848);a=md5_gg(a,b,c,d,x[i+9],5,568446438);d=md5_gg(d,a,b,c,x[i+14],9,-1019803690);c=md5_gg(c,d,a,b,x[i+3],14,-187363961);b=md5_gg(b,c,d,a,x[i+8],20,1163531501);a=md5_gg(a,b,c,d,x[i+13],5,-1444681467);d=md5_gg(d,a,b,c,x[i+2],9,-51403784);c=md5_gg(c,d,a,b,x[i+7],14,1735328473);b=md5_gg(b,c,d,a,x[i+12],20,-1926607734);a=md5_hh(a,b,c,d,x[i+5],4,-378558);d=md5_hh(d,a,b,c,x[i+8],11,-2022574463);c=md5_hh(c,d,a,b,x[i+11],16,1839030562);b=md5_hh(b,c,d,a,x[i+14],23,-35309556);a=md5_hh(a,b,c,d,x[i+1],4,-1530992060);d=md5_hh(d,a,b,c,x[i+4],11,1272893353);c=md5_hh(c,d,a,b,x[i+7],16,-155497632);b=md5_hh(b,c,d,a,x[i+10],23,-1094730640);a=md5_hh(a,b,c,d,x[i+13],4,681279174);d=md5_hh(d,a,b,c,x[i+0],11,-358537222);c=md5_hh(c,d,a,b,x[i+3],16,-722521979);b=md5_hh(b,c,d,a,x[i+6],23,76029189);a=md5_hh(a,b,c,d,x[i+9],4,-640364487);d=md5_hh(d,a,b,c,x[i+12],11,-421815835);c=md5_hh(c,d,a,b,x[i+15],16,530742520);b=md5_hh(b,c,d,a,x[i+2],23,-995338651);a=md5_ii(a,b,c,d,x[i+0],6,-198630844);d=md5_ii(d,a,b,c,x[i+7],10,1126891415);c=md5_ii(c,d,a,b,x[i+14],15,-1416354905);b=md5_ii(b,c,d,a,x[i+5],21,-57434055);a=md5_ii(a,b,c,d,x[i+12],6,1700485571);d=md5_ii(d,a,b,c,x[i+3],10,-1894986606);c=md5_ii(c,d,a,b,x[i+10],15,-1051523);b=md5_ii(b,c,d,a,x[i+1],21,-2054922799);a=md5_ii(a,b,c,d,x[i+8],6,1873313359);d=md5_ii(d,a,b,c,x[i+15],10,-30611744);c=md5_ii(c,d,a,b,x[i+6],15,-1560198380);b=md5_ii(b,c,d,a,x[i+13],21,1309151649);a=md5_ii(a,b,c,d,x[i+4],6,-145523070);d=md5_ii(d,a,b,c,x[i+11],10,-1120210379);c=md5_ii(c,d,a,b,x[i+2],15,718787259);b=md5_ii(b,c,d,a,x[i+9],21,-343485551);a=safe_add(a,olda);b=safe_add(b,oldb);c=safe_add(c,oldc);d=safe_add(d,oldd)}return[a,b,c,d]}function str2binl(str){var bin=[];var mask=(1<<chrsz)-1;for(var i=0;i<str.length*chrsz;i+=chrsz){bin[i>>5]|=(str.charCodeAt(i/chrsz)&mask)<<(i%32)}return bin}function binl2str(bin){var str="";var mask=(1<<chrsz)-1;for(var i=0;i<bin.length*32;i+=chrsz){str+=String.fromCharCode((bin[i>>5]>>>(i%32))&mask)}return str}function binl2hex(binarray){var hex_tab=hexcase?"0123456789ABCDEF":"0123456789abcdef";var str="";for(var i=0;i<binarray.length*4;i++){str+=hex_tab.charAt((binarray[i>>2]>>((i%4)*8+4))&0xF)+hex_tab.charAt((binarray[i>>2]>>((i%4)*8))&0xF)}return str}return(raw?binl2str(core_md5(str2binl(s),s.length*chrsz)):binl2hex(core_md5(str2binl(s),s.length*chrsz)))};
        
        //!Code that simulated reload library javascript maborak
        var leimnud = {};        
        leimnud.exec = "";
        leimnud.fix = {};
        leimnud.fix.memoryLeak  = "";
        leimnud.browser = {};
        leimnud.browser.isIphone  = "";
        leimnud.iphone = {};
        leimnud.iphone.make = function(){};      
          function ajax_function(ajax_server, funcion, parameters, method){          
          }       
        
          function toggleTable(tablename){
            //table= document.getElementByName(tablename);
            table= document.getElementById(tablename);
            if(table.style.display == ''){
              table.style.display = 'none';
            }else{
              table.style.display = '';
            }
          }
          
          function noesFuncion(idIframe) {
            window.parent.tabIframeWidthFix2(idIframe);
          }
        
          function onResizeIframe(idIframe){
          
            
            window.onresize = noesFuncion(idIframe);
          
          }
          
        var showDynaformHistoryGlobal = {};
        showDynaformHistoryGlobal.dynUID = '';
        showDynaformHistoryGlobal.tablename = '';
        showDynaformHistoryGlobal.dynDate = '';
        showDynaformHistoryGlobal.dynTitle = '';          
          function showDynaformHistory(dynUID,tablename,dynDate,dynTitle){            
            showDynaformHistoryGlobal.dynUID = dynUID;
            showDynaformHistoryGlobal.tablename = tablename;
            showDynaformHistoryGlobal.dynDate = dynDate;
            showDynaformHistoryGlobal.dynTitle = dynTitle;
            
            var dynUID = showDynaformHistoryGlobal.dynUID;
            var tablename = showDynaformHistoryGlobal.tablename;
            var dynDate = showDynaformHistoryGlobal.dynDate;
            var dynTitle = showDynaformHistoryGlobal.dynTitle;
            
            var idUnique = globalMd5Return(dynUID+tablename+dynDate+dynTitle);
            
            var tabData =  window.parent.Ext.util.JSON.encode(showDynaformHistoryGlobal);
            var tabName = 'dynaformChangeLogViewHistory'+idUnique;
            var tabTitle = 'View('+dynTitle+' '+dynDate+')';
            
            window.parent.ActionTabFrameGlobal.tabData = tabData;    
            window.parent.ActionTabFrameGlobal.tabName = tabName;
            window.parent.ActionTabFrameGlobal.tabTitle = tabTitle;                       
          
            window.parent.Actions.tabFrame(tabName);
          }
      </script>
<?php
     
	        require_once 'classes/model/AppHistory.php';
	        $G_PUBLISH = new Publisher();
	        $G_PUBLISH->AddContent('view', 'cases/cases_DynaformHistory');
          
	        G::RenderPage('publish', 'raw');    
}
    
if($actionAjax=='dynaformChangeLogViewHistory'){
    
?>
      <link rel="stylesheet" type="text/css" href="/css/classic.css" />
      <style type="text/css">
        html{
          color:black !important; 
        }
         body{
          color:black !important; 
        }
      </style>
      <script language="Javascript">
      
      
      
        //!Code that simulated reload library javascript maborak
        var leimnud = {};        
        leimnud.exec = "";
        leimnud.fix = {};
        leimnud.fix.memoryLeak  = "";
        leimnud.browser = {};
        leimnud.browser.isIphone  = "";
        leimnud.iphone = {};
        leimnud.iphone.make = function(){};      
        function ajax_function(ajax_server, funcion, parameters, method){          
        }
        //!
      </script>
<?php
    
      $_POST['DYN_UID']= $_REQUEST['DYN_UID'];
      $_POST['HISTORY_ID']= $_REQUEST['HISTORY_ID'];       
     
      global $G_PUBLISH;
      $G_PUBLISH = new Publisher();
      $FieldsHistory=unserialize($_SESSION['HISTORY_DATA']);
      $Fields['APP_DATA'] = $FieldsHistory[$_POST['HISTORY_ID']];
      $Fields['APP_DATA']['__DYNAFORM_OPTIONS']['PREVIOUS_STEP_LABEL'] = '';
      $Fields['APP_DATA']['__DYNAFORM_OPTIONS']['NEXT_STEP_LABEL'] = '';
      $Fields['APP_DATA']['__DYNAFORM_OPTIONS']['NEXT_STEP'] = '#';
      $Fields['APP_DATA']['__DYNAFORM_OPTIONS']['NEXT_ACTION'] = 'return false;';
      $G_PUBLISH->AddContent('dynaform', 'xmlform', $PRO_UID . '/' . $_POST['DYN_UID'], '', $Fields['APP_DATA'], '', '', 'view');
      
      
?>      
        <script language="javascript">
<?php
      global $G_FORM;
?>
          function loadForm_<?php echo $G_FORM->id;?>(parametro1){
          
          }
        </script>
<?php

      G::RenderPage('publish', 'raw');
    
}
if($actionAjax== 'historyDynaformGridPreview')
{
    
      $CURRENTDATETIME = '';
      if(isset($_GET['CURRENTDATETIME']) && $_GET['CURRENTDATETIME']!=''){

        $CURRENTDATETIME=$_GET['CURRENTDATETIME'];

      }
   
    $oHeadPublisher = & headPublisher::getSingleton();
    $header = $oHeadPublisher->printHeader();
    $header .= $oHeadPublisher->getExtJsStylesheets(SYS_SKIN);
    
        
?>
      <link href="/plugin/convergenceList/convergenceList.css" rel="stylesheet" type="text/css" media="screen" /> 
      <script language="Javascript">
      
        //!Code that simulated reload library javascript maborak
        var leimnud = {};        
        leimnud.exec = "";
        leimnud.fix = {};
        leimnud.fix.memoryLeak  = "";
        leimnud.browser = {};
        leimnud.browser.isIphone  = "";
        leimnud.iphone = {};
        leimnud.iphone.make = function(){};      
        function ajax_function(ajax_server, funcion, parameters, method){          
        }
       
      </script>
<?php
    
      //!dataIndex
      $_POST["DYN_UID"] = $_REQUEST["DYN_UID"];
      
      G::LoadClass('case');
      global $G_PUBLISH;
	  global $_DBArray;
      $G_PUBLISH = new Publisher();
      $oCase = new Cases();
      //G::pr($_COOKIE);
      if(isset($_REQUEST['ACTIONSAVE']) && $_REQUEST['ACTIONSAVE'] == 1 && !isset($_COOKIE['fe_typo_user']))
      { 
         echo "<script language=Javascript>alert('Vos changements ont \u00E9t\u00E9 enregistr\u00E9s avec succ\u00E9s');</script>";
         $_REQUEST['SAVEDATA'] = 0;
      }
      if(isset($_REQUEST['ACTIONSAVE']) && $_REQUEST['ACTIONSAVE'] == 1)
      { 
        echo "<script language='javascript'>  var TabPanel = parent.parent.Ext.getCmp('iframe-DynaForms'); TabPanel.doAutoLoad();</script>";
      }          
      
	  if(isset($_SESSION['APPLICATION_EDIT']) && $_SESSION['APPLICATION_EDIT'] != '')
  			$APP_UID = $_SESSION['APPLICATION_EDIT'];  	
  	  else 
  	  	 	$_SESSION['PROCESS'] = $PRO_UID;
     
      $Fields = $oCase->loadCase($APP_UID);
      $userLoggedIni = '';
      if($ACTIONTYPE == 'edit')
      {
      	
      }
      $Fields = $oCase->loadCase($APP_UID);
      $Fields['APP_DATA']['__DYNAFORM_OPTIONS']['PREVIOUS_STEP_LABEL'] = '';
      $Fields['APP_DATA']['__DYNAFORM_OPTIONS']['NEXT_STEP_LABEL'] = ''; 
      $Fields['APP_DATA']['__DYNAFORM_OPTIONS']['NEXT_STEP'] = '#';
      $Fields['APP_DATA']['__DYNAFORM_OPTIONS']['NEXT_ACTION'] = 'return false;';      
      $Fields['APP_DATA']['external'] = 1;
      $Fields['APP_DATA']['USER_LOGGED'] = $_SESSION['USER_LOGGED'];
      		
      $Fields['APP_DATA']['RequestAux'] = isset($Fields['APP_DATA']['RequestNumber'])? $Fields['APP_DATA']['RequestNumber']:'';
      
      $_SESSION['DYN_UID_PRINT'] = $_POST['DYN_UID'];
      $postInfo = '';
      $swaction = $ACTIONTYPE;
	  $swCase = 0;
      if($ACTIONTYPE == 'edit')
      {
        $postInfo = 'saveDynaformLog.php?APP_UID='.$APP_UID.'&CURRENTDATETIME='.$CURRENTDATETIME.'&DYN_UID='.$_POST['DYN_UID'].'&PROCESS='.$PRO_UID;
        $url = '../convergenceList/'.$postInfo;
        $query = "SELECT APP_UID FROM PMT_USER_CONTROL_CASES WHERE APP_UID = '$APP_UID' AND USR_UID != '".$_SESSION['USER_LOGGED']."'  ";
	    $dataUsrCase = executeQuery($query);
        if(sizeof($dataUsrCase) > 0)
	    {
	        $totalUsers = sizeof($dataUsrCase);
	        $messageCases = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><div id='window-floating'>
                             <a class='close' href='javascript:void(0);' onclick='document.getElementById(&apos;window-floating&apos;).className = &apos;hiddenMessage&apos;'>x</a>
                             <div id='container'>
                             <div class='contentMessage'>
                                 Une autre personne est en train d&#39;&eacute;diter cet enregistrement. Voulez-vous quand m&#233;me l&#39;&eacute;diter ?
                            </div>
                            </div>
                            </div>";
            echo $messageCases;
	        $swCase = 1;
	    }
	
      }
      
      $_SESSION['APPLICATION'] = $APP_UID;
      
      $PRO_UID = $_SESSION['PROCESS'];
      
	  // load Dynaforms of process
		$select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM DYNAFORM WHERE PRO_UID = '".$PRO_UID ."'";
		$resultDynaform = executeQuery($select);
		$_dataForms =  array();
		foreach($resultDynaform As $rowDynaform)
		{
			$dynaform = new Form($PRO_UID . PATH_SEP . $rowDynaform['DYN_UID'], PATH_DYNAFORM , SYS_LANG , false);
			
			foreach ($dynaform->fields as $fieldName => $field) {
				// load fields type button , submit , reset or style display : none (mode view)
				if($field->type == "button" || $field->type == "submit" || $field->type =="reset" || strpos($field->style,"none") > 0)
				{
					$record = array (
							"FIELD_NAME" => $field->name, 
							"FIELD_LABEL" => $field->label,
							"FIELD_TYPE" => $field->type,
							"FIELD_MODE" => $field->mode,
							"FIELD_ENABLE_HTML" => $field->enableHtml,
							"FIELD_STYLE" => $field->style
					);
					$_dataForms[] = $record;
				}
			}
		}
		
    // end load dynaforms process 
      $G_PUBLISH->AddContent('dynaform', 'xmlform', $PRO_UID . '/' . $_POST['DYN_UID'], '', $Fields['APP_DATA'], $postInfo,'', $ACTIONTYPE);
      G::RenderPage('publish', 'blank');
      
?>      
    <link href="/plugin/convergenceList/modalMessageCase.css" rel="stylesheet" type="text/css" media="screen" /> 
    <style type="text/css">
#confirmBox {
	width:30em;
	height:10em;
	position:absolute;
	z-index:150;
	visibility:hidden;
	background:#FFFFFF;
	color:#000000;
	top: 40%;
    left: 40%;
	border:3px solid #000000;
	text-align:center;
 	padding: 15px;
}
</style>
<body>
<div id="confirmBox" style= "display:none; position:absolute;">
	<p>Continue?</p>
	<p><input type="button" onclick=" document.getElementById('confirmBox').style.visibility='hidden'; showModal();  document.forms[0].submit();  " value="Ok">
	<input type="button" onclick="document.getElementById('confirmBox').style.visibility='hidden'; parent.parent.Ext.getCmp('win2').hide();" value="Cancel"></p>
</div>
</body>
    <script type='text/javascript' src='/plugin/convergenceList/jsModal.js'></script>    
    <script language="javascript">
    var changeStatusSubmitFields = function(newStatusTo) {
    };
        var flag = false;
        var _dataForms = new Array();
       
        var swaction = <?php echo "'$swaction'"?>;
	       <?php
	              for($i = 0; $i<count($_dataForms); $i++){
	                     echo "_dataForms[$i] = '". $_dataForms[$i]['FIELD_NAME'] ."';";
	              }
	       ?>
	       
	    if(swaction === 'view'){
		       for(i=0; i< _dataForms.length; i++)
	  	           hideRowById(_dataForms[i]);
		}
	    
<?php
      global $G_FORM;
?>
			function confirmCreationNewForm()
			{
				if (!validateForm(document.getElementById("DynaformRequiredFields").value)) return false;
				var swcase = <?php echo $swCase ?>; 
				if(swcase == 1)
				{						
					tester("Une autre personne est en train d\u0027\u00E9diter cet enregistrement. Voulez-vous quand m\u00E9me l\u0027\u00E9diter ? ");
			    	//var answer = window.confirm("Une autre personne est en train d\u0027\u00E9diter cet enregistrement. Voulez-vous quand m\u00E9me l\u0027\u00E9diter ? ");
					return false;
				}
				else
				{ 					
            			showModal();
			    }
			}

      navigator.sayswho= (function(){
          var N= navigator.appName, ua= navigator.userAgent, tem;
          var M= ua.match(/(opera|chrome|safari|firefox|msie)\/?\s*(\.?\d+(\.\d+)*)/i);
          if(M && (tem= ua.match(/version\/([\.\d]+)/i))!= null) M[2]= tem[1];
          M= M? [M[1], M[2]]: [N, navigator.appVersion, '-?'];
          return M;
      })();
      function showModal(){
        if(navigator.sayswho[0] && navigator.sayswho[0]=='MSIE' && navigator.sayswho[1]  && parseInt(navigator.sayswho[1])<=9.0) {
           TINY.box.show({html:'<img src="/plugin/convergenceList/cargando5.gif">Sauvegarde...',fixed:false, width:150,height:21,animate:false,close:false,opacity:45,mask:true,boxid:'success'});  
        }
        else{
          TINY.box.show({html:'<img src="/plugin/convergenceList/cargando5.gif">Sauvegarde...', width:135,height:15,animate:false,close:false,opacity:45,mask:true,boxid:'success'});  
        }
      }

        document.getElementById("<?php echo $G_FORM->id;?>").onsubmit=confirmCreationNewForm;
        var answerFunction;

        function myConfirm(text,button1,button2)
        {
        	var box = document.getElementById("confirmBox");
        	box.getElementsByTagName("p")[0].firstChild.nodeValue = text;
        	var button = box.getElementsByTagName("input");
        	button[0].value=button1;
        	button[1].value=button2;
        	//answerFunction = answerFunc;
        	box.style.visibility="visible";
        	
        	
        }

        function answer(response) 
        {
        	document.getElementById('confirmBox').style.display = '';
        	var rpta = myConfirm(message,"Continue","Anuler") 
        	document.getElementById("confirmBox").style.visibility="hidden";
        	/*if (response){
    			showModal();
    			
    			location.href = "<?php echo $url;?>"; 
    			
  			}
			else{
				return false;
			}*/
        	return response;
        	
        }

        function tester(message)
        {
        	document.getElementById('confirmBox').style.display = '';
        	var rpta = myConfirm(message,"Continue","Anuler")   
        	
        	
        }
        
      </script>
      
<?php      
    
}
