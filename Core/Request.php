<?php

namespace WAID\Core;

class Request {

	// Hold the reference of the router in case the controller needs it for reverse routing
	private $router;
	
	// Which Resource to load
	private $resource;
	
	// The Requested Method used
	private $request_method;
	
	// Name of the unique uri to invoke
	private $request_uri;	
	
	// Name of the route
	private $request_name;
	
	// Parameters pulled from the URI
	private $parameter_arr;
	
	// Data passed along with the request
	private $data_arr;
	
	// Variable to hold the request format
	private $request_format;
	
	// Array to hold the status
	private $status_arr;
	
	// Variable to hold the response
	private $response;

	// Constructor
	public function __construct(&$router, $router_response) {
		
		// Check if you need to generate a 404. If the touter did not find a route, it will return false
		if($router_response !== FALSE){			
			$this->resource 		= $router_response['target']['resource'];
			$this->request_uri 		= $router_response['target']['request_uri'];
			$this->request_name 	= $router_response['name'];
			$this->request_method 	= strtoupper($_SERVER['REQUEST_METHOD']);
			$this->parameter_arr 	= $router_response['params'];
		}else{
			$this->resource 		= APP_ERROR_RESOURCE;
			$this->request_method 	= 'GET';
			$this->request_uri		= APP_ERROR_NAME;
			$this->request_name 	= 'error';
			$this->parameter_arr	= Array();
		}
		
		$this->router				= $router;
		$this->request_format		= NULL;	
		$this->status_arr			= NULL;
		$this->response				= NULL;			
		
		// Merge the data_arr and parameters from the URI if they exist
		if(is_array($router_response['params'])){
			$this->data_arr = array_merge_recursive($this->getRequestData(), $router_response['params']);
		}else{
			$this->data_arr = $this->getRequestData();
		}
	}	
	
	// Gather the data submitted depending on what method was used
	private function getRequestData() {
		
		$format = $_SERVER['HTTP_ACCEPT'];
		
		// Set request format	
        if (preg_match('/json/', $format)) {
            $this->request_format = 'json';
        } elseif (preg_match('/html/', $format)) {
            $this->request_format = 'html';
        } elseif (preg_match('/xml/', $format)) {
            $this->request_format = 'xml';
        } else {
            $this->request_format = 'json';
        }
		
		// Store request data here
		$data_arr = array();
		// Sift through the cases to find which request method was used
		switch ($this->request_method) {
			case 'GET' :
				$data_arr = $_GET;
				break;
			case 'POST' :
				$data_arr = $_POST;
				break;
			case 'PUT' :
				parse_str(file_get_contents('php://input'), $data_arr);
				break;
			case 'DELETE' :				
				parse_str(file_get_contents('php://input'), $data_arr);
				break;
			case 'HEAD' :
				parse_str(file_get_contents('php://input'), $data_arr);
				break;
			case 'OPTIONS' :
				parse_str(file_get_contents('php://input'), $data_arr);
				break;
			default :
				break;
		}
		
		if (array_key_exists('data', $data_arr))
			return $data_arr['data'];
		return $data_arr;
	}
	
	// Get input format
	public function getRequestFormat(){
        return $this->request_format;
    }
	
	// Process the request by calling the Constant defined in the configs
	public function processRequest(){
		
		// Make sure that the request format is valid
        $format = $this->getRequestFormat();
		
		// If the request is not valid, then throw a 415
        if (!preg_match('/json/', $format) && !preg_match('/xml/', $format)) {
            $this->status_arr = Array('status_code' => '415','status_message' => "The server supports these formats: JSON");
			return;
        } 
		
		// Based on type of response reform data to what is expected
        switch ($format) {
            case 'json':				
                $response = @json_decode($this->data_arr);
				if ($response === NULL && json_last_error() !== JSON_ERROR_NONE) {
				    $this->setStatus(400, NULL);
					return;
				}
				break;
			case 'xml':				
                $response = $this->convertPHPToXML($this->getResponse());
				if ($response === FALSE) {
				    $this->setStatus(400, NULL);
					return;
				}
				break;
            default:
                die;
                break;
        }
		
		// Transform Constants into a dynamic variable. Constants cannot be used dynamic function calling :(		
		$app_entry_point 			= '\\'.str_replace('{resource}', $this->resource, APP_ENTRY_POINT);
		$app_status_exit_point 		= APP_STATUS_EXIT_POINT;
		$app_response_exit_point 	= APP_RESPONSE_EXIT_POINT;
		
		// Instantiate new application object and process request
		$app_obj = new $app_entry_point($this->request_method, $this->request_uri, $this->data_arr, $this->router, $this->parameter_arr, $this->request_name);
				
		// Retreive status array and response
		$this->status_arr = $app_obj->$app_status_exit_point();
		$this->response = $app_obj->$app_response_exit_point();
	}
	
	// Return the status of the request
	public function getStatus(){
		return $this->status_arr;		
	}
	
	// Set status code
	private function setStatus($status_code, $status_message){
		$this->status_arr = Array('status_code' => $status_code, 'status_message' => $status_message);
		return;
    }	
	
	// Return the reponse of the request
	public function getResponse(){
		return $this->response;		
	}
}