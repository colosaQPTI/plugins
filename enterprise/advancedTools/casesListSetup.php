<?php

global $RBAC;
if($RBAC->userCanAccess('PM_SETUP') != 1 || $RBAC->userCanAccess('PM_SETUP_ADVANCE') != 1){
  G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels');
  //G::header('location: ../login/login');
  die;
}
  // validating if the template files for the cases setup has been already copied
  if (!file_exists(PATH_HOME . 'engine' . PATH_SEP . 'templates' . PATH_SEP . 'cases' . PATH_SEP . 'casesListSetup.js')){
    copy(PATH_PLUGINS . 'enterprise' . PATH_SEP . 'advancedTools' . PATH_SEP . 'casesListSetup.js'  , PATH_HOME . 'engine' . PATH_SEP . 'templates' . PATH_SEP . 'cases' . PATH_SEP . 'casesListSetup.js'  );
  }
  if (!file_exists(PATH_HOME . 'engine' . PATH_SEP . 'templates' . PATH_SEP . 'cases' . PATH_SEP . 'casesListSetup.html')){
    copy(PATH_PLUGINS . 'enterprise' . PATH_SEP . 'advancedTools' . PATH_SEP . 'casesListSetup.html', PATH_HOME . 'engine' . PATH_SEP . 'templates' . PATH_SEP . 'cases' . PATH_SEP . 'casesListSetup.html' );
  }
  
  $oHeadPublisher =& headPublisher::getSingleton();
    	
  $oHeadPublisher->assignNumber( 'pageSize',     20 ); //sending the page size

  $oHeadPublisher->addExtJsScript( 'cases/casesListSetup', true );    //adding a javascript file .js

  $availableFields = array();
  $oHeadPublisher->assignNumber( 'availableFields', G::json_encode($availableFields) );

  $TRANSLATIONS = new stdClass();
  $TRANSLATIONS->LABEL_EMPTY_PMTABLE       = G::LoadTranslation('ID_EMPTY_PMTABLE');
  $TRANSLATIONS->LABEL_HEADER_NUMBER       = G::LoadTranslation('ID_HEADER_NUMBER');
  $TRANSLATIONS->LABEL_HEADER_FIELD_NAME   = G::LoadTranslation('ID_HEADER_FIELD_NAME');
  $TRANSLATIONS->LABEL_HEADER_FIELD_TYPE   = G::LoadTranslation('ID_HEADER_FIELD_TYPE');
  $TRANSLATIONS->LABEL_HEADER_LABEL        = G::LoadTranslation('ID_HEADER_LABEL');
//  $TRANSLATIONS->LABEL_HEADER_WIDTH        = G::LoadTranslation('ID_HEADER_WIDTH');
  $TRANSLATIONS->LABEL_HEADER_ALIGN        = G::LoadTranslation('ID_HEADER_ALIGN');
  $TRANSLATIONS->LABEL_SELECTED_FIELD      = G::LoadTranslation('ID_SELECTED_FIELD');
  $TRANSLATIONS->LABEL_AVAILABLE_FIELDS    = G::LoadTranslation('ID_AVAILABLE_FIELDS');
  $TRANSLATIONS->LABEL_CASES_LIST_FIELDS   = G::LoadTranslation('ID_CASES_LIST_FIELDS');
  $TRANSLATIONS->LABEL_INFO                = G::LoadTranslation('ID_INFO');
  $TRANSLATIONS->LABEL_SAVED               = G::LoadTranslation('ID_SAVED');
  $TRANSLATIONS->LABEL_TITLE_INBOX         = G::LoadTranslation('ID_TITLE_INBOX');
  $TRANSLATIONS->LABEL_TITLE_DRAFT         = G::LoadTranslation('ID_TITLE_DRAFT');
  $TRANSLATIONS->LABEL_TITLE_PARTICIPATED  = G::LoadTranslation('ID_TITLE_PARTICIPATED');
  $TRANSLATIONS->LABEL_TITLE_UNASSIGNED    = G::LoadTranslation('ID_TITLE_UNASSIGNED');
  $TRANSLATIONS->LABEL_TITLE_PAUSED     = G::LoadTranslation('ID_TITLE_PAUSED');
  $TRANSLATIONS->LABEL_TITLE_COMPLETED  = G::LoadTranslation('ID_TITLE_COMPLETED');
  $TRANSLATIONS->LABEL_TITLE_CANCELLED  = G::LoadTranslation('ID_TITLE_CANCELLED');
  $TRANSLATIONS->LABEL_PM_TABLE         = G::LoadTranslation('ID_PM_TABLE');
  $TRANSLATIONS->LABEL_ROWS_PER_PAGE    = G::LoadTranslation('ID_ROWS_PER_PAGE');
  $TRANSLATIONS->LABEL_DATE_FORMAT      = G::LoadTranslation('ID_DATE_FORMAT');
  $TRANSLATIONS->LABEL_RESET            = G::LoadTranslation('ID_RESET');
  $TRANSLATIONS->LABEL_APPLY_CHANGES    = G::LoadTranslation('ID_APPLY_CHANGES');

  $oHeadPublisher->assign( 'TRANSLATIONS',   $TRANSLATIONS); //translations



  $oHeadPublisher->addContent( 'cases/casesListSetup'); //adding a html file  .html.
//  $oHeadPublisher->addContent( PATH_PLUGINS . 'enterprise/advancedTools/casesListSetup'); //adding a html file  .html.

  G::RenderPage('publish', 'extJs');
