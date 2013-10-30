<?php
G::loadClass ( 'pmFunctions' );
/*
function getProUid($appUid){
	$sSQL = "SELECT PRO_UID FROM APPLICATION WHERE APP_UID='$appUid'";
    $aResult = executeQuery($sSQL);
    $proUid = '';
    if (isset($aResult[1]['PRO_UID']))
    	$proUid =$aResult[1]['PRO_UID'];
    return $proUid;
}*/
 
function getTotalDuplicateRecords($reg, $fldNamStat, $fldValStat) {

	G::LoadClass("case");
	$aCase_elected = array();
    $exist_case_elected = false;
    $totalRegisters = 0;
	$items = json_decode($reg,true);
	if(is_array($items)  && isset($items[0]['APP_UID'])){
		$firstAppUid = $items[0]['APP_UID'];
		$proUid = getProUid($firstAppUid);
	    $result_mk = getAllDoublon($proUid,$firstAppUid);
	    $merge = array_merge($items,$result_mk);
	    $totalRegisters = count($merge);

	    ####### Verif Statut = Product
	 	if(is_array($merge)) {
		    foreach($merge as $aRow) {
				$_appUid 	= $aRow['APP_UID'];
				$oCase 		= new Cases ();
				$oDataCase 	= $oCase->loadCase ($_appUid);
				if((isset($oDataCase['APP_DATA'][$fldNamStat]) && $oDataCase['APP_DATA'][$fldNamStat] == $fldValStat) || (isset($oDataCase['APP_DATA'][strtolower($fldNamStat)]) && $oDataCase['APP_DATA'][strtolower($fldNamStat)] == $fldValStat) || (isset($oDataCase['APP_DATA'][strtoupper($fldNamStat)]) && $oDataCase['APP_DATA'][strtoupper($fldNamStat)] == $fldValStat)){
					$aCase_elected = $oDataCase;
					$aCasesDeleted = array();
					foreach ($merge as $aCaseRow) {
						$aCasesDeleted[] = $aCaseRow['APP_UID'];
					}
					$_SESSION['ELIMINATE_POSSIBLE_CASES'] = $aCasesDeleted;
					$exist_case_elected = true;
					break;
				}
			}
		}
	    ####### End Verif Statut Produit
   	}
   	return (array($totalRegisters, $aCase_elected, $exist_case_elected));
}

function deleteDuplicatedCases($appUidElected, $tableName ='') {
	G::LoadClass("case");
	$numDeletedCases = 0;
	$aEliminatePossibleCases = isset($_SESSION['ELIMINATE_POSSIBLE_CASES'])?$_SESSION['ELIMINATE_POSSIBLE_CASES']:array();
	foreach ($aEliminatePossibleCases as $caseId){
		if($caseId != $appUidElected){
			deletePMCases($caseId); ### req
			$numDeletedCases++;
			$CurDateTime=date('Y-m-d H:i:s');
			insertHistoryLogPlugin($appUidElected,$_SESSION['USER_LOGGED'],$CurDateTime,'1',$caseId,'Delete Case');//insertHistoryLogPlugin(father,......child)
		}	
	}
	// genDataReport($tableName);
	unset($_SESSION['ELIMINATE_POSSIBLE_CASES']);
	return $numDeletedCases;
}

