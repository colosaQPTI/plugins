<?php
/**
 * class.convExamples.pmFunctions.php
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.
 * *
 */

////////////////////////////////////////////////////
// convExamples PM Functions
//
// Copyright (C) 2007 COLOSA
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

function convExamples_getMyCurrentDate()
{
	return G::CurDate('Y-m-d');
}

function convExamples_getMyCurrentTime()
{
	return G::CurDate('H:i:s');
}

function DuplicateMySQLRecord ($table, $id_field, $oldid,$newId) {
  // load the original record into an array
  $original_q = "SELECT * FROM {$table} WHERE {$id_field}='$oldid' ";
  $original_record = executeQuery($original_q);  

  foreach ($original_record as $key => $value) {  
    $HData = str_replace("'","\'",$value ['HISTORY_DATA']);    
    $query = "INSERT INTO {$table} (APP_UID,DEL_INDEX,PRO_UID,TAS_UID,DYN_UID,USR_UID,APP_STATUS,HISTORY_DATE,HISTORY_DATA) 
    VALUES (
      '".$newId."',
      '".$value['DEL_INDEX']."',
      '".$value['PRO_UID']."',
      '".$value['TAS_UID']."',
      '".$value['DYN_UID']."',
      '".$value['USR_UID']."',
      '".$value['APP_STATUS']."',
      '".$value['HISTORY_DATE']."',
      '".serialize($HData)."'
    )";
    executeQuery($query);    
  }    
}

function autoDerivate($processId,$caseUID,$userId,$controlCron=true){

	$query = "SELECT TAS_UID FROM TASK WHERE TAS_START = 'TRUE' AND PRO_UID = '".$processId."'";	//query for select all start tasks
	$startTasks = executeQuery($query);
	$taskId = $startTasks[1]['TAS_UID'];
	$queryNextTask = "SELECT ROU_NEXT_TASK FROM ROUTE WHERE PRO_UID = '".$processId."' AND TAS_UID = '".$taskId."'";
	$taskNumber = 1;
	$NextTask = executeQuery($queryNextTask);
	if($NextTask[1]['ROU_NEXT_TASK'] == '-1')
		$taskNumber = 0;
	$userLoggedIni = $_SESSION['USER_LOGGED'];
	if(isset($_SESSION['USER_LOGGED_INI']) && $_SESSION['USER_LOGGED_INI'] != '')
		$userLoggedIni = $_SESSION['USER_LOGGED_INI'];
        
	foreach ($startTasks as $rowTask)
	{
		$flagAction = updateDateAPPDATA($caseUID);
		$taskId = $rowTask['TAS_UID'];
		$currentTask = $taskId;
		$process = $processId;
		$appUid = $caseUID;    
		$task = $taskId;	
		frderivateCase($processId, $currentTask , $caseUID,$userId,$taskNumber, $userLoggedIni, $flagAction);		//Function for derivate case
	}
	if($userLoggedIni !='')
		$_SESSION['USER_LOGGED'] = $userLoggedIni ; 
	if($controlCron == true)
    	FredirectTypo3($caseUID);
        
}

