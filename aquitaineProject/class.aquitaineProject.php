<?php
/**
 * class.aquitaineProject.php
 *  
 */

  class aquitaineProjectClass extends PMPlugin {
    function __construct() {
      set_include_path(
        PATH_PLUGINS . 'aquitaineProject' . PATH_SEPARATOR .
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