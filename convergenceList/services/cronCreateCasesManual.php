<?php
ini_set ( 'error_reporting', E_ALL );
ini_set ( 'display_errors', True );
  executeCron(); 
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


?>