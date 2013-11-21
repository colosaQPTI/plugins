<?php

class archivedCasesClassCron {
    public
            $workspace = SYS_SYS;
    function followUpActions() {
        //echo "* INICIE *";
        if (!defined('PATH_PM_BUSINESS_RULES'))
        {
            define('PATH_PM_BUSINESS_RULES', PATH_CORE . 'plugins' . PATH_SEP . 'pmBusinessRules' . PATH_SEP);
        }
        G::LoadClass('configuration');
        G::LoadClass('pmFunctions');
        G::LoadClass('wsBase');
        G::LoadClass('case');
        G::LoadClass('plugin');
        G::LoadClass("serverConfiguration");
        G::LoadClass("system");
        require_once(PATH_PLUGINS . 'convergenceList/classes/class.pmFunctions.php');
        require_once(PATH_PLUGINS . 'obladyConvergence/classes/class.pmFunctions.php');
        require_once(PATH_PLUGINS . 'NordPDC/classes/class.pmFunctions.php');
        require_once(PATH_PLUGINS . 'idfTranSport/classes/class.pmFunctions.php');
        require_once(PATH_PLUGINS . 'pmBusinessRules/classes/class.pmFunctions.php');
        require_once(PATH_PLUGINS . 'limousinProject/classes/class.pmFunctions.php');
        set_include_path(PATH_PLUGINS . 'pmBusinessRules' . PATH_SEPARATOR . get_include_path());
        define('PATH_WORKSPACE', PATH_DB . SYS_SYS . PATH_SEP);
        set_include_path(get_include_path() . PATH_SEPARATOR . PATH_WORKSPACE);
        $this->createCasesCSV();
        echo "* ARCHIVED CASES EXECUTED *";
    }
    function createCasesCSV() {
        $query = "SELECT IMPCSV_IDENTIFY, IMPCSV_TYPE_ACTION, IMPCSV_CONDITION_ACTION, IMPCSV_FIRSTLINEHEADER, IMPCSV_TABLE_NAME, IMPCSV_TAS_UID, IMPCSV_WHERE_ACTION
                                  FROM wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA GROUP BY IMPCSV_IDENTIFY ";
        $data = executeQuery($query);
        if (sizeof($data))
        {
            foreach ($data as $row)
            {
                $query = "SELECT IMPCSV_FIELD_NAME, IMPCSV_VALUE FROM wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '" . $row['IMPCSV_IDENTIFY'] . "' ";
                $dataCsv = executeQuery($query);
                $dataImportCSV = array();
                foreach ($dataCsv as $index)
                {
                    $record = array(
                        "FIELD_NAME" => $index['IMPCSV_FIELD_NAME'],
                        "COLUMN_CSV" => $index['IMPCSV_VALUE']
                    );
                    $dataImportCSV[] = $record;
                }
                $USR_UID = '00000000000000000000000000000001';
                $_SESSION['USER_LOGGED'] = $USR_UID;
                $user = userInfo($USR_UID);
                $_SESSION['USR_USERNAME'] = $user['username'];
                $actionType = $row['IMPCSV_TYPE_ACTION'];
                $matchFields = $dataImportCSV;
                $uidTask = isset($row["IMPCSV_TAS_UID"]) ? $row["IMPCSV_TAS_UID"] : '';
                $tableName = isset($row["IMPCSV_TABLE_NAME"]) ? $row["IMPCSV_TABLE_NAME"] : '';
                $csvIdentify = isset($row["IMPCSV_IDENTIFY"]) ? $row["IMPCSV_IDENTIFY"] : '';
                $csvWhereAction = isset($row["IMPCSV_WHERE_ACTION"]) ? $row["IMPCSV_WHERE_ACTION"] : '';
                $firstLineHeader = isset($row["IMPCSV_FIRSTLINEHEADER"]) ? $row["IMPCSV_FIRSTLINEHEADER"] : 'on';
                $fileCSV = $tableName . '_' . $row['IMPCSV_IDENTIFY'];
                $queryTot = executeQuery("SELECT IMPCSV_TOTCASES FROM wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName'");
                $totCasesCSV = $queryTot[1]['IMPCSV_TOTCASES'];
                $path = PATH_DOCUMENT . "csvTmp/" . $fileCSV . ".csv";
                $informationCSV = getDataCronCSV($firstLineHeader, $fileCSV, $totCasesCSV, $path);
                $dataDeleteEdit = isset($row["IMPCSV_CONDITION_ACTION"]) ? $row["IMPCSV_CONDITION_ACTION"] : '';
                switch ($actionType)
                {
                    case "ADD":
                        $totalCases = $this->importCreateCaseCSV($matchFields, $uidTask, $tableName, $firstLineHeader, $informationCSV, $csvIdentify, $totCasesCSV);
                        $delete = executeQuery("DELETE FROM wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ");
                        $this->deleteFileCSV($fileCSV);
                        break;
                    case "ADD_DELETE":
                        $totalCases = $this->importCreateCaseDeleteCSV($matchFields, $uidTask, $tableName, $firstLineHeader, $informationCSV, $dataDeleteEdit, $csvIdentify, $totCasesCSV);
                        $delete = executeQuery("DELETE FROM wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ");
                        $this->deleteFileCSV($fileCSV);
                        break;
                    case "ADD_UPDATE":
                        $totalCases = $this->importCreateCaseEditCSV($matchFields, $uidTask, $tableName, $firstLineHeader, $informationCSV, $dataDeleteEdit, $csvIdentify, $totCasesCSV, $csvWhereAction);
                        $delete = executeQuery("DELETE FROM wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ");
                        $this->deleteFileCSV($fileCSV);
                        break;
                    case "ADD_TRUNCATE":
                        $totalCases = $this->importCreateCaseCSV($matchFields, $uidTask, $tableName, $firstLineHeader, $informationCSV, $csvIdentify, $totCasesCSV);
                        $delete = executeQuery("DELETE FROM wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ");
                        $this->deleteFileCSV($fileCSV);
                        break;
                }
            }
            #permissions
            $directory = PATH_DOCUMENT;
            chmodr($directory, 0777);
            chownr($directory, 'apache');
            chgrpr($directory, 'apache');
            # End Permissions
        }
    }
    function getDataCronCSV1($firstLineCsvAs = 'on', $fileCSV, $totCasesCSV) {
        set_include_path(PATH_PLUGINS . 'convergenceList' . PATH_SEPARATOR . get_include_path());
        if (!$handle = fopen(PATH_DOCUMENT . "csvTmp/" . $fileCSV . ".csv", "r"))
        {
            echo "Cannot open file";
            exit;
        }
        $csvData = array();
        $csvDataIni = array();
        $i = 0;
        while ($data = fgetcsv($handle, 4096, ";"))
        {
            /*             By Nico 28/08/2013 fix Bug on the import Background by CRON with header csv files.
             *
             * Add this part because when we import by cron a csv with header, all import are the header for value
             * So, after put the original header in the csv temp file in actionCSV.php,
             * we do this to work perfectly
             *
             */
            $col = 0;
            if ($firstLineCsvAs == 'on' && $i == 0)
            {
                foreach ($data as $row)
                {
                    $column_csv[] = $row;
                }
            }
            else
            {
                $num = count($data);
                foreach ($data as $row)
                {
                    /* $csvData key is the header for good import after */
                    if ($firstLineCsvAs == 'on')
                    {
                        if ($totCasesCSV <= $i)
                            $csvDataIni[$column_csv[$col]] = $row;
                        $col++;
                    }
                    else /* No header on csv files */
                    {
                        if ($totCasesCSV <= $i)
                            $csvDataIni[] = $row;
                    }
                }
                if ($totCasesCSV <= $i)
                    $csvData[] = $csvDataIni;
                $csvDataIni = '';
            }
            $i++;
        }
        return $csvData;
    }
    function deleteFileCSV($fileCSV) {
        $dir = PATH_DOCUMENT . "csvTmp/" . $fileCSV . ".csv";
        if (file_exists($dir))
        {
            if (unlink($dir))
                print "File Deleted ";
        }
        else
            print "The file is not present. ";
    }
    function importCreateCaseCSV($jsonMatchFields, $uidTask, $tableName, $firstLineHeader, $informationCSV, $csvIdentify, $totCasesCSV) {
        G::LoadClass('case');
        $items = $jsonMatchFields;
        $dataCSV = isset($informationCSV) ? $informationCSV : array();
        $USR_UID = '00000000000000000000000000000001';
        $_SESSION['USER_LOGGED_INI'] = $USR_UID;
        $sSQL = "SELECT * FROM wf_" . $this->workspace . ".ADDITIONAL_TABLES WHERE ADD_TAB_NAME ='$tableName'";
        $aResult = executeQuery($sSQL);
        $proUid = '0';
        if (is_array($aResult) && count($aResult) > 0)
        {
            $proUid = $aResult[1]['PRO_UID'];
        }
        $totalCases = $totCasesCSV;
        // load Dynaforms of process
        $select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM wf_" . $this->workspace . ".DYNAFORM WHERE PRO_UID = '" . $proUid . "'";
        $resultDynaform = executeQuery($select);
        $_dataForms = dataDynaforms($resultDynaform, $proUid);
        // end load Dynaforms of process
        foreach ($dataCSV as $row)
        {
            $appData = array();
            foreach ($items as $field)
            {
                if ($firstLineHeader == 'on')
                {
                    if (isset($row[$field['COLUMN_CSV']]))
                    {
                        if ($row[$field['COLUMN_CSV']] != '')
                            $appData[$field['FIELD_NAME']] = _convert($row[$field['COLUMN_CSV']]);
                        else
                            $appData[$field['FIELD_NAME']] = ' ';
                    }
                    else
                    {
                        if ($field['COLUMN_CSV'] != '')
                            $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                        else
                            $appData[$field['FIELD_NAME']] = ' ';
                    }
                }
                else
                {
                    $aCol = explode(' ', trim($field['COLUMN_CSV']));
                    if ((isset($aCol[0]) && trim($aCol[0]) == 'Column' ) && ( isset($aCol[1]) && isset($row[$aCol[1]]) ))
                    {
                        $appData[$field['FIELD_NAME']] = _convert($row[$aCol[1]]);
                    }
                    else if (( isset($aCol[0]) && trim($aCol[0]) != 'Column'))
                    {
                        $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                    }
                }
            }
            foreach ($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if ($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;
                    if ($key == $row['FIELD_NAME'])
                    {
                        $i = isset($fields) ? $fields : $row['FIELD_DEFAULT_VALUE'];
                        if (count($row['FIELD_SQL_OPTION']))
                        {
                            $options = $row['FIELD_SQL_OPTION'];
                            $id = "";
                            $label = "";
                            foreach ($options As $row2)
                            {
                                if ($row2['id'] == $i)
                                {
                                    $id = $row2['id'];
                                    $label = $row2['descrip'];
                                    break;
                                }
                            }
                            if ($id == "" && $label == "")
                            {
                                $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                            }
                            $record[$row['FIELD_NAME']] = $id;
                            $record[$row['FIELD_NAME'] . "_label"] = $label;
                            $appData = array_merge($record, $appData);
                        }
                        else
                        {
                            if (count($row['FIELD_OPTION']))
                            {
                                $options = $row['FIELD_OPTION'];
                                $id = "";
                                $label = "";
                                foreach ($options As $row2)
                                {
                                    if ($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }
                                $record = Array();
                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME'] . "_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                        }
                    }
                }
            }
            foreach ($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if ($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;

                    if($row['FIELD_TYPE'] != 'yesno')
                    {
                        $appData[$row['FIELD_NAME'] . "_label"] = isset($appData[$row['FIELD_NAME'] . "_label"]) ? $appData[$row['FIELD_NAME'] . "_label"] : '';
                        if ($appData[$row['FIELD_NAME'] . "_label"] == "")
                        {
                            $i = $row['FIELD_DEFAULT_VALUE'];
                            if (count($row['FIELD_SQL_OPTION']))
                            {
                                $options = $row['FIELD_SQL_OPTION'];
                                $id = "";
                                $label = "";
                                foreach ($options As $row2)
                                {
                                    if ($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }

                                if ($id == "" && $label == "")
                                {
                                    $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                    $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                                }

                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME'] . "_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                            else
                            {
                                if (count($row['FIELD_OPTION']))
                                {
                                    $options = $row['FIELD_OPTION'];
                                    $id = "";
                                    $label = "";
                                    foreach ($options As $row2)
                                    {
                                        if ($row2['id'] == $i)
                                        {
                                            $id = $row2['id'];
                                            $label = $row2['descrip'];
                                            break;
                                        }
                                    }

                                    if ($id == "" && $label == "")
                                    {
                                        $id = $row['FIELD_OPTION'][0]['id'];
                                        $label = $row['FIELD_OPTION'][0]['descrip'];
                                    }

                                    $record[$row['FIELD_NAME']] = $id;
                                    $record[$row['FIELD_NAME'] . "_label"] = $label;
                                    $appData = array_merge($record, $appData);
                                }
                            }
                        }
                    }
                    else 
                    {
                        $record = Array();
                        if( $appData[$row['FIELD_NAME']] == ''  || $appData[$row['FIELD_NAME']] == ' ')
                        {
                            $appData[$row['FIELD_NAME']] = $row['FIELD_DEFAULT_VALUE'];
                            //$appData = array_merge($record, $appData);
                        }
                    }
                }
            }

            // end labels //
            foreach ($appData as $key => $value)
            {
                if (!is_array($value))
                    $appData[$key] = ($value);
                else
                    $appData[$key] = $value;
            }

            $appData['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
            $appData['FLAG_ACTION'] = 'multipleDerivation';
            $appData['EXEC_AUTO_DERIVATE'] = 'NO';
            $appData['eligible'] = 0; // only process beneficiary
            $appData['FLAG_EDIT'] = 1;
            $appData['STATUT'] = 1;
            $appData['CurrentUserAutoDerivate'] = $USR_UID;
            $appData['SYS_SKIN'] = 'neoclassic';
            $caseUID = PMFNewCase($proUid, $USR_UID, $uidTask, $appData);
            if ($caseUID > 0)
            {
                $oCase = new Cases ();
                $FieldsCase = $oCase->loadCase($caseUID);
                $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];
                $FieldsCase['APP_DATA']['SW_CREATE_CASE'] = 1; # control trigger create new cases csv

                $oCase->updateCase($caseUID, $FieldsCase);
                $controlCron = false;
                autoDerivate($proUid, $caseUID, $USR_UID, $controlCron);
                #permissions
                $app_uidFile = $caseUID;
                if (method_exists('G', 'getPathFromUID'))
                {
                    $app_uidFile = G::getPathFromUID($caseUID);
                    $app_uidFile = explode('/', $app_uidFile);
                    $app_uidFile = $app_uidFile[0];
                }
                $directory = PATH_DOCUMENT . $app_uidFile . PATH_SEP;
                chmodr($directory, 0777);
                chownr($directory, 'apache');
                chgrpr($directory, 'apache');
                # End Permissions
            }
            $totalCases++;
            $update = "UPDATE wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA SET IMPCSV_TOTCASES = '$totalCases' WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ";
            executeQuery($update);
        }

        unset($informationCSV);
        return $totalCases;
    }
    function importCreateCaseEditCSV($jsonMatchFields, $uidTask, $tableName, $firstLineHeader, $informationCSV, $dataDeleteEdit, $csvIdentify, $totCasesCSV, $csvWhereAction) {
        G::LoadClass('case');
        $items = $jsonMatchFields;
        $dataCSV = isset($informationCSV) ? $informationCSV : array();
        $USR_UID = '00000000000000000000000000000001';
        $_SESSION['USER_LOGGED_INI'] = $USR_UID;
        $sSQL = "SELECT * FROM wf_" . $this->workspace . ".ADDITIONAL_TABLES WHERE ADD_TAB_NAME ='$tableName'";
        $aResult = executeQuery($sSQL);
        $proUid = '0';
        $itemsDeleteEdit = json_decode($dataDeleteEdit, true);
        if (is_array($aResult) && count($aResult) > 0)
        {
            $proUid = $aResult[1]['PRO_UID'];
        }
        $totalCases = $totCasesCSV;

        // load Dynaforms of process
        $select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM wf_" . $this->workspace . ".DYNAFORM WHERE PRO_UID = '" . $proUid . "'";
        $resultDynaform = executeQuery($select);
        $_dataForms = dataDynaforms($resultDynaform, $proUid);
        // end load Dynaforms of process

        foreach ($dataCSV as $row)
        {
            $appData = array();
            foreach ($items as $field)
            {
                if ($firstLineHeader == 'on')
                {

                    if (isset($row[$field['COLUMN_CSV']]))
                    {
                        if ($row[$field['COLUMN_CSV']] != '' )
                            $appData[$field['FIELD_NAME']] = _convert($row[$field['COLUMN_CSV']]);
                        else
                            $appData[$field['FIELD_NAME']] = ' ';
                    }
                    else
                    {
                        if ($field['COLUMN_CSV'] != '' )
                            $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                        else
                            $appData[$field['FIELD_NAME']] = ' ';
                    }
                }
                else
                {
                    $aCol = explode(' ', trim($field['COLUMN_CSV']));
                    if ((isset($aCol[0]) && trim($aCol[0]) == 'Column' ) && ( isset($aCol[1]) && isset($row[$aCol[1]]) ))
                        $appData[$field['FIELD_NAME']] = _convert($row[$aCol[1]]);
                    else if (( isset($aCol[0]) && trim($aCol[0]) != 'Column'))
                        $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                }
            }

            // labels //
            foreach ($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if ($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;

                    if ($key == $row['FIELD_NAME'])
                    {
                        $i = isset($fields) ? $fields : $row['FIELD_DEFAULT_VALUE'];

                        if (count($row['FIELD_SQL_OPTION']))
                        {
                            $options = $row['FIELD_SQL_OPTION'];
                            $id = "";
                            $label = "";
                            foreach ($options As $row2)
                            {
                                if ($row2['id'] == $i)
                                {
                                    $id = $row2['id'];
                                    $label = $row2['descrip'];
                                    break;
                                }
                            }

                            if ($id == "" && $label == "")
                            {
                                $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                            }

                            $record[$row['FIELD_NAME']] = $id;
                            $record[$row['FIELD_NAME'] . "_label"] = $label;
                            $appData = array_merge($record, $appData);
                        }
                        else
                        {
                            if (count($row['FIELD_OPTION']))
                            {
                                $options = $row['FIELD_OPTION'];
                                $id = "";
                                $label = "";
                                foreach ($options As $row2)
                                {
                                    if ($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }

                                $record = Array();

                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME'] . "_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                        }
                    }
                }
            }

            $caseUID = '0';
            $whereUpdate = '';
            foreach ($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if ($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;

                    if($row['FIELD_TYPE'] != 'yesno')
                    { 
                        $appData[$row['FIELD_NAME'] . "_label"] = isset($appData[$row['FIELD_NAME'] . "_label"]) ? $appData[$row['FIELD_NAME'] . "_label"] : '';

                        if ($appData[$row['FIELD_NAME'] . "_label"] == "")
                        {
                            $i = $row['FIELD_DEFAULT_VALUE'];
                            if (count($row['FIELD_SQL_OPTION']))
                            {

                                $options = $row['FIELD_SQL_OPTION'];
                                $id = "";
                                $label = "";
                                foreach ($options As $row2)
                                {
                                    if ($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }

                                if ($id == "" && $label == "")
                                {
                                    $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                    $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                                }

                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME'] . "_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                            else
                            {
                                if (count($row['FIELD_OPTION']))
                                {
                                    $options = $row['FIELD_OPTION'];
                                    $id = "";
                                    $label = "";
                                    foreach ($options As $row2)
                                    {
                                        if ($row2['id'] == $i)
                                        {
                                            $id = $row2['id'];
                                            $label = $row2['descrip'];
                                            break;
                                        }
                                    }

                                    if ($id == "" && $label == "")
                                    {
                                        $id = $row['FIELD_OPTION'][0]['id'];
                                        $label = $row['FIELD_OPTION'][0]['descrip'];
                                    }

                                    $record[$row['FIELD_NAME']] = $id;
                                    $record[$row['FIELD_NAME'] . "_label"] = $label;
                                    $appData = array_merge($record, $appData);
                                }
                            }
                        }
                    }
                    else 
                    {                       
                        if( $appData[$row['FIELD_NAME']] == '' || $appData[$row['FIELD_NAME']] == ' ' )
                        {
                            $appData[$row['FIELD_NAME']] = $row['FIELD_DEFAULT_VALUE'];
                            //$appData = array_merge($record, $appData);
                        }
                    }
                }
                foreach ($itemsDeleteEdit as $field)
                {
                    $fieldNameEditDelete = htmlspecialchars_decode($field['CSV_FIELD_NAME']);
                    if ($fieldNameEditDelete == $key)
                    {
                        if ($whereUpdate == '')
                            $whereUpdate = $key . " = '" . mysql_escape_string($fields) . "'";
                        else
                            $whereUpdate = $whereUpdate . " AND " . $key . " = '" . mysql_escape_string($fields) . "'";
                    }
                }
            }
            // end labels //
            $appDataNew = array();
            foreach ($appData as $key => $value)
            {
                foreach ($items as $row)
                {
                    if ($row['FIELD_NAME'] == $key)
                    {
                        if (!is_array($value))
                        {
                            $appDataNew[$key] = $value;
                        }
                        else
                        {
                            $appDataNew[$key] = $value;
                        }
                    }
                }
                if (!is_array($value))
                {
                    $appData[$key] = $value;
                }
                else
                {
                    $appData[$key] = $value;
                }
            }
            // update cases
            $query = "SELECT APP_UID FROM wf_" . $this->workspace . ".$tableName WHERE $whereUpdate " . $csvWhereAction;
            $updateData = executeQuery($query);
            if (sizeof($updateData))
            {
                $user = userInfo($USR_UID);
                foreach ($updateData as $index)
                {
                    $oCase = new Cases ();
                    $FieldsCase = $oCase->loadCase($index['APP_UID']);
                    $appDataNew['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
                    $appDataNew['FLAG_ACTION'] = 'multipleDerivation';
                    $appDataNew['EXEC_AUTO_DERIVATE'] = 'NO';
                    $appDataNew['eligible'] = 0; // only process beneficiary
                    $appDataNew['FLAG_EDIT'] = 1;
                    $appDataNew['FLAG_UPDATE'] = 1; // add by Nico for historyLog
                    $appDataNew['CurrentUserAutoDerivate'] = $USR_UID;
                    $appDataNew['SW_CREATE_CASE'] = 1;
                    ## changes USER_LOGGED, USR_USERNAME
                    $appDataNew['USER_LOGGED'] = $USR_UID;
                    $appDataNew['USR_USERNAME'] = $user['username'];
                    ## end changes USER_LOGGED, USR_USERNAME
                    // needed to create cases. If a loop is generated when you run the trigger
                    $appDataNew = array_merge($FieldsCase['APP_DATA'], $appDataNew);
                    $FieldsCase['APP_DATA'] = $appDataNew;
                    $oCase->updateCase($index['APP_UID'], $FieldsCase);
                    executeTriggers($proUid, $index['APP_UID'], $USR_UID);
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
                if ($caseUID > 0)
                {
                    $oCase = new Cases ();
                    $FieldsCase = $oCase->loadCase($caseUID);
                    $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];
                    $FieldsCase['APP_DATA']['SW_CREATE_CASE'] = 1; # control trigger create new cases csv
                    $oCase->updateCase($caseUID, $FieldsCase);
                    $controlCron = false;
                    autoDerivate($proUid, $caseUID, $USR_UID, $controlCron);
                    #permissions
                    $app_uidFile = $caseUID;
                    if (method_exists('G', 'getPathFromUID'))
                    {
                        $app_uidFile = G::getPathFromUID($caseUID);
                        $app_uidFile = explode('/', $app_uidFile);
                        $app_uidFile = $app_uidFile[0];
                    }
                    $directory = PATH_DOCUMENT . $app_uidFile . PATH_SEP;
                    chmodr($directory, 0777);
                    chownr($directory, 'apache');
                    chgrpr($directory, 'apache');
                    # End Permissions
                }
            }

            $totalCases++;
            $update = "UPDATE wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA SET IMPCSV_TOTCASES = '$totalCases' WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ";
            executeQuery($update);
        }
        unset($informationCSV);
        return $totalCases;
    }
    function importCreateCaseDeleteCSV($jsonMatchFields, $uidTask, $tableName, $firstLineHeader, $informationCSV, $dataDeleteEdit, $csvIdentify, $totCasesCSV) {

        G::LoadClass('case');
        $items = $jsonMatchFields;
        $dataCSV = isset($informationCSV) ? $informationCSV : array();
        $USR_UID = '00000000000000000000000000000001';
        $_SESSION['USER_LOGGED_INI'] = $USR_UID;
        $sSQL = "SELECT * FROM wf_" . $this->workspace . ".ADDITIONAL_TABLES WHERE ADD_TAB_NAME ='$tableName'";
        $aResult = executeQuery($sSQL);
        $proUid = '0';
        $itemsDeleteEdit = json_decode($dataDeleteEdit, true);
        $idCasesGenerate = "''";
        if (is_array($aResult) && count($aResult) > 0)
        {
            $proUid = $aResult[1]['PRO_UID'];
        }
        $totalCases = $totCasesCSV;

        // load Dynaforms of process
        $select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM wf_" . $this->workspace . ".DYNAFORM WHERE PRO_UID = '" . $proUid . "'";
        $resultDynaform = executeQuery($select);
        $_dataForms = dataDynaforms($resultDynaform, $proUid);
        // end load Dynaforms of process

        foreach ($dataCSV as $row)
        {
            $appData = array();
            foreach ($items as $field)
            {
                if ($firstLineHeader == 'on')
                {

                    if (isset($row[$field['COLUMN_CSV']]))
                    {
                        if ($row[$field['COLUMN_CSV']] != '' )
                            $appData[$field['FIELD_NAME']] = _convert($row[$field['COLUMN_CSV']]);
                        else
                            $appData[$field['FIELD_NAME']] = ' ';
                    }
                    else
                    {
                        if ($field['COLUMN_CSV'] != '' )
                            $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                        else
                            $appData[$field['FIELD_NAME']] = ' ';
                    }
                }
                else
                {
                    $aCol = explode(' ', trim($field['COLUMN_CSV']));
                    if ((isset($aCol[0]) && trim($aCol[0]) == 'Column' ) && ( isset($aCol[1]) && isset($row[$aCol[1]]) ))
                        $appData[$field['FIELD_NAME']] = _convert($row[$aCol[1]]);
                    else if (( isset($aCol[0]) && trim($aCol[0]) != 'Column'))
                    {
                        $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                    }
                }
            }

            $caseUID = '0';

            // labels //
            foreach ($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if ($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;

                    if ($key == $row['FIELD_NAME'])
                    {
                        $i = isset($fields) ? $fields : $row['FIELD_DEFAULT_VALUE'];

                        if (count($row['FIELD_SQL_OPTION']))
                        {
                            $options = $row['FIELD_SQL_OPTION'];
                            $id = "";
                            $label = "";
                            foreach ($options As $row2)
                            {
                                if ($row2['id'] == $i)
                                {
                                    $id = $row2['id'];
                                    $label = $row2['descrip'];
                                    break;
                                }
                            }

                            if ($id == "" && $label == "")
                            {
                                $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                            }

                            $record[$row['FIELD_NAME']] = $id;
                            $record[$row['FIELD_NAME'] . "_label"] = $label;
                            $appData = array_merge($record, $appData);
                        }
                        else
                        {
                            if (count($row['FIELD_OPTION']))
                            {
                                $options = $row['FIELD_OPTION'];
                                $id = "";
                                $label = "";
                                foreach ($options As $row2)
                                {
                                    if ($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }

                                $record = Array();

                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME'] . "_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                        }
                    }
                }
            }

            $whereDelete = '';
            foreach ($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if ($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;

                    if($row['FIELD_TYPE'] != 'yesno')
                    { 
                        $appData[$row['FIELD_NAME'] . "_label"] = isset($appData[$row['FIELD_NAME'] . "_label"]) ? $appData[$row['FIELD_NAME'] . "_label"] : '';

                        if ($appData[$row['FIELD_NAME'] . "_label"] == "")
                        {
                            $i = $row['FIELD_DEFAULT_VALUE'];
                            if (count($row['FIELD_SQL_OPTION']))
                            {

                                $options = $row['FIELD_SQL_OPTION'];
                                $id = "";
                                $label = "";
                                foreach ($options As $row2)
                                {
                                    if ($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }

                                if ($id == "" && $label == "")
                                {
                                    $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                    $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                                }

                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME'] . "_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                            else
                            {
                                if (count($row['FIELD_OPTION']))
                                {
                                    $options = $row['FIELD_OPTION'];
                                    $id = "";
                                    $label = "";
                                    foreach ($options As $row2)
                                    {
                                        if ($row2['id'] == $i)
                                        {
                                            $id = $row2['id'];
                                            $label = $row2['descrip'];
                                            break;
                                        }
                                    }

                                    if ($id == "" && $label == "")
                                    {
                                        $id = $row['FIELD_OPTION'][0]['id'];
                                        $label = $row['FIELD_OPTION'][0]['descrip'];
                                    }

                                    $record[$row['FIELD_NAME']] = $id;
                                    $record[$row['FIELD_NAME'] . "_label"] = $label;
                                    $appData = array_merge($record, $appData);
                                }
                            }
                        }
                    }
                    else 
                    {
                        $record = Array();
                        if( $appData[$row['FIELD_NAME']] == ''  || $appData[$row['FIELD_NAME']] == ' ')
                        {
                            $appData[$row['FIELD_NAME']] = $row['FIELD_DEFAULT_VALUE'];
                            //$appData = array_merge($record, $appData);
                        }
                    }
                }
                foreach ($itemsDeleteEdit as $field)
                {
                    $fieldNameEditDelete = htmlspecialchars_decode($field['CSV_FIELD_NAME']);
                    if ($fieldNameEditDelete == $key)
                    {
                        if ($whereDelete == '')
                            $whereDelete = $key . " = '" . mysql_escape_string($fields) . "'";
                        else
                            $whereDelete = $whereDelete . " AND " . $key . " = '" . mysql_escape_string($fields) . "'";
                    }
                }
            }
            // end labels //
            // delete cases
            if ($whereDelete != '')
            {
                // genDataReport($tableName);
                $query = "SELECT APP_UID FROM wf_" . $this->workspace . ".$tableName WHERE $whereDelete AND APP_UID NOT IN ( $idCasesGenerate ) "; //print($query.'  ');
                $deleteData = executeQuery($query);
                if (sizeof($deleteData))
                {
                    foreach ($deleteData as $index)
                    {
                        $CurDateTime = date('Y-m-d H:i:s');
                        insertHistoryLogPlugin($index['APP_UID'], $USR_UID, $CurDateTime, '1', $index['APP_UID'], 'Delete Case');
                        deletePMCases($index['APP_UID'],$tableName);
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
            if ($totalCases == 0)
                $idCasesGenerate = "'" . $caseUID . "'";
            else
                $idCasesGenerate = $idCasesGenerate . ", '" . $caseUID . "'";
            if ($caseUID > 0)
            {
                $oCase = new Cases ();
                $FieldsCase = $oCase->loadCase($caseUID);
                $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];
                $FieldsCase['APP_DATA']['SW_CREATE_CASE'] = 1; # control trigger create new cases csv
                $oCase->updateCase($caseUID, $FieldsCase);
                $controlCron = false;
                autoDerivate($proUid, $caseUID, $USR_UID, $controlCron);
                #permissions
                $app_uidFile = $caseUID;
                if (method_exists('G', 'getPathFromUID'))
                {
                    $app_uidFile = G::getPathFromUID($caseUID);
                    $app_uidFile = explode('/', $app_uidFile);
                    $app_uidFile = $app_uidFile[0];
                }
                $directory = PATH_DOCUMENT . $app_uidFile . PATH_SEP;
                chmodr($directory, 0777);
                chownr($directory, 'apache');
                chgrpr($directory, 'apache');
                # End Permissions
            }

            $totalCases++;
            $update = "UPDATE wf_" . $this->workspace . ".PMT_IMPORT_CSV_DATA SET IMPCSV_TOTCASES = '$totalCases' WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName' ";
            executeQuery($update);
        }


        unset($informationCSV);
        return $totalCases;
    }
}
