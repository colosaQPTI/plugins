<?php
#####################################################################
# @@ ajaxInboxDinamic.php
# @@ Recent change : April 10
#####################################################################

G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );

##### Variables

$idTable = isset($_GET ['idTable'])? $_GET ['idTable']:'';
$idInbox = isset($_GET ['idInbox'])? $_GET ['idInbox']:'';
$start 	 = isset ( $_POST ['start'] ) ? $_POST ['start'] : 0; 
$limit = isset($_POST ['limit']) ? $_POST ['limit'] : 100;


$sJoins 	= '';
$sWhere 	= 'WHERE 1';
$aDataGrid 	= Array ();
$sRol 		= getRolUser();
$bStatus	= false;
global $RBAC;
##### End Variables

##### Section Order By

$sSQL = " SELECT ORDER_BY, FLD_UID, ALIAS_TABLE  FROM PMT_INBOX_FIELDS WHERE ROL_CODE ='$sRol' AND  ID_INBOX = '$idInbox' AND ORDER_BY <> '' "; 
$aData = executeQuery ($sSQL);
$totOrder = sizeof($aData);
$contOrder = 1;
$sOrderBy = '';

if (isset($_POST ['sort']) && $_POST ['sort'] != '') {
		$typeOrder = "";
		if (isset($_POST['dir']) && $_POST['dir'] != '') {
			$typeOrder = $_POST ['dir'];
		}
		$tableAlias = getAliasTable($idInbox,$_POST ['sort']);
		if($tableAlias != '') 
			$sOrderBy = " ORDER BY $tableAlias.".trim($_POST ['sort'])." $typeOrder ";
		else
			$sOrderBy = " ORDER BY ".trim($_POST ['sort'])." $typeOrder ";
}

if(sizeof($aData) )
{
	if($sOrderBy == '') {
		$sOrderBy = " ORDER BY  ";
		
            foreach($aData as $row)
            {
                    if($contOrder == $totOrder)
                            $sOrderBy .= $row['ALIAS_TABLE'].'.'.$row['FLD_UID']."  ". $row['ORDER_BY'];
                    else 
                            $sOrderBy .= $row['ALIAS_TABLE'].'.'.$row['FLD_UID']."  ". $row['ORDER_BY'].",";
                    $contOrder++;
            }
        }		
}

