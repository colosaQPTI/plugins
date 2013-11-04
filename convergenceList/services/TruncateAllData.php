<?php
ini_set ( 'error_reporting', E_ALL );
ini_set ( 'display_errors', True );
// Script Delete Cases
G::loadClass ( 'pmFunctions' );
G::LoadClass ( "case" );

function cleanAllCases()
{
    
    $query1 ="TRUNCATE TABLE wf_" . SYS_SYS . ".APPLICATION";
    G::pr($query1);
    $apps1 = executeQuery ( $query1 );
    
    $query2 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_DELAY ";
    $apps2 = executeQuery ( $query2 );

    $query3 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_DELEGATION ";
    $apps3 = executeQuery ( $query3 );

    $query4 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_DOCUMENT ";
    $apps4 = executeQuery ( $query4 );

    $query5 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_MESSAGE ";
    $apps5 = executeQuery ( $query5 );

	$query6 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_OWNER ";
    $apps6 = executeQuery ( $query6 );

    $query7 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_THREAD ";
    $apps7 = executeQuery ( $query7 );

    $query8 = "TRUNCATE TABLE wf_" . SYS_SYS . ".SUB_APPLICATION ";
    $apps8 = executeQuery ( $query8 );

    $query9 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_EVENT ";
    $apps9 = executeQuery ( $query9 );

    $query10 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_CACHE_VIEW ";
    $apps10 = executeQuery ( $query10 );

    $query11 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_HISTORY ";
    $apps11 = executeQuery ( $query11 );
    
    $query12 = "DELETE FROM wf_" . SYS_SYS . ".CONTENT WHERE CON_CATEGORY LIKE 'APP_%' ";
    $apps12 = executeQuery ( $query12 );
    
    // notes cases
    $query13 = "TRUNCATE TABLE  wf_" . SYS_SYS . ".APP_NOTES ";
    $apps13 = executeQuery ( $query13 );
    // control login user
    /*$query14 = "TRUNCATE TABLE  wf_" . SYS_SYS . ".LOGIN_LOG";
    $apps14 = executeQuery ( $query14 );
    */
    $query15 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_FOLDER ";  //  --(If using PM 1.8 and later)
    $apps15 = executeQuery ( $query15 );

    tablesFrancia(); 

    echo "Ils courent correctement";
}

