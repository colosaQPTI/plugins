<?php
G::loadClass ( 'pmFunctions' );

function getData($app_prod) {


    $qListe = 'select LISTE_DOSSIER from PMT_LISTE_PROD where APP_UID = "' . $app_prod . '"';
    $rListe = executeQuery($qListe);
    if (!empty($rListe))
    {
        $liste = array();
        $liste = explode(',', $rListe[1]['LISTE_DOSSIER']);
        $in = '("' . implode('","', $liste) . '")';
        $query = 'select C.CARTE_PORTEUR_ID as PORT_ID, D.APP_UID as DMDAPPUID, C.CARTE_NUM as CARTE_NUM, C.CARTE_STATUT as CARTE_STATUT, C.CARTE_TYPE as CARTE_TYPE, D.FI_NOM as NOM, D.FI_PRENOM as PRENOM, D.NPAI as PND from PMT_CHEQUES as C INNER JOIN PMT_DEMANDES as D on (C.CARTE_PORTEUR_ID = D.PORTEUR_ID) where D.NUM_DOSSIER IN' . $in . ' and  D.STATUT <> 0 and D.STATUT <> 999';
        $result = executeQuery($query);
        if (is_array($result) && count($result) > 0) {        
            foreach ($result as $value)
                $array[] = $value;
            return $array;
        }
    }
}

$data = getData($_REQUEST["app_uid"]);
header("Content-Type: text/plain");
$paging = array(
    'success'=> true,
    'total'=> count($data),
    'data'=> $data
    );

echo G::json_encode($paging);

?>