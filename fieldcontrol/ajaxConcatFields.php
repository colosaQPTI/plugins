<?php

G::LoadClass ( 'case' );
G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );

$start = isset ( $_POST ['start'] ) ? $_POST ['start'] : 0;
$limit = isset ( $_POST ['limit'] ) ? $_POST ['limit'] : 500;

if (isset ( $_POST ['sort'] )) {
	if ($_POST ['sort'] != '') {
		$typeOrder = "";
		if (isset ( $_POST ['dir'] ) && $_POST ['dir'] != '') {
			$typeOrder = $_POST ['dir'];
		}
		$OrderBy = " ORDER BY " . $_POST ['sort'] . "  " . $typeOrder . " ";
	}
}
$array = Array ();
$ROL_UID = $_GET ['rolID'];
$innerJoin = '';
$j = 0;
$total = 0;
if (isset ( $_REQUEST ['idTable'] ) && $_REQUEST ['idTable'] != '') 
{
	$select = "SELECT * FROM PMT_INBOX_FIELDS_SELECT 
			   WHERE ROL_CODE = '".mysql_escape_string($_REQUEST ['rolID'])."' AND ID_INBOX = '".$_REQUEST ['idInboxData']."' AND (TYPE IS NULL OR TYPE = '')";
	$dataSelect = executeQuery($select);
	$total = sizeof($dataSelect);
	foreach($dataSelect as $row)
	{
		$array[] = $row;
	}

}
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'verify') 
{		
	 $select = "SELECT ID, FIELD_NAME, count( FIELD_NAME ) AS count FROM PMT_INBOX_FIELDS
				WHERE ROL_CODE = '".mysql_escape_string($_REQUEST ['rolID'])."' AND ID_INBOX = '".$_REQUEST ['actionInbox_id']."' AND  ID_TABLE != ''
				GROUP BY FIELD_NAME
				HAVING count >1
				ORDER BY id ASC";
	$dataVerify = executeQuery($select);
	$total = sizeof($dataVerify);
	$i = 0;
	if(sizeof($dataVerify))
	{
		foreach($dataVerify as $row)
		{
			$select = "SELECT ID, FIELD_NAME, ID_TABLE, ALIAS_TABLE FROM PMT_INBOX_FIELDS
			   WHERE ROL_CODE = '".mysql_escape_string($_REQUEST ['rolID'])."' AND ID_INBOX = '".$_REQUEST ['actionInbox_id']."' AND  FIELD_NAME = '".$row['FIELD_NAME']."'
			  ";
			$dataSelect = executeQuery($select);
		
			$selectQuery = "SELECT FIELD_NAME FROM PMT_INBOX_FIELDS_SELECT
			   WHERE ROL_CODE = '".mysql_escape_string($_REQUEST ['rolID'])."' AND ID_INBOX = '".$_REQUEST ['actionInbox_id']."' AND  FIELDS = '".$row['FIELD_NAME']."'
			  ";
			$dataSelectQuery = executeQuery($selectQuery);
			if(sizeof($dataSelectQuery) == 0)
			{
				foreach($dataSelect as $index)
				{
					$array[] = $index;
					$i++;
				}
			}
		}
		$total = $i;
	}
	else 
	{
		$delete = "DELETE FROM PMT_INBOX_FIELDS_SELECT WHERE 
			ROL_CODE = '".mysql_escape_string($_REQUEST ['rolID'])."' AND ID_INBOX = '".$_REQUEST ['actionInbox_id']."' AND  TYPE = 'Yes' ";
		executeQuery($delete);
		
		
		$data = json_decode ( $_REQUEST ['dataVerify'] );
		foreach ( $data as $name => $value ) 
		{
			$idRoles = $value->idRoles;
			$idField = $value->idField;
			$idInbox = $value->idInbox;
			$selectQuery = "SELECT * FROM PMT_INBOX_FIELDS_SELECT WHERE ROL_CODE = '".mysql_escape_string($_REQUEST ['rolID'])."' 
							AND ID_INBOX = '".$_REQUEST ['actionInbox_id']."' AND  TYPE = 'Yes' AND FLD_UID = '".$idField."' ";
			$dataSelect = executeQuery($selectQuery);
			if(sizeof($dataSelect) == 0)
			{
				$deleteInbox = "DELETE FROM PMT_INBOX_FIELDS WHERE 
							ROL_CODE = '".mysql_escape_string($_REQUEST ['rolID'])."' AND ID_INBOX = '".$_REQUEST ['actionInbox_id']."' AND ALIAS_TABLE = '' 
							AND  ID_TABLE = '' AND FIELD_NAME = '".$idField."' ";
				executeQuery($delete);
			}
		}
		
	}
	
}

$paging = array ('success' => true, 'total' => $total, 'data' => array_splice ( $array, $start, $limit ), 'response' => 'OK' );
echo json_encode ( $paging );

?>

    