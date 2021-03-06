<?php
G::loadClass ( 'pmFunctions' );

function getData($dossier) {

    $query = 'SELECT TC.VALEUR_TOTAL as VN, TC.LABEL AS CHQ, D.APP_UID AS DMDAPPUID,D.NUM_DOSSIER,D.THEMATIQUE_LABEL,C.UID,D.COMPLEMENT_CHQ,
        C.BCONSTANTE,C.VN_TITRE,C.DEBUT_VALIDITE,C.FIN_VALIDITE,C.ANNULE,C.REPRODUCTION,C.DATE_RMB
            FROM PMT_CHEQUES AS C
                INNER JOIN PMT_DEMANDES AS D ON (D.NUM_DOSSIER = C.NUM_DOSSIER)
                LEFT JOIN PMT_TYPE_CHEQUIER AS TC ON (D.CODE_CHEQUIER = TC.CODE_CD)
            WHERE (D.STATUT = 6 AND D.NUM_DOSSIER = ' . $dossier . ' OR D.NUM_DOSSIER_COMPLEMENT=' . $dossier . ') AND C.BCONSTANTE = C.NUM_TITRE';
    $result = executeQuery($query);
    
    
    if (is_array($result) && count($result) > 0) {
        
        foreach($result as $value) $array[] = $value;
        
        return $array;
    }


    
}


$data = getData($_REQUEST["num_dossier"]);

header("Content-Type: text/plain");

$paging = array(
    'success'=> true,
    'total'=> count($data),
    'data'=> $data
    );

echo G::json_encode($paging);

?>
