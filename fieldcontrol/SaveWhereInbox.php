<?php
G::LoadClass ( 'case' );
G::LoadClass ( 'pmFunctions' );
header('Content-type:text/javascript;charset=UTF-8');
$form=$_POST;
$res=false;

function removeWhereInbox($id, $tableName){
	$ret = array();
	if($tableName == 'PMT_INBOX_WHERE')
		$sql = "DELETE FROM PMT_INBOX_WHERE WHERE IWHERE_UID = " . $id;
	else 
		$sql = "DELETE FROM PMT_INBOX_WHERE_USER WHERE IWHERE_USR_ID = " . $id;
	executeQuery($sql);
	
	header("Content-Type: text/html");
	$returnStatus = array('success' => true,'Msg'=>'Succesfully');

	echo G::json_encode($returnStatus);

}
function addWhereInbox($idInbox,$whereTxaField,$rolID)
{
	$queryItemFile="INSERT INTO PMT_INBOX_WHERE (IWHERE_UID,IWHERE_QUERY,IWHERE_IID_INBOX,IWHERE_ROL_CODE)
			VALUES (
			NULL,
				'$whereTxaField',
				'$idInbox',			
				'".mysql_escape_string($rolID)."'
			)";
	executeQuery($queryItemFile);
	$res=true;	    
		    
	header("Content-Type: text/html");
	$returnStatus = array('success' => $res);

	echo G::json_encode($returnStatus);
}
function editWhereInbox($idInbox,$whereTxaField,$whereIDField)
{
	$queryItemFile="UPDATE PMT_INBOX_WHERE SET
			IWHERE_QUERY = '$whereTxaField'
			WHERE IWHERE_UID = '$whereIDField'
			";

	executeQuery($queryItemFile);
	$res=true;	    
		    
	header("Content-Type: text/html");
	$returnStatus = array('success' => $res);

	echo G::json_encode($returnStatus);	
}

function addWhereInboxConfigUsers($data,$idInbox,$rolID)
{
	$array = Array ();
	
		$sQueryT = " SELECT * FROM USERS ";
		$DBConnectionUID = 'workflow';
		$con = mysql_connect(DB_HOST,DB_USER,DB_PASS); 
		mysql_select_db(DB_NAME, $con); 
		$selectT = mysql_query ($sQueryT);
		mysql_close($con);
		$i = 0;
		
		while ($i < mysql_num_fields($selectT)) 
		{
			$metaData = mysql_fetch_field($selectT, $i);
   			if (!$metaData) {
       		 	echo "No hay información disponible<br />\n";
   			}
   			$nameTable = $metaData->table;
   			$newArray[] = Array(
								"ID" => $metaData->name,
   	 							"NAME" => $metaData->name,
   								"TABLE" => 'USERS'
						  );
     		$i++;
		}
		
		$query = "SELECT NI.USR_NEW_INF_FIELD_NAME AS ID, CU.CFG_USR_DESCRIPTION AS NAME
				  FROM PMT_USER_NEW_INFORMATION NI
		  		  INNER JOIN PMT_CONFIG_USERS CU ON ( CU.CFG_USR_FIELD_NAME = NI.CFG_USR_FIELD_NAME ) ";
		$newOptions = executeQuery($query);
	foreach($newOptions as $index)
		{
			$index['TABLE'] = 'PMT_USER_NEW_INFORMATION';
			$newArray[] = $index;
		}
	foreach ( $data as $name => $value ) 
	{
		$idInbox = $value->idInbox;
		$fieldName = $value->fieldName;
		$idTable = $value->idTable;
		$parameterField = $value->parameterField;
		$operator = $value->operator;
		
		
		$usrTable = '';
		foreach($newArray as $index)
		{
			if($index['ID'] == $parameterField)
				$usrTable = $index['TABLE'];
		}
		if($usrTable != '')
		{
			if($usrTable != 'USERS')
				$whereQuery = "SELECT USR_NEW_INF_VALUE_FIELD AS DATA_CONFIG FROM $usrTable WHERE USR_NEW_INF_FIELD_NAME = '". $parameterField. "' AND USR_UID = ";
			else  
				$whereQuery = "SELECT $parameterField AS DATA_CONFIG FROM $usrTable WHERE USR_UID = ";
		}
		$sQuery = " SELECT max(IWHERE_USR_ID) as MAX_ID FROM PMT_INBOX_WHERE_USER ";
		$selectId = executeQuery($sQuery);
		$idInboxWhere = $selectId[1]['MAX_ID'];
		$idInboxWhere++;
		$queryItemFile="INSERT INTO PMT_INBOX_WHERE_USER (IWHERE_USR_ID, IWHERE_USR_QUERY, IWHERE_USR_TABLE, INBOX_ID, ROL_CODE, INBOX_ID_TABLE, INBOX_FIELD_NAME, IWHERE_USR_OPERATOR, IWHERE_USR_PARAMETER)
			VALUES (
				'$idInboxWhere',
				'".mysql_escape_string($whereQuery)."',
				'$usrTable',
				'$idInbox',			
				'".mysql_escape_string($rolID)."',
				'$idTable',
				'$fieldName',
				'$operator',
				'$parameterField'
			)";
		executeQuery($queryItemFile);
		    
	}	 
	$res=true;
	header("Content-Type: text/html");
	$returnStatus = array('success' => $res);

	echo G::json_encode($returnStatus);
}

$method = $form["whereaction"];

switch ($method) {
	case "remove":
		$ret = removeWhereInbox($form['whereIDField'], $form['whereTable'] );
	break;
	
	case "add":
		if(isset($_GET['ID']) && $_GET['ID']!='')
		{
			$ret = addWhereInbox($_GET['ID'],mysql_escape_string($form['whereTxaField']),$form['rolID']);
	
		}
		break;
		
	case "edit":
		if(isset($_GET['ID']) && $_GET['ID']!='')
		{
			
			$ret = editWhereInbox($_GET['ID'],mysql_escape_string($form['whereTxaField']),$form['whereIDField']);
	
		}
		break;
		
	case "configUsers":
		if(isset($_POST['idInbox']) && $_POST['idInbox']!='')
		{	
			$sql = "DELETE FROM PMT_INBOX_WHERE_USER WHERE INBOX_ID = '".$_POST['idInbox']."' AND ROL_CODE = '".mysql_escape_string($_POST['rolID'])."' ";
			executeQuery($sql);
			$data = json_decode ( $_POST ['arrayConfigUsers'] );
			$ret = addWhereInboxConfigUsers($data,$_POST['idInbox'],$_POST['rolID']);
		}
		break;
}
?>
