<?php
G::LoadClass ( 'case' );
G::LoadClass ( 'pmFunctions' );
$form=$_POST;
$res=false;

function removeActionInbox($id){
	$ret = array();
	$sql = "DELETE FROM PMT_INBOX_ACTIONS WHERE ID = " . $id;
	executeQuery($sql);
	
	header("Content-Type: text/html");
	$returnStatus = array('success' => true,'Msg'=>'Succesfully');

	echo G::json_encode($returnStatus);

}
function addActionInbox($idInbox,$idAction,$nameAction,$pmFunction,$parametersFunction,$rolID,$sentParameters,$positionField)
{
	$queryItemFile="INSERT INTO PMT_INBOX_ACTIONS (ID_INBOX,ID_ACTION,NAME_ACTION,ID_PM_FUNCTION,PARAMETERS_FUNCTION,ROL_CODE,SENT_FUNCTION_PARAMETERS,POSITION)
			VALUES (
				'$idInbox',
				'$idAction',
				'$nameAction',
				'$pmFunction',
				'$parametersFunction',
				'".mysql_escape_string($rolID)."',
				'$sentParameters',
				'$positionField'
			)";
	executeQuery($queryItemFile);
	$res=true;	    
		    
	header("Content-Type: text/html");
	$returnStatus = array('success' => $res);

	echo G::json_encode($returnStatus);
}
function editActionInbox($idInbox,$idAction,$nameAction,$pmFunction,$parametersFunction,$rolID,$actionInboxID,$sentParameters)
{
	$queryItemFile="UPDATE PMT_INBOX_ACTIONS SET
			ID_ACTION = '$idAction',
			NAME_ACTION = '$nameAction',
			ID_PM_FUNCTION = '$pmFunction',
			PARAMETERS_FUNCTION = '$parametersFunction',
			SENT_FUNCTION_PARAMETERS = '$sentParameters'
			WHERE ROL_CODE = '".mysql_escape_string($rolID)."'
			AND ID_INBOX = '$idInbox'
			AND ID = '$actionInboxID'";

	executeQuery($queryItemFile);
	$res=true;	    

	header("Content-Type: text/html");
	$returnStatus = array('success' => $res);

	echo G::json_encode($returnStatus);	
}
function addDragDropActions($data,$rolID)
{
	foreach ( $data as $name => $value ) 
		{
			$idInbox = $value->idInbox;
			$idAction = $value->idAction;
			$nameAction = $value->name;
			$pmFunction = $value->pmFunction;
			$parametersFunction = $value->parametersFunction;
			$sentParameters = $value->sentFunctionParameters;
			
			$queryPos = "SELECT max(POSITION) AS POSITION FROM PMT_INBOX_ACTIONS WHERE ROL_CODE = '" . mysql_escape_string($rolID) . "' AND ID_INBOX = '" . $idInbox ."'";
			$position = executeQuery ( $queryPos );
			$positionField = $position [1] ['POSITION'];
			$positionField = $positionField + 1;

			$queryItemFile="INSERT INTO PMT_INBOX_ACTIONS (ID_INBOX,ID_ACTION,NAME_ACTION,ID_PM_FUNCTION,PARAMETERS_FUNCTION,ROL_CODE,SENT_FUNCTION_PARAMETERS,POSITION)
				VALUES (
					'$idInbox',
					'$idAction',
					'$nameAction',
					'$pmFunction',
					'$parametersFunction',
					'".mysql_escape_string($rolID)."',
					'$sentParameters',
					'$positionField'
				)";
	    	executeQuery($queryItemFile);
		    		
			$res = true;
		
		}
		$save = array ('success' => $res );
		echo json_encode ( $save );
}

