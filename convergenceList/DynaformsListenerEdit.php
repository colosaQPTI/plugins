<?php
G::LoadClass('pmFunctions');
G::LoadClass('case');
G::loadClass('configuration');
require_once ("classes/model/Dynaform.php");
global $G_PUBLISH;

$_SESSION['APPLICATION_EDIT'] = '';
$_SESSION['PROCESS'] = '';
$_SESSION['APPLICATION'] = ''; 

$ACTIONTYPE = $_GET['actionType'];
$CURRENTDATETIME=date('Y-m-d H:i:s');
$ADAPTIVEHEIGHT = $_GET['adaptiveHeight'];
$APP_UID = $_GET['appUid'];
$SHOWCOMMENT = $_GET['accessComment'];
$FINDEX  ='';
$PRO_UID ='';
$TAS_UID ='';
$USR_UID ='';
$userActiveForm = 0;
#Query To get the process, Actual user and task
$queryAppDelegation="SELECT AD1.DEL_INDEX as FINDEX,PRO_UID, TAS_UID, USR_UID FROM APP_DELEGATION AD1 WHERE AD1.APP_UID='$APP_UID'  
							AND AD1.DEL_INDEX IN (SELECT MAX(AD.DEL_INDEX) FROM APP_DELEGATION AD WHERE AD1.APP_UID=AD.APP_UID)  ";
$resultAppDelegation=executeQuery($queryAppDelegation); 
if(sizeof($resultAppDelegation)){
	$FINDEX  =$resultAppDelegation[1]['FINDEX'];
	$PRO_UID =$resultAppDelegation[1]['PRO_UID'];
	$TAS_UID =$resultAppDelegation[1]['TAS_UID'];
	$USR_UID =$resultAppDelegation[1]['USR_UID'];	
}
#End Query To get the process, Actual user and task

# Get Dynaforms
$query = " SELECT DISTINCT STEP_UID_OBJ AS DYN_UID FROM STEP 
      	   INNER JOIN APP_DELEGATION AD ON AD.TAS_UID = STEP.TAS_UID
      	   INNER JOIN APPLICATION A ON A.APP_UID = AD.APP_UID
           WHERE A.APP_UID = '".$APP_UID."' AND (A.APP_STATUS = 'TO_DO' OR A.APP_STATUS = 'DRAFT')
           AND AD.DEL_INDEX IN (SELECT MAX(AD1.DEL_INDEX) FROM APP_DELEGATION AD1 WHERE AD1.APP_UID=AD.APP_UID)
         ";
$select = executeQuery($query);       
   
$o = new Dynaform();
$DYNAFORMSLIST = array();
if(sizeof($select))
{       
    foreach($select as $index){
        $o->setDynUid($index['DYN_UID']);
        $aFields['DYN_TITLE'] = $o->getDynTitle();
        $aFields['DYN_UID'] = $index['DYN_UID'];
        $aFields['EDIT'] = G::LoadTranslation('ID_EDIT');
        $aFields['PRO_UID'] = $PRO_UID;
        $aFields['APP_UID'] = $APP_UID;
        $aFields['TAS_UID'] = $TAS_UID;
        $DYNAFORMSLIST[] = $aFields;            
    } 
         
    $queryAppDelegation="SELECT AD1.DEL_INDEX as FINDEX, USR_UID FROM APP_DELEGATION AD1 WHERE AD1.APP_UID='$APP_UID'  
							AND AD1.DEL_INDEX IN (SELECT MIN(AD.DEL_INDEX) FROM APP_DELEGATION AD WHERE AD1.APP_UID=AD.APP_UID)  ";
    $resultAppDelegation=executeQuery($queryAppDelegation);          
    $usrUidIniCase = isset($resultAppDelegation[1]['USR_UID'])? $resultAppDelegation[1]['USR_UID']:''; 
    require_once 'classes/model/Event.php';
    require_once 'classes/model/AppDelay.php';
    require_once 'classes/class.wsResponse.php';
    $caseInstance = new Cases ();
    $eventInstance = new Event();
    if($usrUidIniCase == $_SESSION['USER_LOGGED'])
    {
        $_SESSION['APPLICATION'] = $APP_UID;
	    $_SESSION['INDEX'] = $FINDEX;
	    if ($caseInstance->isSelfService( $_SESSION['USER_LOGGED'], $TAS_UID, $APP_UID )) 
	    {
            $queryUpdateDelegation="UPDATE APP_DELEGATION SET USR_UID='".$_SESSION['USER_LOGGED']."' WHERE APP_UID='".$APP_UID."' AND DEL_INDEX='".$FINDEX."' ";
		    $resultDelegation = executeQuery($queryUpdateDelegation);
	    }
	    else
            $userActiveForm = 1;
    }
    else
    {
        
        $_SESSION['APPLICATION'] = $APP_UID;
	    $_SESSION['INDEX'] = $FINDEX;
	    if ($caseInstance->isSelfService( $_SESSION['USER_LOGGED'], $TAS_UID, $APP_UID )) 
	    {
            $queryUpdateDelegation="UPDATE APP_DELEGATION SET USR_UID='".$_SESSION['USER_LOGGED']."' WHERE APP_UID='".$APP_UID."' AND DEL_INDEX='".$FINDEX."' ";
		    $resultDelegation = executeQuery($queryUpdateDelegation);
	    }
    }
}
# End Get Dynaforms

