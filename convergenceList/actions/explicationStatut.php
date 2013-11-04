<?php 
G::loadClass ( 'pmFunctions' );
G::LoadClass("case");


if (isset($_GET['APP_UID']) && $_GET['APP_UID'] != '') {
    
    $app_uid = $_GET['APP_UID'];
}
else
{
    header("Content-Type: text/plain");

    $array = array();
    $array = $_REQUEST['array'];
    $items = json_decode($array, true);
    $app_uid = $items[0]['APP_UID'];
}
/*
if (isset($_SESSION['APPLICATION']) && $_SESSION['APPLICATION'] != '') {
    
    $app_uid = $_SESSION['APPLICATION'];
   // $_GET['APP_UID'] = $_SESSION['APPLICATION'];
} */
$oCase = new Cases ();
$Fields = $oCase->loadCase($app_uid);
$libelStatut = 'SELECT TITLE FROM PMT_STATUT WHERE UID=' . intval($Fields['APP_DATA']['STATUT']);
$libelRes = executeQuery($libelStatut);
{
    switch (strtolower($libelRes[1]['TITLE'])) 
    {

        case 'refusé' :
            if (count(explode('<br/>&nbsp;-&nbsp;', isset($Fields['APP_DATA']['msgRefus']) ? $Fields['APP_DATA']['msgRefus'] : '')) > 2)
            {
                $messageInfo = 'Le dossier est <b>' . strtolower($libelRes[1]['TITLE']) . '</b> pour les raisons suivantes :';
            }
            else
            {
                $messageInfo = 'Le dossier est <b>' . strtolower($libelRes[1]['TITLE']) . '</b> pour la raison suivante :';
            }
            $messageInfo .= isset($Fields['APP_DATA']['msgRefus']) ? $Fields['APP_DATA']['msgRefus'] : '';
        break;

        case 'incomplet' :
            if (count(explode('<br/>&nbsp;-&nbsp;', isset($Fields['APP_DATA']['msgIncomplet']) ? $Fields['APP_DATA']['msgIncomplet'] : '')) > 2)
            {
                $messageInfo = 'Le dossier est <b>' . strtolower($libelRes[1]['TITLE']) . '</b> car les éléments suivants sont manquants :';
            }
            else
            {
                $messageInfo = 'Le dossier est <b>' . strtolower($libelRes[1]['TITLE']) . '</b> car l\'élément suivant est manquant :';
            }
            $messageInfo .= isset($Fields['APP_DATA']['msgIncomplet']) ? $Fields['APP_DATA']['msgIncomplet'] : '';
            break;

        default :
            if (isset($_REQUEST['callback']) && $_REQUEST['callback'] != '')
            {
                $messageInfo = call_user_func($_REQUEST['callback'], $Fields['APP_DATA']);
            }
            else
                $messageInfo = 'Le dossier est en statut <b>'.strtolower($libelRes[1]['TITLE']).'</b>';
            break;
        
    }
}

if (isset($_GET['APP_UID']) && $_GET['APP_UID'] != '')
{
    G::loadClass('configuration');	
    global $G_PUBLISH;
    $oHeadPublisher =& headPublisher::getSingleton();     
    $conf = new Configurations;
    $oHeadPublisher->assign('MESSAGEINFO', $messageInfo);
    $oHeadPublisher->assign('ADAPTIVEHEIGHT', $_GET['ADAPTIVEHEIGHT']);
    $oHeadPublisher->addExtJsScript('convergenceList/explicationStatut', true );    //adding a javascript file .js
    $oHeadPublisher->addContent('convergenceList/explicationStatut');
    G::RenderPage('publish', 'extJs');
    exit(0);
}
else
{
    $paging = array('success' => true, 'messageinfo' => $messageInfo, 'num_dossier' => $items[0]['NUM_DOSSIER']);
    echo G::json_encode($paging);
}
?>