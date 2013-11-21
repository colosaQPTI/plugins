<?php
/**
 * class.limousinActMonExpDashlet.php
 *  
 */

  class limousinActMonExpDashletClass extends PMPlugin {
    function __construct() {
      set_include_path(
        PATH_PLUGINS . 'limousinActMonExpDashlet' . PATH_SEPARATOR .
        get_include_path()
      );
    }

    function setup()
    {
    }

    function getFieldsForPageSetup()
    {
    }

    function updateFieldsForPageSetup()
    {
    }

  }
?>