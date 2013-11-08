<?php

    G::LoadClass('configuration');
		G::LoadClass('pmFunctions');
		G::LoadClass('wsBase');
		G::LoadClass('case');
     $workspace = SYS_SYS;
	 createCasesCSV();
		/*$date_current = Date("m-d-Y");
		$cron = "SELECT COUNT(DATE_CRON) as CANT FROM PMT_CRON_ARCHIVED WHERE DATE_CRON = '".$date_current."' ";
		$dataCron = executeQuery($cron);
		$cantCron = $dataCron[1]['CANT'];
		if($cantCron == '0')
		{
			$ins="INSERT
            		INTO PMT_CRON_ARCHIVED 
              		(DATE_CRON,STATUS)
            		VALUES 
             	 ('".$date_current."', 'NEW') ";	
			executeQuery($ins);
			
			archivedCases();
		}*/
		echo "* ARCHIVED CASES EXECUTED *"; 
			
	

	function createCasesCSV()
	{
	    $query = "SELECT IMPCSV_IDENTIFY, IMPCSV_TYPE_ACTION, IMPCSV_CONDITION_ACTION, IMPCSV_FIRSTLINEHEADER, IMPCSV_TABLE_NAME, IMPCSV_TAS_UID
	              FROM PMT_IMPORT_CSV_DATA GROUP BY IMPCSV_IDENTIFY ";
		$data = executeQuery($query);
		if(sizeof($data))
		{
		    foreach($data as $row)
		    {
		        $query = "SELECT IMPCSV_FIELD_NAME, IMPCSV_VALUE FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '".$row['IMPCSV_IDENTIFY']."' ";
		        $dataCsv = executeQuery($query);
		        $dataImportCSV =  array();
		        
		        foreach($dataCsv as $index)
		        {
		            $record = array (
							"FIELD_NAME" => $index['IMPCSV_FIELD_NAME'], 
							"COLUMN_CSV" => $index['IMPCSV_VALUE']
					);
					$dataImportCSV[] = $record;
		        }
		        
		        $actionType = $row['IMPCSV_TYPE_ACTION'];
		        $matchFields = $dataImportCSV;
                $uidTask     = isset($row["IMPCSV_TAS_UID"])? $row["IMPCSV_TAS_UID"]:'';
                $tableName   = isset($row["IMPCSV_TABLE_NAME"])? $row["IMPCSV_TABLE_NAME"]:'';
                $csvIdentify   = isset($row["IMPCSV_IDENTIFY"])? $row["IMPCSV_IDENTIFY"]:'';
                $firstLineHeader   = isset($row["IMPCSV_FIRSTLINEHEADER"])? $row["IMPCSV_FIRSTLINEHEADER"]:'on';
                $fileCSV     = $tableName.'_'.$row['IMPCSV_IDENTIFY'];
		        $informationCSV = getDataCronCSV($firstLineHeader, $fileCSV);
		        //G::pr($informationCSV); 
		        $dataDeleteEdit   = isset($row["IMPCSV_CONDITION_ACTION"])? $row["IMPCSV_CONDITION_ACTION"]:'';
		        
		        switch ($actionType) 
		        {
    	        	case "ADD": 
                        $totalCases = importCreateCaseCSV($matchFields,$uidTask,$tableName,$firstLineHeader,$informationCSV);
                        $delete = executeQuery("DELETE FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ");
                        deleteFileCSV($fileCSV);
                        break;
                
                    case "ADD_DELETE": 
                        $totalCases = importCreateCaseDeleteCSV($matchFields,$uidTask,$tableName,$firstLineHeader, $informationCSV,$dataDeleteEdit);
                        $delete = executeQuery("DELETE FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ");
                        deleteFileCSV($fileCSV);
                        break;
                    case "ADD_UPDATE": 
                        
                        $totalCases = importCreateCaseEditCSV($matchFields,$uidTask,$tableName,$firstLineHeader,$informationCSV, $dataDeleteEdit);
                        $delete = executeQuery("DELETE FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ");
                        deleteFileCSV($fileCSV);
                        break;
                }
                
                
  		    }
		}
		
		
	}
	
	function getDataCronCSV($firstLineCsvAs = 'on', $fileCSV)
	{
	    ($fileCSV);
        set_include_path(PATH_PLUGINS . 'convergenceList' . PATH_SEPARATOR . get_include_path());
        if (!$handle = fopen("/opt/processmaker/workflow/engine/plugins/convergenceList/csvTmp/".$fileCSV.".csv", "r")) {  
            echo "Cannot open file";  
            exit;  
        } 
        $csvData = array(); 
        $csvDataIni = array();
        while ($data = fgetcsv($handle, 4096, ";")) {
            $num = count ($data);
            $i = 0; 
            foreach($data as $row) {

               $csvDataIni[]= $row;

            }
             $csvData[] = $csvDataIni;
            $csvDataIni = '';
        }
        
        return $csvData;
    }
	
	function deleteFileCSV($fileCSV)
	{
	    $dir= "/opt/processmaker/workflow/engine/plugins/convergenceList/csvTmp/".$fileCSV.".csv"; 
        if(file_exists($dir)) 
        { 
        if(unlink($dir)) 
        print "El archivo fue borrado"; 
        } 
        else 
        print "Este archivo no existe"; 
	}
    function importCreateCaseCSV($jsonMatchFields,$uidTask, $tableName,$firstLineHeader,$informationCSV)
    {
       
        G::LoadClass('case');
        $items   =$jsonMatchFields; 
        $dataCSV = isset($informationCSV) ?$informationCSV: array();
        $USR_UID = '00000000000000000000000000000001';
        $_SESSION['USER_LOGGED_INI'] = $USR_UID;
        $sSQL ="SELECT * FROM ADDITIONAL_TABLES WHERE ADD_TAB_NAME ='$tableName'";
        $aResult= executeQuery($sSQL);
        $proUid = '0';
        if(is_array($aResult) && count($aResult)>0)
        {
            $proUid =$aResult[1]['PRO_UID'];
        } 
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
    		
    	
       
        foreach ($dataCSV as $row) 
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
            
      		    $appData['SIRET'] = isset($appData['SIRET'])? $appData['SIRET']:'';
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
    					//		$appData = array_merge($record,$appData);
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
    							//	$appData = array_merge($record,$appData);
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
    						
    					$appData[$row['FIELD_NAME']."_label"] = isset($appData[$row['FIELD_NAME']."_label"])? $appData[$row['FIELD_NAME']."_label"]:'';
    					
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
    					//		$appData = array_merge($record,$appData);
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
    					//			$appData = array_merge($record,$appData);
                            $record[$row['FIELD_NAME']."_label"] = $label;
    								$appData = array_merge($record,$appData);
    								
    							}
    						}
    					}
    				}
    	        }
          
            // end labels //
           // delete cases 
	       
	        if($whereDelete != '')
	        {
	            genDataReport($tableName);
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
	        
	        $appData['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
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
            $totalCases++;
        }
       
        
        unset($informationCSV);
        return $totalCases;
    }
    
    function importCreateCaseEditCSV($jsonMatchFields,$uidTask, $tableName,$firstLineHeader,$informationCSV, $dataDeleteEdit)
    {
       
        G::LoadClass('case');
        $items   =$jsonMatchFields; 
        $dataCSV = isset($informationCSV) ?$informationCSV: array();
        $USR_UID = '00000000000000000000000000000001';
        $_SESSION['USER_LOGGED_INI'] = $USR_UID;
        $sSQL ="SELECT * FROM ADDITIONAL_TABLES WHERE ADD_TAB_NAME ='$tableName'";
        $aResult= executeQuery($sSQL);
        $proUid = '0';
        $itemsDeleteEdit   = json_decode($dataDeleteEdit,true);
        if(is_array($aResult) && count($aResult)>0)
        {
            $proUid =$aResult[1]['PRO_UID'];
        } 
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
    	 genDataReport($tableName);
        foreach ($dataCSV as $row) 
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
            
      		    $appData['SIRET'] = isset($appData['SIRET'])? $appData['SIRET']:'';
                $query = "SELECT APP_UID FROM PMT_PRESTATAIRE WHERE STATUT = 1 AND SIRET ='".$appData['SIRET']."'";
                $result= executeQuery($query);
                
                $caseUID = '0';
           
                 // labels //
                $whereUpdate = '';
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
    					//		$appData = array_merge($record,$appData);
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
    					//			$appData = array_merge($record,$appData);
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
    						
    					$appData[$row['FIELD_NAME']."_label"] = isset($appData[$row['FIELD_NAME']."_label"])? $appData[$row['FIELD_NAME']."_label"]:'';
    					
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
    					//		$appData = array_merge($record,$appData);
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
    					//			$appData = array_merge($record,$appData);
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
          
            // end labels //
            
             // update cases 
	        
            $query = "SELECT APP_UID FROM $tableName WHERE $whereUpdate ";
	        $updateData = executeQuery($query);
	        if(sizeof($updateData))
	        {  
	        	foreach($updateData as $index)
	        	{	
	        	    $oCase = new Cases ();
	        		$FieldsCase = $oCase->loadCase ( $index['APP_UID'] );
	        		$appData['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
            	    $appData['FLAG_ACTION'] = 'multipleDerivation';
            	    $appData['EXEC_AUTO_DERIVATE'] = 'NO';
            	    $appData['eligible'] = 0; // only process beneficiary
            	    $appData['FLAG_EDIT'] = 1;
                $appDataNew['FLAG_UPDATE'] = 1;
                $appData['CurrentUserAutoDerivate'] = $USR_UID;
            	    $appData = array_merge($FieldsCase['APP_DATA'],$appData);
            	    $FieldsCase['APP_DATA'] = $appData;
	        		$oCase->updateCase($index['APP_UID'],$FieldsCase);
	        		executeTriggers($proUid, $index['APP_UID'] ,$USR_UID);
	        		$oCase = new Cases ();
	        		$FieldsCase = $oCase->loadCase ( $index['APP_UID'] );
	        		$FieldsCase['APP_DATA']['STATUT'] = 1;
                	$oCase->updateCase($index['APP_UID'],$FieldsCase);
	        	}
	        }
	        else 
	        {
	            $appData['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
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
       
        
        unset($informationCSV);
        return $totalCases;
    }
    
    function importCreateCaseDeleteCSV($jsonMatchFields,$uidTask, $tableName,$firstLineHeader,$informationCSV, $dataDeleteEdit)
    {
       
        G::LoadClass('case');
        $items   =$jsonMatchFields; 
        $dataCSV = isset($informationCSV) ?$informationCSV: array();
        $USR_UID = '00000000000000000000000000000001';
        $_SESSION['USER_LOGGED_INI'] = $USR_UID;
        $sSQL ="SELECT * FROM ADDITIONAL_TABLES WHERE ADD_TAB_NAME ='$tableName'";
        $aResult= executeQuery($sSQL);
        $proUid = '0';
        $itemsDeleteEdit   = json_decode($dataDeleteEdit,true);
        $idCasesGenerate = "''";	
        if(is_array($aResult) && count($aResult)>0)
        {
            $proUid =$aResult[1]['PRO_UID'];
        } 
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
    	
        foreach ($dataCSV as $row) 
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
            
      		    $appData['SIRET'] = isset($appData['SIRET'])? $appData['SIRET']:'';
                $query = "SELECT APP_UID FROM PMT_PRESTATAIRE WHERE STATUT = 1 AND SIRET ='".$appData['SIRET']."'";
                $result= executeQuery($query);
                
                $caseUID = '0';
           
                 // labels //
                $whereDelete = '';
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
    					//		$appData = array_merge($record,$appData);
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
    					//			$appData = array_merge($record,$appData);
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
    						
    					$appData[$row['FIELD_NAME']."_label"] = isset($appData[$row['FIELD_NAME']."_label"])? $appData[$row['FIELD_NAME']."_label"]:'';
    					
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
    					//		$appData = array_merge($record,$appData);
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
    					//			$appData = array_merge($record,$appData);
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
          
            // end labels //
            // delete cases 
	       
	        if($whereDelete != '')
	        {
	            genDataReport($tableName);
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
            $appData['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
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
            
            $totalCases++;
        }
       
        
        unset($informationCSV);
        return $totalCases;
    }
    
function genDataReport ($tableName){
    G::loadClass( 'pmTable' );
    G::loadClass ( 'pmFunctions' );
    require_once 'classes/model/AdditionalTables.php';
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
