<?php

$G_MAIN_MENU            = 'processmaker';
$G_SUB_MENU             = 'users';
$G_ID_MENU_SELECTED     = 'USERS';
$G_ID_SUB_MENU_SELECTED = 'USERS';

$G_PUBLISH = new Publisher;

G::LoadClass('configuration');
$c = new Configurations();
$configPage = $c->getConfiguration('usersList', 'pageSize','',$_SESSION['USER_LOGGED']);
$Config['pageSize'] = isset($configPage['pageSize']) ? $configPage['pageSize'] : 20;

$oHeadPublisher =& headPublisher::getSingleton();
$language = SYS_LANG;
$oHeadPublisher->assign('language', $language);
$oHeadPublisher->addExtJsScript('fieldcontrol/customizedLabel', false);    //adding a javascript file .js
$oHeadPublisher->assign('CONFIG', $Config);
$oHeadPublisher->assign('FORMATS',$c->getFormats());

G::RenderPage('publish', 'extJs');