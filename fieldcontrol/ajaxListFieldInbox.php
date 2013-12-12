<?php

G::LoadClass ( 'case' );
G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );

$start = isset ( $_POST ['start'] ) ? $_POST ['start'] : 0;
$limit = isset ( $_POST ['limit'] ) ? $_POST ['limit'] : 500;
$OrderBy = "ORDER BY FLD_DESCRIPTION ASC";
if (isset ( $_POST ['sort'] )) 
{
	if ($_POST ['sort'] != '') {
		$typeOrder = "";
		if (isset ( $_POST ['dir'] ) && $_POST ['dir'] != '') {
			$typeOrder = $_POST ['dir'];
		}
		$OrderBy = " ORDER BY " . $_POST ['sort'] . "  " . $typeOrder . " ";
	}
}
$array = Array ();
$ROL_UID = isset ($_GET ['rolID']) ? $_GET ['rolID'] : '';
$innerJoin = '';
$j = 0;

$total = 0;
if (isset ( $_REQUEST ['idInboxData'] ) && $_REQUEST ['idInboxData'] != '') 
{
	$query = "SELECT * FROM PMT_INBOX_FIELDS 
				  WHERE ROL_CODE  = '" . mysql_escape_string($_GET ['rolID']) . "' AND  ID_INBOX = '".$_REQUEST['idInboxData']."' AND ID_TABLE != ''";
	$newOptions = executeQuery ( $query );
	$total = sizeof($newOptions);
	if (sizeof ( $newOptions )) 
	{
		foreach($newOptions as $index )
		{
			if (isset ( $index ['DESCRIPTION'] ) && $index ['DESCRIPTION'] != '')
				$valor ['FLD_DESCRIPTION'] = $index ['DESCRIPTION'];
			if(isset ( $index ['ID_INBOX'] ) && $index ['ID_INBOX'] != '')
				$valor['ID_INBOX'] = $newOptions[1]['ID_INBOX'];	
			if (isset ( $index ['HIDDEN_FIELD'] ) && $index ['HIDDEN_FIELD'] == 1)
				$valor['HIDDEN_FIELD'] = true;
			else 
				$valor['HIDDEN_FIELD'] = false;
			if (isset ( $index ['INCLUDE_FILTER'] ) && $index ['INCLUDE_FILTER'] == 1)
				$valor['INCLUDE_FILTER'] = true;
			else 
				$valor['INCLUDE_FILTER'] = false;
			
			$valor['ADD_TAB_NAME'] = $index['ID_TABLE'];
			$valor['ALIAS_TABLE'] = $index['ALIAS_TABLE'];
			$swPos = 1;		
			$valor ['ROL_CODE'] = $_GET ['rolID'];
			$valor ['FIELD_NAME'] = $index['FLD_UID'];
			$valor ['FLD_UID'] = $index['FLD_UID'];
			$array[] = $valor;
		} 
	}
}

if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'gral') 
{
	$query = "SELECT * FROM PMT_INBOX_FIELDS 
				  WHERE ROL_CODE  = '" . mysql_escape_string($_GET ['rolID']) . "' AND  ID_INBOX = '".$_REQUEST['idInboxData']."' AND ID_TABLE != ''";
	$newOptions = executeQuery ( $query );
	$total = sizeof($newOptions);
	if (sizeof ( $newOptions )) 
	{
		foreach($newOptions as $index )
		{
			if (isset ( $index ['DESCRIPTION'] ) && $index ['DESCRIPTION'] != '')
				$valor ['FLD_DESCRIPTION'] = $index ['DESCRIPTION'];
			if(isset ( $index ['ID_INBOX'] ) && $index ['ID_INBOX'] != '')
				$valor['ID_INBOX'] = $newOptions[1]['ID_INBOX'];	
			if (isset ( $index ['HIDDEN_FIELD'] ) && $index ['HIDDEN_FIELD'] == 1)
				$valor['HIDDEN_FIELD'] = true;
			else 
				$valor['HIDDEN_FIELD'] = false;
			if (isset ( $index ['INCLUDE_FILTER'] ) && $index ['INCLUDE_FILTER'] == 1)
				$valor['INCLUDE_FILTER'] = true;
			else 
				$valor['INCLUDE_FILTER'] = false;
			
			$valor['ADD_TAB_NAME'] = $index['ID_TABLE'];
			$valor['ALIAS_TABLE'] = $index['ALIAS_TABLE'];
			$swPos = 1;		
			$valor ['ROL_CODE'] = $_GET ['rolID'];
			$valor ['FIELD_NAME'] = $index['FLD_UID'];
			$valor ['FLD_UID'] = $index['FLD_UID'];
			$array[] = $valor;
		} 
	}
}
$paging = array ('success' => true, 'total' => $total, 'data' => array_splice ( $array, $start, $limit ), 'response' => 'OK' );
echo json_encode ( $paging );
?>

    