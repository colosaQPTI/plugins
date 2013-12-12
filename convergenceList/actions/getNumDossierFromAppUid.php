<?php 
G::loadClass ( 'pmFunctions' );
G::LoadClass("case");


if (isset($_POST['app_uid']) && $_POST['app_uid'] != '') {
    
    $reqNum_dossier = 'SELECT NUM_DOSSIER FROM PMT_DEMANDES WHERE APP_UID="' . $_POST['app_uid'].'"';
    $resNum_dossier = executeQuery($reqNum_dossier);

    $paging = array('success' => true, 'num_dossier' => $resNum_dossier[1]['NUM_DOSSIER']);
    echo G::json_encode($paging);
}
?>