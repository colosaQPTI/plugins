<?php

ini_set ( 'error_reporting', E_ALL );
ini_set ( 'display_errors', True );

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
$ROL_UID = $_GET ['rolID'];
$innerJoin = '';
$j = 0;
$total = 0;
if (isset ( $_POST ['idInbox'] ) && $_POST ['idInbox'] != '') 
{
	 $array = Array();
	 $select = "SELECT ID_TABLE FROM PMT_INBOX_PARENT_TABLE WHERE ID_INBOX = '".$_POST['idInbox']."' AND ROL_CODE  = '" . $_GET ['rolID'] . "' ";
	 $dataSelect = executeQuery($select);
	 if(sizeof($dataSelect))
	 {
  	 	$query = " SELECT
    		    ADD_TAB_NAME AS ID,
    		    ADD_TAB_NAME AS NAME
    		    FROM PMT_INBOX_FIELDS
				INNER JOIN ADDITIONAL_TABLES ON (PMT_INBOX_FIELDS.ID_TABLE = ADDITIONAL_TABLES.ADD_TAB_NAME AND ADDITIONAL_TABLES.PRO_UID != '' )
				WHERE PMT_INBOX_FIELDS.ID_INBOX = '".$_POST['idInbox']."' AND ROL_CODE  = '" . $_GET ['rolID'] . "'
					  AND ALIAS_TABLE = ID_TABLE AND ID_TABLE = '".$dataSelect[1]['ID_TABLE']."'
				GROUP BY ADD_TAB_UID";
	 }
	 else 
	 {
	 	$query = " SELECT
    		    ADD_TAB_NAME AS ID,
    		    ADD_TAB_NAME AS NAME
    		    FROM PMT_INBOX_FIELDS
				INNER JOIN ADDITIONAL_TABLES ON (PMT_INBOX_FIELDS.ID_TABLE = ADDITIONAL_TABLES.ADD_TAB_NAME AND ADDITIONAL_TABLES.PRO_UID != '' )
				WHERE PMT_INBOX_FIELDS.ID_INBOX = '".$_POST['idInbox']."' AND ROL_CODE  = '" . $_GET ['rolID'] . "' 
					  AND ALIAS_TABLE = ID_TABLE 
				GROUP BY ADD_TAB_UID
				ORDER BY PRO_UID DESC";
	 	
	 }
    $fields = executeQuery($query);
  
    if(sizeof($fields))
    {
        foreach($fields as $index)
        {
		    $query = "SELECT JOIN_QUERY FROM PMT_INBOX_JOIN 
				  	  WHERE JOIN_ROL_CODE  = '" . $_GET ['rolID'] . "'  AND JOIN_ID_INBOX = '".$_POST['idInbox']."'  ";

		    $newOptions = executeQuery ( $query );
		    $innerJoin = isset ( $newOptions [1]['JOIN_QUERY'] ) ? $newOptions [1]['JOIN_QUERY'] : '';
		    $index['INNER_JOIN'] = $innerJoin; 
    	    $array[] = $index;
	    }
	    $total = count ( $fields );
    }
    else
    {
        $index['INNER_JOIN'] = '';
        $index['ID'] = '';
        $index['NAME'] = '';
        $array[] = $index;
        $total = 1;
    }

}


