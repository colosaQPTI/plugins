<?php

G::LoadClass("webResource");
G::LoadClass("pmFunctions");

ini_set('display_errors',0);

class ajax_presta extends WebResource {

        function search_presta($uid) {
                $res = '';
                $query = "SELECT RAISONSOCIALE, CONCAT(TH_CINE, '-', TH_SPECTACLE, '-', TH_ACHAT, '-', TH_ARTS, '-', TH_SPORT, '-', IF(TH_ADH_ART = '0', TH_ADH_SPORT, TH_ADH_ART)) AS THEMATIQUE, CONCAT(ADRESSE1, '<br/>', ADRESSE2, '<br/>', ADRESSE3) AS ADRESSE, CP, VILLE, PARTENAIRE_UID, USER_ID FROM PMT_PRESTATAIRE where STATUT=1 AND PARTENAIRE_UID ='".$uid."'";
                $result = executeQuery($query);
                if (isset($result))
                        $res = json_encode($result[1]);
                return $res;
        }
}

$o = new ajax_presta($_SERVER['REQUEST_URI'], $_POST);

?>