function frderivateCase($processId, $currentTask , $fcaseUID,$userId,$taskNumber, $userLoggedIni, $flagAction)
{
	try 
	{
		$sw = 0;
		while($sw == 0)
		{
	    	$_SESSION['APPLICATION'] = $fcaseUID;
			$queryDelIndex = "SELECT MAX(DEL_INDEX) AS DEL_INDEX FROM APP_DELEGATION WHERE APP_UID = '".$fcaseUID."'";
			$DelIndex = executeQuery($queryDelIndex);
			$swFLag = 0;
			if($flagAction == 'actionCreateCase' || $flagAction == 'editForms' || $flagAction == 'actionAjaxRestartCases' || $flagAction == 'actionAjax')
			{
				$queryTask = "SELECT TAS_UID FROM APP_DELEGATION WHERE APP_UID = '".$fcaseUID."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
				$dataTask = executeQuery($queryTask);
				if(sizeof($dataTask))
					$currentTask = $dataTask[1]['TAS_UID'];
				$swFLag = 1;
			}
				
			$queryNextTask = "SELECT ROU_CONDITION, ROU_NEXT_TASK FROM ROUTE WHERE PRO_UID = '".$processId."' AND TAS_UID = '".$currentTask."'";
			$NextTask = executeQuery($queryNextTask);
			if($swFLag == 1 )
			{
				foreach($NextTask as $row)
				{
					if($row['ROU_CONDITION'] != '')
					{							
						$oCase = new Cases ();
						$AppFields = $oCase->loadCase($fcaseUID);
                		$oPMScript = new PMScript();
                		$oPMScript->setFields( $AppFields['APP_DATA'] );
                		$oPMScript->setScript( $row['ROU_CONDITION'] );
                		$bContinue = $oPMScript->evaluate();
						//G::pr('response '.$row['ROU_CONDITION'].'  '.$bContinue);
       					if($bContinue == 1)
    						$currentTask = $row['ROU_NEXT_TASK'];
					}
					else 
						$currentTask = $row['ROU_NEXT_TASK'];		 
    				
				}
				
			}
			
			if($currentTask != '-1')
			{
				$stepsByTask = getStepsByTask($currentTask);//FORM IDS in THE TASK			
				$caseStepRes = array();
			
				foreach ($stepsByTask as $caseStep){
					$caseStepRes[] = $caseStep->getStepUidObj();
				}
				//G::pr($caseStepRes);
				$totStep = 0;
			
				foreach($caseStepRes as $index)
				{
					$stepUid = $index;
					executeTriggersMon($processId, $fcaseUID, $stepUid, 'BEFORE', $currentTask);	//execute trigger before form
					executeTriggersMon($processId, $fcaseUID, $stepUid, 'AFTER', $currentTask);	//execute trigger after form	
					$totStep++;
				}
				if($totStep != 0)
					executeTriggersMon( $processId, $fcaseUID, -1, 'BEFORE', $currentTask );
			}
			G::LoadClass( 'wsBase' );
	    	$ws = new wsBase();
			if($NextTask[1]['ROU_NEXT_TASK'] == '-1')
			{
				$stepUid = -1;							
				$beforeA = true;
				if($taskNumber == 0){
					$beforeA = false;
				}
				else 
				{
					executeTriggersMon($processId, $fcaseUID, $stepUid, 'BEFORE', $currentTask);	//execute trigger before form
					executeTriggersMon($processId, $fcaseUID, $stepUid, 'AFTER', $currentTask);	//execute trigger after form	
				}							
				G::LoadClass( 'derivation' );
				$oDerivation = new Derivation();
				$aFields['TASK']= $oDerivation->prepareInformation( array ('USER_UID' => $userId,'APP_UID' => $fcaseUID,'DEL_INDEX' => $DelIndex[1]['DEL_INDEX']) );
				if (empty( $aFields['TASK'] )) {  
				    $_SESSION['USER_LOGGED'] = $userLoggedIni ;           
					throw (new Exception( G::LoadTranslation( 'ID_NO_DERIVATION_RULE' ) ));
				}	
				else
				{
			        if(isset($DelIndex[1]['DEL_INDEX']) && $DelIndex[1]['DEL_INDEX'] != ''){
	                	$queryDel = "SELECT * FROM APP_DELEGATION WHERE APP_UID = '".$fcaseUID."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
	                	$resDel = executeQuery($queryDel);
	                	if(sizeof($resDel)){
	                		if($resDel[1]['USR_UID'] == ""){
	                    		$queryuPDel = "UPDATE APP_DELEGATION SET USR_UID = '".$userLoggedIni."' 
	                    		WHERE APP_UID = '".$fcaseUID."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
	                    		$queryuPDel = executeQuery($queryuPDel);
	                    		$userId = $userLoggedIni;
	                  		}
	                		elseif(isset($_SESSION['USER_LOGGED_INI']) && $_SESSION['USER_LOGGED_INI'] != '' && $DelIndex[1]['DEL_INDEX']!= 1)
	                		{	                			
	                    		$userId = $resDel[1]['USR_UID'];
	                		}
	                	}
			        }
			
				}
				try{
				    $result = $ws->derivateCase( $userId, $fcaseUID, $DelIndex[1]['DEL_INDEX'], false );  
				}
				catch (Exception $e) 
				{
					$status_code = $result['status_code'];					
					G::pr($result);
					G::pr($e);
					die("error");
				}			
				$sw = 1;
			}
			else
			{
				if($totStep == 0)
					executeTriggersMon( $processId, $fcaseUID, -1, 'BEFORE', $currentTask );
				G::LoadClass( 'derivation' );
				$oDerivation = new Derivation();
				$aFields['TASK']= $oDerivation->prepareInformation( array ('USER_UID' => $userId,'APP_UID' => $fcaseUID,'DEL_INDEX' => $DelIndex[1]['DEL_INDEX']) );
				if (empty( $aFields['TASK'] )) {     
				    $_SESSION['USER_LOGGED'] = $userLoggedIni ;             
					throw (new Exception( G::LoadTranslation( 'ID_NO_DERIVATION_RULE' ) ));
				}	
				else
				{
			        if(isset($DelIndex[1]['DEL_INDEX']) && $DelIndex[1]['DEL_INDEX'] != ''){
	                	$queryDel = "SELECT * FROM APP_DELEGATION WHERE APP_UID = '".$fcaseUID."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
	                	$resDel = executeQuery($queryDel);
	                	if(sizeof($resDel)){
	                		if($resDel[1]['USR_UID'] == ""){
	                    		$queryuPDel = "UPDATE APP_DELEGATION SET USR_UID = '".$userLoggedIni."' 
	                    		WHERE APP_UID = '".$fcaseUID."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
	                    		$queryuPDel = executeQuery($queryuPDel);
	                    		$userId = $userLoggedIni;
	                  		}
	                		elseif(isset($_SESSION['USER_LOGGED_INI']) && $_SESSION['USER_LOGGED_INI'] != '' && $DelIndex[1]['DEL_INDEX']!= 1)
	                		{
	                			$userId = $resDel[1]['USR_UID'];
	                		}
	                	}
			        }
			
				}
				try{ 
				    $result = $ws->derivateCase( $userId, $fcaseUID, $DelIndex[1]['DEL_INDEX'], false );
				}
				catch (Exception $e) 
				{
					$status_code = $result['status_code'];					
					G::pr($result); 
					G::pr($e);
					die("error");
				}				
				$queryDelIndex = "SELECT MAX(DEL_INDEX) AS DEL_INDEX FROM APP_DELEGATION WHERE APP_UID = '".$fcaseUID."'";
				$DelIndex = executeQuery($queryDelIndex); 
				$queryDel = "SELECT TAS_UID FROM APP_DELEGATION WHERE APP_UID = '".$fcaseUID."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
	        	$resDel = executeQuery($queryDel);
				$currentTask = $resDel[1]['TAS_UID']; 
			}
				
		}
	} 
	catch (Exception $e) 
	{
		$err = $e->getMessage();
		$err = preg_replace("[\n|\r|\n\r]", ' ', $err);
		die($err);
	}			

	
}

