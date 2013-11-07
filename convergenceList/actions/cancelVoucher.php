<?php

G::loadClass('pmFunctions');
G::LoadClass("case");
header("Content-Type: text/plain");
$array = array();
$array = $_REQUEST['array'];
$items = json_decode($array, true);
$array = array();
$oCase = new Cases ();
$messageInfo = "OK";
foreach ($items as $item)
{
    $fields = convergence_getAllAppData($item['APP_UID']);
    $montantSaisi = $fields['FV_MONTANT'];
    $montantSaisi = str_replace(',', '.', $montantSaisi);
    $montant = ((float) $montantSaisi)*100;
    $sousMontant = array($fields['FV_THEMATIQUE']=>$montant);
    $result = limousinProject_nouvelleTransaction('01', $fields['PORTEUR_ID'], 'C', $montant, $sousMontant);
}
$paging = array('success' => true, 'messageinfo' => $result);
echo G::json_encode($paging);
?>
