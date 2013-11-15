<?php
G::loadClass ( 'pmFunctions' );

function getData($porteurId)
{
    $query = " SELECT t.UID, TYPE, CONVERT(DATE_FORMAT(STR_TO_DATE(DATE_EMISSION, '%d-%m-%Y'), '%d/%m/%Y') USING utf8) AS DATE_EMISSION, ".
             " CONVERT(DATE_FORMAT(STR_TO_DATE(DATE_VALIDATION, '%d-%m-%Y'), '%d/%m/%Y') USING utf8) AS DATE_VALIDATION, ".
             " CONVERT(DATE_FORMAT(STR_TO_DATE(DATE_ENVOI, '%d-%m-%Y'), '%d/%m/%Y') USING utf8) AS DATE_ENVOI, ".
             " CONVERT(DATE_FORMAT(STR_TO_DATE(DATE_REMBOURSEMENT, '%d-%m-%Y'), '%d/%m/%Y') USING utf8) AS DATE_REMBOURSEMENT, ".
             " TITLE, CONCAT(FORMAT(REPLACE(MONTANT, ',', '.'), 2), ' €') AS MONTANT ".
             " FROM PMT_TRANSACTIONS_PRIV t INNER JOIN PMT_STATUT s ON (t.STATUT = s.UID) ".
             " WHERE PORTEUR_ID = '".$porteurId."' ".
             " UNION ".
             " SELECT UID, 'BANCAIRE' AS TYPE, CONVERT(DATE_FORMAT(STR_TO_DATE(DATE_EFFECTIVE, '%Y%m%d'), '%d/%m/%Y') USING utf8) AS DATE_EMISSION, ".
             " CONVERT(DATE_FORMAT(STR_TO_DATE(DATE_COMPENSATION, '%Y%m%d'), '%d/%m/%Y') USING utf8) AS DATE_VALIDATION, ".
             " '' AS DATE_ENVOI, '' AS DATE_REMBOURSEMENT, 'Remboursé' AS TITLE, CONCAT(FORMAT((MONTANT_NET/100), 2), ' €') AS MONTANT ".
             " FROM PMT_TRANSACTIONS t ".
             " WHERE ID_PORTEUR = '".$porteurId."' ";             
    $result = executeQuery($query);
    if (is_array($result) && count($result) > 0)
    {
        foreach ($result as $value)
        {
            $array[] = $value;
        }
        return $array;
    }
}

$data = getData($_REQUEST["porteurId"]);
header("Content-Type: text/plain");
$paging = array(
    'success'=> true,
    'total'=> count($data),
    'data'=> $data
    );

echo G::json_encode($paging);

?>
