<?php    
    G::LoadClass('case');
    G::LoadClass('configuration');
    G::loadClass('pmFunctions');
    require_once ("classes/model/Users.php"); 
    header("Content-Type: text/plain");   
    $start = isset($_POST['start']) ? $_POST['start'] : 0;
    $limit = isset($_POST['limit']) ? $_POST['limit'] : 20000;
    $USER_UID = $_SESSION['USER_LOGGED'];    
    $Us = new Users();
    $Roles=$Us->load($USER_UID);      
    $rolesAdmin=$Roles['USR_ROLE'];
    $APP_UID = isset($_GET['APP_UID']) ? $_GET['APP_UID'] : '';          
    $NUM_DOSSIER = isset($_GET['NUM_DOSSIER']) ? $_GET['NUM_DOSSIER'] : '';          
    $TABLE = isset($_GET['TABLE']) ? $_GET['TABLE'] : 'PMT_DEMANDES';          
    $array = Array();
    $i = 1;
    
    /*while ($i != '') {      
      $sQuery="SELECT HLOG.*,APP.APP_NUMBER, CONCAT(U.USR_FIRSTNAME,' ',U.USR_LASTNAME)AS USERCREATOR 
               FROM PMT_HISTORY_LOG  AS HLOG
               INNER JOIN APPLICATION AS APP ON (APP.APP_UID = HLOG.HLOG_APP_UID)
               INNER JOIN USERS AS U ON (U.USR_UID = HLOG.HLOG_USER_UID)
               WHERE HLOG_CHILD_APP_UID = '$APP_UID' ";
      $aDatos = executeQuery ($sQuery);
      if(sizeof($aDatos)){ //  Verify if I have a child Cases
        foreach($aDatos as $row)
        {                                    
          $array[] = $row;
          $APP_UID = $row['HLOG_APP_UID'];
        }
        $i='full';
      }        
      else{
        $i = '';
      }      
    } 
     
     */                   
    

    if ($NUM_DOSSIER != '' && $TABLE != '') {
        $query = 'SELECT CONCAT(\'"\',APP_UID,\'"\') as APP_UID FROM '.$TABLE.' WHERE NUM_DOSSIER = '.$NUM_DOSSIER;
        $list = executeQuery($query);
        
        foreach ($list as $val) {
            
            $all[] = $val['APP_UID'];
            
        }

        if (count($all) > 0) {
            //version FRED
            $sQuery="SELECT HLOG.HLOG_UID,HLOG.HLOG_APP_UID,HLOG.HLOG_USER_UID,
                            DATE_FORMAT(HLOG.HLOG_DATECREATED, '%d-%m-%Y %H:%i:%s') AS HLOG_DATECREATED,HLOG.HLOG_VERSION,HLOG.HLOG_CHILD_APP_UID,
                            HLOG.HLOG_ACTION,HLOG.HLOG_STATUS, HLOG_UID, 
                            CONCAT(U.USR_FIRSTNAME,' ',U.USR_LASTNAME)AS USERCREATOR, S.TITLE AS HLOG_STATUS                     
                    FROM PMT_HISTORY_LOG  AS HLOG
                    INNER JOIN USERS AS U ON (U.USR_UID = HLOG.HLOG_USER_UID)
                    INNER JOIN PMT_STATUT AS S ON (S.UID = HLOG.HLOG_STATUS)
                    WHERE HLOG_APP_UID in (".implode(',',$all).") ORDER BY HLOG_UID DESC";

            //G::pr($sQuery);
            
            $aDatos = executeQuery ($sQuery);
            if (is_array($aDatos) && count($aDatos) > 0) {
                foreach($aDatos as $row) {
                    $array[] = $row;
                }
            }


            $total = count($array);
            $paging = array(
                'success'=> true,
                'total'=> $total,
                'data'=> array_splice($array,$start,$limit)
            );  
        }
        else {
            $paging = array(
                'success'=> true,
                'total'=> '',
                'data'=> ''
            ); 
        }
    }
    echo json_encode($paging);
     
?>
