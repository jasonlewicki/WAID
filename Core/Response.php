<?php

namespace WAID\Core;

class Response {
	
	// Variable to hold the status code
	private $status_code;
	
	// Variable to hold the status message
	private $status_message;
	
	// Variable to hold the response
	private $response;
	
	// Variable to hold the output format(html,json,xml,etc...)
	private $response_format;

	// Constructor
	public function __construct($status_arr, $response) {		
		
		// Initialize variables with constructor variables
		$this->status_code			= $status_arr['status_code'];
		$this->status_message		= $status_arr['status_message'];
		$this->response				= $response;	
		
		// Set output format
		$this->response_format = $_SERVER['HTTP_ACCEPT'];		
	}	
	
	// Get output format
	public function getResponseFormat(){
        return $this->response_format;
    }
	
	// Get status_code
	public function getStatusCode(){
        return $this->status_code;
    }
	
	// Get status_message
	public function getStatusMessage(){
        return $this->status_message;
    }
	
	// Get response
	public function getResponse(){
        return $this->response;
    }
	
	// Output response based on status and header http_accept type.
	public function sendResponse()
    {
    	// Headers to prevent caching
		header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");
		header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
		
		// Get type of reponse
        $format = $this->getResponseFormat();

        if (preg_match('/json/', $format)) {
            $type = 'json';
        } elseif (preg_match('/html/', $format)) {
            $type = 'html';
        } elseif (preg_match('/xml/', $format)) {
            $type = 'xml';
        } else {
            $type = 'json';
        }

		// Based on type of response reform data to what is expected
        switch ($type) {
            case 'html':                
                $response =  $this->getResponse();
				$this->setStatusHeader();
                header('Content-Type: text/html; charset=iso-8859-1');
				break;
            case 'json':				
                $response = @json_encode($this->getResponse(), JSON_PRETTY_PRINT);
				if ($response === NULL && json_last_error() !== JSON_ERROR_NONE) {
				    $this->setStatus(400, NULL);
				}				
				$this->setStatusHeader();
                header('Content-Type: application/json');
				break;
			case 'xml':				
                $response = $this->convertPHPToXML($this->getResponse());
				if ($response === FALSE) {
				    $this->setStatus(400, NULL);
				}
				$this->setStatusHeader();
				header('Content-Type: text/xml');
				break;
            default:
                die;
                break;
        }
		// Echo response
		echo $response;
    }

	// Set status code
	private function setStatus($status_code, $status_message){
		$this->status_code = $status_code;
		$this->status_message = $status_message;		
		return;
    }
	
	// Set status header
	private function setStatusHeader(){
		$status_code = $this->getStatusCode();
		$status_message = $this->getStatusMessage();
		
		if ($status_message == NULL){
			$status_message = Status::getStatusCodeMessage($status_code);
		}
		
		header($status_message, true, $status_code);
    }
	
	// Convert PHP array to XML
	private function convertPHPToXML($php_array){
        // creating object of SimpleXMLElement
		$xml_obj = new SimpleXMLElement("<?xml version=\"1.0\"?><response></response>");
		
		// function call to convert array to XML
		convertPHPToXMLRecursive($php_array,$xml_obj);
		
		$xml = $xml_obj->asXML();
		
		// Validate XML
		$xml_validate = XMLReader::readString ($xml);
	    $xml_validate->setParserProperty(XMLReader::VALIDATE, true);
		
		if($xml_validate->isValid()){
			return $xml;
		}
		
		return FALSE;	
    }
	
	// function definition to convert array to xml
	private function convertPHPToXMLRecursive($php_array, &$xml_obj) {
	    foreach($php_array as $key => $value) {
	        if(is_array($value)) {
	            if(!is_numeric($key)){
	                $subnode = $xml_obj->addChild("$key");
	                convertPHPToXMLRecursive($value, $subnode);
	            }
	            else{
	                $subnode = $xml_obj->addChild("item$key");
	                convertPHPToXMLRecursive($value, $subnode);
	            }
	        }
	        else {
	            $xml_obj->addChild("$key",htmlspecialchars("$value"));
	        }
	    }
	}
	
}