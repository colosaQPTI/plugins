<?php    
    G::LoadClass('case');
    G::LoadClass('configuration');
    G::loadClass('pmFunctions');
        
    $start = isset($_POST['start']) ? $_POST['start'] : 0;
    $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
	$USER_UID = $_SESSION['USER_LOGGED'];
	$where="";
	
	$sQuery = "SELECT A.*
               FROM PMT_INBOX_FIELDS AS A 
               WHERE A.ID_INBOX = '".$_GET['actionInbox_id']."' 
			   AND A.ROL_CODE = '".mysql_escape_string($_GET['rolID'])."'"; 
    $aDatos = executeQuery ($sQuery);

	$array = Array();
	foreach($aDatos as $valor)
	{        
	    $array[] = $valor;
	}
	$total = count($aDatos);

	header("Content-Type: text/plain");

	$paging = array(
	    'success'=> true,
	    'total'=> $total,
	    'data'=> array_splice($array,$start,$limit)
	);

	echo json_encode($paging);    
?>
