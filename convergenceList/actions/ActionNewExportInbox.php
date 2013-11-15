<?php
G::loadClass('pmFunctions');
G::LoadClass("case");


/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$objPHPExcel = new PHPExcel();



header("Content-Type: text/plain");
$array = array();
$array = $_REQUEST['array'];
$items = json_decode($array, true);
$flag = 0;
$messageInfo = '';
/* * * Get filter value ** */
$sFieldValue = $_REQUEST['sFieldValue'];
$sFieldName = $_REQUEST['sFieldName'];
/* * *** End get filter value ** */
/* * * Get inbox  ** */
$idInbox = $_REQUEST['IdInbox'];
$path = 'exportData_' . $idInbox . date("YmdHis");
/* * *** End get inbox *** */

$sJoins = '';
$sOrderBy = '';
$sWhere = 'WHERE 1'; // base du where de la requete
$idRol = getRolUser(); // role de la inbox
##### End Variables

/* * *** Get description inbox *** */
$qTitle = "SELECT DESCRIPTION FROM PMT_INBOX WHERE INBOX = '" . $idInbox . "'";
$rTitle = executeQuery($qTitle);
$exportTitle = $rTitle[1]['DESCRIPTION'];
/* * *** End get description inbox *** */
######### 1 Récupérer la pmt table de l'inbox ************/
$qPMT = "SELECT ID_TABLE FROM PMT_INBOX_PARENT_TABLE WHERE ROL_CODE ='" . $idRol . "' AND  ID_INBOX = '" . $idInbox . "'";
$rPMT = executeQuery($qPMT);
$idTable = $rPMT[1]['ID_TABLE']; // Table de la inbox
/* * **** get order by ******** */
$qOrderBy = "SELECT ORDER_BY, FLD_UID, ALIAS_TABLE  FROM PMT_INBOX_FIELDS WHERE ROL_CODE ='$idRol' AND  ID_INBOX = '$idInbox' AND ORDER_BY <> '' ";
$rOrderBy = executeQuery($qOrderBy);
$totOrder = sizeof($rOrderBy);
$contOrder = 1;
if (sizeof($rOrderBy))
{
    $sOrderBy = " ORDER BY  ";
    foreach ($rOrderBy as $row)
    {
        if ($contOrder == $totOrder)
            $sOrderBy .= $row['ALIAS_TABLE'] . '.' . $row['FLD_UID'] . "  " . $row['ORDER_BY'];
        else
            $sOrderBy .= $row['ALIAS_TABLE'] . '.' . $row['FLD_UID'] . "  " . $row['ORDER_BY'] . ",";

        $contOrder++;
    }
}
/* * *** End get order by **** */

/* * *** Get join ************ */
$qJoins = " SELECT JOIN_QUERY FROM PMT_INBOX_JOIN WHERE JOIN_ROL_CODE ='$idRol' AND JOIN_ID_INBOX = '$idInbox'";
$rJoins = executeQuery($qJoins);
if (is_array($rJoins) && count($rJoins) > 0 && isset($rJoins[1]['JOIN_QUERY']))
{
    $sJoins = $rJoins[1]['JOIN_QUERY'];
}
/* * *** End get join **** */

/* * *** Get where ******* */
$sWhere.=' ' . getSqlWhere($idInbox);
### add filter query
if ($sFieldValue != '' && $sFieldName != 'ALL')
{
    $sWhere.= getQueryForSimpleSearch($idInbox, $sFieldName, $sFieldValue, true);
}
if ($sFieldValue != '' && $sFieldName == 'ALL')
{
    $sWhere.= getQueryForMultipleSearch($idInbox, $sFieldValue);
}

/* * *** End get where ******** */

/* * *** Get fields select **** */
$exportDescription = array();
$sDataSelect = "SELECT ALIAS_TABLE, FIELD_NAME, DESCRIPTION, POSITION FROM PMT_INBOX_FIELDS WHERE ID_INBOX = '" . $idInbox . "' AND ROL_CODE ='$idRol' ORDER BY POSITION";
$rDtaSelect = executeQuery($sDataSelect);
$dataSelected = array();
if (sizeof($rDtaSelect))
{
    foreach ($rDtaSelect as $index)
    {
        if ($index['FIELD_NAME'] != 'APP_NUMBER' && $index['FIELD_NAME'] != 'APP_UID')
        {
            $fieldSelect = "SELECT QUERY_SELECT FROM PMT_INBOX_FIELDS_SELECT WHERE ID_INBOX = '" . $idInbox . "' AND
                                                                            ROL_CODE ='" . $idRol . "' AND FIELD_NAME = '" . $index['FIELD_NAME'] . "'  ";
            $datafieldSelect = executeQuery($fieldSelect);
            $totSelectQuery = sizeof($datafieldSelect);
            if (sizeof($datafieldSelect))
            {
                foreach ($datafieldSelect as $row)
                {
                    $dataSelected[intval($index['POSITION'])] = $row['QUERY_SELECT'];
                }
            }
            else
            {
                if ($index['ALIAS_TABLE'] == '')
                    $dataSelectedintval[($index['POSITION'])] = $index['FIELD_NAME'];
                else
                    $dataSelected[intval($index['POSITION'])] = $index['ALIAS_TABLE'] . '.' . $index['FIELD_NAME'];
            }
            $exportDescription[intval($index['POSITION'])] = $index['DESCRIPTION'];
        }
    }
    ksort($dataSelected);
    ksort($exportDescription);
    $dataSelected = implode(', ', $dataSelected);
}
/* * **** End get fields select **** */
$sSQL = "SELECT $dataSelected  FROM  $idTable $sJoins $sWhere $sOrderBy";
$rSQL = executeQuery($sSQL);

/* * *** End generate doc *** */
$messageInfo .= 'Export terminé.';
//$infoArray = exportXls($exportTitle, $rSQL, $exportDescription, $path);
exportXls($exportTitle, $rSQL, $exportDescription, $path);
?>