function frexecuteTriggers($processId,$caseUID,$userId){

	$query = "SELECT TAS_UID FROM TASK WHERE TAS_START = 'TRUE' AND PRO_UID = '".$processId."'";	//query for select all start tasks
	$startTasks = executeQuery($query);	
	$taskId = $startTasks[1]['TAS_UID'];
	$queryNextTask = "SELECT ROU_NEXT_TASK FROM ROUTE WHERE PRO_UID = '".$processId."' AND TAS_UID = '".$taskId."'";
	$taskNumber = 1;
	$NextTask = executeQuery($queryNextTask);
	if($NextTask[1]['ROU_NEXT_TASK'] == '-1')
		$taskNumber = 0;
	$userLoggedIni = $_SESSION['USER_LOGGED'];
	if(isset($_SESSION['USER_LOGGED_INI']) && $_SESSION['USER_LOGGED_INI'] != '')
		$userLoggedIni = $_SESSION['USER_LOGGED_INI'];
     
	foreach($startTasks as $rowTask){
		$flagAction = updateDateAPPDATA($caseUID);
		$taskId = $rowTask['TAS_UID'];
		$currentTask = $taskId;
		$process = $processId;
		$appUid = $caseUID;    
		$task = $taskId;	
		frExecuteTriggersCase($processId, $currentTask , $caseUID,$userId,$taskNumber, $userLoggedIni);		//Function for derivate case
	}
	if($userLoggedIni !='')
		$_SESSION['USER_LOGGED'] = $userLoggedIni ; 
    
}

