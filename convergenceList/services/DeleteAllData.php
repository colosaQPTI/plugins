<?php

// Script Delete Cases
G::loadClass ( 'pmFunctions' );
G::LoadClass ( "case" );
die;

function cleanAllCases()
{
    $query1 = "DELETE FROM wf_" . SYS_SYS . ".APPLICATION ";
    $apps1 = executeQuery ( $query1 );
    
    $query2 = "DELETE FROM wf_" . SYS_SYS . ".APP_DELAY ";
    $apps2 = executeQuery ( $query2 );
    
    $query3 = "DELETE FROM wf_" . SYS_SYS . ".APP_DELEGATION ";
    $apps3 = executeQuery ( $query3 );
    
    $query4 = "DELETE FROM wf_" . SYS_SYS . ".APP_DOCUMENT ";
    $apps4 = executeQuery ( $query4 );
    
    $query5 = "DELETE FROM wf_" . SYS_SYS . ".APP_MESSAGE ";
    $apps5 = executeQuery ( $query5 );
	
    $query6 = "DELETE FROM wf_" . SYS_SYS . ".APP_OWNER ";
    $apps6 = executeQuery ( $query6 );
	
    $query7 = "DELETE FROM wf_" . SYS_SYS . ".APP_THREAD ";
    $apps7 = executeQuery ( $query7 );
    
    $query8 = "DELETE FROM wf_" . SYS_SYS . ".SUB_APPLICATION ";
    $apps8 = executeQuery ( $query8 );
    
    $query9 = "DELETE FROM wf_" . SYS_SYS . ".CONTENT WHERE CON_CATEGORY LIKE 'APP_%' ";
    $apps9 = executeQuery ( $query9 );
    
    $query10 = "DELETE FROM wf_" . SYS_SYS . ".APP_EVENT ";
    $apps10 = executeQuery ( $query10 );
    
    $query11 = "DELETE FROM wf_" . SYS_SYS . ".APP_CACHE_VIEW ";
    $apps11 = executeQuery ( $query11 );
    
    $query12 = "DELETE FROM wf_" . SYS_SYS . ".APP_HISTORY ";
    $apps12 = executeQuery ( $query12 );
    
    // notes cases
    $query13 = "DELETE FROM wf_" . SYS_SYS . ".APP_NOTES ";
    $apps13 = executeQuery ( $query13 );

    // control login user
    $query14 = "DELETE FROM wf_" . SYS_SYS . ".LOGIN_LOG";
    $apps14 = executeQuery ( $query14 );

    tablesFrancia(); 
}

