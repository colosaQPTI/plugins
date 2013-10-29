<?php

G::loadClass('pmFunctions');

global $RBAC;

switch($_POST['action'])
{ 
  case 'saveUser' :
    try { 
      $form = $_POST;
	  
      if ( isset($_POST['USR_UID'])) {
        $form['USR_UID'] = $_POST['USR_UID'];
      }
      else {
        $form['USR_UID'] = '';
      } 

      
      if (!isset($form['USR_NEW_PASS'])) {
        $form['USR_NEW_PASS'] = '';
      }
      $sw = 0;
      $passwordT = md5($form['USR_NEW_PASS']);
      if(isset($form['USR_NEW_PASS']) && $form['USR_NEW_PASS'] == ''){
       $query = "SELECT * FROM USERS WHERE USR_UID = '".$_SESSION['USER_LOGGED']."' ";
       $resPassword = executeQuery($query,"rbac");
       if(sizeof($resPassword)){
        $passwordT = $resPassword[1]['USR_PASSWORD'];
        }
        $sw = 1;
      }

      if ($form['USR_NEW_PASS'] != '') {
        //$form['USR_PASSWORD'] = md5($form['USR_NEW_PASS']);
        $form['USR_PASSWORD'] = $passwordT;
      }
      
        $oUser                    = new Users();
        $defaultDatas             = $oUser->loadDetailed($form['USR_UID']);
        
        $sUserUID=$aData['USR_UID'] = $form['USR_UID'];
        $aData['USR_USERNAME']      = ($form['USR_USERNAME']) ? $form['USR_USERNAME'] : $defaultDatas['USR_USERNAME'];
		
        if (isset($form['USR_PASSWORD'])) {

          if ($form['USR_PASSWORD'] != '') {
            $aData['USR_PASSWORD'] = $form['USR_PASSWORD'];
            require_once 'classes/model/UsersProperties.php';
            $oUserProperty = new UsersProperties();
            $aUserProperty = $oUserProperty->loadOrCreateIfNotExists($form['USR_UID'], array('USR_PASSWORD_HISTORY' => serialize(array(md5($form['USR_PASSWORD'])))));

            $memKey = 'rbacSession' . session_id();
            $memcache = & PMmemcached::getSingleton(defined('SYS_SYS') ? SYS_SYS : '');

            if ( ($RBAC->aUserInfo = $memcache->get($memKey)) === false ) {
              $RBAC->loadUserRolePermission($RBAC->sSystem, $_SESSION['USER_LOGGED'] );
              $memcache->set( $memKey, $RBAC->aUserInfo, PMmemcached::EIGHT_HOURS );
            }
            
            if( $RBAC->aUserInfo[ 'PROCESSMAKER' ]['ROLE']['ROL_CODE']=='PROCESSMAKER_ADMIN'){
              $aUserProperty['USR_LAST_UPDATE_DATE'] = date('Y-m-d H:i:s');
              $aUserProperty['USR_LOGGED_NEXT_TIME'] = 1;
              $oUserProperty->update($aUserProperty);
              
            }

            //$aErrors = $oUserProperty->validatePassword($form['USR_NEW_PASS'], $aUserProperty['USR_LAST_UPDATE_DATE'], 0);
            if($sw == 0)
              $aErrors = $oUserProperty->validatePassword($form['USR_NEW_PASS'], $aUserProperty['USR_LAST_UPDATE_DATE'], 0);

            if (count($aErrors) > 0) {
              $sDescription = G::LoadTranslation('ID_POLICY_ALERT').':,';
              foreach ($aErrors as $sError)  {
                switch ($sError) {
                  case 'ID_PPP_MINIMUN_LENGTH':
                    $sDescription .= ' - ' . G::LoadTranslation($sError).': ' . PPP_MINIMUN_LENGTH . ',';
                  break;
                  case 'ID_PPP_MAXIMUN_LENGTH':
                    $sDescription .= ' - ' . G::LoadTranslation($sError).': ' . PPP_MAXIMUN_LENGTH . ',';
                  break;
                  case 'ID_PPP_EXPIRATION_IN':
                    $sDescription .= ' - ' . G::LoadTranslation($sError).' ' . PPP_EXPIRATION_IN . ' ' . G::LoadTranslation('ID_DAYS') . ',';
                  break;
                  default:
                    $sDescription .= ' - ' . G::LoadTranslation($sError).',';
                  break;
                }
              }
              $sDescription .=  ''.G::LoadTranslation('ID_PLEASE_CHANGE_PASSWORD_POLICY');
              $result->success = false;
              $result->msg = $sDescription;
              print(G::json_encode($result));
              die;


            }
            $aHistory      = unserialize($aUserProperty['USR_PASSWORD_HISTORY']);
            if (!is_array($aHistory)) {
              $aHistory = array();
            }
            if (!defined('PPP_PASSWORD_HISTORY')) {
              define('PPP_PASSWORD_HISTORY', 0);
            }
            if (PPP_PASSWORD_HISTORY > 0) {
              //it's looking a password igual into aHistory array that was send for post in md5 way
              $c  = 0;
              $sw = 1;
              while (count($aHistory) >= 1 && count($aHistory) > $c && $sw ){
               if (strcmp(trim($aHistory[$c]), trim($form['USR_PASSWORD'])) == 0){
                 $sw = 0;
               }
               $c++;
              }
              if ($sw == 0) {
                $sDescription = G::LoadTranslation('ID_POLICY_ALERT').':<br /><br />';
                $sDescription .= ' - ' . G::LoadTranslation('PASSWORD_HISTORY').': ' . PPP_PASSWORD_HISTORY . '<br />';
                $sDescription .= '<br />' . G::LoadTranslation('ID_PLEASE_CHANGE_PASSWORD_POLICY').'';
                $result->success = false;
                $result->msg   = $sDescription;
                print(G::json_encode($result));
                die();
              }

              if (count($aHistory) >= PPP_PASSWORD_HISTORY) {
                $sLastPassw = array_shift($aHistory);
              }
              $aHistory[] = $form['USR_PASSWORD'];
            }
            $aUserProperty['USR_LAST_UPDATE_DATE'] = date('Y-m-d H:i:s');
            $aUserProperty['USR_LOGGED_NEXT_TIME'] = 1;
            $aUserProperty['USR_PASSWORD_HISTORY'] = serialize($aHistory);
            $oUserProperty->update($aUserProperty);
          }
        }
        $aData['USR_FIRSTNAME']   = ($form['USR_FIRSTNAME']) ? $form['USR_FIRSTNAME'] : $defaultDatas['USR_FIRSTNAME'];
        $aData['USR_LASTNAME']    = ($form['USR_LASTNAME']) ? $form['USR_LASTNAME'] : $defaultDatas['USR_LASTNAME'];
        $aData['USR_EMAIL']       = ($form['USR_EMAIL']) ? $form['USR_EMAIL'] : $defaultDatas['USR_EMAIL'];
        $aData['USR_UPDATE_DATE'] = date('Y-m-d H:i:s');
          $RBAC->updateUser($aData);
          

        require_once 'classes/model/Users.php';
        $oUser = new Users();
        $oUser->update($aData);
          
    // Typo3 Part

    // Get the group name
    $query = "SELECT * FROM GROUP_USER WHERE USR_UID = '".$_SESSION['USER_LOGGED']."' ";
    
    $resultUser = executeQuery($query);
    $groupId='';
    if(isset($resultUser))
        $groupId = $resultUser[1]['GRP_UID'];
    // End Get the group name
	
    $urlTypo3 = 'http://' . HostName . '/';
            ini_set("soap.wsdl_cache_enabled", "0");
    $hostTypo3 = $urlTypo3.'typo3conf/ext/pm_webservices/serveur.php?wsdl';    
    $pfServer = new SoapClient($hostTypo3);
    $key = rand();
    
    /////////////////////////////

    $passwordT = md5($_POST['USR_NEW_PASS']);
    if(isset($_POST['USR_NEW_PASS']) && $_POST['USR_NEW_PASS'] == ''){
       $query = "SELECT * FROM USERS WHERE USR_UID = '".$_SESSION['USER_LOGGED']."' ";
       $resPassword = executeQuery($query,"rbac");
       if(sizeof($resPassword)){
        $passwordT = $resPassword[1]['USR_PASSWORD'];
       }
    }

    $ret = $pfServer->createAccount(array(
    'username' => $aData['USR_USERNAME'],
    'password' => $passwordT,
    'email' => $aData['USR_EMAIL'],
    'lastname' => $aData['USR_LASTNAME'],
    'firstname' => $aData['USR_FIRSTNAME'],
    'key' => $key,
    'pmid' => $form['USR_UID'],
    'usergroup' => $groupId,
    'cHash' => md5($aData['USR_USERNAME'].'*'.$aData['USR_LASTNAME'].'*'.$aData['USR_FIRSTNAME'].'*'.$key)));
    ////////////////////////////

    // End Typo3
    
      $result->success = true;
      $result->msg = 'Votre compte a correctement &eacute;t&eacute; mis &agrave; jour.';
      print(G::json_encode($result));
    }catch (Exception $e) {
      $result->success = false;
      $result->error   = $e->getMessage();
      print(G::json_encode($result));
    }
    
    break;

  case 'userData':
    require_once 'classes/model/Users.php';
    $_SESSION['CURRENT_USER'] = $_POST['USR_UID'];
    $oUser                    = new Users();
    $aFields                  = $oUser->loadDetailed($_POST['USR_UID']);

   
    #verifying if it has any preferences on the configurations table
    G::loadClass('configuration');
    $oConf = new Configurations;
    $oConf->loadConfig($x, 'USER_PREFERENCES', '', '', $_SESSION['USER_LOGGED'], '');

    $aFields['PREF_DEFAULT_MENUSELECTED']='';
    $aFields['PREF_DEFAULT_CASES_MENUSELECTED']='';
    if( sizeof($oConf->Fields) > 0){ #this user has a configuration record
      $aFields['PREF_DEFAULT_LANG']               = $oConf->aConfig['DEFAULT_LANG'];
      $aFields['PREF_DEFAULT_MENUSELECTED']       = isset($oConf->aConfig['DEFAULT_MENU']) ? $oConf->aConfig['DEFAULT_MENU']: '';
      $aFields['PREF_DEFAULT_CASES_MENUSELECTED'] = isset($oConf->aConfig['DEFAULT_CASES_MENU']) ? $oConf->aConfig['DEFAULT_CASES_MENU']: '';
    } else {
      switch($RBAC->aUserInfo['PROCESSMAKER']['ROLE']['ROL_CODE']){
        case 'PROCESSMAKER_ADMIN':
          $aFields['PREF_DEFAULT_MENUSELECTED'] = 'PM_SETUP';
          break;
        case 'PROCESSMAKER_OPERATOR':
          $aFields['PREF_DEFAULT_MENUSELECTED'] = 'PM_CASES';
          break;

      }
      $aFields['PREF_DEFAULT_LANG'] = SYS_LANG;
    }
	$menuSelected = '';
    
    if ($aFields['PREF_DEFAULT_MENUSELECTED'] != '') {
        foreach ( $RBAC->aUserInfo['PROCESSMAKER']['PERMISSIONS'] as $permission ) {
            if ($aFields['PREF_DEFAULT_MENUSELECTED']==$permission['PER_CODE']) {
                switch ($permission['PER_CODE']) {
                    case 'PM_USERS' :
                    case 'PM_SETUP' :
                        $menuSelected = strtoupper(G::LoadTranslation('ID_SETUP'));
                        break;
                    case 'PM_CASES' :
                        $menuSelected = strtoupper(G::LoadTranslation('ID_CASES'));
                        break;
                    case 'PM_FACTORY' :
                        $menuSelected = strtoupper(G::LoadTranslation('ID_APPLICATIONS'));
                        break;
                    case 'PM_DASHBOARD':
                        $menuSelected = strtoupper(G::LoadTranslation('ID_DASHBOARD'));
                        break;
                }
            }
        }
    }


    $aFields['MENUSELECTED_NAME'] =  $menuSelected;

    $oMenu = new Menu();
    $oMenu->load('cases');
    $casesMenuSelected = '';

    if ($aFields['PREF_DEFAULT_CASES_MENUSELECTED'] != ''){
      foreach($oMenu->Id as $i => $item){

        if ($aFields['PREF_DEFAULT_CASES_MENUSELECTED'] == $item)
          $casesMenuSelected =$oMenu->Labels[$i];
      }
    }

    $aFields['CASES_MENUSELECTED_NAME'] = $casesMenuSelected;

   $result->success = true;
    $result->user    = $aFields;

    print(G::json_encode($result));
    break;

  case 'testPassword';
    require_once 'classes/model/UsersProperties.php';
    $oUserProperty = new UsersProperties();

    $aFields = array();
    $color = '';
    $img = '';
    $dateNow = date('Y-m-d H:i:s');
    $aErrors = $oUserProperty->validatePassword($_POST['PASSWORD_TEXT'], $dateNow, $dateNow);

    if (!empty($aErrors)) {
      $img = '/images/delete.png';
      $color = 'red';
      if (!defined('NO_DISPLAY_USERNAME')) {
        define('NO_DISPLAY_USERNAME', 1);
      }
      $aFields = array();
      $aFields['DESCRIPTION'] = G::LoadTranslation('ID_POLICY_ALERT').':<br />';

      foreach ($aErrors as $sError)  {
        switch ($sError) {
          case 'ID_PPP_MINIMUM_LENGTH':
            $aFields['DESCRIPTION'] .= ' - ' . G::LoadTranslation($sError).': ' . PPP_MINIMUM_LENGTH . '<br />';
            $aFields[substr($sError, 3)] = PPP_MINIMUM_LENGTH;
          break;
          case 'ID_PPP_MAXIMUM_LENGTH':
            $aFields['DESCRIPTION'] .= ' - ' . G::LoadTranslation($sError).': ' . PPP_MAXIMUM_LENGTH . '<br />';
            $aFields[substr($sError, 3)] = PPP_MAXIMUM_LENGTH;
          break;
          case 'ID_PPP_EXPIRATION_IN':
            $aFields['DESCRIPTION'] .= ' - ' . G::LoadTranslation($sError).' ' . PPP_EXPIRATION_IN . ' ' . G::LoadTranslation('ID_DAYS') . '<br />';
            $aFields[substr($sError, 3)] = PPP_EXPIRATION_IN;
          break;
          default:
            $aFields['DESCRIPTION'] .= ' - ' . G::LoadTranslation($sError).'<br />';
            $aFields[substr($sError, 3)] = 1;
          break;
        }
      }

      $aFields['DESCRIPTION'] .= G::LoadTranslation('ID_PLEASE_CHANGE_PASSWORD_POLICY') . '</span>';
      $aFields['STATUS'] = false;
    } else {
      $color = 'green';
      $img = '/images/dialog-ok-apply.png';
      $aFields['DESCRIPTION'] = G::LoadTranslation('ID_PASSWORD_COMPLIES_POLICIES') . '</span>';
      $aFields['STATUS'] = true;
    }
    $span = '<span style="color: ' . $color . '; font: 9px tahoma,arial,helvetica,sans-serif;">';
    $gif = '<img width="13" height="13" border="0" src="' . $img . '">';
    $aFields['DESCRIPTION'] =  $span . $gif . $aFields['DESCRIPTION'];
    print(G::json_encode($aFields));
    break;
}


