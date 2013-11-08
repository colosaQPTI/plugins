<?php
//ini_set ( 'error_reporting', E_ALL );
//ini_set ( 'display_errors', True );
## (c) req - last change May 23
header('Content-Type: text/html; charset=ISO-8859-1');
G::loadClass ( 'pmFunctions' );
G::LoadClass ( 'form' );
//include ("doublonData.php");
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
                $firstLineHeader = isset($_REQUEST["firstLineHeader"])?$_REQUEST["firstLineHeader"]:'';
                list($dataNum, $data) = getDynaformFields($fieldsCSV,$tableName );
                $result = getConfigCSV($data,$idInbox,$firstLineHeader);
                //echo G::json_encode(array("success" => true, "total" => $dataNum, "data" => $data));
                echo G::json_encode(array("success" => true, "total" => $dataNum, "data" => $result));
                break;
                
    case "saveConfigCSV":
				$idInbox= isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
				$fieldsImport = isset($_REQUEST["matchFields"]) ? $_REQUEST["matchFields"] : '';
                $firstLineHeader = isset($_REQUEST["firstLineHeader"]) ? $_REQUEST["firstLineHeader"] : 'on';
                $resp = saveFieldsCSV($idInbox, $fieldsImport, $firstLineHeader);
            echo G::json_encode(array("success" => true, "message" => "OK"));
			    break;
	case "resetConfigCSV":
				$fieldsCSV  = isset($_REQUEST["fieldsCSV"])?$_REQUEST["fieldsCSV"]:'';
				$idInbox = isset($_REQUEST["idInbox"])?$_REQUEST["idInbox"]:'';
				$tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
				$resp = resetFieldsCSV($idInbox);
				list($dataNum, $data) = getDynaformFields($fieldsCSV,$tableName );
			    echo G::json_encode(array("success" => true, "total" => $dataNum, "data" => $data));
			  break;
			              
    case "importCreateCase":
    	    $sRadioOption = $_REQUEST["radioOption"];
            $resultFields = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
            $fieldsConfiguration = json_decode($resultFields);
            $firstLineHeader = isset($_REQUEST["firstLineHeader"]) ? $_REQUEST["firstLineHeader"] : 'on';
            $idInbox = isset($_REQUEST['idInbox']) ? $_REQUEST['idInbox']:'';
            $rolUser = getRolUserImport();
            
            if(count($fieldsConfiguration))
            {
                /*$query = "SELECT * FROM PMT_CONFIG_CSV_IMPORT WHERE ROL_CODE = '".$rolUser."' AND ID_INBOX = '".$idInbox."' 
                GROUP BY ID_INBOX";
                $result = executeQuery($query);
                $firstConfig = $result[1]['CSV_FIRST_LINE_HEADER'];

                if($firstConfig == $firstLineHeader)
                { */   
                    
                    switch ($sRadioOption) {
            		case "add": 
                        $matchFields = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
                        $uidTask     = isset($_REQUEST["uidTask"])?$_REQUEST["uidTask"]:'';
                        $tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
                        $typeAction = 'ADD';
                        $totalCases = importCreateCase($matchFields,$uidTask,$tableName,$firstLineHeader,$typeAction);
                        echo G::json_encode(array("success" => true, "message" => "OK", "totalCases" => $totalCases));
                        break;
                    case "deleteAdd": 
                        $matchFields = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
                        $uidTask     = isset($_REQUEST["uidTask"])?$_REQUEST["uidTask"]:'';
                        $tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
                        $dataDelete  = isset($_REQUEST["dataEditDelete"])?$_REQUEST["dataEditDelete"]:'';
                        $totalCases = importCreateCaseDelete($matchFields, $uidTask, $tableName, $firstLineHeader, $dataDelete);
                        echo G::json_encode(array("success" => true, "message" => "OK" , "totalCases" => $totalCases));
                        break;
                   case "editAdd": 
                        $matchFields = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
                        $uidTask     = isset($_REQUEST["uidTask"])?$_REQUEST["uidTask"]:'';
                        $tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
                        $dataEdit  = isset($_REQUEST["dataEditDelete"])?$_REQUEST["dataEditDelete"]:'';
                        $totalCases = importCreateCaseEdit($matchFields,$uidTask,$tableName,$firstLineHeader, $dataEdit);
                        echo G::json_encode(array("success" => true, "message" => "OK", "totalCases" => $totalCases));
                        break;
                  case "truncateAdd": 
                        $matchFields = isset($_REQUEST["matchFields"])?$_REQUEST["matchFields"]:'';
                        $uidTask     = isset($_REQUEST["uidTask"])?$_REQUEST["uidTask"]:'';
                        $tableName   = isset($_REQUEST["tableName"])?$_REQUEST["tableName"]:'';
                        $totalCases = importCreateCaseTruncate($matchFields,$uidTask,$tableName,$firstLineHeader);
                        echo G::json_encode(array("success" => true, "message" => "OK", "totalCases" => $totalCases));
                        break;
                    }
                /*}
                else
                {
                    echo G::json_encode(array("success" => false,
                                              "message" => "S'il vous plaît gardez cette CSV de configuration", 
                                              "totalCases" => 0));
                } */  
            }
            else
            {   
                echo G::json_encode(array("success" => false,
                                           "message" => "S'il vous plaît définir une colonne pour effectuer l'action du CSV", 
                                           "totalCases" => 0));
            } 
  }

} catch (Exception $e) {
    $err = $e->getMessage();
    $err = preg_replace("[\n|\r|\n\r]", ' ', $err);
    $paging = array ('success' => false, 'total' => 0, 'data' => array(), 'success_req'=> 'error', 'message' => $err);
    echo json_encode ( $paging );
}

?>
