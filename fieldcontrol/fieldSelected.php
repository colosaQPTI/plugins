<?php   
    G::LoadClass('case');
    G::LoadClass('configuration');
    G::loadClass('pmFunctions');    
    $oHeadPublisher   =& headPublisher::getSingleton();   
    $rolID = $_GET['rolID'];
    $rolName = $_GET['rName'];
    $sQuery = " SELECT ID_TABLE  FROM PMT_INBOX_FIELDS WHERE ROL_CODE  = '" . $rolID . "'  ";
	$aDatos = executeQuery ( $sQuery );
	$idTable = '';
	if(isset($aDatos[1]['ID_TABLE']) && $aDatos[1]['ID_TABLE'] != '')
		$idTable = $aDatos[1]['ID_TABLE'];
	$language = SYS_LANG;
    $oHeadPublisher->assign('rolID', $rolID); 
    $oHeadPublisher->assign('idpmTable', $idTable); 
    $oHeadPublisher->assign('rolName', $rolName);
    $oHeadPublisher->assign('language', $language);
    $oHeadPublisher->addExtJsScript(PATH_PLUGINS.SYS_COLLECTION.'/fieldSelected', false, true); 
    G::RenderPage('publish', 'extJs');
?> 
<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type">
<link href="/plugin/fieldcontrol/icons.css" type="text/css" rel="stylesheet">

