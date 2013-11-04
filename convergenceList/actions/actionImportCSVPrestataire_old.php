<?php
ini_set('memory_limit','512M');
ini_set('max_execution_time','0');
ini_set('max_input_time', '-1');
## (c) req - last change May 23
G::loadClass ( 'pmFunctions' );
G::LoadClass("form");
// memory_limit max_execution_time

/*function getProUid($tableName){
    $sSQL ="SELECT * FROM ADDITIONAL_TABLES WHERE ADD_TAB_NAME ='$tableName'";
    $aResult= executeQuery($sSQL);
    $proUid = '0';
    if(is_array($aResult) && count($aResult)>0){$proUid =$aResult[1]['PRO_UID'];} 
    return $proUid;
}

function getDynaformFields($jsonFieldsCSV,$tableName) {

    require_once PATH_CONTROLLERS . 'pmTablesProxy.php';
    G::LoadClass('reportTables');
    $proUid = getProUid($tableName);
    $oReportTables = new pmTablesProxy();
    $dynFields = array();
    $dynFields = $oReportTables->_getDynafields($proUid, 'xmlform', 0,10000, null);
    $aDynFields = array();
    foreach ($dynFields['rows'] as $row) {      
        $aDynFields[strtoupper($row['FIELD_NAME'])] = $row['FIELD_NAME'];
    }
    $_dataFields =  array();
    foreach ($aDynFields as $key => $value) {
        $record = array("FIELD_NAME" => $value, "FIELD_DESC" => $key, "COLUMN_CSV" => 'Select...');
        $_dataFields[] = $record;
    }
  return (array(sizeof($_dataFields), array_values($_dataFields)));
}

function getDataCSV($firstLineCsvAs = 'on'){

    set_include_path(PATH_PLUGINS . 'convergenceList' . PATH_SEPARATOR . get_include_path());
    require_once 'classes/class.parseCSV.php';
    $csv = new parseCSV();
    $csv->heading  = ($firstLineCsvAs == 'on')? true:false;
    $csv->auto($_FILES['form']['tmp_name']['CSV_FILE']);
    $data = $csv->data;
    $_SESSION['REQ_DATA_CSV'] = $data;
    return $data;
}

function importCreateCase($jsonMatchFields,$uidTask, $tableName,$firstLineHeader){

    G::LoadClass('case');
    $items = json_decode($jsonMatchFields, true);
    
    $dataCSV = isset($_SESSION['REQ_DATA_CSV']) ? $_SESSION['REQ_DATA_CSV'] : array();
    $USR_UID = $_SESSION['USER_LOGGED'];
    $_SESSION['USER_LOGGED_INI'] = $USR_UID;
    $proUid  = getProUid($tableName);
    $totalCases = 0;

    // load Dynaforms of process
	$select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM DYNAFORM WHERE PRO_UID = '".$proUid ."'";
		$resultDynaform = executeQuery($select);
		
		$_dataForms =  array();
		foreach($resultDynaform As $rowDynaform)
		{
			$dynaform = new Form($proUid . PATH_SEP . $rowDynaform['DYN_UID'], PATH_DYNAFORM , SYS_LANG , false);
			foreach ($dynaform->fields as $fieldName => $field) {
				if( $field->type == 'dropdown')
				{
					$record = array (
							"FIELD_NAME" => $field->name, 
							"FIELD_LABEL" => $field->label,
							"FIELD_TYPE" => $field->type,
							"FIELD_DEFAULT_VALUE" => $field->defaultValue,
							"FIELD_DEPENDENT_FIELD" => $field->dependentFields,
							"FIELD_OPTION" => $field->option,
							"FIELD_READONLY" => $field->readonly,
							"FIELD_OPTION" => $field->option,
							"FIELD_SQL_CONNECTION" => $field->sqlConnection,
							"FIELD_SQL" => $field->sql,
							"FIELD_SQL_OPTION" => $field->sqlOption,
							"FIELD_SELECTED_VALUE" => $field->selectedValue,
							"FIELD_SAVE_LABEL" => $field->saveLabel
					);
					$_dataForms[] = $record;
				}
			}
		}
		  
    foreach ($dataCSV as $row) {
        $appData =  array();
        foreach ($items as $field) { 
            if($firstLineHeader == 'on'){
            	
                if(isset($row[$field['COLUMN_CSV']]))
                {
                	if($row[$field['COLUMN_CSV']])
                		$appData[$field['FIELD_NAME']] = utf8_encode($row[$field['COLUMN_CSV']]);
                	else
                		$appData[$field['FIELD_NAME']] = ' ';
                }
                else
                {
                	if($field['COLUMN_CSV'])
                    	$appData[$field['FIELD_NAME']] = utf8_encode($field['COLUMN_CSV']);
                    else
                    	$appData[$field['FIELD_NAME']] = ' ';
                } 
            }
            else
            {
                $aCol = explode(' ', trim($field['COLUMN_CSV']));
                if( (isset($aCol[0]) && trim($aCol[0]) == 'Column' ) &&  ( isset($aCol[1]) && isset($row[$aCol[1]]) ) )
                    $appData[$field['FIELD_NAME']] = utf8_encode($row[$aCol[1]]);
                else if ( ( isset($aCol[0])  &&  trim($aCol[0]) != 'Column' )  ){
                    $appData[$field['FIELD_NAME']] =  utf8_encode($field['COLUMN_CSV']);
                }        
            }
        }       
        $appData['FLAG_ACTION'] = 'multipleDerivation';
        $appData['EXEC_AUTO_DERIVATE'] = 'NO';
       // $appData['eligible'] = 0; // only process beneficiary
        $appData['FLAG_EDIT'] = 1;
        $appData['CurrentUserAutoDerivate'] = $USR_UID;       
        
  //si $appData[$field['SIRET']] existe  load et update sinon newcase
  		$appData['SIRET'] = isset($appData['SIRET'])?$appData['SIRET']:'';
        $query = "SELECT APP_UID FROM PMT_PRESTATAIRE WHERE STATUT = 1 AND SIRET ='".$appData['SIRET']."'";
        $result= executeQuery($query);
        //G::pr($row);
        $caseUID = '0';
        if(is_array($result) && count($result)>0){
            $caseUID =$result[1]['APP_UID'];                    
            $oCase = new Cases ();     
            $FieldsCase = $oCase->loadCase ( $caseUID );  
            $FieldsCase['APP_DATA'] = array_merge($FieldsCase['APP_DATA'],$appData);
            $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];
            $FieldsCase['APP_DATA']['STATUT'] = 1;
            $oCase->updateCase($caseUID,$FieldsCase);
        }else{
        	    
	        foreach($appData As $key => $fields)
	        {
		        foreach ($_dataForms As $row)
				{
					if($row['FIELD_DEFAULT_VALUE'] == '')
						$row['FIELD_DEFAULT_VALUE'] = 0;
					
					if($key == $row['FIELD_NAME'])
					{
						$i = isset($fields[$key])?$fields[$key]:$row['FIELD_DEFAULT_VALUE'];	
					
						if($row['FIELD_SQL_CONNECTION'] != 0)
						{
							if(count($row['FIELD_SQL_OPTION']))
							{
								$label = isset($row['FIELD_SQL_OPTION'][$i])?$row['FIELD_SQL_OPTION'][$i]:$row['FIELD_SQL_OPTION'][1];
								$appData[$row['FIELD_NAME']] = isset($appData[$row['FIELD_NAME']])?$appData[$row['FIELD_NAME']]:$i; 
								$appData[$row['FIELD_NAME']."_LABEL"] = $label;
							}
						}
						else
						{
							if(count($row['FIELD_OPTION']))
							{
								$label = isset($row['FIELD_OPTION'][$i])?$row['FIELD_OPTION'][$i]:$row['FIELD_OPTION'][1];
								$appData[$row['FIELD_NAME']] = isset($appData[$row['FIELD_NAME']])?$appData[$row['FIELD_NAME']]:$i;
								$appData[$row['FIELD_NAME']."_LABEL"] = $label;
							}
						}
					}
					else
					{
						$i = $row['FIELD_DEFAULT_VALUE'];	
						
						if($row['FIELD_SQL_CONNECTION'] != 0)
						{
							if(count($row['FIELD_SQL_OPTION']))
							{
								$label = isset($row['FIELD_SQL_OPTION'][$i])?$row['FIELD_SQL_OPTION'][$i]:$row['FIELD_SQL_OPTION'][1];
								$appData[$row['FIELD_NAME']] = isset($appData[$row['FIELD_NAME']])?$appData[$row['FIELD_NAME']]:$i; 
								$appData[$row['FIELD_NAME']."_LABEL"] = $label;
							}
						}
						else
						{
							if(count($row['FIELD_OPTION']))
							{
								if(isset($row['FIELD_OPTION'][$i]))
									$label = $row['FIELD_OPTION'][$i];
								else
								{
									$label = $row['FIELD_OPTION'][1];
									$i = 1;
								}
									
								$appData[$row['FIELD_NAME']] = $i;
								$appData[$row['FIELD_NAME']."_LABEL"] = $label;
							}
						}
					}
				}	
	        }
	        
	        $caseUID = PMFNewCase($proUid, $USR_UID, $uidTask, $appData);        
            if($caseUID >0) {
				
            	$_SESSION['APPLICATION'] = $caseUID;
		    
            	autoDerivate($proUid,$caseUID,$USR_UID);
	            $oCase = new Cases ();
	            $FieldsCase = $oCase->loadCase ( $caseUID );
	            $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];
	            $FieldsCase['APP_DATA']['STATUT'] = 1;
	            $oCase->updateCase($caseUID,$FieldsCase);
            }
        }     
        /*modify FRED NUM DOSSIER */
        // G::LoadClass('case');
        /*
        $caseInstance = new Cases();
        $newFields = $caseInstance->loadCase ($caseUID);
        $selectAppNumber = "SELECT APP_NUMBER FROM APPLICATION WHERE APP_UID = '".$caseUID."' ";
        $dataAppNumber = executeQuery($selectAppNumber);
        //$qtemp = 'SELECT NUM_DOSSIER FROM PMT_EIES WHERE NOM = "'.$appData['EIE_label'].'" AND STATUT = 1 ';
        //$rtemp = executeQuery($qtemp);
        $numDossier = $dataAppNumber[1]['APP_NUMBER'];
        $newFields['APP_DATA']['NUM_DOSSIER'] = $numDossier;
        $newFields['APP_DATA']['STATUT'] = 1;
        //$newFields['APP_DATA']['EIE'] = $rtemp[1]['NUM_DOSSIER'];
        PMFSendVariables($caseUID, $newFields['APP_DATA']);		    
        $caseInstance->updateCase($caseUID, $newFields);
        /* FIN modify FRED NUM DOSSIER */
 /*       $totalCases++;

    }
    unset($_SESSION['REQ_DATA_CSV']);
    return $totalCases;
}*/

