<?php
  G::LoadClass('pmFunctions');
  global $G_TMP_MENU;
  global $RBAC;
  require_once ("classes/model/Users.php");
  # Variables
  $users=$_SESSION['USER_LOGGED'];
  $Us = new Users();
  $Roles=$Us->load($users);
  $rolesAdmin=$Roles['USR_ROLE'];
  
  $RBAC->initRBAC(); 
  $showConfiguration = $RBAC->userCanAccess('PM_CONFIGURATION_AS400');
  
  $LegalGroups = array('','','');
  # End Variables  
  
  # Permissions     
  /*if($rolesAdmin=='PROCESSMAKER_ADMIN')
     $G_TMP_MENU->AddIdRawOption('ID_CONFIGURATION', 'ProductionAS400/configurationOptions', "Configuration" );
  else*/
  if($showConfiguration == 1 ){
      $G_TMP_MENU->AddIdRawOption('ID_CONFIGURATION', 'ProductionAS400/configurationOptions', "Configuration" );
  }
  # End Permissions  
?>