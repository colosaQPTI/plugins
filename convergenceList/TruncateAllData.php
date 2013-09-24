<?php
ini_set ( 'error_reporting', E_ALL );
ini_set ( 'display_errors', True );
// Script Delete Cases
G::loadClass ( 'pmFunctions' );
G::LoadClass ( "case" );

if(isset($_GET['PRO_UID']))
    $proUid = $_GET['PRO_UID'];
else
    $proUid = isset($_REQUEST['PRO_UID'])?$_REQUEST['PRO_UID']:"";

if($proUid != "")
{   
    // ***************** DELETE ALL FILES SITES EXCEPT input,output,logos *******************
    deleteFilesProcess($proUid);
    // ***************** DELETE ALL CASES OF SPECIFIC PROCESS ****************     
    deleteCasesProcess($proUid); 
    // ***************** TRUNCATE Report Tables FRANCIA *************************
    truncateReportTables();
    // ****************** truncate Pmtables ***********************
    truncatePmtables();
}
else
{
    // ***************** TRUNCATE DATA PROCESSMAKER **********************
    cleanAllCases();
    // ***************** TRUNCATE PMTABLES FRANCIA **********************
    truncateReportTables();
    // ***************** DELETE ALL FILES SITES EXCEPT input,output,logos *******************
    deleteFilesAll();
}

function cleanAllCases()
{
    $cnn = Propel::getConnection('workflow');
    $stmt = $cnn->createStatement();
    // ******* Delete All Cases -> Tables PROCESSMAKER *******
    $query1 ="TRUNCATE TABLE wf_" . SYS_SYS . ".APPLICATION";
    $apps1 = $stmt->executeQuery($query1, ResultSet::FETCHMODE_NUM);
    
    $query2 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_DELAY ";
    $apps2 = $stmt->executeQuery ( $query2, ResultSet::FETCHMODE_NUM );

    $query3 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_DELEGATION ";
    $apps3 = $stmt->executeQuery ( $query3, ResultSet::FETCHMODE_NUM );
 
    $query4 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_DOCUMENT ";
    $apps4 = $stmt->executeQuery ( $query4, ResultSet::FETCHMODE_NUM );

    $query5 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_MESSAGE ";
    $apps5 = $stmt->executeQuery ( $query5, ResultSet::FETCHMODE_NUM );

    $query6 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_OWNER ";
    $apps6 = $stmt->executeQuery ( $query6, ResultSet::FETCHMODE_NUM );

    $query7 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_THREAD ";
    $apps7 = $stmt->executeQuery ( $query7, ResultSet::FETCHMODE_NUM );

    $query8 = "TRUNCATE TABLE wf_" . SYS_SYS . ".SUB_APPLICATION ";
    $apps8 = $stmt->executeQuery ( $query8, ResultSet::FETCHMODE_NUM );

    $query9 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_EVENT ";
    $apps9 = $stmt->executeQuery ( $query9, ResultSet::FETCHMODE_NUM );

    $query10 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_CACHE_VIEW ";
    $apps10 = $stmt->executeQuery ( $query10, ResultSet::FETCHMODE_NUM );

    $query11 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_HISTORY ";
    $apps11 = $stmt->executeQuery ( $query11, ResultSet::FETCHMODE_NUM );
    
    $query12 = "DELETE FROM wf_" . SYS_SYS . ".CONTENT WHERE CON_CATEGORY LIKE 'APP_%' ";
    $apps12 = $stmt->executeQuery ( $query12, ResultSet::FETCHMODE_NUM );
     // notes cases
    $query13 = "TRUNCATE TABLE  wf_" . SYS_SYS . ".APP_NOTES ";
    $apps13 = $stmt->executeQuery ( $query13, ResultSet::FETCHMODE_NUM );
  
    $query15 = "TRUNCATE TABLE wf_" . SYS_SYS . ".APP_FOLDER ";  //  --(If using PM 1.8 and later)
    $apps15 = $stmt->executeQuery ( $query15, ResultSet::FETCHMODE_NUM );

    echo "*********** Ils courent correctement ***************";
}

function truncatePmtables()
{
    $cnn = Propel::getConnection('workflow');
    $stmt = $cnn->createStatement();
    // pmtable PMT_HISTORY_LOG
    $query2 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_HISTORY_LOG";
    $apps2 = $stmt->executeQuery ( $query2, ResultSet::FETCHMODE_NUM );      

    // pmtable PMT_USER_CONTROL_CASES                
    $query4 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_USER_CONTROL_CASES";
    $apps4 = $stmt->executeQuery ( $query4, ResultSet::FETCHMODE_NUM ); 

    // pmtable PMT_IMPORT_CSV_DATA
    $query5 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_IMPORT_CSV_DATA";
    $apps5 = $stmt->executeQuery ( $query4, ResultSet::FETCHMODE_NUM ); 

    echo "*********** Ils courent additional tables correctement ***************";
}

