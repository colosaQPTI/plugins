<?php
### changed May 10

G::LoadClass ( 'case' );
G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );
require_once ("classes/model/Users.php");
$users = $_SESSION ['USER_LOGGED'];
$Us = new Users ( );
$Roles = $Us->load ( $users );
$rolesAdmin = $Roles ['USR_ROLE'];
$oHeadPublisher = & headPublisher::getSingleton ();
$idInbox = $_GET ['idInbox'];
$array = Array ();
$queryInbox = " SELECT * FROM PMT_INBOX WHERE  INBOX = '" . $idInbox . "'  ";
$inbox = executeQuery ( $queryInbox ); 
if(sizeof($inbox)){
	
	$description = $inbox[1]['DESCRIPTION'];

	$query = "SELECT * FROM PMT_INBOX_FIELDS  
				  WHERE ROL_CODE ='" . $rolesAdmin . "' AND  ID_INBOX = '" . $idInbox . "'
				  ORDER BY POSITION ";
	$result = executeQuery ( $query );
	$i = 0;
	$conVerify = 1;
	$sw = 1;
	$dataVerify = '';
	if(sizeof($result)){
		foreach ( $result as $row ) 
		{
			if ($row ['INCLUDE_OPTION'] == '1') 
			{
				if($i == 0)
					$table = $row ['ID_TABLE'];
					
				$querySelect = "SELECT * FROM  PMT_INBOX_FIELDS_SELECT  
				  				WHERE ROL_CODE ='" . $rolesAdmin . "' AND ID_INBOX = '" . $idInbox . "' AND
				  				FIELD_NAME = '". $row ['DESCRIPTION'] ."' AND FIELDS = '".$row ['FIELD_NAME']."' AND
				  				TYPE = 'Yes' ";
				$resultSelect = executeQuery ( $querySelect );	
				if(sizeof($resultSelect))
				{
					foreach($resultSelect as $index)
					{
						if($row ['FIELD_NAME'] == 'APP_UID' )
						{
							$row ['FIELD_NAME'] == $row ['FIELD_NAME'];
							$sw = 0;
							$array [] = $row;
						}
							
						$row['FIELD_NAME'] = $index ['FIELD_NAME'];
						//$array [] = $row;
					}
					if($conVerify == 1)
							$dataVerify = "'".$index['FIELD_NAME']."'";
					else
						$dataVerify = "'".$index['FIELD_NAME']."'".','.$dataVerify;
					$conVerify++;
				}
				$array [] = $row;
				$i++;
			}
			
		}
		
		if($dataVerify != '')
		{
			$dataSelect = "SELECT * FROM PMT_INBOX_FIELDS_SELECT WHERE ID_INBOX = '".$idInbox ."' AND ROL_CODE ='$rolesAdmin' 
					AND FIELD_NAME NOT IN ( $dataVerify ) ";
		}
		else
		{
			$dataSelect = "SELECT * FROM PMT_INBOX_FIELDS_SELECT WHERE ID_INBOX = '".$idInbox ."' AND ROL_CODE ='$rolesAdmin' 
					";
		}
		$resultSelect = executeQuery($dataSelect);
		foreach($resultSelect as $index)
		{
			
			$index['DESCRIPTION'] = $index ['FIELD_NAME'];
			
			$index['HIDDEN_FIELD'] = 0;
			$index['FLD_UID'] = '';
			$index['ID_TABLE'] = '';
			$index['ALIAS_TABLE'] = '';
			$index['POSITION'] = $i;
			$index['INCLUDE_OPTION'] = 1;
			$index['INCLUDE_FILTER'] = 0;
			$index['FIELD_REPLACE'] = '';
			$index['ORDER_BY'] = '';
			$array [] = $index;
			$i++;
		}
		
		$array = delete_duplicate($array,'FIELD_NAME');
		
		####### Actions
		$queryActions = "SELECT I.ID_INBOX, I.NAME_ACTION, I.ID_PM_FUNCTION, I.PARAMETERS_FUNCTION, I.ROL_CODE, A.DESCRIPTION, A.ROWS_AFFECT
						 FROM PMT_INBOX_ACTIONS  I
						 INNER JOIN PMT_ACTIONS A ON (I.ID_ACTION = A.ID)
					  	 WHERE ROL_CODE ='" . $rolesAdmin . "' AND  ID_INBOX = '" . $idInbox . "'
					  	 ORDER BY I.POSITION
						";
		$resultActions = executeQuery ( $queryActions );

		$arrayActions = Array ();
		$arrayConditionActions = Array ();
		foreach ( $resultActions as $row ) 
		{
			$queryConditionActions = "SELECT FLD_UID, PARAMETERS_BY_FIELD, NAME_ACTION, OPERATOR
									 FROM PMT_CONDITION_BY_FIELDS
									 WHERE ROL_CODE = '" . $rolesAdmin . "' AND ID_INBOX = '" . $idInbox . "' AND 
									 NAME_ACTION = '" . $row['NAME_ACTION'] . "' AND PARAMETERS_BY_FIELD <> ''
									 ";
			$resultConditionActions = executeQuery($queryConditionActions);
			if(sizeof($resultConditionActions))
			{
				foreach($resultConditionActions as $index)
				{
					
					$arrayConditionActions [] = $index;
				}
			}
			
			$arrayActions [] = $row;
			
		}	
		##### PROCESS_UID for getDybafields
        
        ##### Check if the Table is Report or PM Table
        $tableType = "Report";
        $sqlAddTable = "SELECT PRO_UID FROM ADDITIONAL_TABLES WHERE ADD_TAB_NAME = '$table' ";
        $resAddTable=executeQuery($sqlAddTable);
        if(sizeof($resAddTable)){
	        if($resAddTable[1]['PRO_UID'] == ''){
		        $tableType = "pmTable";	    
	        }		
        }
        #####
        $sProUid = '';
        if($tableType == "Report" )
        {
		    $sSQL ="SELECT  PRO_UID FROM $table RT, APPLICATION A WHERE RT.APP_UID = A.APP_UID";
		    $aResult = executeQuery($sSQL);
		    $sProUid = '';
		    if(is_array($aResult) && isset($aResult[1]['PRO_UID'])){
			    $sProUid = $aResult[1]['PRO_UID'];
		    }
	    }

		global $RBAC;
		$filterSearch = '1';
		if($RBAC->userCanAccess("PM_FILTER_CUSTOMINBOX") != 1){
			$filterSearch = '0';
		}		
		##### 
		$oHeadPublisher->assign ( 'filterSearch', $filterSearch );		
		$oHeadPublisher->assign ( 'tableDef', $array );
		$oHeadPublisher->assign ( 'table', $table );
		$oHeadPublisher->assign ( 'nameInbox', $description );
		$oHeadPublisher->assign ( 'arrayActions', $arrayActions );
		$oHeadPublisher->assign ( 'arrayConditionActions', $arrayConditionActions );
		$oHeadPublisher->assign ( 'idInbox', $idInbox );
		$oHeadPublisher->assign ( 'proUid', $sProUid);

		$oHeadPublisher->addExtJsScript ( PATH_PLUGINS . SYS_COLLECTION . '/inboxDinamic', true, true ); 

		G::RenderPage ( 'publish', 'extJs' );
	}
	else{
		print_r("Please configure an Inbox for this role");	
	}	
}
else{
	print_r("Please configure an Inbox"); 
}

function delete_duplicate($array, $field)
{
  foreach ($array as $sub)
  {
    $cmp[] = $sub[$field];
  }
  $unique = array_unique($cmp);
  foreach ($unique as $k => $field)
  {
    $result[] = $array[$k];
  }
  return $result;
}
?>
<script type='text/javascript' src='/plugin/obladyConvergence/resize_iframe.js'></script> 
<script type='text/javascript' src='/plugin/convergenceList/js/functionsActions.js'></script> 
<script type='text/javascript' src='/extjs/app_main.js'></script>
