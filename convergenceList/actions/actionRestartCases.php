<?php 
G::loadClass ( 'pmFunctions' );
G::LoadClass("case");
G::loadClass('pmTable');
require_once 'classes/model/AdditionalTables.php';
G::LoadClass('reportTables');
header ( "Content-Type: text/plain" );

#####################################################Functions####################################################

function strstr_array( $haystack, $needle ) {
	if ( !is_array( $haystack ) ) {
		return false;
	}
	foreach ( $haystack as $element ) {
		if ( strstr( $element, $needle ) ) {
			return $element;
		}
	}
}


function FRegeneratePMCases($caseId) {
	
	///////////////////////// Regenerate Tables ////////////////////////////////////////	
	
	// Update the status to Draft in this table
	$query1 = "UPDATE APPLICATION SET APP_STATUS  = 'DRAFT', APP_FINISH_DATE = NULL WHERE  APP_UID = '".$caseId."'";	
	$apps1=executeQuery($query1);
	// End Update the status to Draft in this table

	$query2="DELETE FROM wf_".SYS_SYS.".APP_DELAY WHERE APP_UID='".$caseId."'";
	$apps2=executeQuery($query2);

	// Update the status to Open in this table
	$query7 = "UPDATE APP_DELEGATION SET DEL_THREAD_STATUS  = 'OPEN', DEL_FINISH_DATE = NULL WHERE  APP_UID = '".$caseId."' AND DEL_INDEX = '1' ";	
	$apps1=executeQuery($query7);
	$query3="DELETE FROM wf_".SYS_SYS.".APP_DELEGATION WHERE APP_UID='".$caseId."' AND DEL_INDEX <> '1' ";
	$apps3=executeQuery($query3);
	// End Update the status to Open in this table

	$query4="DELETE FROM wf_".SYS_SYS.".APP_DOCUMENT WHERE APP_UID='".$caseId."'";
	$apps4=executeQuery($query4);
	$query5="DELETE FROM wf_".SYS_SYS.".APP_MESSAGE WHERE APP_UID='".$caseId."'";
	$apps5=executeQuery($query5);
	$query6="DELETE FROM wf_".SYS_SYS.".APP_OWNER WHERE APP_UID='".$caseId."'";
	$apps6=executeQuery($query6);
	// Update the status to Open in this table	
	$query7 = "UPDATE APP_THREAD SET APP_THREAD_STATUS  = 'OPEN', DEL_INDEX='1' WHERE  APP_UID = '".$caseId."'";	
	$apps1=executeQuery($query7);
	// End Update the status to Open in this table
	$query8="DELETE FROM wf_".SYS_SYS.".SUB_APPLICATION WHERE APP_UID='".$caseId."'";
	$apps8=executeQuery($query8);
	$query9="DELETE FROM wf_".SYS_SYS.".CONTENT WHERE CON_CATEGORY LIKE 'APP_%' AND CON_ID='".$caseId."'";
	$apps9=executeQuery($query9);	
	$query10="DELETE FROM wf_".SYS_SYS.".APP_EVENT WHERE APP_UID='".$caseId."'";
	$apps10=executeQuery($query10);	
	// Update the status to DRAFT in this table
	$query11="DELETE FROM wf_".SYS_SYS.".APP_CACHE_VIEW WHERE APP_UID='".$caseId."' AND DEL_INDEX <> '1' ";
	$apps11=executeQuery($query11);
	$query7 = "UPDATE APP_CACHE_VIEW SET APP_STATUS  = 'DRAFT' WHERE  APP_UID = '".$caseId."' AND DEL_INDEX = '1' ";	
	$apps1=executeQuery($query7);	
	
	// End Update the status to DRAFT in this table
	
	             
	///////////////////////// End Regenerate Tables ////////////////////////////////////////

	$auxUsrUID = $_SESSION['USER_LOGGED'];
    $auxUsruname = $_SESSION['USR_USERNAME'];
	
	///////////////////////// Route Again the Case /////////////////////////////////////////
	G::LoadClass("case");
	$oCase = new Cases ();
	$newFields = $oCase->loadCase ($caseId); 

	$newFields['APP_DATA']['FLG_INITUSERUID'] = $auxUsrUID;
	$newFields['APP_DATA']['FLG_INITUSERNAME'] = $auxUsruname;
	$newFields['APP_DATA']['FLAG_ACTION'] = 'actionAjaxRestartCases';
	$newFields['APP_DATA']['EXEC_AUTO_DERIVATE'] = 'NO';
	if(isset($newFields['APP_DATA']['FLAGTYPO3'])){
		unset($newFields['APP_DATA']['FLAGTYPO3']);
	}		
	$USR_UID = $newFields['APP_DATA']['USER_LOGGED'];
	$oCase->updateCase($caseId, $newFields);	
	
	$queryDelIndex = "SELECT  MAX(DEL_INDEX) AS DEL_INDEX FROM APP_DELEGATION WHERE APP_UID = '".$caseId."'";
	$DelIndex = executeQuery($queryDelIndex);  
	if(isset($DelIndex[1]['DEL_INDEX']) && $DelIndex[1]['DEL_INDEX'] != ''){
		$queryDel = "SELECT USR_UID,PRO_UID FROM APP_DELEGATION WHERE APP_UID = '".$caseId."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
	    $resDel = executeQuery($queryDel);
	    if(sizeof($resDel)){
	    	if($resDel[1]['USR_UID'] == "" || $resDel[1]['USR_UID']!= $USR_UID ){
	        	$queryuPDel = "UPDATE APP_DELEGATION SET USR_UID = '".$USR_UID."' 
	            WHERE APP_UID = '".$caseId."' AND DEL_INDEX = '".$DelIndex[1]['DEL_INDEX']."' ";
	            $queryuPDel = executeQuery($queryuPDel);
	        }
	        $proUid = $resDel[1]['PRO_UID'];
	        
	        # execute Triggers task Ini
		  	$query = "SELECT TAS_UID FROM TASK WHERE TAS_START = 'TRUE' AND PRO_UID = '".$proUid."'";	//query for select all start tasks
	        $startTasks = executeQuery($query);
	        foreach($startTasks as $rowTask){
		        $taskId = $rowTask['TAS_UID'];
		        $stepsByTask = getStepsByTask($taskId);
	            foreach ($stepsByTask as $caseStep){
				    $caseStepRes[] = 	 $caseStep->getStepUidObj();
			    }
			    break;
	        }
	        
			$totStep = 0;
			foreach($caseStepRes as $index)
			{
				$stepUid = $index;
				executeTriggersMon($proUid, $caseId, $stepUid, 'BEFORE', $taskId);	//execute trigger before form
				executeTriggersMon($proUid, $caseId, $stepUid, 'AFTER', $taskId);	//execute trigger after form	
				$totStep++;
			} 
			# end execute Triggers task Ini
	   }
	}
	
	// $oCase->updateCase($caseId, $newFields);
	
	// If the user is different
	/*if($_SESSION['USER_LOGGED'] != $newFields['APP_DATA']['USER_LOGGED']){
		$arrayUser = userInfo($newFields['APP_DATA']['USER_LOGGED']); 		 
		$_SESSION['USER_LOGGED'] = $newFields['APP_DATA']['USER_LOGGED'];
    	$_SESSION['USR_USERNAME'] = $arrayUser['username'];
	}*/
	// End If the user is different

	// $resInfo = PMFDerivateCase($caseId, 1,true, $USR_UID);
	autoDerivate($proUid,$caseId,$USR_UID);

	///////////////////////// End Route Again the Case /////////////////////////////////////
}