function deleteFilesProcess($proUid)
{
    // Obtain the documents array
    if($directory = opendir(PATH_DOCUMENT)) 
    { 
         while (($file =readdir($directory))!==false) 
         { 
          if ((!is_file($file)) and($file != '.') and ($file != '..'))
          {
            if($file != 'logos' AND $file !='input' AND $file !='output')
                $arrayDirectories[$file]=$file;
          }  
         } 
         closedir($directory); 
    }    

    //Verify if document array exist  
    if(isset($arrayDirectories)){
        // Verifiy if the Process exist
        $selectProc = "SELECT PRO_UID FROM wf_" . SYS_SYS . ".PROCESS WHERE PRO_UID= '". $proUid ."'";
        $appproc = executeQuery($selectProc);
        // If exists the process then delete documents by process
        if(count($appproc)>0){
            // Select al APP_UID from the process
            $masterSelect = "SELECT APP_UID FROM wf_" . SYS_SYS . ".APPLICATION WHERE PRO_UID= '". $proUid ."'";
            $appglobal = executeQuery($masterSelect);

            // Delete all APP_DOCUMENT related with APP_UID
            foreach($appglobal as $valuea){
                // Select APP_DOC_UID from APP_DOCUMENT
                $selectDoc = "SELECT APP_DOC_UID FROM wf_" . SYS_SYS . ".APP_DOCUMENT WHERE APP_UID= '". $valuea["APP_UID"] ."'";
                $appdoc = executeQuery($selectDoc);

                // Delete all Data selected from APP_DOCUMENT
                if (isset($appdoc)) {
                    foreach ($appdoc as $key => $value) {
                        $sDelContent = "DELETE FROM wf_" . SYS_SYS . ".CONTENT WHERE CON_ID = '" . $value['APP_DOC_UID'] . "'";
                        $eDelContent = executeQuery($sDelContent);
                    }
                }

                // Delete the physic file from server with APP_UID
                foreach ($arrayDirectories as $key => $value) {
                    if($valuea["APP_UID"]==$value){
                        $directory = PATH_DOCUMENT.$value;
                        chmod($directory, 0777);
                        rrmdir($directory);
                    }
                }
            }
        }
    }
    echo "*********** Ils courent files correctement ***************";
}

function deleteFilesAll()
{
    $arrayDirectories = array();
    // Obtain the documents array
    if($directory = opendir(PATH_DOCUMENT)) 
    { 
         while (($file =readdir($directory))!==false) 
         { 
          if ((!is_file($file)) and($file != '.') and ($file != '..'))
          {
            if($file != 'logos' AND $file !='input' AND $file !='output')
                $arrayDirectories[$file]=$file;
          }  
         } 
         closedir($directory); 
    }  

    // Delete all files from the directory array
    if(count($arrayDirectories)){
        foreach ($arrayDirectories as $key => $value):
             $directory = PATH_DOCUMENT.$value;
             //chmod($directory, 0777);
             rrmdir($directory);                
        endforeach;     
    echo "*********** Ils courent files correctement ***************";
    }
    else
        echo "*********** Les répertoires sont pas à supprimer ***************";   
}


