<?php

/**
 * class.limousinProject.pmFunctions.php
 *
 * ProcessMaker Open Source Edition
 * Copyright (C) 2004 - 2008 Colosa Inc.
 * *
 */
require_once("plugins/limousinProject/classes/Webservices/Webservice.php");
require_once("plugins/limousinProject/classes/Webservices/Autorisation.php");
require_once("plugins/limousinProject/classes/Webservices/Blocage.php");
require_once("plugins/limousinProject/classes/Webservices/Transaction.php");
require_once("plugins/limousinProject/classes/Webservices/ActionCRM.php");
require_once("plugins/limousinProject/classes/Webservices/Activation.php");
require_once("plugins/limousinProject/classes/Webservices/Operation.php");
require_once("plugins/limousinProject/classes/Webservices/Solde.php");
require_once("plugins/limousinProject/classes/Webservices/Identification.php");

////////////////////////////////////////////////////
// limousinProject PM Functions
//
// Copyright (C) 2007 COLOSA
//
// License: LGPL, see LICENSE
////////////////////////////////////////////////////

function limousinProject_getMyCurrentDate(){
    return G::CurDate('Y-m-d');
}

function limousinProject_getMyCurrentTime(){
    return G::CurDate('H:i:s');
}
//LOCAL : a transforme dans le moteur de regle
function convergence_getIncompletErreur($app_id){
    $fields = convergence_getAllAppData($app_id);   
    $incomplet = array();    
    if ($fields['FINOM'] == '')
    {
        $incomplet[] = "Votre nom";
    }
    if ($fields['FIPRENOM'] == '')
    {
        $incomplet[] = "Votre prénom";
    }
    if ($fields['FIDATEDENAISSANCE'] == '')
    {
        $incomplet[] = "Votre date de naissance";
    }
    if ($fields['FIADRESSE1'] == '' || $fields['FICODEPOSTAL'] == '' || $fields['FIVILLE'] == '')
    {
        $incomplet[] = "L'adresse d'envoie pour la carte";
    }
    if ($fields['FIVILLE'] == '')
    {
        $incomplet[] = "Un code postal sur le territoire national";
    }
    if ($fields['FITELEPHONE'] == '')
    {
        $incomplet[] = "Votre numéro de téléphone";
    }
    if ($fields['FIEMAIL'] == '')
    {
        $incomplet[] = "Votre e-mail";
    }
    if ($fields['FISITUATION'] == '1' && $fields['FIETABLISSEMENT'] == '')
    {
        $incomplet[] = "Votre établissement";
    }
    if ($fields['FISITUATION'] == '2' && $fields['FISITUATIONDETAIL'] == '')
    {
        $incomplet[] = "Votre situation";
    }
    return $incomplet;
}

function convergence_getMsgErreur($app_id) {
   
    $refus = array();
    $fields = convergence_getAllAppData($app_id);
    if (!isset($fields['GDCERTIFSCOLARITE']) || $fields['GDCERTIFSCOLARITE'] == 0)
    {
        $refus[] = "Votre certificat de scolarité est manquant.";
    }
    else if (!isset($fields['GDCERTIFSCOLARITEOK']) || $fields['GDCERTIFSCOLARITEOK'] == 0)
    {
        $refus[] = "Votre certificat de scolarité n'est pas conforme.";
    }
    if (!isset($fields['GDSIGNATUREPARENTALE']) || $fields['GDSIGNATUREPARENTALE'] == 0)
    {
        $refus[] = "Votre signature parentale est manquante.";
    }
    if (!isset($fields['GDJUSTIFDOM']) || $fields['GDJUSTIFDOM'] == 0)
    {
        $refus[] = "Votre justificatif de domicile est manquant.";
    }
    else if (!isset($fields['GDJUSTIFDOMOK']) || $fields['GDJUSTIFDOMOK'] == 0)
    {
        $refus[] = "Votre justificatif de domicile n'est pas conforme.";
    }
    if (!isset($fields['GDJUSTIFIDENTITE']) || $fields['GDJUSTIFIDENTITE'] == 0)
    {
        $refus[] = "Votre justificatif d'identité est manquant.";
    }
    else if (!isset($fields['GDJUSTIFIDENTITEOK']) || $fields['GDJUSTIFIDENTITEOK'] == 0)
    {
        $refus[] = "Votre justificatif d'identité n'est pas conforme.";
    }
    return $refus;
}


function limousinProject_getGroupIdByRole($role) {
    $returnValue = false;
    $qGpId = ' SELECT *  FROM `CONTENT` WHERE `CON_VALUE` LIKE "' . $role . '" AND CON_CATEGORY = "GRP_TITLE"';
    $rGpId = executeQuery($qGpId);
    if (!empty($rGpId[1]['CON_ID']))
    {
     $returnValue = $rGpId[1]['CON_ID'];
    }

    return $returnValue;
}

