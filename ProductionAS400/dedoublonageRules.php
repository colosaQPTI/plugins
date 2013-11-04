<?php

ini_set ( 'error_reporting', E_ALL );
ini_set ( 'display_errors', True );
G::LoadClass ( 'case' );
G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );
$oHeadPublisher = & headPublisher::getSingleton ();

$idTable = '';
$rolID = '';
$oHeadPublisher->assign ( 'rolID', $rolID );
$oHeadPublisher->assign ( 'idpmTable', $idTable );
$oHeadPublisher->assign('language', SYS_LANG);
$oHeadPublisher->addExtJsScript ( PATH_PLUGINS . SYS_COLLECTION . '/dedoublonageRules', false, true );
G::RenderPage ( 'publish', 'extJs' );
?>
<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type">
<link href="/plugin/ProductionAS400/productionCss.css" type="text/css"
	rel="stylesheet">