function deleteCasesProcess($proUid)
{
    // Master for obtain all APP_UID from the specific process
    $masterSelect = "SELECT * FROM wf_" . SYS_SYS . ".APPLICATION WHERE PRO_UID = '". $proUid ."'";
    $appglobal = executeQuery($masterSelect);

    // Go over result of the master table
    foreach($appglobal as $value) {
        // Delete all data from CONTENT related to process
        $query1 = " DELETE FROM wf_" . SYS_SYS . ".CONTENT 
                    WHERE wf_" . SYS_SYS . ".CONTENT.CON_ID='".$value["APP_UID"]."'";
        $app1 = executeQuery($query1);

        // Delete all data from APP_DOCUMENT related to process
        $query2 = " DELETE FROM wf_" . SYS_SYS . ".APP_DOCUMENT 
                    WHERE wf_" . SYS_SYS . ".APP_DOCUMENT.APP_UID='".$value["APP_UID"]."'";
        $app2 = executeQuery($query2);

        // Delete all data from APP_EVENT related to process
        $query3 = " DELETE FROM wf_" . SYS_SYS . ".APP_EVENT
                    WHERE wf_" . SYS_SYS . ".APP_EVENT.APP_UID='".$value["APP_UID"]."'";
        $app3 = executeQuery($query3);

        // Delete all data from APP_MESSAGE related to process
        $query4 = " DELETE FROM wf_" . SYS_SYS . ".APP_MESSAGE
                    WHERE wf_" . SYS_SYS . ".APP_MESSAGE.APP_UID='".$value["APP_UID"]."'";
        $app4 = executeQuery($query4);

        // Delete all data from APP_OWNER related to process
        $query5 = " DELETE FROM wf_" . SYS_SYS . ".APP_OWNER
                    WHERE wf_" . SYS_SYS . ".APP_OWNER.APP_UID='".$value["APP_UID"]."'";
        $app5 = executeQuery($query5);

        // Delete all data from APP_THREAD related to process
        $query6 = " DELETE FROM wf_" . SYS_SYS . ".APP_THREAD
                    WHERE wf_" . SYS_SYS . ".APP_THREAD.APP_UID='".$value["APP_UID"]."'";
        $app6 = executeQuery($query6);	

        // Delete all data from SUB_APPLICATION related to process
        $query7 = " DELETE FROM wf_" . SYS_SYS . ".SUB_APPLICATION
                    WHERE wf_" . SYS_SYS . ".SUB_APPLICATION.APP_UID='".$value["APP_UID"]."'";
        $app6 = executeQuery($query7);

        ############### PMTABLES #######################################
        // Delete all data from PMT_HISTORY_LOG related to process
        $querya = " DELETE FROM wf_" . SYS_SYS . ".PMT_HISTORY_LOG
                    WHERE wf_" . SYS_SYS . ".PMT_HISTORY_LOG.HLOG_APP_UID='".$value["APP_UID"]."'";
        $appa = executeQuery($querya);

        // Delete all data from PMT_USER_CONTROL_CASES related to process
        $queryb = " DELETE FROM wf_" . SYS_SYS . ".PMT_USER_CONTROL_CASES
                    WHERE wf_" . SYS_SYS . ".PMT_USER_CONTROL_CASES.APP_UID='".$value["APP_UID"]."'";
        $appb = executeQuery($queryb);
        ############### END PMTABLES ###################################
    
    }

    // Delete all data from APP_DELAY related to process
    $query8 = " DELETE FROM wf_" . SYS_SYS . ".APP_DELAY WHERE PRO_UID= '".$proUid."' ";        
    $app8 = executeQuery($query8);
	
    // Delete all data from APP_DELEGATION related to process
	$query9 = "DELETE FROM wf_" . SYS_SYS . ".APP_DELEGATION  WHERE PRO_UID= '".$proUid."' ";
    $app9 = executeQuery($query9);
	
    // Delete all data from APP_CACHE_VIEW related to process
	$query10 = "DELETE FROM wf_" . SYS_SYS . ".APP_CACHE_VIEW  WHERE PRO_UID= '".$proUid."' ";
    $app10 = executeQuery($query10);
	
    // Delete all data from APP_HISTORY related to process
	$query11 = "DELETE FROM wf_" . SYS_SYS . ".APP_HISTORY WHERE PRO_UID= '".$proUid."' ";
    $app11 = executeQuery($query11);
	
    // Delete all data from APPLICATION related to process
	$query12 = "DELETE FROM wf_" . SYS_SYS . ".APPLICATION  WHERE PRO_UID= '".$proUid."' ";
    $app12 = executeQuery($query12);

	echo "******** WARNING : Processus visant à éliminer mate ***************";
}

function rrmdir($dir)
{
  if (is_dir($dir)) {

         $objects = scandir($dir);
         
          foreach ($objects as $object) {
             if ($object != "." && $object != "..") {
              G::pr("type => ".$dir . "/" . $object);
              //G::pr(filetype($dir . "/" . $object));
                 if (filetype($dir . "/" . $object) == "dir") {
                     rrmdir($dir . "/" . $object);
                 } else {
                        /*chmodr($dir . "/" . $object, 0777);
                        chownr($dir . "/" . $object, 'apache');
                        chgrpr($dir . "/" . $object, 'apache');*/
                        unlink($dir . "/" . $object);
                 }
            }
         }
         reset($objects);
         rmdir($dir);
         G::pr("Delete directory => ".$dir);
    }
}


