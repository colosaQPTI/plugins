<?php   

    ini_set ( 'error_reporting', E_ALL );
    ini_set ( 'display_errors', True );
    G::LoadClass('case');
    G::LoadClass('configuration');
    G::loadClass('pmFunctions');    
    $oHeadPublisher   =& headPublisher::getSingleton();
    $language = SYS_LANG;
    $oHeadPublisher->assign('language', $language);
    $oHeadPublisher->addExtJsScript(PATH_PLUGINS.SYS_COLLECTION.'/configUsers', false, true); 
    G::RenderPage('publish', 'extJs');
?> 
<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type">
<link href="/plugin/fieldcontrol/icons.css" type="text/css" rel="stylesheet">

