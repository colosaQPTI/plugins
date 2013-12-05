<?php
G::LoadClass ( 'case' );
G::LoadClass ( 'configuration' );
G::loadClass ( 'pmFunctions' );

$start    = isset($_POST['start']) ? $_POST['start'] : 0;
$limit    = isset($_POST['limit']) ? $_POST['limit'] : 2000000;
$USER_UID = $_SESSION ['USER_LOGGED'];
$Where    ='';
$array    = Array ();
$total    = ''; 
$rolID    = isset($_GET['rolID'])?$_GET['rolID']:'';
$_GET['rolID'] =isset( $_GET['rolID'])? $_GET['rolID']:'';
$_POST['idInbox'] = isset($_POST['idInbox'])?$_POST['idInbox']:'';
if(isset($_REQUEST) && $_REQUEST['TYPE'] == 'TableCombo')
{
      
    $sQuery = "SELECT A.ADD_TAB_NAME as ID, A.ADD_TAB_NAME as NAME, A.ADD_TAB_DESCRIPTION 
               FROM ADDITIONAL_TABLES  AS A
               WHERE  A.ADD_TAB_UID <> '' AND A.PRO_UID <>'' ";
    $aData = executeQuery ($sQuery);
    
    foreach ( $aData as $value ) 
    {
        $query = "SELECT JOIN_QUERY FROM PMT_INBOX_JOIN 
        WHERE JOIN_ROL_CODE = '" . mysql_escape_string($_GET ['rolID']) ."' AND JOIN_QUERY != '' AND JOIN_ID_INBOX = '".$_POST['idInbox']."' ";     
        $newOptions = executeQuery ( $query );
        $innerJoin = isset ( $newOptions [1]['JOIN_QUERY'] ) ? $newOptions [1]['JOIN_QUERY'] : '';
        $value['INNER_JOIN'] = $innerJoin;
        $array [] = $value;
    }
    $total = count ( $aData );
}
$_REQUEST['idTable'] = isset($_REQUEST['idTable'])?$_REQUEST['idTable']:'';
if(isset($_REQUEST) && $_REQUEST['idTable'] != '')
{
    $query = " SELECT F.FLD_UID AS ID, F.FLD_DESCRIPTION AS NAME 
                FROM FIELDS F, PMT_INBOX_FIELDS PO
                WHERE F.ADD_TAB_UID = '".$_REQUEST['idTable']."' AND F.FLD_UID != PO.FLD_UID
                GROUP BY F.FLD_UID
            ";
    $fields = executeQuery($query);
    $i = 1;
    foreach($fields as $index)
    {
        $array[] = $index;
    }
    $total = count ( $fields );
    
}
if (isset ( $_REQUEST ['idInbox'] ) && $_REQUEST ['idInbox'] != '') 
{
    $query = " SELECT ADD_TAB_NAME AS ID, ADD_TAB_NAME AS NAME
                FROM PMT_INBOX_FIELDS
                INNER JOIN ADDITIONAL_TABLES ON PMT_INBOX_FIELDS.ID_TABLE = ADDITIONAL_TABLES.ADD_TAB_UID
                WHERE PMT_INBOX_FIELDS.ID_INBOX = '".$_REQUEST['idInbox']."'
                GROUP BY ADD_TAB_UID";
    $fields = executeQuery($query);
    
    $i = 1;
    foreach($fields as $index)
    {
        $array[] = $index;
    }
    $total = count ( $fields );
 
}
header("Content-Type: text/plain");
    $paging = array(
        'success'=> true,
        'total'=> $total,
        'data'=> array_splice($array,$start,$limit)
    );  

echo json_encode($paging);

?>
