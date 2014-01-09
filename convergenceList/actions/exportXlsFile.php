<?php

G::loadClass('pmFunctions');
$file = $_REQUEST['file'];

$xls = file_get_contents($file);

$filname = basename($_REQUEST['file']);
//OUPUT HEADERSs

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//header("Cache-Control: private",false);
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=" . $filname . ';');
header("Content-Transfer-Encoding: binary");
 
echo $xls;

header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
?>