function getData($reg,$idInbox) {

	G::LoadClass("case");
	$aData = array();
	$items = json_decode($reg,true);
	if(count($items) == 1){ ### do make 
	    $firstAppUid = isset($items[0]['APP_UID'])?$items[0]['APP_UID']:'0';
		$proUid 	 = getProUid($firstAppUid);
	    $result_mk 	 = getAllDoublon($proUid,$firstAppUid);
	    $merge = array_merge($items,$result_mk);
	}else{
		$merge = $items;
	}
	$field = 'NUM_DOSSIER';
	$merge = orderMultiDimensionalArray($merge, $field, '');
	
	list($dataNum, $fieldData) = getFieldsIncludedDoublon($idInbox);
	if(is_array($merge) && count($merge) >0 && is_array($fieldData) &&  count($fieldData) >0){
	 	$ind=0;// ind row
	 
	 	foreach($fieldData as $col){
	 		$ite=0;
	 		$aData[$ind]["COL_DOUBLON".$ite] = $col['FIELD_NAME'];
	 		$aData[$ind]["COL_DOUBLON".$ite."_DESC"] = $col['FIELD_DESC'];
		
			foreach($merge as $aRow) {
				$_appUid 	= $aRow['APP_UID'];
				$oCase 		= new Cases ();
				$oDataCase 	= $oCase->loadCase ($_appUid);
				
			    $ite++;
			    $col['TYPE_CONFIGURATION']=isset($col['TYPE_CONFIGURATION'])?$col['TYPE_CONFIGURATION']:'';
			    
			    switch($col['TYPE_CONFIGURATION'])
			    {
			    	case 1:
			    		
			    		$fieldNameShow = isset($oDataCase['APP_DATA'][$col['FIELD_NAME']])? $oDataCase['APP_DATA'][$col['FIELD_NAME']]: '';
			    		if($fieldNameShow == 1)
			    			 $fieldNameShow = 'Yes';
			    			else
			    			 $fieldNameShow = 'No';
			    		
			    		$aData[$ind]["COL_DOUBLON".$ite] = $fieldNameShow;
						// radio
				    	$aData[$ind]["COL_DOUBLON_CHK".$ite] = isset($oDataCase['APP_DATA'][$col['FIELD_NAME']])? $oDataCase['APP_DATA'][$col['FIELD_NAME']]: '';
						break;
			    		
			    	case 2:
			    		$select = "SELECT ".$col['CONFIG_FIELD_SHOW']." FROM ".$col['CONFIG_PMTABLE']." WHERE ".$col['CONFIG_FIELD_CONDITION']." = '".$oDataCase['APP_DATA'][$col['FIELD_NAME']]."'  ";
						$query = executeQuery($select);
						
						$fieldNameShow = isset($query[1][$col['CONFIG_FIELD_SHOW']])?$query[1][$col['CONFIG_FIELD_SHOW']]:0;
						$aData[$ind]["COL_DOUBLON".$ite] = $fieldNameShow;
						// radio
				    	$aData[$ind]["COL_DOUBLON_CHK".$ite] = isset($oDataCase['APP_DATA'][$col['FIELD_NAME']])? $oDataCase['APP_DATA'][$col['FIELD_NAME']]: '';
						break;
			    	
			    	default:
			    		
			    		$aData[$ind]["COL_DOUBLON".$ite] = isset($oDataCase['APP_DATA'][$col['FIELD_NAME']])? $oDataCase['APP_DATA'][$col['FIELD_NAME']]: '';
						// radio
					    $aData[$ind]["COL_DOUBLON_CHK".$ite] = isset($oDataCase['APP_DATA'][$col['FIELD_NAME']])? $oDataCase['APP_DATA'][$col['FIELD_NAME']]: '';
						break;
			    }
			}
			##
			$ind++;
		}
	}

   return (array(count($aData), $aData));
}

function getDataSupprime($reg,$idInbox) {

	G::LoadClass("case");
	$aData1 = array();
	$aData = array();
	$items = json_decode($reg,true);
	if(count($items) == 1){ ### do make 
	    $firstAppUid = isset($items[0]['APP_UID'])?$items[0]['APP_UID']:'0';
		$proUid 	 = getProUid($firstAppUid);
	    $result_mk 	 = getAllDoublon($proUid,$firstAppUid);
	    $merge = array_merge($items,$result_mk);
	}else{
		$merge = $items;
	}
	$field = 'NUM_DOSSIER';
	$merge = orderMultiDimensionalArray($merge, $field, '');
	
	list($dataNum, $fieldData) = getFieldsIncludedDoublon($idInbox);
	if(is_array($merge) && count($merge) >0 && is_array($fieldData) &&  count($fieldData) >0){
	 	$ind=0;// ind row
		foreach($merge as $aRow) {
			$_appUid 	= $aRow['APP_UID'];
				
			$aData1["APP_UID"] = $_appUid;
			$aData[] = $aData1;
		}
		
	}
//G::pr($aData);
   return (array(count($aData), $aData));
}
 
