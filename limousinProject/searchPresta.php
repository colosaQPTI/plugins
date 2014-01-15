<?php
G::LoadClass ('pmFunctions');
$POST['THEMATIQUE']='';
$POST['PRESTA_NAME']='';
$POST['VILLE']='';
$POST['CODE_OPER']='';
$sWhere = ' WHERE STATUT=1 AND upper(TYPE_PRESTA) like "PRIVATIF" ';


/*if(isset($_POST['codeOper']) && $_POST['codeOper'] !=""){
    $sWhere .= 'AND CODE_OPER_ELIGIBLE = '.$_POST['codeOper'].' ';
    $POST['CODE_OPER']=$_POST['codeOper'];
}*/
if(isset($_POST['thematique']) && $_POST['thematique'] !=""){
    switch($_POST['thematique'])
    {
        case '165' : $sWhere .= "AND TH_CINE like '1' "; break;
        case '166' : $sWhere .= "AND TH_SPECTACLE like '1' "; break;
        case '167' : $sWhere .= "AND TH_ACHAT like '1' "; break;
        case '168' : $sWhere .= "AND TH_ARTS like '1' "; break;
        case '169' : $sWhere .= "AND TH_SPORT like '1' "; break;
        case '170' : $sWhere .= "AND (TH_ADH_ART like '1' OR TH_ADH_SPORT = '1') "; break;
    }
    $POST['THEMATIQUE']=$_POST['thematique'];
}
if(isset($_POST['raisonsociale']) && $_POST['raisonsociale'] !=""){
    $sWhere .= "AND RAISONSOCIALE LIKE '%".mysql_escape_string($_POST['raisonsociale'])."%' ";
    $POST['PRESTA_NAME']=$_POST['raisonsociale'];
}
if(isset($_POST['ville']) && $_POST['ville'] !=""){
    $sWhere .= "AND VILLE LIKE '%".$_POST['ville']."%' ";
    $POST['VILLE']=$_POST['ville'];
}
$sSQL = "SELECT PARTENAIRE_UID, RAISONSOCIALE, VILLE
            FROM (SELECT PARTENAIRE_UID, RAISONSOCIALE, VILLE, STATUT, TYPE_PRESTA, TH_CINE, TH_SPECTACLE, TH_ACHAT, TH_ARTS, TH_SPORT, TH_ADH_ART, TH_ADH_SPORT
                  FROM PMT_PRESTATAIRE
                  WHERE ADR_ACTIV1 IS NULL
                  UNION ALL
                  SELECT PARTENAIRE_UID, ADR_ACTIV1, BUR_ACTIV, STATUT, TYPE_PRESTA, TH_CINE, TH_SPECTACLE, TH_ACHAT, TH_ARTS, TH_SPORT, TH_ADH_ART, TH_ADH_SPORT
                  FROM PMT_PRESTATAIRE
                  WHERE ADR_ACTIV1 IS NOT NULL ) tmpPartenaire $sWhere ORDER BY RAISONSOCIALE";
$aResult = executeQuery ($sSQL);
$aRows = array('PARTENAIRE_UID' => 'char', 'RAISONSOCIALE' => 'char', 'VILLE' => 'char', 'SELECT_ETAB' => 'char');
$aDatas[] = $aRows;
foreach($aResult as $row){
    $sLink='<span class="RowLink"><a class="tableOption" href="#" onClick="setPrestaUid(\''.$row['PARTENAIRE_UID'].'\');">OK</a></span>';
    $aRows = array('PARTENAIRE_UID' => $row['PARTENAIRE_UID'], 'PRESTA_NAME' => $row['RAISONSOCIALE'], 'VILLE' => $row['VILLE'], 'SELECT_ETAB' =>$sLink );
    $aDatas[] = $aRows;
}   
global $_DBArray;           
$_DBArray['LIST']     = $aDatas;
$_SESSION['_DBArray'] = $_DBArray;
$criteria = new Criteria('dbarray');
$criteria->setDBArrayTable('LIST');  
$G_PUBLISH = new Publisher;  
$G_PUBLISH->AddContent('xmlform', 'xmlform', SYS_COLLECTION.'/presta_filters','',$POST);
$G_PUBLISH->AddContent('propeltable', SYS_COLLECTION.'/paged-table', SYS_COLLECTION.'/searchPresta', $criteria);
G::RenderPage('publish',"raw");
?>



