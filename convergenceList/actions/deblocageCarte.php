<?php

G::loadClass('pmFunctions');
G::LoadClass("case");
header("Content-Type: text/plain");
$porteurId = $_REQUEST['porteur_id'];
//$items = json_decode($array, true);
//$oCase = new Cases ();
$success = false;
$messageInfo = 'Unknow Error';
 if (!empty($porteurId))
 {
    // on regarde si le porteurid fourni est correct et on remonte le cas echeant les infos de la demande
    $exist = limousinProject_getCartePorteurId($porteurId);
    if (!empty($exist))
    {
            //on appel le WS d'activation de la carte, on ajoute un groupe utilsateur carte active dans le fe_user Typo3
            //et mise a jour de la table des carte PMT_CHEQUES comme quoi elle est activée
            $active = limousinProject_getDeblocage($porteurId);
            if (!empty($active->CODE) && $active->CODE == 'OK')
            {
               $success = true;
               $messageInfo = 'OK';
            }
            else
            {
                if (!empty($active->Description))
                {
                    // Erreur lors de l'updateUsergroup dans Typo3
                    $messageInfo = $active->Description;
                   
                }
                else
                {
                    // On récupére le label de l'erreur lors de l'appel ws activation carte
                    $messageInfo = limousinProject_getErrorAqoba($active->CODE, 'WS210') . " (code $active->CODE du WS210)";
                    $messageInfo .= var_export($active,true);
                }
            }
    } else {
     $messageInfo = 'Carte non produite.'   ; 
    }        

 } else {
     $messageInfo = 'Porteur Id incorrect'   ; 
 }
$result = array('success' => $success, 'messageinfo' => $messageInfo);
echo G::json_encode($result);

