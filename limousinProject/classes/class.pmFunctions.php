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
            $active = limousinProject_getBlocage($porteurId, $groupeId);
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
        $query = 'update PMT_CHEQUES SET CARTE_STATUT = "Bloquée", DATE_BLOCAGE = "' . date('Y-m-d') . '" where CARTE_PORTEUR_ID= "' . mysql_escape_string($porteurId) . '"';
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
        $query = 'update PMT_CHEQUES SET CARTE_STATUT = "Active", DATE_ACTIVE = "' . date('Y-m-d') . '" where CARTE_PORTEUR_ID= "' . mysql_escape_string($porteurId) . '"';
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

function limousinProject_generatePorteurID($numDossier) {

    /* Les 4 premiers caractères seront : 3028
      Les 6 autres seront le numéro unique créé par convergence
      Concernant le dernier la formule exacte est : 9 - somme(des 10 premiers chiffres) modulo 9.
      Ce qui fait que l'exemple du document est faux : 23 mod 9 = 5, et 9-5=4 donc le dernier chiffre doit être 4.
     */

    $query = 'INSERT INTO PMT_PORTEURID (NUM_DOSSIER) VALUES("%s") ';
    executeQuery(sprintf($query, $numDossier));
    $query = 'SELECT ID FROM PMT_PORTEURID WHERE NUM_DOSSIER= "%s"';
    $result = executeQuery(sprintf($query, $numDossier));


    $prefix = '3028';
    $middle = str_pad($result[1]['ID'], 6, "0", STR_PAD_LEFT);
    $checksum = array_sum(str_split($prefix . $middle));
    $checksum = 9 - $checksum % 9;

    $query = 'UPDATE PMT_PORTEURID  SET PORTEUR_ID = %s WHERE ID = %s';
    executeQuery(sprintf($query, $prefix . $middle . $checksum, $result[1]['ID']));

    return $prefix . $middle . $checksum;
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
// Ajout de numDossier en paramètre pour avoir une relation lors des remboursement avec la report table
function limousin_addTransactionPriv($codePartenaire, $porteurID, $montant, $libelle, $thematique, $type, $numDossier) {
    $now = date('d-m-Y');
    $queryInsertTransactionPriv = "INSERT INTO PMT_TRANSACTIONS_PRIV(CODE_PARTENAIRE, PORTEUR_ID, MONTANT, LIBELLE, THEMATIQUE, TYPE, DATE_EMISSION, STATUT, DOSSIER) VALUES('" . $codePartenaire . "','" . $porteurID . "','" . $montant . "','" . $libelle . "','" . $thematique . "','" . $type . "','" . $now . "', '16', '" . $numDossier . "')";
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
        $query = 'UPDATE PMT_CHEQUES SET CARTE_STATUT = "Active", DATE_ACTIVE = "' . date('Ymd') . '" where CARTE_PORTEUR_ID= "' . mysql_escape_string($porteurId) . '"';
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

// Créer un utilisateur Prestataire Privatif dans PM et Typo
function limousinProject_createUser($app_id, $role, $pwd) {
    $fields = convergence_getAllAppData($app_id);
    $fields['PASSWORD'] = $pwd;
    // On prend la Raison Social comme prénom pour l'afficher dans Typo
    // Si la raison social est vide on prend le prénom ou le nom.
    if ( empty($fields['RAISONSOCIALE']) )
    {
        if ( empty($fields['PRENOM_CONTACT']) )
        {
            $fields['RAISONSOCIALE'] = $fields['MAIL'];
        }
        else
        {
            $fields['RAISONSOCIALE'] = $fields['PRENOM_CONTACT'];
        }
    }
    if ( empty($fields['NOM_CONTACT']) )
    {       
            $fields['NOM_CONTACT'] = $fields['MAIL'];
    }

    //PMFCreateUser(string userId, string password, string firstname, string lastname, string email, string role)
    $isCreate = PMFCreateUser($fields['MAIL'], $fields['PASSWORD'], $fields['RAISONSOCIALE'], $fields['NOM_CONTACT'], $fields['MAIL'], $role);
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
                'lastname' => $fields['NOM_CONTACT'],
                'firstname' => $fields['RAISONSOCIALE'],
                'key' => $key,
                'pmid' => $usr_uid,
                'usergroup' => $groupId,
                'cHash' => md5($fields['MAIL'] . '*' . $fields['NOM_CONTACT'] . '*' . $fields['RAISONSOCIALE'] . '*' . $key) ));
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
    if(!empty($list_file))
    {
        foreach ($list_file as $file)
        {
            $content = file($file);
            if(!empty($content))
            {
                $first = array_shift($content);
                $last = array_pop($content);
                $new_content = implode('', $content);
                $fp = fopen($file, 'w+');
                $w = fwrite($fp, $new_content);
                fclose($fp);
            }
        }
        return TRUE;
    }
    return FALSE;
}

/*  Supprime les lignes d'entête et de fin de fichier fournie par AQOBA
 *
 * @param   array   $list_file  liste des fichiers sur la machine PM en local
 *
 * @return  array   $list_file  liste des fichiers modifiés sur la machine PM en local
 */
function limousinProject_removeWrapFileAqobaTransaction($list_file) {
    if (!empty($list_file))
    {
        foreach ($list_file as $file)
        {
            $content = file($file);
            if (!empty($content))
            {
                $first = array_shift($content);
                $new_content = implode('', $content);
                $fp = fopen($file, 'w+');
                $w = fwrite($fp, $new_content);
                fclose($fp);
            }
        }
        return TRUE;
    }
    return FALSE;
}

