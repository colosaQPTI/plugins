<?php

//extranet
define('LimousinPort', '8084');
//define('HostName', 'belim.adequation.com');
define('HostName', '172.17.20.29:8084');
define('gpIdPartenaire', '89c7034503fb5da547bd3f684d50fa82');
define('gpIdCarteActive', '222');
//sftp
define('port_ftp', '22');
define('username_ftp', 'ad_belim');
define('pwd_ftp', 'QsS5oboj7Jj5BJ4TRE');
define('serveur_ftp', '217.108.231.49');
define('protocol_transfert', 'sftp');
//WebService
//define('wsHote', 'https://www.gaiacardsystem.com/api/v09/');
define('wsHote_Url', 'https://extranet.aqoba-preprod.customers.artful.net/api/v09/');
define('wsToken_param', '?access_token=99ac21619656c825e788ffb8ac6bfa23f08f4b08');
define('wsPrestaId', '00028');
G::LoadClass("plugin");

class limousinProjectPlugin extends PMPlugin
{
  
  
  public function limousinProjectPlugin($sNamespace, $sFilename = null)
  {
    $res = parent::PMPlugin($sNamespace, $sFilename);
    $this->sFriendlyName = "limousinProject Plugin";
    $this->sDescription  = "Autogenerated plugin for class limousinProject";
    $this->sPluginFolder = "limousinProject";
    $this->sSetupPage    = "setup";
    $this->iVersion      = 1;
    //$this->iPMVersion    = 2425;
    $this->aWorkspaces   = null;
    //$this->aWorkspaces = array("os");
    
    
    
    return $res;
  }

  public function setup()
  {
    $this->setCompanyLogo("/plugin/limousinProject/limousinProject.png");
    $this->registerPmFunction();
    $this->redirectLogin("PROCESSMAKER_LIMOUSINPROJECT", "users/users_List");  
  }

  public function install()
  {
    $RBAC = RBAC::getSingleton() ;
    $RBAC->initRBAC();
    $roleData = array();
    $roleData["ROL_UID"] = G::GenerateUniqueId();
    $roleData["ROL_PARENT"] = "";
    $roleData["ROL_SYSTEM"] = "00000000000000000000000000000002";
    $roleData["ROL_CODE"] = "PROCESSMAKER_LIMOUSINPROJECT";
    $roleData["ROL_CREATE_DATE"] = date("Y-m-d H:i:s");
    $roleData["ROL_UPDATE_DATE"] = date("Y-m-d H:i:s");
    $roleData["ROL_STATUS"] = "1";
    $RBAC->createRole($roleData);
    $RBAC->createPermision("PM_LIMOUSINPROJECT");
  }
  
  public function enable()
  {
    
  }

  public function disable()
  {
    
  }
  
}

$oPluginRegistry = &PMPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin("limousinProject", __FILE__);
