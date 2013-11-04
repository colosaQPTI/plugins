<?php

require_once dirname(__FILE__).'/Webservice.php';


class Solde extends Webservices {   

	
	public function __construct() { 
		parent::__construct(); 
		
		// INIT
       	$this->url = "https://extranet.aqoba-preprod.customers.artful.net/api/v09/solde?access_token=99ac21619656c825e788ffb8ac6bfa23f08f4b08";
        $this->wsId = "304";
		
		// GET Bouchon
		// $bouchonFileName = dirname(__FILE__).'/bouchonSolde.txt';
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
	
		// CALL Ws
		$res = $this->_call();        
        // RETURN
		return $res->status->success;

    } 
	
}