function getRolUserDoublon(){
	require_once ("classes/model/Users.php");
    $oUser = new Users();
    $oDetailsUser = $oUser->load ($_SESSION ['USER_LOGGED']);
    return $oDetailsUser['USR_ROLE'];
}

function getFieldsforConfigDoublon_old($idInbox) {
	$rolUser= getRolUserDoublon();
	$sSQL="SELECT * FROM PMT_DOUBLON_FIELD
		   WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox'  ORDER BY FIELD_POSITION";
	$aResult= executeQuery($sSQL);
	if(!(is_array($aResult) && count($aResult)>0)){
		$sSQL="SELECT FLD_UID  AS FIELD_NAME, DESCRIPTION AS FIELD_DESC, POSITION AS FIELD_POSITION, '0' AS FIELD_INCLUDE 
				FROM PMT_INBOX_FIELDS
				WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox' ORDER BY FIELD_POSITION";
		$aResult= executeQuery($sSQL);	
	}
   return (array(sizeof($aResult), array_values($aResult)));
}

function getFieldsforConfigDoublon($idInbox,$proUid) {

	require_once PATH_CONTROLLERS . 'pmTablesProxy.php';
    G::LoadClass('reportTables');
    G::LoadClass("form");
	$rolUser= getRolUserDoublon();

	#### 

	$sSQL="SELECT * FROM PMT_DOUBLON_FIELD
		   WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox'  
		   ORDER BY FIELD_POSITION"; // ORDER BY FIELD_POSITION
	$aResult= executeQuery($sSQL);
	

	$select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM DYNAFORM WHERE PRO_UID = '".$proUid ."'";
	$resultDynaform = executeQuery($select);
	
	$_dataForms =  array();
	foreach($resultDynaform As $rowDynaform)
	{
		$dynaform = new Form($proUid . PATH_SEP . $rowDynaform['DYN_UID'], PATH_DYNAFORM , SYS_LANG , false);
		foreach ($dynaform->fields as $fieldName => $field) {

				$record = array (
						"FIELD_NAME" => $field->name, 
						"FIELD_LABEL" => $field->label,
						"FIELD_TYPE" => $field->type
						
				);
				$_dataForms[] = $record;
			}
	}
	if(!(is_array($aResult) && count($aResult)>0)){
		/*$sSQL="SELECT FLD_UID  AS FIELD_NAME, DESCRIPTION AS FIELD_DESC, POSITION AS FIELD_POSITION 
				FROM PMT_INBOX_FIELDS
				WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox' ORDER BY FIELD_POSITION";
		$aResult= executeQuery($sSQL);	
		*/
		#######################

		$oReportTables = new pmTablesProxy();
	    $aFields['FIELDS'] = array();
	    $aFields['PRO_UID'] = $proUid;
	    $dynFields = array();
	    $dynFields = $oReportTables->_getDynafields($aFields['PRO_UID'], 'xmlform', 0,100000, null);
	    $aDynFields = array();
	    foreach ($dynFields['rows'] as $row) {		
			$aDynFields[strtoupper($row['FIELD_NAME'])] = $row['FIELD_NAME'];
		}

		$_dataFields =  array();
		$pos = 1;
		/*foreach ($aResult as $row) {
			$record = array("FIELD_NAME" => $row['FIELD_NAME'], "FIELD_NAME_UPPER" => strtoupper($row['FIELD_NAME']), "FIELD_DESC" => $row['FIELD_DESC'], "FIELD_INCLUDE" => '0',"FIELD_POSITION" =>$pos,"TYPE" => 'RT');
			if(isset($aDynFields[$row['FIELD_NAME']])) unset($aDynFields[$row['FIELD_NAME']]);
			$_dataFields[] = $record;
			$pos++;
		}*/

		foreach ($aDynFields as $key => $value) {
			if($value == 'NUM_DOSSIER')
				$include = '1';
			else
				$include = '0';
				
			for($i = 0 ; $i < count($_dataForms) ; $i++)
			{
				if($_dataForms[$i]['FIELD_NAME'] == $value)
				{
						if($_dataForms[$i]['FIELD_LABEL'])
							$fieldDesc = $_dataForms[$i]['FIELD_LABEL'];
						else
							$fieldDesc = strtolower($key);
							
					$record = array("FIELD_NAME" => $value,
									"FIELD_NAME_UPPER" => strtoupper($key), 
									"FIELD_DESC" => $fieldDesc, 
									"FIELD_INCLUDE" => $include,
									"FIELD_POSITION" =>$pos,
									"TYPE" => 'DYN',
									"TYPE_CONFIGURATION"=> '',
									"CONFIG_PMTABLE" => '', 
									"CONFIG_FIELD_SHOW" => '',
									"CONFIG_FIELD_CONDITION" => '');
					$_dataFields[] = $record;
					break;
				}
			}
			$pos++;
		}
	
	}else{
		
		$_dataFields =  array();
		foreach ($aResult as $row) {
			$row['FIELD_ID']='';
				if($row['FIELD_NAME'] == 'NUM_DOSSIER')
					$row['FIELD_INCLUDE'] = '1';

			for($i = 0 ; $i < count($_dataForms) ; $i++)
			{
				if($_dataForms[$i]['FIELD_NAME'] == $row['FIELD_NAME'])
				{
					$row['FIELD_ID'] = $row['FIELD_NAME'];
						if($_dataForms[$i]['FIELD_LABEL'])
							$row['FIELD_DESC'] = $_dataForms[$i]['FIELD_LABEL'];
						else
							$row['FIELD_DESC'] = strtolower($row['FIELD_DESC']);

						switch($row['TYPE_CONFIGURATION'])
						{
							case 1:
								$row['TYPE_CONFIGURATION'] = 'Yes-No';
								break;
							case 2:
								$row['TYPE_CONFIGURATION'] = 'Query';
								break;
							default:
								$row['TYPE_CONFIGURATION'] = '';
								break;
						}
					$record = array(
								"FIELD_ID" => $row['FIELD_ID'], 
								"FIELD_NAME" => $row['FIELD_NAME'], 
								"FIELD_NAME_UPPER" => strtoupper($row['FIELD_NAME']), 
								"FIELD_DESC" => $row['FIELD_DESC'], 
								"FIELD_INCLUDE" => $row['FIELD_INCLUDE'],
								"FIELD_POSITION" =>$row['FIELD_POSITION'],
								"TYPE" => '',
								"TYPE_CONFIGURATION"=> $row['TYPE_CONFIGURATION'],
								"CONFIG_PMTABLE" => $row['CONFIG_PMTABLE'], 
								"CONFIG_FIELD_SHOW" => $row['CONFIG_FIELD_SHOW'],
								"CONFIG_FIELD_CONDITION" => $row['CONFIG_FIELD_CONDITION']);
					
						$_dataFields[] = $record;
						break;
				}
			}		
		}
		/*$_dataFields =  array();
		
		foreach ($aResult as $row) {
			if($row['FIELD_NAME'] == 'NUM_DOSSIER')
				$row['FIELD_INCLUDE'] = '1';
				
			$record = array("FIELD_NAME" => $row['FIELD_NAME'], 
							"FIELD_NAME_UPPER" => strtoupper($row['FIELD_NAME']), 
							"FIELD_DESC" => $row['FIELD_DESC'], 
							"FIELD_INCLUDE" => $row['FIELD_INCLUDE'],
							"FIELD_POSITION" =>$row['FIELD_POSITION']  ,
							"TYPE" => '');
			$_dataFields[] = $record;
		}*/
	}
		$field = 'FIELD_POSITION';
		$_dataFields = orderMultiDimensionalArray($_dataFields, $field, '');
	
	#####
  return (array(sizeof($_dataFields), array_values($_dataFields)));
}

