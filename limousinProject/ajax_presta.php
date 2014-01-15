<?php

G::LoadClass("webResource");
G::LoadClass("pmFunctions");

ini_set('display_errors',0);

class ajax_presta extends WebResource {

        function search_presta($uid) {
                $res = '';
                $query = "SELECT IF(ADR_ACTIV1 IS NULL, RAISONSOCIALE, ADR_ACTIV1) AS RAISONSOCIALE, 
                                 CONCAT(TH_CINE, '-', TH_SPECTACLE, '-', TH_ACHAT, '-', TH_ARTS, '-', TH_SPORT, '-', IF(TH_ADH_ART = '0', TH_ADH_SPORT, TH_ADH_ART)) AS THEMATIQUE, 
                                 IF(ADR_ACTIV1 IS NULL, CONCAT( ADRESSE1, COALESCE( CONCAT( '<br/>', ADRESSE2 ) , '' ) , COALESCE( CONCAT( '<br/>', ADRESSE3 ) , '' ) ), CONCAT( ADR_ACTIV2, COALESCE( CONCAT( '<br/>', ADR_ACTIV3 ) , '' ) ))  AS ADRESSE,
                                 IF(ADR_ACTIV1 IS NULL, CP, CP_ACTIV) AS CP, 
                                 IF(ADR_ACTIV1 IS NULL, VILLE, BUR_ACTIV) AS VILLE,
                                 PARTENAIRE_UID, 
                                 USER_ID 
                                 FROM PMT_PRESTATAIRE where STATUT=1 AND PARTENAIRE_UID ='".$uid."'";
                $result = executeQuery($query);
                if (isset($result))
                        $res = json_encode($result[1]);
                return $res;
        }
}

$o = new ajax_presta($_SERVER['REQUEST_URI'], $_POST);

?>
