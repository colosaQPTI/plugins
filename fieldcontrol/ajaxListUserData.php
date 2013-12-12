<?php
G::LoadClass ( 'case' );
G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );

$start = isset ( $_POST ['start'] ) ? $_POST ['start'] : 0;
$limit = isset ( $_POST ['limit'] ) ? $_POST ['limit'] : 1000;

if(isset($_GET['type']) && $_GET['type'] == 'combo')
{   
	$array = Array ();
	
	$sQueryT = " SELECT * FROM USERS ";
	try{
		
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
   	 							"NAME" => $metaData->name
						  );
     		$i++;
		}
		
		$query = "SELECT NI.USR_NEW_INF_FIELD_NAME AS ID, CU.CFG_USR_DESCRIPTION AS NAME
				  FROM PMT_USER_NEW_INFORMATION NI
		  		  INNER JOIN PMT_CONFIG_USERS CU ON ( CU.CFG_USR_FIELD_NAME = NI.CFG_USR_FIELD_NAME ) 
		  		  GROUP BY ID ";
		$newOptions = executeQuery($query);
		foreach($newOptions as $index)
		{
			$newArray[] = $index;
		}
	}	
	catch (Exception $e)
	{
		$error = $e->getMessage();
		$error = preg_replace("[\n|\r|\n\r]", ' ', $error);
		$paging = array ('success' => false, 'response' => $error);
		echo json_encode ( $paging );
		die;
	}
	$total = count ( $newArray );
	$paging = array ('success' => true, 'total' => $total, 'data' => array_splice ( $newArray, $start, $limit ) );
	echo json_encode ( $paging );
}	
else 
{
$total = 0;

	if (isset ( $_REQUEST ['idTable'] ) && $_REQUEST ['idTable'] != '') 
	{
		$query = "SELECT JOIN_QUERY FROM PMT_INBOX_JOIN 
				  WHERE JOIN_ROL_CODE  = '" . mysql_escape_string($_GET ['rolID']) . "' AND JOIN_ID_INBOX = '".$_REQUEST['idInboxData']."' ";

		$newOptions = executeQuery ( $query );
		$innerJoin = isset ( $newOptions [1]['JOIN_QUERY'] ) ? $newOptions [1]['JOIN_QUERY'] : '';
		
		$innerJoin= str_replace(' AS ' , ' ' , $innerJoin);
		$innerJoin= str_replace(' as ' , ' ' , $innerJoin);
		$innerJoin= str_replace(' inner ' , ' left ' , $innerJoin);
		$innerJoin= str_replace(' INNER ' , ' LEFT ' , $innerJoin);
		$innerJoin= str_replace(' join ' , ' JOIN ' , $innerJoin);
		$table =  $_REQUEST ['idTable'];

		$sQueryT = " SELECT *
	  						 FROM ".$table." 
	  						 " . $innerJoin . " 
	    		   	";
		try{
		
	    	$DBConnectionUID = 'workflow';
			$con = mysql_connect(DB_HOST,DB_USER,DB_PASS); 
			mysql_select_db(DB_NAME, $con); 
			$selectT = mysql_query ($sQueryT);
			mysql_close($con);
			if(sizeof($selectT))
			{
				$newArray = Array ();
				$arrayTotalFields = Array ();
				## Get Names of Tables of Query
	    		$queryExplain = "EXPLAIN $sQueryT";
	   			$infoTables = executeQuery ( $queryExplain );
	    		$aTables = array();
	    		foreach ( $infoTables as $key => $data ) 
	    		{
	        		$aTables[] = trim($data['table']);
	    		}

				$tableNames=array();
	    		$tableNames[]=  array('OLD_NAME' => $table , 'ORIG_NAME' => $table );
	    		$tableOldLast = $table;
	    		for ($i=0;$i< count($aTables); $i++) 
	    		{
	    			$partQuery1= explode(' '.$aTables[$i].' ',$sQueryT);
	        		$partQuery2 = explode(' ',$partQuery1[0]);
	        		$origTableName = trim($partQuery2[count($partQuery2)-1]);
	        		if($aTables[$i] != $table)
	        			$tableNames[]=  array('OLD_NAME' => $aTables[$i] , 'ORIG_NAME' => $origTableName);
	    		}

	    		$i = 0;
				while ($i < mysql_num_fields($selectT)) 
				{
					$metaData = mysql_fetch_field($selectT, $i);
	   				if (!$metaData) {
	       		 		echo "No hay información disponible<br />\n";
	   				}
	   				$nameTable = $metaData->table;
	   				foreach ($tableNames as $row) 
					{
						if($row['ORIG_NAME'] != 'JOIN' && $row['ORIG_NAME'] != 'FROM')
	      					$tableShow = $row['ORIG_NAME'];
	      				else
	      					$tableShow = $row['OLD_NAME'];
						if($nameTable == $tableShow || $nameTable == $row['OLD_NAME'])
						{
							$arrayTotalFields[] = Array(
									"FLD_UID" => $metaData->name,
	   	 							"ADD_TAB_UID" => $tableShow,
	      							"ALIAS_TABLE" => $row['OLD_NAME']
									);
						}
					}
	    			$newArray[] = $metaData->name;
	     			$i++;
				}

				### data custom columns
				$select = "SELECT FIELD_NAME FROM PMT_INBOX_FIELDS_SELECT 
				  	 WHERE ROL_CODE = '".mysql_escape_string($_GET ['rolID'])."' AND ID_INBOX = '".$_REQUEST['idInboxData']."' AND TYPE = 'Yes' 
				  ";
				$dataSelect = executeQuery($select);
				foreach($dataSelect as $row)
				{
					$sw = 0;
					foreach($arrayTotalFields as $index )
					{
						if($row['FIELD_NAME'] == $index['FLD_UID'] && $sw == 0)
						{
							$sw = 1;
						}
					}
					if($sw == 0)
					{
						$arrayTotalFields[] = Array(
									"FLD_UID" => $row['FIELD_NAME'],
	   	 							"ADD_TAB_UID" => '',
	      							"ALIAS_TABLE" => ''
									);
					}
				}
		
				$totalInner = 0;
				$iColor = 1;
				$swColor = 0;
				$valor = Array();
				$swPos = 0;
				$iTotal = 1;
				$queryTot = "SELECT FLD_UID FROM PMT_INBOX_FIELDS 
					  WHERE ROL_CODE  = '" . mysql_escape_string($_GET ['rolID']) . "'  AND ID_INBOX = '".$_REQUEST['idInboxData']."' ";
				$execTot= executeQuery ( $queryTot );
				$totalFields = sizeof($execTot);
				$posField = $totalFields;
				$total = sizeof($arrayTotalFields);
	
				foreach($arrayTotalFields as $index )
				{
					$query = "SELECT * FROM PMT_INBOX_FIELDS 
					 	 WHERE ROL_CODE  = '" . mysql_escape_string($_GET ['rolID']) . "' AND FLD_UID = '" . $index['FLD_UID'] . "' AND 
					  	 ID_INBOX = '".$_REQUEST['idInboxData']."' AND ID_TABLE = '".$index['ADD_TAB_UID']."' ";

					$newOptions = executeQuery ( $query );
					if (sizeof ( $newOptions )) 
					{
						if (isset ( $newOptions [1] ['DESCRIPTION'] ) && $newOptions [1] ['DESCRIPTION'] != '')
							$valor ['FLD_DESCRIPTION'] = $newOptions [1] ['DESCRIPTION'];
					
						if (isset ( $newOptions [1] ['POSITION'] ) && $newOptions [1] ['POSITION'] != '')	
							$valor ['POSITION'] = $newOptions [1] ['POSITION'];	
					
						if(isset ( $newOptions [1] ['ID_INBOX'] ) && $newOptions [1] ['ID_INBOX'] != '')
							$valor['ID_INBOX'] = $newOptions[1]['ID_INBOX'];	
					
						$valor['ADD_TAB_NAME'] = $index['ADD_TAB_UID'];

						$valor['ALIAS_TABLE'] = $index['ALIAS_TABLE'];

						$swPos = 1;		

					} 
					else 
					{
						$posField++;
						$valor ['FLD_DESCRIPTION'] = $index['FLD_UID'];
						$valor ['POSITION'] = $posField;
						$valor ['ID_INBOX'] = '';
						$valor ['ADD_TAB_NAME'] = $index['ADD_TAB_UID'];
						$valor ['ALIAS_TABLE'] = $index['ALIAS_TABLE'];
					}
					$queryUser = "SELECT * FROM PMT_INBOX_WHERE_USER 
					 	 WHERE ROL_CODE  = '" . mysql_escape_string($_GET ['rolID']) . "' AND INBOX_FIELD_NAME = '" . $index['FLD_UID'] . "' AND 
					  	 INBOX_ID = '".$_REQUEST['idInboxData']."' AND INBOX_ID_TABLE = '".$index['ADD_TAB_UID']."' ";

					$dataWhere = executeQuery ( $queryUser );
					if (sizeof ( $dataWhere )) 
					{
						$valor ['INCLUDE_SELECT'] = true;
						$valor ['PARAMETERS_CONFIG_USER'] = $dataWhere[1]['IWHERE_USR_PARAMETER'];
						$valor ['OPERATOR'] = $dataWhere[1]['IWHERE_USR_OPERATOR'];
					} 
					else 
					{
						$valor ['INCLUDE_SELECT'] = false;
						$valor ['PARAMETERS_CONFIG_USER'] = '';
						$valor ['OPERATOR'] = '';
					}

					$valor ['ROL_CODE'] = $_GET ['rolID'];
					$valor ['FIELD_NAME'] = $index['FLD_UID'];
					$valor ['FLD_UID'] = $index['FLD_UID'];
					
					if($iTotal != 1)
						$indexAux = next($arrayTotalFields);
					else
						$indexAux = current($arrayTotalFields);
				
					$array [] = $valor;

					$valor ['FLD_DESCRIPTION'] = '';
					$valor ['POSITION'] = '';
					$valor ['ID_INBOX'] = '';
					$valor ['ADD_TAB_NAME'] = '';
					$valor ['ALIAS_TABLE'] = '';
					$valor++;
					$iTotal++;

				}

				$field = 'POSITION';
				$array = orderMultiDimensionalArray($array, $field, '');
			}
		}
		catch (Exception $e)
		{
			$error = $e->getMessage();
			$error = preg_replace("[\n|\r|\n\r]", ' ', $error);
			$paging = array ('success' => false, 'response' => $error);
			echo json_encode ( $paging );
			die;
		}
	}
	$total = count ( $array );
	$paging = array ('success' => true, 'total' => $total, 'data' => array_splice ( $array, $start, $limit ) );
	echo json_encode ( $paging );
}
	
function orderMultiDimensionalArray ($toOrderArray, $field, $inverse = false) 
{  
    $position = array();  
    $newRow = array();  
    foreach ($toOrderArray as $key => $row) {  
            $position[$key]  = $row[$field];  
            $newRow[$key] = $row;  
    }  

    if ($inverse) {  
        arsort($position);  
    }  
    else {  
        asort($position);  
    }  

    $returnArray = array();  
    foreach ($position as $key => $pos) {       
        $returnArray[] = $newRow[$key];  
    }  
    return $returnArray;  
}  
?>
    