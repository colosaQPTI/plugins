<?php
G::LoadClass ('pmFunctions');
$POST['RNE']='';
$POST['ETAB_NAME']='';
$POST['VILLE']='';
$POST['CODE_OPER']='';
$sWhere = ' WHERE STATUT=1 ';


/*if(isset($_POST['codeOper']) && $_POST['codeOper'] !=""){
    $sWhere .= 'AND CODE_OPER_ELIGIBLE = '.$_POST['codeOper'].' ';
    $POST['CODE_OPER']=$_POST['codeOper'];
}
if(isset($_POST['rne']) && $_POST['rne'] !=""){
    $sWhere .= "AND RNE LIKE '%".$_POST['rne']."%' ";
    $POST['RNE']=$_POST['rne'];
}*/
if(isset($_POST['filtre']) && $_POST['filtre'] !=""){
    $sWhere .= "AND (TYPE_ETAB IN (SELECT TYPE FROM PMT_TYPE_LYCEE WHERE FILTRE = ".$_POST['filtre'].") OR TYPE_ETAB = '0') ";
}
if(isset($_POST['nom']) && $_POST['nom'] !=""){
    $sWhere .= "AND NOM LIKE '%".mysql_escape_string($_POST['nom'])."%' ";
    $POST['ETAB_NAME_POPUP']=$_POST['nom'];
}
if(isset($_POST['ville']) && $_POST['ville'] !=""){
    $sWhere .= "AND VILLE LIKE '%".$_POST['ville']."%' ";
    $POST['VILLE']=$_POST['ville'];
}
$sSQL = "SELECT RNE, NUM_DOSSIER, NOM, ADR1, ADR2, VILLE, CP
            FROM
              PMT_ETABLISSEMENT $sWhere ORDER BY NOM";
$aResult = executeQuery ($sSQL);
$aRows = array('RNE' => 'char', 'NOM' => 'char', 'ADR1' => 'char', 'VILLE' => 'char', 'CP' => 'char', 'SELECT_ETAB' => 'char');
$aDatas[] = $aRows;
foreach($aResult as $row){
    $sLink='<span class="RowLink"><a class="tableOption" href="#" onClick="setEtabUid(\''.$row['RNE'].'\');">Sélectionner</a></span>';
    $aRows = array('RNE' => $row['RNE'], 'NOM' => $row['NOM'],'ADR1' => $row['ADR1'], 'VILLE' => $row['VILLE'],'CP' => $row['CP'], 'SELECT_ETAB' =>$sLink );
    $aDatas[] = $aRows;
}   
global $_DBArray;           
$_DBArray['LIST']     = $aDatas;
$_SESSION['_DBArray'] = $_DBArray;
$criteria = new Criteria('dbarray');
$criteria->setDBArrayTable('LIST');  
$G_PUBLISH = new Publisher;  
$G_PUBLISH->AddContent('xmlform', 'xmlform', SYS_COLLECTION.'/etab_filters','',$POST);
$G_PUBLISH->AddContent('propeltable', SYS_COLLECTION.'/paged-table', SYS_COLLECTION.'/searchEtab', $criteria);
G::RenderPage('publish',"raw");
?>