##### End Section Order By
try {  
	
	##### new config users
	
	$sQuery = "SELECT IWHERE_USR_QUERY,INBOX_ID_TABLE,INBOX_FIELD_NAME,IWHERE_USR_OPERATOR FROM PMT_INBOX_WHERE_USER WHERE ROL_CODE = '$sRol' AND INBOX_ID = '$idInbox' ";
	$sData = executeQuery($sQuery);
	if(sizeof($sData))
	{	$sDataConfig = '';
		foreach($sData as $index)
		{
			$sQueyConfig =  $index['IWHERE_USR_QUERY'] ."'". $_SESSION['USER_LOGGED']."'";
			$sExecConfig = executeQuery($sQueyConfig);
			
			if(sizeof($sExecConfig))
			{
			 	$sDataConfig = $sExecConfig[1]['DATA_CONFIG'];
			 	$sWhere.= ' AND '.$index['INBOX_ID_TABLE'].'.'.$index['INBOX_FIELD_NAME'].' '.$index['IWHERE_USR_OPERATOR'].' "'.$sDataConfig.'"';
			}
		}
	}
	
	##### end new config users
	
	##### Get JOINS 

	$sSQL = " SELECT JOIN_QUERY FROM PMT_INBOX_JOIN WHERE JOIN_ROL_CODE ='$sRol' AND JOIN_ID_INBOX = '$idInbox'";
	$aData = executeQuery ($sSQL);
	if(is_array($aData) && count($aData) >0 && isset($aData[1]['JOIN_QUERY'])){
		$sJoins = $aData[1]['JOIN_QUERY'];
	}
	
		##### Current User !!! ###########

	if($RBAC->userCanAccess("PM_ALLCASES") != 1){
		$userlogged = $_SESSION['USER_LOGGED'];		
		$sJoins.=" INNER JOIN APPLICATION ON (APPLICATION.APP_UID = ".$idTable.".APP_UID AND APPLICATION.APP_CUR_USER='".$userlogged."')";		
	}
	##### End Current User !!! ###########

	##### End Get JOINS

	##### Section where

	$sWhere.=' '.getSqlWhere($idInbox); 
	##### End where

	##### Section filters (General & Specific)

	if(isset($_POST['fieldInputSpecific']) && $_POST['fieldInputSpecific']!="" && isset($_POST['fieldName']) && $_POST['fieldName']!=""){
		 $sWhere.= getQueryForSimpleSearch($idInbox,$_POST['fieldName'], $_POST['fieldInputSpecific'],true);
	}
	if(isset($_POST['fieldInputGeneral'])){
		 $sWhere.= getQueryForMultipleSearch($idInbox,$_POST['fieldInputGeneral']);
	}

	##### End Section Filters

	
	##### Options select query
	##### Options data selected
	$contDataSelect = 1;
	$dataSelect = "SELECT ALIAS_TABLE, FIELD_NAME FROM PMT_INBOX_FIELDS WHERE ID_INBOX = '".$idInbox ."' AND ROL_CODE ='$sRol'
					";
	$dataSelect = executeQuery($dataSelect);
	$dataSelected = '';
	$dataVerify = '';
	$conVerify = 1;
	if(sizeof($dataSelect))
	{$contSelect = 1;
		foreach($dataSelect as $index)
		{
			if($contDataSelect == 1)
			{				
					$fieldSelect = "SELECT QUERY_SELECT FROM PMT_INBOX_FIELDS_SELECT WHERE ID_INBOX = '".$idInbox ."' AND 
									ROL_CODE ='".$sRol."' AND FIELD_NAME = '".$index['FIELD_NAME']."'  ";
					$datafieldSelect = executeQuery($fieldSelect);
					$fieldSelectQuery = '';
					$totSelectQuery = sizeof($datafieldSelect);
					if(sizeof($datafieldSelect))
					{
						foreach($datafieldSelect as $row)
						{
							if($contSelect == 1)
								 $dataSelected = $row['QUERY_SELECT'];
							else 
								$dataSelected = $dataSelected.', '.$row['QUERY_SELECT'];
							$contSelect++;
							
						}
						if($conVerify == 1)
							$dataVerify = "'".$index['FIELD_NAME']."'";
						else
							$dataVerify = "'".$index['FIELD_NAME']."'".','.$dataVerify;
						$conVerify++; 
						
					}
					else
					{
						if($index['ALIAS_TABLE'] == '')
							$dataSelected = $index['FIELD_NAME'];
						else
							$dataSelected = $index['ALIAS_TABLE'].'.'.$index['FIELD_NAME']; 
					}
				
						
			}
			else
			{ //die('lalal');
			##### Options select query
					
					$fieldSelect = "SELECT QUERY_SELECT FROM PMT_INBOX_FIELDS_SELECT WHERE ID_INBOX = '".$idInbox ."' AND 
									ROL_CODE ='".$sRol."' AND FIELD_NAME = '".$index['FIELD_NAME']."'  ";
					$datafieldSelect = executeQuery($fieldSelect);
					$fieldSelectQuery = '';
					$totSelectQuery = sizeof($datafieldSelect);
					if(sizeof($datafieldSelect))
					{
						foreach($datafieldSelect as $row)
						{
							//if($contSelect == 1)
								//$dataSelected = $row['QUERY_SELECT'];
							//else
								$dataSelected = $dataSelected.', '.$row['QUERY_SELECT'];
							$contSelect++;
						} 
						if($conVerify == 1)
							$dataVerify = "'".$index['FIELD_NAME']."'";
						else
							$dataVerify = "'".$index['FIELD_NAME']."'".','.$dataVerify;
						$conVerify++;
					}
					else 
					{
						if($index['ALIAS_TABLE'] != '')
							$dataSelected = $dataSelected.', '.$index['ALIAS_TABLE'].'.'.$index['FIELD_NAME'];
						else 
							$dataSelected = $dataSelected.', '.$index['FIELD_NAME'];
					}
								
			}
			$contDataSelect++;
		} 
	}
	##### End options select query
	
	##### Options data selected
	$contDataSelect = 1;

	if($dataVerify != '')
	{
		$dataSelect = "SELECT QUERY_SELECT FROM PMT_INBOX_FIELDS_SELECT WHERE ID_INBOX = '".$idInbox ."' AND ROL_CODE ='$sRol' 
					AND FIELD_NAME NOT IN ( $dataVerify ) ";
	}
	else
	{
		$dataSelect = "SELECT QUERY_SELECT FROM PMT_INBOX_FIELDS_SELECT WHERE ID_INBOX = '".$idInbox ."' AND ROL_CODE ='$sRol' 	";
	}
	$dataSelect = executeQuery($dataSelect);
	$fieldSelectQuery = '';
	if(sizeof($dataSelect))
	{
		foreach($dataSelect as $index)
		{
			if($contDataSelect == 1)
			{				
				$fieldSelectQuery =$index['QUERY_SELECT'];								
			}
			else
			{
				$fieldSelectQuery = $fieldSelectQuery.', '.$index['QUERY_SELECT'];			
			}
			$contDataSelect++;
		} 
	}
//G::pr($dataSelected);die;
	##### End options data selected
	
	//$fieldSelectQuery = '';
	##### Query and Result
	if($fieldSelectQuery != '')
	{
		$sSQL = "SELECT $fieldSelectQuery, $dataSelected FROM  $idTable $sJoins $sWhere $sOrderBy "; 
	}
	else 
	{
		$sSQL = "SELECT $dataSelected  FROM  $idTable $sJoins $sWhere $sOrderBy ";	
	}	
	$aData = executeQuery($sSQL);
	$total = sizeof($aData);
	foreach ( $aData as $index ) {
		$aDataGrid [] = $index;
	}
	$paging = array ('success' => true, 'total' => $total, 'data' => array_splice($aDataGrid,$start,$limit), 'success_req'=> 'ok');
	echo json_encode ( $paging );
} catch(Exception $e){
	$err = $e->getMessage();
	$err = preg_replace("[\n|\r|\n\r]", ' ', $err);
	$paging = array ('success' => true, 'total' => 0, 'data' => $aDataGrid, 'success_req'=> 'error', 'message' => $err);
	echo json_encode ( $paging );
}

?> 
    