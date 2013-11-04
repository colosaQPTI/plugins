<?php
/**
 * class.dashProjects.php
 *  
 */

  class dashProjectsClass extends PMPlugin {
    function __construct() {
      set_include_path(
        PATH_PLUGINS . 'dashProjects' . PATH_SEPARATOR .
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