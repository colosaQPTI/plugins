<?php   

    G::LoadClass('case');
    G::LoadClass('configuration');
    G::loadClass('pmFunctions');

    $start = isset($_POST['start']) ? $_POST['start'] : 0;
    $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
	$USER_UID = $_SESSION['USER_LOGGED'];
	$where="";
	$array = Array();
	if(isset($_GET['idAction']) && $_GET['idAction'] != '')
	{
		$sQuery = "SELECT F.FLD_UID AS ID, F.ID_INBOX, A.NAME_ACTION, A.ID_ACTION,  F.DESCRIPTION ,F.FIELD_NAME AS NAME_FIELD, F.ID_TABLE
               FROM PMT_INBOX_ACTIONS  A 
               INNER JOIN PMT_INBOX_FIELDS F on (F.ID_INBOX = A.ID_INBOX)
			   WHERE 1 
			   AND F.ID_INBOX = '".$_GET['actionInbox_id']."' 
			   AND F.ROL_CODE = '".mysql_escape_string($_GET['rolID'])."'
			   AND A.ID_ACTION = '".$_GET['idAction']."'
			   GROUP BY ID
			   ORDER BY A.POSITION, A.ID";
		$aDatos = executeQuery ($sQuery);
		foreach($aDatos as $valor)
		{        
	    	$query = "SELECT PARAMETERS_BY_FIELD, OPERATOR FROM PMT_CONDITION_BY_FIELDS 
	    	          WHERE ROL_CODE = '".mysql_escape_string($_GET['rolID'])."' 
	    	          AND ID_INBOX = '".$_GET['actionInbox_id']."' 
	    	          AND ID_ACTION = '".$_GET['idAction']."'
	    	          AND FLD_UID = '".$valor['NAME_FIELD']."' ";
	    	$data = executeQuery($query);
	    	foreach($data as $row)
	    	{
	    	    $valor['PARAMETERS_BY_FIELD'] = $row['PARAMETERS_BY_FIELD'];
	    	    $valor['OPERATOR'] = $row['OPERATOR'];
	    	    if(isset($row['PARAMETERS_BY_FIELD']) &&  $row['PARAMETERS_BY_FIELD'] != '')
	    		    $valor['INCLUDE_SELECT'] = true;
	    	    else
	    		    $valor['INCLUDE_SELECT'] = false;
	    	}

			$array[] = $valor;
		}
	}
	else
	{
		$sQuery = "SELECT A.ID, A.ID_INBOX,A.ID_ACTION ,A.NAME_ACTION AS NAME,A.ID_PM_FUNCTION AS PM_FUNCTION,A.PARAMETERS_FUNCTION AS PARAMETERS_FUNCTION,
			   A.SENT_FUNCTION_PARAMETERS AS SENT_FUNCTION_PARAMETERS, B.DESCRIPTION, B.PARAMETERS_FUNCTION AS PARAMETERS_FUNCTION_AUX 
               FROM PMT_INBOX_ACTIONS AS A , PMT_ACTIONS AS B
               WHERE 1 
			   AND A.ID_INBOX = '".$_GET['actionInbox_id']."' 
			   AND A.ROL_CODE = '".mysql_escape_string($_GET['rolID'])."'
			   AND A.ID_ACTION = B.ID
			   ORDER BY A.POSITION, A.ID";
	

    	$aDatos = executeQuery ($sQuery);
		foreach($aDatos as $valor)
		{        
	    	$queryByField = "SELECT PARAMETERS_BY_FIELD FROM  PMT_CONDITION_BY_FIELDS
	    					 WHERE ID_INBOX = '".$valor['ID_INBOX']."' AND
	    					 ROL_CODE = '".mysql_escape_string($_GET['rolID'])."' AND 
	    					 ID_ACTION = '".$valor['ID_ACTION']."' ";
	    	$dataByField = executeQuery($queryByField);
	    	if(sizeof($dataByField))
	    		$valor['BY_FIELDS'] = 'Yes';
	    	else
	    		$valor['BY_FIELDS'] = 'No';
			$array[] = $valor;
		}
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