function addConditionByFields($data,$rolID)
{
	foreach ( $data as $name => $value ) 
		{
			$idInbox = $value->idInbox;
			$idAction = $value->idAction;
			$nameAction = $value->nameAction;
			$idfield = $value->idfield;
			$parameterField = $value->parameterField;
			$parameterField = str_replace("'", "\"",$parameterField );  
			$idTable = $value->idTable;
			$operator = $value->operator;
			if($operator == '=')
			{
				$operator = '==';
			}
			if($operator == 'IN')
			{
				$newparameterField = str_replace('(', '', $parameterField);
				$newparameterField = str_replace(')', '', $newparameterField);
				$parameterField = $newparameterField;
			}
			$queryItemFile="INSERT INTO PMT_CONDITION_BY_FIELDS (FLD_UID, ID_TABLE, ROL_CODE, ID_INBOX, ID_ACTION, NAME_ACTION,PARAMETERS_BY_FIELD, OPERATOR)
			VALUES (
			'$idfield',
			'$idTable',
			'".mysql_escape_string($rolID)."',
			'$idInbox',
			'$idAction',
			'$nameAction',
			'$parameterField',
			'$operator'
			)";
		    executeQuery($queryItemFile);
		    		
			$res = true;
		
		}
		$save = array ('success' => $res );
		echo json_encode ( $save );
}

header('Content-type:text/javascript;charset=UTF-8');
$method = $_GET["method"];
switch ($method) {
	case "remove":
		$ret = removeActionInbox($_POST["ID"]);
	break;
	
	case "add":
		if(isset($_GET['ID']) && $_GET['ID']!='')
		{							
			$select = "SELECT NAME, DESCRIPTION, PM_FUNCTION, PARAMETERS_FUNCTION 
						FROM PMT_ACTIONS WHERE ID = '".$form['idAction']."'";
			
			$querySelect = executeQuery($select);
			if (sizeof($querySelect) > 0)
			{
				$nameAction = $querySelect[1]['NAME'];
				$pmFunction = $querySelect[1]['PM_FUNCTION'];
			}
			$queryPos = "SELECT max(POSITION) AS POSITION FROM PMT_INBOX_ACTIONS WHERE ROL_CODE = '" . mysql_escape_string($form ['rolID']) . "' 
			AND  ID_INBOX = '" . $form ['idInbox'] . "' ";
			$position = executeQuery ( $queryPos );
			$positionField = $position [1] ['POSITION'];
			$positionField = $positionField + 1;
			
			$ret = addActionInbox($_GET['ID'],$form['idAction'],$nameAction,$pmFunction,$form['parameters'],$form['rolID'],$form['sentParameters'],$positionField);
	
		}
		break;
		
	case "edit":
		if(isset($_GET['ID']) && $_GET['ID']!='')
		{							
			$select = "SELECT NAME, DESCRIPTION, PM_FUNCTION, PARAMETERS_FUNCTION 
						FROM PMT_ACTIONS WHERE ID = '".$form['idAction']."'";
			
				$querySelect = executeQuery($select);
				if (sizeof($querySelect) > 0)
				{
					$nameAction = $querySelect[1]['NAME'];
					$pmFunction = $querySelect[1]['PM_FUNCTION'];
				}
			$ret = editActionInbox($_GET['ID'],$form['idAction'],$nameAction,$pmFunction,$form['parameters'],$form['rolID'],$form['actionInboxID'],$form['sentParameters']);
	
		}
		break;
		
	case "dragdrop":
		
		if(isset($_GET['ID']) && $_GET['ID']!='')
		{
			$delQuery = "DELETE FROM PMT_INBOX_ACTIONS WHERE ROL_CODE = '" . mysql_escape_string($form ['rolID']) . "' AND ID_INBOX = '" . $form ['idInbox'] ."'  ";
			$delete = executeQuery ($delQuery);
		}
		$data = json_decode ( $_POST ['arrayActionsInbox'] );
		$ret = addDragDropActions($data,$form['rolID']);
		break;
	
	case "fieldAction":
		
		if(isset($_GET['ID']) && $_GET['ID']!='')
		{
			$delQuery = "DELETE FROM PMT_CONDITION_BY_FIELDS WHERE ROL_CODE = '" . mysql_escape_string($form ['rolID']) . "' AND ID_INBOX = '" . $form ['idInbox'] ."' AND ID_ACTION = '" . $form ['idAction'] . "' ";
			$delete = executeQuery ($delQuery);
		}
		$data = json_decode ( $_POST ['arrayFieldsAction'] );
		
		$ret = addConditionByFields($data,$form['rolID']);
		break;
}
?>
