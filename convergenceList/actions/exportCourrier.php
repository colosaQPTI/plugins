<?php 
G::loadClass ( 'pmFunctions' );

$getVar = array();
$getVar = json_decode($_REQUEST['idFile'], true);
if (!empty($_REQUEST['doc_uid_list']))
{
    $doc_uid_list = $_REQUEST['doc_uid_list'];
    $where_exclude = ' AND DOC_UID <>"' . $doc_uid_list . '"';
}
else
{
    $where_exclude = '';
}
if (count($getVar) == 1) {
    // ajout de AND CON_LANG = 'fr' car la requète trouve un doc avec le même app_uid dans chaque langue depuis la maj des traductions.
     $query = 'SELECT MAX(DOC_VERSION) AS DOC_VERSION, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID="' . $getVar[0]['APP_UID'] . '" AND APP_DOC_TYPE="OUTPUT" AND 
              APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "fr" ' . $where_exclude;    
    $result = executeQuery($query);
    if(sizeof($result) == 0)
    {
         $query = 'SELECT MAX(DOC_VERSION) AS DOC_VERSION, APP_DOC_UID, CON_VALUE FROM APP_DOCUMENT, CONTENT WHERE APP_UID="' . $getVar[0]['APP_UID'] . '" AND APP_DOC_TYPE="OUTPUT" AND 
                   APP_DOC_STATUS="ACTIVE" AND APP_DOC_UID = CON_ID AND CON_CATEGORY = "APP_DOC_FILENAME" AND CON_LANG = "en" ' . $where_exclude;    
         $result = executeQuery($query);
    }
    if( count($result) >= 1 && isset($result[1]['APP_DOC_UID'])) {
    
        $app_uid = $getVar[0]['APP_UID'];

        if(method_exists('G','getPathFromUID')){
            $app_uid = G::getPathFromUID($getVar[0]['APP_UID']);
        }        
        $path = PATH_DOCUMENT.$app_uid.PATH_SEP . 'outdocs' . PATH_SEP . $result[1]['APP_DOC_UID'] . '_' . $result[1]['DOC_VERSION'];
        $file = file_get_contents($path.'.pdf');

            //OUPUT HEADERS
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: application/pdf");    
        header('Content-Disposition: attachment; filename="'.$result[1]['CON_VALUE'].'.pdf";' );
        header('Content-Length: '.strlen($file)); 
        header("Content-Transfer-Encoding: binary");

        echo $file;
    }
    else
        echo 'pas de fichier';  
    
}
else {
    
    foreach($getVar as $item) {
        $files[]= "'".$item['APP_UID']."'";     
    }
    $fileContent = convergence_concatFiles($files, $where_exclude);

    //OUPUT HEADERS
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header("Content-Type: application/pdf");    
    header('Content-Disposition: attachment; filename="courriers_'.time().'.pdf";' );
    header('Content-Length: '.strlen($fileContent)); 
    header("Content-Transfer-Encoding: binary");

    echo $fileContent;
    
}



?>