function getFieldsIncludedDoublon($idInbox) {
   $rolUser= getRolUserDoublon();
   $sSQL="SELECT * FROM PMT_DOUBLON_FIELD
			WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox' AND FIELD_INCLUDE ='1'  ORDER BY FIELD_POSITION";
	$aResult= executeQuery($sSQL);
   return (array(sizeof($aResult), array_values($aResult)));
}

function saveFieldsDoublon($idInbox, $fieldCustom) {
	$items = json_decode($fieldCustom,true);
	$rolUser= getRolUserDoublon();
    $sSQL="DELETE FROM PMT_DOUBLON_FIELD WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox'";
	executeQuery($sSQL);

	$position = 0;
	foreach ($items as $row) {
		
		$queryPos = "SELECT max(FIELD_POSITION) AS POSITION FROM PMT_DOUBLON_FIELD WHERE ROL_CODE = '" . $rolUser . "' AND ID_INBOX = '" . $idInbox ."'";
		$position = executeQuery ( $queryPos );
		$positionField = $position [1] ['POSITION'];
		$positionField = $positionField + 1;
		$row['TYPE_CONFIGURATION'] = isset($row['TYPE_CONFIGURATION'])?$row['TYPE_CONFIGURATION']:'';
		$row['CONFIG_PMTABLE'] = isset($row['CONFIG_PMTABLE'])?$row['CONFIG_PMTABLE']:'';
		$row['CONFIG_FIELD_SHOW'] = isset($row['CONFIG_FIELD_SHOW'])?$row['CONFIG_FIELD_SHOW']:'';
		$row['CONFIG_FIELD_CONDITION'] = isset($row['CONFIG_FIELD_CONDITION'])?$row['CONFIG_FIELD_CONDITION']:'';
		
		$sSQL="INSERT INTO PMT_DOUBLON_FIELD (FIELD_NAME, FIELD_DESC,FIELD_POSITION,FIELD_INCLUDE ,
		 ROL_CODE, ID_INBOX,TYPE_CONFIGURATION,CONFIG_PMTABLE,CONFIG_FIELD_SHOW,CONFIG_FIELD_CONDITION) VALUES(
			'".$row['FIELD_NAME']."',
			'".mysql_real_escape_string($row['FIELD_DESC'])."',
			".$row['FIELD_POSITION'].",
			'".$row['FIELD_INCLUDE']."',
			'".$rolUser."',
			'".$idInbox."',
			'".$row['TYPE_CONFIGURATION']."',
			'".$row['CONFIG_PMTABLE']."',
			'".$row['CONFIG_FIELD_SHOW']."',
			'".$row['CONFIG_FIELD_CONDITION']."')";		
		
		executeQuery($sSQL);	
	}
   return true;
}
function resetFieldsDoublon($idInbox) {
	$rolUser= getRolUserDoublon();
	 $sSQL="DELETE FROM PMT_DOUBLON_FIELD
				WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox'";
	$aResult= executeQuery($sSQL);
	$bRes= '0';
	if(is_array($aResult) && count($aResult)>0){
		$bRes='1';
	}
   return $bRes;
   
   
}
function verifyFieldsDoublon($idInbox) {
	$rolUser= getRolUserDoublon();
	 $sSQL="SELECT * FROM PMT_DOUBLON_FIELD
				WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox'";
	$aResult= executeQuery($sSQL);
	$bRes= '0';
	if(is_array($aResult) && count($aResult)>0){
		$bRes='1';
	}
   return $bRes;
}

