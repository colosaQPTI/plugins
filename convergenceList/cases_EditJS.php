<?php



ini_set('error_reporting', E_ALL);

ini_set('display_errors', True);

G::LoadClass ( 'case' );

G::LoadClass ( 'configuration' );

G::loadClass ( 'pmFunctions' );

$oHeadPublisher = & headPublisher::getSingleton ();





## new list forms tabs

require_once ("classes/model/Dynaform.php");

global $G_PUBLISH;

$CURRENTDATETIME = date('Y-m-d H:i:s');



$ADAPTIVEHEIGHT = $_REQUEST['ADAPTIVEHEIGHT'];

if(isset($_SESSION['APPLICATION']) && $_SESSION['APPLICATION'] != '')

	$APP_UID = $_SESSION['APPLICATION'];

else

	$APP_UID = $_REQUEST['APP_UID'];



$FINDEX  ='';

$PRO_UID ='';

$TAS_UID ='';

$USR_UID ='';

$start = isset ( $_POST ['start'] ) ? $_POST ['start'] : 0;

$limit = isset ( $_POST ['limit'] ) ? $_POST ['limit'] : 20;



#Query To get the process, Actual user and task

$queryAppDelegation="SELECT MAX(AD1.DEL_INDEX) as FINDEX,PRO_UID, TAS_UID, USR_UID FROM APP_DELEGATION AD1 WHERE AD1.APP_UID='$APP_UID' ";

$resultAppDelegation=executeQuery($queryAppDelegation);

if(sizeof($resultAppDelegation)){

	$FINDEX  =$resultAppDelegation[1]['FINDEX'];

	$PRO_UID =$resultAppDelegation[1]['PRO_UID'];

	$TAS_UID =$resultAppDelegation[1]['TAS_UID'];

	$USR_UID =$resultAppDelegation[1]['USR_UID'];	 

}

#End Query To get the process, Actual user and task



#Rol user

$users=$_SESSION['USER_LOGGED'];

$Us = new Users();

$Roles=$Us->load($users);

$rolesAdmin=$Roles['USR_ROLE'];
$userName = $Roles['USR_USERNAME'];
#End rol user



 # execute Triggers task Ini

$userLoggedIni = '';

$oCase = new Cases();

$Fields = $oCase->loadCase($APP_UID);



if(!isset($_COOKIE['fe_typo_user']) && isset($Fields['APP_DATA']['FLAGTYPO3']) && $Fields['APP_DATA']['FLAGTYPO3'] == 'On' )

{     

	//$Fields = $oCase->loadCase($APP_UID);

	$Fields['APP_DATA']['FLAGTYPO3'] = 'Off'; 

	$Fields['APP_DATA']['SW_CREATE_CASE'] = 1;

    if(isset($Fields['APP_DATA']['USER_LOGGED']) && $Fields['APP_DATA']['USER_LOGGED'] != $_SESSION['USER_LOGGED'] )

    {

    	$userLoggedIni = $Fields['APP_DATA']['USER_LOGGED'];

        $Fields['APP_DATA']['USER_LOGGED'] = $_SESSION['USER_LOGGED'];
        
        $Fields['APP_DATA']['USR_USERNAME'] = $userName;

    }

    $oCase->updateCase($APP_UID, $Fields);

   

}

else if((isset($Fields['APP_DATA']['USER_LOGGED']) && $Fields['APP_DATA']['USER_LOGGED'] != $_SESSION['USER_LOGGED']) || (isset($Fields['APP_DATA']['FLAG_EDIT']) && $Fields['APP_DATA']['FLAG_EDIT'] == 1 ))

{

	$userLoggedIni = $Fields['APP_DATA']['USER_LOGGED'];

	$Fields['APP_DATA']['USER_LOGGED'] = $_SESSION['USER_LOGGED'];
	
	$Fields['APP_DATA']['USR_USERNAME'] = $userName;

	$Fields['APP_DATA']['SW_CREATE_CASE'] = 1;

    $oCase->updateCase($APP_UID, $Fields);

    

}


# execute Triggers task Ini

	$query = "SELECT TAS_UID FROM TASK WHERE TAS_START = 'TRUE' AND PRO_UID = '".$PRO_UID."'";	//query for select all start tasks

	$startTasks = executeQuery($query);

	foreach($startTasks as $rowTask){

		$taskId = $rowTask['TAS_UID'];

		$stepsByTask = getStepsByTask($taskId);

	    foreach ($stepsByTask as $caseStep){

			$caseStepRes[] = $caseStep->getStepUidObj();

		}

		break;

	}

	        

	$totStep = 0;

	foreach($caseStepRes as $index)

	{

		$stepUid = $index;

		executeTriggersMon($PRO_UID, $APP_UID, $stepUid, 'BEFORE', $taskId);	//execute trigger before form

		executeTriggersMon($PRO_UID, $APP_UID, $stepUid, 'AFTER', $taskId);	//execute trigger after form	

		$totStep++;

	} 

    # end execute Triggers task Ini 



# Get Dynaforms



if($rolesAdmin == 'PROCESSMAKER_ADMIN')

