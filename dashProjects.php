<?php
G::LoadClass("plugin");

class dashProjectsPlugin extends PMPlugin
{


  public function dashProjectsPlugin($sNamespace, $sFilename = null)
  {
    $res = parent::PMPlugin($sNamespace, $sFilename);
    $this->sFriendlyName = "dashProjects Plugin";
    $this->sDescription  = "Autogenerated plugin for class dashProjects";
    $this->sPluginFolder = "dashProjects";
    $this->sSetupPage    = "setup";
    $this->iVersion      = 1.1;
    //$this->iPMVersion    = 2425;
    $this->aWorkspaces   = null;
    //$this->aWorkspaces = array("os");

    #Dashlet Information
    $dashletClassName = 'dashletProjects';
    $dashletClass = md5($dashletClassName);
    $this->dashletsUids  = array(array('DAS_UID' => $dashletClass,
                                       'DAS_CLASS' => $dashletClassName,
                                       'DAS_TITLE' => 'Dashlet Projects',
                                       'DAS_DESCRIPTION' => '',
                                       'DAS_VERSION' => '1.0',
                                       'DAS_CREATE_DATE' => date('Y-m-d'),
                                       'DAS_UPDATE_DATE' => date('Y-m-d')));

    #End Dashlet Information

    return $res;
  }

  public function setup()
  {        
    $this->registerDashlets();
  }

  public function install()
  {
  }

  public function enable()
  {    
    G::LoadClass('pmFunctions');
    $sQuery="SELECT * FROM DASHLET WHERE DAS_UID = '".$this->dashletsUids[0]['DAS_UID']."' ";
    $resSQuery = executeQuery($sQuery);
    
    if(sizeof($resSQuery) == 0){

      executeQuery("INSERT INTO DASHLET (
                        DAS_UID,
                        DAS_CLASS,
                        DAS_TITLE,
                        DAS_VERSION,
                        DAS_CREATE_DATE
                ) VALUES ('" . $this->dashletsUids[0]['DAS_UID'] . "',
                '" . $this->dashletsUids[0]['DAS_CLASS'] . "',
                '" . $this->dashletsUids[0]['DAS_TITLE'] . "',
                '" . $this->dashletsUids[0]['DAS_VERSION'] . "',
                '" . $this->dashletsUids[0]['DAS_CREATE_DATE'] . "'
                )");
    }    
  }

  public function disable()
  {

  }

}

$oPluginRegistry = &PMPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin("dashProjects", __FILE__);
