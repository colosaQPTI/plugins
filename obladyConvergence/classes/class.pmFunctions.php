<?php
/**
 * class.obladyConvergence.pmFunctions.php
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.
 * *
 */
////////////////////////////////////////////////////
// obladyConvergence PM Functions
//
// Copyright (C) 2007 COLOSA
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////
//GLOBAL : recupere le role pour l'affichage des champs des formulaires
function convergence_getUserRole($usr_uid) {
    $query = "SELECT USR_ROLE FROM USERS WHERE USR_UID='" . $usr_uid . "'";
    $result = executeQuery($query);
    $role = '';
    if (isset($result))
        $role = $result[1]['USR_ROLE'];
    return $role;
}

//GLOBAL : mise a jour du statut et du coup ajout d'une ligne dans les logs.
function convergence_changeStatut($app_uid, $statut, $labelLog = '') {

    try
    {
        $oCase = new Cases ();
        $Fields = $oCase->loadCase($app_uid);
        $oldStatut = $Fields['APP_DATA']['STATUT'];
        $Fields['APP_DATA']['STATUT'] = $statut;

        $oCase->updateCase($app_uid, $Fields);
        if ($labelLog == '')
        {
            $libelStatut = 'SELECT TITLE FROM PMT_STATUT WHERE UID=' . intval($statut);
            $libelRes = executeQuery($libelStatut);
            insertHistoryLogPlugin($app_uid, $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', $libelRes[1]['TITLE'], $statut);
        }
        else
            insertHistoryLogPlugin($app_uid, $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', $labelLog, $statut);
    }
    catch (Exception $e)
    {
        var_dump($e);
        die();
    }
}

//GLOBAL : mise a jour du statut sans ajout d'une ligne dans les logs.
function convergence_changeStatutWithoutHistory($app_uid, $statut) {

    try
    {
        $oCase = new Cases ();
        $Fields = $oCase->loadCase($app_uid);
        $Fields['APP_DATA']['STATUT'] = $statut;

        $test_result = PMFSendVariables($app_uid, $Fields['APP_DATA']);
        $oCase->updateCase($app_uid, $Fields);
    }
    catch (Exception $e)
    {
        var_dump($e);
        die();
    }
}

//GLOBAL : ajouter une ligne dans le compteur des PND  // appeler quand on classe PND
function convergence_insertCompteurPND($dossier) {

    $insert = "INSERT INTO PMT_CPTPND (DOSSIER) VALUES ('$dossier')";
    $resultInsData = executeQuery($insert);
}

//GLOBAL : supprimer une ligne dans le compteur des PND  // appeler quand on enleve des PND
function convergence_deleteCompteurPND($dossier) {

    $delete = "DELETE FROM PMT_CPTPND WHERE DOSSIER = '$dossier'";
    $resultInsData = executeQuery($delete);
}

//GLOBAL : mise a jour de donnee de demande
function convergence_updateDemande($app_uid, $data) {

    $set = '';
    try
    {
        if (is_array($data) && !empty($data))
        {
            $oCase = new Cases ();
            $Fields = $oCase->loadCase($app_uid);
            $Fields['APP_DATA'] = array_merge($Fields['APP_DATA'], $data);

            PMFSendVariables($app_uid, $Fields['APP_DATA']);
            $oCase->updateCase($app_uid, $Fields);
        }
    }
    catch (Exception $e)
    {
        var_dump($e);
        die();
    }
}

//GLOBAL : Insertio ndes données dans l'historique

function insertHistoryLogPlugin($APP_UID, $USR_UID, $CURRENTDATETIME, $VERSION, $NEWAPP_UID, $ACTION, $STATUT = "") {
    $selectVersion = "SELECT MAX(HLOG_VERSION) AS VERSION FROM PMT_HISTORY_LOG WHERE HLOG_APP_UID = '" . $APP_UID . "'";
    $qSelectVersion = executeQuery($selectVersion);
    $versionHistory = 0;
    if (sizeof($qSelectVersion))
        $versionHistory = $qSelectVersion[1]['VERSION'];

    $versionHistory = $versionHistory + 1;

    $ACTION = addslashes($ACTION);

    $Insertdata = "INSERT INTO PMT_HISTORY_LOG (
                          HLOG_UID ,
                          HLOG_APP_UID ,
                          HLOG_USER_UID ,
                          HLOG_DATECREATED ,
                          HLOG_VERSION ,
                          HLOG_CHILD_APP_UID,
                          HLOG_ACTION,
                          HLOG_STATUS
                        )
                        VALUES (
                        NULL , '$APP_UID', '$USR_UID', '$CURRENTDATETIME', '$versionHistory','$NEWAPP_UID','$ACTION','$STATUT'
                        );
              ";
    $resultInsData = executeQuery($Insertdata);

    if ($NEWAPP_UID != '' || $NEWAPP_UID != 0)
    {
        $data = array();
        $data['STATUT'] = '0';
        convergence_updateDemande($APP_UID, $data);
    }
}

//GLOBAL : update history apres edition pour avoir le nouveau statut
function updateHistoryLogStatus($app_uid, $statut) {

    $query = 'UPDATE PMT_HISTORY_LOG SET HLOG_STATUS = "' . $statut . '" WHERE HLOG_CHILD_APP_UID="' . $app_uid . '"';
    $result = executeQuery($query);
    if (sizeof($result))
    {
        return 0;
    }
    else
        return 1;
}

function FredirectTypo3($APP_UID) {

    $caseInstance = new Cases();
    $caseFields = $caseInstance->loadCase($APP_UID);
    $DATA = $caseFields['APP_DATA'];

    if (isset($DATA['FLAGTYPO3']) && $DATA['FLAGTYPO3'] == 'On' && !$DATA['FLAG_ACTION'])
    {
        // Correction du 25/10/2013 pour ne pas avoir d'erreur en front lors de la fin d'un process sans @@FLAG_REDIRECT_PAGE
        /* if (!$DATA['FLAG_REDIRECT_PAGE']){
          //$IPTYPO3 = 'http://'.$_SERVER['HTTP_HOST'].':8084/';
          $IPTYPO3 = 'http://'.HostName.'/';
          $page = $IPTYPO3.'index.php?id=76';
          }
          else */
        if ($DATA['FLAG_REDIRECT_PAGE'])
        {
            $page = $DATA['FLAG_REDIRECT_PAGE'];
            //$hostPort = 'http://' . $_SERVER['HTTP_HOST'] . ':8084/';
            //$hostPort = 'http://' . HostName . '/';
            echo "<script language='javascript'> parent.parent.location.href = '" . $page . "';</script>";
            die();
        }
    }
    else
    {
        if (isset($DATA['FLAG_ACTION']))
        {

            if ($DATA['FLAG_ACTION'] == 'actionCreateCase')
            {

                $query = "SELECT ID_INBOX FROM PMT_STATUT WHERE UID = '" . $DATA['STATUT'] . "'";
                $result = executeQuery($query);
                if (!empty($result))
                    $inbox = isset($result[1]['ID_INBOX']) ? $result[1]['ID_INBOX'] : "";

                if ($inbox)
                {
                    $node = 'NEW_OPTION_' . $inbox;
                    echo "<html><head>
                    <script language='javascript'>
                    parent.location.href = 'http://" . $_SERVER['HTTP_HOST'] . "/sys" . $DATA['SYS_SYS'] . "/" . $DATA['SYS_LANG'] . "/" . $DATA['SYS_SKIN'] . "/convergenceList/inboxDinamic.php?idInbox=" . $inbox . "';
                    var treepanel = parent.parent.Ext.getCmp('tree-panel');
                    var node = treepanel.getNodeById('" . $node . "');
                    node.select();
                    </script></head></html>";
                    die();
                }
                else
                {
                    echo "<script language='javascript'>
                    parent.Ext.getCmp('gridNewTab').store.reload();
                    parent.Ext.getCmp('win2').hide();
                    </script>";
                    die();
                }
            }
            if ($DATA['FLAG_ACTION'] == 'editForms')
            {
                $DYN_UID = $DATA['DYN_UID'];
                $CURRENTDATETIME = $DATA['CURRENTDATETIME'];
                $APP_UID = $DATA['APPLICATION'];
                $PRO_UID = $DATA['PROCESS'];
                $_SESSION['USER_LOGGED'] = $DATA['FLG_INITUSERUID'];
                $_SESSION['USR_USERNAME'] = $DATA['FLG_INITUSERNAME'];
                /* $url = '../convergenceList/casesHistoryDynaformPage_Ajax.php?ACTIONTYPE=edit&actionAjax=historyDynaformGridPreview&DYN_UID=' . $DYN_UID . '&APP_UID=' . $APP_UID . '&PRO_UID=' . $PRO_UID . '&CURRENTDATETIME=' . $CURRENTDATETIME . '&ACTIONSAVE=1';
                  echo "<script language='javascript'> location.href = '" . $url . "';</script>";
                  die(); */
                if(!isset($DATA['FLAG_CONFIRM_EDIT']) || $DATA['FLAG_CONFIRM_EDIT'] == '1')
                {
                    echo "<script language=Javascript>parent.parent.message('Vos changements ont \u00E9t\u00E9 enregistr\u00E9s avec succ\u00E9s');</script>";
                    echo "<script language='javascript'>
                    //parent.Ext.getCmp('gridNewTab').store.reload();
                    //parent.parent.Ext.getCmp('win2').hide();
                    </script>";
                    die();
                }
                else
                {
                    echo "<script language=Javascript>parent.parent.Ext.getCmp('win2').hide();</script>";
                }
            }
            if ($DATA['FLAG_ACTION'] == 'actionAjaxRestartCases')
            {
                $_SESSION['USER_LOGGED'] = $DATA['FLG_INITUSERUID'];
                $_SESSION['USR_USERNAME'] = $DATA['FLG_INITUSERNAME'];
                header("Content-Type: text/plain");
                $paging = array('success' => true, 'messageinfo' => 'Operation Completed');
                echo G::json_encode($paging);
                die();
            }
            if ($DATA['FLAG_ACTION'] == 'actionAjax')
            {
                if (isset($DATA['FLG_INITUSERUID_DOUBLON']) && isset($DATA['FLG_INITUSERNAME_DOUBLON']))
                {
                    $_SESSION['USER_LOGGED'] = $DATA['FLG_INITUSERUID_DOUBLON'];
                    $_SESSION['USR_USERNAME'] = $DATA['FLG_INITUSERNAME_DOUBLON'];
                }
                header("Content-Type: text/plain");
                $paging = array('success' => true, 'messageinfo' => 'Operation Completed');
                echo G::json_encode($paging);
                die();
            }
        }
        else
        {
            header("Location:http://" . $_SERVER['HTTP_HOST'] . "/sys" . $DATA['SYS_SYS'] . "/" . $DATA['SYS_LANG'] . "/" . $DATA['SYS_SKIN'] . "/cases/casesListExtJsRedirector.php");
            die();
        }
    }
}

### unsetSessionVars ($words, $var) -req
function unsetSessionVars($words = 'FLAG') {
    //$words = 'FLAG|FLAG_ACTION';
    $aVarSession = array();
    foreach ($_SESSION as $key => $value)
    {
        $aVarSession [] = $key;
    }
    $list_words = explode('|', $words);
    $aDeleteVarSession = array();
    foreach ($aVarSession as $key => $text)
    {
        foreach ($list_words as $sCad)
        {
            $patron = '#^' . $sCad . '.*#s';
            if (preg_match($patron, trim($text)))
                $aDeleteVarSession[] = $aVarSession[$key];
        }
    }
    $aDeleteVarSession = array_unique($aDeleteVarSession);
    foreach ($aDeleteVarSession as $key => $value)
    {
        unset($_SESSION[$value]);
    }
}

function unsetCasesFlag($words = 'FLAG', $APP_DATA) {
    //$words = 'FLAG|FLAG_ACTION';
    $aVarSession = array();
    foreach ($APP_DATA as $key => $value)
    {
        $aVarSession [] = $key;
    }
    $list_words = explode('|', $words);
    $aDeleteVarSession = array();
    foreach ($aVarSession as $key => $text)
    {
        foreach ($list_words as $sCad)
        {
            $patron = '#^' . $sCad . '.*#s';
            if (preg_match($patron, trim($text)))
                $aDeleteVarSession[] = $aVarSession[$key];
        }
    }
    $aDeleteVarSession = array_unique($aDeleteVarSession);
    foreach ($aDeleteVarSession as $key => $value)
    {
        if ($value != 'FLAG_NON_DOUBLON')
            unset($APP_DATA[$value]);
    }
    return $APP_DATA;
}

//GLOBAL : fonction pour creer un fe_user Typo3 a la confirmation de creation de compte dans PM
function userSettingsPlugin($groupId, $urlTypo3 = 'http://172.17.20.29:8081/') {
    $res = "";
    if (isset($_GET['ER_REQ_UID']))
    {

        //set_include_path(PATH_PLUGINS . 'externalRegistration' . PATH_SEPARATOR . get_include_path());
        require_once PATH_PLUGINS . 'externalRegistration/classes/model/ErConfiguration.php';
        require_once PATH_PLUGINS . 'externalRegistration/classes/model/ErRequests.php';
        require_once PATH_PLUGINS . 'externalRegistration/classes/class.ExternalRegistrationUtils.php';

        $erReqUid = G::decrypt($_GET['ER_REQ_UID'], URL_KEY);
        // Load request
        $erRequestsInstance = new ErRequests();
        $request = $erRequestsInstance->load($erReqUid);

        $data = $request['ER_REQ_DATA'];
        ini_set("soap.wsdl_cache_enabled", "0");
        $hostTypo3 = $urlTypo3 . 'typo3conf/ext/pm_webservices/serveur.php?wsdl';
        $pfServer = new SoapClient($hostTypo3);
        $key = rand();
        $ret = $pfServer->createAccount(array(
            'username' => $data['__USR_USERNAME__'],
            'password' => md5($data['__PASSWORD__']),
            'email' => $data['__USR_EMAIL__'],
            'lastname' => $data['__USR_LASTNAME__'],
            'firstname' => $data['__USR_FIRSTNAME__'],
            'key' => $key,
            'pmid' => $data['USR_UID'],
            'usergroup' => $groupId,
            'cHash' => md5($data['__USR_USERNAME__'] . '*' . $data['__USR_LASTNAME__'] . '*' . $data['__USR_FIRSTNAME__'] . '*' . $key)));

        // Get the group name
        $query = "SELECT CON_VALUE FROM CONTENT WHERE CON_ID = '$groupId' AND CON_LANG='fr' AND CON_CATEGORY='GRP_TITLE' ";
        $result = executeQuery($query);
        $roleName = '';
        if (isset($result))
            $roleName = $result[1]['CON_VALUE'];
        // End Get the group name
        // Change the role
        if ($roleName != '')
        {
            $updateRole = "UPDATE USERS SET USR_ROLE ='$roleName' WHERE USR_UID='" . $data['USR_UID'] . "'";
            $updateRQuery = executeQuery($updateRole);
        }
        // End Change the role
    }

    return $res;
}

//GLOBAL : Recupère toutes les données des chanmps dans le case passé en paramètre
function convergence_getAllAppData($app_id, $upper = 0) {

    G::LoadClass('case');
    $oCase = new Cases();
    $fields = $oCase->loadCase($app_id);
    $fields['APP_DATA']['APP_NUMBER'] = $fields['APP_NUMBER'];

    if ($upper == 1)
    {
        foreach ($fields['APP_DATA'] as $k => $v)
        {
            $fields['APP_DATA'][strtoupper($k)] = $v;
        }
    }

    return $fields['APP_DATA'];
}

//Global permet de récupérer le CODE_OPER en fonction du code du chéquier
function convergence_getCodeOperation($code) {
    $query = 'SELECT NUM_OPER FROM PMT_LISTE_OPER, PMT_TYPE_CHEQUIER WHERE PMT_LISTE_OPER.CODE_OPER = PMT_TYPE_CHEQUIER.CODE_OPER AND PMT_TYPE_CHEQUIER.CODE_CD = ' . $code;
    $result = executeQuery($query);
    if (!empty($result))
    {
        return $result[1]['NUM_OPER'];
    }
    else
        return 0;
}

/*
 * GLOBALS
 * Fonction qui renvoi la possibilité que le cas soit un doublon avec un autre.
 *
 */
function make_dedoublonage($process, $app_id, $debug = 0, $lv = 1, $dm = 1) {

    //recuperation des variable du formulaire
    $fields = convergence_getAllAppData($app_id);

    $doublon = 0;

    if ($fields['FLAG_NON_DOUBLON'] == 1)
        return $doublon;

    $where = 'STATUT !=0 AND STATUT !=999 AND NUM_DOSSIER !="' . $fields['NUM_DOSSIER'] . '"';

    $getTableName = 'SELECT * FROM PMT_CONFIG_DEDOUBLONAGE WHERE CD_PROCESS_UID="' . $process . '"';
    $table = executeQuery($getTableName);
    if (!empty($table))
    {
        $uid_config = $table[1]['CD_UID'];
        $table = $table[1]['CD_TABLENAME'];
    }

    $getFieldsQuery = 'SELECT * FROM PMT_COLUMN_DEDOUBLONAGE WHERE CD_INCLUDE_OPTION = 1 AND CD_UID_CONFIG_AS=' . $uid_config;
    $config = executeQuery($getFieldsQuery);
    if (!empty($config) && $table != '')
    {

        foreach ($config as $data)
        {
            $whereOption = array();
            $select_debug .= ',"' . $fields[$data['CD_FIELDNAME']] . '" AS reference,' . strtoupper($data['CD_FIELDNAME']) . ',levenshtein_ratio("' . metaphone($fields[$data['CD_FIELDNAME']]) . '",dm(' . strtoupper($data['CD_FIELDNAME']) . ')),levenshtein_ratio("' . $fields[$data['CD_FIELDNAME']] . '",' . strtoupper($data['CD_FIELDNAME']) . ')';

            $whereOption[] = strtoupper($data['CD_FIELDNAME']) . ' = "' . $fields[$data['CD_FIELDNAME']] . '"';
            if ($dm == 1)
                $whereOption[] = 'levenshtein_ratio("' . metaphone($fields[$data['CD_FIELDNAME']]) . '",dm(' . strtoupper($data['CD_FIELDNAME']) . ')) >= ' . $data['CD_RATIO'];
            if ($lv == 1)
                $whereOption[] = 'levenshtein_ratio("' . $fields[$data['CD_FIELDNAME']] . '",' . strtoupper($data['CD_FIELDNAME']) . ') >= ' . $data['CD_RATIO'];


            $where .= ' AND (' . implode(' OR ', $whereOption) . ')';
            // $whereLev .= ' AND levenshtein_ratio("'.metaphone($fields[$data['CD_FIELDNAME']]).'",dm('.strtoupper($data['CD_FIELDNAME']).')) >= '.$data['CD_RATIO'];
            //SOUNDEX censé etre moi perfofmant que le metaphone du dessus.
            //$whereSound .= ' AND SOUNDEX("'.$fields[$data['CD_FIELDNAME']].'") = SOUNDEX('.strtoupper($data['CD_FIELDNAME']).')';
        }

        $requete = 'SELECT count(*) as NB FROM ' . $table . ' WHERE ' . $where;
        $result = executeQuery($requete);
        //$requeteLev = 'SELECT count(*) as NB FROM '.$table.' WHERE '.$whereLev;
        //$resultLev = executeQuery($requeteLev);
        //$requeteSound = 'SELECT * FROM '.$table.' WHERE '.$whereSound;
        //$resultSound = executeQuery($requeteSound);

        if ($debug != 0)
        {

            $requeteDebug = 'SELECT * FROM ' . $table . ' WHERE ' . $where;
            $resultDebug = executeQuery($requeteDebug);

            //$requeteLevDebug = 'SELECT * FROM '.$table.' WHERE '.$whereLev;
            // $resultLevDebug = executeQuery($requeteLevDebug);

            G::pr($select_debug);
            G::pr($requeteDebug);
            G::pr($resultDebug);

            /* G::pr('AVEC LEVENSHTEIN');
              G::pr($requeteLevDebug);
              G::pr($resultLevDebug); */
        }
        if (isset($result) && $result[1]['NB'] > 0)      // || isset($resultLev) && $resultLev[1]['NB'] > 0)
        {
            $doublon = 1;
        }
    }

    if ($debug != 0)
    {
        G::pr('Doublon : ' . $doublon);
    }
    return $doublon;
}

//GLobaL : return all the app_uid for the doublon
function getAllDoublon($process, $app_id) {

    //recuperation des variable du formulaire
    $fields = convergence_getAllAppData($app_id);

    $where = 'STATUT !=0 AND STATUT !=999 AND NUM_DOSSIER !="' . $fields['NUM_DOSSIER'] . '" AND ( FLAG_NON_DOUBLON IS NULL OR ( FLAG_NON_DOUBLON =1 AND "' . $fields['NUM_DOSSIER'] . '" < APP_NUMBER))';


    $getTableName = 'SELECT * FROM PMT_CONFIG_DEDOUBLONAGE WHERE CD_PROCESS_UID="' . $process . '"';
    $table = executeQuery($getTableName);
    if (!empty($table))
    {
        $uid_config = $table[1]['CD_UID'];
        $table = $table[1]['CD_TABLENAME'];
    }

    $getFieldsQuery = 'SELECT * FROM PMT_COLUMN_DEDOUBLONAGE WHERE CD_INCLUDE_OPTION = 1 AND CD_UID_CONFIG_AS=' . $uid_config;
    $config = executeQuery($getFieldsQuery);
    if (!empty($config) && $table != '')
    {

        foreach ($config as $data)
        {

            $where .= ' AND (' . strtoupper($data['CD_FIELDNAME']) . ' = "' . $fields[$data['CD_FIELDNAME']] . '" OR levenshtein_ratio("' . metaphone($fields[$data['CD_FIELDNAME']]) . '",dm(' . strtoupper($data['CD_FIELDNAME']) . ')) >= ' . $data['CD_RATIO'] . ' OR levenshtein_ratio("' . $fields[$data['CD_FIELDNAME']] . '",' . strtoupper($data['CD_FIELDNAME']) . ') >= ' . $data['CD_RATIO'] . ')';
        }

        $requete = 'SELECT * FROM ' . $table . ' WHERE ' . $where;
        $result = executeQuery($requete);

        return $result;
    }

    return '';
}

//GLOBAL
function convergence_getFrenchDate() {
    return date('d/m/Y');
}

//GLOBAL
function convergence_getAS400Date() {
    return G::CurDate('d.m.Y');
}

//GLOBAL
function convergence_getOutputDocument($app_id, $doc_id) {

    $aAttachFiles = array();
    $outDocQuery = 'SELECT AD.APP_DOC_UID, AD.DOC_VERSION, C.CON_VALUE AS FILENAME
    FROM APP_DOCUMENT AD, CONTENT C
    WHERE AD.APP_UID="' . $app_id . '" AND AD.DOC_UID="' . $doc_id . '" AND
    AD.APP_DOC_STATUS="ACTIVE" AND AD.DOC_VERSION = (
    SELECT MAX(DOC_VERSION) FROM APP_DOCUMENT WHERE APP_UID="' . $app_id . '" AND
    DOC_UID="' . $doc_id . '" AND APP_DOC_STATUS="ACTIVE")
    AND AD.APP_DOC_UID = C.CON_ID AND C.CON_CATEGORY = "APP_DOC_FILENAME"';
    $outDoc = executeQuery($outDocQuery);
    if (!empty($outDoc))
    {
        // Ajout suite à la modification de la gestion des documents dans processmaker
        if ( method_exists('G', 'getPathFromUID') )
        {
            $app_id = G::getPathFromUID($app_id);
        }
        $path = PATH_DOCUMENT . $app_id . PATH_SEP . 'outdocs' . PATH_SEP .
                $outDoc[1]['APP_DOC_UID'] . '_' . $outDoc[1]['DOC_VERSION'];
        $filename = $outDoc[1]['FILENAME'];
        $aAttachFiles[$filename . '.pdf'] = $path . '.pdf';
        //$aAttachFiles[$filename . '.doc'] = $path . '.doc';
    }
    return $aAttachFiles;
}

//GLOBAL
function convergence_getNameUser($userID) {

    $user = userInfo($userID);
    return $user['firstname'] . ' ' . $user['lastname'];
}

//GLOBAL
function convergence_getNamePresta($prestaID) {

    $query = 'SELECT RAISONSOCIALE FROM PMT_PRESTATAIRE WHERE SIRET=' . $prestaID;
    $result = executeQuery($query);
    if (is_array($result))
    {
        return $result[1]['RAISONSOCIALE'];
    }
    else
        return '';
}

//GLOBAL
function convergence_getCPVille($villeID) {
    if ($villeID != '')
    {
        $query = 'SELECT ZIP,NAME FROM PMT_VILLE WHERE UID=' . $villeID;
        $result = executeQuery($query);
        if (is_array($result))
        {
            return $result[1]['ZIP'] . ' ' . $result[1]['NAME'];
        }
        else
            return '';
    }
    else
        return '';
}

//GLOBAL
function convergence_annuleCheque($chequeID) {

    $query = 'UPDATE PMT_CHEQUES SET ANNULE=1 WHERE UID=' . $chequeID;
    $result = executeQuery($query);
    if (is_array($result))
    {
        return '';
    }
    else
        return '';
}

//GLOBAL
function convergence_annuleChequier($commandeID) {

    $query = 'UPDATE PMT_CHEQUES SET ANNULE=1 WHERE ID_DEMANDE=' . $commandeID;
    $result = executeQuery($query);
    if (is_array($result))
    {
        return '';
    }
    else
        return '';
}

//GLOBAL
function convergence_concatFiles($files, $where_exclude) {
    //si plusieurs fichiers on les concatenent
    if (is_array($files) && count($files) > 1)
    {

        $i = 0;
       /* $query = 'SELECT DOC_VERSION, APP_UID, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID IN (' . implode(',', $files) . ') AND APP_DOC_TYPE="OUTPUT" AND APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "fr"' . $where_exclude;
        $result = executeQuery($query);
        if(sizeof($result) == 0)
        {
            $query = 'SELECT DOC_VERSION, APP_UID, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID IN (' . implode(',', $files) . ') AND APP_DOC_TYPE="OUTPUT" AND APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "en"' . $where_exclude;
            $result = executeQuery($query);
        }*/

        foreach ($files as $f)
        { 
            $query = 'SELECT MAX(DOC_VERSION) AS DOC_VERSION, APP_UID, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID IN (' . $f . ') AND APP_DOC_TYPE="OUTPUT" AND APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "fr"' . $where_exclude;
            $result = executeQuery($query);
            if(sizeof($result) == 0)
            {
                $query = 'SELECT MAX(DOC_VERSION) AS DOC_VERSION, APP_UID, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID IN (' . $f . ') AND APP_DOC_TYPE="OUTPUT" AND APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "en"' . $where_exclude;
                $result = executeQuery($query);
            } 
            $app_uid = trim($f,"'");
            if(method_exists('G','getPathFromUID')){
                $app_uid = G::getPathFromUID(trim($f,"'"));
            }
            $path = PATH_DOCUMENT .$app_uid. PATH_SEP . 'outdocs' . PATH_SEP . $result[1]['APP_DOC_UID'] . '_' . $result[1]['DOC_VERSION'];
            $concatFile[$i++] = $path . '.pdf';
        }
    }
    //sinon on concatene tous les docs de ce dispositif
    else
    {
        $i = 0;
        /* $query = 'SELECT DOC_VERSION, APP_UID, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID IN (' . implode(',', $files) . ') AND APP_DOC_TYPE="OUTPUT" AND APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "fr"' . $where_exclude;
        $result = executeQuery($query);
        if(sizeof($result) == 0)
        {
            $query = 'SELECT DOC_VERSION, APP_UID, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID IN (' . implode(',', $files) . ') AND APP_DOC_TYPE="OUTPUT" AND APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "en"' . $where_exclude;
            $result = executeQuery($query);
        }*/
        
        foreach ($files as $f)
        { 
            $query = 'SELECT MAX(DOC_VERSION) AS DOC_VERSION, APP_UID, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID IN (' . $f . ') AND APP_DOC_TYPE="OUTPUT" AND APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "fr"' . $where_exclude;
            $result = executeQuery($query);
            if(sizeof($result) == 0)
            {
                $query = 'SELECT MAX(DOC_VERSION) AS DOC_VERSION, APP_UID, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID IN (' . $f . ') AND APP_DOC_TYPE="OUTPUT" AND APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "en"' . $where_exclude;
                $result = executeQuery($query);
            } 
            $app_uid = trim($f,"'"); 
            if(method_exists('G','getPathFromUID')){
                $app_uid = G::getPathFromUID(trim($f,"'"));
            }
            $path = PATH_DOCUMENT .$app_uid. PATH_SEP . 'outdocs' . PATH_SEP . $result[1]['APP_DOC_UID'] . '_' . $result[1]['DOC_VERSION'];
            $concatFile[$i++] = $path . '.pdf';
        }
    } 
    $resultFile = '/tmp/temp_concat_' . time() . '.pdf';
    $a = exec('gs -q -dBATCH -dNOPAUSE -dSAFER -sDEVICE=pdfwrite -sOutputFile=' . $resultFile . ' -dBATCH ' . implode(' ', $concatFile));

    $return = file_get_contents($resultFile);
    unlink($resultFile);

    return $return;
}

/* * *
 *      Récupère la liste des fichiers sur le server ftp distant
 *
 * @param       string  $remote_dir     le chemin d'acces au dossier à lister, racine du ftp par defaut.
 * @param       string  $remote_bkp     le chemin d'acces au dossier de sauvegarde où l'on déplace les fichiers après les avoir récupérés.
 * @param       string  $pattern        expression régulière pour filtrer le nom des fichiers désirés.
 * @param       string  $local_dir      le chemin d'acces du dossier sur la machine local (PM) où l'on récupère les fichiers pour les traiter.
 *
 * @constant    string  serveur_ftp     constante dans le fichier php du dispositif contenant l'host Ftp
 * @constant    string  port_ftp        constante dans le fichier php du dispositif contenant le port Ftp
 * @constant    string  username_ftp    constante dans le fichier php du dispositif contenant le login Ftp
 * @constant    string  pwd_ftp         constante dans le fichier php du dispositif contenant le password Ftp
 *
 * @return      array   $files_liste    liste des fichiers chargés avec leur chemin d'acces sur le serveur Ftp, retourne false si échec de connection
 * */
function convergence_getFileByFtp($remote_dir = '/.', $remote_bkp = '', $pattern = '', $local_dir = '/var/tmp/') {
    //INIT
    $remote_file = array();
    $files_liste = array();
    $host = serveur_ftp;
    $user = username_ftp;
    $pwd = pwd_ftp;
    $port_ftp = port_ftp;
    $protocol = protocol_transfert;
    $ssh2 = "ssh2.$protocol://$user:$pwd@$host:$port_ftp";

    $remote_file = scandir($ssh2 . $remote_dir);
    if (!empty($remote_file))
    {
        foreach ($remote_file as $file)
        {
            if (preg_match($pattern, $file) == 1)
            {
                if (copy($ssh2 . $remote_dir . $file, $local_dir . $file))
                    $files_liste[] = $local_dir . $file;
                if ( $remote_bkp != '' )
                {
                    if ( !$testRename = rename($ssh2 . $remote_dir . $file, $ssh2 . $remote_bkp . $file) )
                    {
                        unlink($ssh2 . $remote_bkp . $file);
                        rename($ssh2 . $remote_dir . $file, $ssh2 . $remote_bkp . $file);
                    }
                }
            }
        }
    }
    return $files_liste;
}

/* * *
 *      Récupère un fichier depuis le serveur Ftp distant pour l'uploader
 *
 * @param       string  $remote_file    le chemin d'acces sur le Ftp du fichier à charger.
 * @param       string  $local_dir      le chemin d'acces du dossier sur la machine local (PM) où l'on récupère les fichiers pour les traiter.
 *
 * @constant    string  serveur_ftp     constante dans le fichier php du dispositif contenant l'host Ftp
 * @constant    string  port_ftp        constante dans le fichier php du dispositif contenant le port Ftp
 * @constant    string  username_ftp    constante dans le fichier php du dispositif contenant le login Ftp
 * @constant    string  pwd_ftp         constante dans le fichier php du dispositif contenant le password Ftp
 *
 * @return      string  $files_liste    le chemin du fichier récupéré
 * */
function convergence_uploadFileByFtp($remote_file = '', $local_dir = '/var/tmp/') {
    if ($remote_file != '')
    {
        //INIT
        $remote_file = array();
        $files_liste = array();
        $host = serveur_ftp;
        $user = username_ftp;
        $pwd = pwd_ftp;
        $port_ftp = port_ftp;
        $protocol = protocol_transfert;
        $ssh2 = "ssh2.$protocol://$user:$pwd@$host:$port_ftp";

        $local_file = $local_dir . basename($remote_file);
        $bool = copy($ssh2 . $remote_file, $local_file);
        if ($bool)
            return $local_file;
    }
    return FALSE;
}

/* * *
 *      Dépose un fichier sur le server ftp distant
 *
 * @param       string  $local_file     le chemin sur la machine local (PM) du fichier à déposer.
 * @param       string  $remote_dir     le chemin d'acces au dossier où l'on souhaite déposer le fichier, racine du ftp par defaut.
 * @param       string  $remote_bkp     le chemin d'acces au dossier de sauvegarde sur le server Ftp (Facultatif).
 * @param       integer $deletLocal     si 1, supprime $local_file (Facultatif).
 *
 * @constant    string  serveur_ftp     constante dans le fichier php du dispositif contenant l'host Ftp
 * @constant    string  port_ftp        constante dans le fichier php du dispositif contenant le port Ftp
 * @constant    string  username_ftp    constante dans le fichier php du dispositif contenant le login Ftp
 * @constant    string  pwd_ftp         constante dans le fichier php du dispositif contenant le password Ftp
 *
 * @return      bool                    retourne true si réussie.
 * */
function convergence_putFileByFtp($local_file = '', $remote_dir = '/', $remote_bkp = '', $deletLocal = 0) {
    if (!empty($local_file))
    {
        //  INIT
        $host = serveur_ftp;
        $user = username_ftp;
        $pwd = pwd_ftp;
        $port_ftp = port_ftp;
        $protocol = protocol_transfert;
        $ssh2 = "ssh2.$protocol://$user:$pwd@$host:$port_ftp";
        $remote_file = $remote_dir . basename($local_file);
        $bool = copy($local_file, $ssh2 . $remote_file);
        if ($remote_bkp != '')
            copy($local_file, $ssh2 . $remote_bkp);
        ftp_close($ftp_stream);
        if ($deletLocal == 1)
            unlink($local_file);
        return $bool;
    }
    return FALSE;
}

//GLOBAL
// $useCodeOper à 0 si on ne veux pas tenir compte de $onlyThisCodeOper, des code opération en générale, du code oper dans le nom du fichier et un seul fichier pour tout le dispositif
function convergence_exportToAS400($process_id, $file_base, $code, $liste = null, $makeRetourProdTxtForRecette = 0, $onlyThisCodeOper = 0, $useCodeOper = 1) {
    if (!isset($process_id))
    {
        return 'Le process_uid n\'est pas renseigné pour l\'export de fichier';
    }
    $query_config = 'SELECT * FROM PMT_AS400_CONFIG WHERE PROCESS_UID =' . "'$process_id'";
    $res_config = executeQuery($query_config);
    if (!empty($res_config))
    {
        $config = $res_config[1];
        ($config['TOKEN_CSV'] == '') ? $token = ' ' : $token = $config['TOKEN_CSV'];
    }
    else
    {
        return 'Aucun process uid correcpondant dans la table AS400 Config !';
    }
    $query_fields = 'SELECT * FROM PMT_COLUMN_AS400 WHERE ID_CONFIG_AS = ' . $config['ID'] . ' ORDER BY ORDER_FIELD';
    $select = executeQuery($query_fields);
    // a confirmer avec fred
    if (!is_null($liste))
    {
        // Modifier par Nico, voir avec fred si pas d'effet de bord dans ses process
        //$config['CONFIG_WHERE'] = ' AND NUM_DOSSIER IN ('.$liste.')';
        $value = "'" . implode("','", $liste) . "'";
        $config['CONFIG_WHERE'] = ' AND APP_UID IN(' . $value . ')';
    }
    /*     * *****  récupération des different code opération pour le dispositif et génération d'un fichier de production par code ** */
    $whereOper = ' WHERE 1';
    // Si l'on ne souhaite lancer la production que sur un seul code opération
    if (intval($onlyThisCodeOper) != 0 && $useCodeOper == 1)
    {
        $whereOper .= ' AND NUM_OPER = ' . intval($onlyThisCodeOper);
    }
    $sqlOper = 'SELECT DISTINCT(NUM_OPER) FROM PMT_LISTE_OPER' . $whereOper;
    $resOper = executeQuery($sqlOper);
    $listOper = array();
    if (!empty($resOper))
    {
        foreach ($resOper as $operation)
        {
            $listOper[] = intval($operation['NUM_OPER']);
        }
    }
    else
    {
        G::pr('Aucun dispositif défini dans la table PMT_LISTE_OPER');
        die;
    }
    //G::pr($resOper);die;
    /*     * ***** */
    $app_uid = array();
    $nb_result = 0;
    foreach ($listOper as $codeOper)
    {
        if ((count($listOper) > 1 || $onlyThisCodeOper != 0) && $useCodeOper == 1)
            $whereCodeOper = ' AND CODE_OPERATION =' . $codeOper;
        else
            $whereCodeOper = '';
        $dateFile = date("YmdHis");
        if ($useCodeOper == 1)
            $file = $file_base . $codeOper . '.txt';
        else
            $file = $file_base;

        $query = 'SELECT * FROM ' . $config['TABLENAME'] . ' ' . $config['JOIN_CONFIG'] . ' WHERE 1 ' . $config['CONFIG_WHERE'] . $whereCodeOper;
        $result = executeQuery($query);
        $mode = 'w+'; // enregistrer le fichier sous un format _date et le sauvegarder dans une table historique

        $data = array();
        if (!empty($result))
        {
            $ftic = fopen($file, $mode);
            foreach ($result as $row)
            {
                $line = '';
                $error = false;
                foreach ($select as $field)
                {
                    if (!empty($field['CONSTANT']))
                    {// contient 0 par defaut
                        $oldFieldName = $row[$field['FIELD_NAME']];
                        $row[$field['FIELD_NAME']] = $field['CONSTANT'];
                    }
                    if ($field['REQUIRED'] == 'no' || ($field['REQUIRED'] == 'yes' && isset($row[$field['FIELD_NAME']]) && $row[$field['FIELD_NAME']] != ''))
                    {
                        switch ($field['AS400_TYPE'])
                        {
                            case 'Integer':
                            case 'Entier':
                                $line .= substr(str_pad($row[$field['FIELD_NAME']], $field['LENGTH'], 0, STR_PAD_LEFT), 0, $field['LENGTH']);
                                break;
                            case 'Ymd':
                            case 'ymd':
                            case 'y-m-d':
                            case 'Y-m-d':
                            case 'y.m.d':
                            case 'Y.m.d':
                            case 'dmY':
                            case 'd.m.y':
                            case 'd-m-y':
                            case 'dmy':
                            case 'd.m.Y':
                            case 'd-m-Y':
                                $dateIn = date_create($row[$field['FIELD_NAME']]);
                                $dateOut = date_format($dateIn, $field['AS400_TYPE']);
                                $line .= substr(str_pad($dateOut, $field['LENGTH'], $token), 0, $field['LENGTH']);
                                break;
                            case 'Decimal'://0000000.00                                
                                $value = str_replace(' ', '', $row[$field['FIELD_NAME']]);
                                $value = str_replace(',', '.', $value);
                                $count = count(explode('.', $value));
                                ($count > 1) ? $dec = $value : $dec = $value . '00';
                                $line .= substr(str_pad(str_replace('.', '', $dec), $field['LENGTH'], 0, STR_PAD_LEFT), 0, $field['LENGTH']);
                                break;
                            case 'mail':
                                $string = removeAllAccents($row[$field['FIELD_NAME']], '/[^a-zA-Z0-9.@]+/i', '');
                                $line .= substr(str_pad_unicode($string, $field['LENGTH'], $token), 0, $field['LENGTH']);
                                break;
                            case 'Telephone':
                                $char = array('-', '.', ' ');
                                $line .= substr(str_pad(str_replace($char, '', $row[$field['FIELD_NAME']]), $field['LENGTH'], $token), 0, $field['LENGTH']);
                                break;
                            case 'Yesno':
                                $yes = array('oui', 'yes', 'o', '0', '1', 'Oui', 'Yes', 'YES', 'OUI', 1);
                                (in_array(strtolower($row[$field['FIELD_NAME']], $yes))) ? $yesno = 'O' : $yesno = 'N';
                                $line .= $yesno;
                                break;
                            case 'OuiNon':
                                $yes = array('oui', 'yes', 'o', '0', '1', 'Oui', 'Yes', 'YES', 'OUI', 1);
                                (in_array(strtolower($row[$field['FIELD_NAME']], $yes))) ? $yesno = 'oui' : $yesno = 'non';
                                $line .= $yesno;
                                break;
                            case 'binaire':
                                $zero = array('oui', 'yes', 'o', '0', '1', 'Oui', 'Yes', 'YES', 'OUI', 1);
                                (in_array(strtolower($row[$field['FIELD_NAME']], $zero))) ? $bin = '1' : $bin = '0';
                                $line .= $bin;
                                break;
                            case 'AI':
                                $aiArray = array('oui', 'yes', 'o', '0', '1', 'Oui', 'Yes', 'YES', 'OUI', 'Actif', 'ACTIF', 'actif', 'A', 'a', 1);
                                (in_array(strtolower($row[$field['FIELD_NAME']], $aiArray))) ? $ai = 'A' : $ai = 'I';
                                $line .= $ai;
                                break;
                            case 'NCommande':
                                // numéro de commande à récupérer en amont et passer en paramètre
                                $line .= substr(str_pad($code, $field['LENGTH'], 0, STR_PAD_LEFT), 0, $field['LENGTH']);
                                break;
                            case 'codeOper':
                                // code opération
                                $line .= substr(str_pad($codeOper, $field['LENGTH'], 0, STR_PAD_LEFT), 0, $field['LENGTH']);
                                break;
                            // ajouter les autres cas possible, par defaut les champs sont comblés avec des espaces à droite
                            case 'Ignore':
                                $line .= substr(str_pad('', $field['LENGTH'], $token), 0, $field['LENGTH']);
                                break;
                            case 'strSecure':
                                $string = removeAllAccents($row[$field['FIELD_NAME']]);
                                $line .= substr(str_pad($string, $field['LENGTH'], $token), 0, $field['LENGTH']);
                                break;
                            case 'strSecureL':
                                $string = strtolower(removeAllAccents($row[$field['FIELD_NAME']]));
                                $line .= substr(str_pad($string, $field['LENGTH'], $token), 0, $field['LENGTH']);
                                break;
                            case 'strSecureU':
                                $string = strtoupper(removeAllAccents($row[$field['FIELD_NAME']]));
                                $line .= substr(str_pad($string, $field['LENGTH'], $token), 0, $field['LENGTH']);
                                break;
                            default:                               
                                $line .= mb_substr(str_pad_unicode($row[$field['FIELD_NAME']], $field['LENGTH'], $token), 0, $field['LENGTH']);
                                break;
                        }
                    }
                    else
                    {
                        $error = true;
                    }
                    if (!empty($field['CONSTANT'])) // dans le cas ou on utilise un champs pour sa valeur ET une constante
                    {// contient 0 par defaut
                        $row[$field['FIELD_NAME']] = $oldFieldName;
                    }
                }
                if (!$error)
                {
                    fwrite($ftic, $line . "\n"); // voir si l'as400 supporte la dernière ligne vide
                    $nb_result++;
                    if (isset($row['APP_UID']))
                    {
                        $app_uid[] = $row['APP_UID'];
                    }
                }
            }
            fclose($ftic);
        }
        if ($useCodeOper == 0)
            break;
    }
    /*     * *** autogenerate a sample file of return production from as400 for test on debug mode***** */
    if ($makeRetourProdTxtForRecette == 1)
    {
        $nameFile = '/var/tmp/autogenerateForRetourProd_' . $code . '.txt';
        $fret = fopen($nameFile, $mode);
        $in = "'" . implode("','", $app_uid) . "'";
        $q = 'SELECT CODE_OPERATION, CODE_CHEQUIER, NUM_DOSSIER FROM PMT_DEMANDES WHERE APP_UID IN(' . $in . ')';
        $r = executeQuery($q);
        $q1 = 'SELECT MAX(BCONSTANTE)+1 as TITRE FROM PMT_CHEQUES WHERE 1';
        $r1 = executeQuery($q1);
        $numChq = $r1[1]['TITRE'];
        foreach ($r as $k => $v)
        {
            // $chqSql = 'SELECT SUM(NB) AS S FROM PMT_CHEQUIER_MM_VN WHERE LOCAL_ID='.$v['CODE_CHEQUIER'];
            $chqSql = 'SELECT *, PMT_LISTE_TYPES.LABEL as LBTYPE FROM PMT_CHEQUIER_MM_VN LEFT JOIN PMT_LISTE_VN ON(PMT_CHEQUIER_MM_VN.FOREIGN_ID = PMT_LISTE_VN.CODE) LEFT JOIN PMT_LISTE_TYPES on(PMT_LISTE_VN.TYPE_TITRE = PMT_LISTE_TYPES.CODE) WHERE LOCAL_ID=' . $v['CODE_CHEQUIER'];
            $chqRes = executeQuery($chqSql);
            $numChqier = $numChq = str_pad($numChq, 9, 0, STR_PAD_LEFT);

            foreach ($chqRes as $cheque)
            {
                $nbInsert = 0;
                while ($nbInsert < $cheque['NB'])
                {

                    $type = str_pad($cheque['LBTYPE'], 3, 0, STR_PAD_LEFT);

                    $char = array('.', ',');
                    $count = count(explode('.', $cheque['VALEUR_NOMINALE']));
                    ($count > 1) ? $dec = $cheque['VALEUR_NOMINALE'] : $dec = $cheque['VALEUR_NOMINALE'] . '00';
                    $vn = substr(str_pad(str_replace($char, '', $dec), 7, 0, STR_PAD_LEFT), 0, 7);

                    $numChq = str_pad($numChq, 9, 0, STR_PAD_LEFT);
                    $codeoper = str_pad($v['CODE_OPERATION'], 5, 0, STR_PAD_LEFT);
                    $numD = str_pad($v['NUM_DOSSIER'], 12, 0, STR_PAD_LEFT);
                    $numP = str_pad($code, 12, 0, STR_PAD_LEFT);
                    $line = $codeoper . $numD . $numP . $numChqier . $numChq . $type . $vn . '04.09.201204.12.2014100';
                    fwrite($fret, $line . "\n");
                    $numChq++;
                    $nbInsert++;
                }
            }
        }
        fclose($fret);
    }
    /*     * *********************** end debug mode function ********************************* */
    if (!empty($app_uid))
    {
        return $app_uid;
    }
    else
    {
        return $nb_result;
    }
}

/* * *
 *      Nettoye une chaine de caractère des accents, ligatures, ponctuations et autres
 *
 * @param       string  $str                la chaine de caractères à nettoyer.
 * @param       string  $keepOther          0 pour ne conserver que les chiffres et les lettres, 1 garde la ponctuation et autre.
 * @param       string  $keepPattern        si $keepOther à 1 on peut personaliser le pattern pour certain caractère comme . et @ pour les mails.
 * @param       integer $replacePattern     ce par quoi on souhaite remplacer ce qui ne correspond pas à $keepPattern.
 * @param       integer $encoding           le type d'encodage.
 *
 * @return      string  $str            retourne la chaine de caractère nettoyée.
 * */
function removeAllAccents($str, $keepOther = 0, $keepPattern = '/[^a-zA-Z0-9]+/i', $replacePattern = ' ', $encoding = 'utf-8') {

    // transformer les caractères accentués en entités HTML
    $str = htmlentities($str, ENT_NOQUOTES, $encoding);
    // remplacer les entités HTML pour avoir juste le premier caractères non accentués
    // Exemple : "&ecute;" => "e", "&Ecute;" => "E", "Ã " => "a" ...
    $str = preg_replace('#&([A-Za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    // Remplacer les ligatures tel que : Œ, Æ ...
    // Exemple "Å“" => "oe"
    $str = preg_replace('#&([A-Za-z]{2})(?:lig);#', '\1', $str);
    // Supprimer tout le reste
    $str = preg_replace('#&[^;]+;#', '', $str);
    // Conserve les lettres et chiffre uniquement
    if ( $keepOther )
        $str = preg_replace($keepPattern, $replacePattern, $str);
    return $str;
}

function str_pad_unicode($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT) {
    mb_internal_encoding('utf-8');
    $str_len = mb_strlen($str);
    $pad_str_len = mb_strlen($pad_str);
    if ( !$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT) )
    {
        $str_len = 1;
    }
    if ( !$pad_len || !$pad_str_len || $pad_len <= $str_len )
    {
        return $str;
    }

    $result = null;
    $repeat = ceil($str_len - $pad_str_len + $pad_len);
    if ( $dir == STR_PAD_RIGHT )
    {
        $result = $str . str_repeat($pad_str, $repeat);
        $result = mb_substr($result, 0, $pad_len);
    }
    else if ( $dir == STR_PAD_LEFT )
    {
        $result = str_repeat($pad_str, $repeat) . $str;
        $result = mb_substr($result, -$pad_len);
    }
    else if ( $dir == STR_PAD_BOTH )
    {
        $length = ($pad_len - $str_len) / 2;
        $repeat = ceil($length / $pad_str_len);
        $result = mb_substr(str_repeat($pad_str, $repeat), 0, floor($length))
                . $str
                . mb_substr(str_repeat($pad_str, $repeat), 0, ceil($length));
    }
    return $result;
}

/* * **** //GLOBAL
 *  Met le statut En cours de production sur les demandes pour les export de production de chèques
 *
 *
 */
function convergence_updateAllStatutDemandes($app_uid, $statutTo) {
    if (is_array($app_uid))
    {
        foreach ($app_uid as $uid)
        {
            convergence_changeStatut($uid, $statutTo);
        }
    }
}

/* * **** //GLOBAL
 * Supprime le falg à reproduire après le lancement en production de la demande
 *
 *
 */
function convergence_updateAllReproductionDemandes($app_uid, $flagTo) {
    if (is_array($app_uid))
    {
        $data['REPRODUCTION_CHQ'] = $flagTo;
        foreach ($app_uid as $uid)
        {
            $qRepro = 'SELECT NUM_DOSSIER FROM PMT_DEMANDES WHERE APP_UID ="' . $uid . '" AND REPRODUCTION_CHQ ="O" AND STATUT <> "0"';
            $rRepro = executeQuery($qRepro);
            // si c'est une demande de reproduction
            if (!empty($rRepro))
            {
                //on annule tout les chèques des productions précédente pour cette demande et on incrémente le nombre de reproduction.
                $qUpdateCheque = 'UPDATE PMT_CHEQUES SET REPRODUCTION = IF(REPRODUCTION IS NULL, 1, REPRODUCTION + 1), ANNULE = 1 WHERE NUM_DOSSIER =' . $rRepro[1]['NUM_DOSSIER'];
                $rUpdateCheque = executeQuery($qUpdateCheque);
                //on met à jour le flag de reproduction de la demande
                convergence_updateDemande($uid, $data);
            }
        }
    }
}

/* * **** //GLOBAL
 *  Met le statut En cours de remboursement sur les demandes pour les export de remboursement
 *
 *
 */
function convergence_updateAllStatutRemboursement($app_uid, $statutTo) {
    if (is_array($app_uid))
    {
        foreach ($app_uid as $uid)
        {
            convergence_changeStatut($uid, $statutTo);
        }
    }
}

function convergence_getDossiers($res, $table, $export = true) {
    if ($export == true)
    {
        $in = "'" . implode("','", $res) . "'";
        $query = 'SELECT NUM_DOSSIER FROM ' . $table . ' WHERE APP_UID IN(' . $in . ')';
        $res = executeQuery($query);
    }
    $array = array();
    foreach ($res as $value)
    {
        $array[] = $value['NUM_DOSSIER'];
    }
    $unique = array_unique($array);
    $liste = implode(',', $unique);
    return $liste;
}

// n'est plus utilisé, mais après modification, peut servir pour les réédition, même num_titre et bconstante
function convergence_checkReproduction($line_import) {
    $repro = 0;
    // G::pr('-------line_import in the function --------');G::pr($line_import);
    if (!empty($line_import))
    {
        try
        {
            $query = 'SELECT UID, REPRODUCTION FROM PMT_CHEQUES WHERE NUM_TITRE ="' . $line_import['NUM_TITRE'] . '" AND (ANNULE <> 1 OR ANNULE IS NULL)';
            //G::pr('------debut pm-------');G::pr($line_import);
            //G::pr($query);//G::pr('------fin pm-------');
            $result = executeQuery($query);
            //G::pr($result);
            if (!empty($result))
            {
                if (is_null($result[1]['REPRODUCTION']))
                    $result[1]['REPRODUCTION'] = 0;
                $repro = $result[1]['REPRODUCTION'] + 1;
                //$update = 'UPDATE PMT_CHEQUES SET ANNULE = 1 WHERE UID = '.$result[1]['UID'];
                //$resUpdate = executeQuery($update);
                return $repro;
            }
        }
        catch (Exception $e)
        {
            var_dump($e);
            die();
        }
    }
    return 0;
}

/*      Récupère toutes les données de chaque fichier importé depuis le serveur Ftp pour
 * les fusionner en un seul afin d'être traité par la function importFromAS400 facilement
 *
 * @param   array   $list_file  liste des fichiers sur le serveur en local
 *
 * @return  string  $file       chemin du fichier regroupé à traiter
 */
function convergence_concatImportFile($list_file, $app_uid) {
    if (!empty($list_file))
    {
        $new_file = dirname($list_file[0]) . '/import_' . $app_uid;
        $globalContent = '';
        foreach ($list_file as $file)
        {
            $globalContent .= file_get_contents($file);
            unlink($file);
        }
        $handle = fopen($new_file, 'w+');
        $w = fwrite($handle, $globalContent);
        fclose($handle);
        return $new_file;
    }
    else
        return FALSE;
}
//GLOBAL
/* * ***
 * Lecture d'un fichier plat provenant de l'AS400
 *
 * $process_uid     @string    uid du process courant
 * $app_id          @array     app_uid du cas courant
 * $childProc       @array     0 on ne lance pas le process de traitement des données
 */
function convergence_importFromAS400($process_uid, $app_id = '', $childProc = 0, $filePath = '') {
    if ($app_id != '')
    {
        if ($filePath == '')
        {
            try
            {
                $query = 'SELECT C.CON_ID, C.CON_VALUE, AD.DOC_VERSION FROM APP_DOCUMENT AD, CONTENT C
                WHERE AD.APP_UID="' . $app_id . '" AND AD.APP_DOC_TYPE="INPUT" AND AD.APP_DOC_STATUS="ACTIVE"
               AND AD.APP_DOC_UID=C.CON_ID AND C.CON_CATEGORY="APP_DOC_FILENAME" AND C.CON_VALUE<>""';
                $result = executeQuery($query);
                if (!empty($result))
                {
                    $filePath = PATH_DOCUMENT . $app_id . '/' . $result[1]['CON_ID'] . '_' . $result[1]['DOC_VERSION'] . '.' . pathinfo($result[1]['CON_VALUE'], PATHINFO_EXTENSION);
                }
            }
            catch (Exception $e)
            {
                G::pr('Erreur lors de la récupération du document');
                G::pr($e);
                die();
            }
        }
        if (!isset($process_uid))
        {
            G::pr('Le process_uid n\'est pas renseigné pour configurer l\'import du fichier');
            die;
        }
        $query_config = 'SELECT * FROM PMT_AS400_CONFIG WHERE PROCESS_UID ="' . $process_uid . '"';

        $res_config = executeQuery($query_config);
        if (!empty($res_config))
        {
            $config = $res_config[1];
            ($config['TOKEN_CSV'] == '') ? $token = ' ' : $token = $config['TOKEN_CSV'];
            if ($config['TASK_UID'] == '' || is_null($config['TASK_UID']))
            {
                G::pr('Le uid_process n\'est pas renseigné pour traiter les données importées');
                die();
            }
        }
        else
        {
            G::pr('Aucun process uid correspondant dans la table AS400 Config !');
            die;
        }
        $query_fields = 'SELECT * FROM PMT_COLUMN_AS400 WHERE ID_CONFIG_AS = ' . $config['ID'] . ' ORDER BY ORDER_FIELD';

        $select = executeQuery($query_fields);
        $mode = 'r';
        $file = basename($filePath);
        $ftic = fopen($filePath, $mode);
        $data = array();
        $logField = array();
        // génération du fichier de log
        $Log = 'Le fichier ' . $file . ' a été intégré le ' . date("d/m/Y à H:i:s") . ".\r\n\n\n";
        $nbcurrentLine = $nbAnomalie = $nbCreate = 0;
        while (($current_line = fgets($ftic)) !== false)
        { //pour chaque ligne
            $importLine = array(); // import d'une ligne, champs par champs
            $checkLog = array(); // log pour un champ
            $logLine = array(); // log pour une ligne
            $nbcurrentLine++; // N° de la ligne courante
            foreach ($select as $field)
            { // pour chaque champs configuré
                if (!empty($field['CONSTANT']))
                {
                    $importLine[$field['FIELD_NAME']] = $field['CONSTANT'];
                }
                else
                {
                    switch ($field['AS400_TYPE'])
                    { // traitement de la valeur
                        case 'Integer':
                        case 'Entier':
                            $importLine[$field['FIELD_NAME']] = intval(trim(substr($current_line, 0, $field['LENGTH']), $token));
                            break;
                        case 'Ignore':
                            //$forget = trim(substr($current_line, 0, $field['LENGTH']), $token);
                            break;
                        case 'Decimal':
                            $importLine[$field['FIELD_NAME']] = floatval(substr_replace(substr($current_line, 0, $field['LENGTH']), '.', -2, 0));
                            break;
                        case 'Telephone':
                            $stringTel = trim(substr($current_line, 0, $field['LENGTH']), $token);
                            $importLine[$field['FIELD_NAME']] = wordwrap($stringTel, 2, "-", 1);
                            break;
                        case 'Date':
                            $importLine[$field['FIELD_NAME']] = str_replace('.', '-', trim(substr($current_line, 0, $field['LENGTH']), $token));
                            break;
                        default:
                            $importLine[$field['FIELD_NAME']] = trim(substr($current_line, 0, $field['LENGTH']), $token);
                            break;
                    }
                    $current_line = substr($current_line, $field['LENGTH']);
                }
                //on vérifie la valeur obtenue en fonction de la configuration. 'txt' pour un fichier plat type AS400
                $checkLog = convergence_checkFieldLog($importLine[$field['FIELD_NAME']], $field, 'txt');
                if ($checkLog != 1)
                {
                    $logLine[] = implode(",\r\n\t", $checkLog); // chaque erreur sur un champs / valeur est consigné
                }
            } // fin des champs d'une ligne
            if (!empty($logLine)) // si au moins un champs comporte une erreur
            {
                $logField[$nbcurrentLine] = implode(",\r\n\t", $logLine);   // on consigne toutes les erreurs pour la ligne courante
                $nbAnomalie++; // on ajoute une anomalie
            }
            else // si aucune erreurs
            {
                $data[] = $importLine; //on récupère les données, elles peuvent être importées
                $nbCreate++;

                //lancer le process ici $start_process_uid
                // /!\ lancer un ou plusieur process enfant depuis un process parent doit toujours ce faire en dernier dans le workflow du process parent
                // $importLine contien un array assoc FIELD => VALUE
                //$data contien tous les importLine
                if ($childProc == 1)
                {
                    $importLine['APPUID_RELATION'] = $app_id; // use it if you need relation fields from the parent process in the child process
                    new_case_for_import($importLine, $config);
                }
            }
        } // fin du fichier d'import
        if ($childProc != 1) // pour ne pas écrire 2 fois le fichier des logs
        {
            $path = '/var/tmp/' . $file . '_' . date("YmdHis") . '_log.txt'; // log toujours ecrit en local, à déplacer selon les besoins dans le process
            if (!empty($logField))
            {
                $Log .= "Liste des erreurs survenues lors de l’intégration :\r\n";
                foreach ($logField as $k => $v)
                {
                    $Log .= "Ligne N° $k : $v.\r\n";
                }
            }
            else
            {
                $Log .= "Aucune erreur survenue lors de l'intégration\r\n";
            }
            $Log .= "\nNombre création         :   $nbCreate\r\nNombre d'anomalie      :   $nbAnomalie\r\nTotal de lignes        :   $nbcurrentLine\r\n";
            $handle = fopen($path, 'x+');
            fwrite($handle, $Log);
            fclose($handle);
        }
        return $data;
    }
    else
    {
        return;
        ////autre méthode
    }
}

/* * ***
 * Teste la validitée des champs importés
 *
 * $value   @string     variable contenant la valeur à traiter
 * $params  @array      tableau contenant les paramètres de conformité pour $value
 * $type    @string     type d'import fichier plat as400 ou csv
 */
//GLOBAL
function convergence_checkFieldLog($value, $params, $type) {
    $log = array();

    if (!empty($params['FIELD_DESCRIPTION']))
    {
        $field = $params['FIELD_DESCRIPTION'];
    }
    else
    {
        $field = $params['FIELD_NAME'];
    }
    if (!empty($params['CONSTANT']))
        $value = $params['CONSTANT'];
    $length = $params['LENGTH'];
    $lengthValue = strlen($value);
    if (isset($value) && $value != '' && $value != ' ')
    {
        switch ($params['AS400_TYPE'])
        {
            case 'Integer':
            case 'Entier':
                $val = $value + 0;
                $val = "$val";
                if ($value != $val)
                    $log[] = "la valeur '$value' du champs '$field' n'est pas de type 'Entier'";
                break;
            case 'Ignore':
                break;
            case 'Decimal':
                $val = $value + 0.0;
                $val = "$val";
                if ($value != $val)
                    $log[] = "la valeur '$value' du champs '$field' n'est pas de type 'Décimal'";
                break;
            case 'Telephone': //  ^0[0-9]([-. ]?[0-9]{2}){4}$ ou ^[0-9]([-. ]?[0-9]{2}){4}$
                if (preg_match('#^0?[0-9]([-. ]?[0-9]{2}){4}$#', $value) != 1 && $value != '0')
                {
                    $log[] = "la valeur '$value' du champs '$field' ne correspond pas au format 'Téléphone' attendu";
                }
                break;
            case 'mail': //^[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$
                if (preg_match('#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-zA-Z]{2,}$#', $value) != 1)
                {
                    $log[] = "la valeur '$value' du champs '$field' ne correspond pas au format 'E-mail' attendu";
                }
                break;
            /*case 'Date':  // ^(0[1-9]|1\d|2\d|3[0-1])[\/\.-]?(0[1-9]|1[0-2])[\/\.-]?(\d{4})$
                if (preg_match('#^(0[1-9]|1\d|2\d|3[0-1])[\/\.-]?(0[1-9]|1[0-2])[\/\.-]?(\d{4})$#', $value, $match) == 1)
                {
                    if (!checkdate($match[2], $match[1], $match[3]))
                    {
                        $log[] = "la valeur '$value' du champ date '$field' n'existe pas dans le calendrier";
                    }
                }
                else
                {
                    $log[] = "le format date du champ '$field' est invalide";
                }
                break;
            case 'Yesno':
                if (strtoupper($value) != 'O' && strtoupper($value) != 'N')
                    $log[] = "la valeur '$value' du champs '$field' ne correspond pas au format 'O / N'";
                break;
            case 'OuiNon':
                if (strtolower($value) != 'oui' && strtolower($value) != 'non')
                    $log[] = "la valeur '$value' du champs '$field' ne correspond pas au format 'oui / non'";
                break;
            case 'binaire':
                if ($value != 1 && $value != 0)
                    $log[] = "la valeur '$value' du champs '$field' ne correspond pas au format '1 / 0'";
                break;
            case 'AI':
                if ($value != 'A' && $value != 'I')
                    $log[] = "la valeur '$value' du champs '$field' ne correspond pas au format Actif/Inactif 'A / I'";
                break;*/
            case 'NCommande':
                $val = $value + 0;
                $val = "$val";
                if ($value != $val)
                    $log[] = "la valeur '$value' du champs '$field' ne correspond pas au format 'Numéro de commande'";
                break;
            case 'cp': // #^[0-9]{5}$#
                if (preg_match('#^[0-9]{5}$#', $value) != 1)
                {
                    $log[] = "La valeur '$value' du champs '$field' ne correspond pas au type 'Code postal'";
                }
                break;
            case 'codeOper': // modifier
                $val = $value + 0;
                $val = "$val";
                if ($value != $val)
                    $log[] = "la valeur '$value' du champs '$field' ne correspond pas au format du Code opération";
                break;
            default: // chaine de caractères
                if (!is_string($value))
                    $log[] = "la valeur '$value' du champs '$field' n'est pas de type 'Chaine de caractère'";
                break;
        }
        if ($length != 0 && $length != $lengthValue && $type == 'csv')
        {
            $log[] = "la taille de la valeur '$value' du champ '$field' ne correspond pas à celle attendue ($length)";
        }
    }
    elseif ($params['REQUIRED'] == 'yes')
    {
        $log[] = "aucune valeur renseignée pour le champ requis '$field'";
    }
    if (!empty($log))
    {
        return $log;
    }
    else
    {
        return 1;
    }
}

/* * ***
 * Execution d'un nouveau process suite à un import de l'AS400
 *
 * $line    @array     tableau contenant les champs => valeur à traiter
 * $config  @array     tableau contenant le nom de la table
 */
//GLOBAL
function new_case_for_import($line, $config) {

    G::LoadClass("case");
// Execute events
    //require_once 'classes/model/Event.php';

    $caseInstance = new Cases ();
    //$eventInstance = new Event();
    $data = $caseInstance->startCase($config['TASK_UID'], $_SESSION['USER_LOGGED']);
    $_SESSION['APPLICATION'] = $data['APPLICATION'];
    $_SESSION['INDEX'] = $data['INDEX'];
    $_SESSION['PROCESS'] = $data['PROCESS'];
    $_SESSION['STEP_POSITION'] = 0;

    $newFields = $caseInstance->loadCase($data['APPLICATION']);
    /* $newFields['APP_DATA']['FLAG_ACTION'] = 'actionCreateCase';
      $newFields['APP_DATA']['FLAGTYPO3'] = 'Off'; */
    $newFields['APP_DATA']['APPUID_RELATION'] = $line['APPUID_RELATION'];
    unset($line['APPUID_RELATION']);
    $newFields['APP_DATA']['LINE_IMPORT'] = $line;
    $newFields['APP_DATA']['CONFIG_IMPORT'] = $config;

    PMFSendVariables($data['APPLICATION'], $newFields['APP_DATA']);
    $caseInstance->updateCase($data['APPLICATION'], $newFields);
    $resInfo = PMFDerivateCase($data['APPLICATION'], 1, true, $_SESSION['USER_LOGGED']);

    //$eventInstance->createAppEvents($_SESSION['PROCESS'], $_SESSION['APPLICATION'], $_SESSION['INDEX'], $_SESSION['TASK']);
// Redirect to cases steps
//$nextStep = $caseInstance->getNextStep($_SESSION['PROCESS'], $_SESSION['APPLICATION'], $_SESSION['INDEX'], $_SESSION['STEP_POSITION']);
//G::header('Location: ../../cases/' . $nextStep['PAGE']);
//G::header('Location: ../../cases/open?APP_UID=' . $_SESSION['APPLICATION'].'&DEL_INDEX='.$_SESSION['INDEX']);
}

/* * *
 * Mise a jour de DATE_PROD dans PMT_LISTE_PROD et des dossiers produits
 *
 */
//GLOBAL
function convergence_updateDateProd($num_prod, $update) {
    try
    {
        $query = 'SELECT APP_UID FROM PMT_LISTE_PROD WHERE NUM_DOSSIER =' . intval($num_prod);
        $result = executeQuery($query);
        convergence_updateDemande($result[1]['APP_UID'], $update);
    }
    catch (Exception $e)
    {
        var_dump($e);
        die();
    }
}

//GLOBAL Inutilisé, voir pour la supprimer
function convergence_updateListeProd($app_id, $res) {
    $field = convergence_getAllAppData($app_id);
    try
    {// à changer et recup de la table dispositif  CODE_OPER et CODE_CHEQUIER dans demandes
        $queryCode = 'SELECT CODE_OPERATION FROM PMT_DEMANDES WHERE APP_UID = \'' . $res[0] . '\'';
        $resCode = executeQuery($queryCode);
        $code = $resCode[1]['CODE_OPERATION'];
        /* $query = 'SELECT APP_NUMBER FROM PMT_LISTE_PROD WHERE APP_UID =\''.$field['APPLICATION'].'\'';
          $result = executeQuery($query);
          $data['NUM_DOSSIER'] = $result[1]['APP_NUMBER']; */
        $data['CODE_OPER'] = $code;
        $data['NB_DOSSIERS'] = count($res); //doublon
        convergence_updateDemande($field['APPLICATION'], $data);
    }
    catch (Exception $e)
    {
        var_dump($e);
        die();
    }
}

//GLOBAL
function convergence_getNumDossier($app_id) {
    try
    {
        $query = 'SELECT APP_NUMBER FROM APPLICATION WHERE APP_UID = \'' . $app_id . '\'';
        $result = executeQuery($query);
        return $result[1]['APP_NUMBER'];
    }
    catch (Exception $e)
    {
        var_dump($e);
        die();
    }
}

function convergence_getPathDispositif() {
    try
    {
        $query = 'SELECT PATH_FILE FROM PMT_LISTE_OPER';
        $result = executeQuery($query);
        return $result[1]['PATH_FILE'];
    }
    catch (Exception $e)
    {
        var_dump($e);
        G::pr("Le répertoire des fichiers ou ftp n'est pas renseigné dans la table PMT_LISTE_OPER");
        die();
    }
}

function convergence_setVnForRmh() {
    $ret = 0;
    $sql = 'DELETE FROM PMT_VN_FOR_RMH';
    $res = executeQuery($sql);
    $sql = 'select SUM(VN_TITRE) as total, CODE_PRESTA as code, CONCAT(NOM_CONTACT," ", PRENOM_CONTACT) as nom, count(CODE_PRESTA) as nbTitre FROM PMT_CHEQUES, PMT_PRESTATAIRE where ETAT_TITRE < 300 and PMT_CHEQUES.STATUT = "15" and CODE_PRESTA = CONVENTION group by CODE_PRESTA';
    $res = executeQuery($sql);
    if (!empty($res))
    {
        $insert = array();
        foreach ($res as $row)
        {
            $insert[] = '("' . $row['code'] . '","' . $row['total'] . '","' . $row['nom'] . '")';
            $ret += $row['nbTitre'];
        }
        $value = implode(',', $insert);
        $qInsert = 'INSERT INTO PMT_VN_FOR_RMH (CONV_PRESTA,VN_TOTAL,NOM) VALUES ' . $value;
        $result = executeQuery($qInsert);
    }
    return $ret;
}

function convergence_unsetVnForRmh() {
    $sql = 'DELETE FROM PMT_VN_FOR_RMH';
    $res = executeQuery($sql);
    $sqlUpdate = 'update PMT_CHEQUES set STATUT="10" where ETAT_TITRE < 300 and STATUT = "15"';
    $resU = executeQuery($sqlUpdate);
}

//GLOBAL
function convergence_updateListeRemboursement($app_id, $res) {
    $field = convergence_getAllAppData($app_id);
    try
    {
        $query = 'SELECT APP_NUMBER FROM PMT_LISTE_RMBT WHERE APP_UID =\'' . $field['APPLICATION'] . '\'';
        $result = executeQuery($query);
        $queryCode = 'SELECT CODE_OPERATION FROM PMT_REMBOURSEMENT WHERE APP_UID = \'' . $res[0] . '\'';
        $resCode = executeQuery($queryCode);
        $data['CODE_OPER'] = $resCode[1]['CODE_OPERATION'];
        $data['NB_DOSSIERS'] = count($res); //doublon
        convergence_updateDemande($field['APPLICATION'], $data);
    }
    catch (Exception $e)
    {
        var_dump($e);
        die();
    }
}

//convergence_InsertLineImport(@@LINE_IMPORT,@@CONFIG_IMPORT);
//GLOBAL
function convergence_InsertLineImport($line, $config) {

    // INIT
    $finalTab = array();

    // Escape scpeial caracters
    foreach ($line as $key => $lineItem)
        $finalTab[$key] = mysql_escape_string($lineItem);

    $key = implode(',', array_keys($finalTab));
    $value = '"' . implode('","', $finalTab) . '"';
    try
    {

        $query = 'INSERT INTO ' . $config['TABLENAME'] . '(' . $key . ') VALUES (' . $value . ')';
        $result = executeQuery($query);
    }
    catch (Exception $e)
    {
        G::pr('Erreur : impossible d\'exécuter la requête suivante : "INSERT INTO ' . $config['TABLENAME'] . '(' . $key . ') VALUES (' . $value . ')"');
        G::pr($e);
        die();
    }
}

//GLOBAL
/* * ***** Met à jour les titres lors des remboursements effectués par l'AS400 via le fichier importé RMB ************* */
function convergence_updateTitreRmb($line, $config, $uid_rmb = '') {

    $sqlRmb = 'SELECT * FROM PMT_LISTE_RMBT WHERE APP_UID ="' . $uid_rmb . '"';
    $rRmb = executeQuery($sqlRmb);
    $array = array();
    foreach ($line as $key => $value)
    {
        $array [] = $key . '="' . $value . '"';
    }
    $array[] = 'ID_RMB ="' . $rRmb[1]['NUM_DOSSIER'] . '"';
    $set = implode(',', $array);


    try
    {
        $query = 'UPDATE ' . $config['TABLENAME'] . ' SET ' . $set . ' WHERE NUM_TITRE = ' . $line['NUM_TITRE'];
        $result = executeQuery($query);
    }
    catch (Exception $e)
    {
        G::pr('Erreur : impossible d\'exécuter la requête');
        G::pr($e);
        die();
    }
}

/* * *
 * Function à utiliser après un import de production de chèques dans le Workflow
 *   - Update du statut de la demande de 7 à 6 pour 'Produit'
 *   - Ajout dans la table HistoryLog pour avoir la date de production de la demandes x
 *
 * $data            @array      Contient les datas de la function convergence_importFromAS400
 * $statut          @int        le nouveau statut à appliquer pour la demande, 'Produit' par defaut
 * $user_logged     @varchar    l'id de l'utilisateur courant pour l'historique log
 *
 */
function convergence_changeStatutFromImport($data, $statut = 6) {
    $dossier = array();
    foreach ($data as $row)
    {
        $dossier[$row['NUM_DOSSIER']] = $row['BCONSTANTE'];
    }
    foreach ($dossier as $key => $value)
    {
        try
        {
            $query = 'SELECT APP_UID, NUM_DOSSIER_COMPLEMENT FROM PMT_DEMANDES WHERE NUM_DOSSIER = "' . $key . '" AND APP_NUMBER = (SELECT MAX(APP_NUMBER) AS APP_NUMBER FROM PMT_DEMANDES WHERE NUM_DOSSIER = "' . $key . '")';
            $result = executeQuery($query);
            if (!empty($result))
            {
                convergence_changeStatut($result[1]['APP_UID'], $statut, 'Retour de production Chéquier N°' . $value);
                //Si production complémentaire, on insert un historique dans le dossier d'origine
                if (isset($result[1]['NUM_DOSSIER_COMPLEMENT']) && !is_null($result[1]['NUM_DOSSIER_COMPLEMENT']) && $result[1]['NUM_DOSSIER_COMPLEMENT'] != '')
                {
                    $qComplement = 'SELECT APP_UID FROM PMT_DEMANDES WHERE NUM_DOSSIER = "' . $result[1]['NUM_DOSSIER_COMPLEMENT'] . '" AND APP_NUMBER = (SELECT MAX(APP_NUMBER) AS APP_NUMBER FROM PMT_DEMANDES WHERE NUM_DOSSIER = "' . $result[1]['NUM_DOSSIER_COMPLEMENT'] . '")';
                    $rComplement = executeQuery($qComplement);
                    insertHistoryLogPlugin($rComplement[1]['APP_UID'], $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', "Chéquier de Complément N°" . $value . " produit", 6);
                }
                /*else
                {
                    convergence_changeStatut($result[1]['APP_UID'], $statut, 'Retour de production Chéquier N°' . $value);
                }*/
            }
        }
        catch (Exception $e)
        {
            G::pr('Erreur : les numéros de Dossier ne correspondent pas, veuilliez vérifier que vous avez importer le bon fichier');
            G::pr($e);
            die();
        }
    }
}

//GLOBAL
function modifyAdresseofDemande($app_uid, $case_uid_demande, $callback = '') {
    //je recupere mes valeurs courantes
    $datas = convergence_getAllAppData($app_uid);
    $noMergeDatas = array('SYS_LANG', 'SYS_SKIN', 'SYS_SYS', 'APPLICATION', 'PROCESS', 'TASK', 'INDEX', 'USER_LOGGED', 'USER_USERNAME', 'PIN', 'FLAG_ACTION', 'APP_NUMBER');
    //on modifie le case de la demande
    if (!empty($callback))
    {
        $new_params = call_user_func($callback, $case_uid_demande);
        if (!$new_params)
        {
            unset($callback);
        }
        else
        {
            unset($case_uid_demande);
            $case_uid_demande = $new_params['uid'];
        }
    }
    $oCase = new Cases ();
    $Fields = $oCase->loadCase($case_uid_demande);
    foreach ($datas as $key => $value)
    {
        if (!in_array($key, $noMergeDatas))
            $Fields['APP_DATA'][$key] = $value;
    }
    $oCase->updateCase($case_uid_demande, $Fields);
    if (empty($callback))
        insertHistoryLogPlugin($case_uid_demande, $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', "Modification de l'adresse", 6);
    else
        insertHistoryLogPlugin($case_uid_demande, $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', $new_params['action'], $new_params['status']);
}

//GLOBAL
function modifyDateExpedition($app_uid, $case_uid_liste_prod) {
    //jerecupere mes valeurs courantes
    $datas = convergence_getAllAppData($app_uid);

    //on modifie le case de la demande
    $oCase = new Cases ();
    $Fields = $oCase->loadCase($case_uid_liste_prod);
    $Fields['APP_DATA']['DATE_EXP'] = $datas['dateExp'];

    $oCase->updateCase($case_uid_liste_prod, $Fields);
}

//GLOBAL
function modifyDateVirement($app_uid, $case_uid_liste_rmbt) {
    //je recupere mes valeurs courantes
    $datas = convergence_getAllAppData($app_uid);
    $datasForm = convergence_getAllAppData($case_uid_liste_rmbt);


    //on modifie le case de la demande
    $oCase = new Cases ();
    $Fields = $oCase->loadCase($case_uid_liste_rmbt);
    $Fields['APP_DATA']['DATE_VIREMENT'] = $datas['dateVir'];
    $oCase->updateCase($case_uid_liste_rmbt, $Fields);
    $query = 'SELECT APP_UID FROM PMT_REMBOURSEMENT WHERE STATUT = 9 AND NUM_DOSSIER IN(' . $datasForm['LISTE_DOSSIER'] . ')';
    $result = executeQuery($query);
    if (!empty($result))
    {
        foreach ($result as $row)
        {
            convergence_changeStatut($row['APP_UID'], '10');
        }
    }
}

/* * ***
 *  Fonction récupérant le nombre de dossier traité pour un export
 *
 *  $statut     @integer    le statut des dossiers traités
 *  $groupby      @string     le trie voulu
 */
//LOCALE mais doit etre GLOBAL
function convergence_countCaseToProduct($statut, $codeOper, $detailChequier = 1) {
    $queryCodeOper = '';
    if (isset($codeOper) && $codeOper != 0)
    {
        $queryCodeOper = ' AND CODE_OPERATION = ' . $codeOper;
    }
    $query = 'SELECT APP_UID, THEMATIQUE, THEMATIQUE_LABEL, CODE_OPERATION, T.LABEL FROM PMT_DEMANDES as D INNER JOIN PMT_TYPE_CHEQUIER as T ON (D.CODE_CHEQUIER = T.CODE_CD) WHERE (STATUT = ' . $statut . ' OR (STATUT = 6 AND REPRODUCTION_CHQ = "O")) ' . $queryCodeOper;
    $res = executeQuery($query);
    $count = array();
    if (!empty($res))
    {
        $msg['NOTHING'] = 0;
        $msg['HTML'] = 'Vous allez lancer la production de : <br />';
        foreach ($res as $thema)
        {
            $count[$thema['THEMATIQUE']]['label'] = $thema['THEMATIQUE_LABEL'];
            $count[$thema['THEMATIQUE']]['total']++;
            $count[$thema['THEMATIQUE']]['codeOper'] = $thema['CODE_OPERATION'];
            $count[$thema['THEMATIQUE']][$thema['LABEL']]['chequier'] = $thema['LABEL'];
            $count[$thema['THEMATIQUE']][$thema['LABEL']]['total']++;
        }
        $queryRepro = 'SELECT COUNT(CODE_OPERATION) as NB, CODE_OPERATION FROM PMT_DEMANDES WHERE STATUT = 6 AND REPRODUCTION_CHQ = "O" ' . $queryCodeOper . ' GROUP BY CODE_OPERATION';
        $resRepro = executeQuery($queryRepro);
        if (!empty($resRepro))
        {
            $reproTab = array();
            foreach ($resRepro as $repro)
            {
                (intval($repro['NB']) > 1 ) ? $s = 's' : $s = '';
                $reproTab[$repro['CODE_OPERATION']] = 'dont ' . $repro['NB'] . ' reproduction' . $s . '<br />';
            }
        }
        $queryComp = 'SELECT COUNT(CODE_OPERATION) as NB, CODE_OPERATION FROM PMT_DEMANDES WHERE STATUT = 2 AND COMPLEMENT_CHQ = "1" ' . $queryCodeOper . ' GROUP BY CODE_OPERATION';
        $resComp = executeQuery($queryComp);
        if (!empty($resComp))
        {
            $compTab = array();
            foreach ($resComp as $comp)
            {
                (intval($comp['NB']) > 1 ) ? $s = 's' : $s = '';
                $compTab[$comp['CODE_OPERATION']] = 'dont ' . $comp['NB'] . ' chéquier' . $s . ' de complément<br />';
            }
        }
        foreach ($count as $tab)
        {
            (intval($tab['total']) > 1) ? $s = 's' : $s = '';
            $nb = $tab['total'];
            $th = $tab['label'];

            $msg['HTML'] .= "-  $nb dossier$s pour la thématique :  $th <br />";
            $msg['HTML'] .= $reproTab[$tab['codeOper']];
            $msg['HTML'] .= $compTab[$tab['codeOper']];
            if (!empty($detailChequier))
            {
                foreach ($tab as $chequier)
                {
                    if (is_array($chequier))
                    {
                        $nbCheq = $chequier['total'];
                        $lbCheq = $chequier['chequier'];
                        $msg['HTML'] .= "<span style=\"margin-left:5em\">$lbCheq : $nbCheq</span><br />";
                    }
                }
            }
        }
    }
    else
    {
        $msg['HTML'] = "Aucun dossier à produire ! Veuillez annuler l'opération";
        $msg['NOTHING'] = 1;
    }
    return $msg;
}

/* * ***
 *  Fonction pour vérifier si la demande n'as pas déjà était faite, par un même bénéficiaire
 *
 *  $user           @string    le @@USER_LOGGED du dynaform courant.
 *  $porcess_id     @string    le @@PROCESS du dynaform courant.
 */
//LOCALE mais doit etre GLOBAL
function convergence_justeOneDemande($user, $porcess_id) {

    $query = 'SELECT APPLICATION.APP_UID
              FROM APPLICATION
                JOIN PMT_DEMANDES
                    ON APPLICATION.APP_UID = PMT_DEMANDES.APP_UID
              WHERE APPLICATION.APP_INIT_USER ="' . $user . '" AND APPLICATION.APP_STATUS = "COMPLETED" AND PRO_UID = "' . $porcess_id . '"
              AND PMT_DEMANDES.STATUT <> 999 AND PMT_DEMANDES.STATUT <> 0';
    $res = executeQuery($query);

    if (!empty($res))
    {
        return 0;
    }
    return 1;
}

// Récupère le champs UID généré par l'auto-incrémentation pour le conserver lors d'une édition ou de le générer
function convergence_keepAutoIncrement($table, $field, $value) {
    $qInsert = 'INSERT INTO ' . strtoupper($table) . ' (' . strtoupper($field) . ') VALUES (' . $value . ')';
    $rInsert = executeQuery($qInsert);
    $q = 'SELECT UID FROM ' . strtoupper($table) . ' WHERE ' . strtoupper($field) . ' = "' . $value . '"';
    $r = executeQuery($q);
    return $r[1]['UID'];
}

## disable user conection web services
function pmDisableUser($userName) {
    $ret = 1;
    //$IP = $_SERVER['HTTP_HOST'];
    $pfServer = new SoapClient('http://' . HostName . '/typo3conf/ext/pm_webservices/serveur.php?wsdl');
    $ret = $pfServer->disableAccount(array('username' => $userName));
    return $ret;
}

## end disable user conection web services
## actions import CSV
function getDataCSV($firstLineCsvAs = 'on') {
    set_include_path(PATH_PLUGINS . 'convergenceList' . PATH_SEPARATOR . get_include_path());
    require_once 'classes/class.parseCSV.php';
    $csv = new parseCSV();
    $csv->heading = ($firstLineCsvAs == 'on') ? true : false;
    $csv->auto($_FILES['form']['tmp_name']['CSV_FILE']);
    $data = $csv->data;
    $_SESSION['REQ_DATA_CSV'] = $data;
    $_SESSION['CSV_FILE_NAME'] = $_FILES['form']['name']['CSV_FILE'];
    return $data;
}

function getProUid($tableName) {
    $sSQL = "SELECT * FROM ADDITIONAL_TABLES WHERE ADD_TAB_NAME ='$tableName'";
    $aResult = executeQuery($sSQL);
    $proUid = '0';
    if (is_array($aResult) && count($aResult) > 0)
    {
        $proUid = $aResult[1]['PRO_UID'];
    }
    else
    {
        $sSQL = "SELECT PRO_UID FROM APPLICATION WHERE APP_UID='$tableName'";
        $aResult = executeQuery($sSQL);
        $proUid = '';
        if (isset($aResult[1]['PRO_UID']))
            $proUid = $aResult[1]['PRO_UID'];
    }
    return $proUid;
}

function getRolUserImport() {
    require_once ("classes/model/Users.php");
    $oUser = new Users();
    $oDetailsUser = $oUser->load($_SESSION ['USER_LOGGED']);
    return $oDetailsUser['USR_ROLE'];
}

function genDataReport($tableName) {
    G::loadClass('pmTable');
    G::loadClass('pmFunctions');
    require_once 'classes/model/AdditionalTables.php';
    // Check if the Table is Report or PM Table
    $tableType = "Report";
    $sqlAddTable = "SELECT * FROM ADDITIONAL_TABLES WHERE ADD_TAB_NAME = '$tableName' ";
    $resAddTable = executeQuery($sqlAddTable);
    if (sizeof($resAddTable)) {
        if ($resAddTable[1]['PRO_UID'] == '') {
            $tableType = "pmTable";
        }
    }
    if ($tableType == "Report") {
        $cnn = Propel::getConnection('workflow');
        $stmt = $cnn->createStatement();
        $additionalTables = new AdditionalTables();
        $oPmTable = $additionalTables->loadByName($tableName);
        $table = $additionalTables->load($oPmTable['ADD_TAB_UID']);
        if ($table['PRO_UID'] != '') {
            $truncateRPTable = "TRUNCATE TABLE  " . $tableName . " ";
            $rs = $stmt->executeQuery($truncateRPTable, ResultSet::FETCHMODE_NUM);
            $additionalTables->populateReportTable($table['ADD_TAB_NAME'], pmTable::resolveDbSource($table['DBS_UID']), $table['ADD_TAB_TYPE'], $table['PRO_UID'], $table['ADD_TAB_GRID'], $table['ADD_TAB_UID']);
        }
    }
}

function deletePMCases($caseId,$tableName) {
    $query1 = "DELETE FROM wf_" . SYS_SYS . ".APPLICATION WHERE APP_UID='" . $caseId . "' ";
    $apps1 = executeQuery($query1);
    $query2 = "DELETE FROM wf_" . SYS_SYS . ".APP_DELAY WHERE APP_UID='" . $caseId . "'";
    $apps2 = executeQuery($query2);
    $query3 = "DELETE FROM wf_" . SYS_SYS . ".APP_DELEGATION WHERE APP_UID='" . $caseId . "'";
    $apps3 = executeQuery($query3);
    $query4 = "DELETE FROM wf_" . SYS_SYS . ".APP_DOCUMENT WHERE APP_UID='" . $caseId . "'";
    $apps4 = executeQuery($query4);
    $query5 = "DELETE FROM wf_" . SYS_SYS . ".APP_MESSAGE WHERE APP_UID='" . $caseId . "'";
    $apps5 = executeQuery($query5);
    $query6 = "DELETE FROM wf_" . SYS_SYS . ".APP_OWNER WHERE APP_UID='" . $caseId . "'";
    $apps6 = executeQuery($query6);
    $query7 = "DELETE FROM wf_" . SYS_SYS . ".APP_THREAD WHERE APP_UID='" . $caseId . "'";
    $apps7 = executeQuery($query7);
    $query8 = "DELETE FROM wf_" . SYS_SYS . ".SUB_APPLICATION WHERE APP_UID='" . $caseId . "'";
    $apps8 = executeQuery($query8);
    $query9 = "DELETE FROM wf_" . SYS_SYS . ".CONTENT WHERE CON_CATEGORY LIKE 'APP_%' AND CON_ID='" . $caseId . "'";
    $apps9 = executeQuery($query9);
    $query10 = "DELETE FROM wf_" . SYS_SYS . ".APP_EVENT WHERE APP_UID='" . $caseId . "'";
    $apps10 = executeQuery($query10);
    $query11 = "DELETE FROM wf_" . SYS_SYS . ".APP_CACHE_VIEW WHERE APP_UID='" . $caseId . "'";
    $apps11 = executeQuery($query11);
    /*$query12 = "DELETE FROM wf_" . SYS_SYS . ".APP_HISTORY WHERE APP_UID='" . $caseId . "'";
    $apps12 = executeQuery($query12);*/
    $query13 = "DELETE FROM wf_" . SYS_SYS . "." . $tableName . " WHERE APP_UID='" . $caseId . "'";
    $apps13=executeQuery($query13);
}

function getDynaformFields($jsonFieldsCSV, $tableName) {

    require_once PATH_CONTROLLERS . 'pmTablesProxy.php';
    G::LoadClass('reportTables');
    $proUid = getProUid($tableName);
    $oReportTables = new pmTablesProxy();
    $dynFields = array();
    $dynFields = $oReportTables->_getDynafields($proUid, 'xmlform', 0, 10000, null);
    $aDynFields = array();
    foreach ($dynFields['rows'] as $row) {
        $aDynFields[strtoupper($row['FIELD_NAME'])] = $row['FIELD_NAME'];
    }
    $_dataFields = array();
    foreach ($aDynFields as $key => $value) {
        $record = array("FIELD_NAME" => $value, "FIELD_DESC" => $key, "COLUMN_CSV" => 'Select...');
        $_dataFields[] = $record;
    }
    return (array(sizeof($_dataFields), array_values($_dataFields)));
}

function getConfigCSV($data, $idInbox, $firstLineHeader = "") {
    $rolUser = getRolUserImport();
    $query = "SELECT * FROM PMT_CONFIG_CSV_IMPORT WHERE ROL_CODE = '" . $rolUser . "' AND ID_INBOX = '" . $idInbox . "'";
    $aData = executeQuery($query);
    if (sizeof($aData)) {
        for ($i = 0; $i < count($data); $i++) {
            foreach ($aData as $key => $row) {
                //G::pr($data[$i]['FIELD_NAME']." --- ".$row['CSV_FIELD_NAME']);
                if ($data[$i]['FIELD_NAME'] == $row['CSV_FIELD_NAME'])
                {
                    if($firstLineHeader == "")
                        $data[$i]['COLUMN_CSV'] = $row['CSV_COLUMN'];
                    else
                    {
                        if($row['CSV_FIRST_LINE_HEADER'] == $firstLineHeader) 
                            $data[$i]['COLUMN_CSV'] = $row['CSV_COLUMN'];
                    }
                    if (!empty($row['CSV_TYPE']))
                    {
                        $data[$i]['COLUMN_TYPE'] = $row['CSV_TYPE'];
                    }
                    if (!empty($row['CSV_PIVOT_EDIT']))
                    {
                        $data[$i]['DELETE_EDIT_FIELD'] = $row['CSV_PIVOT_EDIT'];
                    }
                    if (!empty($row['CSV_REQUIRED']))
                    {
                        $data[$i]['REQ_COLUMN'] = $row['CSV_REQUIRED'];
                    }
                }
            }
        }
    }
    return $data;
}

function _convert($content) {
    if (!mb_check_encoding($content, 'UTF-8') OR !($content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32'))) {
        $content = mb_convert_encoding($content, 'UTF-8');

        if (mb_check_encoding($content, 'UTF-8')) {
            // log('Converted to UTF-8');
        } 
        else {
            // log('Could not converted to UTF-8');
        }
    }
    return $content;
}

/* * * add by Nico for log file
 * This function create a log file and remove wrong data import
 *
 * $dataCSV
 * $items
 * $tableName
 * $firstLineHeader
 * $dataEdit
 *
 * return $dataCSV
 * ** */
function createLog($dataCSV, $items, $tableName, $firstLineHeader, $dataEdit = '') {
    $logField = array();
    //$sPath = 'SELECT PATH_FILE FROM PMT_LISTE_OPER GROUP BY PATH_FILE';
    //$rPath = executeQuery($sPath);
    $file = $_SESSION['CSV_FILE_NAME'];
    (array) $ftemp = explode('.', $file);
    //if (!empty($rPath))
    //    $path = $rPath[1]['PATH_FILE'] . '/LOG/' . $ftemp[0] . '_' . date("YmdHis") . '_log.txt';
    //else
    // TODO voir pour le chemin en define
    $path = '/var/tmp/import_' . $ftemp[0] . '_' . date("YmdHis") . '_log.txt';
    $Log = 'Le fichier ' . $file . ' a été intégré le ' . date("d/m/Y à H:i:s") . ".\r\n\n\n";
    $nbcurrentLine = $nbAnomalie = $nbCreate = $nbModif = 0;
    foreach ($dataCSV as $row)
    {
        $checkLog = array();
        $logLine = array();
        $whereUpdate = array();
        $nbcurrentLine++;
        foreach ($items as $field)
        {
            $param = array();
            $param['LENGTH'] = 0;
            //$param['REQUIRED'] = 'no'; // à implémenter
            if (!empty($field['REQUIRED_COLUMN']))
            {
                $param['REQUIRED'] = 'yes';
            }
            else
            {
                $param['REQUIRED'] = 'no';
            }
            if (!empty($field['COLUMN_TYPE']))
            {
                $param['AS400_TYPE'] = $field['COLUMN_TYPE'];
            }
            else
            {
                $param['AS400_TYPE'] = 'defaut';
            }
            $param['FIELD_NAME'] = $field['FIELD_NAME'];
            if ($firstLineHeader == 'on')
            {
                if (isset($row[$field['COLUMN_CSV']])) // le nom de la colonne est présent dans le csv
                {
                    $param['FIELD_DESCRIPTION'] = $field['COLUMN_CSV'];
                    //if ($row[$field['COLUMN_CSV']])
                    $value = _convert($row[$field['COLUMN_CSV']]);
                }
                else // sinon c'est une constante
                {
                    //if ($field['COLUMN_CSV'])
                    $value = _convert($field['COLUMN_CSV']);
                }
            }
            else
            {
                $aCol = explode(' ', trim($field['COLUMN_CSV']));
                if ((isset($aCol[0]) && trim($aCol[0]) == 'Column' ) && ( isset($aCol[1]) && isset($row[$aCol[1]]) ))
                {// le num colonne exite dans row
                    $value = _convert($row[$aCol[1]]);
                }
                elseif (( isset($aCol[0]) && trim($aCol[0]) != 'Column'))
                { // c'est une constante
                    $value = _convert($field['COLUMN_CSV']);
                }
            }
            $checkLog = convergence_checkFieldLog($value, $param, 'csv');
            if ($checkLog != 1)
            {
                $logLine[] = implode(",\r\n\t", $checkLog);
            }
            elseif ($dataEdit != '')
            {
                foreach ($dataEdit as $array)
                {
                    $fieldNameEditDelete = htmlspecialchars_decode($array['CSV_FIELD_NAME']);
                    if ($fieldNameEditDelete == htmlspecialchars_decode($field['FIELD_NAME']))
                    {
                        $whereUpdate[] = "$fieldNameEditDelete = '" . mysql_escape_string($value) . "' ";
                    }
                }
            }
        }
        if (!empty($logLine))
        {
            $logField[$nbcurrentLine] = implode(",\r\n\t", $logLine);
            unset($dataCSV[$nbcurrentLine - 1]); // on n'importe pas les lignes pourries
            $nbAnomalie++;
        }
        elseif ($dataEdit != '')
        {
            $where = implode(' AND ', $whereUpdate);
            $sql = 'SELECT * FROM ' . $tableName . ' WHERE ' . $where;
            $rSql = executeQuery($sql);
            if (!empty($rSql))
            {
                $nbModif++;
            }
            else
            {
                $nbCreate++;
            }
        }
        else
        {
            $nbCreate++;
        }
    }
    unset($row);
    unset($field);
    if (!empty($logField))
    {
        $Log .= "Liste des erreurs survenues lors de l’intégration :\r\n";
        foreach ($logField as $k => $v)
        {
            $Log .= "Ligne N° $k : $v.\r\n";
        }
    }
    else
    {
        $Log .= "Aucune erreur survenue lors de l'intégration\r\n";
    }
    $Log .= "\nNombre création         :   $nbCreate\r\nNombre de modification  :   $nbModif\r\nNombre d'anomalie      :   $nbAnomalie\r\nTotal de lignes        :   $nbcurrentLine\r\n";
    $handle = fopen($path, 'x+');
    fwrite($handle, $Log);
    fclose($handle);
    $dataCSV = array_merge((array) $dataCSV); // on ré-indexe le tableau
    return $dataCSV;
}

function dataDynaforms($resultDynaform,$proUid)
{
    ini_set('memory_limit', '256M');
    $_dataForms =  array();
    foreach($resultDynaform As $rowDynaform)
    {
        $dynaform = new Form($proUid . PATH_SEP . $rowDynaform['DYN_UID'], PATH_DYNAFORM , SYS_LANG , false);
        foreach ($dynaform->fields as $fieldName => $field) 
        {
            if( $field->type == 'dropdown' || $field->type == 'radiogroup' || $field->type == 'checkgroup' || $field->type == 'listbox' || $field->type == 'yesno' || $field->type == 'suggest')
            {
                $aData = array();
                $dataSQL = array();
                $data = array();
                if(strlen($field->sql))
                {
                    $query = $field->sql;
                    $valueData = explode(",",$query);
                    $valueId = explode(" ",$valueData[0]);
                    $position = count($valueId)-1 ;
                    $valueId = $valueId[$position];
                    $valueDataCount = count($valueData);
                    $valueName = explode(" ",$valueData[$valueDataCount-1]);
                    for($i = 0; $i <count($valueName) ; $i++)
                    {
                        if($valueName[$i]=="from" || $valueName[$i]=="FROM")
                        {
                            $dataName = $valueName[$i-1];   
                            break;
                        }
                    }
                    
                    $aData = executeQuery($field->sql);
                }   
                if(sizeof($aData))
                {
                    foreach($aData As $key => $row)
                    {
                        $rowData = array ( 'id'=>$row[$valueId],'descrip'=>$row[$dataName]);
                        $dataSQL[] = $rowData;
                    }
                }
                    
                if(sizeof($field->option))
                {
                    foreach($field->option As $key => $row)
                    {
                        $rowData = array ( 'id'=>$key,'descrip'=>$row);
                        $data[] = $rowData;
                    }
                }
                $record = array (
                        "FIELD_NAME" => $field->name, 
                        "FIELD_LABEL" => $field->label,
                        "FIELD_TYPE" => $field->type,
                        "FIELD_DEFAULT_VALUE" => $field->defaultValue,
                        "FIELD_DEPENDENT_FIELD" => $field->dependentFields,
                        "FIELD_OPTION" => $data,
                        "FIELD_READONLY" => $field->readonly,
                        "FIELD_SQL_CONNECTION" => $field->sqlConnection,
                        "FIELD_SQL" => $field->sql,
                        "FIELD_SQL_OPTION" => $dataSQL,
                        "FIELD_SELECTED_VALUE" => $field->selectedValue,
                        "FIELD_SAVE_LABEL" => $field->saveLabel
                        );
                $_dataForms[] = $record;
            }
        }
    }
    return $_dataForms;
}

function createFileTmpCSV($csv, $csv_file) {
    if ($csv != '') {
        $sPathName = PATH_DOCUMENT . "csvTmp";
        if (!is_dir($sPathName))
            G::verifyPath($sPathName, true);
        if (!$handle = fopen($sPathName . "/" . $csv_file, "w")) {
            echo "Cannot open file";
            exit;
        }
        if (fwrite($handle, utf8_decode($csv)) === FALSE) {
            echo "Cannot write to file";
            exit;
        }
        fclose($handle);

        // Use it for debug csvTemp files
        //
        //$handle = fopen("/var/tmp/csvMore.csv", "w");
        //fclose($handle);
    }
}

function importCreateCase($jsonMatchFields,$uidTask, $tableName,$firstLineHeader, $typeAction)
{
    G::LoadClass('case');
    $items   = json_decode($jsonMatchFields,true); 
    $dataCSV = isset($_SESSION['REQ_DATA_CSV']) ?$_SESSION['REQ_DATA_CSV']: array(); 
    $USR_UID = $_SESSION['USER_LOGGED'];
    $_SESSION['USER_LOGGED_INI'] = $USR_UID;
    $proUid  = getProUid($tableName);
    $totalCases = 0;
    // check all fields and remove wrong data

    $dataCSVdebug = createLog($dataCSV, $items, $tableName, $firstLineHeader);
    //$dataCSV = $dataCSVdebug;
    // load Dynaforms of process
    $select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM DYNAFORM WHERE PRO_UID = '".$proUid ."'";
    $resultDynaform = executeQuery($select);
    $_dataForms =  dataDynaforms($resultDynaform,$proUid);
    // end load dynaforms process   
    $select = executeQuery("SELECT MAX(IMPCSV_IDENTIFY) AS IDENTIFY FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_TABLE_NAME = '$tableName' ");
    $identify = isset($select[1]['IDENTIFY'])? $select[1]['IDENTIFY']:0;
    $identify = $identify + 1;
    $csv_file = $tableName."_".$identify.".csv";  
    $csv_sep = ",";     
    $csv="";  
    $csv_end = "\n";
    $swInsert = 0;

    foreach ($dataCSV as $row) 
    {
        $totRow = sizeof($row);
        $totIni = 1;
       
        if($totalCases >= 10)
        {
            /* add header on csv temp files for import background */
            if ($firstLineHeader == 'on' && $swInsert == 0)
              {
              $csv .= getCSVHeader($dataCSV, $csv_sep) . $csv_end;
            } 
            foreach($row as $value)
            {
                if($totIni == $totRow)
                    $csv.= _convert($value);
                else
                    $csv.= _convert($value).$csv_sep;
                $totIni++;
            }
            $csv.=$csv_end;
            if($swInsert == 0)
            {
                $select = executeQuery("SELECT MAX(IMPCSV_ID) AS ID_CSV FROM PMT_IMPORT_CSV_DATA");
                $maxId = isset($select[1]['ID_CSV'])? $select[1]['ID_CSV']:0;
                $maxId = $maxId + 1;
                foreach ($items as $field)
                { 
                    $insert = "INSERT INTO PMT_IMPORT_CSV_DATA 
                          (IMPCSV_ID, IMPCSV_FIELD_NAME, IMPCSV_VALUE,IMPCSV_TAS_UID, IMPCSV_TABLE_NAME, IMPCSV_FIRSTLINEHEADER, IMPCSV_IDENTIFY, IMPCSV_TYPE_ACTION) VALUES
                          ('$maxId','".$field['FIELD_NAME']."', '".$field['COLUMN_CSV']."', '$uidTask', '$tableName','$firstLineHeader', '$identify', '$typeAction')";
                    executeQuery($insert);
                    $swInsert = 1;
                    $maxId++;
                }
            }
            
        }
        else 
        {
            $appData =  array();
            foreach ($items as $field) { 
                if($firstLineHeader == 'on'){
                
                    if(isset($row[$field['COLUMN_CSV']]))
                    {
                        if($row[$field['COLUMN_CSV']]  != '')
                            $appData[$field['FIELD_NAME']] = _convert($row[$field['COLUMN_CSV']]);                           
                        else
                            $appData[$field['FIELD_NAME']] = '';
                    }
                    else
                    {
                        if($field['COLUMN_CSV']  != '')
                            $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                        else
                            $appData[$field['FIELD_NAME']] = '';
                    } 
                }
                else
                {
                    $aCol = explode(' ', trim($field['COLUMN_CSV']));
                    if( (isset($aCol[0]) && trim($aCol[0]) == 'Column' ) &&  ( isset($aCol[1]) && isset($row[$aCol[1]]) ) )
                    {   
                        $appData[$field['FIELD_NAME']] = _convert($row[$aCol[1]]);
                    }
                    else if ( ( isset($aCol[0])  &&  trim($aCol[0]) != 'Column' )  ){
                        $appData[$field['FIELD_NAME']] =  _convert($field['COLUMN_CSV']);
                    }        
                }
            }       

        // labels //
      
            foreach($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;
                    
                    if($key == $row['FIELD_NAME'])
                    {
                        $i = isset($fields) ? $fields : $row['FIELD_DEFAULT_VALUE'];

                        if(count($row['FIELD_SQL_OPTION']))
                        {                            
                            $options = $row['FIELD_SQL_OPTION'];
                            $id = "";
                            $label = "";
                            foreach($options As $row2)
                            {
                                if($row2['id'] == $i)
                                {
                                    $id = $row2['id'];
                                    $label = $row2['descrip'];
                                    break;
                                }
                            }
                            
                            if($id=="" && $label=="")
                            {
                                $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                            }
                            
                            $record[$row['FIELD_NAME']] = $id;
                            $record[$row['FIELD_NAME']."_label"] = $label;
                            $appData = array_merge($record,$appData);
                        }
                        else
                        {
                            if(count($row['FIELD_OPTION']))
                            {
                                $options = $row['FIELD_OPTION'];
                                $id = "";
                                $label = "";
                                foreach($options As $row2)
                                {
                                    if($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }
                                
                                $record = Array();
                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME']."_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                        }
                    }
                }   
            }
            
            foreach($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;
                    if($row['FIELD_TYPE'] != 'yesno')
                    { 
                        $appData[$row['FIELD_NAME']."_label"] = isset($appData[$row['FIELD_NAME']."_label"]) ? $appData[$row['FIELD_NAME']."_label"]:'';
                        ## Control label type field suggest
                        if($row['FIELD_TYPE'] == 'suggest')
                        {
                            $appData[$row['FIELD_NAME'] . "_label"] = isset($appData[$row['FIELD_NAME']]) ? $appData[$row['FIELD_NAME']] : '';                              
                        }
                        ## end Control label type field suggest
                        if($appData[$row['FIELD_NAME']."_label"] =="")
                        {
                            $i = $row['FIELD_DEFAULT_VALUE'];   
                            if(count($row['FIELD_SQL_OPTION']))
                            {
                                
                                $options = $row['FIELD_SQL_OPTION'];
                                $id = "";
                                $label = "";
                                foreach($options As $row2)
                                {
                                    if($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }
                                
                                if($id=="" && $label=="")
                                {
                                    $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                    $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                                }
                                
                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME']."_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                            else
                            {
                                if(count($row['FIELD_OPTION']))
                                {
                                    $options = $row['FIELD_OPTION'];
                                    $id = "";
                                    $label = "";
                                    foreach($options As $row2)
                                    {
                                        if($row2['id'] == $i)
                                        {
                                            $id = $row2['id'];
                                            $label = $row2['descrip'];
                                            break;
                                        }
                                    }
                                    
                                    if($id=="" && $label=="")
                                    {
                                        $id = $row['FIELD_OPTION'][0]['id'];
                                        $label = $row['FIELD_OPTION'][0]['descrip'];
                                    }
                                
                                    $record[$row['FIELD_NAME']] = $id;
                                    $record[$row['FIELD_NAME']."_label"] = $label;
                                    $appData = array_merge($record, $appData);
                                   
                                }
                            }
                        }
                    }
                    else 
                    {
                        $record = Array();
                        if( $appData[$row['FIELD_NAME']] == '' || $appData[$row['FIELD_NAME']] == ' ' )
                        {
                            $appData[$row['FIELD_NAME']] = $row['FIELD_DEFAULT_VALUE'];
                            //$appData = array_merge($record, $appData);
                        }
                    }
                    if($appData[$row['FIELD_NAME']] !='' && (isset($appData[$row['FIELD_NAME']."_label"]) && $appData[$row['FIELD_NAME']."_label"] ==''))
                    {
                        $appData[$row['FIELD_NAME']."_label"] = $label;
                    }
                }
            }
         
         // end labels 
        
            foreach ($appData as $key => $value)
            {   
                if(!is_array($value))
                    $appData[$key] = ($value);
                else
                    $appData[$key] = ($value);
            } 
           
            $appData['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
            $appData['FLAG_ACTION'] = 'multipleDerivation';
            $appData['EXEC_AUTO_DERIVATE'] = 'NO';
            $appData['eligible'] = 0; // only process beneficiary
            $appData['FLAG_EDIT'] = 1;
            $appData['STATUT'] = 1;
            $appData['CurrentUserAutoDerivate'] = $USR_UID;
            $appData['SW_CREATE_CASE'] = 1; // needed to create cases. If a loop is generated when you run the trigger
            // $appData['LOOP'] = 1;           

            $caseUID = PMFNewCase($proUid, $USR_UID, $uidTask, $appData);        
            if($caseUID > 0) 
            {  
                $oCase = new Cases ();
                $FieldsCase = $oCase->loadCase ( $caseUID ); 
                $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER']; 
                $oCase->updateCase($caseUID,$FieldsCase);
                autoDerivate($proUid,$caseUID,$USR_UID);
              
                /* Comment by Nico 28/08/2013
                 * Please, don't remove the comment because make some bug on process
                 * or explain to me why you want to put this value
                 *
                 */
               
                
            }
            
        }
        $totalCases++;
    }
    //  genDataReport($tableName);
    # create file tmp
    createFileTmpCSV($csv,$csv_file);   
    # end create file tmp 
    
    unset($_SESSION['REQ_DATA_CSV']);
    return $totalCases;
}

function importCreateCaseDelete($jsonMatchFields,$uidTask, $tableName,$firstLineHeader,$dataDeleteEdit)
{
    G::LoadClass('case');
    $items   = json_decode($jsonMatchFields,true);
    $dataCSV = isset($_SESSION['REQ_DATA_CSV']) ?$_SESSION['REQ_DATA_CSV']: array();
    $USR_UID = $_SESSION['USER_LOGGED'];
    $_SESSION['USER_LOGGED_INI'] = $USR_UID;
    $proUid  = getProUid($tableName);
    $totalCases = 0;
    $itemsDeleteEdit = json_decode($dataDeleteEdit, true);

    $dataCSVdebug = createLog($dataCSV, $items, $tableName, $firstLineHeader, $itemsDeleteEdit);
    $dataCSV = $dataCSVdebug;
    // load Dynaforms of process
    $select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM DYNAFORM WHERE PRO_UID = '".$proUid ."'";
    $resultDynaform = executeQuery($select);
    $idCasesGenerate = "''";    
    
    $_dataForms =  dataDynaforms($resultDynaform,$proUid);
        
    $select = executeQuery("SELECT MAX(IMPCSV_IDENTIFY) AS IDENTIFY FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_TABLE_NAME = '$tableName' ");
    $identify = isset($select[1]['IDENTIFY'])? $select[1]['IDENTIFY']:0;
    $identify = $identify + 1;
    $csv_file = $tableName."_".$identify.".csv";  
    $csv_sep = ",";     
    $csv="";  
    $csv_end = "\n";
    $swInsert = 0;

    foreach ($dataCSV as $row) 
    {
        $totRow = sizeof($row);
        $totIni = 1;
      
        if($totalCases >= 10) 
        {
            /* add header on csv temp files for import background */
            if ($firstLineHeader == 'on' && $swInsert == 0)
            {
                $csv .= getCSVHeader($dataCSV, $csv_sep) . $csv_end;
            }
            foreach($row as $value)
            {
                if($totIni == $totRow)
                    $csv.= _convert($value);
                else
                    $csv.= _convert($value).$csv_sep;
                $totIni++;
            }
            $csv.=$csv_end;
            if($swInsert == 0)
            {
                $select = executeQuery("SELECT MAX(IMPCSV_ID) AS ID_CSV FROM PMT_IMPORT_CSV_DATA");
                $maxId = isset($select[1]['ID_CSV'])? $select[1]['ID_CSV']:0;
                $maxId = $maxId + 1;
                foreach ($items as $field)
                { 
                    $insert = "INSERT INTO PMT_IMPORT_CSV_DATA 
                          (IMPCSV_ID, IMPCSV_FIELD_NAME, IMPCSV_VALUE,IMPCSV_TAS_UID, IMPCSV_TABLE_NAME, IMPCSV_FIRSTLINEHEADER, IMPCSV_IDENTIFY, IMPCSV_TYPE_ACTION, IMPCSV_CONDITION_ACTION) VALUES
                          ('$maxId','".$field['FIELD_NAME']."', '".$field['COLUMN_CSV']."', '$uidTask', '$tableName','$firstLineHeader', '$identify', 'ADD_DELETE', '".mysql_real_escape_string($dataDeleteEdit)."' )";
                    executeQuery($insert);
                    $swInsert = 1;
                    $maxId++;
                }
            }
            
        }
        else
        {
            $appData =  array();
            foreach ($items as $field) 
            { 
                if($firstLineHeader == 'on')
                {
                    if(isset($row[$field['COLUMN_CSV']]))
                    {
                        if($row[$field['COLUMN_CSV']]  != '')
                            $appData[$field['FIELD_NAME']] = _convert($row[$field['COLUMN_CSV']]);
                        else
                            $appData[$field['FIELD_NAME']] = ''; // Attention en dev chaine vide et espace en pré-prod : POURQUOI ?
                    }
                    else
                    {
                        if($field['COLUMN_CSV']  != '')
                            $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                        else
                            $appData[$field['FIELD_NAME']] = ''; // Attention en dev chaine vide et espace en pré-prod : POURQUOI ?
                    } 
                }
                else
                {
                    $aCol = explode(' ', trim($field['COLUMN_CSV']));
                    if( (isset($aCol[0]) && trim($aCol[0]) == 'Column' ) &&  ( isset($aCol[1]) && isset($row[$aCol[1]]) ) )
                        $appData[$field['FIELD_NAME']] = _convert($row[$aCol[1]]);
                    else if ( ( isset($aCol[0])  &&  trim($aCol[0]) != 'Column' )  ){
                        $appData[$field['FIELD_NAME']] =  _convert($field['COLUMN_CSV']);
                    }        
                }
            }       
            
            foreach($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;
                        
                    if($key == $row['FIELD_NAME'])
                    {
                        $i = isset($fields)?$fields:$row['FIELD_DEFAULT_VALUE'];
                            
                        if(count($row['FIELD_SQL_OPTION']))
                        {
                            $options = $row['FIELD_SQL_OPTION'];
                            $id = "";
                            $label = "";
                            foreach($options As $row2)
                            {
                                if($row2['id'] == $i)
                                {
                                    $id = $row2['id'];
                                    $label = $row2['descrip'];
                                    break;
                                }
                            }
                                
                            if($id=="" && $label=="")
                            {
                                $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                            }
                            
                            $record[$row['FIELD_NAME']] = $id;
                            $record[$row['FIELD_NAME']."_label"] = $label;
                            $appData = array_merge($record,$appData);
                        }
                        else
                        {
                            if(count($row['FIELD_OPTION']))
                            {
                                $options = $row['FIELD_OPTION'];
                                $id = "";
                                $label = "";
                                foreach($options As $row2)
                                {
                                    if($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }
                                
                                $record = Array();
                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME']."_label"] = $label;
                                $appData = array_merge($record,$appData);
                            }
                        }
                    }
                }   
            }
            $whereDelete = '';
            foreach($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;
                    if($row['FIELD_TYPE'] != 'yesno')
                    {
                        $appData[$row['FIELD_NAME']."_label"] = isset($appData[$row['FIELD_NAME']."_label"])?$appData[$row['FIELD_NAME']."_label"]:'';
                        ## Control label type field suggest
                        if($row['FIELD_TYPE'] == 'suggest')
                        {
                            $appData[$row['FIELD_NAME'] . "_label"] = isset($appData[$row['FIELD_NAME']]) ? $appData[$row['FIELD_NAME']] : '';
                        }
                        ## end Control label type field suggest
                        if($appData[$row['FIELD_NAME']."_label"] =="")
                        {
                            $i = $row['FIELD_DEFAULT_VALUE'];   
                            if(count($row['FIELD_SQL_OPTION']))
                            {
                                $options = $row['FIELD_SQL_OPTION'];
                                $id = "";
                                $label = "";
                                foreach($options As $row2)
                                {
                                    if($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }
                                    
                                if($id=="" && $label=="")
                                {
                                    $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                    $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                                }
                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME']."_label"] = $label;
                                $appData = array_merge($record,$appData);
                            }
                            else
                            {
                                if(count($row['FIELD_OPTION']))
                                {
                                    $options = $row['FIELD_OPTION'];
                                    $id = "";
                                    $label = "";
                                    foreach($options As $row2)
                                    {
                                        if($row2['id'] == $i)
                                        {
                                            $id = $row2['id'];
                                            $label = $row2['descrip'];
                                            
                                        }
                                    }
                                        
                                    if($id=="" && $label=="")
                                    {
                                        $id = $row['FIELD_OPTION'][0]['id'];
                                        $label = $row['FIELD_OPTION'][0]['descrip'];
                                    }
                                    $record[$row['FIELD_NAME']] = $id;
                                    $record[$row['FIELD_NAME']."_label"] = $label;
                                    $appData = array_merge($record,$appData);
                                }
                            }
                        }
                    }
                    else 
                    {
                        $record = Array();
                        if( $appData[$row['FIELD_NAME']] == '' || $appData[$row['FIELD_NAME']] == ' ' )
                        {
                            $appData[$row['FIELD_NAME']] = $row['FIELD_DEFAULT_VALUE'];
                            //$appData = array_merge($record, $appData);
                        }
                    }
                    if($appData[$row['FIELD_NAME']] !='' && (isset($appData[$row['FIELD_NAME']."_label"]) && $appData[$row['FIELD_NAME']."_label"] ==''))
                    {
                        $appData[$row['FIELD_NAME']."_label"] = $label;
                    }
                }
                // delete cases 
                foreach ($itemsDeleteEdit as $field ) 
                { 
                    $fieldNameEditDelete = htmlspecialchars_decode($field['CSV_FIELD_NAME']);
                    if($fieldNameEditDelete == $key )
                    {
                        if($whereDelete == '')
                            $whereDelete = $key." = '".mysql_escape_string($fields)."'";
                        else 
                            $whereDelete = $whereDelete." AND " .$key." = '".mysql_escape_string($fields)."'";
                    }
                } 
                // end delete cases 
                
            }
            // end labels
            
            // delete cases            
            if($whereDelete != '')
            {
                $query = "SELECT APP_UID FROM $tableName WHERE $whereDelete AND APP_UID NOT IN ( $idCasesGenerate ) "; //print($query.'  '); 
                $deleteData = executeQuery($query);
                if(sizeof($deleteData))
                {
                    foreach($deleteData as $index)
                    {   
                        $CurDateTime=date('Y-m-d H:i:s');
                        insertHistoryLogPlugin($index['APP_UID'],$_SESSION['USER_LOGGED'],$CurDateTime,'1',$index['APP_UID'],'Delete Case');
                        deletePMCases($index['APP_UID'],$tableName); 
                    }
                    //genDataReport($tableName);
                }
            }
            // end delete cases
            foreach ($appData as $key => $value)
            {
                if(!is_array($value))
                    $appData[$key] = ($value);
                else
                    $appData[$key] = $value;
            } 
            $appData['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
            $appData['FLAG_ACTION'] = 'multipleDerivation';
            $appData['EXEC_AUTO_DERIVATE'] = 'NO';
            $appData['eligible'] = 0; // only process beneficiary
            $appData['FLAG_EDIT'] = 1;
            $appData['STATUT'] = 1;
            $appData['CurrentUserAutoDerivate'] = $USR_UID;
            $appData['SW_CREATE_CASE'] = 1; // needed to create cases. If a loop is generated when you run the trigger
            $caseUID = PMFNewCase($proUid, $USR_UID, $uidTask, $appData);  
            if($totalCases == 0)
                $idCasesGenerate = "'".$caseUID."'";
            else
                $idCasesGenerate = $idCasesGenerate.", '".$caseUID."'";
            if($caseUID >0) 
            {
                $oCase = new Cases ();
                $FieldsCase = $oCase->loadCase ( $caseUID );
                $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];  
                $oCase->updateCase($caseUID,$FieldsCase);
                autoDerivate($proUid,$caseUID,$USR_UID);
               
               
               /* Comment by Nico 28/08/2013
                 * Please, don't remove the comment because make some bug on process
                 * or explain to me why you want to put this value
                 *
                 */
               
               
            }
        }
        $totalCases++;
    }
    
    # create file tmp
    createFileTmpCSV($csv,$csv_file);   
    # end create file tmp 
    
    unset($_SESSION['REQ_DATA_CSV']);
    return $totalCases;
}

function importCreateCaseEdit($jsonMatchFields, $uidTask, $tableName, $firstLineHeader, $dataDeleteEdit, $updateOnly = 0) {
    G::LoadClass('case');
    $items = json_decode($jsonMatchFields, true);
    $dataCSV = isset($_SESSION['REQ_DATA_CSV']) ? $_SESSION['REQ_DATA_CSV'] : array();
    $USR_UID = $_SESSION['USER_LOGGED'];
    $_SESSION['USER_LOGGED_INI'] = $USR_UID;
    $proUid = getProUid($tableName);
    $totalCases = 0;
    $itemsDeleteEdit = json_decode($dataDeleteEdit, true);
    // TODO gerer le $updateOnly dans la creation du log
    $dataCSVdebug = createLog($dataCSV, $items, $tableName, $firstLineHeader, $itemsDeleteEdit);
    $dataCSV = $dataCSVdebug;
    // load Dynaforms of process
    $select = "SELECT DYN_UID, PRO_UID, DYN_TYPE, DYN_FILENAME FROM DYNAFORM WHERE PRO_UID = '" . $proUid . "'";
    $resultDynaform = executeQuery($select);
    $_dataForms = dataDynaforms($resultDynaform, $proUid);

    $select = executeQuery("SELECT MAX(IMPCSV_IDENTIFY) AS IDENTIFY FROM PMT_IMPORT_CSV_DATA WHERE IMPCSV_TABLE_NAME = '$tableName' ");
    $identify = isset($select[1]['IDENTIFY']) ? $select[1]['IDENTIFY'] : 0;
    $identify = $identify + 1;
    $csv_file = $tableName . "_" . $identify . ".csv";
    $csv_sep = ",";
    $csv = "";
    $csv_end = "\n";
    $swInsert = 0;
    if ( $updateOnly == 0 )
        $typeAction = 'ADD_UPDATE';
    else
        $typeAction = 'ONLY_UPDATE';

    //genDataReport($tableName);

    foreach ($dataCSV as $row)
    {
        $totRow = sizeof($row);
        $totIni = 1;
        if ($totalCases >= 10)
        {
            /* add header on csv temp files for import background */
            if ($firstLineHeader == 'on' && $swInsert == 0)
            {
                $csv .= getCSVHeader($dataCSV, $csv_sep) . $csv_end;
            }
            foreach ($row as $value)
            {
                if ($totIni == $totRow)
                    $csv.= _convert($value);
                else
                    $csv.= _convert($value) . $csv_sep;
                $totIni++;
            }
            $csv.=$csv_end;
            if ($swInsert == 0)
            {
                $select = executeQuery("SELECT MAX(IMPCSV_ID) AS ID_CSV FROM PMT_IMPORT_CSV_DATA");
                $maxId = isset($select[1]['ID_CSV']) ? $select[1]['ID_CSV'] : 0;
                $maxId = $maxId + 1;
                foreach ($items as $field)
                {
                    $insert = "INSERT INTO PMT_IMPORT_CSV_DATA
                               (IMPCSV_ID, IMPCSV_FIELD_NAME, IMPCSV_VALUE,IMPCSV_TAS_UID, IMPCSV_TABLE_NAME, IMPCSV_FIRSTLINEHEADER, IMPCSV_IDENTIFY, IMPCSV_TYPE_ACTION, IMPCSV_CONDITION_ACTION, IMPCSV_WHERE_ACTION)
                               VALUES
                               ('$maxId','" . $field['FIELD_NAME'] . "', '" . $field['COLUMN_CSV'] . "', '$uidTask', '$tableName','$firstLineHeader', '$identify', '$typeAction', '" . mysql_real_escape_string($dataDeleteEdit) . "', '" . mysql_escape_string(getSqlWhere($_REQUEST['idInbox'])) . "')";
                    executeQuery($insert);
                    $swInsert = 1;
                    $maxId++;
                }
            }
        }
        else
        {
            $appData = array();
            foreach ($items as $field)
            {
                if ($firstLineHeader == 'on')
                {
                    if (isset($row[$field['COLUMN_CSV']]))
                    {
                        if ($row[$field['COLUMN_CSV']] != '')
                            $appData[$field['FIELD_NAME']] = _convert($row[$field['COLUMN_CSV']]);
                        else
                            $appData[$field['FIELD_NAME']] = '';
                    }
                    else
                    {
                        if ($field['COLUMN_CSV'] != '')
                            $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                        else
                            $appData[$field['FIELD_NAME']] = '';
                    }
                }
                else
                {
                    $aCol = explode(' ', trim($field['COLUMN_CSV']));
                    if ((isset($aCol[0]) && trim($aCol[0]) == 'Column' ) && ( isset($aCol[1]) && isset($row[$aCol[1]]) ))
                        $appData[$field['FIELD_NAME']] = _convert($row[$aCol[1]]);
                    else if (( isset($aCol[0]) && trim($aCol[0]) != 'Column'))
                    {
                        $appData[$field['FIELD_NAME']] = _convert($field['COLUMN_CSV']);
                    }
                }
            }
            
            foreach ($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if ($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;

                    if ($key == $row['FIELD_NAME'])
                    {
                        $i = isset($fields) ? $fields : $row['FIELD_DEFAULT_VALUE'];

                        if (count($row['FIELD_SQL_OPTION']))
                        {
                            $options = $row['FIELD_SQL_OPTION'];
                            $id = "";
                            $label = "";
                            foreach ($options As $row2)
                            {
                                if ($row2['id'] == $i)
                                {
                                    $id = $row2['id'];
                                    $label = $row2['descrip'];
                                    break;
                                }
                            }

                            if ($id == "" && $label == "")
                            {
                                $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                            }

                            $record[$row['FIELD_NAME']] = $id;
                            $record[$row['FIELD_NAME'] . "_label"] = $label;
                            $appData = array_merge($record, $appData);
                        }
                        else
                        {
                            if (count($row['FIELD_OPTION']))
                            {
                                $options = $row['FIELD_OPTION'];
                                $id = "";
                                $label = "";
                                foreach ($options As $row2)
                                {
                                    if ($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }

                                $record = Array();
                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME'] . "_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                        }
                    }
                }
            }
            $whereUpdate = ''; 
            foreach ($appData As $key => $fields)
            {
                foreach ($_dataForms As $row)
                {
                    if ($row['FIELD_DEFAULT_VALUE'] == '')
                        $row['FIELD_DEFAULT_VALUE'] = 0;

                    if($row['FIELD_TYPE'] != 'yesno')
                    {
                        $appData[$row['FIELD_NAME'] . "_label"] = isset($appData[$row['FIELD_NAME'] . "_label"]) ? $appData[$row['FIELD_NAME'] . "_label"] : '';
                        ## Control label type field suggest
                        if($row['FIELD_TYPE'] == 'suggest')
                        {
                            $appData[$row['FIELD_NAME'] . "_label"] = isset($appData[$row['FIELD_NAME']]) ? $appData[$row['FIELD_NAME']] : '';
                        }
                        ## end Control label type field suggest
                        if ($appData[$row['FIELD_NAME'] . "_label"] == "")
                        {
                            $i = $row['FIELD_DEFAULT_VALUE'];
                            if (count($row['FIELD_SQL_OPTION']))
                            {
                                $options = $row['FIELD_SQL_OPTION'];
                                $id = "";
                                $label = "";
                                foreach ($options As $row2)
                                {
                                    if ($row2['id'] == $i)
                                    {
                                        $id = $row2['id'];
                                        $label = $row2['descrip'];
                                        break;
                                    }
                                }
    
                                if ($id == "" && $label == "")
                                {
                                    $id = $row['FIELD_SQL_OPTION'][0]['id'];
                                    $label = $row['FIELD_SQL_OPTION'][0]['descrip'];
                                }
                                $record[$row['FIELD_NAME']] = $id;
                                $record[$row['FIELD_NAME'] . "_label"] = $label;
                                $appData = array_merge($record, $appData);
                            }
                            else
                            {
                                if (count($row['FIELD_OPTION']))
                                {
                                    $options = $row['FIELD_OPTION'];
                                    $id = "";
                                    $label = "";
                                    foreach ($options As $row2)
                                    {
                                        if ($row2['id'] == $i)
                                        {
                                            $id = $row2['id'];
                                            $label = $row2['descrip'];
                                        }
                                    }
    
                                    if ($id == "" && $label == "")
                                    {
                                        $id = $row['FIELD_OPTION'][0]['id'];
                                        $label = $row['FIELD_OPTION'][0]['descrip'];
                                    }
                                    $record[$row['FIELD_NAME']] = $id;
                                    $record[$row['FIELD_NAME'] . "_label"] = $label;
                                    $appData = array_merge($record, $appData);
                                }
                            }
                        }
                    }
                    else 
                    {
                        $record = Array();
                        if( $appData[$row['FIELD_NAME']] == '' || $appData[$row['FIELD_NAME']] == ' ' )
                        {
                            $appData[$row['FIELD_NAME']] = $row['FIELD_DEFAULT_VALUE'];
                            //$appData = array_merge($record, $appData);
                        }
                    }
                    if($appData[$row['FIELD_NAME']] !='' && (isset($appData[$row['FIELD_NAME']."_label"]) && $appData[$row['FIELD_NAME']."_label"] ==''))
                    {
                        $appData[$row['FIELD_NAME']."_label"] = $label;
                    }
                }
                foreach ($itemsDeleteEdit as $field)
                {
                    $fieldNameEditDelete = htmlspecialchars_decode($field['CSV_FIELD_NAME']);
                    if ($fieldNameEditDelete == $key)
                    {
                        if ($whereUpdate == '')
                            $whereUpdate = $key . " = '" . mysql_escape_string($fields) . "'";
                        else
                            $whereUpdate = $whereUpdate . " AND " . $key . " = '" . mysql_escape_string($fields) . "'";
                    }
                }
            }

            // end labels
            // update cases
            $appDataNew = array();
            foreach ($appData as $key => $value)
            {
                foreach ($items as $row)
                {
                    if ($row['FIELD_NAME'] == $key || $row['FIELD_NAME'].'_label' == $key)
                    {
                        if (!is_array($value))
                        {
                            $appDataNew[$key] = $value;
                        }
                        else
                        {
                            $appDataNew[$key] = $value;
                        }
                    }
                }
                if (!is_array($value))
                {
                    $appData[$key] = $value;
                }
                else
                {
                    $appData[$key] = $value;
                }
            }
            $query = "SELECT APP_UID FROM $tableName WHERE $whereUpdate" . mysql_escape_string(getSqlWhere($_REQUEST['idInbox']));
            $updateData = executeQuery($query);
            if (sizeof($updateData))
            {
                $user = userInfo($USR_UID);
                foreach ($updateData as $index)
                {
                    $oCase = new Cases ();
                    $FieldsCase = $oCase->loadCase($index['APP_UID']);
                    $appDataNew['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
                    $appDataNew['FLAG_ACTION'] = 'multipleDerivation';
                    $appDataNew['EXEC_AUTO_DERIVATE'] = 'NO';
                    $appDataNew['eligible'] = 0; // only process beneficiary
                    $appDataNew['FLAG_EDIT'] = 1;
                    $appDataNew['FLAG_UPDATE'] = 1;
                    $appDataNew['CurrentUserAutoDerivate'] = $USR_UID;
                    $appDataNew['SW_CREATE_CASE'] = 1;
                    ## changes USER_LOGGED, USR_USERNAME
                    $appDataNew['USER_LOGGED'] = $USR_UID;
                    $appDataNew['USR_USERNAME'] = $user['username'];
                    ## end changes USER_LOGGED, USR_USERNAME
                    // needed to create cases. If a loop is generated when you run the trigger
                    $appDataNew = array_merge($FieldsCase['APP_DATA'], $appDataNew);
                    $FieldsCase['APP_DATA'] = $appDataNew;
                    $oCase->updateCase($index['APP_UID'], $FieldsCase);
                    frexecuteTriggers($proUid, $index['APP_UID'], $USR_UID);
                }
            }
            elseif ( $updateOnly == 0 )
            {
                $appData['VALIDATION'] = '0'; //needed for the process, if not you will have an error.
                $appData['FLAG_ACTION'] = 'multipleDerivation';
                $appData['EXEC_AUTO_DERIVATE'] = 'NO';
                $appData['eligible'] = 0; // only process beneficiary
                $appData['STATUT'] = 1;
                $appData['FLAG_EDIT'] = 1;
                $appData['SW_CREATE_CASE'] = 1; // needed to create cases. If a loop is generated when you run the trigger
                $appData['CurrentUserAutoDerivate'] = $USR_UID;
                $caseUID = PMFNewCase($proUid, $USR_UID, $uidTask, $appData);
                if ($caseUID > 0)
                {
                    $oCase = new Cases ();
                    $FieldsCase = $oCase->loadCase($caseUID);
                    $FieldsCase['APP_DATA']['NUM_DOSSIER'] = $FieldsCase['APP_NUMBER'];
                    $oCase->updateCase($caseUID, $FieldsCase);
                    autoDerivate($proUid, $caseUID, $USR_UID);
                }
            }
        }
        $totalCases++;
    }
    //genDataReport($tableName);
    # create file tmp
    createFileTmpCSV($csv, $csv_file);
    # end create file tmp
    unset($_SESSION['REQ_DATA_CSV']);
    return $totalCases;
}

function importCreateCaseTruncate($jsonMatchFields,$uidTask, $tableName,$firstLineHeader)
{
    // delete cases 
    //genDataReport($tableName);
    $query = "SELECT APP_UID FROM $tableName "; 
    $deleteData = executeQuery($query);
    if(sizeof($deleteData))
    {
        foreach($deleteData as $index)
        {   
            $CurDateTime=date('Y-m-d H:i:s');
            //insertHistoryLogPlugin($index['APP_UID'],$_SESSION['USER_LOGGED'],$CurDateTime,'1',$index['APP_UID'],'Delete Case');
            deletePMCases($index['APP_UID'],$tableName);           
            $dir = PATH_DOCUMENT . $index['APP_UID'];
            DeleteDir($dir);
           
        }
    }
    //genDataReport($tableName);
    // end delete cases 
    $typeAction = 'ADD';
    $totalCases = importCreateCase($jsonMatchFields,$uidTask,$tableName,$firstLineHeader,$typeAction);
    
    return $totalCases;
}

function DeleteDir($dir) {
    if (file_exists($dir))
    {
        foreach (glob($dir . "/*") as $files_dire)
        {
            if (is_dir($files_dire))
                DeleteDir($files_dire);
            else
                unlink($files_dire);
        }
        rmdir($dir);
    }
}

function saveFieldsCSV($idInbox, $fieldsImport, $firstLineHeader) {
    $items = json_decode($fieldsImport, true);
    $rolUser = getRolUserImport();
    $sSQL = "DELETE FROM PMT_CONFIG_CSV_IMPORT WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox'";
    executeQuery($sSQL);

    foreach ($items as $row) {
        $requireColumn = isset($row['REQUIRED_COLUMN']) ? $row['REQUIRED_COLUMN'] : '';
        $sSQL = "INSERT INTO PMT_CONFIG_CSV_IMPORT (CSV_FIELD_NAME, CSV_COLUMN, CSV_FIRST_LINE_HEADER, ROL_CODE, ID_INBOX, CSV_TYPE, CSV_PIVOT_EDIT, CSV_REQUIRED) VALUES(
            '" . $row['CSV_FIELD_NAME'] . "',
            '" . mysql_real_escape_string($row['CSV_COLUMN']) . "',
            '" . $firstLineHeader . "',
            '" . $rolUser . "',
            '" . $idInbox . "',
            '" . $row['TYPE_COLUMN'] . "',
            '" . $row['CSV_PIVOT_EDIT'] . "',
            '" . $requireColumn . "')";

        executeQuery($sSQL);
    }
    return true;
}

function resetFieldsCSV($idInbox) {
    $rolUser = getRolUserImport();
    $sSQL = "DELETE FROM PMT_CONFIG_CSV_IMPORT WHERE ROL_CODE  = '$rolUser' AND ID_INBOX = '$idInbox'";
    $aResult = executeQuery($sSQL);
    $bRes = '0';
    if (is_array($aResult) && count($aResult) > 0) {
        $bRes = '1';
    }
    return $bRes;
}

/*             By Nico 28/08/2013 fix Bug on the import Background by CRON with header csv files.
 *
 * Simple function to get and put the header on the csv temp file
 *
 */
function getCSVHeader($dataCSV, $csv_sep) {
    $key = array();
    $key = array_keys($dataCSV[0]);
    $key = array_map(utf8_encode, $key);
    $header = implode($csv_sep, $key);
    return $header;
}

## end actions import CSV
## function load parameters csv
function loadParametersCSV($idInbox, $pathCSV, $actionType, $fileNameCSV) {
    $directory = $pathCSV;
    chmodr($directory, 0777);
    chownr($directory, 'apache');
    chgrpr($directory, 'apache');
    $directoryFile = $pathCSV . "/" . $fileNameCSV;
    chmodr($directoryFile, 0777);
    chownr($directoryFile, 'apache');
    chgrpr($directoryFile, 'apache');
    G::loadClass('pmTable');
    G::loadClass('pmFunctions');
    G::LoadClass('case');

    // load rol user
    require_once ("classes/model/Users.php");
    # Variables
    $users = $_SESSION['USER_LOGGED'];
    $Us = new Users();
    $Roles = $Us->load($users);
    $rolesAdmin = $Roles['USR_ROLE'];
    // Uid retrieve current case
    $appUidCasOrig = $_SESSION['APPLICATION'];
    $oCase = new Cases();
    $fieldsCase = $oCase->loadCase($appUidCasOrig);
    $dataAnt = $fieldsCase['APP_DATA'];
    //G::pr($fieldsCase);
    $query1 = "SELECT ID_TABLE FROM PMT_INBOX_PARENT_TABLE WHERE ID_INBOX = '" . $idInbox . "' AND ROL_CODE = '" . $rolesAdmin . "' ";
    $result = executeQuery($query1);
    $tableName = $result[1]['ID_TABLE'];
    $fieldsCSV = isset($_REQUEST["fieldsCSV"]) ? $_REQUEST["fieldsCSV"] : "";
    list($dataNum, $data) = getDynaformFields($fieldsCSV, $tableName);
    $query2 = "SELECT * FROM  PMT_CONFIG_CSV_IMPORT WHERE ID_INBOX  = '" . $idInbox . "' ";
    $resultFields = executeQuery($query2);
    $firstLineHeader = $resultFields[1]['CSV_FIRST_LINE_HEADER'];
    $resultConfig = getConfigCSV($data, $idInbox, "");

    $fieldsCSV = array();
    $dataDeleteCSV = array();

    $query = "SELECT CSV_FIELD_NAME, CSV_COLUMN, CSV_REQUIRED, CSV_PIVOT_EDIT FROM PMT_CONFIG_CSV_IMPORT WHERE ID_INBOX = '" . $idInbox . "' AND ROL_CODE = '" . $rolesAdmin . "' ";
    $dataCsv = executeQuery($query);
    $dataImportCSV = array();
    foreach ($dataCsv as $index)
    {
        $record = array(
            "FIELD_NAME" => $index['CSV_FIELD_NAME'],
            "COLUMN_CSV" => $index['CSV_COLUMN'],
            "REQUIRED_COLUMN" => $index['CSV_REQUIRED']
        );
        $dataImportCSV[] = $record;
        // deleteEdit
        if ($index['CSV_PIVOT_EDIT'] == 1) {
            $record1 = array(
                "FIELD_NAME" => $index['CSV_FIELD_NAME'],
                "CSV_FIELD_NAME" => $index['CSV_FIELD_NAME'],
                "CSV_COLUMN" => $index['CSV_COLUMN'],
                "COLUMN_CSV" => $index['CSV_COLUMN'],
                "REQUIRED_COLUMN" => $index['CSV_REQUIRED'],
                "CSV_PIVOT_EDIT" => $index['CSV_PIVOT_EDIT']
            );
            $dataDeleteCSV[] = $record1;
        }
    }
    if (count($dataImportCSV))
    {
        $matchFields = json_encode($dataImportCSV);
        $dataDeleteEdit = json_encode($dataDeleteCSV);

        $fileCSV = $fileNameCSV;
        $filePath = $directory . "/" . $fileCSV;
        $uidTask = isset($_SESSION['TASK']) ? $_SESSION['TASK'] : "";

        // ************** TOT CASES  ****************+
        //$queryTot = executeQuery("SELECT IMPCSV_TOTCASES FROM wf_".$this->workspace.".PMT_IMPORT_CSV_DATA WHERE IMPCSV_IDENTIFY = '$csvIdentify' AND IMPCSV_TABLE_NAME = '$tableName'");
        //$totCasesCSV = $queryTot[1]['IMPCSV_TOTCASES'];
        $informationCSV = getDataCronCSV($firstLineHeader, $fileCSV, 0, $filePath);

        $_SESSION['REQ_DATA_CSV'] = $informationCSV;
        $_SESSION['CSV_FILE_NAME'] = $fileCSV;
        switch ($actionType)
        {
            case "add":
                $typeAction = 'ADD';
                $totalCases = importCreateCase($matchFields, $uidTask, $tableName, $firstLineHeader, $typeAction);
                echo G::json_encode(array("success" => true, "message" => "OK", "totalCases" => $totalCases));
                break;

            case "deleteAdd":
                $totalCases = importCreateCaseDelete($matchFields, $uidTask, $tableName, $firstLineHeader, $dataDeleteEdit);
                echo G::json_encode(array("success" => true, "message" => "OK", "totalCases" => $totalCases));
                break;

            case "editAdd":
                $totalCases = importCreateCaseEdit($matchFields, $uidTask, $tableName, $firstLineHeader, $dataDeleteEdit);
                echo G::json_encode(array("success" => true, "message" => "OK", "totalCases" => $totalCases));
                break;

            case "edit":
                $totalCases = importCreateCaseEdit($matchFields, $uidTask, $tableName, $firstLineHeader, $dataDeleteEdit, 1);
                echo G::json_encode(array( "success" => true, "message" => "OK", "totalCases" => $totalCases ));
                break;

            case "truncateAdd":
                $totalCases = importCreateCaseTruncate($matchFields, $uidTask, $tableName, $firstLineHeader);
                echo G::json_encode(array("success" => true, "message" => "OK", "totalCases" => $totalCases));
                break;
        }

        // case reset
        $_SESSION['APPLICATION'] = $appUidCasOrig;
        $oCase = new Cases ();
        $FieldsCase = $oCase->loadCase($appUidCasOrig);
        $FieldsCase['APP_DATA'] = $dataAnt;
        $oCase->updateCase($appUidCasOrig, $FieldsCase);
        $fieldsCase1 = $oCase->loadCase($appUidCasOrig);
        $dataNew = $fieldsCase1['APP_DATA'];
    }
    else
    {
        echo G::json_encode(array("success" => false,
            "message" => "Il devrait y avoir un cadre pour importer un fichier CSV",
            "totalCases" => 0));
    }
}

function getDataCronCSV($firstLineCsvAs = 'on', $fileCSV, $totCasesCSV, $pathCSV) {
    set_include_path(PATH_PLUGINS . 'convergenceList' . PATH_SEPARATOR . get_include_path());

    //PATH_DOCUMENT . "csvTmp/".$fileCSV."csv"
    if (!$handle = fopen($pathCSV, "r"))
    {
        echo "Cannot open file";
        exit;
    }

    $csvData = array();
    $csvDataIni = array();
    $i = 0;

    while ($data = fgetcsv($handle, 4096, ","))
    {
        /*     By Nico 28/08/2013 fix Bug on the import Background by CRON with header csv files.
         *
         * Add this part because when we import by cron a csv with header, all import are the header for value
         * So, after put the original header in the csv temp file in actionCSV.php,
         * we do this to work perfectly
         *
         */
        $col = 0;
        if ($firstLineCsvAs == 'on' && $i == 0)
        {
            foreach ($data as $row)
            {
                $column_csv[] = $row;
            }
        }
        else
        {
            $num = count($data);
            foreach ($data as $row)
            {
                /* $csvData key is the header for good import after */
                if ($firstLineCsvAs == 'on')
                {
                    if ($totCasesCSV <= $i)
                        $csvDataIni[$column_csv[$col]] = $row;

                    $col++;
                }
                else /* No header on csv files */
                {
                    if ($totCasesCSV <= $i)
                        $csvDataIni[] = $row;
                }
            }
            if ($totCasesCSV <= $i)
                $csvData[] = $csvDataIni;
            $csvDataIni = '';
        }
        $i++;
    }
    return $csvData;
}

function chmodr($path, $filemode) {
    if (!is_dir($path))
        return chmod($path, $filemode);
    $dh = opendir($path);
    while (($file = readdir($dh)) !== false)
    {
        if ($file != '.' && $file != '..')
        {
            $fullpath = $path . '/' . $file;
            if (is_link($fullpath))
                return FALSE;
            elseif (!is_dir($fullpath) && !chmod($fullpath, $filemode))
                return FALSE;
            elseif (!chmodr($fullpath, $filemode))
                return FALSE;
        }
    }
    closedir($dh);

    if (chmod($path, $filemode))
        return TRUE;
    else
        return FALSE;
}

function chownr($path, $owner) {
    if (!is_dir($path))
        return chown($path, $owner);
    $dh = opendir($path);
    while (($file = readdir($dh)) !== false)
    {
        if ($file != '.' && $file != '..')
        {
            $fullpath = $path . '/' . $file;
            if (is_link($fullpath))
                return FALSE;
            elseif (!is_dir($fullpath) && !chown($fullpath, $owner))
                return FALSE;
            elseif (!chownr($fullpath, $owner))
                return FALSE;
        }
    }
    closedir($dh);

    if (chown($path, $owner))
        return TRUE;
    else
        return FALSE;
}

function chgrpr($path, $group) {
    if (!is_dir($path))
        return chgrp($path, $group);
    $dh = opendir($path);
    while (($file = readdir($dh)) !== false)
    {
        if ($file != '.' && $file != '..')
        {
            $fullpath = $path . '/' . $file;
            if (is_link($fullpath))
                return FALSE;
            elseif (!is_dir($fullpath) && !chgrp($fullpath, $group))
                return FALSE;
            elseif (!chgrpr($fullpath, $group))
                return FALSE;
        }
    }
    closedir($dh);

    if (chgrp($path, $group))
        return TRUE;
    else
        return FALSE;
}

/**
 * Permet de récupérer les dates des semaines précedant une date
 *
 * $MoinsNSemaine       @int        Nombre de semaine avant la date, 0 pour la semaine courante
 * $dateReferente       @string     Date à partir de laquelle on effectue la recherche
 *
 * $dateSemaine         @array      Contient les dates de début et fin de semaine voulue
 */
function convergence_getDateLastWeek($MoinsNSemaine = 0, $dateReferente = NULL) {

    // INIT
    (!$dateReferente ? $dateReferente = date('d-m-Y') : $dateReferente = date('d-m-Y', strtotime($dateReferente)));
    //dans le format anglais les semaine commence par Dimanche donc si le jour de ref est un dimanche, pose problème
    $date = explode('-', $dateReferente);
    if ( date('w', mktime(0, 0, 0, $date[1], $date[0], $date[2])) == 0 )
        $dateReferente = date('d-m-Y', mktime(0, 0, 0, $date[1], $date[0] - 1, $date[2]));
    $n = ( string ) $MoinsNSemaine;

    $lundi = date('d-m-Y', strtotime('monday this week' . $dateReferente));
    $dimanche = date('d-m-Y', strtotime('sunday this week' . $dateReferente));
    $dateSemaine = array(
        'Lundi' => date('d-m-Y', strtotime('Monday -' . $n . ' week ' . $lundi)),
        'Dimanche' => date('d-m-Y', strtotime('Sunday -' . $n . ' week ' . $dimanche)) );

    // RETURN
    return $dateSemaine;
}

/**
 * Permet de récupérer les dates des n semaines précedant une date
 *
 * $NSemaine        @int        Nombre de semaine
 * $dateReferente   @string     Date à partir de laquelle on effectue la recherche
 *
 * $dateSemaine     @array      Contient les dates de début et fin de période voulue
 */
function convergence_getDateXLastWeek($NSemaine = 0, $dateReferente = NULL) {

    // INIT
    (!$dateReferente ? $dateReferente = date('d-m-Y') : $dateReferente = date('d-m-Y', strtotime($dateReferente)));
    //dans le format anglais les semaines commence par Dimanche donc si le jour de ref est un dimanche, pose problème, on prend le samedi précédent.
    $date = explode('-', $dateReferente);
    if ( date('w', mktime(0, 0, 0, $date[1], $date[0], $date[2])) == 0 )
        $dateReferente = date('d-m-Y', mktime(0, 0, 0, $date[1], $date[0] - 1, $date[2]));
    $n = ( string ) $NSemaine;

    $lundi = date('d-m-Y', strtotime('monday this week' . $dateReferente));
    $dimanche = date('d-m-Y', strtotime('sunday this week' . $dateReferente));
    $dateSemaine = array(
        'Lundi' => date('d-m-Y', strtotime('Monday -' . $n . ' week ' . $lundi)),
        'Dimanche' => date('d-m-Y', strtotime('Sunday -1 week ' . $dimanche)) );

    // RETURN
    return $dateSemaine;
}

// TODO a tester
function convergence_getQuinzaine($dateReferente = NULL) {

    // INIT
    $periode = array( );
    (!$dateReferente ? $dateReferente = date('d-m-Y') : $dateReferente = date('d-m-Y', strtotime($dateReferente)));
    //calcule de la quinzaine voulu en fonction de $dateReferente
    $date = explode('-', $dateReferente);
    $periode['date'] = $date;
    // Attention, tester janvier / decembre
    if ( $date[0] < 16 )
    { // Seconde quinzaine
        $jourSeize = mktime(0, 0, 0, $date[1] - 1, 16, $date[2]);
        $nbJoursDansMois = date("t", $jourSeize);
        $periode['premierJour'] = date("d-m-Y", $jourSeize);
        $periode['dernierJour'] = date("d-m-Y", mktime(0, 0, 0, $date[1] - 1, $nbJoursDansMois, $date[2]));
    }
    else
    { // Première quinzaine
        $periode['premierJour'] = date("d-m-Y", mktime(0, 0, 0, $date[1], 1, $date[2]));
        $periode['dernierJour'] = date("d-m-Y", mktime(0, 0, 0, $date[1], 15, $date[2]));
    }


    return $periode;
}

function convergence_getDateCreateDemande($num_dossier) {
    $query = "SELECT DATE_FORMAT(APP_CREATE_DATE, '%d/%m/%Y') AS APP_CREATE_DATE
                FROM APPLICATION                    
                WHERE  APP_NUMBER = '$num_dossier'";
    $res = executeQuery($query);
    $date = $res[1]['APP_CREATE_DATE'];
    return $date;
}
function convergence_getListeOperation() {
    $sqlOper = 'SELECT DISTINCT(NUM_OPER) FROM PMT_LISTE_OPER';
    $resOper = executeQuery($sqlOper);
    $listOper = array( );
    if ( !empty($resOper) )
    {
        foreach ( $resOper as $operation )
        {
            $listOper[] = intval($operation['NUM_OPER']);
        }
    }
    return $listOper;
}

function convergence_editDateCompta($appuidDate, $appuidEdit, $dateField, $newStatut = null) {
    //je récupère mes valeurs courantes
    $datas = convergence_getAllAppData($appuidDate);

    //on modifie le case de la demande
    $oCase = new Cases ();
    $Fields = $oCase->loadCase($appuidEdit);
    $Fields['APP_DATA'][$dateField] = $datas['DATE_EDITE'];
    if ( !empty($newStatut) && isset($Fields['APP_DATA']['STATUT']) )
        $Fields['APP_DATA']['STATUT'] = $newStatut;

    $oCase->updateCase($appuidEdit, $Fields);
}

function convergence_updateLotVirementCompta($app_uid, $date, $directory) {
    //INIT
    // On récupère les informations
    $query = 'SELECT * FROM  PMT_LOT_VIREMENT WHERE APP_UID = "' . $app_uid . '"';
    $result = executeQuery($query);
    $q = 'SELECT * FROM  PMT_LOT_VIREMENT_OPER WHERE NUM_DOSSIER = "' . $result[1]['NUM_DOSSIER'] . '"';
    $r = executeQuery($q);

    // Mettre à jour la date et le Statut
    $qUpdate = 'UPDATE PMT_LOT_VIREMENT_OPER SET STATUT = "18", DATE_REMBOURSEMENT = "' . $date . '" WHERE UID ="' . $r[1]['UID'] . '"';
    $rUpdate = executeQuery($qUpdate);

    // Déposer le fichier de Lot de virement sur l'AS400
    if ( !empty($r['PATHFILE_LOT_QZ']) )
    {
        $put = convergence_putFileByFtp($r['PATHFILE_LOT_QZ'], $directory);
        if ( $put )
            return 2; // Aucune erreur
        else
            return 1; // Erreur lors du dépôt
    }
    return 0; // Pas de fichier
}

######### GLOBAL : function validate browser #########
function browser_detection_Convergence($which_test, $test_excludes = '', $external_ua_string = '')
{
    G::script_time(); // set script timer to start timing

    static $a_full_assoc_data, $a_mobile_data, $a_moz_data, $a_webkit_data, $b_dom_browser, $b_repeat, $b_safe_browser, $browser_name, $browser_number, $browser_math_number, $browser_user_agent, $browser_working, $ie_version, $mobile_test, $moz_number, $moz_rv, $moz_rv_full, $moz_release_date, $moz_type, $os_number, $os_type, $true_ie_number, $ua_type, $webkit_type, $webkit_type_number;

    // switch off the optimization for external ua string testing.
    if ( $external_ua_string ) {
        $b_repeat = false;
    }

    /*
    this makes the test only run once no matter how many times you call it since
    all the variables are filled on the first run through, it's only a matter of
    returning the the right ones
    */
    if ( !$b_repeat ) {
        //initialize all variables with default values to prevent error
        $a_browser_math_number = '';
        $a_full_assoc_data = '';
        $a_full_data = '';
        $a_mobile_data = '';
        $a_moz_data = '';
        $a_os_data = '';
        $a_unhandled_browser = '';
        $a_webkit_data = '';
        $b_dom_browser = false;
        $b_os_test = true;
        $b_mobile_test = true;
        $b_safe_browser = false;
        $b_success = false;// boolean for if browser found in main test
        $browser_math_number = '';
        $browser_temp = '';
        $browser_working = '';
        $browser_number = '';
        $ie_version = '';
        $mobile_test = '';
        $moz_release_date = '';
        $moz_rv = '';
        $moz_rv_full = '';
        $moz_type = '';
        $moz_number = '';
        $os_number = '';
        $os_type = '';
        $run_time = '';
        $true_ie_number = '';
        $ua_type = 'bot';// default to bot since you never know with bots
        $webkit_type = '';
        $webkit_type_number = '';

        // set the excludes if required
        if ( $test_excludes ) {
            switch ( $test_excludes ){
                case '1':
                    $b_os_test = false;
                    break;
                case '2':
                    $b_mobile_test = false;
                    break;
                case '3':
                    $b_os_test = false;
                    $b_mobile_test = false;
                    break;
                default:
                    die( 'Error: bad $test_excludes parameter 2 used: ' . $test_excludes );
                    break;
            }
        }

        /*
        make navigator user agent string lower case to make sure all versions get caught
        isset protects against blank user agent failure. tolower also lets the script use
        strstr instead of stristr, which drops overhead slightly.
        */
        if ( $external_ua_string ) {
            $browser_user_agent = strtolower( $external_ua_string );
        } elseif ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
            $browser_user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
        } else {
            $browser_user_agent = '';
        }

        // known browsers, list will be updated routinely, check back now and then
        $a_browser_types = array(
            array( 'opera', true, 'op', 'bro' ),
            array( 'msie', true, 'ie', 'bro' ),
            // webkit before gecko because some webkit ua strings say: like gecko
            array( 'webkit', true, 'webkit', 'bro' ),
            // konq will be using webkit soon
            array( 'konqueror', true, 'konq', 'bro' ),
            // covers Netscape 6-7, K-Meleon, Most linux versions, uses moz array below
            array( 'gecko', true, 'moz', 'bro' ),
            array( 'netpositive', false, 'netp', 'bbro' ),// beos browser
            array( 'lynx', false, 'lynx', 'bbro' ), // command line browser
            array( 'elinks ', false, 'elinks', 'bbro' ), // new version of links
            array( 'elinks', false, 'elinks', 'bbro' ), // alternate id for it
            array( 'links2', false, 'links2', 'bbro' ), // alternate links version
            array( 'links ', false, 'links', 'bbro' ), // old name for links
            array( 'links', false, 'links', 'bbro' ), // alternate id for it
            array( 'w3m', false, 'w3m', 'bbro' ), // open source browser, more features than lynx/links
            array( 'webtv', false, 'webtv', 'bbro' ),// junk ms webtv
            array( 'amaya', false, 'amaya', 'bbro' ),// w3c browser
            array( 'dillo', false, 'dillo', 'bbro' ),// linux browser, basic table support
            array( 'ibrowse', false, 'ibrowse', 'bbro' ),// amiga browser
            array( 'icab', false, 'icab', 'bro' ),// mac browser
            array( 'crazy browser', true, 'ie', 'bro' ),// uses ie rendering engine

            // search engine spider bots:
            array( 'bingbot', false, 'bing', 'bot' ),// bing
            array( 'exabot', false, 'exabot', 'bot' ),// exabot
            array( 'googlebot', false, 'google', 'bot' ),// google
            array( 'google web preview', false, 'googlewp', 'bot' ),// google preview
            array( 'mediapartners-google', false, 'adsense', 'bot' ),// google adsense
            array( 'yahoo-verticalcrawler', false, 'yahoo', 'bot' ),// old yahoo bot
            array( 'yahoo! slurp', false, 'yahoo', 'bot' ), // new yahoo bot
            array( 'yahoo-mm', false, 'yahoomm', 'bot' ), // gets Yahoo-MMCrawler and Yahoo-MMAudVid bots
            array( 'inktomi', false, 'inktomi', 'bot' ), // inktomi bot
            array( 'slurp', false, 'inktomi', 'bot' ), // inktomi bot
            array( 'fast-webcrawler', false, 'fast', 'bot' ),// Fast AllTheWeb
            array( 'msnbot', false, 'msn', 'bot' ),// msn search
            array( 'ask jeeves', false, 'ask', 'bot' ), //jeeves/teoma
            array( 'teoma', false, 'ask', 'bot' ),//jeeves teoma
            array( 'scooter', false, 'scooter', 'bot' ),// altavista
            array( 'openbot', false, 'openbot', 'bot' ),// openbot, from taiwan
            array( 'ia_archiver', false, 'ia_archiver', 'bot' ),// ia archiver
            array( 'zyborg', false, 'looksmart', 'bot' ),// looksmart
            array( 'almaden', false, 'ibm', 'bot' ),// ibm almaden web crawler
            array( 'baiduspider', false, 'baidu', 'bot' ),// Baiduspider asian search spider
            array( 'psbot', false, 'psbot', 'bot' ),// psbot image crawler
            array( 'gigabot', false, 'gigabot', 'bot' ),// gigabot crawler
            array( 'naverbot', false, 'naverbot', 'bot' ),// naverbot crawler, bad bot, block
            array( 'surveybot', false, 'surveybot', 'bot' ),//
            array( 'boitho.com-dc', false, 'boitho', 'bot' ),//norwegian search engine
            array( 'objectssearch', false, 'objectsearch', 'bot' ),// open source search engine
            array( 'answerbus', false, 'answerbus', 'bot' ),// http://www.answerbus.com/, web questions
            array( 'sohu-search', false, 'sohu', 'bot' ),// chinese media company, search component
            array( 'iltrovatore-setaccio', false, 'il-set', 'bot' ),

            // various http utility libaries
            array( 'w3c_validator', false, 'w3c', 'lib' ), // uses libperl, make first
            array( 'wdg_validator', false, 'wdg', 'lib' ), //
            array( 'libwww-perl', false, 'libwww-perl', 'lib' ),
            array( 'jakarta commons-httpclient', false, 'jakarta', 'lib' ),
            array( 'python-urllib', false, 'python-urllib', 'lib' ),
            // download apps
            array( 'getright', false, 'getright', 'dow' ),
            array( 'wget', false, 'wget', 'dow' ),// open source downloader, obeys robots.txt
            // netscape 4 and earlier tests, put last so spiders don't get caught
            array( 'mozilla/4.', false, 'ns', 'bbro' ),
            array( 'mozilla/3.', false, 'ns', 'bbro' ),
            array( 'mozilla/2.', false, 'ns', 'bbro' )
        );

        //array( '', false ); // browser array template

        /*
        moz types array
        note the order, netscape6 must come before netscape, which  is how netscape 7 id's itself.
        rv comes last in case it is plain old mozilla. firefox/netscape/seamonkey need to be later
        Thanks to: http://www.zytrax.com/tech/web/firefox-history.html
        */
        $a_moz_types = array( 'bonecho', 'camino', 'epiphany', 'firebird', 'flock', 'galeon', 'iceape', 'icecat', 'k-meleon', 'minimo', 'multizilla', 'phoenix', 'songbird', 'swiftfox', 'seamonkey', 'shiretoko', 'iceweasel', 'firefox', 'minefield', 'netscape6', 'netscape', 'rv' );

        /*
        webkit types, this is going to expand over time as webkit browsers spread
        konqueror is probably going to move to webkit, so this is preparing for that
        It will now default to khtml. gtklauncher is the temp id for epiphany, might
        change. Defaults to applewebkit, and will all show the webkit number.
        */
        $a_webkit_types = array( 'arora', 'chrome', 'epiphany', 'gtklauncher', 'konqueror', 'midori', 'omniweb', 'safari', 'uzbl', 'applewebkit', 'webkit' );

        /*
        run through the browser_types array, break if you hit a match, if no match, assume old browser
        or non dom browser, assigns false value to $b_success.
        */
        $i_count = count( $a_browser_types );
        for ($i = 0; $i < $i_count; $i++) {
            //unpacks browser array, assigns to variables, need to not assign til found in string
            $browser_temp = $a_browser_types[$i][0];// text string to id browser from array

            if ( strstr( $browser_user_agent, $browser_temp ) ) {
                /*
                it defaults to true, will become false below if needed
                this keeps it easier to keep track of what is safe, only
                explicit false assignment will make it false.
                */
                $b_safe_browser = true;
                $browser_name = $browser_temp;// text string to id browser from array

                // assign values based on match of user agent string
                $b_dom_browser = $a_browser_types[$i][1];// hardcoded dom support from array
                $browser_working = $a_browser_types[$i][2];// working name for browser
                $ua_type = $a_browser_types[$i][3];// sets whether bot or browser

                switch ( $browser_working ) {
                    // this is modified quite a bit, now will return proper netscape version number
                    // check your implementation to make sure it works
                    case 'ns':
                        $b_safe_browser = false;
                        $browser_number = G::get_item_version( $browser_user_agent, 'mozilla' );
                        break;
                    case 'moz':
                        /*
                        note: The 'rv' test is not absolute since the rv number is very different on
                        different versions, for example Galean doesn't use the same rv version as Mozilla,
                        neither do later Netscapes, like 7.x. For more on this, read the full mozilla
                        numbering conventions here: http://www.mozilla.org/releases/cvstags.html
                        */
                        // this will return alpha and beta version numbers, if present
                        $moz_rv_full = G::get_item_version( $browser_user_agent, 'rv' );
                        // this slices them back off for math comparisons
                        $moz_rv = substr( $moz_rv_full, 0, 3 );

                        // this is to pull out specific mozilla versions, firebird, netscape etc..
                        $j_count = count( $a_moz_types );
                        for ($j = 0; $j < $j_count; $j++) {
                            if ( strstr( $browser_user_agent, $a_moz_types[$j] ) ) {
                                $moz_type = $a_moz_types[$j];
                                $moz_number = G::get_item_version( $browser_user_agent, $moz_type );
                                break;
                            }
                        }
                        /*
                        this is necesary to protect against false id'ed moz'es and new moz'es.
                        this corrects for galeon, or any other moz browser without an rv number
                        */
                        if ( !$moz_rv ) {
                            // you can use this if you are running php >= 4.2
                            if ( function_exists( 'floatval' ) ) {
                                $moz_rv = floatval( $moz_number );
                            } else {
                                $moz_rv = substr( $moz_number, 0, 3 );
                            }
                            $moz_rv_full = $moz_number;
                        }
                        // this corrects the version name in case it went to the default 'rv' for the test
                        if ( $moz_type == 'rv' ) {
                            $moz_type = 'mozilla';
                        }

                        //the moz version will be taken from the rv number, see notes above for rv problems
                        $browser_number = $moz_rv;
                        // gets the actual release date, necessary if you need to do functionality tests
                        G::get_set_count( 'set', 0 );
                        $moz_release_date = G::get_item_version( $browser_user_agent, 'gecko/' );
                        /*
                        Test for mozilla 0.9.x / netscape 6.x
                        test your javascript/CSS to see if it works in these mozilla releases, if it
                        does, just default it to: $b_safe_browser = true;
                        */
                        if ( ( $moz_release_date < 20020400 ) || ( $moz_rv < 1 ) ) {
                            $b_safe_browser = false;
                        }
                        break;
                    case 'ie':
                        /*
                        note we're adding in the trident/ search to return only first instance in case
                        of msie 8, and we're triggering the  break last condition in the test, as well
                        as the test for a second search string, trident/
                        */
                        $browser_number = G::get_item_version( $browser_user_agent, $browser_name, true, 'trident/' );
                        // construct the proper real number if it's in compat mode and msie 8.0/9.0
                        if ( strstr( $browser_number, '7.' ) && strstr( $browser_user_agent, 'trident/5' ) ) {
                            // note that 7.0 becomes 9 when adding 1, but if it's 7.1 it will be 9.1
                            $true_ie_number = $browser_number + 2;
                        } elseif ( strstr( $browser_number, '7.' ) && strstr( $browser_user_agent, 'trident/4' ) ) {
                            // note that 7.0 becomes 8 when adding 1, but if it's 7.1 it will be 8.1
                            $true_ie_number = $browser_number + 1;
                        }
                        // the 9 series is finally standards compatible, html 5 etc, so worth a new id
                        if ( $browser_number >= 9 ) {
                            $ie_version = 'ie9x';
                        } elseif ( $browser_number >= 7 ) {
                            $ie_version = 'ie7x';
                        } elseif ( strstr( $browser_user_agent, 'mac') ) {
                            $ie_version = 'ieMac';
                        } elseif ( $browser_number >= 5 ) {
                            $ie_version = 'ie5x';
                        } elseif ( ( $browser_number > 3 ) && ( $browser_number < 5 ) ) {
                            $b_dom_browser = false;
                            $ie_version = 'ie4';
                            // this depends on what you're using the script for, make sure this fits your needs
                            $b_safe_browser = true;
                        } else {
                            $ie_version = 'old';
                            $b_dom_browser = false;
                            $b_safe_browser = false;
                        }
                        break;
                    case 'op':
                        $browser_number = G::get_item_version( $browser_user_agent, $browser_name );
                        // opera is leaving version at 9.80 (or xx) for 10.x - see this for explanation
                        // http://dev.opera.com/articles/view/opera-ua-string-changes/
                        if ( strstr( $browser_number, '9.' ) && strstr( $browser_user_agent, 'version/' ) ) {
                            G::get_set_count( 'set', 0 );
                            $browser_number = G::get_item_version( $browser_user_agent, 'version/' );
                        }

                        if ( $browser_number < 5 ) {
                            $b_safe_browser = false;
                        }
                        break;
                    case 'webkit':
                        // note that this is the Webkit version number
                        $browser_number = G::get_item_version( $browser_user_agent, $browser_name );
                        // this is to pull out specific webkit versions, safari, google-chrome etc..
                        $j_count = count( $a_webkit_types );
                        for ($j = 0; $j < $j_count; $j++) {
                            if (strstr( $browser_user_agent, $a_webkit_types[$j])) {
                                $webkit_type = $a_webkit_types[$j];
                                if ( $webkit_type == 'omniweb' ) {
                                    G::get_set_count( 'set', 2 );
                                }
                                $webkit_type_number = G::get_item_version( $browser_user_agent, $webkit_type );
                                // epiphany hack
                                if ( $a_webkit_types[$j] == 'gtklauncher' ) {
                                    $browser_name = 'epiphany';
                                } else {
                                    $browser_name = $a_webkit_types[$j];
                                }
                                break;
                            }
                        }
                        break;
                    default:
                        $browser_number = G::get_item_version( $browser_user_agent, $browser_name );
                        break;
                }
                // the browser was id'ed
                $b_success = true;
                break;
            }
        }

        //assigns defaults if the browser was not found in the loop test
        if ( !$b_success ) {
            /*
            this will return the first part of the browser string if the above id's failed
            usually the first part of the browser string has the navigator useragent name/version in it.
            This will usually correctly id the browser and the browser number if it didn't get
            caught by the above routine.
            If you want a '' to do a if browser == '' type test, just comment out all lines below
            except for the last line, and uncomment the last line. If you want undefined values,
            the browser_name is '', you can always test for that
            */
            // delete this part if you want an unknown browser returned
            $browser_name = substr( $browser_user_agent, 0, strcspn( $browser_user_agent , '();') );
            // this extracts just the browser name from the string, if something usable was found
            if ( $browser_name && preg_match( '/[^0-9][a-z]*-*\ *[a-z]*\ *[a-z]*/', $browser_name, $a_unhandled_browser ) ) {
                $browser_name = $a_unhandled_browser[0];
                if ( $browser_name == 'blackberry' ) {
                    G::get_set_count( 'set', 0 );
                }
                $browser_number = G::get_item_version( $browser_user_agent, $browser_name );
            } else {
                $browser_name = 'NA';
                $browser_number = 'NA';
            }
        }
        // get os data, mac os x test requires browser/version information, this is a change from older scripts
        if ($b_os_test) {
            $a_os_data = G::get_os_data( $browser_user_agent, $browser_working, $browser_number );
            $os_type = $a_os_data[0];// os name, abbreviated
            $os_number = $a_os_data[1];// os number or version if available
        }
        /*
        this ends the run through once if clause, set the boolean
        to true so the function won't retest everything
        */
        $b_repeat = true;
        if ($browser_number && preg_match( '/[0-9]*\.*[0-9]*/', $browser_number, $a_browser_math_number ) ) {
            $browser_math_number = $a_browser_math_number[0];
        }
        if ( $b_mobile_test ) {
            $mobile_test = G::check_is_mobile( $browser_user_agent );
            if ( $mobile_test ) {
                $a_mobile_data = G::get_mobile_data( $browser_user_agent );
                $ua_type = 'mobile';
            }
        }
    }

    switch ($which_test) {
        case 'math_number':
            $which_test = 'browser_math_number';
            break;
        case 'number':
            $which_test = 'browser_number';
            break;
        case 'browser':
            $which_test = 'browser_working';
            break;
        case 'moz_version':
            $which_test = 'moz_data';
            break;
        case 'true_msie_version':
            $which_test = 'true_ie_number';
            break;
        case 'type':
            $which_test = 'ua_type';
            break;
        case 'webkit_version':
            $which_test = 'webkit_data';
            break;
    }
    /*
    assemble these first so they can be included in full return data, using static variables
    Note that there's no need to keep repacking these every time the script is called
    */
    if (!$a_moz_data) {
        $a_moz_data = array( $moz_type, $moz_number, $moz_rv, $moz_rv_full, $moz_release_date );
    }
    if (!$a_webkit_data) {
        $a_webkit_data = array( $webkit_type, $webkit_type_number, $browser_number );
    }
    $run_time = G::script_time();

    if ( !$a_full_assoc_data ) {
        $a_full_assoc_data = array(
            'browser_working' => $browser_working,
            'browser_number' => $browser_number,
            'ie_version' => $ie_version,
            'dom' => $b_dom_browser,
            'safe' => $b_safe_browser,
            'os' => $os_type,
            'os_number' => $os_number,
            'browser_name' => $browser_name,
            'ua_type' => $ua_type,
            'browser_math_number' => $browser_math_number,
            'moz_data' => $a_moz_data,
            'webkit_data' => $a_webkit_data,
            'mobile_test' => $mobile_test,
            'mobile_data' => $a_mobile_data,
            'true_ie_number' => $true_ie_number,
            'run_time' => $run_time
        );
    }

    // return parameters, either full data arrays, or by associative array index key
    switch ($which_test) {
        // returns all relevant browser information in an array with standard numberic indexes
        case 'full':
            $a_full_data = array(
                $browser_working,
                $browser_number,
                $ie_version,
                $b_dom_browser,
                $b_safe_browser,
                $os_type,
                $os_number,
                $browser_name,
                $ua_type,
                $browser_math_number,
                $a_moz_data,
                $a_webkit_data,
                $mobile_test,
                $a_mobile_data,
                $true_ie_number,
                $run_time
            );
            return $a_full_data;
            break;
        case 'full_assoc':
            return $a_full_assoc_data;
            break;
        default:
            # check to see if the data is available, otherwise it's user typo of unsupported option
            if (isset( $a_full_assoc_data[$which_test])) {
                return $a_full_assoc_data[$which_test];
            } else {
                die( "You passed the browser detector an unsupported option for parameter 1: " . $which_test );
            }
            break;
    }
}
################# VALIDATE BROWSER DETECTION ####################
function validate_BrowserNavigation($testBrowser){

    $browser = browser_detection_Convergence($testBrowser);
    switch($browser['browser_working'])
    {
        case 'moz':
            $browserVersion = explode('.',$browser['browser_number']);
            if($browserVersion[0] < 13)
            {
                echo "Vous disposez d'une version:".$browser['browser_number']." . Ce site est optimis&#233; pour Firefox 13";
                die;
            }
        break;
        
        case 'webkit':   //chrome, safari, opera 
        
            if($browser['browser_name'] == 'safari')
            {
                echo "Ce site ne supporte pas le navigateur Safari, Ce site est optimis&#233; pour Firefox 13, Internet Explorer 8 et Chrome 18";
                die;
            }
            else
            {
                $browserVersion = explode ('.',$browser['webkit_data'][1]);
                
                if($browserVersion[0] < 18)
                {
                    echo "Vous disposez d'une version: ".$browser['webkit_data']." . Ce site est optimis&#233; pour Chrome 18";
                    die;
                }
            }
        break;
        
        case 'ie':
            
            if($browser['browser_name'] == 'msie')
            {
                $browserVersion = explode('.',$browser['browser_number']);
                
                if($browserVersion[0] < 8)
                {
                    echo "Vous disposez d'une version: ".$browser['browser_number']." . Ce site est optimis&#233; pour Internet Explorer 8";
                    die;
                }
            }   
        break;
        
        default:
                echo "Le navigateur que vous essayez d'entrer est inconnue essayer un autre navigateur ";
                die;
        break;
    }
}
################# VALIDATE ROLE CONVERGENCE ENDPOINT ####################
function conv_validateRole($userName){
    
    // Gets logged user data to have role PROCESSMAKER_OPERATOR
    $queryUser = "SELECT * FROM wf_" . SYS_SYS . ".USERS WHERE USR_USERNAME = '" . $userName . "' AND USR_ROLE = 'PROCESSMAKER_OPERATOR' ";
    $appQueryUser = executeQuery($queryUser);
    
    // if User has logged PROCESSMAKER_OPERATOR role
    if(count($appQueryUser)){
                
        G::LoadClass('pmFunctions');
        $RBAC = RBAC::getSingleton() ;
        $RBAC->initRBAC();
            
        // Id of the user logged
        $sUserUID=$appQueryUser[1]['USR_UID'];
        // Rol para setear roles PROCESSMAKER_OPERATOR
        $rolUser = 'BÃ©nÃ©ficiaires';
        
        ######################### DELETE USER BENEFICIAIRE ##########################
        // User Id to delete because they have no case
        $queryUserDelete = "SELECT * FROM wf_" . SYS_SYS . ".USERS WHERE USR_USERNAME = '" . $userName . "' AND USR_ROLE = '". mysql_real_escape_string($rolUser)."' ";
        $appQueryUserDelete = executeQuery($queryUserDelete);
        
        // Check and eliminate duplicate user if
        if(count($appQueryUserDelete) > 0)
        {
            // User Id to delete because they have no case
            $userUidDelete = $appQueryUserDelete[1]['USR_UID'];
            
            // Assign tasks to the user log
            G::LoadClass('tasks');
            $oTasks = new Tasks();
            $oTasks->ofToAssignUserOfAllTasks($userUidDelete);
            
            // Remove the user with the same username groups
            G::LoadClass('groups');
            $oGroups = new Groups();
            $oGroups->removeUserOfAllGroups($userUidDelete);
            
            // Change status user same username
            $RBAC->changeUserStatus($userUidDelete, 'CLOSED');
            $_GET['USR_USERNAME'] = '';
            
            // Modify the user to remove
            $RBAC->updateUser(array(
                'USR_UID' => $userUidDelete,
                'USR_USERNAME' => $_GET['USR_USERNAME']
            ), '');
            require_once 'classes/model/Users.php';
            $oUser                   = new Users();
            $aFields                 = $oUser->load($userUidDelete);
            $aFields['USR_STATUS']   = 'CLOSED';
            $aFields['USR_USERNAME'] = '';
            $oUser->update($aFields);
        }
        ######################### UPDATE USER WITH ROLE PROCESSMAKER_OPERATOR ###########################
        // Assign the data
        $aData['USR_UID'] = $sUserUID;
        $aData['USR_USERNAME'] = $appQueryUser[1]['USR_USERNAME'];
        $aData['USR_FIRSTNAME']   = $appQueryUser[1]['USR_FIRSTNAME'];
        $aData['USR_LASTNAME']    = $appQueryUser[1]['USR_LASTNAME'];
        $aData['USR_EMAIL']       = $appQueryUser[1]['USR_EMAIL'];
        $aData['USR_DUE_DATE']    = $appQueryUser[1]['USR_DUE_DATE'];
        $aData['USR_UPDATE_DATE'] = date('Y-m-d H:i:s');
        $aData['USR_STATUS'] = $appQueryUser[1]['USR_STATUS'];
        
        // Get the group name
        $query = "SELECT * FROM GROUP_USER WHERE USR_UID = '".$sUserUID."' ";
        $resultUser = executeQuery($query);
        $groupId='';
        if(isset($resultUser))
            $groupId = $resultUser[1]['GRP_UID'];
        
        // Get the id of beneficiary role
        $queryRol = "SELECT rb_" . SYS_SYS . ".ROLES.ROL_UID FROM rb_" . SYS_SYS . ".ROLES WHERE rb_" . SYS_SYS . ".ROLES.ROL_CODE = '" .mysql_real_escape_string($rolUser) . "' ";
        $appQueryRol = executeQuery($queryRol,"rbac");
        $rolUid = $appQueryRol[1]['ROL_UID'];
        
        // Remove logged user group
        if (isset($rolUser)) {
            $deleteGroup = executeQuery("DELETE FROM GROUP_USER WHERE USR_UID = '".$sUserUID."' ");
            $RBAC->updateUser($aData, mysql_real_escape_string($rolUser));
            $role = $rolUser;
        }
        else {
          $RBAC->updateUser($aData);
        }

        if (isset($rolUser)) {
            $aData['USR_ROLE'] = $rolUser;
        }

        require_once 'classes/model/Users.php';
        $oUser = new Users();
        $oUser->update($aData);
        
        ######################### ADD GROUP USER LOGGED ###########################
    
        G::LoadClass('pmFunctions');
        $query = "SELECT GRP_UID FROM GROUPWF
                 INNER JOIN CONTENT ON (CON_ID = GRP_UID)
                 WHERE CON_VALUE = '".$rolUser."' AND CON_CATEGORY = 'GRP_TITLE'
                ";
        $group = executeQuery($query);
        $groupID = $group[1]['GRP_UID'];
      
        G::LoadClass('groups');
        $oGroup = new Groups();
        $oGroup->addUserToGroup($groupID, $sUserUID);
        
        #############################################################################
    
    return true;
    }
}
