<?php

require_once dirname(__FILE__).'/Webservice.php';

class ActionCRM extends Webservices {   
    
	public function __construct() { 
		parent::__construct(); 
		
		// INIT
       	$this->url = "";
		$this->wsId = "210";
		
		// GET Bouchon
		//$bouchonFileName = dirname(__FILE__).'/bouchonActionCRM_2.txt';
		$bouchonFileName = dirname(__FILE__).'/bouchonActionCRM_1.txt';
        $handle = fopen($bouchonFileName, "r");
		while (!feof($handle)) { 
			$this->bouchonWs .= fgets($handle, 4096); 
		}
		
	} 
    
	public function call(){	

        // INIT
        $this->inputParams = array();
		$this->inputParams["partenaire"]["value"] = $this->partenaire;
		$this->inputParams["porteurId"]["value"] = $this->porteurId;
		$this->inputParams["action"]["value"] = $this->action;
		if(!empty($this->motif))
			$this->inputParams["motif"]["value"] = $this->motif;		
      
		// CALL Ws
		$res = $this->_call();		        
		
		// RETURN
		return $res->status->success;

    } 

}


