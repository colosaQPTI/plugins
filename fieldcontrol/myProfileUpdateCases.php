<?php

G::loadClass('pmFunctions');

//$_REQUEST = $_POST;
if ( isset($_REQUEST['USR_LASTNAMEORIG'])) {
	
	
	$lastNameOrig =  isset($_REQUEST['USR_LASTNAMEORIG']) ? $_REQUEST['USR_LASTNAMEORIG'] : '';
	$firstnameOrig  = isset($_REQUEST['USR_FIRSTNAMEORIG']) ? $_REQUEST['USR_FIRSTNAMEORIG'] : '';
	$lastnameNew = isset($_REQUEST['USR_LASTNAMENEW']) ? $_REQUEST['USR_LASTNAMENEW'] : '';
	$firstnameNew  = isset($_REQUEST['USR_FIRSTNAMENEW']) ? $_REQUEST['USR_FIRSTNAMENEW'] : '';
	if(SYS_SYS == 'limousin')
	{
		$nom = 'FI_NOM';
		$prenom = 'FI_PRENOM';
	}
	
	$queryData = "SELECT APP_UID FROM PMT_DEMANDES WHERE $nom = '$lastNameOrig' AND  $prenom = '$firstnameOrig' AND STATUT NOT IN ('2','4','5','6')";
	$selectData = executeQuery($queryData); 
    if(sizeof($selectData))
    {	
    	G::LoadClass('case');
    	$oCase = new Cases();
    	foreach($selectData as $row)
    	{
    		$Fields = $oCase->loadCase($row['APP_UID']);
    		$Fields['APP_DATA'][$nom] = $lastnameNew;
	  		$Fields['APP_DATA'][$prenom] = $firstnameNew;
    		$oCase->updateCase($row['APP_UID'], $Fields);
    		
    	}
    }
    // genDataReport ('PMT_DEMANDES');
   
	$result->success = true;
	$result->msg = 'mise a jour';
	print(G::json_encode($result));
}
else {
	$form['USR_UID'] = '';
}
      

  