<?php
ini_set ( 'error_reporting', E_ALL );
ini_set ( 'display_errors', True );
G::LoadClass ( 'case' );
G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );

$start = isset ( $_POST ['start'] ) ? $_POST ['start'] : 0;
$limit = isset ( $_POST ['limit'] ) ? $_POST ['limit'] : 1000;
   
$array = Array ();

if (isset ( $_REQUEST ['action'] ) && $_REQUEST ['action'] == 'listField' ) 
{
	$sQueryT = " SELECT ID_LABEl ID, NAME_LABEL NAME, DESCRIPTION_EN, DESCRIPTION_FR
  				FROM PMT_CUSTOMIZE_LABEL
  				
    		   	";
    		   	
	$selectT = executeQuery($sQueryT);
	
	foreach ( $selectT as $value )
	{
    	$array[] =  $value;
	}
	 
	
	$total = count ( $array );
	
	$paging = array ('success' => true, 'total' => $total, 'data' => array_splice ( $array, $start, $limit ) );
	
	echo json_encode ( $paging );
}
if (isset ( $_REQUEST ['action'] ) && $_REQUEST ['action'] == 'editField' ) 
{
	$id = $_POST['ID'];
	$labelName = $_POST['fieldName'];
	$descriptionEN = $_POST['descriptionEN'];
	$descriptionFR = $_POST['descriptionFR'];
	$sQuery = " UPDATE PMT_CUSTOMIZE_LABEL 
					       SET
					       NAME_LABEL = '$labelName', 
					       DESCRIPTION_EN = '".mysql_real_escape_string($descriptionEN)."',
					       DESCRIPTION_FR = '".mysql_real_escape_string($descriptionFR)."'
					       WHERE ID_LABEl = '$id'
					       ";
	executeQuery ($sQuery); 	    
	$res = true;
	 
	
	header("Content-Type: text/plain");
	$paging = array('success' => $res ); 
	echo json_encode($paging);
}


?>
    