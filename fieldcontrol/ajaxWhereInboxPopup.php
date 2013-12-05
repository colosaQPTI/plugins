<?php
    G::LoadClass('case');
    G::LoadClass('configuration');
    G::loadClass('pmFunctions');

    $start = isset($_POST['start']) ? $_POST['start'] : 0;
    $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
	$USER_UID = $_SESSION['USER_LOGGED'];
	$where="";
	$sQuery = "SELECT * FROM PMT_INBOX_WHERE AS A
			   WHERE A.IWHERE_IID_INBOX = '".$_REQUEST['actionInbox_id']."'
			   AND A.IWHERE_ROL_CODE = '".mysql_escape_string($_REQUEST['rolID'])."' ";
    $aData = executeQuery ($sQuery);

	$array = Array();
	foreach($aData as $index)
	{
	    $index['TABLE_NAME'] = 'PMT_INBOX_WHERE';
		$array[] = $index;
	}
	$sQuery = "SELECT IWHERE_USR_ID AS IWHERE_UID, INBOX_ID_TABLE, INBOX_FIELD_NAME, IWHERE_USR_OPERATOR, IWHERE_USR_PARAMETER, INBOX_ID AS IWHERE_IID_INBOX, ROL_CODE AS IWHERE_ROLE_CODE
			   FROM PMT_INBOX_WHERE_USER AS IU
			   WHERE IU.INBOX_ID = '".$_REQUEST['actionInbox_id']."'
			   AND IU.ROL_CODE = '".mysql_escape_string($_REQUEST['rolID'])."' ";
	$aData = executeQuery ($sQuery);
	foreach($aData as $index)
	{
	    $index['TABLE_NAME'] = 'PMT_INBOX_WHERE_USER';
	    $index['IWHERE_QUERY'] = $index['INBOX_ID_TABLE'].'.'.$index['INBOX_FIELD_NAME'].' '.$index['IWHERE_USR_OPERATOR'].' '.$index['IWHERE_USR_PARAMETER'];
		$array[] = $index;
	}
	
	$total = count($array);
	header("Content-Type: text/plain");

	$paging = array(
	    'success'=> true,
	    'total'=> $total,
	    'data'=> array_splice($array,$start,$limit)
	);

	echo json_encode($paging);
?>
