<?php

class Webservices {       

    protected $bouchonWs;
    protected $url;
    protected $wsId;
    protected $inputParams;
    public $errors;
    public $failureCode;

    public function __construct() { 
    }

    public function _call() {
        $info = array();
        //BUILD StreamContent
        $streamContent = $this->buildInputXML($this->inputParams);
//         echo "**********STREAM input XML********";
//          echo $streamContent;
//          echo "******************";
        //var_dump($streamContent);
        $ch = curl_init($this->url);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: text/xml'));    
        // A decommenter pour debugger l'entete HTTP
        // curl_setopt($ch,CURLOPT_HEADER, true);
        // curl_setopt($ch,CURLINFO_HEADER_OUT, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,"$streamContent");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch,CURLOPT_SSLCERT, "/etc/apache2/ssl/limousin.pem");
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, "pempp");        
        //curl_setopt($ch,CURLOPT_CERTINFO, true);
        // GET Response
        $response = curl_exec($ch);
        //var_dump($response);
        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 //        echo 'info = ' . $info;
        try{
        // Check Response
            $response = $this->checkReturn($response);
        }
        catch (Exception $e)
        {            
            throw $e;
        }        
        curl_close($ch);
        // GET Return value
        return $response;

    }

    private function buildInputXML($params){

        // BUILD dom
        $dom = new DomDocument();

        // ******* ROOT *******
        $nodeRequest = $dom->createElement("request");

        // ******* HEAD *******
        $nodeHead = $dom->createElement("head");
        $nodeWsId = $dom->createElement("wsId");
        $nodeWsIdValue = $dom->createTextNode($this->wsId);     
        $nodeWsId->appendChild($nodeWsIdValue);
        $nodeHead->appendChild($nodeWsId);
        $nodeRequest->appendChild($nodeHead);       

        // ******* BODY *******
        $nodeBody = $dom->createElement("body");
        if($this->operation){
            $nodeOperation = $dom->createElement("operation");  
            $nodeOperationValue = $dom->createTextNode($this->operation);
            $nodeOperation->appendChild($nodeOperationValue);   
        }
        $nodeArguments = $dom->createElement("arguments");  

        // BROWSE Params
        foreach($params as $key=>$param){

            // CREATE Node
            $nodeParam = self::createNodeParam($dom,$key,$param);           
            $nodeArguments->appendChild($nodeParam);

        }

        if($this->operation)
            $nodeBody->appendChild($nodeOperation);
        $nodeBody->appendChild($nodeArguments);
        $nodeRequest->appendChild($nodeBody);
        $dom->appendChild($nodeRequest);

        // RETURN
        return $dom->saveXML();


    }

    private function createNodeParam($dom,$key,$param){

        // INIT
        $returnNode = $dom->createElement($key);    
        $nodeValue = $param["value"];

        // SET Value
        if(!empty($param["value"])){

            // BUILD Text Node
            $returnNodeValue = $dom->createTextNode($param["value"]);
            $returnNode->appendChild($returnNodeValue); 

        }elseif(!empty($param["children"])){

            foreach($param["children"] as $newParam){
                $newKey = key($newParam);
                $returnNode->appendChild(self::createNodeParam($dom,$newKey,$newParam[$newKey]));   
            }       


        }

        // ADD Attributes
        if($param['attr'] && is_array($param['attr']) && !empty($param['attr'])){

            foreach($param['attr'] as $attrName=>$attrValue){

                $returnNode->setAttribute($attrName,$attrValue);    
            }

        }

        // RETURN
        return $returnNode;

    }
    
    private function checkReturn($retour){      
        // BUILD dom
        $dom = new DomDocument();

        //LOAD
        $dom->loadXML($retour);
        $reponse = new SimpleXMLElement($dom->saveXML());
        //CHECK Return Code
        if(empty($reponse->status)){
            throw new Exception('Status introuvable dans la réponse XML', 1);

        }elseif(empty($reponse->status->success)){
            if(!empty($reponse->status->failure)){
    
                /*foreach($reponse->status->failure->errors->field as $field)
                    $this->errors[] = array("field" => $field['name'],"desc" => $field);*/

                $this->errors = $reponse->status->failure;
                throw new Exception('Status en echec dans la réponse XML.', 1);

            }else{
                throw new Exception('Status inconnu dans la réponse XML', 1);
            }
        }
        // RETURN
        return $reponse;        

    }
}
