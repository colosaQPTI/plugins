<?php
  
$G_PUBLISH = new Publisher;

$oHeadPublisher =& headPublisher::getSingleton();

function obtainRoleInfo($name){
  G::LoadClass('pmFunctions');
  $queryRole = "SELECT * FROM ROLES WHERE ROL_CODE = '$name' ";  
  $queryRole1 = executeQuery($queryRole,'rbac');
  $res = '';
  if(isset($queryRole1) && $queryRole1!='')
    $res = $queryRole1[1]['ROL_UID'];  
  
  return $res;
}
function obtainSWInbox($rolID){
  G::LoadClass('pmFunctions');
  
  $sQuery = " SELECT PM_SW_INBOX FROM PMT_PM_INBOX_ROLES
			WHERE PM_ROL_CODE = '".$rolID."' ";
  	
	$aData = executeQuery ($sQuery);
	$swInboxPm = 1;
	if(count($aData))
			$swInboxPm = $aData[1]['PM_SW_INBOX'];
	
  return $swInboxPm;
}
$roles = Array();
$roles['ROL_UID'] = obtainRoleInfo($_GET['rUID']);
$roles['ROL_CODE'] = $RBAC->getRoleCode($roles['ROL_UID']);
$roles['CURRENT_TAB'] = ($_GET['tab']=='permissions') ? 1 : 0;
$rolID = $roles['ROL_CODE'];
$swInbox = obtainSWInbox($rolID);
$oHeadPublisher->assign('rolID', $rolID); 

$oHeadPublisher->assign('ROLES', $roles);

$oHeadPublisher->assign('SW_INBOX', $swInbox);

$oHeadPublisher->addExtJsScript('fieldcontrol/inboxRelationPermission', false);    //adding a javascript file .js

G::RenderPage('publish', 'extJs');
	
?>

<meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type">
<link href="/plugin/fieldcontrol/icons.css" type="text/css" rel="stylesheet">