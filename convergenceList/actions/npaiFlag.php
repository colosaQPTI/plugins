<?php 
G::loadClass ( 'pmFunctions' );
G::LoadClass("case");
header ( "Content-Type: text/plain" );
$array=array();
$array = $_REQUEST['array'];
$todoAnnule = $_REQUEST['todo'];
$items = json_decode($array,true);
$flag = 0;
$array=array();
$oCase = new Cases ();
$messageInfo = '';
foreach($items as $item){
	if(isset($item['APP_UID']) && $item['APP_UID'] != ''){
		//on regarde si on a pas modifier l'adresse entre temps
		//si statut produit et si date modif adresse > date de la production
		$newAdresse = 0;
		if (!empty($_REQUEST['callback']))
        {
            $newAdresse = call_user_func($_REQUEST['callback'], $item);
        }
        else
        {
            $query = 'SELECT max(HLOG_DATECREATED) as HLOG_DATECREATED FROM PMT_HISTORY_LOG WHERE HLOG_APP_UID="' . $item['APP_UID'] . '" AND HLOG_ACTION LIKE "Retour de production%"';
            $result = executeQuery($query);
            //si j'ai une date de retour de prod, je regarde si je n'ai pas de modif d'adresse apres
            if (isset($result[1]['HLOG_DATECREATED']) && $result[1]['HLOG_DATECREATED'] != '')
            {
                $query2 = 'SELECT count(*) as NB FROM PMT_HISTORY_LOG WHERE HLOG_APP_UID="' . $item['APP_UID'] . '" AND HLOG_DATECREATED > "' . $result[1]['HLOG_DATECREATED'] . '" AND HLOG_ACTION="Modification de l\'adresse"';
                $result2 = executeQuery($query2);

    			if ($result2[1]['NB'] > 0)
                    $newAdresse = 1;
                else
                    $newAdresse = 0;
            }
        }
        // Ajout du $todoAnnule == 1 pour pouvoir enlever le flag si l'adress à été modifiée
        //if ($newAdresse == 0 || $todoAnnule == 1)
        //{
            //on met a jour le flag NPAI
            $Fields = $oCase->loadCase ($item['APP_UID']);
            if ($todoAnnule == 1) {
                $Fields['APP_DATA']['NPAI'] = 'N';
                convergence_deleteCompteurPND($item['NUM_DOSSIER']);
                insertHistoryLogPlugin($item['APP_UID'],$_SESSION['USER_LOGGED'],date('Y-m-d H:i:s'),'0','',"Enlever des PND",$Fields['APP_DATA']['STATUT']);
            }
            else
            {
                $Fields['APP_DATA']['NPAI'] = 'O';
                convergence_insertCompteurPND($item['NUM_DOSSIER']);
                insertHistoryLogPlugin($item['APP_UID'],$_SESSION['USER_LOGGED'],date('Y-m-d H:i:s'),'0','',"Classer en PND",$Fields['APP_DATA']['STATUT']);
            }                        
            
            $oCase->updateCase($item['APP_UID'], $Fields);		
            $flag = 1;
            $messageInfo .= "<strong>Dossier " . $item['NUM_DOSSIER'] . " : </strong> Mis a jour.<br/>";
        //}
        
        if ($newAdresse == 1 && $todoAnnule == 0)
        {
            $fields = convergence_getAllAppData($item['APP_UID']);                        
            /*if ($messageInfo != '')
            {
                $messageInfo .= '<br/>';                                                
            }*/
            $messageInfo .= '<strong>Dossier N°' . $item['NUM_DOSSIER'] . ' : </strong> Une nouvelle adresse existe pour cette demande.' . "<br/>";
        }
                

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