function frExecuteTriggersCase($processId, $currentTask , $fcaseUID,$userId,$taskNumber, $userLoggedIni)
{
	try 
	{
		G::LoadClass( 'derivation' );
		G::LoadClass('pmScript');
		G::LoadClass( 'case' );
		$oPMScript = new PMScript();
		$sw = 0;
		$indexCase = $_SESSION["INDEX"] = 1;
		while($sw == 0)
		{
	    	$_SESSION['APPLICATION'] = $fcaseUID;
			$queryDelIndex = "SELECT MAX(DEL_INDEX) AS DEL_INDEX FROM APP_DELEGATION WHERE APP_UID = '".$fcaseUID."'";
			$DelIndex = executeQuery($queryDelIndex);      
			$queryNextTask = "SELECT ROU_NEXT_TASK FROM ROUTE WHERE PRO_UID = '".$processId."' AND TAS_UID = '".$currentTask."'";
			$NextTask = executeQuery($queryNextTask);			
			$stepsByTask = getStepsByTask($currentTask);//FORM IDS in THE TASK			
			$caseStepRes = array();
			if(isset($DelIndex[1]['DEL_INDEX']) && $DelIndex[1]['DEL_INDEX'] != ''){
	            $queryDel = "SELECT * FROM APP_DELEGATION WHERE APP_UID = '".$fcaseUID."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
	            $resDel = executeQuery($queryDel);
	            if(sizeof($resDel)){
	            	if($resDel[1]['USR_UID'] == ""){
	            		$queryuPDel = "UPDATE APP_DELEGATION SET USR_UID = '".$userLoggedIni."' 
	            		WHERE APP_UID = '".$fcaseUID."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
	            		$queryuPDel = executeQuery($queryuPDel);
	            		$userId = $userLoggedIni;
	            	}
	            	elseif(isset($_SESSION['USER_LOGGED_INI']) && $_SESSION['USER_LOGGED_INI'] != '' && $DelIndex[1]['DEL_INDEX']!= 1)
	            	{	                			
	            		$userId = $resDel[1]['USR_UID'];
	            	}
	            }
			}
			foreach ($stepsByTask as $caseStep){
				$caseStepRes[] = 	 $caseStep->getStepUidObj();
			}
			
			$totStep = 0;
			foreach($caseStepRes as $index)
			{
				$stepUid = $index;
				executeTriggersMon($processId, $fcaseUID, $stepUid, 'BEFORE', $currentTask);	//execute trigger before form
				executeTriggersMon($processId, $fcaseUID, $stepUid, 'AFTER', $currentTask);	//execute trigger after form	
				$totStep++;
			} 
			
			if($NextTask[1]['ROU_NEXT_TASK'] == '-1')
			{
				$stepUid = -1;					
				$beforeA = true;
				if($taskNumber == 0){
					$beforeA = false;
				}
				else 
				{
					executeTriggersMon($processId, $fcaseUID, $stepUid, 'BEFORE', $currentTask);	//execute trigger before assignment
					executeTriggersMon($processId, $fcaseUID, -2, 'BEFORE', $currentTask); //execute trigger before derivation
					executeTriggersMon($processId, $fcaseUID, -2, 'AFTER', $currentTask);	//execute trigger after derivation	
				}				
				$sw = 1;
			}
			else
			{				
				executeTriggersMon($processId, $fcaseUID, -1, 'BEFORE', $currentTask ); //execute trigger before assignment
				executeTriggersMon($processId, $fcaseUID, -2, 'BEFORE', $currentTask); //execute trigger before derivation
				executeTriggersMon($processId, $fcaseUID, -2, 'AFTER', $currentTask);	//execute trigger after derivation	
				
				$queryNextTask = "SELECT ROU_NEXT_TASK, ROU_TYPE, ROU_CONDITION FROM ROUTE WHERE PRO_UID = '".$processId."' AND TAS_UID = '".$currentTask."'";
				$NextTask = executeQuery($queryNextTask);
				foreach($NextTask as $row)
				{
					if($row['ROU_CONDITION'] != '')
					{							
						$oCase = new Cases ();
						$AppFields = $oCase->loadCase($fcaseUID);
                		$oPMScript = new PMScript();
                		$oPMScript->setFields( $AppFields['APP_DATA'] );
                		$oPMScript->setScript( $row['ROU_CONDITION'] );
                		$bContinue = $oPMScript->evaluate();
						//G::pr('response '.$row['ROU_CONDITION'].'  '.$bContinue);
       					if($bContinue == 1)
    						$currentTask = $row['ROU_NEXT_TASK'];
					}
					else 
						$currentTask = $row['ROU_NEXT_TASK'];		 
    				
				}
				$_SESSION["INDEX"] = $indexCase + 1;
				$indexCase = $_SESSION["INDEX"];
				## update app_delegation
				$finishDate = date("Y-m-d H:i:s");
				$queryuPDel = "	UPDATE APP_DELEGATION SET 
								USR_UID = '".$userLoggedIni."',
								TAS_UID = '".$currentTask."',
								DEL_INDEX = '".$indexCase."',
								DEL_FINISH_DATE = '".$finishDate."'
	            				WHERE APP_UID = '".$fcaseUID."' AND DEL_INDEX = '".$indexCase."' ";
	          	$queryuPDel = executeQuery($queryuPDel);
	          	## end update app_delegation
			}
				
		}
				
	}
	
	catch (Exception $e) 
	{
		$err = $e->getMessage();
		$err = preg_replace("[\n|\r|\n\r]", ' ', $err);
		die($err);
	}			

	
}

