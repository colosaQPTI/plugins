<?php
G::LoadClass ( 'case' );
G::LoadClass ( 'pmFunctions' );
$form=$_POST;
$res=false;

function removeSelectQueryInbox($id){
	$ret = array();
	
	$selectInbox = "SELECT ALIAS_ID_INBOX FROM PMT_INBOX_FIELDS_SELECT 
					WHERE ID = '$id' ";
	$idAliasInbox = executeQuery($selectInbox);
	
	$sql = "DELETE FROM PMT_INBOX_FIELDS_SELECT WHERE ID = " . $id;
	executeQuery($sql);
	
	$sqlInbox = "DELETE FROM PMT_INBOX_FIELDS WHERE ID_TABLE = '' AND ID = " . $idAliasInbox[1]['ALIAS_ID_INBOX'] ;
	executeQuery($sqlInbox); 
	
	header("Content-Type: text/html");
	$returnStatus = array('success' => true,'Msg'=>'Succesfully');

	echo G::json_encode($returnStatus);

}

function editSelectQueryInbox($idSelect,$data,$dataFields, $rolID, $idInbox)
{
	$newFielData = explode('.',$data);
	$search  = array(',', ' ', ')', '(');
	$replace = array(' ', ' ', ' ', ' ');
	$i = 0;
	$dataFieldBac = $dataFields;
	$dataFields = '';
	foreach($newFielData as $index => $value)
	{
		if($i != 0)
		{
			$subject = $value;
			$newFields = str_replace($search, $replace, $subject);
			$newFields = explode(' ',$newFields);
			$j= 0;
			foreach($newFields as $row => $dataField)
			{
				$dataString = trim($dataField);
				break;
			}
			if($i == 1)
				$dataFields = $dataString;
			else
				$dataFields = $dataFields.','.$dataString;
		}
		$i++;
	}
	$newFielData = explode('AS',$data);
	if(sizeof($newFielData) <= 1)
	{
		$newFielData = explode('as',$data);
		if(sizeof($newFielData) <= 1)
		{
			$newFielData = explode(',',$dataFields);
			$aliasSelect = '';
			$contField = 1;
			if(sizeof($newFielData) == 1)
				$aliasSelect = $dataFields;
			else
			{
				foreach($newFielData as $index => $value)
				{
					if(sizeof($newFielData) == $contField)
						$aliasSelect = $aliasSelect.$value;
					else 
						$aliasSelect = $value.'_'.$aliasSelect;
					$contField++;
				}
			}
			$newField = $aliasSelect;
			$data = $data.' AS '.$newField;
		}
		else 
		{
			$newField = trim($newFielData[1]);
		}
	}
	else
	{
		$newField = trim($newFielData[1]);
	}
	
	$updateSelect="UPDATE PMT_INBOX_FIELDS_SELECT SET
			FIELDS = '$dataFields',
			ROL_CODE = '".mysql_escape_string($rolID)."',
			ID_INBOX = '$idInbox',
			QUERY_SELECT = '$data',
			FIELD_NAME = '$newField'
			WHERE ROL_CODE = '".mysql_escape_string($rolID)."'
			AND ID_INBOX = '$idInbox'
			AND ID = '$idSelect'
			AND TYPE != 'Yes'
			";

	executeQuery($updateSelect);

	$selectInbox = "SELECT ALIAS_ID_INBOX FROM PMT_INBOX_FIELDS_SELECT 
					WHERE ROL_CODE = '".mysql_escape_string($rolID)."' AND ID_INBOX = '$idInbox' AND ID = '$idSelect' AND ID_INBOX = '$idInbox' AND TYPE != 'Yes' ";
	$idAliasInbox = executeQuery($selectInbox);
	if(sizeof($idAliasInbox))
	{
		$updateInbox="UPDATE PMT_INBOX_FIELDS SET
			FLD_UID = '$newField',
			ROL_CODE = '".mysql_escape_string($rolID)."',
			ID_INBOX = '$idInbox',
			DESCRIPTION = '$newField',
			FIELD_NAME = '$newField'
			WHERE ROL_CODE = '".mysql_escape_string($rolID)."' 
			AND ID_INBOX = '$idInbox'
			AND ID = '".$idAliasInbox[1]['ALIAS_ID_INBOX']."'
			";

		    executeQuery($updateInbox);
	}
		    $res=true;	    
		    
	header("Content-Type: text/html");
	$returnStatus = array('success' => $res);

	echo G::json_encode($returnStatus);	
}


