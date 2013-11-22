<?php

require_once dirname(__FILE__).'/Webservice.php';


class Transaction extends Webservices {   

	private $sousMontant;
    
	public function __construct() { 
		parent::__construct(); 
		
		// INIT
       	//$this->url = "http://extranet.aqoba-preprod.customers.artful.net/api/v09/versement?access_token=99ac21619656c825e788ffb8ac6bfa23f08f4b08";
        $this->url = wsHote_Url . "versement" . wsToken_param;
        $this->wsId = "201";
		$this->sousMontant = array();				
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