function updateDateAPPDATA($application){
 
 $caseInstance = new Cases();
 $caseFields = $caseInstance->loadCase( $application ); 
 if(isset($caseFields['APP_DATA']['FLAG_ACTION']))
 	$flagAction = isset($caseFields['APP_DATA']['FLAG_ACTION']) ? $caseFields['APP_DATA']['FLAG_ACTION']:'';
 else
 	$flagAction = isset($caseFields['APP_DATA']['FLAG_ACTIONTYPO3']) ? $caseFields['APP_DATA']['FLAG_ACTIONTYPO3']:'';
 	
 $caseInstance->updateCase($application, $caseFields);
 return $flagAction;
}


function getStepsByTask($task){
		require_once 'classes/model/Step.php';
	  $c = new Criteria();
    $c->addSelectColumn('*');    	
		    $c->setDistinct();
		    $c->add(StepPeer::TAS_UID, $task);
		    $c->addAscendingOrderByColumn (StepPeer::STEP_POSITION);		    
		    $caseSteps =  StepPeer::doSelect($c);  
				return $caseSteps;
		}
		
	
function executeTriggersMon($process, $appUid, $stepUid, $time='BEFORE', $task){
  
    $type = getStepType($stepUid);
    //$type = '';
    $oCase = new Cases();
    $Fields = $oCase->loadCase($appUid);  
     if($stepUid == -1 || $stepUid == -2){
  	    $obj = 'ASSIGN_TASK';
    }else{
  	    $obj = 'DYNAFORM';  	
    }
  
    $triggers = $oCase->loadTriggers ( $task, $obj, $stepUid, $time );
    if($stepUid == -1 || $stepUid == -2)
    {
   		$type = 'ASSIGN_TASK';
    }
    // G::pr($task.' task '. $type.' type '. $stepUid.' step '. $time.' time ');
    $Fields['APP_DATA'] = $oCase->ExecuteTriggers($task, $type , $stepUid, $time, $Fields['APP_DATA'] );  
    $oCase->updateCase($appUid, $Fields);
    return true;
}

function getStepType($step){
		$task = executeQuery("SELECT * FROM STEP WHERE STEP_UID_OBJ = '".$step."'");
	  return $task[1]['STEP_TYPE_OBJ'];
}

