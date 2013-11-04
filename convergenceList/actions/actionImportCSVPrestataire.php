<?php
ini_set('memory_limit','512M');
ini_set('max_execution_time','0');

G::loadClass ( 'pmFunctions' );
G::LoadClass("form");


/*function getProUid($tableName){
    $sSQL ="SELECT * FROM ADDITIONAL_TABLES WHERE ADD_TAB_NAME ='$tableName'";
    $aResult= executeQuery($sSQL);
    $proUid = '0';
    if(is_array($aResult) && count($aResult)>0){$proUid =$aResult[1]['PRO_UID'];} 
    return $proUid;
}
function getRolUserImport(){
	require_once ("classes/model/Users.php");
    $oUser = new Users();
    $oDetailsUser = $oUser->load ($_SESSION ['USER_LOGGED']);
    return $oDetailsUser['USR_ROLE'];
}

function genDataReport ($tableName){
    G::loadClass( 'pmTable' );
    G::loadClass ( 'pmFunctions' );
    require_once 'classes/model/AdditionalTables.php';
    
    $tableType = "Report";
   
    // Check if the Table is Report or PM Table

    $sqlAddTable = "SELECT * FROM ADDITIONAL_TABLES WHERE ADD_TAB_NAME = '$tableName' ";
    $resAddTable=executeQuery($sqlAddTable);
    if(sizeof($resAddTable)){
	    if($resAddTable[1]['PRO_UID'] == ''){
		    $tableType = "pmTable";	    
	    }		
    }
    if($tableType == "Report" )
    {
        $cnn = Propel::getConnection('workflow');
	    $stmt = $cnn->createStatement();
        $additionalTables = new AdditionalTables(); 
        $oPmTable = $additionalTables->loadByName($tableName);
        $table 	  = $additionalTables->load($oPmTable['ADD_TAB_UID']);
        if ($table['PRO_UID'] != '') {
    	    $truncateRPTable = "TRUNCATE TABLE  ".$tableName." ";
	        $rs = $stmt->executeQuery($truncateRPTable, ResultSet::FETCHMODE_NUM);   
	        $additionalTables->populateReportTable( $table['ADD_TAB_NAME'], pmTable::resolveDbSource( $table['DBS_UID'] ), $table['ADD_TAB_TYPE'], $table['PRO_UID'], $table['ADD_TAB_GRID'], $table['ADD_TAB_UID'] ); 
        }
    }
}

function deletePMCases($caseId) {
	
	$query1="DELETE FROM wf_".SYS_SYS.".APPLICATION WHERE APP_UID='".$caseId."' ";
	$apps1=executeQuery($query1);
	$query2="DELETE FROM wf_".SYS_SYS.".APP_DELAY WHERE APP_UID='".$caseId."'";
	$apps2=executeQuery($query2);
	$query3="DELETE FROM wf_".SYS_SYS.".APP_DELEGATION WHERE APP_UID='".$caseId."'";
	$apps3=executeQuery($query3);
	$query4="DELETE FROM wf_".SYS_SYS.".APP_DOCUMENT WHERE APP_UID='".$caseId."'";
	$apps4=executeQuery($query4);
	$query5="DELETE FROM wf_".SYS_SYS.".APP_MESSAGE WHERE APP_UID='".$caseId."'";
	$apps5=executeQuery($query5);
	$query6="DELETE FROM wf_".SYS_SYS.".APP_OWNER WHERE APP_UID='".$caseId."'";
	$apps6=executeQuery($query6);
	$query7="DELETE FROM wf_".SYS_SYS.".APP_THREAD WHERE APP_UID='".$caseId."'";
	$apps7=executeQuery($query7);
	$query8="DELETE FROM wf_".SYS_SYS.".SUB_APPLICATION WHERE APP_UID='".$caseId."'";
	$apps8=executeQuery($query8);
	$query9="DELETE FROM wf_".SYS_SYS.".CONTENT WHERE CON_CATEGORY LIKE 'APP_%' AND CON_ID='".$caseId."'";
	$apps9=executeQuery($query9);	
	$query10="DELETE FROM wf_".SYS_SYS.".APP_EVENT WHERE APP_UID='".$caseId."'";
	$apps10=executeQuery($query10);
	$query11="DELETE FROM wf_".SYS_SYS.".APP_CACHE_VIEW WHERE APP_UID='".$caseId."'";
	$apps11=executeQuery($query11);
	$query12="DELETE FROM wf_".SYS_SYS.".APP_HISTORY WHERE APP_UID='".$caseId."'";
	$apps12=executeQuery($query12);
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
}*/
/*function getConfigCSV($data,$idInbox){
	
	$rolUser= getRolUserImport();
	$query = "SELECT * FROM PMT_CONFIG_CSV_IMPORT WHERE ROL_CODE = '".$rolUser."' AND ID_INBOX = '".$idInbox."'";
	
	$aData = executeQuery($query);
	if(sizeof($aData))
	{
		for($i = 0; $i < count($data); $i++)
			{
				foreach($aData As $key => $row)
				{
					if($data[$i]['FIELD_NAME'] == $row['CSV_FIELD_NAME'])
					{
						$data[$i]['COLUMN_CSV'] = $row['CSV_COLUMN'];
					}
					
				}
			}
	}
	return $data;
	
}

function importCreateCase($jsonMatchFields,$uidTask, $tableName,$firstLineHeader){

    G::LoadClass('case');
    $items   = json_decode($jsonMatchFields,true); 
    $dataCSV = isset($_SESSION['REQ_DATA_CSV']) ?$_SESSION['REQ_DATA_CSV']: array();
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
					$aData = array();
					$dataSQL = array();
					$data = array();
					if(strlen($field->sql))
					{
						$query = $field->sql;
						$valueData = explode(",",$query); 
						$valueId = explode(" ",$valueData[0]);
						$position = count($valueId)-1 ;
						$valueId = $valueId[$position];
						$valueDataCount = count($valueData);
						$valueName = explode(" ",$valueData[$valueDataCount-1]);
						for($i = 0; $i <count($valueName) ; $i++)
						{
							if($valueName[$i]=="from" || $valueName[$i]=="FROM")
							{
								$dataName = $valueName[$i-1];
								break;		
							}
						}
						
						$aData = executeQuery($field->sql);
					}	
					if(sizeof($aData))
					{
						foreach($aData As $key => $row)
						{
							$rowData = array ( 'id'=>$row[$valueId],'descrip'=>$row[$dataName]);
							$dataSQL[] = $rowData;
						}
					}
					
					if(sizeof($field->option))
					{
						foreach($field->option As $key => $row)
						{
							$rowData = array ( 'id'=>$key,'descrip'=>$row);
							$data[] = $rowData;
						}
					}
					
					$record = array (
							"FIELD_NAME" => $field->name, 
							"FIELD_LABEL" => $field->label,
							"FIELD_TYPE" => $field->type,
							"FIELD_DEFAULT_VALUE" => $field->defaultValue,
							"FIELD_DEPENDENT_FIELD" => $field->dependentFields,
							"FIELD_OPTION" => $data,
							"FIELD_READONLY" => $field->readonly,
							"FIELD_SQL_CONNECTION" => $field->sqlConnection,
							"FIELD_SQL" => $field->sql,
							"FIELD_SQL_OPTION" => $dataSQL,
							"FIELD_SELECTED_VALUE" => $field->selectedValue,
							"FIELD_SAVE_LABEL" => $field->saveLabel
					);
					$_dataForms[] = $record;
				}
			}
		}
		
	$select = executeQuery("SELECT MAX(IMPCSV_IDENTIFY) AS IDENTIFY FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_TABLE_NAME = '$tableName'");
    $identify = isset($select[1]['IDENTIFY'])? $select[1]['IDENTIFY']:0;
    $identify = $identify + 1;
    $csv_file = $tableName."_".$identify.".csv";  
	$csv_sep = ";";  
	$csv="";  
    $csv_end = "\n";
    $swInsert = 0;
    	
    foreach ($dataCSV as $row) 
    {
        $totRow = sizeof($row);
        $totIni = 1;
        if($totalCases >= 150)
        {
            foreach($row as $value)
            {
                if($totIni == $totRow)
                $csv.=$value;
                else
                    $csv.=$value.$csv_sep;
                $totIni++;
            }
            $csv.=$csv_end;
            if($swInsert == 0)
            {
                $select = executeQuery("SELECT MAX(IMPCSV_ID) AS ID_CSV FROM PMT_IMPORT_CSV_DATA");
                $maxId = isset($select[1]['ID_CSV'])? $select[1]['ID_CSV']:0;
                $maxId = $maxId + 1;
                foreach ($items as $field)
                { 
                    $insert = "INSERT INTO PMT_IMPORT_CSV_DATA 
                          (IMPCSV_ID, IMPCSV_FIELD_NAME, IMPCSV_VALUE,IMPCSV_TAS_UID, IMPCSV_TABLE_NAME, IMPCSV_FIRSTLINEHEADER, IMPCSV_IDENTIFY, IMPCSV_TYPE_ACTION) VALUES
                          ('$maxId','".$field['FIELD_NAME']."', '".$field['COLUMN_CSV']."', '$uidTask', '$tableName','$firstLineHeader', '$identify', 'ADD')";
                    executeQuery($insert);
                    $swInsert = 1;
                    $maxId++;
                }
            }
            
        }
        else
        {
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
            $appData['eligible'] = 0; // only process beneficiary
            $appData['FLAG_EDIT'] = 1;
            $appData['CurrentUserAutoDerivate'] = $USR_UID;       
        
  		    $appData['SIRET'] = isset($appData['SIRET'])?$appData['SIRET']:'';
            $query = "SELECT APP_UID FROM PMT_PRESTATAIRE WHERE STATUT = 1 AND SIRET ='".$appData['SIRET']."'";
            $result= executeQuery($query);
            
            $caseUID = '0';
        
             // labels //
        
	        foreach($appData As $key => $fields)
	        {
		        foreach ($_dataForms As $row)
				{
					if($row['FIELD_DEFAULT_VALUE'] == '')
						$row['FIELD_DEFAULT_VALUE'] = 0;
					
					if($key == $row['FIELD_NAME'])
					{
						$i = isset($fields)?$fields:$row['FIELD_DEFAULT_VALUE'];
						
						if(count($row['FIELD_SQL_OPTION']))
						{
							$options = $row['FIELD_SQL_OPTION'];
							$id = "";
							$label = "";
							foreach($options As $row2)
							{
								if($row2['id'] == $i)
								{
									$id = $row2['id'];
									$label = $row2['descrip'];
									break;
								}
							}
							
							if($id=="" && $label=="")
							{
								$id = $row['FIELD_SQL_OPTION'][0]['id'];
								$label = $row['FIELD_SQL_OPTION'][0]['descrip'];
							}
							
							$record[$row['FIELD_NAME']] = $id;
							$appData = array_merge($record,$appData);
							$record[$row['FIELD_NAME']."_label"] = $label;
							$appData = array_merge($record,$appData);
						}
						else
						{
							if(count($row['FIELD_OPTION']))
							{
								$options = $row['FIELD_OPTION'];
								$id = "";
								$label = "";
								foreach($options As $row2)
								{
									if($row2['id'] == $i)
									{
										$id = $row2['id'];
										$label = $row2['descrip'];
										break;
									}
								}
								
								$record = Array();
								
								$record[$row['FIELD_NAME']] = $id;
								$appData = array_merge($record,$appData);
								$record[$row['FIELD_NAME']."_label"] = $label;
								$appData = array_merge($record,$appData);
								
							}
						}
					}
				}	
	        }
	         
	        foreach($appData As $key => $fields)
	        {
	        	foreach ($_dataForms As $row)
				{
					if($row['FIELD_DEFAULT_VALUE'] == '')
						$row['FIELD_DEFAULT_VALUE'] = 0;
						
					$appData[$row['FIELD_NAME']."_label"] = isset($appData[$row['FIELD_NAME']."_label"])?$appData[$row['FIELD_NAME']."_label"]:'';
					
					if($appData[$row['FIELD_NAME']."_label"] =="")
					{
						$i = $row['FIELD_DEFAULT_VALUE'];	
						if(count($row['FIELD_SQL_OPTION']))
						{
							
							$options = $row['FIELD_SQL_OPTION'];
							$id = "";
							$label = "";
							foreach($options As $row2)
							{
								if($row2['id'] == $i)
								{
									$id = $row2['id'];
									$label = $row2['descrip'];
									break;
								}
							}
							
							if($id=="" && $label=="")
							{
								$id = $row['FIELD_SQL_OPTION'][0]['id'];
								$label = $row['FIELD_SQL_OPTION'][0]['descrip'];
							}
							
							$record[$row['FIELD_NAME']] = $id;
							$appData = array_merge($record,$appData);
							$record[$row['FIELD_NAME']."_label"] = $label;
							$appData = array_merge($record,$appData);
							
						}
						else
						{
							if(count($row['FIELD_OPTION']))
							{
								$options = $row['FIELD_OPTION'];
								$id = "";
								$label = "";
								foreach($options As $row2)
								{
									if($row2['id'] == $i)
									{
										$id = $row2['id'];
										$label = $row2['descrip'];
										break;
									}
								}
								
								if($id=="" && $label=="")
								{
									$id = $row['FIELD_OPTION'][0]['id'];
									$label = $row['FIELD_OPTION'][0]['descrip'];
								}
								
								$record[$row['FIELD_NAME']] = $id;
								$appData = array_merge($record,$appData);
								$record[$row['FIELD_NAME']."_label"] = $label;
								$appData = array_merge($record,$appData);
								
							}
						}
					}
				}
	        }
        //G::pr($appData);die; 
        // end labels //
   
            if(is_array($result) && count($result)>0){
                $caseUID =$result[1]['APP_UID'];                    
                $oCase = new Cases ();     
                $FieldsCase = $oCase->loadCase ( $caseUID );  
                $FieldsCase['APP_DATA'] = array_merge($FieldsCase['APP_DATA'],$appData);
                $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];
                $FieldsCase['APP_DATA']['STATUT'] = 1;
                $oCase->updateCase($caseUID,$FieldsCase);
            
            }else{
            	
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
        }
        $totalCases++;
    }
    
    # create file tmp   
    if($csv != '')
    {
        $sPathName = PATH_DOCUMENT . "csvTmp" ;
        if (!is_dir($sPathName)) 
       	    G::verifyPath($sPathName, true);
        if (!$handle = fopen($sPathName."/".$csv_file, "w")) {  
           echo "Cannot open file";  
           exit;  
        }  
        if (fwrite($handle, utf8_decode($csv)) === FALSE) {  
           echo "Cannot write to file";  
           exit;  
        }  
        fclose($handle);  
    }
    # end create file tmp
    
    unset($_SESSION['REQ_DATA_CSV']);
    return $totalCases;
}

function importCreateCaseDelete($jsonMatchFields,$uidTask, $tableName,$firstLineHeader,$dataDeleteEdit)
{
	G::LoadClass('case');
    $items   = json_decode($jsonMatchFields,true);
    $dataCSV = isset($_SESSION['REQ_DATA_CSV']) ?$_SESSION['REQ_DATA_CSV']: array();
    $USR_UID = $_SESSION['USER_LOGGED'];
    $_SESSION['USER_LOGGED_INI'] = $USR_UID;
    $proUid  = getProUid($tableName);
    $totalCases = 0;
    $itemsDeleteEdit   = json_decode($dataDeleteEdit,true);
	// load Dynaforms of process
	$select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM DYNAFORM WHERE PRO_UID = '".$proUid ."'";
	$resultDynaform = executeQuery($select);
	$idCasesGenerate = "''";	
	$_dataForms =  array();
	foreach($resultDynaform As $rowDynaform)
	{
		$dynaform = new Form($proUid . PATH_SEP . $rowDynaform['DYN_UID'], PATH_DYNAFORM , SYS_LANG , false);
			
		foreach ($dynaform->fields as $fieldName => $field) {
			if( $field->type == 'dropdown')
			{
				$aData = array();
				$dataSQL = array();
				$data = array();
				if(strlen($field->sql))
				{
					$query = $field->sql;
					$valueData = explode(",",$query);
					$valueId = explode(" ",$valueData[0]);
					$position = count($valueId)-1 ;
					$valueId = $valueId[$position];
					$valueDataCount = count($valueData);
					$valueName = explode(" ",$valueData[$valueDataCount-1]);
					for($i = 0; $i <count($valueName) ; $i++)
					{
						if($valueName[$i]=="from" || $valueName[$i]=="FROM")
						{
							$dataName = $valueName[$i-1];	
						}
					}
					
					$aData = executeQuery($field->sql);
				}	
				if(sizeof($aData))
				{
					foreach($aData As $key => $row)
					{
						$rowData = array ( 'id'=>$row[$valueId],'descrip'=>$row[$dataName]);
						$dataSQL[] = $rowData;
					}
				}
					
				if(sizeof($field->option))
				{
					foreach($field->option As $key => $row)
					{
						$rowData = array ( 'id'=>$key,'descrip'=>$row);
						$data[] = $rowData;
					}
				}
					
				$record = array (
							"FIELD_NAME" => $field->name, 
							"FIELD_LABEL" => $field->label,
							"FIELD_TYPE" => $field->type,
							"FIELD_DEFAULT_VALUE" => $field->defaultValue,
							"FIELD_DEPENDENT_FIELD" => $field->dependentFields,
							"FIELD_OPTION" => $data,
							"FIELD_READONLY" => $field->readonly,
							"FIELD_SQL_CONNECTION" => $field->sqlConnection,
							"FIELD_SQL" => $field->sql,
							"FIELD_SQL_OPTION" => $dataSQL,
							"FIELD_SELECTED_VALUE" => $field->selectedValue,
							"FIELD_SAVE_LABEL" => $field->saveLabel
						);
				$_dataForms[] = $record;
			}
		}
	}

	$select = executeQuery("SELECT MAX(IMPCSV_IDENTIFY) AS IDENTIFY FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_TABLE_NAME = '$tableName'");
    $identify = isset($select[1]['IDENTIFY'])? $select[1]['IDENTIFY']:0;
    $identify = $identify + 1;
    $csv_file = $tableName."_".$identify.".csv";  
	$csv_sep = ";";  
	$csv="";  
    $csv_end = "\n";
    $swInsert = 0;
    $whereDelete = '';
    foreach ($dataCSV as $row) 
    {
        
        if($totalCases >= 150)
        {
            foreach($row as $value)
            {
                if($totIni == $totRow)
                $csv.=$value;
                else
                    $csv.=$value.$csv_sep;
                $totIni++;
            }
            $csv.=$csv_end;
            if($swInsert == 0)
            {
                $select = executeQuery("SELECT MAX(IMPCSV_ID) AS ID_CSV FROM PMT_IMPORT_CSV_DATA");
                $maxId = isset($select[1]['ID_CSV'])? $select[1]['ID_CSV']:0;
                $maxId = $maxId + 1;
                foreach ($items as $field)
                { 
                    $insert = "INSERT INTO PMT_IMPORT_CSV_DATA 
                          (IMPCSV_ID, IMPCSV_FIELD_NAME, IMPCSV_VALUE,IMPCSV_TAS_UID, IMPCSV_TABLE_NAME, IMPCSV_FIRSTLINEHEADER, IMPCSV_IDENTIFY, IMPCSV_TYPE_ACTION, IMPCSV_CONDITION_ACTION ) VALUES
                          ('$maxId','".$field['FIELD_NAME']."', '".$field['COLUMN_CSV']."', '$uidTask', '$tableName','$firstLineHeader', '$identify', 'ADD_DELETE', '".mysql_escape_string($dataDeleteEdit)."' )";
                    executeQuery($insert);
                    $swInsert = 1;
                    $maxId++;
                }
            }
            
        }
        else
        {
            $appData =  array();
            foreach ($items as $field) 
            { 
                if($firstLineHeader == 'on')
                {
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
		
            foreach($appData As $key => $fields)
	        {
		    	foreach ($_dataForms As $row)
		    	{
		    		if($row['FIELD_DEFAULT_VALUE'] == '')
		    			$row['FIELD_DEFAULT_VALUE'] = 0;
		    			
		    		if($key == $row['FIELD_NAME'])
		    		{
		    			$i = isset($fields)?$fields:$row['FIELD_DEFAULT_VALUE'];
		    				
		    			if(count($row['FIELD_SQL_OPTION']))
		    			{
		    				$options = $row['FIELD_SQL_OPTION'];
		    				$id = "";
		    				$label = "";
		    				foreach($options As $row2)
		    				{
		    					if($row2['id'] == $i)
		    					{
		    						$id = $row2['id'];
		    						$label = $row2['descrip'];
		    						break;
		    					}
		    				}
		    					
		    				if($id=="" && $label=="")
		    				{
		    					$id = $row['FIELD_SQL_OPTION'][0]['id'];
		    					$label = $row['FIELD_SQL_OPTION'][0]['descrip'];
		    				}
		    				
		    				$record[$row['FIELD_NAME']] = $id;
		    				$appData = array_merge($record,$appData);
		    				$record[$row['FIELD_NAME']."_label"] = $label;
		    				$appData = array_merge($record,$appData);
		    			}
		    			else
		    			{
		    				if(count($row['FIELD_OPTION']))
		    				{
		    					$options = $row['FIELD_OPTION'];
		    					$id = "";
		    					$label = "";
		    					foreach($options As $row2)
		    					{
		    						if($row2['id'] == $i)
		    						{
		    							$id = $row2['id'];
		    							$label = $row2['descrip'];
		    							break;
		    						}
		    					}
		    					
		    					$record = Array();
		    					$record[$row['FIELD_NAME']] = $id;
		    					$appData = array_merge($record,$appData);
		    					$record[$row['FIELD_NAME']."_label"] = $label;
		    					$appData = array_merge($record,$appData);
		    				}
		    			}
		    		}
		    	}	
		    }
		    $whereDelete = '';
	        foreach($appData As $key => $fields)
	        {
	        	foreach ($_dataForms As $row)
		    	{
		    		if($row['FIELD_DEFAULT_VALUE'] == '')
		    			$row['FIELD_DEFAULT_VALUE'] = 0;
		    				
		    		$appData[$row['FIELD_NAME']."_label"] = isset($appData[$row['FIELD_NAME']."_label"])?$appData[$row['FIELD_NAME']."_label"]:'';
		    		if($appData[$row['FIELD_NAME']."_label"] =="")
		    		{
		    			$i = $row['FIELD_DEFAULT_VALUE'];	
		    			if(count($row['FIELD_SQL_OPTION']))
		    			{
		    				$options = $row['FIELD_SQL_OPTION'];
		    				$id = "";
		    				$label = "";
		    				foreach($options As $row2)
		    				{
		    					if($row2['id'] == $i)
		    					{
		    						$id = $row2['id'];
		    						$label = $row2['descrip'];
		    						break;
		    					}
		    				}
		    					
		    				if($id=="" && $label=="")
		    				{
		    					$id = $row['FIELD_SQL_OPTION'][0]['id'];
		    					$label = $row['FIELD_SQL_OPTION'][0]['descrip'];
		    				}
		    				$record[$row['FIELD_NAME']] = $id;
		    				$appData = array_merge($record,$appData);
		    				$record[$row['FIELD_NAME']."_label"] = $label;
		    				$appData = array_merge($record,$appData);
		    			}
		    			else
		    			{
		    				if(count($row['FIELD_OPTION']))
		    				{
		    					$options = $row['FIELD_OPTION'];
		    					$id = "";
		    					$label = "";
		    					foreach($options As $row2)
		    					{
		    						if($row2['id'] == $i)
		    						{
		    							$id = $row2['id'];
		    							$label = $row2['descrip'];
		    							
		    						}
		    					}
		    						
		    					if($id=="" && $label=="")
		    					{
		    						$id = $row['FIELD_OPTION'][0]['id'];
		    						$label = $row['FIELD_OPTION'][0]['descrip'];
		    					}
		    					$record[$row['FIELD_NAME']] = $id;
		    					$appData = array_merge($record,$appData);
		    					$record[$row['FIELD_NAME']."_label"] = $label;
		    					$appData = array_merge($record,$appData);
		    				}
		    			}
		    		}
		    	}
		    	foreach ($itemsDeleteEdit as $field ) 
    	    	{ 
   		    		$fieldNameEditDelete = utf8_encode($field['CSV_FIELD_NAME']);
   		    		
   		    		if($fieldNameEditDelete == $key )
   		    		{
    	    			if($whereDelete == '')
		    				$whereDelete = $key." = '".$fields."'";
		    			else 
		    				$whereDelete = $whereDelete." AND " .$key." = '".$fields."'";
   		    		}
    	    	} 
		    	
	        }
	        // end labels
	       
	        // delete cases 
	        if($whereDelete != '')
	        {   genDataReport($tableName);
	        	$query = "SELECT APP_UID FROM $tableName WHERE $whereDelete AND APP_UID NOT IN ( $idCasesGenerate ) "; //print($query.'  '); 
	        	$deleteData = executeQuery($query);
	        	if(sizeof($deleteData))
	        	{
	        		foreach($deleteData as $index)
	        		{	
	        			$CurDateTime=date('Y-m-d H:i:s');
	        			insertHistoryLogPlugin($index['APP_UID'],$_SESSION['USER_LOGGED'],$CurDateTime,'1',$index['APP_UID'],'Delete Case');
	        			deletePMCases($index['APP_UID']); 
	        		}
	        		
	        	}
	        }
	        // end delete cases
	        
            $appData['FLAG_ACTION'] = 'multipleDerivation';
            $appData['EXEC_AUTO_DERIVATE'] = 'NO';
            $appData['eligible'] = 0; // only process beneficiary
            $appData['FLAG_EDIT'] = 1;
            $appData['CurrentUserAutoDerivate'] = $USR_UID;
            $caseUID = PMFNewCase($proUid, $USR_UID, $uidTask, $appData);  
            if($totalCases == 0)
            	$idCasesGenerate = "'".$caseUID."'";
            else
            	$idCasesGenerate = $idCasesGenerate.", '".$caseUID."'";
            if($caseUID >0) 
            {
                autoDerivate($proUid,$caseUID,$USR_UID);
                $oCase = new Cases ();
                $FieldsCase = $oCase->loadCase ( $caseUID );
                $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];
                $FieldsCase['APP_DATA']['STATUT'] = 1;
                $oCase->updateCase($caseUID,$FieldsCase);
            }
        }
      // print($caseUID);
        $totalCases++;

    }
    genDataReport($tableName);
    
    # create file tmp   
    if($csv != '')
    {
        $sPathName = PATH_DOCUMENT . "csvTmp" ;
        if (!is_dir($sPathName)) 
       	    G::verifyPath($sPathName, true);
        if (!$handle = fopen($sPathName."/".$csv_file, "w")) {  
           echo "Cannot open file";  
           exit;  
        }  
        if (fwrite($handle, utf8_decode($csv)) === FALSE) {  
           echo "Cannot write to file";  
           exit;  
        }  
        fclose($handle);  
    }
    # end create file tmp
    
    unset($_SESSION['REQ_DATA_CSV']);
    return $totalCases;
}

function importCreateCaseEdit($jsonMatchFields,$uidTask, $tableName,$firstLineHeader, $dataDeleteEdit)
{
	G::LoadClass('case');
    $items   = json_decode($jsonMatchFields,true);
    $dataCSV = isset($_SESSION['REQ_DATA_CSV']) ?$_SESSION['REQ_DATA_CSV']: array();
    $USR_UID = $_SESSION['USER_LOGGED'];
    $_SESSION['USER_LOGGED_INI'] = $USR_UID;
    $proUid  = getProUid($tableName);
    $totalCases = 0;
    $itemsDeleteEdit   = json_decode($dataDeleteEdit,true);
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
				$aData = array();
				$dataSQL = array();
				$data = array();
				if(strlen($field->sql))
				{
					$query = $field->sql;
					$valueData = explode(",",$query);
					$valueId = explode(" ",$valueData[0]);
					$position = count($valueId)-1 ;
					$valueId = $valueId[$position];
					$valueDataCount = count($valueData);
					$valueName = explode(" ",$valueData[$valueDataCount-1]);
					for($i = 0; $i <count($valueName) ; $i++)
					{
						if($valueName[$i]=="from" || $valueName[$i]=="FROM")
						{
							$dataName = $valueName[$i-1];	
						}
					}
					
					$aData = executeQuery($field->sql);
				}	
				if(sizeof($aData))
				{
					foreach($aData As $key => $row)
					{
						$rowData = array ( 'id'=>$row[$valueId],'descrip'=>$row[$dataName]);
						$dataSQL[] = $rowData;
					}
				}
					
				if(sizeof($field->option))
				{
					foreach($field->option As $key => $row)
					{
						$rowData = array ( 'id'=>$key,'descrip'=>$row);
						$data[] = $rowData;
					}
				}
					
				$record = array (
							"FIELD_NAME" => $field->name, 
							"FIELD_LABEL" => $field->label,
							"FIELD_TYPE" => $field->type,
							"FIELD_DEFAULT_VALUE" => $field->defaultValue,
							"FIELD_DEPENDENT_FIELD" => $field->dependentFields,
							"FIELD_OPTION" => $data,
							"FIELD_READONLY" => $field->readonly,
							"FIELD_SQL_CONNECTION" => $field->sqlConnection,
							"FIELD_SQL" => $field->sql,
							"FIELD_SQL_OPTION" => $dataSQL,
							"FIELD_SELECTED_VALUE" => $field->selectedValue,
							"FIELD_SAVE_LABEL" => $field->saveLabel
						);
				$_dataForms[] = $record;
			}
		}
	}
	
	$select = executeQuery("SELECT MAX(IMPCSV_IDENTIFY) AS IDENTIFY FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_TABLE_NAME = '$tableName'");
    $identify = isset($select[1]['IDENTIFY'])? $select[1]['IDENTIFY']:0;
    $identify = $identify + 1;
    $csv_file = $tableName."_".$identify.".csv";  
	$csv_sep = ";";  
	$csv="";  
    $csv_end = "\n";
    $swInsert = 0;

    foreach ($dataCSV as $row) 
    {
        if($totalCases >= 150)
        {
            foreach($row as $value)
            {
                if($totIni == $totRow)
                $csv.=$value;
                else
                    $csv.=$value.$csv_sep;
                $totIni++;
            }
            $csv.=$csv_end;
            if($swInsert == 0)
            {
                $select = executeQuery("SELECT MAX(IMPCSV_ID) AS ID_CSV FROM PMT_IMPORT_CSV_DATA");
                $maxId = isset($select[1]['ID_CSV'])? $select[1]['ID_CSV']:0;
                $maxId = $maxId + 1;
                foreach ($items as $field)
                { 
                    $insert = "INSERT INTO PMT_IMPORT_CSV_DATA 
                          (IMPCSV_ID, IMPCSV_FIELD_NAME, IMPCSV_VALUE,IMPCSV_TAS_UID, IMPCSV_TABLE_NAME, IMPCSV_FIRSTLINEHEADER, IMPCSV_IDENTIFY, IMPCSV_TYPE_ACTION, IMPCSV_CONDITION_ACTION ) VALUES
                          ('$maxId','".$field['FIELD_NAME']."', '".$field['COLUMN_CSV']."', '$uidTask', '$tableName','$firstLineHeader', '$identify', 'ADD_UPDATE', '".mysql_escape_string($dataDeleteEdit)."' )";
                    executeQuery($insert);
                    $swInsert = 1;
                    $maxId++;
                }
            }
            
        }
        else
        {
            $appData =  array();
            foreach ($items as $field) 
            { 
                if($firstLineHeader == 'on')
                {
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
	    	
            foreach($appData As $key => $fields)
	        {
	    		foreach ($_dataForms As $row)
	    		{
	    			if($row['FIELD_DEFAULT_VALUE'] == '')
	    				$row['FIELD_DEFAULT_VALUE'] = 0;
	    				
	    			if($key == $row['FIELD_NAME'])
	    			{
	    				$i = isset($fields)?$fields:$row['FIELD_DEFAULT_VALUE'];
	    					
	    				if(count($row['FIELD_SQL_OPTION']))
	    				{
	    					$options = $row['FIELD_SQL_OPTION'];
	    					$id = "";
	    					$label = "";
	    					foreach($options As $row2)
	    					{
	    						if($row2['id'] == $i)
	    						{
	    							$id = $row2['id'];
	    							$label = $row2['descrip'];
	    							break;
	    						}
	    					}
	    						
	    					if($id=="" && $label=="")
	    					{
	    						$id = $row['FIELD_SQL_OPTION'][0]['id'];
	    						$label = $row['FIELD_SQL_OPTION'][0]['descrip'];
	    					}
	    					
	    					$record[$row['FIELD_NAME']] = $id;
	    					$appData = array_merge($record,$appData);
	    					$record[$row['FIELD_NAME']."_label"] = $label;
	    					$appData = array_merge($record,$appData);
	    				}
	    				else
	    				{
	    					if(count($row['FIELD_OPTION']))
	    					{
	    						$options = $row['FIELD_OPTION'];
	    						$id = "";
	    						$label = "";
	    						foreach($options As $row2)
	    						{
	    							if($row2['id'] == $i)
	    							{
	    								$id = $row2['id'];
	    								$label = $row2['descrip'];
	    								break;
	    							}
	    						}
	    						
	    						$record = Array();
	    						$record[$row['FIELD_NAME']] = $id;
	    						$appData = array_merge($record,$appData);
	    						$record[$row['FIELD_NAME']."_label"] = $label;
	    						$appData = array_merge($record,$appData);
	    					}
	    				}
	    			}
	    		}	
	    	}
	    	$whereUpdate = '';
	        foreach($appData As $key => $fields)
	        {
	        	foreach ($_dataForms As $row)
	    		{
	    			if($row['FIELD_DEFAULT_VALUE'] == '')
	    				$row['FIELD_DEFAULT_VALUE'] = 0;
	    					
	    			$appData[$row['FIELD_NAME']."_label"] = isset($appData[$row['FIELD_NAME']."_label"])?$appData[$row['FIELD_NAME']."_label"]:'';
	    			if($appData[$row['FIELD_NAME']."_label"] =="")
	    			{
	    				$i = $row['FIELD_DEFAULT_VALUE'];	
	    				if(count($row['FIELD_SQL_OPTION']))
	    				{
	    					$options = $row['FIELD_SQL_OPTION'];
	    					$id = "";
	    					$label = "";
	    					foreach($options As $row2)
	    					{
	    						if($row2['id'] == $i)
	    						{
	    							$id = $row2['id'];
	    							$label = $row2['descrip'];
	    							break;
	    						}
	    					}
	    						
	    					if($id=="" && $label=="")
	    					{
	    						$id = $row['FIELD_SQL_OPTION'][0]['id'];
	    						$label = $row['FIELD_SQL_OPTION'][0]['descrip'];
	    					}
	    					$record[$row['FIELD_NAME']] = $id;
	    					$appData = array_merge($record,$appData);
	    					$record[$row['FIELD_NAME']."_label"] = $label;
	    					$appData = array_merge($record,$appData);
	    				}
	    				else
	    				{
	    					if(count($row['FIELD_OPTION']))
	    					{
	    						$options = $row['FIELD_OPTION'];
	    						$id = "";
	    						$label = "";
	    						foreach($options As $row2)
	    						{
	    							if($row2['id'] == $i)
	    							{
	    								$id = $row2['id'];
	    								$label = $row2['descrip'];
	    								
	    							}
	    						}
	    							
	    						if($id=="" && $label=="")
	    						{
	    							$id = $row['FIELD_OPTION'][0]['id'];
	    							$label = $row['FIELD_OPTION'][0]['descrip'];
	    						}
	    						$record[$row['FIELD_NAME']] = $id;
	    						$appData = array_merge($record,$appData);
	    						$record[$row['FIELD_NAME']."_label"] = $label;
	    						$appData = array_merge($record,$appData);
	    					}
	    				}
	    			}
	    		}
	    		foreach ($itemsDeleteEdit as $field ) 
        		{ 
   	    			$fieldNameEditDelete = utf8_encode($field['CSV_FIELD_NAME']);
   	    			if($fieldNameEditDelete == $key )
   	    			{
        				if($whereUpdate == '')
	    					$whereUpdate = $key." = '".$fields."'";
	    				else 
	    					$whereUpdate = $whereUpdate." AND " .$key." = '".$fields."'";
   	    			}
        		} 
	        }
	            
	         // end labels 
	        
	         // update cases 
	         genDataReport($tableName);
	        $query = "SELECT APP_UID FROM $tableName WHERE $whereUpdate ";
	        $updateData = executeQuery($query);
	        if(sizeof($updateData))
	        {
	        	foreach($updateData as $index)
	        	{	
	        		$appData['FLAG_ACTION'] = 'multipleDerivation';
            	    $appData['EXEC_AUTO_DERIVATE'] = 'NO';
            	    $appData['eligible'] = 0; // only process beneficiary
            	    $appData['FLAG_EDIT'] = 1;
            	    $appData['CurrentUserAutoDerivate'] = $USR_UID;
	        		$oCase = new Cases ();
	        		$FieldsCase = $oCase->loadCase ( $index['APP_UID'] );
	        		$appData = array_merge($FieldsCase['APP_DATA'],$appData);
	        		$FieldsCase['APP_DATA'] = $appData;
                	$oCase->updateCase($index['APP_UID'],$FieldsCase);
                	executeTriggers($proUid, $index['APP_UID'] ,$USR_UID);
	        	}
	        }
	        else 
	        {
            	$appData['FLAG_ACTION'] = 'multipleDerivation';
            	$appData['EXEC_AUTO_DERIVATE'] = 'NO';
            	$appData['eligible'] = 0; // only process beneficiary
            	$appData['FLAG_EDIT'] = 1;
            	$appData['CurrentUserAutoDerivate'] = $USR_UID;
            	$caseUID = PMFNewCase($proUid, $USR_UID, $uidTask, $appData);        
            	if($caseUID >0) 
            	{
                	autoDerivate($proUid,$caseUID,$USR_UID);
                	$oCase = new Cases ();
                	$FieldsCase = $oCase->loadCase ( $caseUID );
                	$FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];
                	$FieldsCase['APP_DATA']['STATUT'] = 1;
                	$oCase->updateCase($caseUID,$FieldsCase);
            	}
	        }
           
            $totalCases++;
        
        }
    }
    genDataReport($tableName);
    
    # create file tmp   
    if($csv != '')
    {
        $sPathName = PATH_DOCUMENT . "csvTmp" ;
        if (!is_dir($sPathName)) 
       	    G::verifyPath($sPathName, true);
        if (!$handle = fopen($sPathName."/".$csv_file, "w")) {  
           echo "Cannot open file";  
           exit;  
        }  
        if (fwrite($handle, utf8_decode($csv)) === FALSE) {  
           echo "Cannot write to file";  
           exit;  
        }  
        fclose($handle);  
    }
    # end create file tmp
    
    unset($_SESSION['REQ_DATA_CSV']);
    return $totalCases;
}
/*
function saveFieldsCSV($idInbox, $fieldsImport,$firstLineHeader) {
	$items = json_decode($fieldsImport,true);
	$rolUser= getRolUserImport();
    $sSQL="DELETE FROM PMT_CONFIG_CSV_IMPORT WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox'";
	executeQuery($sSQL);

	foreach ($items as $row) {
		
		$sSQL="INSERT INTO PMT_CONFIG_CSV_IMPORT (CSV_FIELD_NAME, CSV_COLUMN, CSV_FIRST_LINE_HEADER, ROL_CODE, ID_INBOX) VALUES(
			'".$row['CSV_FIELD_NAME']."',
			'".mysql_real_escape_string($row['CSV_COLUMN'])."',
			'".$firstLineHeader."',
			'".$rolUser."',
			'".$idInbox."')";		
		
		executeQuery($sSQL);
	}
   return true;
}
function resetFieldsCSV($idInbox) {
	$rolUser= getRolUserImport();
	$sSQL="DELETE FROM PMT_CONFIG_CSV_IMPORT WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox'";
	
	$aResult= executeQuery($sSQL);
	$bRes= '0';
	if(is_array($aResult) && count($aResult)>0){
		$bRes='1';
	}
   return $bRes;
    
}
*/
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
                $idInbox    = isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
                list($dataNum, $data) = getDynaformFields($fieldsCSV,$tableName );
                $result = getConfigCSV($data,$idInbox);
                echo G::json_encode(array("success" => true, "total" => $dataNum, "data" => $result));
                break;
                
    case "importCreateCase":
    	 $sRadioOption = $_REQUEST["radioOption"];
    	// include ('actionCSV.php');
  		 switch ($sRadioOption) {
    		case "add": 
                $matchFields = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
                $uidTask     = isset($_REQUEST["uidTask"])?$_REQUEST["uidTask"]:'';
                $tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
                $firstLineHeader   = isset($_REQUEST["firstLineHeader"])?$_REQUEST["firstLineHeader"]:'on';
                $totalCases = importCreateCase($matchFields,$uidTask,$tableName,$firstLineHeader);
                echo G::json_encode(array("success" => true, "message" => "OK" , "totalCases" => $totalCases));
                break;
            case "deleteAdd": 
                $matchFields = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
                $uidTask     = isset($_REQUEST["uidTask"])?$_REQUEST["uidTask"]:'';
                $tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
                $firstLineHeader   = isset($_REQUEST["firstLineHeader"])?$_REQUEST["firstLineHeader"]:'on';
                $dataDelete  = isset($_REQUEST["dataEditDelete"])?$_REQUEST["dataEditDelete"]:'';
                $totalCases = importCreateCaseDelete($matchFields,$uidTask,$tableName,$firstLineHeader, $dataDelete);
                echo G::json_encode(array("success" => true, "message" => "OK" , "totalCases" => $totalCases));
                break;
           case "editAdd": 
                $matchFields = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
                $uidTask     = isset($_REQUEST["uidTask"])?$_REQUEST["uidTask"]:'';
                $tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
                $firstLineHeader   = isset($_REQUEST["firstLineHeader"])?$_REQUEST["firstLineHeader"]:'on';
                $dataEdit  = isset($_REQUEST["dataEditDelete"])?$_REQUEST["dataEditDelete"]:'';
                $totalCases = importCreateCaseEdit($matchFields,$uidTask,$tableName,$firstLineHeader,$dataEdit);
                echo G::json_encode(array("success" => true, "message" => "OK" , "totalCases" => $totalCases));
                break;
           
  		 }
  	     break; 
  	                
    case "saveConfigCSV":
				$idInbox= isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
				$fieldsImport = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
				$firstLineHeader = isset($_REQUEST["firstLineHeader"])?$_REQUEST["firstLineHeader"]:'on';
			    $resp = saveFieldsCSV($idInbox,$fieldsImport,$firstLineHeader);
			    echo G::json_encode(array("success" => true, "message" => "OK"));
			    break;
			    
	case "resetConfigCSV":
				$fieldsCSV  = isset($_REQUEST["fieldsCSV"])?$_REQUEST["fieldsCSV"]:'';
				$idInbox= isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
				$tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
				$resp = resetFieldsCSV($idInbox);
				list($dataNum, $data) = getDynaformFields($fieldsCSV,$tableName );
			    echo G::json_encode(array("success" => true, "total" => $dataNum, "data" => $data));
			  break;
  }

} catch (Exception $e) {
    $err = $e->getMessage();
    $err = preg_replace("[\n|\r|\n\r]", ' ', $err);
    $paging = array ('success' => false, 'total' => 0, 'data' => array(), 'success_req'=> 'error', 'message' => $err);
    echo json_encode ( $paging );
}

?>