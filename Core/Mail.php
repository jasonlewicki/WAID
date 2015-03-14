<?php

namespace WAID\Core;

class Mail {

	// DB Objects
	protected $db_conn;
	protected $db;	
	
	// Parameter Array
	protected $parameter_arr;
	
	// Mail Host
	protected $host;
	
	// Constructor
	public function __construct($db_conn, $db) {
		$this -> db_conn = $db_conn;
		$this -> db = $db;
		
		$this->parameter_arr = array();
		
		preg_match("/[^\.\/]+\.[^\.\/]+$/", $_SERVER['HTTP_HOST'], $matches);
		$this->host = $matches[0];
		
	}
	
	public function parameterAdd($from, $to){
		$this->parameter_arr["{{".$from."}}"] = $to;
	}
	
	public function mailSend($to_name, $locale_language_id, $to_email){
		$mail_row = $this->mailGetByNameAndLanguage($to_name, $locale_language_id);		
		
		$to 		= $this->parameter_arr['{{full_name}}']." <$to_email>";		
		$subject 	= $mail_row['mail_subject'];
		$message 	= strtr(str_replace("{{body}}",$mail_row['mail_body_html'],$mail_row['mail_frame_html']), $this->parameter_arr);
		$headers 	= "From: noreply@".$this->host."\r\n";
		$headers   .= "Reply-To: ".$mail_row['mail_from']."@".$this->host."\r\n";
		$headers   .= "MIME-Version: 1.0\r\n";
		$headers   .= "Content-Type: text/html; charset=utf-8\r\n";
		
		mail($to, $subject, $message, $headers);
		
		return;		
	}
	
	private function mailGetByNameAndLanguage($email_name, $locale_language_id){
		$sql_str = "CALL " . $this -> db . ".mailGetByNameAndLanguage('$email_name', $locale_language_id)";
		return $this -> db_conn -> getRow($sql_str);	
	}
}