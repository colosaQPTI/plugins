<?php
G::loadClass ( 'pmFunctions' );

function getData($idRmb) {

    $query = 'SELECT D.APP_UID AS DMDAPPUID, D.NUM_DOSSIER, P.APP_UID AS PRESTAID, D.THEMATIQUE_LABEL,
        C.UID, D.COMPLEMENT_CHQ, C.BCONSTANTE, C.NUM_TITRE, C.VN_TITRE, C.DEBUT_VALIDITE, C.FIN_VALIDITE,
        C.ANNULE, C.REPRODUCTION, P.RAISONSOCIALE, P.VILLE, C.DATE_RMB, S.TITLE
                FROM PMT_CHEQUES AS C
                INNER JOIN PMT_DEMANDES AS D
                    ON (D.NUM_DOSSIER = C.NUM_DOSSIER)
                LEFT JOIN PMT_PRESTATAIRE AS P
                    ON (C.CODE_PRESTA = P.CONVENTION)
                LEFT JOIN PMT_STATUT AS S 
                    ON (C.STATUT = S.UID) 
                WHERE C.ID_RMB=' . $idRmb;
    $result = executeQuery($query);
    
    
    if (is_array($result) && count($result) > 0) {
        
        foreach($result as $value) $array[] = $value;
        
        return $array;
    }


    
}


$data = getData($_REQUEST["idRmb"]);

header("Content-Type: text/plain");

$paging = array(
    'success'=> true,
    'total'=> count($data),
    'data'=> $data
    );

echo G::json_encode($paging);

?>