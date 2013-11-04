<?php

G::LoadClass("webResource");
G::LoadClass("pmFunctions");

ini_set('display_errors',0);

class ajax_etab extends WebResource {

        function search_etab($uid) {
                $res = '';
                $query = 'SELECT NOM,RNE,ADR2,ADR1,VILLE,CP FROM PMT_ETABLISSEMENT where STATUT=1 AND RNE ="'.$uid.'"';
                $result = executeQuery($query);
                if (isset($result))
                        $res = json_encode($result[1]);

                return $res;
        }

}

$o = new ajax_etab($_SERVER['REQUEST_URI'], $_POST);

?>