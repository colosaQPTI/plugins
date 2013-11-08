<?php   

    ini_set ( 'error_reporting', E_ALL );
    ini_set ( 'display_errors', True );
    G::LoadClass('case');
    G::LoadClass('configuration');
    G::loadClass('pmFunctions');    
    $oHeadPublisher   =& headPublisher::getSingleton();  
    $USR_UID = $_SESSION['USER_LOGGED'];
    
//calculating the max upload file size;
	$POST_MAX_SIZE   = ini_get('post_max_size');
	$mul             = substr($POST_MAX_SIZE, -1);
	$mul             = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));
	$postMaxSize     = (int)$POST_MAX_SIZE * $mul;
	
	$UPLOAD_MAX_SIZE = ini_get('upload_max_filesize');
	$mul             = substr($UPLOAD_MAX_SIZE, -1);
	$mul             = ($mul == 'M' ? 1048576 : ($mul == 'K' ? 1024 : ($mul == 'G' ? 1073741824 : 1)));
	$uploadMaxSize   = (int)$UPLOAD_MAX_SIZE * $mul;
	
	if ( $postMaxSize < $uploadMaxSize ) $uploadMaxSize = $postMaxSize;
	    

    $MODE = 'edit';
    $TYPE = (isset($_GET['type'])) ? $_GET['type'] : '';
    $oHeadPublisher->assign('USR_UID', $USR_UID); 
    $oHeadPublisher->assign('MODE', $MODE);
    $oHeadPublisher->assign('TYPE', $TYPE);
    $oHeadPublisher->assign('MAX_FILES_SIZE', ' (' . $UPLOAD_MAX_SIZE . ') ');
    $oHeadPublisher->addExtJsScript(PATH_PLUGINS.SYS_COLLECTION.'/myProfile', false, true); 

    G::RenderPage('publish', 'extJs');
?>
<script type='text/javascript' src='/plugin/limousinProject/varGlobal.js'></script>
<script type='text/javascript' src='/plugin/obladyConvergence/resize_iframe.js'></script>  
<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type">
<link href="/plugin/fieldcontrol/icons.css" type="text/css" rel="stylesheet">

