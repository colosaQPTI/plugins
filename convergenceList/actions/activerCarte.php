<?php

G::loadClass('pmFunctions');
G::LoadClass("case");
header("Content-Type: text/plain");
$porteurId = $_REQUEST['porteur_id'];
$televersement = ( bool ) $_REQUEST['televersement'];
$resultActivation = array( );
$role_user = convergence_getUserRole($_SESSION['USER_LOGGED']);
$success = false;
$messageInfo = 'Unknow Error';
if (!empty($porteurId))
{
    $resultActivation = limousinProject_activationCarte($porteurId, 1, $role_user, $televersement);
    if ( $resultActivation['success'] === TRUE )
        $success = true;
    if ( !$success )
        $messageInfo = $resultActivation['messageInfo'];
    else
        $messageInfo = "Carte activée par le " . $role_user;
}
else
{
    $messageInfo = 'Porteur Id incorrect'   ; 
}
$result = array('success' => $success, 'messageinfo' => $messageInfo);
echo G::json_encode($result);

