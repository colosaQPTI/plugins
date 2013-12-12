<?php
G::LoadInclude('ajax');
G::LoadClass ( 'case' );
G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );
if (isset($_POST['rolID']))
{
	$_POST['rolID'] = $_POST['rolID'];
}
else
	$_POST['rolID'] = $_REQUEST['rolID'];
if(isset($_REQUEST['action'])){
	$value = get_ajax_value('action');
}
$_POST['sw_pm_inbox'] = isset($_POST['sw_pm_inbox'])? $_POST['sw_pm_inbox']: 0;
switch ($value)
{
	case 'RelationList':
		
  	$sQuery = " SELECT 
		IP.ID AS ID,
		IP.ID_INBOX AS ID_INBOX,
		I.DESCRIPTION AS INBOX_DESCRIPTION,
		IP.DESCRIPTION AS DESCRIPTION_RELATION
		FROM PMT_INBOX_ROLES AS IP
		INNER JOIN PMT_INBOX AS I ON I.INBOX = IP.ID_INBOX
		WHERE IP.ROL_CODE = '".mysql_escape_string($_POST['rolID'])."' 
		ORDER BY POSITION";
  	
  	$aDatos = executeQuery ($sQuery);
	$array = Array();
	foreach($aDatos as $valor)
	{        
	$array[] = $valor;
	}
	$total = count($aDatos);
	
	$result = new StdClass();
	$result->success = true;
	$result->data = $array;
	$result->total = $total;

    echo G::json_encode($result);

    break;
		
	case 'saveNewRelation':
  	
		$queryPos = "SELECT max(POSITION) AS POSITION FROM PMT_INBOX_ROLES WHERE ROL_CODE = '" . mysql_escape_string($_POST ['rolID']) . "'	";
		$position = executeQuery ( $queryPos );
		$positionField = $position [1] ['POSITION'];
		$positionField = $positionField + 1;
		
		$insert = "INSERT INTO  PMT_INBOX_ROLES (  
					ROL_CODE,
					ID_INBOX,
					DESCRIPTION,
					POSITION
				)
				VALUES (
					'" . mysql_escape_string($_POST ['rolID']) . "',
					'" . $_POST ['idInbox'] . "',
					'" . $_POST ['name'] . "',
					'" . $positionField ."'
				)";
		executeQuery ( $insert );
    	echo '{success: true}';
    break;
    
    case 'deleteRelation':
    	
    	$delInboxPermission = "DELETE FROM PMT_INBOX_ROLES
    	WHERE ID_INBOX = '".$_POST['name']."' AND ROL_CODE = '".mysql_escape_string($_POST ['rolID'])."' " ;
    	executeQuery ( $delInboxPermission );
    	
    	$delInboxAction = "DELETE FROM PMT_INBOX_ACTIONS 
    	WHERE ID_INBOX = '".$_POST['name']."' AND ROL_CODE = '".mysql_escape_string($_POST['rolID'])."'";
    	executeQuery($delInboxAction);
    	
    	$delPermissionOption = "DELETE FROM PMT_FIELDS_INBOX
    	WHERE ID_INBOX = '".$_POST['name']."' AND ROL_CODE = '".mysql_escape_string($_POST ['rolID'])."' " ;
    	executeQuery ( $delPermissionOption );
    	
    echo '{success: true}';
    break;
    
    case 'saveDragDropRelation':
		if (isset ( $_POST ['rolID'] )) {
			$delQuery = "DELETE FROM PMT_INBOX_ROLES WHERE ROL_CODE = '" . mysql_escape_string($_POST ['rolID']) . "'  ";
			$delete = executeQuery ($delQuery);
		}
		
		// INSERT IN PMT_PM_INBOX_ROLES (SHOW - HIDDEN INBOX PM)
		if (isset ( $_POST ['rolID'] )) {
			
			$query = "SELECT PM_ID FROM PMT_PM_INBOX_ROLES WHERE PM_ROL_CODE = '". mysql_escape_string($_POST ['rolID']) ."' ";
			$query1 = executeQuery($query);
			
			$pmId = $query1[1]['PM_ID'];
			
			if($pmId){ 
				
				$updatepm = "UPDATE PMT_PM_INBOX_ROLES SET PM_SW_INBOX = '" .$_POST ['sw_pm_inbox']."' WHERE PM_ROL_CODE = '". mysql_escape_string($_POST ['rolID']) ."' AND PM_ID = '". $pmId ."' ";
				executeQuery($updatepm);
			}
			else{
				$insertpm = "INSERT INTO PMT_PM_INBOX_ROLES (
				PM_ROL_CODE,
				PM_SW_INBOX ) 
				VALUES
				('" . mysql_escape_string($_POST ['rolID']) . "',
				 '" . $_POST ['sw_pm_inbox'] . "')";
				executeQuery($insertpm);
			}
		}
		// END INSERT PMT_PM_INBOX_ROLES
		
		$data = json_decode ( $_POST ['arrayRelation'] );
		
		
		foreach ( $data as $name => $value ) 
		{
			$idRoles = $value->idRoles;
			$idInbox = $value->idInbox;
			$queryPos = "SELECT max(POSITION) AS POSITION FROM PMT_INBOX_ROLES WHERE ROL_CODE = '" . mysql_escape_string($_POST ['rolID']) . "' ";
			$position = executeQuery ( $queryPos );
			$positionField = $position [1] ['POSITION'];
			$positionField = $positionField + 1;
							
			$insert = "INSERT INTO PMT_INBOX_ROLES (  
					ROL_CODE,
					ID_INBOX,
					DESCRIPTION,
					POSITION
				)
				VALUES (
					'" . mysql_escape_string($idRoles) . "',
					'" . $idInbox . "',
					'',
					'" . $positionField ."'
				)";
			executeQuery ( $insert );
			$res = true;
		}
		
		$save = array ('success' => $res );
		echo json_encode ( $save );
	break;
}
 