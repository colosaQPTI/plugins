<?php


class cronCreateCasesClassCron   
{
   /**
   * Default Constructor for the class
   */
  function __construct (  ) {
    
  }

  function executeCron()
  {
    $pluginFile = PATH_PLUGINS.'convergenceList'.PATH_SEP.'services'.PATH_SEP.'class.createCasescron.php';
    if(file_exists($pluginFile)){
        G::LoadClass('plugin');
        require_once($pluginFile);
    }
      
    $plugin = new archivedCasesClassCron();
    $plugin->followUpActions();
    
    
  }

}


?>