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
if(isset($_GET['Type']) && $_GET['Type'] == 'ProcessCombo'){
	
	$sQuery = "	SELECT P.PRO_UID as ID, C.CON_VALUE AS NAME
   				FROM PROCESS P
   				INNER JOIN CONTENT C ON (C.CON_ID = P.PRO_UID)
   				WHERE C.CON_CATEGORY = 'PRO_TITLE' AND C.CON_LANG = '".SYS_LANG."'     
   				GROUP BY  PRO_UID                                                                               
              ";
	$aDatos = executeQuery ( $sQuery );
	
	$array = Array ();
	foreach ( $aDatos as $valor ) {
		$array [] = $valor;
	}
	$total = count ( $array );
}

header ( "Content-Type: text/plain" );
$paging = array ('success' => true, 'total' => $total, 'data' => array_splice ( $array, $start, $limit ) );
echo json_encode ( $paging );
?>