function limousinProject_importFromFileTransaction($file)
{
    if(($handle = fopen($file, "r")) !== FALSE)
    {
        while(($data = fgetcsv($handle, 500, ";")) !== FALSE)
        {
            $valuesQuery .= '("'.$data[0].'","'.$data[1].'","'.$data[2].'","'.$data[3].'","'.$data[4].'","'.$data[5].'","'.($data[6]*100).'","'.$data[7].'","'.$data[8].'","'.$data[9].'","'.$data[10].'","'.$data[11].'","'.$data[12].'","'.$data[13].'","'.$data[14].'"), ';
        }
        $valuesQuery = substr($valuesQuery, 0, -2);
        $insertQuery = 'INSERT INTO PMT_TRANSACTIONS(NOM_OFFRE, SOCIETE_CLIENTE, ID_PORTEUR, DATE_EFFECTIVE, DATE_COMPENSATION, TYPE_TRANSACTION, '.
                       ' MONTANT_NET, DEVISE, C_RAISON_SOCIALE, ADRESSE_COMMERCANT, CP_COMMERCANT, CODE_MCC, LIBELLE_MCC, ID_COMMERCANT, ID_TRANSACTION) VALUES ';
        $insertQuery .= $valuesQuery;
        executeQuery($insertQuery);
        //mail('quentin@oblady.fr', 'plop', var_export($insertQuery, true));
        fclose($handle);
        unlink($file);
    }
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

        // Escape special caracters
        foreach ($line as $key => $lineItem)
            $escapeLine[$key] = mysql_escape_string($lineItem);

        $qExist = 'select count(UID) as nb from PMT_CHEQUES where CARTE_PORTEUR_ID = "' . $escapeLine['CARTE_PORTEUR_ID'] . '"';
        $rExist = executeQuery($qExist);
        // Requête utilisé pour les insertHistoryLogPlugin()
        $qDemande = 'select count(*) as nb, APP_UID, OLD_PORTEUR_ID from PMT_DEMANDES where PORTEUR_ID ="' . $escapeLine['CARTE_PORTEUR_ID'] . '" and STATUT <> 0 and STATUT <> 999';
        $rDemande = executeQuery($qDemande);
        $nbID = $rExist[1]['nb'];
        switch ($escapeLine['CODE_EVENT'])
        {
            case '05' :// Phase 1 : Prise en compte et création dans le système AQOBA
                /* création de la ligne dans la table PMT_CHEQUES */
                if ($nbID == 0)
                {
                    $escapeLine['CARTE_STATUT'] = 'Création';
                    $escapeLine['DATE_CREATION'] = $escapeLine['DATE_EVENT_CARTE'];
                    $keys = implode(',', array_keys($escapeLine));
                    $values = '"' . implode('","', $escapeLine) . '"';
                    $query = 'INSERT INTO PMT_CHEQUES (' . $keys . ') VALUES (' . $values . ')';
                    $result = executeQuery($query);
                    if ($rDemande[1]['nb'] > 0)
                    {
                        insertHistoryLogPlugin($rDemande[1]['APP_UID'], $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', 'Evénement AQOBA AQ_CARTE n°05', 7);
                    }
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
                    $escapeLine['DATE_EXPE'] = $escapeLine['DATE_EVENT_CARTE'];
                    foreach ($escapeLine as $key => $value)
                    {
                        $set[] = $key . '="' . $value . '"';
                    }
                    $update = implode(',', $set);
                    $query = 'update PMT_CHEQUES SET ' . $update . ' where CARTE_PORTEUR_ID= "' . $escapeLine['CARTE_PORTEUR_ID'] . '"';
                    $result = executeQuery($query);
                    // reste les ws dans le cas des ré-édition TODO: à modifier
                    if ($rDemande[1]['nb'] > 0)
                    {
                        convergence_changeStatut($rDemande[1]['APP_UID'], 6, 'Evenement AQOBA AQ_CARTE n°14');
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
                    $escapeLine['DATE_ACTIVE'] = $escapeLine['DATE_EVENT_CARTE'];
                    foreach ($escapeLine as $key => $value)
                    {
                        $set[] = $key . '="' . $value . '"';
                    }
                    $update = implode(',', $set);
                    $query = 'update PMT_CHEQUES SET ' . $update . ' where CARTE_PORTEUR_ID= "' . $escapeLine['CARTE_PORTEUR_ID'] . '" AND CARTE_STATUT != "Bloquée"';
                    $result = executeQuery($query);
                    if ($rDemande[1]['nb'] > 0)
                    {
                        insertHistoryLogPlugin($rDemande[1]['APP_UID'], $_SESSION['USER_LOGGED'], date('Y-m-d H:i:s'), '0', '', 'Evénement AQOBA AQ_CARTE n°10', 6);
                    }
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
    {
      //TODO
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

function limousinProject_activationCarte($porteurId, $statut, $role_user = 'Bénéficiaires', $televersement = TRUE) {
    $return = TRUE;
    if (!empty($porteurId) && $statut == 1)
    {
        // on regarde si le porteurid fourni est correct et on remonte le cas echeant les infos de la demande
        $exist = limousinProject_getCartePorteurId($porteurId);
        $role_permit = array( "PROCESSMAKER_ADMIN", "Gestionnaires Adéquation", "Encadrants" );
        if ( !empty($exist) && ($exist['USER_ID'] == $_SESSION['USER_LOGGED'] || in_array($role_user, $role_permit)) )
        {
            //on appel le WS d'activation de la carte, on ajoute un groupe utilsateur carte active dans le fe_user Typo3
            //et mise a jour de la table des carte PMT_CHEQUES comme quoi elle est activée
            $active = limousinProject_getActivation($porteurId);
            if ( !empty($active->CODE) && $active->CODE == 'OK' )
            {
                // si $televersement à FALSE, alors c'est une simple activation de carte sans chargement
                if ( $televersement )
                {                    
                    // on appel le WS de televersement des montants
                    if ( !empty($exist['CODE_OPERATION']) )
                    {
                        $sousMontants = limousinProject_getMontantRsxByCodeOper($exist['CODE_OPERATION']);
                        $totalMontants = limousinProject_getMontantByCodeOper($exist['CODE_OPERATION']);
                        $transaction = array( );
                        $transaction = limousinProject_nouvelleTransaction('01', $porteurId, 'C', $totalMontants, $sousMontants);
                        if ( !empty($transaction['errors']) )
                        {
                            //on récupére le label du code erreur de transaction
                            $erreur = limousinProject_getErrorAqoba($transaction['errors'], 'WS201') . ' (code' . $transaction['errors'] . ' du WS201)';
                            $return = 'erreur';
                        }
                    }
                    else
                    {
                        // pas de code opération donc pas de montant connu
                        $erreur = 'Montant et code opération inconnus pour le chargement de la carte.';
                        $return = 'erreur';
                    }
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
                $queryDemande = "SELECT APP_UID FROM PMT_DEMANDES WHERE PORTEUR_ID = '" . $porteurId . "' AND STATUT <> 0 AND STATUT <> 999";
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
    return array( 'success' => $return, 'messageInfo' => $erreur );
}

function limousinProject_getThematiqueFromPartenaire($userId) {
    $thematiquesArray = array();
    $queryTh = "SELECT TH_CINE, TH_SPECTACLE, TH_ACHAT, TH_ARTS, TH_SPORT, IF(TH_ADH_ART = '0', TH_ADH_SPORT, TH_ADH_ART) AS TH_ADH_SPORT
      FROM PMT_PRESTATAIRE where STATUT=1 AND USER_ID ='" . $userId . "'";
    $resultTh = executeQuery($queryTh);
    if (!empty($resultTh[1]))
    {
        $index = 1;
        foreach ($resultTh[1] as $thematiqueFields => $value)
        {
            if ($value == 1)
            {
                $query = 'SELECT CODE_RESEAU AS CODE, LIBELLE AS TYPE
      FROM PMT_THEMATIQUES AS T
      JOIN PMT_RSXTHEMATIQ_MM_PRESTAFIELDTHEMATIQ AS MM
      ON (T.CODE_RESEAU = MM.MM_CODE_RESEAU)
      WHERE PRESTA_NAMEFIELD="' . $thematiqueFields . '"';
                $result = executeQuery($query);
                if (!empty($result))
                {
                    $i = (string) $index;
                    $thematiquesArray[$index] = array('CODE' => $result[1]['CODE'], 'TYPE' => $result[1]['TYPE']);
                    $index++;
                }
            }
        }
    }
    return $thematiquesArray;
}

function limousinProject_getPartenaireInfoForTicket($userId) {
    $partenaireInfo = '';
    $query = "SELECT RAISONSOCIALE, CP, VILLE FROM PMT_PRESTATAIRE WHERE STATUT = 1 AND USER_ID ='".$userId."'";
    $result = executeQuery($query);
    if(isset($result))
    {
        $raison = $result[1]['RAISONSOCIALE'];
        $cp = $result[1]['CP'];
        $ville = $result[1]['VILLE'];
        if(isset($raison))
        {
            $partenaireInfo .= $raison;
        }
        if(isset($cp) && isset($ville))
        {
            $partenaireInfo .= '<br/>'.$cp.' '.$ville;
        }
    }
    return $partenaireInfo;
}

function limousinProject_getPartenaireIdFromPartenaire($userId) {
    $partenaireId = '';
    $query = "SELECT PARTENAIRE_UID FROM PMT_PRESTATAIRE where STATUT=1 AND USER_ID ='".$userId."'";
    $result = executeQuery($query);
    if(isset($result))
    {
        $partenaireId = $result[1]['PARTENAIRE_UID'];
    }
    return $partenaireId;
}

/* * * Renvoie le tableau des sous montants en centimes pour le webservice de transaction ws 201 pour le premier versement
 *
 * @param   integer   $code_oper    code opération de l'offre que l'on souhaite initialiser
 * @return  array     $sousMontants  liste des réseaux et de leurs montants à charger pour l'offre données
 */
function limousinProject_getMontantRsxByCodeOper($code_oper) {

    // INIT
    $sousMontants = array( );

    $query = 'SELECT CODE_RESEAU, MONTANT_INITIAL * 100 AS MONTANT FROM PMT_THEMATIQUES WHERE NUM_OPER = ' . intval($code_oper);
    $result = executeQuery($query);
    if ( !empty($result) )
    {
        foreach ( $result as $reseau )
        {
            $sousMontants[$reseau['CODE_RESEAU']] = intval($reseau['MONTANT']);
        }
    }
    return $sousMontants;
}

/* * * Renvoie le montant total en centimes pour le webservice de transaction ws 201 pour le premier versement
 *
 * @param   integer   $code_oper    code opération de l'offre que l'on souhaite initialiser
 * @return  array     $montantTotal le montants à charger pour l'offre données
 */
function limousinProject_getMontantByCodeOper($code_oper) {

    // INIT
    $montantTotal = 0;
    $query = 'SELECT SUM(MONTANT_INITIAL) * 100 as TOTAL FROM PMT_THEMATIQUES WHERE NUM_OPER = ' . $code_oper;
    $result = executeQuery($query);
    if ( !empty($result) )
    {
       $montantTotal = intval($result[1]['TOTAL']);
    }
    return $montantTotal;
}

function limousinProject_updateEtabNameFromRNE($rne)
{
    $query = "SELECT NOM FROM PMT_ETABLISSEMENT WHERE RNE='".$rne."'";
    $result = executeQuery($query);
    if(!empty($result))
    {
        $nomEtab = $result[1]['NOM'];
    }
    return $nomEtab;
}

function limousinProject_getLibelleFromCodeReseau($codeReseau)
{
    $query = "SELECT LIBELLE FROM PMT_THEMATIQUES WHERE CODE_RESEAU=".$codeReseau;
    $result = executeQuery($query);
    if(!empty($result))
    {
        $libelle = $result[1]['LIBELLE'];
    }
    return $libelle;
}

function limousinProject_actionDeleteCases_callback($item) {
    $appUid = $item['APP_UID'];
    $result = array( );
    $result['check'] = true;
    $sqlGetEtabFromAppUid = "SELECT RNE FROM PMT_ETABLISSEMENT WHERE APP_UID = '" . $appUid . "'";
    $resultGetEtab = executeQuery($sqlGetEtabFromAppUid);
    if ( isset($resultGetEtab[1]['RNE']) && $resultGetEtab[1]['RNE'] != '' )
    {
        $codeEtab = $resultGetEtab[1]['RNE'];
        $sqlCheckEtab = "SELECT NUM_DOSSIER FROM PMT_DEMANDES WHERE RNE = '" . $codeEtab . "' AND STATUT != '0' AND STATUT != '999'";
        $resultCheckEtab = executeQuery($sqlCheckEtab);
        if ( isset($resultCheckEtab[1]['NUM_DOSSIER']) && $resultCheckEtab[1]['NUM_DOSSIER'] != '' )
        {
            $result['check'] = false;
            $result['messageInfo'] = "L'établissement " . $item['NUM_DOSSIER'] . " de code RNE $codeEtab n'a pas été supprimé car il est rattaché à une ou plusieur demandes.";
        }
        else
        {
            $result['messageInfo'] = "L'établissement " . $item['NUM_DOSSIER'] . " a été correctement supprimé.";
        }
    }
    return $result;
}

function limousinProject_getAffilieAqoba($thematique) {

    if(!empty($thematique))
    {
        // recherche des thématiques et champs associé pour le préstataire
        $arrayThematique = array( );
        $queryTh = 'SELECT PRESTA_NAMEFIELD, LIBELLE 
                    FROM PMT_THEMATIQUES AS T
                        INNER JOIN PMT_RSXTHEMATIQ_MM_PRESTAFIELDTHEMATIQ AS R
                        ON (R.MM_CODE_RESEAU = T.CODE_RESEAU)
                    WHERE T.CODE_RESEAU ="' . $thematique . '"';
        $resultTh = executeQuery($queryTh);
        if ( count($resultTh) > 1 )
        {
            foreach ( $resultTh as $liste )
            {
                $arrayThematique[] = $liste['PRESTA_NAMEFIELD'] . ' = 1';
            }
            $whereThematique = ' AND (' . implode(' OR ', $arrayThematique) . ')';
        }
        else
            $whereThematique = ' AND ' . $resultTh[1]['PRESTA_NAMEFIELD'] . ' = 1';

    $path = 'Affiliés - ' . $resultTh[1]['LIBELLE'];
    }
    else
    {
        $whereThematique = '';
        $path = 'Affiliés - Toutes thématiques';
    }
    $queryListePresta = "SELECT `NUM_TPE`, `RAISONSOCIALE` FROM PMT_PRESTATAIRE WHERE upper(TYPE_PRESTA) LIKE 'BANCAIRE' AND STATUT = 1" . $whereThematique;
    $resultListePresta = executeQuery($queryListePresta);
    $headerLine = array('ID commercant','Raison Sociale');
    array_unshift($resultListePresta, $headerLine);
    exportXls('', $resultListePresta, array( ), $path, 'csv');     
}

function limousinProject_getFileEtatSoldeHebdo($path = '/var/tmp/etat_SOLDE_hebdo_', $ext = 'xls', $dateReferente = NULL) {
    // INIT
    $calendrier = array( );
    $path .= date('Y-m-d');

    // On récupère le Dimanche précédent
    $calendrier = convergence_getDateLastWeek(1, $dateReferente);
    $calendrier['File'] = limousinProject_getFileEtatSolde($path, $calendrier['Dimanche'], $ext);
    return $calendrier;
}

function limousinProject_getFileEtatSoldeQuinzaine($appuid, $path = '/var/tmp/solde_CARTES_quinz_', $ext = 'xls', $dateReferente = NULL) {
    // INIT
    $calendrier = array( );
    $path .= date('Y-m-d');
    // On récupère la quinzaine précédente
    $calendrier = convergence_getQuinzaine($dateReferente);
    $fichierSolde = limousinProject_getFileEtatSolde($path, $calendrier['dernierJour'], $ext);
    PMFSendMessage($appuid, 'quentin@oblady.fr', 'quentin@oblady.fr', '', '','Etat Solde', 'mailQuinz.html',array(), array($fichierSolde)); 
    return $fichierSolde;
}
// TODO Modifier en conséquence en fonction de comment sera géré la demandes avec plusieurs dispositif et plusieurs chargement de carte différent et cumulé.
function limousinProject_getFileEtatSolde($path, $endDate, $ext = 'xls', $subTitle = '(Annexe 3)') {

    // INIT
    $pathFile = '';
    $header = array( );
    $datas = array( );
    $footer = array( );

    // Données
    // 1 - On récupère la liste des cartes actives ou bloquées avec leur montant de chargement (sEMIS) à la création selon le CODE_OPERATION
    $requeteTransaction = "SELECT c.CARTE_PORTEUR_ID, c.CARTE_NUM,
                                DATE_FORMAT(c.DATE_CREATION,'%d/%m/%Y') AS DATE_CREATION,
                                DATE_ADD(c.DATE_CREATION, INTERVAL 3 YEAR) AS DATE_VALIDITE,
                                IF(e.MONTANT_EMIS IS NOT NULL, e.MONTANT_EMIS, 0) AS MONTANT_EMIS,
                                CONCAT(
                                    FORMAT(
                                        (IF(sEMIS IS NOT NULL, sEMIS, 0) - IF(sTPE IS NOT NULL, sTPE, 0) - IF(sTPI IS NOT NULL, sTPI, 0)),
                                        2),
                                    ' €') AS SOLDE,
                                IF(t.TPE IS NOT NULL, t.TPE, 0) as TPE,
                                IF(tp.TPI IS NOT NULL, tp.TPI, 0) as TPI, '' AS SOLDE_BLQ, c.DATE_BLOCAGE
                           FROM `PMT_CHEQUES` AS c
                                LEFT JOIN (
                                    SELECT PORTEUR_ID,
                                        CONCAT(FORMAT(SUM(REPLACE(MONTANT,',','.')), 2), ' €') AS TPI,
                                        SUM(REPLACE(MONTANT,',','.')) AS sTPI
                                    FROM PMT_TRANSACTIONS_PRIV
                                    WHERE STR_TO_DATE(DATE_EMISSION, '%d-%m-%Y') < STR_TO_DATE('" . $endDate . "', '%d-%m-%Y')
                                    GROUP BY PORTEUR_ID) AS tp
                                ON (c.CARTE_PORTEUR_ID = tp.PORTEUR_ID)
                                LEFT JOIN (
                                    SELECT ID_PORTEUR,
                                        CONCAT(FORMAT(SUM(MONTANT_NET)/100, 2), ' €') AS TPE,
                                        SUM(MONTANT_NET)/100 AS sTPE
                                    FROM PMT_TRANSACTIONS
                                    WHERE STR_TO_DATE(DATE_EFFECTIVE, '%Y%m%d') < STR_TO_DATE('" . $endDate . "', '%d-%m-%Y')
                                    GROUP BY ID_PORTEUR) AS t
                                ON (c.CARTE_PORTEUR_ID = t.ID_PORTEUR)
                                LEFT JOIN (
                                    SELECT PORTEUR_ID, CODE_OPERATION
                                    FROM PMT_DEMANDES
                                    WHERE STATUT != 0) AS d
                                ON (c.CARTE_PORTEUR_ID = d.PORTEUR_ID)
                                LEFT JOIN (
                                    SELECT NUM_OPER,
                                        CONCAT(FORMAT(SUM(MONTANT_INITIAL), 2), ' €') AS MONTANT_EMIS,
                                        SUM(MONTANT_INITIAL) AS sEMIS
                                    FROM PMT_THEMATIQUES
                                    GROUP BY `NUM_OPER`) AS e
                                ON (e.NUM_OPER = d.CODE_OPERATION)
                           WHERE c.CARTE_STATUT IN('Active','Bloquée')";
    $resultTransaction = executeQuery($requeteTransaction);
    $datas = array_values($resultTransaction);
    unset($resultTransaction);
    if ( !empty($datas) )
    {
        // Début du fichier
        $header['title'] = 'SOLDE des cartes au ' . $endDate;
        $header['subTitle'] = $subTitle;
        $header['colTitle'] = array( "Porteur ID", 'N° de carte', "Date d'émission", 'Date de validité',
            "Montant à l'émission", 'Solde', 'Total Rbt TPE', 'Total Rbt TPI', 'Total Carte annulés',
            "Date d'annulation", 'CTRL'
        );
        // Total
        // 2 - Renseigner les champs CTRL, Solde bloqué si date de blocage. calculer les totaux
        $totalEmis = 0;
        $totalSolde = 0;
        $totalTPE = 0;
        $totalTPI = 0;
        $totalAnomalie = 0;
        foreach ( $datas as $k => $value )
        {
            $datas[$k]['CTRL'] = 0;
            $totalEmis += round(floatval(floatval($value['MONTANT_EMIS'])), 2, PHP_ROUND_HALF_UP);
            $totalTPE += round(floatval(floatval($value['TPE'])), 2, PHP_ROUND_HALF_UP);
            $totalTPI += round(floatval(floatval($value['TPI'])), 2, PHP_ROUND_HALF_UP);
            if ( floatval($value['SOLDE']) < 0 )
            {
                $datas[$k]['CTRL'] = 1;
                $datas[$k]['SOLDE'] = '0 €';
                $totalAnomalie++;
            }
            else
            {
                $totalSolde += round(floatval($value['SOLDE']), 2, PHP_ROUND_HALF_UP);
            }
            // carte bloquée ?
            if ( floatval($value['SOLDE']) >= 0 && !empty($value['DATE_BLOCAGE']) )
                $datas[$k]['SOLDE_BLQ'] = floatval($value['SOLDE']);
        }
        $footer[] = array( 'nbColRight' => 3, 'Total', number_format($totalEmis, 2, '.', ' ') . ' €', number_format($totalSolde, 2, '.', ' ') . ' €', number_format($totalTPE, 2, '.', ' ') . ' €', number_format($totalTPI, 2, '.', ' ') . ' €' );
        $footer[] = array( 'Quantité anomalie', $totalAnomalie );
        $pathFile = phpExcelLibraryProject_exportCompta($header, $datas, $footer, $path, $ext);
    }
    unset($datas);
    return $pathFile;
}

function limousinProject_getFileEtatTransaction($appuid, $path = '/var/tmp/transac_TPE_hebdo_', $ext = 'xls', $dateReferente = NULL) {

    // INIT
    $calendrier = array( ); // contient la période
    $infoReturn = array( ); // le fichier à retourner et informations pour l'inbox
    $header = array( );
    $datas = array( );
    $footer = array( );
    $fields = convergence_getAllAppData($appuid);
    $appNumber = $fields['APP_NUMBER'];
    $pathFile = $path . date('Y-m-d');
    unset($fields);

    // Période
    $calendrier = convergence_getDateLastWeek(1, $dateReferente);

    // Données
    $requeteTransaction = "SELECT IF(CODE_OPERATION = '445', '452', IF(CODE_OPERATION IS NULL, '452', CODE_OPERATION)) AS num_oper,
                                       'TPE' AS typeTCs, r.RAISONSOCIALE AS nom_presta, ID_TRANSACTION AS numTCs,
                                        DATE_FORMAT(STR_TO_DATE(t.DATE_EFFECTIVE,'%Y%m%d'),'%d/%m/%Y') AS date,
                                        CONCAT(FORMAT((MONTANT_NET/100), 2), ' €') AS montant,
                                        ID_COMMERCANT
                                FROM PMT_TRANSACTIONS AS t
                                LEFT JOIN
                                    (SELECT PORTEUR_ID, CODE_OPERATION
                                     FROM PMT_DEMANDES
                                     WHERE STATUT != 0) AS d
                                ON (t.ID_PORTEUR = d.PORTEUR_ID)
                                LEFT JOIN
                                    (SELECT NUM_TPE, RAISONSOCIALE
                                    FROM PMT_PRESTATAIRE
                                    WHERE STATUT != 0) AS r
                                ON (t.ID_COMMERCANT = r.NUM_TPE)
                                WHERE (STR_TO_DATE(t.DATE_EFFECTIVE, '%Y%m%d') > STR_TO_DATE('" . $calendrier['Lundi'] . "', '%d-%m-%Y')
                                      AND STR_TO_DATE(t.DATE_EFFECTIVE, '%Y%m%d') < STR_TO_DATE('" . $calendrier['Dimanche'] . "', '%d-%m-%Y')) OR 1
                                GROUP BY numTCs";
    $resultTransaction = executeQuery($requeteTransaction);
    $datas = array_values($resultTransaction);
    unset($resultTransaction);
    if ( !empty($datas) )
    {
        $arrayRecap = array( );
        $totalByPresta = array( );
        // Début du fichier
        $header['title'] = 'TRANSACTIONS TPE du ' . $calendrier['Lundi'] . ' au ' . $calendrier['Dimanche'];
        $header['subTitle'] = '(Annexe 1)';
        $header['colTitle'] = array( "N d'opération", 'Type de transaction', 'Nom prestataire', 'N de transaction', 'Date de transaction', 'Montant accepté' );
        // Total
        $total = 0;
        foreach ( $datas as $k => $value )
        {
            $montant = round(floatval($value['montant']), 2, PHP_ROUND_HALF_UP);
            $total += $montant;
            // on conserve les informations pour le récapitulatif des remboursements TPE et TPI en fin de mois dans une table
            !empty($totalByPresta[$value['ID_COMMERCANT']]) ? $totalByPresta[$value['ID_COMMERCANT']] += $montant : $totalByPresta[$value['ID_COMMERCANT']] = $montant;
            $arrayRecap[$value['ID_COMMERCANT']] = '(' . $value['num_oper'] . ',' . $appNumber . ',NOW(),"TPE","' . $value['RAISONSOCIALE'] . '",' . $totalByPresta[$value['ID_COMMERCANT']] . ')';
        unset($datas[$k]['ID_COMMERCANT']);
        }
        // on ajjout dans noter table le récapitulatif des remboursements TPE
        $insert = implode(',', $arrayRecap);
        $queryInsert = 'INSERT INTO PMT_TEMP_RECAP_RMB (CODE_OPER, NUM_LOT, DATE_VIR, TYPE_TRANSAC, NOM_PRESTA, MONTANT_RMB) VALUES %s';
        executeQuery(sprintf($queryInsert, $insert));
        $footer[] = array( 'Total', number_format($total, 2, '.', ' ') . ' €' );
        $infoReturn['File'] = phpExcelLibraryProject_exportCompta($header, $datas, $footer, $pathFile, $ext);
        $infoReturn['Montant'] = $total;
        $infoReturn['nbTCs'] = count($datas);
    }
    unset($datas);
    return $infoReturn;
}

function limousinProject_getFileEtatTransactionPriv($appuid, $codeOper, $num_oper, $path = '/var/tmp/transac_TPI_quinz_', $ext = 'xls', $dateReferente = NULL) {

   // INIT
    $calendrier = array( ); // contient la période
    $listeFichier = array( ); // liste des fichier à retourner
    $header = array( );
    $datas = array( );
    $footer = array( );

    // Période de quinzaine précédente
    $calendrier = convergence_getQuinzaine($dateReferente);
    $pathFile = $path . $num_oper . '_' . date('Y-m-d');

    // on récupère la liste des thématiques correspondant au dispositifs
    $queryTh = 'SELECT CODE_RESEAU FROM PMT_THEMATIQUES WHERE NUM_OPER = ' . intval($codeOper);
    $resultTh = executeQuery($queryTh);
    $whereTh = '';
    if ( !empty($resultTh) )
    {
        foreach ( $resultTh as $reseau )
        {
            $thema[] = intval($reseau['CODE_RESEAU']);
        }
        $whereTh = 'AND THEMATIQUE IN(' . implode(',', $thema) . ') ';
    }

    // Données
    $requeteTransaction = "SELECT '" . $num_oper . "' AS num_oper,
                    IF(t.TYPE = 'VOUCHER', 'Voucher', 'TPI') AS typeTCs, r.RAISONSOCIALE AS nom_presta, UID AS numTCs,
                    DATE_FORMAT(STR_TO_DATE(t.DATE_EMISSION,'%d-%m-%Y'),'%d/%m/%Y') AS date,
                    CONCAT(REPLACE(t.MONTANT,',','.'), ' €') AS montant
            FROM PMT_TRANSACTIONS_PRIV AS t

            LEFT JOIN
                (SELECT PARTENAIRE_UID, RAISONSOCIALE
                FROM PMT_PRESTATAIRE
                WHERE STATUT != 0) AS r
            ON (t.CODE_PARTENAIRE = r.PARTENAIRE_UID)
            WHERE (STR_TO_DATE(t.DATE_EMISSION, '%d-%m-%Y') > STR_TO_DATE('" . $calendrier['premierJour'] . "', '%d-%m-%Y')
                  AND STR_TO_DATE(t.DATE_EMISSION, '%d-%m-%Y') < STR_TO_DATE('" . $calendrier['dernierJour'] . "', '%d-%m-%Y')) OR 1
                  " . $whereTh . "
            GROUP BY numTCs";
    $resultTransaction = executeQuery($requeteTransaction);
    $datas = array_values($resultTransaction);
    unset($resultTransaction);
    // Total
    $total = 0;
    $nbTrans = 0;
    if ( !empty($datas) )
    {
        // Début du fichier
        $header['title'] = 'TRANSACTIONS INTERNET du ' . $calendrier['premierJour'] . ' au ' . $calendrier['dernierJour'];
        $header['subTitle'] = '(Annexe 2)';
        $header['colTitle'] = array( "N° d'opération", 'Type de transaction', 'Nom prestataire', 'N° de transaction', 'Date de transaction', 'Montant accepté' );
        foreach ( $datas as $value )
        {
            $total += floatval($value['montant']);
            $nbTrans ++;
        }
        $footer[] = array( 'Total', number_format($total, 2, '.', ' ') . ' €' );            
        $fichierTPI['PATHFILE_TPI_QZ'] = phpExcelLibraryProject_exportCompta($header, $datas, $footer, $pathFile, $ext);
        PMFSendMessage($appuid, 'quentin@oblady.fr', 'quentin@oblady.fr', '', '','Etat TPI : '.$num_oper, 'mailQuinz.html',array(), $fichierTPI['PATHFILE_TPI_QZ']); 
    }
    $fichierTPI['MONTANT_TPI'] = number_format($total, 2, '.', '');
    $fichierTPI['NB_TRANSACTIONS'] = $nbTrans;
    unset($datas);
    return $fichierTPI;
}

function limousinProject_getFileLotDeVirement($appuid, $appNumber, $codeOper, $num_oper, $path = '/var/tmp/Lot_de_Virement_quinz_', $ext = 'xls', $dateReferente = NULL) {
    // INIT
    $calendrier = array( );
    $listeTPI = array( ); // Liste des NUM_DOSSIER des transaction privatif et voucher dont il faut modifier le statut.
    $header = array( );
    $footer = array( );
    $datas = array( );    

    // Période de quinzaine précédente
    $calendrier = convergence_getQuinzaine($dateReferente);
    $periodeLibel = $calendrier['premierJour'] . '-' . $calendrier['dernierJour'];
    $pathFile = $path . $num_oper . '_' . date('Y-m-d');
    // on récupère la liste des thématiques correspondant au dispositifs
    $queryTh = 'SELECT CODE_RESEAU FROM PMT_THEMATIQUES WHERE NUM_OPER = ' . intval($codeOper);
    $resultTh = executeQuery($queryTh);
    $whereTh = '';
    if ( !empty($resultTh) )
    {
        foreach ( $resultTh as $reseau )
        {
            $thema[] = intval($reseau['CODE_RESEAU']);
        }
        $whereTh = 'AND THEMATIQUE IN(' . implode(',', $thema) . ') ';
    }
    // Données
    $requeteTransaction = "SELECT '' AS nolot, '6' AS cdenr, '" . $num_oper . "' AS cdope,
                           '" . date('d/m/y') . "' AS datemvt, '" . date('d/m/y') . "' AS datrec, '" . date('H:i:s') . "' AS hhrec,
                           tp.CODE_PARTENAIRE,
                           '0' AS grppai, '0' AS fcdcn, p.RAISONSOCIALE,
                           '0' AS CDEMT, CODE_GUICHET, CODE_BANQUE, COMPTE, CLE,
                           p.BANQUE AS RMDOM, SUM(REPLACE(MONTANT,',','.')) AS MTVIR,
                           '7779' AS NUMFAC, '0' AS NODPO, '' AS MSGB, '' AS MSGD, '' AS MSGS,
                           CONCAT('" . $appNumber . " ', CODE_PARTENAIRE, ' " . $periodeLibel . "')  AS LIBEL, '' AS LOTOK,
                           UID
                           FROM PMT_TRANSACTIONS_PRIV tp
                           LEFT JOIN (
                                    SELECT CODE_GUICHET, CODE_BANQUE, COMPTE, CLE, RAISONSOCIALE, PARTENAIRE_UID, BANQUE
                                    FROM PMT_PRESTATAIRE
                                    WHERE STATUT != 0) AS p
                           ON (p.PARTENAIRE_UID = tp.CODE_PARTENAIRE)
                           WHERE (STR_TO_DATE(DATE_EMISSION, '%d-%m-%Y') > STR_TO_DATE('" . $calendrier['premierJour'] . "', '%d-%m-%Y')
                             AND STR_TO_DATE (DATE_EMISSION, '%d-%m-%Y') < STR_TO_DATE('" . $calendrier['dernierJour'] . "', '%d-%m-%Y')) OR 1
                             " . $whereTh . "
                           GROUP BY tp.CODE_PARTENAIRE";
    $resultTransaction = executeQuery($requeteTransaction);
    $datas = array_values($resultTransaction);
    unset($resultTransaction);
    $recap_temp = array( );
    if ( !empty($datas) )
    {
        // on conserve les informations pour le récapitulatif des remboursements TPE et TPI en fin de mois dans une table
        foreach ( $datas as $k => $field )
        {
            $recap_temp[] = '(' . $num_oper . ',' . $appNumber . ',NOW(),"TPI","' . $field['RAISONSOCIALE'] . '",' . $field['MTVIR'] . ')';
            //$listeTPI[$field['TYPE']][] = $field['DOSSIER'];
            $listeTPI[] = $field['UID'];
            //unset($datas[$k]['TYPE'], $datas[$k]['DOSSIER']);
            unset($datas[$k]['UID']);
        }      
        $insert = implode(',', $recap_temp);
        $queryInsert = 'INSERT INTO PMT_TEMP_RECAP_RMB (CODE_OPER, NUM_LOT, DATE_VIR, TYPE_TRANSAC, NOM_PRESTA, MONTANT_RMB) VALUES %s';
        executeQuery(sprintf($queryInsert, $insert));
        // Début du fichier pas de header ni footer
        $fichierLotVirement['PATHFILE_LOT_QZ'] = phpExcelLibraryProject_exportCompta($header, $datas, $footer, $pathFile, $ext);
        //PMFSendMessage($appuid, 'quentin@oblady.fr', 'quentin@oblady.fr', '', '','Etat Lot : '.$num_oper, 'mailQuinz.html',array(), $fichierLotVirement['PATHFILE_LOT_QZ']); 
        // Mettre au statut en cours de remboursement les TPI
        $rmbTPI = '(' . implode(',', $listeTPI) . ')';
        $queryUpdate = 'UPDATE PMT_TRANSACTIONS_PRIV SET STATUT = "9" WHERE UID IN %s';
        executeQuery(sprintf($queryUpdate, $rmbTPI));
    }
    $fichierLotVirement['NB_PARTENAIRES'] = count($recap_temp);
    unset($datas);
    return $fichierLotVirement;
}

function limousinProject_processComptaQuinzaine($appuid) {
    $returnData = array();
    $fields = convergence_getAllAppData($appuid);
    $appNumber = $fields['APP_NUMBER'];
    unset($fields);
    $returnData['PATHFILE_SOLDE_QZ'] = limousinProject_getFileEtatSoldeQuinzaine($appuid);
    $listeCodeOper = convergence_getListeOperation();
    foreach ( $listeCodeOper as $codeOper ) {
        // un fichier par dispositif, dans PM 445 correspond à Culture et Sport, car Adequation nous à communiquer le 452 qu'après la mise en prod >(
        ($codeOper == '445') ? $num_oper = '452' : $num_oper = $codeOper;
        $returnData[$num_oper]['TPI'] = limousinProject_getFileEtatTransactionPriv($appuid, $codeOper, $num_oper);
        $returnData[$num_oper]['LOT'] = limousinProject_getFileLotDeVirement($appuid, $appNumber, $codeOper, $num_oper);
        // Insertion dans la PM Table PMT_LOT_VIREMENT_OPER par code opération
        $queryInsertLotVirementOper = "INSERT INTO PMT_LOT_VIREMENT_OPER (NUM_DOSSIER, CODE_OPER, STATUT, DATE_REMBOURSEMENT, NB_PARTENAIRES, NB_TRANSACTIONS, MONTANT_TPI, PATHFILE_TPI_QZ, PATHFILE_LOT_QZ) ".
                                " VALUES (".$appNumber.", ".$num_oper.", 17, null, ".$returnData[$num_oper]['LOT']['NB_PARTENAIRES'].", ".$returnData[$num_oper]['TPI']['NB_TRANSACTIONS'].", ".$returnData[$num_oper]['TPI']['MONTANT_TPI'].", '".$returnData[$num_oper]['TPI']['PATHFILE_TPI_QZ']."', '".$returnData[$num_oper]['LOT']['PATHFILE_LOT_QZ']."') ";
        executeQuery($queryInsertLotVirementOper);
    }
    return $returnData;
}

function limousinProject_getFileEtatRecapRmb($appuid, $path = '/var/tmp/recap_mensuel_', $ext = 'xls', $dateReferente = NULL) {
   // INIT
    $calendrier = array( ); // contient la période
    $listeFichier = array( ); // liste des fichier à envoyer par mail
    $fields = convergence_getAllAppData($appuid);
    $appNumber = $fields['APP_NUMBER'];
    unset($fields);
    $contenuMail = '';
    $moisPrecedent = date('m/Y', strtotime('-1 month'));
    // on récupère la liste des dispositifs, un code opération par dispositif dans Convergence
    $listeCodeOper = convergence_getListeOperation();
    foreach ( $listeCodeOper as $codeOper )
    {
        $header = array( );
        $datas = array( );
        $footer = array( );
        // un fichier par dispositif, dans PM 445 correspond à Culture et Sport, car Adequation nous à communiquer le 452 qu'après la mise en prod >(
        ($codeOper == '445') ? $num_oper = '452' : $num_oper = $codeOper;
        $pathFile = $path . $num_oper . '_' . date('Y-m-d');
        // Données
        $requeteRecap = "SELECT CODE_OPER, NUM_LOT, DATE_FORMAT(DATE_VIR, '%d/%m/%Y') AS DATE_VIR, TYPE_TRANSAC, NOM_PRESTA, MONTANT_RMB FROM PMT_TEMP_RECAP_RMB WHERE CODE_OPER = '".$num_oper."'";
        $resultRecap = executeQuery($requeteRecap);
        $datas = array_values($resultRecap);
        unset($resultRecap);
        // Total
        $total = 0;
        $nbTPE = 0;
        $totalTPE = 0;
        $nbTPI = 0;
        $totalTPI = 0;
        if ( !empty($datas) )
        {
            // Début du fichier
            $header['title'] = 'Virements émis du mois '.$moisPrecedent;
            $header['subTitle'] = ' ';
            $header['colTitle'] = array( "N° d'opération", 'N° lot de virement', 'date virement', 'type transaction', 'Nom prestataire', 'Montant remboursé' );
            foreach ( $datas as $key => $value )
            {
                $typeTrans = $value['TYPE_TRANSAC'];
                $montantRmb = $value['MONTANT_RMB'];
                $total += floatval($montantRmb);
                switch($typeTrans)
                {
                    case 'TPI' : $totalTPI += floatval($montantRmb); $nbTPI++; break;
                    case 'TPE' : $totalTPE += floatval($montantRmb); $nbTPE++; break;
                }
                $datas[$key]['MONTANT_RMB'] = number_format($value['MONTANT_RMB'], 2, '.', ' '). ' €';
            }
            $footer[] = array( 'nbColRight' => 1, 'Total TPE', number_format($totalTPE, 2, '.', ' ') . ' €');
            $footer[] = array( 'nbColRight' => 1, 'Total TPI', number_format($totalTPI, 2, '.', ' ') . ' €');
            $footer[] = array( 'Total à remb', number_format($total, 2, '.', ' ') . ' €');
            $listeFichier[] = phpExcelLibraryProject_exportCompta($header, $datas, $footer, $pathFile, $ext);
            // Insertion dans la PM Table PMT_RECAP_MENSUEL_OPER par code opération
            $queryInsertRecapOper = "INSERT INTO PMT_RECAP_MENSUEL_OPER (NUM_DOSSIER, CODE_OPER, STATUT, DATE_PAIEMENT, MONTANT_MENSUEL, NB_TRANS_TPE, MONTANT_TPE, NB_TRANS_TPI, MONTANT_TPI, PATHFILE_RECAP_MENSUEL) ".
                                    " VALUES (".$appNumber.", ".$num_oper.", 17, null, ".$total.", ".$nbTPE.", ".$totalTPE.", ".$nbTPI.", ".$totalTPI.", '".end($listeFichier)."') ";
            executeQuery($queryInsertRecapOper);
            $contenuMail = "Le montant total à facturer au CR Limousin pour le mois du ".$moisPrecedent." sera de ".number_format($total, 2, '.', ' '). ' €'." pour le code opération ".$num_oper."<br />";
        }
    }
    unset($datas);
    // Vidage de la table PMT_TEMP_RECAP_RMB après utilisation
    $queryTruncateTempRecapRmb = "DELETE FROM PMT_TEMP_RECAP_RMB"; // TRUNCATE TABLE PMT_TEMP_RECAP_RMB doesn't works ... -_-'
    executeQuery($queryTruncateTempRecapRmb);
    // Récupération des soldes de carte à la quinzaine pour le récap mensuel
    $soldesQZ = limousinProject_getPathfileSoldeForRecapMensuel();
    // Envoi du mail
    $listeFichier = array_merge($listeFichier, array_values($soldesQZ));
    $aFields = array('ContenuMail' => $contenuMail);
    PMFSendMessage($appuid, 'quentin@oblady.fr', 'quentin@oblady.fr', '', '','Mise à jour du compte de cantonnement BeLim ', 'mailMensuel.html',$aFields, $listeFichier);
    // On retourne les soldes pour affectation des variables ProcessMaker et champs de la Report Table
    return $soldesQZ;
}

function limousinProject_getPathfileSoldeForRecapMensuel()
{
    $queryPathfileSolde = "SELECT PATHFILE_SOLDE_QZ FROM PMT_LOT_VIREMENT ORDER BY DATE_LANCEMENT DESC LIMIT 2";
    $resultPathfileSolde = executeQuery($queryPathfileSolde);
    if(!empty($resultPathfileSolde))
    {
        if(!empty($resultPathfileSolde[1]) && !empty($resultPathfileSolde[2]))
        {
            $pathFileSolde['PATHFILE_SOLDE_QZ1'] = $resultPathfileSolde[2]['PATHFILE_SOLDE_QZ'];
            $pathFileSolde['PATHFILE_SOLDE_QZ2'] = $resultPathfileSolde[1]['PATHFILE_SOLDE_QZ'];
        }
    }
    return $pathFileSolde;
}
?>
