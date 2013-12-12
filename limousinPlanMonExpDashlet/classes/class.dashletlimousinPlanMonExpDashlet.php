<?php
require_once ("classes/interfaces/dashletInterface.php");





class dashletlimousinPlanMonExpDashlet implements DashletInterface
{
  const version = '1.0';

  private $role;
  private $note;

  public static function getAdditionalFields($className)
  {
    $additionalFields = array();

    ///////
    $cnn = Propel::getConnection("rbac");
    $stmt = $cnn->createStatement();

    $arrayRole = array();

    $sql = "SELECT ROL_CODE
            FROM   ROLES
            WHERE  ROL_SYSTEM = '00000000000000000000000000000002' AND ROL_STATUS = 1
            ORDER BY ROL_CODE ASC";
    $rsSQL = $stmt->executeQuery($sql, ResultSet::FETCHMODE_ASSOC);
    while ($rsSQL->next()) {
      $row = $rsSQL->getRow();

      $arrayRole[] = array($row["ROL_CODE"], $row["ROL_CODE"]);
    }

    ///////
    $storeRole = new stdclass();
    $storeRole->xtype = "arraystore";
    $storeRole->idIndex = 0;
    $storeRole->fields = array("value", "text");
    $storeRole->data = $arrayRole;

    ///////
    $cboRole = new stdclass();
    $cboRole->xtype = "combo";
    $cboRole->name = "DAS_ROLE";

    $cboRole->valueField = "value";
    $cboRole->displayField = "text";
    $cboRole->value = $arrayRole[0][0];
    $cboRole->store = $storeRole;

    $cboRole->triggerAction = "all";
    $cboRole->mode = "local";
    $cboRole->editable = false;

    $cboRole->width = 320;
    $cboRole->fieldLabel = "Role";
    $additionalFields[] = $cboRole;

    ///////
    $txtNote = new stdclass();
    $txtNote->xtype = "textfield";
    $txtNote->name = "DAS_NOTE";
    $txtNote->fieldLabel = "Note";
    $txtNote->width = 320;
    $txtNote->value = null;
    $additionalFields[] = $txtNote;

    ///////
    return ($additionalFields);
  }
  
  public function setup($config)
  {
    $this->role = $config["DAS_ROLE"];
    $this->note = $config["DAS_NOTE"];
  }
  
  public static function getXTemplate($className)
  {
    return "<iframe src=\"{" . "page" . "}?DAS_INS_UID={" . "id" . "}\" width=\"{" . "width" . "}\" height=\"207\" frameborder=\"0\"></iframe>";
  }
  
  public static function getThematiques($stmt)
  {
    $arrayThematiques = array();
    $sqlThematiqueLabel = "SELECT CODE_RESEAU, LIBELLE FROM PMT_THEMATIQUES ORDER BY CODE_RESEAU";
    $rsSQL = $stmt->executeQuery($sqlThematiqueLabel, ResultSet::FETCHMODE_ASSOC);
    while($rsSQL->next())
    {
        $row = $rsSQL->getRow();
        $arrayThematiques[$row["CODE_RESEAU"]] = array('codeReseau' => $row["CODE_RESEAU"], 'libelle' => $row["LIBELLE"]);
    }
    return $arrayThematiques;
  }
  
  public static function getTransactions($stmt, $result)
  {
      $arrayTransactionsPriv = array();
      $sqlTransactions = "SELECT CONVERT(r.MM_CODE_RESEAU USING utf8) AS THEMATIQUE, 'BANCAIRE' AS TYPE, FORMAT(SUM(MONTANT_NET/100), 2) AS MONTANT ".
      " FROM (SELECT NUM_TPE, IF(TH_CINE = 1, 'TH_CINE', IF(TH_SPECTACLE = 1, 'TH_SPECTACLE', IF(TH_ACHAT = 1, 'TH_ACHAT', IF(TH_ARTS = 1, 'TH_ARTS', IF(TH_ADH_ART = 1, 'TH_ADH_ART', ".
      " IF(TH_ADH_SPORT = 1, 'TH_ADH_SPORT', IF(TH_SPORT = 1, 'TH_SPORT', 'NULL'))))))) as THEMATIQUE ".
      " FROM PMT_PRESTATAIRE WHERE upper(TYPE_PRESTA) LIKE 'BANCAIRE' AND STATUT = 1) p INNER JOIN PMT_RSXTHEMATIQ_MM_PRESTAFIELDTHEMATIQ r ON (p.THEMATIQUE = r.PRESTA_NAMEFIELD) ".
      " INNER JOIN PMT_TRANSACTIONS t ON (p.NUM_TPE = t.ID_COMMERCANT) UNION SELECT THEMATIQUE, TYPE, FORMAT(SUM(REPLACE(MONTANT, ',', '.')), 2) AS MONTANT FROM PMT_TRANSACTIONS_PRIV tp ".
      " INNER JOIN PMT_THEMATIQUES t ON ( tp.thematique = t.CODE_RESEAU) GROUP BY THEMATIQUE , TYPE";
      $rsSQL = $stmt->executeQuery($sqlTransactions, ResultSet::FETCHMODE_ASSOC);
      while($rsSQL->next())
      {
          $row = $rsSQL->getRow();
          if($row["THEMATIQUE"] != '')
          {
            $type = $row["TYPE"];
            $result[$row["THEMATIQUE"]][$type] = $row["MONTANT"];
            $result[$row["THEMATIQUE"]]["total"] += $row["MONTANT"];
            $result["total"] += $row["MONTANT"];
          }
      }
      return $result;
  }

  public function render($width = 300)
  {
    $cnn = Propel::getConnection("workflow");
    $stmt = $cnn->createStatement();
    $result = $this->getThematiques($stmt);
    $result = $this->getTransactions($stmt, $result);
    $dashletView = new dashletlimousinPlanMonExpDashletView($result);
    $dashletView->templatePrint();
  }
}

class dashletlimousinPlanMonExpDashletView extends Smarty
{
  private $smarty;
  private $result;

  public function __construct($result)
  {
    $this->result = $result;

    $this->smarty = new Smarty();
    $this->smarty->compile_dir  = PATH_SMARTY_C;
    $this->smarty->cache_dir    = PATH_SMARTY_CACHE;
    $this->smarty->config_dir   = PATH_THIRDPARTY . "smarty/configs";
    $this->smarty->caching      = false;
    $this->smarty->templateFile = PATH_PLUGINS . "limousinPlanMonExpDashlet" . PATH_SEP . "views" . PATH_SEP . "dashletlimousinPlanMonExpDashlet.html";
  }

  public function templateRender()
  {
    $this->smarty->assign("result", $this->result);
    return ($this->smarty->fetch($this->smarty->templateFile));
  }

  public function templatePrint()
  {
    echo $this->templateRender();
    exit(0);
  }
}
?>
