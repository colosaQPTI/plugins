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

function FDeletePMCases($caseId) {
	$query1="DELETE FROM wf_".SYS_SYS.".APPLICATION WHERE APP_UID='".$caseId."' ";
	$apps1=executeQuery($query1);
	$query2="DELETE FROM wf_".SYS_SYS.".APP_DELAY WHERE APP_UID='".$caseId."'";
	$apps2=executeQuery($query2);
	$query3="DELETE FROM wf_".SYS_SYS.".APP_DELEGATION WHERE APP_UID='".$caseId."'";
	$apps3=executeQuery($query3);
	$query4="DELETE FROM wf_".SYS_SYS.".APP_DOCUMENT WHERE APP_UID='".$caseId."'";
	$apps4=executeQuery($query4);
	$query5="DELETE FROM wf_".SYS_SYS.".APP_MESSAGE WHERE APP_UID='".$caseId."'";
	$apps5=executeQuery($query5);
	$query6="DELETE FROM wf_".SYS_SYS.".APP_OWNER WHERE APP_UID='".$caseId."'";
	$apps6=executeQuery($query6);
	$query7="DELETE FROM wf_".SYS_SYS.".APP_THREAD WHERE APP_UID='".$caseId."'";
	$apps7=executeQuery($query7);
	$query8="DELETE FROM wf_".SYS_SYS.".SUB_APPLICATION WHERE APP_UID='".$caseId."'";
	$apps8=executeQuery($query8);
	$query9="DELETE FROM wf_".SYS_SYS.".CONTENT WHERE CON_CATEGORY LIKE 'APP_%' AND CON_ID='".$caseId."'";
	$apps9=executeQuery($query9);	
	$query10="DELETE FROM wf_".SYS_SYS.".APP_EVENT WHERE APP_UID='".$caseId."'";
	$apps10=executeQuery($query10);
	$query11="DELETE FROM wf_".SYS_SYS.".APP_CACHE_VIEW WHERE APP_UID='".$caseId."'";
	$apps11=executeQuery($query11);
	/*$query12="DELETE FROM wf_".SYS_SYS.".APP_HISTORY WHERE APP_UID='".$caseId."'";
	$apps12=executeQuery($query12);*/
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
$array = $_REQUEST['array'];
$items = json_decode($array,true);
$pmTableId = $_REQUEST['pmTableId'];
$tableType = "Report";
$tableName = '';

// Check if the Table is Report or PM Table
$sqlAddTable = "SELECT * FROM ADDITIONAL_TABLES WHERE ADD_TAB_UID = '$pmTableId' ";
$resAddTable=executeQuery($sqlAddTable);
if(sizeof($resAddTable)){
	if($resAddTable[1]['PRO_UID'] == ''){
		$tableType = "pmTable";
		$tableName = $resAddTable[1]['ADD_TAB_NAME'];
		$sqlPMtable = "SELECT * FROM ".$tableName." ";
		$resPMtable=executeQuery($sqlPMtable);
		if(sizeof($resPMtable)){
			$keysPMTable = array_keys($resPMtable[1]);
	    	$pmTableFieldAPPUID = strstr_array($keysPMTable,'APP_UID');	    	
		}	    
	}		
}

// Check if the Table is Report or PM Table
if(count($items)>0){
	$oCase = new Cases ();
	foreach($items as $item){
		$vals = array_keys($item);
		$APPUID = strstr_array($vals,'APP_UID');	
		if(isset($item[$APPUID]) && $item[$APPUID] != ''){		
		    FDeletePMCases($item[$APPUID]);
			$sqlRPTable = "SELECT * FROM ADDITIONAL_TABLES WHERE PRO_UID <> '' AND ADD_TAB_TYPE = 'NORMAL' "; 
		    $resRPTable=executeQuery($sqlRPTable);
		    if(sizeof($resRPTable)){
			    foreach ($resRPTable as $key => $value) {
			    	$additionalTables = new AdditionalTables();
			        $table = $additionalTables->load($value['ADD_TAB_UID']);
			        if ($table['PRO_UID'] != '') {	        	
			        	$deleteItem = "DELETE FROM ".$value['ADD_TAB_NAME']." WHERE APP_UID = '".$item[$APPUID]."'";
			        	$rs = executeQuery($deleteItem);
			     	}
			    }        	
		    }
            if($tableType == "pmTable" && $tableName != ''){
            	$sqlDelTable = "DELETE FROM ".$tableName." WHERE ".$pmTableFieldAPPUID." = '".$item[$APPUID]."' ";				
                $resDelTable = executeQuery($sqlDelTable);
            }   
		}	
	}
	
	/*if($tableType == "Report"){
		FRegenerateRPT(); // regenerate all RP tables
	}*/

	$messageInfo = "Le dossier a été correctement supprimé.";
}
else{
	$messageInfo = "Le dossier n'a pas été supprimé.";
}	
	
$paging = array ('success' => true, 'messageinfo' => $messageInfo);
echo G::json_encode ( $paging );
?>