###### For Filters
function getRolUser(){
	require_once ("classes/model/Users.php");
    $oUser = new Users();
    $oDetailsUser = $oUser->load ($_SESSION ['USER_LOGGED']);
    return $oDetailsUser['USR_ROLE'];
}
function getQueryForSimpleSearch($idInbox='',$fieldName='', $fieldValue,$all=true){
    $sTableName = '';
    $sWhere ='';
    $sRolCode   = getRolUser();
    $sSQL="SELECT DISTINCT ALIAS_TABLE FROM PMT_INBOX_FIELDS WHERE ID_INBOX='$idInbox' AND ROL_CODE ='$sRolCode' AND FIELD_NAME ='$fieldName'";
    $aData = executeQuery ($sSQL);
    if(is_array($aData) && count($aData) >0){
        if(isset($aData[1]['ALIAS_TABLE'])) $sTableName = $aData[1]['ALIAS_TABLE'];
    }
    
    $fieldSelect = "SELECT QUERY_SELECT FROM PMT_INBOX_FIELDS_SELECT WHERE ID_INBOX = '".$idInbox ."' AND ROL_CODE ='".$sRolCode."' AND FIELD_NAME = '".$fieldName."'  ";
	$datafieldSelect = executeQuery($fieldSelect);
		
    if($sTableName != '' && sizeof($datafieldSelect) == '') {
    	$sWhere=" AND $sTableName.$fieldName LIKE '";
    	if($all) $sWhere.="%";
    	$sWhere .= mysql_real_escape_string($fieldValue)."%' ";
    }
    else 
    {
    	 ##### Options select query
		$contSelect = 1;
		
		$fieldSelectQuery = '';
		$totSelectQuery = sizeof($datafieldSelect);
		if(sizeof($datafieldSelect))
		{
			foreach($datafieldSelect as $index)
			{
				if($contSelect == 1)
				$fieldSelectQuery = $index['QUERY_SELECT'];
				else
					$fieldSelectQuery = $fieldSelectQuery.', '.$index['QUERY_SELECT'];
				$contSelect++;
			} 
			$newNameFielData = explode(' AS ',$fieldSelectQuery);
			$newNameField1 = '';
			if(sizeof($newNameFielData) <= 1)
			{
				$newNameFielData = explode(' as ',$fieldSelectQuery);
				$newNameField2 = '';
				$totArray = sizeof($newNameFielData);
				if(sizeof($newNameFielData) > 2 )
				{				
					$i = 1;
					foreach($newNameFielData as $row)
					{
						if($i < $totArray)
						{
							if($i+1 != $totArray)
								$newNameField2 .= ' '.$row.' as';
							else
							$newNameField2 .= ' '.$row;
						}
						$i++;
					}
					
				}
				else
					$newNameField2 = trim($newNameFielData[0]);
				$newNameField = $newNameField2;
			}
			else 
			{
				$totArray = sizeof($newNameFielData);
				if(sizeof($newNameFielData) > 2 )
				{				
					$i = 1;
					foreach($newNameFielData as $row)
					{
						if($i < $totArray)
						{
							if($i+1 != $totArray)
								$newNameField1 .= ' '.$row.' AS';
							else
							$newNameField1 .= ' '.$row;
						}
						$i++;
					}
					
				}
				else
					$newNameField1 = trim($newNameFielData[0]);
				$newNameField = $newNameField1;
			}
		}
	
		
		##### End options select query
    	
    	$sWhere=" AND $newNameField LIKE '";
    	if($all) $sWhere.="%";
    	$sWhere .= mysql_real_escape_string($fieldValue)."%' ";
    }
    return $sWhere;
}
function getAliasTable($idInbox='',$fieldName=''){
    $sTableName = '';
    $sRolCode   = getRolUser();
    $sSQL="SELECT DISTINCT ALIAS_TABLE FROM PMT_INBOX_FIELDS WHERE ID_INBOX='$idInbox' AND ROL_CODE ='$sRolCode' AND FIELD_NAME ='$fieldName'";
    $aData = executeQuery ($sSQL);
    if(is_array($aData) && count($aData) >0){
        if(isset($aData[1]['ALIAS_TABLE'])) $sTableName = $aData[1]['ALIAS_TABLE'];
    }
    return $sTableName;
}

