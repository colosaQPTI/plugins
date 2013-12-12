<?php
/**
 * class.limousinPlanMonExpDashlet.php
 *  
 */

  class limousinPlanMonExpDashletClass extends PMPlugin {
    function __construct() {
      set_include_path(
        PATH_PLUGINS . 'limousinPlanMonExpDashlet' . PATH_SEPARATOR .
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