function truncateReportTables()
{
    $cnn = Propel::getConnection('workflow');
    $stmt = $cnn->createStatement();

    // ******************** REPORT TABLES *************************
    //  PMT_DEMANDES    
    $query1 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_DEMANDES";
    $apps1 = $stmt->executeQuery ( $query1, ResultSet::FETCHMODE_NUM ); 
    //  PMT_PRESTATAIRE    ******
    $query2 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_PRESTATAIRE";
    $apps2 = $stmt->executeQuery ( $query2, ResultSet::FETCHMODE_NUM ); 
    //  PMT_LISTE_PROD    
    $query3 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_LISTE_PROD";
    $apps3 = $stmt->executeQuery ( $query3, ResultSet::FETCHMODE_NUM ); 
    

    if ("wf_".SYS_SYS == "wf_aquitaine")
    {
        // *************************** REPORT TABLES **************************

        //  PMT_LISTE_RMBT    
        $query4 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_LISTE_RMBT";
        $apps4 = $stmt->executeQuery ( $query4, ResultSet::FETCHMODE_NUM ); 

        //  PMT_CONSEILLER_EIE  ****  
        $query5 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_CONSEILLER_EIE";
        $apps5 = $stmt->executeQuery ( $query5, ResultSet::FETCHMODE_NUM ); 

        //  PMT_EIES    ******
        $query6 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_EIES";
        $apps6 = $stmt->executeQuery ( $query6, ResultSet::FETCHMODE_NUM );   

        //  PMT_REMBOURSEMENT   *****   
        $query7 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_REMBOURSEMENT";
        $apps7 = $stmt->executeQuery ( $query7, ResultSet::FETCHMODE_NUM );   
    }

    if("wf_".SYS_SYS == "wf_CheqLivreApp")
    {
        //****************** REPORT TABLES  *************************
        //  PMT_LISTE_RMBT    
        $query4 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_LISTE_RMBT";
        $apps4 = $stmt->executeQuery ( $query4, ResultSet::FETCHMODE_NUM ); 

        //  PMT_LIMOUSIN    
        $query8 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_LIMOUSIN";
        $apps8 = $stmt->executeQuery ( $query8, ResultSet::FETCHMODE_NUM); 

        //  PMT_ETABLISSEMENT    
        $query9 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_ETABLISSEMENT";
        $apps9 = $stmt->executeQuery ( $query9, ResultSet::FETCHMODE_NUM ); 

        //  PMT_AJOUT_USER    
        $query10 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_AJOUT_USER";
        $apps10 = $stmt->executeQuery ( $query10, ResultSet::FETCHMODE_NUM ); 

        //  PMT_REMBOURSEMENT    
        $query11 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_REMBOURSEMENT";
        $apps11 = $stmt->executeQuery ( $query11, ResultSet::FETCHMODE_NUM ); 

        //  PMT_EIES    
        $query12 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_EIES";
        $apps8 = $stmt->executeQuery ( $query12, ResultSet::FETCHMODE_NUM );
    // ******************* END REPORT TABLES **********************
    }

    if("wf_".SYS_SYS == 'wf_idfTranSport')
    {
        // ******************* REPORT TABLES **************************

        //  PMT_LISTE_RMBT    
        $query4 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_LISTE_RMBT";
        $apps4 = $stmt->executeQuery ( $query4, ResultSet::FETCHMODE_NUM ); 

         // PMT_ETABLISSEMENT
        $query14 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_ETABLISSEMENT";
        $apps14 = $stmt->executeQuery ( $query14, ResultSet::FETCHMODE_NUM ); 

         // PMT_AJOUT_USER 
        $query15 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_AJOUT_USER";
        $apps15 = $stmt->executeQuery ( $query15, ResultSet::FETCHMODE_NUM ); 

         // PMT_FICHIER_RMH 
        $query16 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_FICHIER_RMH";
        $apps16 = $stmt->executeQuery ( $query16, ResultSet::FETCHMODE_NUM );
    
    // This table are the BD but not in aditional tables
    $query16a = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_LIST_PROD";
        $apps16a = $stmt->executeQuery ( $query16a, ResultSet::FETCHMODE_NUM ); 

        // ******************END REPORT TABLES ************************
    }

    if( "wf_".SYS_SYS == 'wf_limousin')
    {
    // ***************** REPORT TABLES ********************
        $query17 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_DEMANDES_CHEQUIER";
        $apps17 = $stmt->executeQuery ( $query17, ResultSet::FETCHMODE_NUM );

        $query18 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_VOUCHER";
        $apps18 = $stmt->executeQuery ( $query18, ResultSet::FETCHMODE_NUM );

         // PMT_ETABLISSEMENT
        $query19 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_ETABLISSEMENT";
        $apps19 = $stmt->executeQuery ( $query19, ResultSet::FETCHMODE_NUM ); 

        $query20 = "TRUNCATE TABLE wf_" . SYS_SYS . ".PMT_SAISIE_TRANS";
        $apps20 = $stmt->executeQuery ( $query20, ResultSet::FETCHMODE_NUM );         
        // ***************** END REPORT TABLES ****************
    
    }
    
}
   

?>