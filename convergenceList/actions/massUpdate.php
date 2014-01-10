<?php 
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', True);
G::loadClass ( 'pmFunctions' );
G::LoadClass("case");
header ( "Content-Type: text/plain" );
$array=array();
$array = $_REQUEST['array'];
$champs = $_REQUEST['champs'];
$taskuid = $_REQUEST['taskuid'];
$items = json_decode($array,true);
$flag = 0;
$array=array();
$oCase = new Cases ();
$messageInfo = '';
$array = explode('|', trim($champs));

foreach($array as $champsItem)
{
    $champsArray[] = explode('=', trim($champsItem));
}

foreach($items as $item){
    if (isset($item['APP_UID']) && $item['APP_UID'] != '')
    {
        $Fields = $oCase->loadCase($item['APP_UID']);
        $query = "SELECT MAX(DEL_INDEX) AS DEL_INDEX FROM APP_DELEGATION WHERE APP_UID = '".$item['APP_UID']."' ";
        $data = executeQuery($query);
        $index = $data[1]['DEL_INDEX'];
        $queryDel = "SELECT USR_UID, TAS_UID, PRO_UID, DEL_THREAD_STATUS FROM APP_DELEGATION WHERE APP_UID = '".$item['APP_UID']."' AND DEL_INDEX = '".$index."' ";
        $resDel = executeQuery($queryDel);
        $taskIni = '';
        $process = '';
        $statusCase = '';
        if(sizeof($resDel))
        {
            $taskIni = $resDel[1]['TAS_UID'];
            $process = $resDel[1]['PRO_UID'];
            $statusCase = $resDel[1]['DEL_THREAD_STATUS'];
            if($resDel[1]['USR_UID'] == ""){
                $queryuPDel = "UPDATE APP_DELEGATION SET USR_UID = '".$_SESSION['USER_LOGGED']."' 
                WHERE APP_UID = '".$item['APP_UID']."' AND DEL_INDEX = '".$index."' ";
                $queryuPDel = executeQuery($queryuPDel);
                $userId = $_SESSION['USER_LOGGED'];
            }
            else
            {
                $userId = $resDel[1]['USR_UID'];
            }
        }
        
        if ($statusCase != 'CLOSED')
        {
             foreach ($champsArray as $champsItem)
            { 
                $Fields['APP_DATA'][$champsItem[0]] = $champsItem[1];
                $Fields['APP_DATA']['FLAG_ACTION'] = 'actionAjax';
                $oCase->updateCase($item['APP_UID'], $Fields);
                insertHistoryLogPlugin($item['APP_UID'], $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', "Modification en masse du champ " . $champsItem[0], $Fields['APP_DATA']['STATUT']);
                $resInfo = PMFDerivateCase($item['APP_UID'], $index, true, $userId );
                
            }
        }
        else
        {
             foreach ($champsArray as $champsItem)
            { 
                $Fields['APP_DATA'][$champsItem[0]] = $champsItem[1];
                $Fields['APP_DATA']['FLAG_ACTION'] = 'actionAjax';
                if ($champsItem[1] == '0') 
                {
                  $Fields['APP_DATA']['isRefus']     = 1;     
                  $Fields['APP_DATA']['msgRefus']    = '<br/>&nbsp;-&nbsp; Dossier refusé par votre établissement';
                  $Fields['APP_DATA']['STATUT']      = '4';
                  $Fields['APP_DATA']['eligible']    = 0;
                }
                if($champsItem[1] == '1')
                {
                   $Fields['APP_DATA']['STATUT']    = '2'; 
                   $Fields['APP_DATA']['isRefus']   = 0;
                   $Fields['APP_DATA']['eligible']  = 1;
                }
                $oCase->updateCase($item['APP_UID'], $Fields);
                insertHistoryLogPlugin($item['APP_UID'], $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', "Modification en masse du champ " . $champsItem[0], $Fields['APP_DATA']['STATUT']);
               
                
            }
        }
      
        $flag = 1; 
    }
}
    
if ($flag == 1) {
    
    if ($messageInfo != '')
        $messageInfo .= '<br/>';
    
    $messageInfo .= 'Les dossiers ont ete mis a jour.';
    
}

$paging = array ('success' => true, 'messageinfo' => $messageInfo);
echo G::json_encode ( $paging );
?>
