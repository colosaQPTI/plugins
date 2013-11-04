<?php

require_once dirname(__FILE__).'/Webservice.php';


class Transaction extends Webservices {   

	private $sousMontant;
    
	public function __construct() { 
		parent::__construct(); 
		
		// INIT
       	$this->url = "https://extranet.aqoba-preprod.customers.artful.net/api/v09/versement?access_token=99ac21619656c825e788ffb8ac6bfa23f08f4b08";
		$this->wsId = "201";
		$this->sousMontant = array();		
		
		// GET Bouchon
		//$bouchonFileName = dirname(__FILE__).'/bouchonVersementFailure.txt';
		// $bouchonFileName = dirname(__FILE__).'/bouchonVersementSuccess.txt';
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
		$this->inputParams["sens"]["value"] = $this->sens;
		$this->inputParams["montant"]["value"] = $this->montant;
		if(!empty($this->sousMontant))
			$this->inputParams["sousMontants"]["children"] = $this->sousMontant;			
      
		// CALL Ws
		$res = $this->_call();		        
		//var_dump($res->status->success);
        //die;
        // RETURN
        return $res->status->success;

    } 
	
	public function addSousMontant($reseau,$montant){
	
		// INIT
		$smTemp = array();
		$smTemp['sousMontant']['attr']['reseau'] = $reseau;
		$smTemp['sousMontant']['value'] = $montant;
		
		// ADD it
		$this->sousMontant[] = $smTemp;
		
	}
}