if (isset ( $_REQUEST ['idTable'] ) && $_REQUEST ['idTable'] != '') 
{
    $total = 0;
	if( (isset($_REQUEST ['inner']) && $_REQUEST ['inner'] != '') || (isset($_REQUEST ['swinner']) && $_REQUEST ['swinner'] == 1) )
		$innerJoin = isset($_REQUEST ['inner']) ? $_REQUEST ['inner'] : '';
	else 
	{
		$query = "SELECT JOIN_QUERY FROM PMT_INBOX_JOIN 
				  WHERE JOIN_ROL_CODE  = '" . $_GET ['rolID'] . "' AND JOIN_ID_INBOX = '".$_REQUEST['idInboxData']."' ";

		$newOptions = executeQuery ( $query );
		$innerJoin = isset ( $newOptions [1]['JOIN_QUERY'] ) ? $newOptions [1]['JOIN_QUERY'] : '';
	}

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
    	$ipServer = getIP();
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
			  	 WHERE ROL_CODE = '".$_GET ['rolID']."' AND ID_INBOX = '".$_REQUEST['idInboxData']."' AND TYPE = 'Yes' 
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

			$queryColor = "SELECT COLOR_CODE FROM PMT_INBOX_FIELDS_COLOR WHERE COLOR_UID = ". $iColor ." ";
			$color = executeQuery($queryColor);	
			$value = Array();
			$swPos = 0;
			$iTotal = 1;
			$swColorCon = 0;
			$queryTot = "SELECT FLD_UID FROM PMT_INBOX_FIELDS 
				  WHERE ROL_CODE  = '" . $_GET ['rolID'] . "'  AND ID_INBOX = '".$_REQUEST['idInboxData']."' ";
			$execTot= executeQuery ( $queryTot );

			$totalFields = sizeof($execTot);

			$posField = $totalFields;
	
			$total = sizeof($arrayTotalFields);
	
			foreach($arrayTotalFields as $index )
			{
				$query = "SELECT * FROM PMT_INBOX_FIELDS 
				 	 WHERE ROL_CODE  = '" . $_GET ['rolID'] . "' AND FLD_UID = '" . $index['FLD_UID'] . "' AND 
				  	 ID_INBOX = '".$_REQUEST['idInboxData']."' AND ID_TABLE = '".$index['ADD_TAB_UID']."' ";

				$newOptions = executeQuery ( $query );
				if (sizeof ( $newOptions )) 
				{
			
					$value ['INCLUDE_OPTION'] = true;
					if (isset ( $newOptions [1] ['DESCRIPTION'] ) && $newOptions [1] ['DESCRIPTION'] != '')
						$value ['FLD_DESCRIPTION'] = $newOptions [1] ['DESCRIPTION'];
				
					if (isset ( $newOptions [1] ['JOIN_QUERY'] ) && $newOptions [1] ['JOIN_QUERY'] != '')
						$value ['INNER_JOIN'] = $newOptions [1] ['JOIN_QUERY'];
				
					if (isset ( $newOptions [1] ['POSITION'] ) && $newOptions [1] ['POSITION'] != '')	
						$value ['POSITION'] = $newOptions [1] ['POSITION'];	
				
					if (isset ( $newOptions [1] ['FIELD_REPLACE'] ) && $newOptions [1] ['FIELD_REPLACE'] != '')	
						$value ['FIELD_REPLACE'] = $newOptions [1] ['FIELD_REPLACE'];
				
					if(isset ( $newOptions [1] ['ID_INBOX'] ) && $newOptions [1] ['ID_INBOX'] != '')
						$value['ID_INBOX'] = $newOptions[1]['ID_INBOX'];	
				
					if (isset ( $newOptions [1] ['OPTION_QUERY_FUNCTION'] ) && $newOptions [1] ['OPTION_QUERY_FUNCTION'] != '')
						$value['SELECT_OPTION'] = $newOptions [1] ['OPTION_QUERY_FUNCTION'];
				
					if (isset ( $newOptions [1] ['FUNCTIONS'] ) && $newOptions [1] ['FUNCTIONS'] != '')
						$value['FUNCTIONS'] = $newOptions [1] ['FUNCTIONS'];

					if (isset ( $newOptions [1] ['PARAMETERS'] ) && $newOptions [1] ['PARAMETERS'] != '')
						$value['PARAMETERS'] = $newOptions [1] ['PARAMETERS'];

					if (isset ( $newOptions [1] ['HIDDEN_FIELD'] ) && $newOptions [1] ['HIDDEN_FIELD'] == 1)
						$value['HIDDEN_FIELD'] = true;
					else 
						$value['HIDDEN_FIELD'] = false;

					if (isset ( $newOptions [1] ['INCLUDE_FILTER'] ) && $newOptions [1] ['INCLUDE_FILTER'] == 1)
						$value['INCLUDE_FILTER'] = true;
					else 
						$value['INCLUDE_FILTER'] = false;

					if (isset ( $newOptions [1] ['ORDER_BY'] ) && $newOptions [1] ['ORDER_BY']  != '')
						$value['ORDER_BY'] = $newOptions [1] ['ORDER_BY'];
					else 
						$value['ORDER_BY'] = '';

					$value['ADD_TAB_NAME'] = $index['ADD_TAB_UID'];

					$value['ALIAS_TABLE'] = $index['ALIAS_TABLE'];

					$swPos = 1;		

				} 
				else 
				{
					$posField++;
					$value ['INCLUDE_OPTION'] = false;
					$value ['INCLUDE_FILTER'] = false;
					$value ['HIDDEN_FIELD'] = false;
					$value ['FLD_DESCRIPTION'] = $index['FLD_UID'];
					$value ['INNER_JOIN'] = '';
					$value ['POSITION'] = $posField;
					$value ['FIELD_REPLACE'] = '';
					$value ['ID_INBOX'] = '';
					$value ['SELECT_OPTION'] = '';
					$value ['FUNCTIONS'] = '';
					$value ['PARAMETERS'] = '';
					$value['ORDER_BY'] = '';
					$value['ADD_TAB_NAME'] = $index['ADD_TAB_UID'];
					$value['ALIAS_TABLE'] = $index['ALIAS_TABLE'];
				}

				$value ['ROL_CODE'] = $_GET ['rolID'];
				$value ['FIELD_NAME'] = $index['FLD_UID'];
				$value ['FLD_UID'] = $index['FLD_UID'];
				$value ['COLOR'] = isset ( $color[1]['COLOR_CODE'] ) ? $color[1]['COLOR_CODE'] : '';

				if($iTotal != 1)
					$indexAux = next($arrayTotalFields);
				else
					$indexAux = current($arrayTotalFields);

			//G::pr($index['FLD_UID'].' index   indexAux '.$indexAux['FLD_UID'].' '. ($iTotal).'  '. $total.'  '.$indexAux['ADD_TAB_UID'].'  '.$index['ADD_TAB_UID'].' next ');

				if($indexAux['ADD_TAB_UID'] != $index['ADD_TAB_UID']  && $iTotal  < $total)
				{
					$iColor++;
					$queryColor = "SELECT COLOR_CODE FROM PMT_INBOX_FIELDS_COLOR WHERE COLOR_UID = ". $iColor ." ";
					$color = executeQuery($queryColor);
					$swColor++;
					$swColorCon = 0;
				}
				if($indexAux['ADD_TAB_UID'] != $index['ADD_TAB_UID']  && $iTotal  < $total && $indexAux['ADD_TAB_UID'] == '')
				{
					$iColor++;
					$queryColor = "SELECT COLOR_CODE FROM PMT_INBOX_FIELDS_COLOR WHERE COLOR_UID = '5' ";
					$color = executeQuery($queryColor);
					$swColor++;
					$swColorCon = 0;
				}

				if($swColor == 1);
					$swColorCon ++;
				if($swColorCon == 1)
					$swColor = 0 ;
		
				$array [] = $value;

				$value ['INCLUDE_OPTION'] = '';
				$value ['FLD_DESCRIPTION'] = '';
				$value ['INNER_JOIN'] = '';
				$value ['POSITION'] = '';
				$value ['FIELD_REPLACE'] = '';
				$value ['ID_INBOX'] = '';
				$value ['SELECT_OPTION'] = '';
				$value ['FUNCTIONS'] = '';
				$value ['PARAMETERS'] = '';
				$value ['ADD_TAB_NAME'] = '';
				$value ['ALIAS_TABLE'] = '';
				$value++;
				$iTotal++;

			}

			$field = 'POSITION';
			$array = orderMultiDimensionalArray($array, $field, '');
		//G::pr($array);
		
		
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

$paging = array ('success' => true, 'total' => $total, 'data' => array_splice ( $array, $start, $limit ), 'response' => 'OK' );
echo json_encode ( $paging );


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

function getIP()
{
    if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] )) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if( isset( $_SERVER ['HTTP_VIA'] ))  $ip = $_SERVER['HTTP_VIA'];
    else if( isset( $_SERVER ['REMOTE_ADDR'] ))  $ip = $_SERVER['REMOTE_ADDR'];
    else $ip = null ;
    return $ip;
}
?>

    