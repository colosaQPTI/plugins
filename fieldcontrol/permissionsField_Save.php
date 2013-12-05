<?php
G::LoadClass ( 'case' );
G::LoadClass ( 'pmFunctions' );

function validate_field($newInner)
{
    $newInner = str_replace('  ', ' ', $newInner);
    return $newInner;
}

$res = false;

if (isset ( $_POST ['idRoles'] ) && isset( $_POST ['idInbox']) ) {
	
	$qInboxRoles = executeQuery("SELECT POSITION FROM PMT_INBOX_ROLES WHERE ROL_CODE = '" .mysql_escape_string($_POST ['idRoles']) ."' AND ID_INBOX = '" . $_POST ['idInbox'] ."'");
	if(count($qInboxRoles))
		$positionField = $qInboxRoles[1]['POSITION'];
	
	$delete = executeQuery ( "DELETE FROM PMT_INBOX_FIELDS WHERE ROL_CODE = '" . mysql_escape_string($_POST ['idRoles']) . "' AND  ID_INBOX = '" . $_POST ['idInbox'] . "' " );
	$delete = executeQuery ( "DELETE FROM PMT_INBOX_ROLES WHERE ROL_CODE = '" . mysql_escape_string($_POST ['idRoles']) . "' AND  ID_INBOX = '" . $_POST ['idInbox'] . "' " );
	$delete = executeQuery ( "DELETE FROM PMT_INBOX_FILTERS WHERE ROL_CODE = '" . mysql_escape_string($_POST ['idRoles']) . "' AND  ID_INBOX = '" . $_POST ['idInbox'] . "' " );
	$delete = executeQuery ( "DELETE FROM PMT_INBOX_JOIN WHERE JOIN_ROL_CODE = '" . mysql_escape_string($_POST ['idRoles']) . "' AND  JOIN_ID_INBOX = '" . $_POST ['idInbox'] . "' " );
	$delete = executeQuery ( "DELETE FROM PMT_INBOX_PARENT_TABLE WHERE ROL_CODE = '" . mysql_escape_string($_POST ['idRoles']) . "' AND  ID_INBOX = '" . $_POST ['idInbox'] . "' " );
	
	if(!isset($positionField))
	{
		$qPos = "SELECT max(POSITION) AS POSITION FROM PMT_INBOX_ROLES WHERE ROL_CODE = '" . mysql_escape_string($_POST ['idRoles']) . "' ";
		$position = executeQuery ( $qPos );
		$positionField = $position [1] ['POSITION'];
		$positionField = $positionField + 1;
	}

	$queryId = "SELECT max(ID) AS MAX_ID FROM  PMT_INBOX_ROLES  ";
	$maxId = executeQuery ( $queryId );
	$sgtId = $maxId[1]['MAX_ID'] + 1;
			
	$insert = "INSERT INTO PMT_INBOX_ROLES (ID, ROL_CODE, ID_INBOX, POSITION)
				VALUES ('" . $sgtId . "', '" . mysql_escape_string($_POST ['idRoles']) . "', '" . $_POST ['idInbox'] . "', '" . $positionField ."')";
	executeQuery ( $insert );
	
	if(isset($_POST ['idTable']) && $_POST ['idTable'] != '')
	{
		$queryId = "SELECT max(ID) AS MAX_ID FROM  PMT_INBOX_PARENT_TABLE  ";
		$maxId = executeQuery ( $queryId );
		$sgtId = $maxId[1]['MAX_ID'] + 1;
		$insert = "INSERT INTO PMT_INBOX_PARENT_TABLE (ID, ID_TABLE, ID_INBOX, ROL_CODE)
					VALUES ('" . $sgtId . "', '" . $_POST ['idTable'] . "', '" . $_POST ['idInbox'] . "', '" . mysql_escape_string($_POST ['idRoles']) . "')";
		executeQuery ( $insert );
	}
}

$data = json_decode ( $_POST ['myArray'] );
foreach ( $data as $name => $value ) 
{
	$idTable = $value->idTable;
	$idRoles = $value->idRoles;
	$innerJoin = $value->innerJoin;
	$idField = $value->idField;
	$fieldChange = $value->fieldReplace;
	$idInbox = $value->idInbox;
	$descripField = $value->descripField;
	$hiddenField = $value->hiddenField;
	$filterField = $value->filterField;
	$aliasTable = $value->aliasTable;
	$orderBy = $value->orderBy;
	
	$queryPos = "SELECT max(POSITION) AS POSITION FROM  PMT_INBOX_FIELDS WHERE ROL_CODE = '" . mysql_escape_string($idRoles) . "'  AND  ID_INBOX = '" . $_POST ['idInbox'] . "' ";
	$position = executeQuery ( $queryPos );
	$positionField = $position [1] ['POSITION'];
	$positionField = $positionField + 1;
		
	if($innerJoin != '')
		$innerJoin = validate_field($innerJoin);

	$queryId = "SELECT max(ID) AS MAX_ID FROM  PMT_INBOX_FIELDS  ";
	$maxId = executeQuery ( $queryId );
	$sgtId = $maxId[1]['MAX_ID'] + 1;
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
					'" . $sgtId . "',
					'" . $idField . "',
					'" . mysql_escape_string($idRoles) . "',
					'" . $descripField . "',
					'1',
					'" . $positionField . "',
					'" . $idField . "',
					'" . $idTable . "',
					'" . $fieldChange . "',
					'" . $idInbox . "',
					'" . $hiddenField . "',
					'" . $filterField . "',
					'" . $aliasTable . "',
					'" . $orderBy . "'
				)
    	
		";
	executeQuery ( $insert );
		
	if($fieldChange != '')
	{
		$update = "UPDATE  PMT_INBOX_FIELDS  SET
				   FIELD_NAME = '" . $fieldChange . "'
				   WHERE FLD_UID = '" . $idField . "' AND ROL_CODE = '" . mysql_escape_string($idRoles) . "'
					   ";
		executeQuery($update);
	}
		
	if($filterField == 1)
	{	
		$insert = "INSERT INTO PMT_INBOX_FILTERS (  
						ROL_CODE,
						ID_INBOX,
						ID_TABLE,
						FIELD_NAME,
						DESCRIPTION_FILTER
					)
					VALUES (
						'" . mysql_escape_string($_POST ['idRoles']) . "',
						'" . $_POST ['idInbox'] . "',
						'" . $idTable . "',
						'" . $idField . "',
						'" . $descripField . "'
					)";
		executeQuery ( $insert );
	}
	if($innerJoin != '')
	{	
		$insert = "INSERT INTO PMT_INBOX_JOIN (  
						JOIN_QUERY,
						JOIN_ID_INBOX,
						JOIN_ROL_CODE
					)
					VALUES (
						'" . $innerJoin . "',
						'" . $_POST ['idInbox'] . "',
						'" . mysql_escape_string($_POST ['idRoles']) . "'
					)";
		executeQuery ( $insert );
	}	
	$res = true;
}

$save = array ('success' => $res );
echo json_encode ( $save );

?>