function createCase($appUid, $appData,$uidTask,$jsonSelected,$hiddenUids, $idInbox) {
	
	G::LoadClass("case");
	$_auxUserUid 	= $_SESSION['USER_LOGGED'];
    $_auxUserName 	= $_SESSION['USR_USERNAME'];
	$items 			= json_decode($appData,true);
	$oCase 			= new Cases ();
	$newFields 		= $oCase->loadCase ($appUid);
	$PRO_UID 		= $newFields['PRO_UID'];
	$USR_UID 		= $newFields['APP_DATA']['USER_LOGGED'];
	list($dataNum, $data) = getDataSupprime($jsonSelected, $idInbox);
	foreach ($items as $key => $value) {
		if(isset($newFields['APP_DATA'][$key])) {$newFields['APP_DATA'][$key] = $value;}
	}
	
    $newFields['APP_DATA']['FLAG_ACTION'] = 'actionAjax';
	$newFields['APP_DATA']['FLG_INITUSERUID_DOUBLON'] 	= $_auxUserUid;
    $newFields['APP_DATA']['FLG_INITUSERNAME_DOUBLON']	= $_auxUserName;
    if(isset($newFields['APP_DATA']['FLAGTYPO3'])){
		unset($newFields['APP_DATA']['FLAGTYPO3']);
	}
	if(isset($newFields['APP_DATA']['FLG_INITUSERUID'])){
		unset($newFields['APP_DATA']['FLG_INITUSERUID']);
	}
	if(isset($newFields['APP_DATA']['FLG_INITUSERNAME'])){
		unset($newFields['APP_DATA']['FLG_INITUSERNAME']);
	}
	// If the user is different
	if($_SESSION['USER_LOGGED'] != $newFields['APP_DATA']['USER_LOGGED']){
		$arrayUser = userInfo($newFields['APP_DATA']['USER_LOGGED']); 		 
		$_SESSION['USER_LOGGED'] = $newFields['APP_DATA']['USER_LOGGED'];
    	$_SESSION['USR_USERNAME'] = $arrayUser['username'];
	}
	$newFields['APP_DATA']['FLAG_EDIT'] = 1;
	$newFields['APP_DATA']['SW_CREATE_CASE'] = 1; # control trigger create new cases csv
	$caseUID = PMFNewCase($PRO_UID, $USR_UID, $uidTask, $newFields['APP_DATA']);	
	$hiddenUids = json_decode($hiddenUids,true);
	$rowSelected = json_decode($jsonSelected,true);
	$selected = array ();
	foreach($rowSelected as $row)
	{
		$i = 0;
		if(sizeof($hiddenUids))
		{
			foreach($hiddenUids as $rowHidden)
			{
				if($row['APP_UID'] == $rowHidden['APP_UID'])
					$i = 1;	
			}
			if($i == 0)
				$selected[] = $row;
		}
	
	}
	
	if(sizeof($rowSelected) == 1)
	{
		foreach($data as $row)
		{
			$swHidden = 0;
			foreach($hiddenUids as $rowHidden)
			{
				if($row['APP_UID'] == $rowHidden['APP_UID'])
				{
					$swHidden = 1;
				}
					
			}
			if($swHidden == 0)
			{
					$CurDateTime=date('Y-m-d H:i:s');
					insertHistoryLogPlugin($row['APP_UID'],$_auxUserUid,$CurDateTime,'1',$caseUID,'Suppression','999');
					deletePMCases($row['APP_UID']);
			}
			
		}
	}
	else
	{
		foreach($rowSelected as $row)
		{
			$swHidden = 0;
			foreach($hiddenUids as $rowHidden)
			{
				if($row['APP_UID'] == $rowHidden['APP_UID'])
				{
					$swHidden = 1;
				}
					
			}
			if($swHidden == 0)
			{
					$CurDateTime=date('Y-m-d H:i:s');
					insertHistoryLogPlugin($row['APP_UID'],$_auxUserUid,$CurDateTime,'1',$caseUID,'Suppression','999');
					deletePMCases($row['APP_UID']);
			}
		}
	}
	
     ## executeTriggers task ini
    $_SESSION['APPLICATION'] = $caseUID;  
	$query = "SELECT TAS_UID FROM TASK WHERE TAS_START = 'TRUE' AND PRO_UID = '".$PRO_UID."'";	//query for select all start tasks
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
		executeTriggersMon($PRO_UID, $caseUID, $stepUid, 'BEFORE', $taskId);	//execute trigger before form
		executeTriggersMon($PRO_UID, $caseUID, $stepUid, 'AFTER', $taskId);	//execute trigger after form	
		$totStep++;
	} 
	## End executeTriggers task ini
	$_SESSION['USER_LOGGED'] = $_auxUserUid;
    $_SESSION['USR_USERNAME'] = $_auxUserName;
	$resp = PMFDerivateCase($caseUID, 1,true, $USR_UID);   
	
	return $resp;
}

