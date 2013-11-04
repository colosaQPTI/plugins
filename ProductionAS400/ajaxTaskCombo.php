<?php
# Headers
G::LoadClass ( 'case' );
G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );
require_once ("classes/model/Users.php");
# End Headers

$start = isset ( $_POST ['start'] ) ? $_POST ['start'] : 0;
$limit = isset ( $_POST ['limit'] ) ? $_POST ['limit'] : 200000;
$USER_UID = $_SESSION ['USER_LOGGED'];
$Us = new Users ();
$Roles = $Us->load ( $USER_UID );
$rolesAdmin = $Roles ['USR_ROLE'];
if(isset($_GET['Type']) && $_GET['Type'] == 'TaskCombo'){
	
	$sQuery = "     SELECT T.TAS_UID as ID, C.CON_VALUE AS NAME
   					FROM TASK T
   					INNER JOIN CONTENT C ON (C.CON_ID = T.TAS_UID)
   					WHERE C.CON_CATEGORY = 'TAS_TITLE' AND CON_LANG = '".SYS_LANG."'     
   					GROUP BY  TAS_UID                                                                               
                  ";
	
	$aDatos = executeQuery ( $sQuery );
}


$array = Array ();
foreach ( $aDatos as $valor ) {
	$array [] = $valor;
}
$total = count ( $aDatos );
header ( "Content-Type: text/plain" );
$paging = array ('success' => true, 'total' => $total, 'data' => array_splice ( $array, $start, $limit ) );

echo json_encode ( $paging );
?>
