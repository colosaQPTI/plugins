<?php
  G::LoadClass('pmFunctions');
  require_once ("classes/model/Users.php");
  global $G_TMP_MENU;
  global $RBAC;  
  $users=$_SESSION['USER_LOGGED'];
  $userTab = 'Users';
  $inboxTab = 'Admin Inbox';
  $actionTab = 'Actions Inbox';
  $profileTab = 'My Profile';
  $customizedLabel = 'Customize labels';
  
  if(SYS_LANG == 'fr')
  {
  	$userTab = 'Utilisateurs';
  	$inboxTab = 'Administrateur Inbox';
  	$actionTab = 'Actions Inbox';
  	$profileTab = 'Mon Profil';
  	$customizedLabel = 'Customisez les &Eacute;tiquettes';
  }
  $G_TMP_MENU->DisableOptionId("GROUPS");
  $G_TMP_MENU->DisableOptionId("ROLES");
  $G_TMP_MENU->DisableOptionId("USERS");
  $G_TMP_MENU->AddIdRawOption('MT_GROUPS_ROLES', '../fieldcontrol/groupsRoles', "Permissions","","","users" );
  $G_TMP_MENU->AddIdRawOption('USERS', '../fieldcontrol/users_List', $userTab,"","","users" );
  $G_TMP_MENU->AddIdRawOption('INBOX', '../fieldcontrol/admininbox', $inboxTab,"","","users" );
  $G_TMP_MENU->AddIdRawOption('ACTION_INBOX', '../fieldcontrol/actions_Inbox', $actionTab,"","","users" );
  $G_TMP_MENU->AddIdRawOption('MY_PROFILE', '../fieldcontrol/my_profile', $profileTab,"","","users" );
  $G_TMP_MENU->AddIdRawOption('CUSTOMIZED_LABEL', '../fieldcontrol/customizedLabel', $customizedLabel,"","","users" );