try {
  $sOption = $_REQUEST["option"];
  switch ($sOption) {
    case "getDataCSV": 
                $firstLineCsvAs = (isset($_REQUEST['form']['FIRSTLINE_ISHEADER']))?$_REQUEST['form']['FIRSTLINE_ISHEADER']:'on';
                $response = getDataCSV($firstLineCsvAs);
                echo G::json_encode(array("success" => true, "data" => $response));
                break;

    case "getDataMatch":
                $fieldsCSV  = isset($_REQUEST["fieldsCSV"])?$_REQUEST["fieldsCSV"]:'';
                $tableName  = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
                list($dataNum, $data) = getDynaformFields($fieldsCSV,$tableName );
                echo G::json_encode(array("success" => true, "total" => $dataNum, "data" => $data));
                break;
                
    case "importCreateCase":
                $matchFields = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
                $uidTask     = isset($_REQUEST["uidTask"])?$_REQUEST["uidTask"]:'';
                $tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
                $firstLineHeader   = isset($_REQUEST["firstLineHeader"])?$_REQUEST["firstLineHeader"]:'on';
                $totalCases = importCreateCase($matchFields,$uidTask,$tableName,$firstLineHeader);
                echo G::json_encode(array("success" => true, "message" => "OK" , "totalCases" => $totalCases));
  }

} catch (Exception $e) {
    $err = $e->getMessage();
    //mail('nicolas@oblady.fr', 'debug $err mail ', var_export($err, true));
    $err = preg_replace("[\n|\r|\n\r]", ' ', $err);
    $paging = array ('success' => false, 'total' => 0, 'data' => array(), 'success_req'=> 'error', 'message' => $err);
    echo json_encode ( $paging );
}

?>