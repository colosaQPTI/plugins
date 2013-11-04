<?php

require_once dirname(__FILE__).'/Webservice.php';


class Operation extends Webservices {   

	
	public function __construct() { 
		parent::__construct(); 
		
		// INIT
       	$this->url = "https://extranet.aqoba-preprod.customers.artful.net/api/v09/dernieresOperations?access_token=99ac21619656c825e788ffb8ac6bfa23f08f4b08";
        $this->wsId = "303";
		
		// GET Bouchon
		// $bouchonFileName = dirname(__FILE__).'/bouchonOperations.txt';
        // $handle = fopen($bouchonFileName, "r");
		// while (!feof($handle)) { 
			// $this->bouchonWs .= fgets($handle, 4096); 
		// }
		
	} 
    
	public function call(){	

        // INIT
        $this->inputParams = array();
		$this->inputParams["partenaire"]["value"] = $this->partenaire;
		$this->inputParams["porteurId"]["value"] = $this->porteurId;
		$this->inputParams["date"]["value"] = $this->dateDepart;
		$this->inputParams["jours"]["value"] = $this->jours;		
      
		// CALL Ws
		$res = $this->_call();		        
		
		// RETURN
		return $res->status->success->list;

    } 
	
}


