<?php
/**
 * class.NordPDC.php
 *  
 */

  class NordPDCClass extends PMPlugin {
    function __construct() {
      set_include_path(
        PATH_PLUGINS . 'NordPDC' . PATH_SEPARATOR .
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