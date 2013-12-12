<?php 
    G::LoadClass('case');
    G::LoadClass('configuration');
    G::loadClass('pmFunctions');
        
    $start = isset($_POST['start']) ? $_POST['start'] : 0;
    $limit = isset($_POST['limit']) ? $_POST['limit'] : 50;
        $USER_UID = $_SESSION['USER_LOGGED'];
	    $where="";
	    if (isset($_GET['action']))
	    {
	    	$inboxVar = $_GET['action'];
	    	switch ($inboxVar) {
	    		case 'listAction':
					$sQuery = " SELECT *
								FROM PMT_ACTIONS
					       ";
					$aDatos = executeQuery ($sQuery);
					$array = Array();
					foreach($aDatos as $valor)
					{        
						$valor['ROWS_AFFECT_ID'] = $valor['ROWS_AFFECT'] ;
						switch($valor['ROWS_AFFECT'])
						{
							case 'multiple':
								$valor['ROWS_AFFECT'] = 'Affect Multiple row';
								break;
								
							case 'one':
								$valor['ROWS_AFFECT'] = 'Affect One row';
								break;

							case 'none':
								$valor['ROWS_AFFECT'] = 'None';
								break;
								
							case 'oneMore':
								$valor['ROWS_AFFECT'] = 'One and More';
								break;
								
							default:
								$valor['ROWS_AFFECT'] = '';
								break;
						}
						if($valor['PARAMETERS_FUNCTION'] == 'The function has no parameters')
							$valor['PARAMETERS_FUNCTION'] = '';
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
					break;

	    		case 'newAction':
	    		
	    		default:
	    			# code...
	    			break;
	    	}
	    } else {
	    	$operation = $_POST['operation'];
	    	switch ($operation) {
	    		case 'saveNewAction':
	    			
	    			$res = false;
	    			
			    	$actionName = $_POST['action'];
			    	$actionDescription = $_POST['description'];
			    	$pmFunction = $_POST['pmFunction'];
			    	$parametersFunction = $_POST['parametersFunction'];
			    	$pluginName = $_POST['pluginName'];
					$rowsAffect = $_POST['rowsAffect'];
			    	
			    	$qVerification = "SELECT NAME FROM PMT_ACTIONS WHERE NAME = '$actionName'";
					$selectVerification = executeQuery($qVerification);
					
					if(count($selectVerification) == 0)
					{
						$sQuery = " INSERT INTO 
										PMT_ACTIONS (
											NAME, 
											DESCRIPTION,
											PM_FUNCTION,
											PARAMETERS_FUNCTION,
											NAME_PLUGIN,
											ROWS_AFFECT)
										VALUES (
											'$actionName', 
											'".mysql_real_escape_string($actionDescription)."',
											'$pmFunction',
											'$parametersFunction',
											'$pluginName',
											'$rowsAffect'
										)";
						$aDatos = executeQuery ($sQuery);
						$res = true;
					}
					
					echo json_encode($res);
					
	    			break;
	    		
	    		case 'saveEditAction':
	    			$id = $_POST['ID'];
	    			$actionName = $_POST['actionName'];
			    	$actionDescription = $_POST['actionDescription'];
			    	$pmFunction = $_POST['pmFunction'];
			    	$parametersFunction = $_POST['parametersFunction'];
			    	$rowsAffect = $_POST['rowsAffect'];
			    	
	    			$sQuery = " UPDATE PMT_ACTIONS 
					       SET
					       NAME = '$actionName', 
					       DESCRIPTION = '".mysql_real_escape_string($actionDescription)."',
					       PM_FUNCTION = '$pmFunction',
					       PARAMETERS_FUNCTION = '$parametersFunction',
					       ROWS_AFFECT = '$rowsAffect'
					       WHERE ID = '$id'
					       ";
					$aDatos = executeQuery ($sQuery); 	    
					$res = true;
					header("Content-Type: text/plain");

					$paging = array('success' => $res );  

					echo json_encode($paging);
					break;
					
	    		case 'deleteAction':
			    	$id = $_POST['ID'];
			    	// Delete relation inbox action
			    	$qInboxAction = "DELETE FROM PMT_INBOX_ACTIONS WHERE ID_ACTION = $id ";
			    	$delInboxAction = executeQuery($qInboxAction);
			    	
					$sQuery = "DELETE FROM PMT_ACTIONS WHERE ID = $id ";
					$aDatos = executeQuery ($sQuery);	    
	    			break;

	    		default:
	    			# code...
	    			break;
	    	}
	    	
			$array = Array();
	    }
?>