function userTypo3Update(){

  // Typo3 Part

    // Get the group name
    $query = "SELECT * FROM GROUP_USER WHERE  USR_UID = '".$_SESSION['USER_LOGGED']."' ";
    $result = executeQuery($query);
    $groupId='';
    if(isset($result))
        $groupId = $result[1]['GRP_UID'];
    // End Get the group name

    $urlTypo3 = 'http://' . HostName . '/';
    ini_set("soap.wsdl_cache_enabled", "0");
    $hostTypo3 = $urlTypo3.'typo3conf/ext/pm_webservices/serveur.php?wsdl';    
    $pfServer = new SoapClient($hostTypo3);
    $key = rand();    
    $ret = $pfServer->createAccount(array(
    'username' => $_POST['USR_USERNAME'],
    'password' => md5($_POST['USR_NEW_PASS']),
    'email' => $_POST['USR_EMAIL'],
    'lastname' => $_POST['USR_LASTNAME'],
    'firstname' => $_POST['USR_FIRSTNAME'],
    'key' => $key,
    'pmid' => $_POST['USR_UID'],
    'usergroup' => $groupId,
    'cHash' => md5($_POST['USR_USERNAME'].'*'.$_POST['USR_LASTNAME'].'*'.$_POST['USR_FIRSTNAME'].'*'.$key)));

}