try {
  $sOption = $_REQUEST["option"];
  switch ($sOption) {
    case "dataInbox": 
    			$registers = $_REQUEST["registers"];
    			$idInbox= isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
                list($dataNum, $data) = getData($registers, $idInbox);
                echo G::json_encode(array("success" => true, "total" => $dataNum, "data" => $data));
                break;
                
    case "configDoublon": 
				$idInbox= isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
				$proUid= isset($_REQUEST["proUid"])?$_REQUEST["proUid"]:'';
			    list($dataNum, $data) = getFieldsforConfigDoublon($idInbox,$proUid);
			    echo G::json_encode(array("success" => true, "total" => $dataNum, "data" => $data));
			    break;
			    
	case "saveConfigDoublon":
				$idInbox= isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
				$fieldsDoublon= isset($_REQUEST["fieldsDoublon"])?$_REQUEST["fieldsDoublon"]:'';
			    $resp = saveFieldsDoublon($idInbox,$fieldsDoublon);
			    echo G::json_encode(array("success" => true, "message" => "OK"));
			    break;
	case "resetConfigDoublon":
				$idInbox= isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
				$proUid = isset($_REQUEST["proUid"])?$_REQUEST["proUid"]:'';
				$resp = resetFieldsDoublon($idInbox);
				list($dataNum, $data) = getFieldsforConfigDoublon($idInbox,$proUid);
			    echo G::json_encode(array("success" => true, "total" => $dataNum, "data" => $data));
			  break;
    case "isConfig":
				$idInbox= isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
			    $config = verifyFieldsDoublon($idInbox);
			    echo G::json_encode(array("success" => true, "configured" => $config));
			    break;
	case "getFields":
				$idInbox= isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
			    $fields = getFieldsIncludedDoublon($idInbox);
			    echo G::json_encode(array("success" => true, "data" => $fields));
			    break;
	case "createCase":
				$appData = isset($_REQUEST["appData"])?$_REQUEST["appData"]:''; 
				$appUid  = isset($_REQUEST["dblAppUid"])?$_REQUEST["dblAppUid"]:'0'; 
				$uidTask = isset($_REQUEST["uidTask"])?$_REQUEST["uidTask"]:''; 
				$registers = isset($_REQUEST["registers"])?$_REQUEST["registers"]:''; 
				$hiddenUids = isset($_REQUEST["hiddenColumn"])?$_REQUEST["hiddenColumn"]:'';
				$idInbox = isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
			    $result  = createCase($appUid,$appData,$uidTask,$registers,$hiddenUids, $idInbox);
		    	echo G::json_encode(array("success" => true, "result" => $result));
			    break;
	case "totalDuplicRec": 
    			$registers 	= isset($_REQUEST["registers"])?$_REQUEST["registers"]:'';
    			$fldNamStat = isset($_REQUEST["fldNamStat"])?$_REQUEST["fldNamStat"]:'';
    			$fldValStat = isset($_REQUEST["fldValStat"])?$_REQUEST["fldValStat"]:'';
                list($total, $caseElected, $existCaseElected) = getTotalDuplicateRecords($registers,$fldNamStat,$fldValStat);
                echo G::json_encode(array("success" => true, "existCaseElected"=> $existCaseElected,"caseElected"=> $caseElected, "total" => $total));
                break;
    case "delCaseDuplic":
				$appUidElected 	 = isset($_REQUEST["appUidElected"])?$_REQUEST["appUidElected"]:'0'; 
				$tableName 	 = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:''; 
			    $numDeletedCases = deleteDuplicatedCases($appUidElected, $tableName);
		    	echo G::json_encode(array("success" => true, "numDelCases" => $numDeletedCases));
			    break;	    
  }

} catch (Exception $e) {
	$err = $e->getMessage();
	$err = preg_replace("[\n|\r|\n\r]", ' ', $err);
	$paging = array ('success' => false, 'total' => 0, 'data' => array(), 'message' => $err);
	echo json_encode ( $paging );
}
function orderMultiDimensionalArray ($toOrderArray, $field, $inverse = false) 
{  
    $position = array();  
    $newRow = array();  
    foreach ($toOrderArray as $key => $row) {  
            $position[$key]  = $row[$field];  
            $newRow[$key] = $row;  
    }  
    if ($inverse) {  
        arsort($position);  
    }  
    else {  
        asort($position);  
    }  

    $returnArray = array();  
    foreach ($position as $key => $pos) {       
        $returnArray[] = $newRow[$key];  
    }  
    return $returnArray;  

}

?>