function tablesFrancia()
{
    //joins -> inbox -roles 
    $query1 = "DELETE FROM wf_" .SYS_SYS . ".PMT_INBOX_JOIN";
    $apps1 = executeQuery ( $query1 );

    // pmtable actions 
    /*$query2 = "DELETE FROM wf_" .SYS_SYS . ".PMT_ACTIONS";
    $apps1 = executeQuery($query2);*/

    // PMTABLE CHEQUES 
    $query3 = "DELETE FROM wf_" .SYS_SYS . ".PMT_CHEQUES";
    $apps3 = executeQuery ( $query3 );

    // pmtable PMT_PERMISSION_OPTIONS (save options of rol )
    /*$query4 = "DELETE FROM wf_" . SYS_SYS . ".PMT_PERMISSION_OPTIONS";
    $apps4 = executeQuery ( $query4);    */

    // pmtable PMT_INBOX_ACTIONS (actions for inbox)
    /*$query5 = "DELETE FROM wf_" . SYS_SYS . ".PMT_INBOX_ACTIONS";
    $apps5 = executeQuery($query5);*/    

    // pmtable PMT_INBOX_FIELDS (fields of inbox )
    /*$query6 = "DELETE FROM wf_" . SYS_SYS . ".PMT_INBOX_FIELDS";
    $apps6 = executeQuery ( $query6 );*/

    // pmtable PMT_INBOX_ROLES 
    /*$query7 = "DELETE FROM wf_" . SYS_SYS . ".PMT_INBOX_ROLES";
    $apps7 = executeQuery ( $query7 );*/

    // pmtable PMT_INBOX_FILTERS (filters of inbox)
    /*$query8 = "DELETE FROM wf_" . SYS_SYS . ".PMT_INBOX_FILTERS";
    $apps8 = executeQuery ( $query8 );*/

    // pmtable PMT_INBOX
    /*$query9 = "DELETE FROM wf_" . SYS_SYS . ".PMT_INBOX";
    $apps9 = executeQuery ( $query9 );*/

    // pmtable PMT_HISTORY_LOG
    $query10 = "DELETE FROM wf_" . SYS_SYS . ".PMT_HISTORY_LOG";
    $apps10 = executeQuery ( $query10 );    

    // pmtable PMT_INBOX_WHERE (where of inbox)
    /*$query11 = "DELETE FROM wf_" . SYS_SYS . ".PMT_INBOX_WHERE";
    $apps11 = executeQuery ( $query11 );    */

    //(configuration AS400) 
    // pmtable PMT_CONFIG_DEDOUBLONAGE 

    /*$query12 = "DELETE FROM wf_" . SYS_SYS . ".PMT_CONFIG_DEDOUBLONAGE";
    $apps12 = executeQuery ( $query12 );   

    // pmtable PMT_COLUMN_AS400        
    $query13 = "DELETE FROM wf_" . SYS_SYS . ".PMT_COLUMN_AS400";
    $apps13 = executeQuery ( $query13 );  

    // pmtable PMT_AS400_CONFIG
    $query14 = "DELETE FROM wf_" . SYS_SYS . ".PMT_AS400_CONFIG";
    $apps14 = executeQuery ( $query14 );       

    // pmtable PMT_DOUBLON_FIELD
    $query15 = "DELETE FROM wf_" . SYS_SYS . ".PMT_DOUBLON_FIELD";
    $apps15 = executeQuery ( $query15 );

    // pmtable PMT_CONDITION_BY_FIELDS
    $query16 = "DELETE FROM wf_" . SYS_SYS . ".PMT_CONDITION_BY_FIELDS";
    $apps16 = executeQuery ( $query16 );    
    */
    //pmtable PMT_CHEQUIER_MM_VN
    /*$query17 = "DELETE FROM wf_" . SYS_SYS . ".PMT_CHEQUIER_MM_VN";
    $apps17 = executeQuery ( $query17 );    */

    // pmtable PMT_COLUMN_DEDOUBLONAGE (as400)
    $query18 = "DELETE FROM wf_" . SYS_SYS . ".PMT_COLUMN_DEDOUBLONAGE";
    $apps18 = executeQuery ( $query18 );    

    // pmtable PMT_INBOX_FIELDS_SELECT 
    $query19 = "DELETE FROM wf_" . SYS_SYS . ".PMT_INBOX_FIELDS_SELECT";
    $apps19 = executeQuery ( $query19 );

    // pmtable PMT_INBOX_PARENT_TABLE (table ,rol , inbox)
    $query20 = "DELETE FROM wf_" . SYS_SYS . ".PMT_INBOX_PARENT_TABLE";
    $apps20 = executeQuery ( $query20 );    

    // pmtable PMT_CONFIG_USERS_OPTIONS
    $query21 = "DELETE FROM wf_" . SYS_SYS . ".PMT_CONFIG_USERS_OPTIONS";
    $apps21 = executeQuery ( $query21 );        

    // pmtable PMT_CONFIG_USERS
    $query22 = "DELETE FROM wf_" . SYS_SYS . ".PMT_CONFIG_USERS";
    $apps22 = executeQuery ( $query22 );            

    // pmtable PMT_USER_NEW_INFORMATION
    $query23 = "DELETE FROM wf_" . SYS_SYS . ".PMT_USER_NEW_INFORMATION";
    $apps23 = executeQuery ( $query23 );                

    // pmtable PMT_INBOX_WHERE_USER
    $query24 = "DELETE FROM wf_" . SYS_SYS . ".PMT_INBOX_WHERE_USER";
    $apps24 = executeQuery ( $query24 );         

    // pmtable PMT_CONFIG_LIST_USERS
    $query25 = "DELETE FROM wf_" . SYS_SYS . ".PMT_CONFIG_LIST_USERS";
    $apps25 = executeQuery ( $query25 );

    // pmtable PMT_IMPORT_CSV_DATA
    $query26 = "DELETE FROM wf_" . SYS_SYS . ".PMT_IMPORT_CSV_DATA";
    $apps26 = executeQuery ( $query26 );            

    // pmtable PMT_USER_CONTROL_CASES                
    $query27 = "DELETE FROM wf_" . SYS_SYS . ".PMT_USER_CONTROL_CASES";
    $apps27 = executeQuery ( $query27 );            

    // pmtable PMT_CONFIG_CSV_IMPORT
    $query28 = "DELETE FROM wf_" . SYS_SYS . ".PMT_CONFIG_CSV_IMPORT";
    $apps28 = executeQuery ( $query28 ); 

    // pmtable PMT_PM_INBOX_ROLES (rol ,sw_inbox)
    $query29 = "DELETE FROM wf_" . SYS_SYS . ".PMT_PM_INBOX_ROLES";
    $apps29 = executeQuery ( $query29 );     


    // OTHER TABLES
    // old_partenaire_real_data
    $query01 = "DELETE FROM wf_" . SYS_SYS . ".old_partenaire_real_data";
    $apps01 = executeQuery ( $query01 );         

}
?>