function FRegenerateRPT(){

	$cnn = Propel::getConnection('workflow');
	$stmt = $cnn->createStatement();	
	$sqlRPTable = "SELECT * FROM ADDITIONAL_TABLES WHERE PRO_UID <> '' AND ADD_TAB_TYPE = 'NORMAL' "; 
    $resRPTable=executeQuery($sqlRPTable);
    if(sizeof($resRPTable)){
	    foreach ($resRPTable as $key => $value) {
	    	$additionalTables = new AdditionalTables();
	        $table = $additionalTables->load($value['ADD_TAB_UID']);
	        if ($table['PRO_UID'] != '') {	        	
	        	$truncateRPTable = "TRUNCATE TABLE  ".$value['ADD_TAB_NAME']." ";
	        	$rs = $stmt->executeQuery($truncateRPTable, ResultSet::FETCHMODE_NUM);    			
	            $additionalTables->populateReportTable(
	                    $table['ADD_TAB_NAME'],
	                    pmTable::resolveDbSource($table['DBS_UID']),
	                    $table['ADD_TAB_TYPE'],
	                    $table['PRO_UID'],
	                    $table['ADD_TAB_GRID']
	            );
	     	}
	    }         	
    }
}
#####################################################End Functions####################################################

$array=array();
$array = $_REQUEST['item'];
$items = $array; 
$pmTableId = $_REQUEST['pmTableId'];
$tableType = "Report";
$tableName = '';

if($items != ''){
	$query = "SELECT APP_STATUS FROM APPLICATION WHERE APP_UID = '".$items."' AND APP_STATUS != 'DRAFT' ";
	$data = executeQuery($query);
	if(isset($items) && $items != ''  ){		
		FRegeneratePMCases($items);			
	}		
	
	if($tableType == "Report"){
		//FRegenerateRPT(); // regenerate all RP tables
	}

	$messageInfo = "The case was Restarted sucessfully!";
}
else{
	$messageInfo = "The case was not Restarted";
}	
	
$paging = array ('success' => true, 'messageinfo' => $messageInfo);
echo G::json_encode ( $paging );
?>