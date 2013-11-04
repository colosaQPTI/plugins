<?php
ini_set ( 'error_reporting', E_ALL );
ini_set ( 'display_errors', True );
if (($RBAC_Response=$RBAC->userCanAccess("PM_LOGIN"))!=1) return $RBAC_Response;
global $RBAC;

$access = $RBAC->userCanAccess('PM_USERS');
if( $access != 1 ){
  switch ($access)
  {
  	case -1:
  	  G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels');
  	  G::header('location: ../login/login');
  	  die;
  	break;
  	case -2:
  	  G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_SYSTEM', 'error', 'labels');
  	  G::header('location: ../login/login');
  	  die;
  	break;
  	default:
  	  G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels');
  	  G::header('location: ../login/login');
  	  die;
  	break;  	
  }
}

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
$oHeadPublisher->addExtJsScript('fieldcontrol/actionsInbox', false);    //adding a javascript file .js
$oHeadPublisher->assign('CONFIG', $Config);
$oHeadPublisher->assign('FORMATS',$c->getFormats());

G::RenderPage('publish', 'extJs');