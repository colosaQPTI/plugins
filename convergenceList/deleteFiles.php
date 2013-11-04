<?php
ini_set ( 'error_reporting', E_ALL );
ini_set ( 'display_errors', True );
// Script Delete All files Cases

G::loadClass ( 'pmFunctions' );
G::LoadClass ( "case" );

// $dir = PATH_DOCUMENT . "csvTmp/".$fileCSV.".csv";
//unlink($dir);
//G::pr(PATH_DOCUMENT);die();

if($directory = opendir(PATH_DOCUMENT)) 
 { 
     while (($file =readdir($directory))!==false) 
     { 
      //if(is_dir($file))
      if ((!is_file($file)) and($file != '.') and ($file != '..')) 
      {	
      	if($file != 'logos' AND $file !='input' AND $file !='output')
      	    $arrayDirectories[$file]=$file; 
      }  
     } 
     closedir($directory); 

 }

 if(count($arrayDirectories))
 {
    foreach ($arrayDirectories as $key => $value) {
     
     $directory = PATH_DOCUMENT.$value;
     //G::pr($directory);
     chmod($directory, 0777);
     rrmdir($directory);
     G::pr("se elimino correctamente ");

    }

 }  

function rrmdir($dir)
{
  if (is_dir($dir)) {

         $objects = scandir($dir);
         
          foreach ($objects as $object) {
             if ($object != "." && $object != "..") {
              G::pr("type => ".$dir . "/" . $object);
                 if (filetype($dir . "/" . $object) == "dir") {
                     rrmdir($dir . "/" . $object);
                 } else {
                     unlink($dir . "/" . $object);
                 }
            }
         }
         reset($objects);
         rmdir($dir);
         G::pr("se elimino => ".$dir);
    }
}

?>
