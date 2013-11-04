<?php    
    G::LoadClass('case');
    G::LoadClass('configuration');
    G::loadClass('pmFunctions');    
    $oHeadPublisher   =& headPublisher::getSingleton();    
    $oHeadPublisher->assign('language', SYS_LANG);
    $oHeadPublisher->addExtJsScript(PATH_PLUGINS.SYS_COLLECTION.'/groupesRoles', false, true);    
    G::RenderPage('publish', 'extJs');
    
?>  
<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type">
<link href="/plugin/fieldcontrol/icons.css" type="text/css" rel="stylesheet">