function addSelectQueryInbox($data,$dataFields, $rolID, $idInbox, $idFieldTable)
{
	$newFielData = explode('.',$data);
	$search  = array(',', ' ', ')', '(');
	$replace = array(' ', ' ', ' ', ' ');
	$i = 0;
	$dataFieldBac = $dataFields;
	$dataFields = '';
	foreach($newFielData as $index => $value)
	{
		if($i != 0)
		{
			$subject = $value;
			$newFields = str_replace($search, $replace, $subject);
			$newFields = explode(' ',$newFields);
			$j= 0;
			foreach($newFields as $row => $dataField)
			{
				$dataString = trim($dataField);
				break;
			}
			if($i == 1)
				$dataFields = $dataString;
			else
				$dataFields = $dataFields.','.$dataString;
		}
		$i++;
	}
	
	$newFielData = explode('AS',$data);
	if(sizeof($newFielData) <= 1)
	{
		$newFielData = explode('as',$data);
		if(sizeof($newFielData) <= 1)
		{
			$newFielData = explode(',',$dataFields);
			$aliasSelect = '';
			$contField = 1;
			if(sizeof($newFielData) == 1)
				$aliasSelect = $dataFields;
			else
			{
				foreach($newFielData as $index => $value)
				{
					if(sizeof($newFielData) == $contField)
						$aliasSelect = $aliasSelect.$value;
					else 
						$aliasSelect = $value.'_'.$aliasSelect;
					$contField++;
				}
			}
			$newField = $aliasSelect;
			$data = $data.' AS '.$newField;
		}
		else 
		{
			$newField = trim($newFielData[1]);
		}
	}
	else
	{
		$newField = trim($newFielData[1]);
	}
			
	$queryPos = "SELECT max(POSITION) AS POSITION FROM  PMT_INBOX_FIELDS WHERE ROL_CODE = '".mysql_escape_string($rolID)."'  AND  ID_INBOX = '" . $idInbox . "' ";
	$position = executeQuery ( $queryPos );
	$positionField = $position [1] ['POSITION'];
	$positionField = $positionField + 1;
	
	$queryId = "SELECT max(ID) AS MAX_ID FROM  PMT_INBOX_FIELDS  ";
	$maxId = executeQuery ( $queryId );
	$sgtIdIn = $maxId[1]['MAX_ID'] + 1;
	
	if($idFieldTable != 0)
	{
		$insert = "INSERT INTO PMT_INBOX_FIELDS (  
					ID,
					FLD_UID,
					ROL_CODE,
					DESCRIPTION,
					INCLUDE_OPTION,
					POSITION,
					FIELD_NAME,
					ID_TABLE,
					FIELD_REPLACE,
					ID_INBOX,
					HIDDEN_FIELD,
					INCLUDE_FILTER,
					ALIAS_TABLE,
					ORDER_BY
				)
				VALUES (
					'" . $sgtIdIn ."',
					'" . $newField . "',
					'" . mysql_escape_string($rolID) . "',
					'" . $newField . "',
					'1',
					'" . $positionField . "',
					'" . $newField . "',
					'',
					'',
					'" . $idInbox . "',
					'0',
					'0',
					'',
					''
				)
    	
			";
		executeQuery ( $insert );
	}
	$queryId = "SELECT max(ID) AS MAX_ID FROM  PMT_INBOX_FIELDS_SELECT  ";
	$maxId = executeQuery ( $queryId );
	$sgtId = $maxId[1]['MAX_ID'] + 1;
		
	
	$selectID = "SELECT ID FROM PMT_INBOX_FIELDS 
					WHERE FLD_UID = '".$newField."' AND  ROL_CODE = '".mysql_escape_string($rolID)."' AND FIELD_NAME = '".$newField."' AND ID_INBOX = '".$idInbox."'  ";
	$dataId = 	executeQuery($selectID);
	$type = '';
	if($idFieldTable != 0)
	{
		$type = 'Yes';
	}
	$queryItemFile="INSERT INTO PMT_INBOX_FIELDS_SELECT (
				ID, 
				FLD_UID, 
				FIELDS, 
				ROL_CODE, 
				ID_INBOX, 
				QUERY_SELECT, 
				FIELD_NAME , 
				ALIAS_ID_INBOX,
				TYPE 
			)
			VALUES (
				'". $sgtId ."',
				'". $newField ."',
				'". $dataFields ."',
				'". mysql_escape_string($rolID) ."',
				'". $idInbox ."',
				'". $data ."',
				'". $newField ."', 
				'". $dataId[1]['ID'] ."',
				'". $type ."'
			)";
	executeQuery($queryItemFile);
	
	$newFielData = $newFielData[0];
	$newFielDataTable = explode('.',$newFielData);

	if($idFieldTable != 0)
	{
		$update = "UPDATE PMT_INBOX_FIELDS SET INCLUDE_OPTION = '0'
						WHERE ID = '".$idFieldTable."' ";
		executeQuery($update);
	}

	$res = true;
	$save = array ('success' => $res );
	echo json_encode ( $save );
}

header('Content-type:text/javascript;charset=UTF-8');
$method = $_GET["method"];
switch ($method) {
	case "remove":
		$ret = removeSelectQueryInbox($_POST["ID"]);
	break;
	
	case "add":
		$dataParameters = $_POST ['parameters'] ;
		$dataParameters = str_replace("'", '"', $dataParameters);
		if(isset($_POST ['rolID']) && $_POST ['rolID']!='')
		{
			$delQuery = "DELETE FROM PMT_INBOX_FIELDS_SELECT WHERE ROL_CODE = '" . mysql_escape_string($_POST ['rolID']) . "' AND ID_INBOX = '" . $_POST ['idInbox'] ."' AND QUERY_SELECT = '" . $dataParameters . "' ";
			$delete = executeQuery ($delQuery);
		}
		
		$dataParameters = $_POST ['parameters'] ;
		$dataParameters = str_replace("'", '"', $dataParameters);
		$dataFields =  $_POST ['fields'];
		$idFieldTable = 0;
		if(isset($_POST ['idFieldTable']) && $_POST ['idFieldTable'] != '')
			$idFieldTable = $_POST ['idFieldTable'];
		$ret = addSelectQueryInbox($dataParameters,$dataFields, $_POST['rolID'],  $_POST ['idInbox'],  $idFieldTable);
		break;
		
	case "edit":
		if(isset($_GET['ID']) && $_GET['ID']!='')
		{	
			$dataParameters = $_POST ['parameters'] ;
			$dataParameters = str_replace("'", '"', $dataParameters);
			$dataFields =  $_POST ['fields'];
		
			$ret = editSelectQueryInbox($_GET['ID'],$dataParameters,$dataFields, $_POST['rolID'],  $_POST ['idInbox']);
	
		}
		break;
	
}
?>