function getQueryForMultipleSearch($idInbox='',$fieldValue){
    $sWhere = '';
    $sPartWhere='';
    $aFields= array();
    $sRolCode   = getRolUser();
    
    ### columns of Inbox
    $sSQL="SELECT DISTINCT FIELD_NAME,ALIAS_TABLE FROM PMT_INBOX_FIELDS WHERE ID_INBOX='$idInbox' AND ROL_CODE ='$sRolCode' AND HIDDEN_FIELD='0' ";
    $aData = executeQuery ($sSQL);
    foreach ($aData as $row) {
        $aFields[]=  array('FIELD_NAME' => $row['FIELD_NAME'] , 'TABLE_NAME' => $row['ALIAS_TABLE']);
    }

    ## concat columns
    foreach ($aFields as $item) {
    	if($item['TABLE_NAME'] != '')
        	$sPartWhere.=" OR ". $item['TABLE_NAME'].".".$item['FIELD_NAME']." LIKE '%".mysql_real_escape_string($fieldValue)."%' ";
    }
    
    ##### options select query
    $fieldSelect = "SELECT QUERY_SELECT FROM PMT_INBOX_FIELDS_SELECT WHERE ID_INBOX = '".$idInbox ."' AND ROL_CODE ='".$sRolCode."' AND TYPE IS NULL ";
	$datafieldSelect = executeQuery($fieldSelect);
	$contSelect = 1;
	$fieldSelectQuery = '';
	$totSelectQuery = sizeof($datafieldSelect);
	if(sizeof($datafieldSelect))
	{
		foreach($datafieldSelect as $index)
		{
			$newNameFielData = explode('AS',$index['QUERY_SELECT']);
			$newNameField1 = '';
			if(sizeof($newNameFielData) <= 1)
			{
				$newNameFielData = explode(' as ',$index['QUERY_SELECT']);
				$newNameField2 = '';
				$totArray = sizeof($newNameFielData);
				if(sizeof($newNameFielData) > 2 )
				{				
					$i = 1;
					foreach($newNameFielData as $row)
					{
						if($i < $totArray)
						{
							if($i+1 != $totArray)
								$newNameField2 .= ' '.$row.' as';
							else
							$newNameField2 .= ' '.$row;
						}
						$i++;
					}
					
				}
				else
					$newNameField2 = trim($newNameFielData[0]);
				$newNameField = $newNameField2;
			}
			else 
			{
				$totArray = sizeof($newNameFielData);
				if(sizeof($newNameFielData) > 2 )
				{				
					$i = 1;
					foreach($newNameFielData as $row)
					{
						if($i < $totArray)
						{
							if($i+1 != $totArray)
								$newNameField1 .= ' '.$row.' AS';
							else
							$newNameField1 .= ' '.$row;
						}
						$i++;
					}
					
				}
				else
					$newNameField1 = trim($newNameFielData[0]);
				$newNameField = $newNameField1;
			}
			
			$sPartWhere.=" OR ".$newNameField." LIKE '%".mysql_real_escape_string($fieldValue)."%' ";
		} 
		
		
		
	}
	##### End options select query
	
		
    if(strlen($sPartWhere) >0)$sWhere=" AND (FALSE $sPartWhere)";
    return $sWhere;
}

function getSqlWhere($idInbox=''){
  $sWhereCustom ='';
  $sRolCode   = getRolUser();
  $sqlGeneralwhere = "SELECT * FROM PMT_INBOX_WHERE WHERE IWHERE_ROL_CODE ='$sRolCode' AND IWHERE_IID_INBOX = '$idInbox' ";
  $resultGeneralwhere = executeQuery($sqlGeneralwhere);
  if(sizeof($resultGeneralwhere)){
    foreach ($resultGeneralwhere as $key => $value) {
      $aPartWhere = explode("@@", $value['IWHERE_QUERY']);  // WHERE PMT_DEMANDES.VILLA = @@USER_LOGGED AND STRUCTUREIE = (SELECT EIE FROM USERS WHERE USERS_ID = @@USER_LOGGED)
      if(count($aPartWhere) >1){
        $bFlag=true;
        $aSysVar  = array();
        for($i=1 ;$i < count($aPartWhere) ;$i++) {
          $ind = 1;
          $sVarName='';
          $_chr = substr($aPartWhere[$i], 0, 1); 
          while ( preg_match("/^[A-Za-z0-9_]+$/", $_chr) && ($ind <= strlen($aPartWhere[$i]))){
            $sVarName .= $_chr;
            if($ind < strlen($aPartWhere[$i]))
              $_chr = substr($aPartWhere[$i], $ind, 1);
            $ind++;     
          }
          if(isset($_SESSION[$sVarName])){
            $aSysVar[$sVarName] = $_SESSION[$sVarName];
          }
          else{
            $bFlag = false;
            break;
          }
        }
        if($bFlag){
          foreach ($aSysVar as $key => $valSess) {
            $value['IWHERE_QUERY'] =str_replace("@@".$key, "'". $valSess."'", $value['IWHERE_QUERY']);
          }
          $sWhereCustom.=' '.$value['IWHERE_QUERY'];
        }
      }
      else
        $sWhereCustom.=' '.$value['IWHERE_QUERY'];
    } 
  }
  return $sWhereCustom;
}

######################################-----END FUNCTIONS-----######################################