{

	$query = " SELECT DISTINCT STEP_UID_OBJ AS DYN_UID, STEP_CONDITION, MIN( STEP_POSITION ) AS POSITION  FROM STEP 

  				WHERE PRO_UID = '$PRO_UID' AND STEP_TYPE_OBJ ='DYNAFORM'

  				GROUP BY DYN_UID   

   				ORDER BY POSITION, STEP_MODE ASC";

	$select = executeQuery($query);

}

else

{	

	$selectUser = "SELECT GRP_UID  FROM GROUP_USER WHERE USR_UID = '$users' ";

	$selectUser = executeQuery($selectUser);	

	$userGroup = $selectUser[1]['GRP_UID'];

	$query = " SELECT DISTINCT STEP_UID_OBJ AS DYN_UID, STEP_CONDITION FROM STEP ST

  				INNER JOIN TASK_USER TU ON (TU.TAS_UID = ST.TAS_UID) 

  				WHERE ST.PRO_UID = '$PRO_UID' AND ST.STEP_TYPE_OBJ ='DYNAFORM'

   				AND (TU.USR_UID = '$users' OR TU.USR_UID = '$userGroup')

   				GROUP BY DYN_UID   

   				ORDER BY ST.STEP_POSITION, ST.STEP_MODE ASC";

	$select = executeQuery($query);



}

 

$newSelect = array();

G::LoadClass('pmScript');

$sAppUid = $APP_UID;

$oPMScript = new PMScript();

$oApplication = new Application();

$oApplication = ApplicationPeer::retrieveByPk($sAppUid);

$aFields = $oApplication->toArray(BasePeer::TYPE_FIELDNAME);

if (!is_array($aFields['APP_DATA'])) 

{

	$aFields['APP_DATA'] = G::array_merges(G::getSystemConstants(), unserialize($aFields['APP_DATA']));

}

foreach ($select as $row) 

{

	if (trim($row['STEP_CONDITION']) != '') {

      	$oPMScript->setFields($aFields['APP_DATA']);   

       	$oPMScript->setScript($row['STEP_CONDITION']); 

       	$bAccessStep = $oPMScript->evaluate();

    } else {

       	$bAccessStep = true;

    }

    if($bAccessStep)

    	$newSelect[]= $row;

}

unset($aFields);

$select = $newSelect;

     

$o = new Dynaform();

$DYNAFORMSLIST = array();

       

foreach($select as $index)

{

	$get = $index['DYN_UID'];

	$process = $PRO_UID;

	$oForm = new Form ( $process.'/'.$get , PATH_DYNAFORM );

	$i=0;

	foreach($oForm->fields as $key => $val){ //G::pr($val->type);

		if($val->type == "dropdown" or $val->type == "radiogroup"  or $val->type == "checkgroup" or $val->type == "listbox" or $val->type == "text"  or $val->type == "date" or $val->type == "suggest" or $val->type == "yesno" or $val->type == "checkbox" ){

			$i++;

		}			

	}

	if($i != 0)

	{

    	$o->setDynUid($index['DYN_UID']);

        $aFields['DYN_TITLE'] = $o->getDynTitle();

        $aFields['DYN_UID'] = $index['DYN_UID'];

        $aFields['EDIT'] = G::LoadTranslation('ID_EDIT');

        $aFields['PRO_UID'] = $PRO_UID;

        $aFields['APP_UID'] = $APP_UID;

        $aFields['TAS_UID'] = $TAS_UID;

        $aFields['CURRENTDATETIME'] = $CURRENTDATETIME;

        $selectUser = "SELECT GRP_UID  FROM GROUP_USER WHERE USR_UID = '$users' ";

		$selectUser = executeQuery($selectUser);	

		$userGroup = $selectUser[1]['GRP_UID'];

		$queryStepMode = " SELECT STEP_UID_OBJ AS DYN_UID, STEP_MODE  FROM STEP S

						   INNER JOIN  TASK_USER TU ON TU.TAS_UID = S.TAS_UID

  						   WHERE S.PRO_UID = '$PRO_UID' AND STEP_UID_OBJ ='".$index['DYN_UID']."' AND

  						   (TU.USR_UID = '$users' OR TU.USR_UID = '$userGroup')

   						 ";

		$selectStepMode = executeQuery($queryStepMode);

		if(sizeof($selectStepMode))

		{

			$aFields['TYPEFORM'] = 'view';

			foreach($selectStepMode as $index)

			{

				if($index['STEP_MODE'] == 'EDIT')

				{

					$aFields['TYPEFORM'] = 'edit';

				}

			}

				

		}

			

        $DYNAFORMSLIST[] = $aFields;            

	}

          

} 

## end new list forms tabs

$oHeadPublisher->assign('APP_UID', $APP_UID);

$oHeadPublisher->assign('ADAPTIVEHEIGHT', $ADAPTIVEHEIGHT);

$oHeadPublisher->assign('DYNAFORMSLIST', $DYNAFORMSLIST);

$oHeadPublisher->addExtJsScript ( PATH_PLUGINS . SYS_COLLECTION . '/cases_Edit', true, true );



G::RenderPage ( 'publish', 'extJs' );



?> 



