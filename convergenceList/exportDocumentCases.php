<?php

G::loadClass("webResource");
G::loadClass("pmFunctions");

class web_Ajax extends WebResource{

	function download_document($appUid, $iddoc)
	{
		$selAppDocument = "SELECT
                        DOC_VERSION AS VERSION
	                     ,APP_DOC_UID
	                   FROM APP_DOCUMENT
					   WHERE APP_UID = '".$appUid."' AND DOC_UID='".$iddoc."'
					        ORDER BY DOC_VERSION DESC";
        $datAppDocument = executeQuery($selAppDocument);
		if(count($datAppDocument)>0){
	  		$appDocUid  = $datAppDocument[1]['APP_DOC_UID'];
	  		$version    = $datAppDocument[1]['VERSION'];
		}
 		$sys  = @@SYS_SYS;
		$lang = @@SYS_LANG;
		$skin = @@SYS_SKIN;
  		$sAddress = 'http://'.$_SERVER['HTTP_HOST'].'/sys'.$sys.'/'.$lang.'/'.$skin.'/cases/cases_ShowOutputDocument?a='.$appDocUid.'&v='.$version.'&ext=pdf&random='.rand();
        return $sAddress;
    }
}
$o = new web_Ajax($_SERVER['REQUEST_URI'], $_POST);



?>
