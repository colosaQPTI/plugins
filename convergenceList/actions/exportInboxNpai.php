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
$type = $_REQUEST['type'];
$ext = $_REQUEST['ext'];
$items = json_decode($array, true);
$flag = 0;
$messageInfo = '';
/* * * Get filter value ** */
$sFieldValue = $_REQUEST['sFieldValue'];
$sFieldName = $_REQUEST['sFieldName'];
$idInbox = $_REQUEST['IdInbox'];
/* * *** End get filter value ** */

$path = 'exportData_' . $idInbox . date("YmdHis");

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
if ($type != 'npai')
{
    $sSQL = "SELECT $dataSelected , APP_UID  FROM  $idTable $sJoins $sWhere $sOrderBy";
}
else
{
        $sSQL = "SELECT $dataSelected FROM  $idTable $sJoins $sWhere $sOrderBy";
}
$rSQL = executeQuery($sSQL);
if ($type != 'npai' && !empty($_REQUEST['callback']))
{
    $rCallback = call_user_func($_REQUEST['callback'], $rSQL); // call an user_func in the class.pmFunction.php of the current dispositif
    $rSQL = $rCallback;
}
else if ($type != 'npai')
{
    $listeApp_uid = array();
    foreach ($rSQL as $k => $npai)
    {//34669712651e874bb6e5ba1068843455
        $query = 'SELECT max(HLOG_DATECREATED) as HLOG_DATECREATED FROM PMT_HISTORY_LOG WHERE HLOG_APP_UID="' . $npai['APP_UID'] . '" AND HLOG_ACTION LIKE "Retour de production%"'; //2013-07-19 04:26:00
        $resultDate = executeQuery($query);
        $qSetPnd = 'SELECT max(HLOG_DATECREATED) as HLOG_DATECREATED,count(*) as nb_pnd FROM PMT_HISTORY_LOG WHERE HLOG_APP_UID="' . $npai['APP_UID'] . '" AND HLOG_ACTION="Classer en PND"'; //2013-07-23 06:13:06
        $rSetPnd = executeQuery($qSetPnd);
        $qSetPnd = 'SELECT max(HLOG_DATECREATED) as HLOG_DATECREATED,count(*) as nb_pnd FROM PMT_HISTORY_LOG WHERE HLOG_APP_UID="' . $npai['APP_UID'] . '" AND HLOG_ACTION="Classer en PND"'; //2013-07-23 06:13:06
        $rUnsetPnd = executeQuery($qUnsetPnd);
        if (isset($resultDate[1]['HLOG_DATECREATED']) && $resultDate[1]['HLOG_DATECREATED'] != '' && isset($rSetPnd[1]['HLOG_DATECREATED']) && $rSetPnd[1]['HLOG_DATECREATED'] != '')
        {/* Modifié le 16/09/13 de HLOG_DATECREATED < $rSetPnd[1]['HLOG_DATECREATED'] à HLOG_DATECREATED > $rSetPnd[1]['HLOG_DATECREATED'] selon le mail de Stéphane du 9 Sept 2013
          -> On doit pouvoir exporter la liste des PND dont l'adresse a été modifiée avec les règles ci-après:
            -> Si chéquier jamais topé PND: Export de tout PND dont l'adresse a été modifiée avant ou après classement PND,
            -> Si chéquier déjà topé PND  et déjà renvoyé dans le passé, on exporte uniquement les dossiers dont l'adresse a été modifiée après la date de renvoi du 1er PND. */
            if ($rSetPnd[1]['nb_pnd'] > 1 && !empty($rUnsetPnd[1]['HLOG_DATECREATED'])) // Si chéquier déjà topé PND
            {
                $query2 = 'SELECT count(*) as NB, HLOG_APP_UID FROM PMT_HISTORY_LOG WHERE HLOG_APP_UID="' . $npai['APP_UID'] . '" AND HLOG_DATECREATED > "' . $resultDate[1]['HLOG_DATECREATED'] . '" AND HLOG_DATECREATED > "' . $rUnsetPnd[1]['HLOG_DATECREATED'] . '" AND HLOG_ACTION="Modification de l\'adresse"';
                $result2 = executeQuery($query2);
                if ($result2[1]['NB'] > 0)
                {
                    $listeApp_uid[] = $npai['APP_UID'];
                    unset($rSQL[$k]['APP_UID']);
                }
                else
                {
                    unset($rSQL[$k]);
                }
            }
            elseif ($rSetPnd[1]['nb_pnd'] == 1)
            {
                $query3 = 'SELECT count(*) as NB, HLOG_APP_UID FROM PMT_HISTORY_LOG WHERE HLOG_APP_UID="' . $npai['APP_UID'] . '" AND HLOG_DATECREATED > "' . $resultDate[1]['HLOG_DATECREATED'] . '" AND HLOG_ACTION="Modification de l\'adresse"';
                $result3 = executeQuery($query3);
                if ($result3[1]['NB'] > 0)
                {
                    $listeApp_uid[] = $npai['APP_UID'];
                    unset($rSQL[$k]['APP_UID']);
                }
                else
                {
                    unset($rSQL[$k]);
                }
            }
            else
            {
                unset($rSQL[$k]);
            }
        }
        else
        {
            unset($rSQL[$k]);
        }
    }
}
$rSQL = array_values($rSQL);
/* * *** End generate doc *** */
$messageInfo .= 'Export terminé.';
exportXls($exportTitle, $rSQL, $exportDescription, $path, $ext);
?>