function limousinProject_blocageCarte($porteurId, $statut, $role_user = 'Bénéficiaires') {
    $return = TRUE;
    if (!empty($porteurId))
    {
        // on regarde si le porteurid fourni est correct et on remonte le cas echeant les infos de la demande
        $exist = limousinProject_getCartePorteurId($porteurId);
        if (!empty($exist) && ($exist['USER_ID'] == $_SESSION['USER_LOGGED'] || $role_user != 'Bénéficiaires'))
        {
            //on appel le WS d'activation de la carte, on ajoute un groupe utilsateur carte active dans le fe_user Typo3
            //et mise a jour de la table des carte PMT_CHEQUES comme quoi elle est activée
            $groupeId = limousinProject_getGroupIdByRole($role_user);
            $active = limousinProject_getBlocage($porteurId,$groupeId);
            if (!empty($active->CODE) && $active->CODE == 'OK')
            {
               // pas d'erreur c'est lessieur ?
            }
            else
            {
                if (!empty($active->Description))
                {
                    // Erreur lors de l'updateUsergroup dans Typo3
                    $erreur = $active->Description;
                    $return = 'erreur';
                }
                else
                {
                    // On récupére le label de l'erreur lors de l'appel ws blocage carte
                    $erreur = limousinProject_getErrorAqoba($active->code, 'WS210') . " (code $active->code du WS210)";
                    $return = FALSE;
                }
            }
        }
        else
        {
            $return = FALSE;
            if (empty($exist))
                $erreur = 'Carte non produite.';
            else
                $erreur = 'PorteurId ' . $porteurId . ' incorrect.';
        }
    }
    else
    {
        $return = FALSE;
        if ($statut != 1)
            $erreur = 'Blocage annulée par le ' . $role_user;
        else
            $erreur = 'PorteurId incorrect.';
    }
    // Dans le cas où $return == 'erreur', alors la carte est bloqué, mais avec quand même des erreurs à signaler, donc on rentre dans les 2 conditions suivantes
    if ($return !== FALSE)
    {
        insertHistoryLogPlugin($exist['APPLICATION'], $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', "Carte bloquée par le $role_user", $exist['STATUT']);
    }
    if ($return !== TRUE)
    {
        if (empty($exist))
        {
            $arrayDemandeInfos = array();
            if ($role_user == 'Bénéficiaires')
                $queryDemande = "SELECT APP_UID FROM PMT_DEMANDES WHERE USER_ID = '" . $_SESSION['USER_LOGGED'] . "' AND STATUT <> 0 AND STATUT <> 999";
            else // cas où l'activation de carte est faite par un gestionnaire ou autre, et que le porteur id n'est pas valide
                $queryDemande = "SELECT APP_UID FROM PMT_DEMANDES WHERE CARTE_PORTEUR_ID = '" . $porteurId . "' AND STATUT <> 0 AND STATUT <> 999";
            $resultAppUid = executeQuery($queryDemande);
            if (sizeof($resultAppUid) == 1)
            {
                $appUid = $resultAppUid[1]['APP_UID'];
                $exist = convergence_getAllAppData($appUid, 1);
            }
        }
        if (!empty($exist))
            insertHistoryLogPlugin($exist['APPLICATION'], $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', "Erreur lors du blocage de la carte : " . $erreur, $exist['STATUT']);
    }
    return $return;
}



function limousinProject_getBlocage($porteurId, $groupeId ) {

    // INIT Ws 211    
    $result = null;
    $v = new Blocage();

    // SET Params
    $v->partenaire = wsPrestaId;
    $v->porteurId = $porteurId;
    

    // CALL Ws
    try
    {
        $v->call();
    // Si la carte est bien activée, on met à jour la table des cartes PMT_CHEQUES
        $query = 'update PMT_CHEQUES SET CARTE_STATUT = "Bloquée" where CARTE_PORTEUR_ID= "' . mysql_escape_string($porteurId) . '"';
        $resQuery = executeQuery($query);
        // Puis on change le usergroup dans Typo3 en Carte activé
        $data = limousinProject_getDemandeFromPorteurID($porteurId);
        $userInfo = userInfo($data['USER_ID']);
        $result = limousinProject_updateUsergroupTypo($userInfo, $porteurId, $groupeId);
    }
    catch (Exception $e)
    {
        $result = $v->errors;
    }
    // RETURN
    return $result;
}


function limousinProject_getDeblocage($porteurId ) {

    // INIT Ws 211    
    $result = null;
    $v = new Blocage();

    // SET Params
    $v->partenaire = wsPrestaId;
    $v->porteurId = $porteurId;// groupe carte Active typo
    

    // CALL Ws
    try
    {
        $v->call();
    // Si la carte est bien activée, on met à jour la table des cartes PMT_CHEQUES
        $query = 'update PMT_CHEQUES SET CARTE_STATUT = "Active" where CARTE_PORTEUR_ID= "' . mysql_escape_string($porteurId) . '"';
        $resQuery = executeQuery($query);
        // Puis on change le usergroup dans Typo3 en Carte activé
        $data = limousinProject_getDemandeFromPorteurID($porteurId);
        $userInfo = userInfo($data['USER_ID']);
        $result = limousinProject_updateUsergroupTypo($userInfo, $porteurId, 222);
    }
    catch (Exception $e)
    {

        $result = $v->errors;
        var_dump($e->getMessage());
    }
    // RETURN
    return $result;
}


function limousinProject_generatePorteurID($num_dossier) {
    
    /* Les 4 premiers caractères seront : 3028
      Les 6 autres seront le numéro unique créé par convergence
      Concernant le dernier la formule exacte est : 9 - somme(des 10 premiers chiffres) modulo 9.
      Ce qui fait que l'exemple du document est faux : 23 mod 9 = 5, et 9-5=4 donc le dernier chiffre doit être 4.
     */

    $query = 'INSERT INTO PMT_PORTEURID NUM_DOSSIER VALUES("%s") ';
    executeQuery(sprintf($query,$numDossier));
    $query = 'SELECT ID FROM PMT_PORTEURID WHERE NUM_DOSSIER= "%s"';
    $result =  executeQuery(sprintf($query,$numDossier));

           
    $prefix = '3028';
    $middle = str_pad($result[1]['ID'], 6, "0", STR_PAD_LEFT);
    $checksum = array_sum(str_split($prefix . $middle));
    $checksum = 9 - $checksum % 9;

    return  $prefix . $middle . $checksum;;
}

function limousinProject_getDemandeFromUserID($userId) {
    $arrayDemandeInfos = array();
    $queryDemande = "SELECT APP_UID FROM PMT_DEMANDES WHERE USER_ID = '" . $userId . "' AND STATUT = 6 AND THEMATIQUE = 1";
    $resultAppUid = executeQuery($queryDemande);
    if(sizeof($resultAppUid) == 1)
    {
        $appUid = $resultAppUid[1]['APP_UID'];
        $arrayDemandeInfos = convergence_getAllAppData($appUid, 1);
    }
    return $arrayDemandeInfos;
}

function limousinProject_getDemandeFromPorteurID($porterId) {
    $arrayDemandeInfos = array();
    $queryDemande = 'select APP_UID from PMT_DEMANDES where PORTEUR_ID = "' . $porterId . '" and STATUT <> 0 and STATUT <> 999';
    $resultAppUid = executeQuery($queryDemande);
    if(sizeof($resultAppUid) == 1)
    {
        $appUid = $resultAppUid[1]['APP_UID'];
        $arrayDemandeInfos = convergence_getAllAppData($appUid);
    }
    return $arrayDemandeInfos;
}

function limousin_addTransactionPriv($codePartenaire, $porteurID, $montant, $libelle, $thematique, $type) {
    $now = date('d-m-Y');
    $queryInsertTransactionPriv = "INSERT INTO PMT_TRANSACTIONS_PRIV(CODE_PARTENAIRE, PORTEUR_ID, MONTANT, LIBELLE, THEMATIQUE, TYPE, DATE_EMISSION, STATUT) VALUES('".$codePartenaire."','".$porteurID."','".$montant."','".$libelle."','".$thematique."','".$type."','".$now."', '16')";
    executeQuery($queryInsertTransactionPriv);
}

function limousinProject_isCarteActive($porteurID) {
    $isActive = false;
    $arrayData = array();
    $query = "SELECT CARTE_STATUT FROM PMT_CHEQUES WHERE CARTE_PORTEUR_ID = '".$porteurID."'";
    $result = executeQuery($query);
    if(sizeof($result) == 1)
    {
        $carteStatut = $result[1]['CARTE_STATUT'];
        $isActive = $carteStatut == 'Active' ? 1 : 0;
    }
    return $isActive;
}

function limousinProject_getDateNaissanceFromPorteurID($porteurID) {
    $dateNaissance = '';
    $query = "SELECT FI_DATEDENAISSANCE FROM PMT_DEMANDES WHERE STATUT <> 0 AND STATUT <> 999 AND PORTEUR_ID = '".$porteurID."'";
    $result = executeQuery($query);
    if(sizeof($result) == 1)
    {
        $dateNaissance = $result[1]['FI_DATEDENAISSANCE'];
    }
    return $dateNaissance;
}

function limousinProject_nouvelleTransaction($operation = 0, $porteurId = 0, $sens = 'N', $montant = 0, $sousMontants = array()) {

    // INIT Ws 201
    $t = new Transaction();
    $retour = array();
    /*
     * Liste non exhaustive d’opérations disponible:
     * 01:Versement,03:Déversement,10: Chargement par coupon recharge,15: Chargement par Transfert de carte à carte,
     * 17: Chargement depuis carte bancaire,20: Virement entrant,21: Virement sortant
     */

    // SET Params
    $t->partenaire = wsPrestaId;
    $t->operation = $operation;
    $t->porteurId = $porteurId;
    // C -> Chargement ou D -> Dechargement
    $t->sens = $sens;
    $t->montant = $montant;

    /* Mode Test On */
    //$t->porteurId = "30280000023";
    //$t->porteurId = "30280055283"; 30280055364
    //$t->porteurId = "0009";
    //$t->sens = "C";
    //$t->montant = "200";
    foreach ($sousMontants as $rsx => $sm)
    {
        $t->addSousMontant($rsx, $sm);
    }
    /* Mode Test Off */
    // CALL Ws
    try
    {
        $retour['success'] = $t->call();
    }
    catch (Exception $e)
    {
        $retour['errors'] = $t->errors->code;
    }
    return $retour;
}

function limousinProject_nouvelleActionCRM($porteurId = 0, $action = '00', $motif = '') {

    // INIT Ws 210
    $a = new ActionCRM();

    // SET Params
    $a->partenaire = wsPrestaId;
    $a->porteurId = $porteurId;
    $a->action = $action;


    /* Mode Test On */
   
    /* Mode Test Off */

    if (!empty($motif))
        $a->motif = $motif;

    // CALL Ws
    try
    {
        $retour = $a->call();
        echo 'ok => crm' . $retour . '--- end retour';
    }
    catch (Exception $e)
    {
        // TODO
        $echo = $a->errors->code;
        echo 'Code Erreur action = ' . $echo . '--- End Error ---';
    }
}

function limousinProject_getOperations($porteurId = 0, $op = '00', $nbJours = '100') {

    // INIT Ws 303
    $o = new Operation();

    // SET Params
    $o->operation = $op;
    $o->porteurId = $porteurId;
    $o->partenaire = wsPrestaId;
    $dateDep = date("Ymd", mktime(0, 0, 0, date("m") - 2, date("d"), date("Y"))); // a définir
    $o->dateDepart = $dateDep;
    $o->jours = $nbJours;
    /*
      00: Toutes les operations listé ci-dessous sauf les frais programmes,
      01: Retraits (et annulations de retraits),
      02: Versements (et annulations de versements),
      03: Déversements (et annulations de déversements),
      04: Achats (et annulations d’achats),
      05: Frais Porteurs (de retraits ou autres),
      06: Frais Programme
     */

    /* Mode Test On */
    //$o->porteurId = 30280000023;
    $o->porteurId = 30280055283;
    $o->operation = '02';
    /* Mode Test Off */

    // CALL Ws
    try
    {
        // TODO
        $retour = $o->call();
        echo 'ok oper => ' . $retour . '--- end retour';
    }
    catch (Exception $e)
    {
        // TODO
        //$echo = $e->errors; //. ' : ' . $e->message;
        echo 'Code Erreur operation = ' . $o->errors->code . '--- End Error ---';
        //var_dump($e);
    }
}


function limousinProject_getAutorisations() {

    // INIT Ws 303
    $a = new Autorisation();

    // SET Params
    $a->partenaire = wsPrestaId;
    
    // CALL Ws
    try
    {
        // TODO
        $retour = $a->call();
        echo 'ok oper => ' . $retour . '--- end retour';
    }
    catch (Exception $e)
    {
        // TODO
        //$echo = $e->errors; //. ' : ' . $e->message;
        echo 'Code Erreur operation = ' . $a->errors->code . '--- End Error ---';
        //var_dump($e);
    }
}

function limousinProject_getActivation($porteurId = 0) {

    // INIT Ws 211    
    $result = null;
    $v = new Activation();

    // SET Params
    $v->partenaire = wsPrestaId;
    $v->porteurId = $porteurId;

    /* Mode Test On */
    //$v->porteurId = 30280055364;
    //$s->porteurId = 30280000023;
    /* Mode Test Off */
    // CALL Ws
    try
    {
        $v->call();
    // Si la carte est bien activée, on met à jour la table des cartes PMT_CHEQUES
        $query = 'update PMT_CHEQUES SET CARTE_STATUT = "Active", DATE_ACTIVE = "'.date('Ymd').'" where CARTE_PORTEUR_ID= "' . mysql_escape_string($porteurId) . '"';
        $resQuery = executeQuery($query);
        // Puis on change le usergroup dans Typo3 en Carte activé
        $data = limousinProject_getDemandeFromPorteurID($porteurId);
        $userInfo = userInfo($data['USER_ID']);
        $result = limousinProject_updateUsergroupTypo($userInfo, $porteurId, '222');
    }
    catch (Exception $e)
    {
        $result = $v->errors;
    }
    // RETURN
    return $result;
}
function limousinProject_getSolde($porteurId = 0) {

    // INIT Ws 304
    $s = new Solde();

    // SET Params
    $s->partenaire = wsPrestaId;
    $s->porteurId = $porteurId;

    /* Mode Test On */
    //$s->porteurId = 30280055364;
    //$s->porteurId = 30280055283;
    //$s->porteurId = '30280000023';
    /* Mode Test Off */

    // CALL Ws
    try
    {
        // TODOs
        $arraySolde = array();
        $arraySousSoldes = array();        
        $obj = $s->call();
        foreach($obj->sousSoldes->sousSolde as $sousSolde)
        {
            $attrib = $sousSolde->attributes();
            $attrib = (array) $attrib['reseau'];
            $value = (array) $sousSolde;
            $arraySousSoldes[$attrib[0]] = (int) $value[0];
        }
        $solde = (array) $obj->solde;
        $arraySolde['solde'] = $solde[0];
        $soldeBrut = (array) $obj->soldeBrut;
        $arraySolde['soldeBrut'] = $soldeBrut[0];
        $soldeComptable = (array) $obj->soldeComptable;
        $arraySolde['soldeComptable'] = $soldeComptable[0];
        $arraySolde['sousSoldes'] = $arraySousSoldes;
        //$arraySolde = $obj->sousSoldes->sousSolde[0]->attributes();
        //$arraySolde = (array) $arraySolde['reseau'];
        //return $obj->sousSoldes->sousSolde[0]->attributes();
        return $arraySolde;
    }
    catch (Exception $e)
    {
        // TODO
        return $s;
        //return $s;
        // echo 'Code Erreur solde = ' . $echo . '--- End Error ---';
    }
}

function limousinProject_testSolde($soldeReseau, $montant)
{
    $result = -1;
    if($soldeReseau >= $montant)
    {
        $result = 1;
    }
    else
    {
        $result = 0;
    }
    return $result;
}

function limousinProject_identification($porteurId = 0, $tel = '', $portable = '', $mail = '', $numCarte = '') {

    // INIT Ws 307
    $i = new Identification();

    // SET Params
    $i->porteurId = $porteurId;
    $i->telephone = $tel;
    $i->portable = $portable;
    $i->email = $mail;
    $i->numcarte = $numCarte;

    /* Mode Test On 
    //$i->porteurId = 30280055364;
    $i->porteurId = 30280055283;
    $i->numcarte = '0007';
    $i->portable = 'quentin@oblady.fr';
    $i->email = '11-15-61-56-51';
    //$i->porteurId = 30280000023;
    /* Mode Test Off */

    // CALL Ws
    try
    {
        $retour = $i->call();
    }
    catch (Exception $e)
    {
        // TODO
        $echo = $i->errors->code;
        echo 'Code Erreur identification = ' . $echo . '--- End Error ---';
    }
}


function limousinProject_createUser($app_id, $role, $pwd) {
    $fields = convergence_getAllAppData($app_id);
    $fields['PASSWORD'] = $pwd;
    if (empty($fields['PRENOM_CONTACT']))
        $fields['PRENOM_CONTACT'] = $fields['NOM_CONTACT']; // need the both for create account on typo3
    //PMFCreateUser(string userId, string password, string firstname, string lastname, string email, string role)
    $isCreate = PMFCreateUser($fields['MAIL'], $fields['PASSWORD'], $fields['NOM_CONTACT'], $fields['PRENOM_CONTACT'], $fields['MAIL'], $role);
    if ($isCreate == 0)
    {
        return FALSE;
    }
    $uQuery = 'SELECT USR_UID FROM USERS WHERE USR_USERNAME ="' . $fields['MAIL'] . '"';
    $rQuery = executeQuery($uQuery);
    if (!empty($rQuery))
    {
        $usr_uid = $rQuery[1]['USR_UID'];
        $qGpId = ' SELECT *  FROM `CONTENT` WHERE `CON_VALUE` LIKE "' . $role . '" AND CON_CATEGORY = "GRP_TITLE"';
        $rGpId = executeQuery($qGpId);

        if (!empty($rGpId[1]['CON_ID']))
        {
          
            $groupId = $rGpId[1]['CON_ID'];
            $var = PMFAssignUserToGroup($usr_uid, $groupId);

            // creation du fe_user dans typo3
            ini_set("soap.wsdl_cache_enabled", "0");
            $hostTypo3 = 'http://' . HostName . '/typo3conf/ext/pm_webservices/serveur.php?wsdl';
            $pfServer = new SoapClient($hostTypo3);
            $key = rand();
            $ret = $pfServer->createAccount(array(
                'username' => $fields['MAIL'],
                'password' => md5($fields['PASSWORD']),
                'email' => $fields['MAIL'],
                'lastname' => $fields['PRENOM_CONTACT'],
                'firstname' => $fields['NOM_CONTACT'],
                'key' => $key,
                'pmid' => $usr_uid,
                'usergroup' => $groupId,
                'cHash' => md5($fields['MAIL'] . '*' . $fields['PRENOM_CONTACT'] . '*' . $fields['NOM_CONTACT'] . '*' . $key)));
        }
    }
    else
    {
        return FALSE;
    }
    return $usr_uid;
}

function limousinProject_getEtablissementFromRNE($rneCode) {
    $sql = 'SELECT RNE, NOM FROM PMT_ETABLISSEMENT WHERE RNE = "' . $rneCode . '" AND STATUT = 1';
    $res = executeQuery($sql);
    if (isset($res) && count($res) > 0)
    {
        $ret = $res[1]['RNE'] . ' - ' . $res[1]['NOM'];
    }
    else
    {
        $ret = '0';
    }
    return $ret;
}

function limousinProject_getPathAQPORTR() {

    $sql = 'select PATH_FILE from PMT_LISTE_OPER';
    $res = executeQuery($sql);
    if (!empty($res))
    {
        $path = $res[1]['PATH_FILE'] . '/OUT/AQ_PORT_R_001_00028.' . date('Ymd');
    }
    else
        $path = '/var/tmp/AQ_PORT_R_001_00028.' . date('Ymd');

    return $path;
}

/*  Ajout les lignes d'entête et de fin de fichier pour le fichier AQ_PORT 
 *
 * @param string $file le chemin du fichier à modifier
 *  */

function limousinProject_updateAQPORTR($file, $num_dossier) {

    $qIdFile = 'select max(ID)+1 as num_fic from PMT_NUM_PROD_FOR_AQOBA';
    $rIdFile = executeQuery($qIdFile);
    if (!empty($rIdFile[1]['num_fic']) && $rIdFile[1]['num_fic'] != 0)
    {
        $num_fic = str_pad($rIdFile[1]['num_fic'], 15, 0, STR_PAD_LEFT);
        $id = $rIdFile[1]['num_fic'];
    }
    else
    {
        $num_fic = str_pad('1', 15, 0, STR_PAD_LEFT);
        $id = 1;
    }
    $qAdd = 'insert into PMT_NUM_PROD_FOR_AQOBA (FILE_NAME, NUM_PROD, ID) values ("' . mysql_escape_string(basename($file)) . '","' . intval($num_dossier) . '","' . intval($id) . '")';
    $rAdd = executeQuery($qAdd);
    $filler = str_pad('', 32, ' ');
    $start_line = '00101004' . date("YmdHis") . $num_fic . $filler . "\n";
    $end_line = '00301004' . date("YmdHis") . $num_fic . $filler . "\n";
    $content = file($file);
    array_unshift($content, $start_line);
    array_push($content, $end_line);
    $new_content = implode('', $content);
    $fp = fopen($file, 'w');
    $w = fwrite($fp, $new_content);
    fclose($fp);
}

/*  Supprime les lignes d'entête et de fin de fichier fournie par AQOBA
 *
 * @param   array   $list_file  liste des fichiers sur la machine PM en local
 * 
 * @return  array   $list_file  liste des fichiers modifiés sur la machine PM en local
 */

function limousinProject_removeWrapFileAqoba($list_file){
    
    if(!empty($list_file)){        
        foreach ($list_file as $file) 
        {
            $content = file($file);
            if(!empty($content))
            {
                $first = array_shift($content);
                $last = array_pop($content);
                $new_content =  implode('', $content);
                $fp = fopen($file, 'w+');
                $w = fwrite($fp, $new_content);
                fclose($fp);
            }
        }
        return TRUE;        
    }
    return FALSE;
}
function limousinProject_updateFromAQPORTREJ($file){
    //on récupère le contenu du fichier
    $content = file($file);
    $data = array();
    if(!empty($content))
    {
        foreach($content as $line)            
        {            
            $code_erreurs = trim(substr($line, 1123));
            $porter_id = trim(substr($line, 29, 12));
            $q = 'select APP_UID from PMT_DEMANDES where PORTEUR_ID = "' . $porter_id . '" and STATUT = 7';
            $r = executeQuery($q);
            if(!empty($r[1]['APP_UID']))
            {
                convergence_changeStatut($r[1]['APP_UID'], 41, 'Erreur dans le fichier AQ_PORT_R code :' . $code_erreurs);
                $code_err = '"' . strtr(trim($code_erreurs), array(' ' => '","')) . '"';
                $qError = 'select LABEL_E_AQ from PMT_CODE_ERREUR_AQOBA where CODE_E_AQ IN(' . $code_err . ') AND SERVICE_E_AQ = "AQ_PORT"';
                $rError = executeQuery($qError);
                foreach ($rError as $value)
                {
                    $data[$r[1]['APP_UID']][] = $value['LABEL_E_AQ'];
                }
            }
        }            
        foreach ($data as $app_uid => $list_err)
        {            
            $msgList = '<br/>&nbsp;-&nbsp;' . implode('<br/>&nbsp;-&nbsp;', $list_err);
            $msg['msgRefus'] = 'Création de carte refusé par AQOBA pour les raisons suivante : <br/>' . $msgList;
            convergence_updateDemande($app_uid, $msg);
            
        }
    }
}
function limousinProject_explicationStatut_callback($app_data) {

    $libelStatut = 'SELECT TITLE FROM PMT_STATUT WHERE UID=' . intval($app_data['STATUT']);
    $libelRes = executeQuery($libelStatut);
    switch (intval($app_data['STATUT']))
    {
        case 41 :
            $messageInfo = $app_data['msgRefus'];
            break;
        case 42 :
            $msgList = $app_data['msgRefus'];
            if (count(explode('<br/>&nbsp;-&nbsp;', $app_data['msgRefus'] ? $app_data['msgRefus'] : '')) > 2)
            {
                $messageInfo = 'Le dossier est <b>' . strtolower($libelRes[1]['TITLE']) . '</b> pour les raisons suivantes :';
            }
            else
            {
                $messageInfo = 'Le dossier est <b>' . strtolower($libelRes[1]['TITLE']) . '</b> pour la raison suivante :';
            }
            $messageInfo .= isset($app_data['msgRefus']) ? $app_data['msgRefus'] : '';
            break;

        default :
            $messageInfo = 'Le dossier est en statut : <b>' . strtolower($libelRes[1]['TITLE']) . '</b>';
            break;
    }
    return $messageInfo;
}
function limousinProject_readLineFromAQCARTE($datas) {
    
    //INIT
    $err = array();

    foreach ($datas as $line)
    {
        $escapeLine = array();

        // Escape scpeial caracters
        foreach ($line as $key => $lineItem)
            $escapeLine[$key] = mysql_escape_string($lineItem);

        $qExist = 'select count(UID) as nb from PMT_CHEQUES where CARTE_PORTEUR_ID = "' . $escapeLine['CARTE_PORTEUR_ID'] . '"';
        $rExist = executeQuery($qExist);
        $nbID = $rExist[1]['nb'];
        switch ($escapeLine['CODE_EVENT'])
        {
            case '05' :// Phase 1 : Prise en compte et création dans le système AQOBA
                /* création de la ligne dans la table PMT_CHEQUES */
                if ($nbID == 0)
                {
                    $escapeLine['CARTE_STATUT'] = 'Création';
                    $escapeLine['DATE_CREATION'] = date('Ymd');
                    $keys = implode(',', array_keys($escapeLine));
                    $values = '"' . implode('","', $escapeLine) . '"';
                    $query = 'INSERT INTO PMT_CHEQUES (' . $keys . ') VALUES (' . $values . ')';
                    $result = executeQuery($query);
                }
                else                
                    $err[] = 'Porteur Id existe déjà';                
                break;

            case '14' : //Phase 2 : Frabrication et envoie
                /* Dans ce cas, vérifier si une demande possède ce porteur id en mode ré-édition
                 * pour transférer les soldes entre les deux cartes
                 * sinon mettre à jour simplement la ligne et le statut, et mettre la demande en produite  */
                if ($nbID > 0)
                {
                    $set = array();
                    $escapeLine['CARTE_STATUT'] = 'Envoyée';
                    $escapeLine['DATE_EXPE'] = date('Ymd');
                    foreach ($escapeLine as $key => $value)
                    {
                        $set[] = $key . '="' . $value . '"';
                    }
                    $update = implode(',', $set);
                    $query = 'update PMT_CHEQUES SET ' . $update . ' where CARTE_PORTEUR_ID= "' . $escapeLine['CARTE_PORTEUR_ID'] . '"';
                    $result = executeQuery($query);
                    // reste les ws dans le cas des ré-édition
                    $qDemande = 'select count(*) as nb, APP_UID, OLD_PORTEUR_ID from PMT_DEMANDES where PORTEUR_ID ="' . $escapeLine['CARTE_PORTEUR_ID'] . '" and STATUT <> 0 and STATUT <> 999';
                    $rDemande = executeQuery($qDemande);
                    if ($rDemande[1]['nb'] > 0)
                    {
                        convergence_changeStatut($rDemande[1]['APP_UID'], 6);
                        if (!empty($rDemande[1]['OLD_PORTEUR_ID']))
                        {
                            // ws pour le transfert des soldes
                        }
                    }
                }
                else
                    $err[] = "Porteur Id n'existe pas";
                break;

            case '10' : // Phase 3 : Activation
                /* Mettre à jour la ligne, le DATE_ACTIVE et le statut à Active */
                if ($nbID > 0)
                {
                    $escapeLine['CARTE_STATUT'] = 'Active';
                    $escapeLine['DATE_ACTIVE'] = date('Ymd');
                    foreach ($escapeLine as $key => $value)
                    {
                        $set[] = $key . '="' . $value . '"';
                    }
                    $update = implode(',', $set);
                    $query = 'update PMT_CHEQUES SET ' . $update . ' where CARTE_PORTEUR_ID= "' . $escapeLine['CARTE_PORTEUR_ID'] . '" AND CARTE_STATUT != "Bloquée"';
                    $result = executeQuery($query);
                }
                else
                    $err[] = "Porteur Id n'existe pas";
                break;

            default:
                break;
        }
    }
    return TRUE;
}

// non op
function limousinProject_showPdf($app_uid) {
    $query = 'SELECT * FROM APP_DOCUMENT, CONTENT WHERE APP_UID="' . $app_uid . '" AND DOC_UID="884895097521c61f362fc13075215643" AND APP_DOC_TYPE="OUTPUT" AND APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "fr"';
    $result = executeQuery($query);
    if (method_exists('G', 'getPathFromUID'))
    {
        $app_uid = G::getPathFromUID($app_uid);
    }
    $path = PATH_DOCUMENT . $app_uid . PATH_SEP . 'outdocs' . PATH_SEP . $result[1]['APP_DOC_UID'] . '_' . $result[1]['DOC_VERSION'];
    $file = $path . '.pdf';

        
    if (file_exists($file))
      {//TODO
        //OUPUT HEADERS
      
        $fileContent = file_get_contents($file);
        
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: private",false);
      header("Content-Type: application/pdf");
      header('Content-Disposition: attachment; filename="' . $result[1]['CON_VALUE'] . '.pdf";');
      header('Content-Length: ' . strlen($fileContent));
      header("Content-Transfer-Encoding: binary");

      
      echo $fileContent;
      
    }
    return;
}

function limousinProject_getSituationLabel($situation) {
    $query = 'select LABEL from PMT_FI_SITUATION where VALEUR = "' . intval($situation) . '"';
    $result = executeQuery($query);
    return $label = (!empty($result[1]['LABEL']) ? $result[1]['LABEL'] : '');
}

function limousinProject_getCartePorteurId($porteur_id) {

    // on check qu'il y ai bien une carte produite pour ce porteur id
    $qExist = 'select count(UID) as nb from PMT_CHEQUES where CARTE_PORTEUR_ID = "' . mysql_escape_string($porteur_id) . '" AND CODE_EVENT = 14';    
    $rExist = executeQuery($qExist);
    $nbID = intval($rExist[1]['nb']);
    if (!empty($nbID) && $nbID > 0)
    {
        $datas = limousinProject_getDemandeFromPorteurID(intval($porteur_id));
        return $datas;
    }
    else
        return FALSE;
}

function limousinProject_getErrorAqoba($code, $service) {

    $qError = "select LABEL_E_AQ from PMT_CODE_ERREUR_AQOBA where CODE_E_AQ = '" . $code . "'  AND SERVICE_E_AQ = '" . $service . "'";
    $rError = executeQuery($qError);
    if(!empty($rError[1]['LABEL_E_AQ']))
        return $rError[1]['LABEL_E_AQ'];
    else
        return "Une erreur inconnue c'est produite !!! code : $code, service : $service";
}

function limousinProject_updateUsergroupTypo($userInfo, $porteurid, $groupId) {
    //appel du ws pour modifier le usergroup dans typo3
    ini_set("soap.wsdl_cache_enabled", "0");
    $hostTypo3 = 'http://' . HostName . '/typo3conf/ext/pm_webservices/serveur.php?wsdl';
    $pfServer = new SoapClient($hostTypo3);
    $key = rand();
    $ret = $pfServer->updateUsergroup(array(
        'username' => $userInfo['username'],
        'firstname' => $userInfo['firstname'],
        'lastname' => $userInfo['lastname'],
        'porteurid' => $porteurid,
        'key' => $key,
        'usergroup' => $groupId,
        'cHash' => md5($userInfo['username'] . '*' . $userInfo['lastname'] . '*' . $userInfo['firstname'] . '*' . $key)));
    return $ret;
}

function limousinProject_activationCarte($porteurId, $statut, $role_user = 'Bénéficiaires') {
    $return = TRUE;
    if (!empty($porteurId) && $statut == 1)
    {
        // on regarde si le porteurid fourni est correct et on remonte le cas echeant les infos de la demande
        $exist = limousinProject_getCartePorteurId($porteurId);
        if (!empty($exist) && ($exist['USER_ID'] == $_SESSION['USER_LOGGED'] || $role_user != 'Bénéficiaires'))
        {
            //on appel le WS d'activation de la carte, on ajoute un groupe utilsateur carte active dans le fe_user Typo3
            //et mise a jour de la table des carte PMT_CHEQUES comme quoi elle est activée
            $active = limousinProject_getActivation($porteurId);
            if (!empty($active->CODE) && $active->CODE == 'OK')
            {
                // on appel le WS de televersement des montants
                $sousMontants = array(165 => "800", "800", "1000", "800", "400", "1200");
                $transaction = array();
                $transaction = limousinProject_nouvelleTransaction('01', $porteurId, 'C', 5000, $sousMontants);
                if (!empty($transaction['errors']))
                {
                    //on récupére le label du code erreur de transaction
                    $erreur = limousinProject_getErrorAqoba($transaction['errors'], 'WS201') . ' (code' . $transaction['errors'] . ' du WS201)';
                    $return = 'erreur';
                }
            }
            else
            {
                if (!empty($active->Description))
                {
                    // Erreur lors de l'updateUsergroup dans Typo3
                    $erreur = $active->Description;
                    $return = 'erreur';
                }
                else
                {
                    // On récupére le label de l'erreur lors de l'appel ws activation carte
                    $erreur = limousinProject_getErrorAqoba($active->code, 'WS211') . " (code $active->code du WS211)";
                    $return = FALSE;
                }
            }
        }
        else
        {
            $return = FALSE;
            if (empty($exist))
                $erreur = 'Carte non produite.';
            else
                $erreur = 'PorteurId ' . $porteurId . ' incorrect.';
        }
    }
    else
    {
        $return = FALSE;
        switch ($statut)
        {
            case '2':
                $erreur = 'Activation annulée par le ' . $role_user;
                break;
            case '3':
                $erreur = 'CGU non accepté par le ' . $role_user;
                break;

            default:
                $erreur = 'PorteurId incorrect.';
                break;
        }
//        if ($statut != 1)
//            $erreur = 'Activation annulée par le ' . $role_user;
//        else
//            $erreur = 'PorteurId incorrect.';
    }
    // Dans le cas où $return == 'erreur', alors la carte est activé, mais avec quand même des erreurs à signaler, donc on rentre dans les 2 conditions suivantes
    if ($return !== FALSE)
    {
        insertHistoryLogPlugin($exist['APPLICATION'], $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', "Carte activée par le $role_user", $exist['STATUT']);
    }
    if ($return !== TRUE)
    {
        if (empty($exist))
        {
            $arrayDemandeInfos = array();
            if ($role_user == 'Bénéficiaires')
                $queryDemande = "SELECT APP_UID FROM PMT_DEMANDES WHERE USER_ID = '" . $_SESSION['USER_LOGGED'] . "' AND STATUT <> 0 AND STATUT <> 999";
            else // cas où l'activation de carte est faite par un gestionnaire ou autre, et que le porteur id n'est pas valide
                $queryDemande = "SELECT APP_UID FROM PMT_DEMANDES WHERE CARTE_PORTEUR_ID = '" . $porteurId . "' AND STATUT <> 0 AND STATUT <> 999";
            $resultAppUid = executeQuery($queryDemande);
            if (sizeof($resultAppUid) == 1)
            {
                $appUid = $resultAppUid[1]['APP_UID'];
                $exist = convergence_getAllAppData($appUid, 1);
            }
        }
        if (!empty($exist))
            insertHistoryLogPlugin($exist['APPLICATION'], $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', "Erreur lors de l'activation de la carte : " . $erreur, $exist['STATUT']);
    }
    return $return;
}

function limousinProject_getThematiqueFromPartenaire($userId) {
    //array('1' => array('CODE'=>'165', 'TYPE'=>'Cinéma'));
    $query = "SELECT CONCAT(TH_CINE, '-', TH_SPECTACLE, '-', TH_ACHAT, '-', TH_ARTS, '-', TH_SPORT, '-', IF(TH_ADH_ART = '0', TH_ADH_SPORT, TH_ADH_ART)) AS THEMATIQUE FROM PMT_PRESTATAIRE where STATUT=1 AND USER_ID ='".$userId."'";
    $result = executeQuery($query);
    $thematiquesArray = array();
    if(isset($result))        
    {
        $thematiqueString = $result[1]['THEMATIQUE'];
        $thematiquePrestaArray = explode('-', $thematiqueString);
        if($thematiquePrestaArray[0] == 1)
        {
            $thematiquesArray['1'] = array('CODE'=>'165', 'TYPE'=>'Cinéma');
        }
        if($thematiquePrestaArray[1] == 1)
        {
            $thematiquesArray['2'] = array('CODE'=>'166', 'TYPE'=>'Spectacle Vivant');
        }
        if($thematiquePrestaArray[2] == 1)
        {
            $thematiquesArray['3'] = array('CODE'=>'167', 'TYPE'=>'Achat de livre et produtis multimédia');
        }
        if($thematiquePrestaArray[3] == 1)
        {
            $thematiquesArray['4'] = array('CODE'=>'168', 'TYPE'=>'Arts Plastiques');
        }
        if($thematiquePrestaArray[4] == 1)
        {
            $thematiquesArray['5'] = array('CODE'=>'169', 'TYPE'=>'Manifestation ou évènement sportif');
        }
        if($thematiquePrestaArray[5] == 1)
        {
            $thematiquesArray['6'] = array('CODE'=>'170', 'TYPE'=>'Adhésion pratique sportive ou artistique');
        }
    }   
    return $thematiquesArray;    
}

?>
