<?php

require_once dirname(__FILE__).'/Webservice.php';


class Blocage extends Webservices {

    public function __construct() { 
		parent::__construct(); 
		
		// INIT
       	$this->url = "http://extranet.aqoba-preprod.customers.artful.net/api/v09/crm?access_token=99ac21619656c825e788ffb8ac6bfa23f08f4b08";
        $this->wsId = "210";    
	} 
    
	public function call(){	

        // INIT
        $this->inputParams = array();
		$this->inputParams["partenaire"]["value"] = $this->partenaire;
		$this->inputParams["porteurId"]["value"] = $this->porteurId;
		$this->inputParams["action"]["value"] = '03'; // code blocage uniqument pas de moif
	
		// CALL Ws
		$res = $this->_call();        
        // RETURN
		return $res->status->success;

    } 
	
}


