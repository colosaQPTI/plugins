<?php
header('Access-Control-Allow-Origin: *');
G::LoadClass('pmFunctions');
require_once (PATH_PLUGINS.'convergenceList'.PATH_SEP.'classes'.PATH_SEP.'class.Rest.inc.php');
class API extends REST {

	public $data = "";
	private $fileMaster = "/api.php/";
	const DB_SERVER = "localhost";
	const DB_USER = "root";
	const DB_PASSWORD = "arun";
	const DB = "users";

	private $db = NULL;

	public function __construct(){
		parent::__construct();				// Init parent contructor
		$this->dbConnect();				// Initiate Database connection
	}

	/*
	 *  Database connection
	*/
	private function dbConnect(){		
		return true;		
	}

	/*
	 * Public method for access api.
	 * This method dynmically call the method based on the query string
	 *
	 */
	public function processApi(){	
		$func = $this->getMethodRequestedForProcessMaker();
		if((int)method_exists($this,$func) > 0)
			$this->$func();
		else
			$this->response('',404); // If the method not exist with in this class, response would be "Page not found".
	}

	function getMethodRequestedForProcessMaker(){
		$url = $_SERVER['REQUEST_URI'];
		$pos = strpos($url,$this->fileMaster);
		if($pos > 0){
				$method = substr($url, $pos + strlen($this->fileMaster));
				return $method;
		}else{
				$this->response('No method on the url',404);
		}
	}	
	private function json($data){
		if(is_array($data)){
			return json_encode($data);
		}
	}

	private function forgotpasswdpm(){				
		if($this->get_request_method() != "POST"){
			$this->response('',406);
		}
		else{

				if((!(isset($this->_request["username"]))) || ($this->_request["username"] == "")){
						$error = array('status' => "Failed", "msg" => "username index or value empty");
						$this->response($this->json($error),400);
				}				
				if((!(isset($this->_request["currentpasswd"]))) || ($this->_request["currentpasswd"] == "")){
						$error = array('status' => "Failed", "msg" => "Current passwd index or value empty");
						$this->response($this->json($error),400);						
				}
				if((!(isset($this->_request["newpasswd"]))) || ($this->_request["newpasswd"] == "")){
						$error = array('status' => "Failed", "msg" => "New password index or value empty");
						$this->response($this->json($error),400);						
				}
				else{

						$uname = urldecode($this->_request['username']);
                        $currentpasswd = $this->_request['currentpasswd'];
                $newpasswd = md5($this->_request['newpasswd']);
						$checkuser = executeQuery("SELECT USR_USERNAME, CONCAT(USR_FIRSTNAME, ' ', USR_LASTNAME) FROM USERS WHERE USR_USERNAME = '".$uname."' AND USR_PASSWORD = '".$currentpasswd."' ","rbac");						
						if(sizeof($checkuser) == 0){
								$error = array('status' => "Failed", "msg" => "The user or the password are not correct.");
								$this->response($this->json($error),400);	
						}						
						else{
								$res = executeQuery("UPDATE USERS SET USR_PASSWORD = '".$newpasswd."' WHERE USR_USERNAME = '".$uname."' ","workflow");
								$res = executeQuery("UPDATE USERS SET USR_PASSWORD = '".$newpasswd."' WHERE USR_USERNAME = '".$uname."' ","rbac");
								$msg = array('status' => "Validated", "msg" => "Password was restored");
								$this->response($this->json($msg),200);		
						}						
				}								
		}
	}		


}

// Initiate Library

$api = new API;
$api->processApi();