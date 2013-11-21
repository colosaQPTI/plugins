<?php
require_once ("classes/interfaces/dashletInterface.php");

class dashletlimousinActMonExpDashlet implements DashletInterface
{
  const version = '1.0';

  private $role;
  private $total;

  public static function getAdditionalFields($className)
  {
    $additionalFields = array();
    $cnn = Propel::getConnection("rbac");
    $stmt = $cnn->createStatement();

    $arrayRole = array();

    $sql = "SELECT ROL_CODE
            FROM   ROLES
            WHERE  ROL_SYSTEM = '00000000000000000000000000000002' AND ROL_STATUS = 1
            ORDER BY ROL_CODE ASC";
    $rsSQL = $stmt->executeQuery($sql, ResultSet::FETCHMODE_ASSOC);
    while ($rsSQL->next()) 
    {
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

  public static function getXTemplate($className)
  {
    return "<iframe src=\"{" . "page" . "}?DAS_INS_UID={" . "id" . "}\" width=\"{" . "width" . "}\" height=\"207\" frameborder=\"0\"></iframe>";
  }

  public function setup($config)
  {
    $this->role = $config["DAS_ROLE"];
    $this->note = $config["DAS_NOTE"];
  }

  public function render($width = 300)
  {
    $cnn = Propel::getConnection("workflow");
    $stmt = $cnn->createStatement();

    $arrayData = array();

    $sql = "SELECT d.FI_NOM AS NOM, ".
           " d.FI_PRENOM AS PRENOM, ".
           " p.RAISONSOCIALE AS PRESTATAIRE, ".
           " p.VILLE AS VILLE, ".
           " th.libelle AS THEMATIQUE, ".
           " CONCAT(FORMAT(SUM(REPLACE(t.MONTANT, ',', '.')), 2), ' €') AS MONTANT, ".
           " SUM(REPLACE(t.MONTANT, ',', '.')) AS MONTANT_MATH ".
           " FROM PMT_DEMANDES d INNER JOIN PMT_TRANSACTIONS_PRIV t ON (d.PORTEUR_ID = t.porteur_ID) ".
           " INNER JOIN PMT_PRESTATAIRE p ON (t.CODE_PARTENAIRE = p.PARTENAIRE_UID) ".
           " INNER JOIN PMT_THEMATIQUES th ON (t.thematique = th.code_reseau) ".
           " WHERE d.STATUT <> 0 ".
           " GROUP BY d.PORTEUR_ID , p.PARTENAIRE_UID , t.THEMATIQUE";
    $rsSQL = $stmt->executeQuery($sql, ResultSet::FETCHMODE_ASSOC);
    while ($rsSQL->next())
    {
      $row = $rsSQL->getRow();
      $arrayData[] = array("nom" => $row["NOM"], "prenom" => $row["PRENOM"], "prestataire" => $row["PRESTATAIRE"], "ville" => $row["VILLE"], "thematique" => $row["THEMATIQUE"], "montant" => $row["MONTANT"]);
      $total+=$row["MONTANT_MATH"];
    }
    $total .= ' €';
    ///////
    $dashletView = new dashletlimousinActMonExpDashletView($arrayData, $total);
    $dashletView->templatePrint();
  }
}

class dashletlimousinActMonExpDashletView extends Smarty
{
  private $smarty;

  private $user;
  private $total;

  public function __construct($u, $t)
  {
    $this->user = $u;
    $this->total = $t;

    $this->smarty = new Smarty();
    $this->smarty->compile_dir  = PATH_SMARTY_C;
    $this->smarty->cache_dir    = PATH_SMARTY_CACHE;
    $this->smarty->config_dir   = PATH_THIRDPARTY . "smarty/configs";
    $this->smarty->caching      = false;
    $this->smarty->templateFile = PATH_PLUGINS . "limousinActMonExpDashlet" . PATH_SEP . "views" . PATH_SEP . "dashletlimousinActMonExpDashlet.html";
  }

  public function templateRender()
  {
    $this->smarty->assign("user", $this->user);
    $this->smarty->assign("total", $this->total);

    return ($this->smarty->fetch($this->smarty->templateFile));
  }

  public function templatePrint()
  {
    echo $this->templateRender();
    exit(0);
  }
}
?>
