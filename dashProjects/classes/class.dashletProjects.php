<?php
require_once 'classes' . PATH_SEP . 'interfaces' . PATH_SEP . 'dashletInterface.php';

class dashletProjects implements DashletInterface {

  //const version = '1.0';

  private $config;// nuestros atributos que queremos q persistan

  public static function getAdditionalFields($className) {
    // los campos adicionales de nuestro dashlet
    $additionalFields = array();
    /*$field = new stdclass();
    $field->xtype = 'textarea';
    $field->name = 'DAS_YO';
    $field->fieldLabel = 'Yo';
    $field->width = 50;
    $field->allowBlank = false;
    $additionalFields[] = $field;*/

    # Query
    $txtSql = new stdclass();
    $txtSql->xtype = "textarea";
    $txtSql->id    = "DAS_CUSDDSQL";
    $txtSql->name  = "DAS_CUSDDSQL";
    $txtSql->fieldLabel = "Query";
    $txtSql->width = 420;
    $txtSql->height = 250;
    $txtSql->value = null;    
    $additionalFields[] = $txtSql;
    # End Query

    # DB Connection    

    G::loadClass ( 'pmFunctions' );
    $sQuery = "SELECT DBS_UID,CONCAT(DBS_SERVER,'-',DBS_DATABASE_NAME) AS DBS_DATABASE_NAME FROM DB_SOURCE ";
    $aDatos = executeQuery ( $sQuery );
  
    $arrayCaseType = array ();
    $arrayCaseType[0][0] = "";
    $arrayCaseType[0][1] = "---Select---";
    $i = 1;
    foreach ( $aDatos as $key => $valor ) {
      $arrayCaseType [$i][0] = $valor['DBS_UID'];
      $arrayCaseType [$i][1] = $valor['DBS_DATABASE_NAME'];
      $i++;
    }

    $storeCaseType = new stdclass();
    $storeCaseType->xtype = "arraystore";
    $storeCaseType->idIndex = 0;
    $storeCaseType->fields = array("DBS_UID", "DBS_DATABASE_NAME");
    $storeCaseType->data = $arrayCaseType;

    $cboCaseType = new stdclass();
    $cboCaseType->xtype = "combo";
    $cboCaseType->id    = "DB_CONNECTION";
    $cboCaseType->name  = "DB_CONNECTION";
    $cboCaseType->valueField = "DBS_UID";
    $cboCaseType->displayField = "DBS_DATABASE_NAME";
    $cboCaseType->value = $arrayCaseType[0][0];
    $cboCaseType->store = $storeCaseType;
    $cboCaseType->triggerAction = "all";
    $cboCaseType->mode = "local";
    $cboCaseType->editable = false;
    $cboCaseType->allowBlank = true;
    $cboCaseType->width = 320;
    $cboCaseType->fieldLabel = "Database Connection";
    $cboCaseType->listeners = $listeners;
    $additionalFields[] = $cboCaseType;  
       
    $additionalFields[] = $cboCaseType;
    # End DB Connection
    
    return $additionalFields;
  }

  public static function getXTemplate($className) {
    // el template base, donde definimos el tipo de contenido que se devolverá
    return "<iframe src=\"{page}?DAS_INS_UID={id}\" width=\"{width}\" height=\"207\" frameborder=\"0\"></iframe>";
    //return "xxx";
  }

  public function setup($config) {
    // este método se usa para pro-procesar los datos de la configuración y los guardamos en atributos d ela clase para q persistan
    $this->config = $config;
  }

  public function render ($width = 300) {

    G::loadClass ( 'pmFunctions' );
    if(isset($this->config["DAS_CUSDDSQL"]) && $this->config["DAS_CUSDDSQL"] != ''){
      $sQuery = $this->config["DAS_CUSDDSQL"];
      
      if(isset($this->config["DB_CONNECTION"]) && $this->config["DB_CONNECTION"] != ''){
        
        $sQueryProcess = "SELECT PRO_UID FROM DB_SOURCE WHERE DBS_UID = '".$this->config["DB_CONNECTION"]."' ";
        $resProcess = executeQuery ( $sQueryProcess );
        if(sizeof($resProcess)){          
          $_SESSION['PROCESS'] = $resProcess[1]['PRO_UID'];
          $DB_CONNECTION = $this->config["DB_CONNECTION"];
          $aDatos = executeQuery($sQuery, $DB_CONNECTION);
        }        
      }        
      else
        $aDatos = executeQuery ( $sQuery );

      $arrayQuery = Array ();
      foreach ( $aDatos as $valor ) {
        $arrayQuery [] = $valor;
      }
      
      $paths["{scriptjsPath}"]= PATH_SEP.'plugin'.PATH_SEP."dashProjects".PATH_SEP.'js'.PATH_SEP;
      $paths["{styleCssPath}"] = PATH_SEP.'plugin'.PATH_SEP."dashProjects".PATH_SEP.'css-dashfiles'.PATH_SEP;
      
      $tableQuery = array();
      $tableQuery["{columnQ}"] .=  "<tr>";
      $fields = array_keys($arrayQuery[0]);
      foreach ($fields as $key1 => $f) {        
          $tableQuery["{columnQ}"] .=  "<th>".$f."</th> ";
      }
      $tableQuery["{columnQ}"] .=  "</tr>";  
      foreach ($arrayQuery as $key => $value) {
        $fields = array_keys($value);
        $tableQuery["{tableQ}"] .=  "<tr>";
        foreach ($fields as $key1 => $f) {        
          $tableQuery["{tableQ}"] .=  "<td>".$value[$f]."</td> ";
        }
        $tableQuery["{tableQ}"] .=  "</tr>";      
      }    
      ob_start();    
      include_once(PATH_PLUGINS."dashProjects".PATH_SEP.'templates'.PATH_SEP.'BaseWorkspaceList.html');
      echo str_replace(array_keys($paths), $paths, ob_get_clean());
      echo str_replace(array_keys($tableQuery), $tableQuery, ob_get_clean());
    }
    else{
      print_r("Query Empty");
    }    
    
  }
}
