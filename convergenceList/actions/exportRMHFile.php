<?php

G::loadClass('pmFunctions');
$array = $_REQUEST['items'];
$items = json_decode($array, true);

$file = $items[0]['FICHIER'];

$rmh = file_get_contents($file);
unlink($file);

$filname = basename($items[0]['FICHIER']);
//OUPUT HEADERSs
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

header('Content-Disposition: attachment; filename="' . $filname . '";');
header("Content-Transfer-Encoding: binary");
 
echo $rmh;

header("Content-Type: text/plain");
?>