#Update the flag typo3
$oCase = new Cases ();
$olfFields = $oCase->loadCase($APP_UID);
unset($olfFields['APP_DATA']['FLAG_ACTION']);		
unset($olfFields['APP_DATA']['FLAG_ACTIONTYPO3']);
PMFSendVariables($APP_UID, $olfFields);
$oCase->updateCase($APP_UID, $olfFields);
# End Update the flag typo3      

# control user case

$date_start = Date("m-d-Y H:i:s");
$delete = executeQuery("DELETE FROM PMT_USER_CONTROL_CASES WHERE APP_UID = '$APP_UID' AND USR_UID = '".$_SESSION['USER_LOGGED']."' ");
$queryId = "SELECT max(USR_CTR_CAS_ID) AS MAX_ID FROM  PMT_USER_CONTROL_CASES  "; 
$maxId = executeQuery ( $queryId );
$sgtIdIn = $maxId[1]['MAX_ID'] + 1;
$insert = "INSERT INTO PMT_USER_CONTROL_CASES (
		   USR_CTR_CAS_ID, APP_UID, USR_UID, USR_CTR_CAS_START_DATE, USR_CTR_CAS_END_DATE )
		   VALUES(
		   '$sgtIdIn', '$APP_UID', '".$_SESSION['USER_LOGGED']."','$date_start' ,'' ) ";
executeQuery($insert);
# end control user case
$dataLabels = Array();
$oHeadPublisher =& headPublisher::getSingleton();     
$conf = new Configurations;
# control labels language
$language = strtoupper(SYS_LANG);
$queryLabels = "SELECT NAME_LABEL, DESCRIPTION_$language DESCRIPTION FROM PMT_CUSTOMIZE_LABEL ";
$dataLabelsQuery = executeQuery($queryLabels);
if(sizeof($dataLabelsQuery))
{
	foreach($dataLabelsQuery as $row)
	{
		$dataLabels[] = $row;
	}
}
# end control labels language
$oHeadPublisher->assign('ACTIONTYPE', $ACTIONTYPE);
$oHeadPublisher->assign('APP_UID', $APP_UID);
$oHeadPublisher->assign('FINDEX', $FINDEX);
$oHeadPublisher->assign('PRO_UID', $PRO_UID);
$oHeadPublisher->assign('TAS_UID', $TAS_UID);
$oHeadPublisher->assign('USR_UID', $USR_UID);
$oHeadPublisher->assign('CURRENTDATETIME', $CURRENTDATETIME);
$oHeadPublisher->assign('DYNAFORMSLIST', $DYNAFORMSLIST);
$oHeadPublisher->assign('ADAPTIVEHEIGHT', $ADAPTIVEHEIGHT);
$oHeadPublisher->assign('SHOWCOMMENT', $SHOWCOMMENT);
$oHeadPublisher->assign('SWTAB', '');
$oHeadPublisher->assign('ACTIVEFORMS', $userActiveForm);
$oHeadPublisher->assign('DATALABELS', $dataLabels);
$oHeadPublisher->addExtJsScript('convergenceList/caseHistoryDynaformPageEdit', true );    //adding a javascript file .js
$oHeadPublisher->addContent    ('convergenceList/caseHistoryDynaformPage'); //adding a html file  .html.      
$oHeadPublisher->assign('pageSize', $conf->getEnvSetting('casesListRowNumber'));    
G::RenderPage('publish', 'extJs');  