function tablesFrancia()
{
    // PMTABLE CHEQUES 
    $query1 = "TRUNCATE TABLE wf_" .SYS_SYS . ".PMT_CHEQUES";
    $apps1 = executeQuery ( $query1 );

    // pmtable PMT_HISTORY_LOG
    $query2 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_HISTORY_LOG";
    $apps2 = executeQuery ( $query2 );    
    
    // pmtable PMT_IMPORT_CSV_DATA
    $query3 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_IMPORT_CSV_DATA";
    $apps3 = executeQuery ( $query3 );            

    // pmtable PMT_USER_CONTROL_CASES                
    $query4 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_USER_CONTROL_CASES";
    $apps4 = executeQuery ( $query4 ); 

    // ******************** REPORT TABLES *************************
    
    //  PMT_DEMANDES    
    $query5 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_DEMANDES";
    $apps5 = executeQuery ( $query5 ); 

    //  PMT_PRESTATAIRE    
    $query7 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_PRESTATAIRE";
    $apps7 = executeQuery ( $query7 ); 

       //  PMT_LISTE_PROD    
    $query10 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_LISTE_PROD";
    $apps10 = executeQuery ( $query10 ); 

    //  PMT_LISTE_RMBT    
    $query11 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_LISTE_RMBT";
    $apps11 = executeQuery ( $query11 ); 

    G::pr("wf_".SYS_SYS);
    if ("wf_".SYS_SYS == "wf_aquitaine")
    {
        //  PMT_CONSEILLER_EIE    
        $query9 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_CONSEILLER_EIE";
        $apps9 = executeQuery ( $query9 ); 
    }

    if("wf_".SYS_SYS == "wf_CheqLivreApp")
    {
        //****************** REPORT TABLES  *************************
        //  PMT_LIMOUSIN    
        $query10 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_LIMOUSIN";
        $apps10 = executeQuery ( $query10 ); 

        //  PMT_ETABLISSEMENT    
        $query11 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_ETABLISSEMENT";
        $apps11 = executeQuery ( $query11 ); 

        //  PMT_AJOUT_USER    
        $query12 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_AJOUT_USER";
        $apps12 = executeQuery ( $query12 ); 

        //  PMT_REMBOURSEMENT    
        $query6 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_REMBOURSEMENT";
        $apps6 = executeQuery ( $query6 ); 

        //  PMT_EIES    
        $query8 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_EIES";
        $apps8 = executeQuery ( $query8 );     


        // ******************* END REPORT TABLES **********************

    }

    if("wf_".SYS_SYS == 'wf_idfTranSport')
    {
        // PMT_VN_FOR_RMH esta vacio
        $query13 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_VN_FOR_RMH";
        $apps13 = executeQuery ( $query13 ); 

        // ******************* REPORT TABLES **************************
         // PMT_ETABLISSEMENT
        $query14 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_ETABLISSEMENT";
        $apps14 = executeQuery ( $query14 ); 

         // PMT_AJOUT_USER 
        $query15 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_AJOUT_USER";
        $apps15 = executeQuery ( $query15 ); 

         // PMT_FICHIER_RMH 
        $query16 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_FICHIER_RMH";
        $apps16 = executeQuery ( $query16 ); 

        // ******************END REPORT TABLES ************************
    }

    if( "wf_".SYS_SYS == 'wf_limousin')
    {
        $query17 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_AUTORISATIONS";
        $apps17 = executeQuery ( $query17 );

        $query18 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_TRANSACTIONS";
        $apps18 = executeQuery ( $query18 );

        // ***************** REPORT TABLES ********************

        $query19 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_DEMANDES_CHEQUIER";
        $apps19 = executeQuery ( $query19 );

        $query20 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_VOUCHER";
        $apps20 = executeQuery ( $query20 );

         // PMT_ETABLISSEMENT
        $query21 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_ETABLISSEMENT";
        $apps21 = executeQuery ( $query21 ); 

         // PMT_ETABLISSEMENT
        $query22 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_NUM_PROD_FOR_AQOBA";
        $apps22 = executeQuery ( $query22 );         

        // ***************** END REPORT TABLES ****************
    }

    
    //joins -> inbox -roles 
    /*$query1 = "TRUNCATE TABLE wf_" .SYS_SYS . ".PMT_INBOX_JOIN";
    $apps1 = executeQuery ( $query1 );*/

    // pmtable actions 
    /*$query2 = "TRUNCATE TABLE wf_" .SYS_SYS . ".PMT_ACTIONS";
    $apps1 = executeQuery($query2);*/

    // pmtable PMT_INBOX_ACTIONS (actions for inbox)
    /*$query5 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_INBOX_ACTIONS";
    $apps5 = executeQuery($query5);*/    

    // pmtable PMT_INBOX_FIELDS (fields of inbox )
    /*$query6 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_INBOX_FIELDS";
    $apps6 = executeQuery ( $query6 );*/

    // pmtable PMT_INBOX_ROLES 
    /*$query7 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_INBOX_ROLES";
    $apps7 = executeQuery ( $query7 );*/

    // pmtable PMT_INBOX_FILTERS (filters of inbox)
    /*$query8 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_INBOX_FILTERS";
    $apps8 = executeQuery ( $query8 );*/

    // pmtable PMT_INBOX
    /*$query9 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_INBOX";
    $apps9 = executeQuery ( $query9 );*/

     // pmtable PMT_PERMISSION_OPTIONS (save options of rol )
    /*$query4 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_PERMISSION_OPTIONS";
    $apps4 = executeQuery ( $query4);    */

     // pmtable PMT_INBOX_WHERE (where of inbox)
    /*$query11 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_INBOX_WHERE";
    $apps11 = executeQuery ( $query11 );    */

    //(configuration AS400) 
    // pmtable PMT_CONFIG_DEDOUBLONAGE 

    /*$query12 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_CONFIG_DEDOUBLONAGE";
    $apps12 = executeQuery ( $query12 );   

    // pmtable PMT_COLUMN_AS400        
    $query13 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_COLUMN_AS400";
    $apps13 = executeQuery ( $query13 );  

    // pmtable PMT_AS400_CONFIG
    $query14 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_AS400_CONFIG";
    $apps14 = executeQuery ( $query14 );       

    // pmtable PMT_DOUBLON_FIELD
    $query15 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_DOUBLON_FIELD";
    $apps15 = executeQuery ( $query15 );

    // pmtable PMT_CONDITION_BY_FIELDS
    $query16 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_CONDITION_BY_FIELDS";
    $apps16 = executeQuery ( $query16 );    
    */
    //pmtable PMT_CHEQUIER_MM_VN
    /*$query17 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_CHEQUIER_MM_VN";
    $apps17 = executeQuery ( $query17 );    */

    
    // pmtable PMT_COLUMN_DEDOUBLONAGE (as400)
    /*$query18 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_COLUMN_DEDOUBLONAGE";
    $apps18 = executeQuery ( $query18 );    */

    // pmtable PMT_INBOX_FIELDS_SELECT 
    /*$query19 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_INBOX_FIELDS_SELECT";
    $apps19 = executeQuery ( $query19 );*/

    // pmtable PMT_INBOX_PARENT_TABLE (table ,rol , inbox)
    /*$query20 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_INBOX_PARENT_TABLE";
    $apps20 = executeQuery ( $query20 );    */

    // pmtable PMT_CONFIG_USERS_OPTIONS
    /*$query21 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_CONFIG_USERS_OPTIONS";
    $apps21 = executeQuery ( $query21 );        */

    // pmtable PMT_CONFIG_USERS
    /*$query22 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_CONFIG_USERS";
    $apps22 = executeQuery ( $query22 );            */

    // pmtable PMT_USER_NEW_INFORMATION
    /*$query23 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_USER_NEW_INFORMATION";
    $apps23 = executeQuery ( $query23 );                */

    // pmtable PMT_INBOX_WHERE_USER
    /*$query24 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_INBOX_WHERE_USER";
    $apps24 = executeQuery ( $query24 );         */

    // pmtable PMT_CONFIG_LIST_USERS
    /*$query25 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_CONFIG_LIST_USERS";
    $apps25 = executeQuery ( $query25 );*/

    // pmtable PMT_PM_INBOX_ROLES (rol ,sw_inbox)
    /*$query29 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_PM_INBOX_ROLES";
    $apps29 = executeQuery ( $query29 );     */

    // pmtable PMT_CONFIG_CSV_IMPORT
    /*$query28 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_CONFIG_CSV_IMPORT";
    $apps28 = executeQuery ( $query28 ); */

    // OTHER TABLES
    // old_partenaire_real_data
    /*$query01 = "TRUNCATE TABLE wf_" . SYS_SYS . ".old_partenaire_real_data";
    $apps01 = executeQuery ( $query01 );         */

}

    cleanAllCases();
?>