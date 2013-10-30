<?php
G::LoadClass ('pmFunctions');
$POST['PARTENAIRE_UID']='';
$POST['PRESTA_NAME']='';
$POST['VILLE']='';
$POST['CODE_OPER']='';
$sWhere = ' WHERE STATUT=1 AND upper(TYPE_PRESTA) like "PRIVATIF" ';


/*if(isset($_POST['codeOper']) && $_POST['codeOper'] !=""){
    $sWhere .= 'AND CODE_OPER_ELIGIBLE = '.$_POST['codeOper'].' ';
    $POST['CODE_OPER']=$_POST['codeOper'];
}*/
if(isset($_POST['partenaireUid']) && $_POST['partenaireUid'] !=""){
    $sWhere .= "AND PARTENAIRE_UID LIKE '%".$_POST['partenaireUid']."%' ";
    $POST['PARTENAIRE_UID']=$_POST['partenaireUid'];
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
            FROM
              PMT_PRESTATAIRE $sWhere ORDER BY RAISONSOCIALE";
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



