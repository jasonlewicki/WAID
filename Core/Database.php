<?php

namespace WAID\Core;

// Database Factory for use in a singleton design pattern
class DatabaseFactory
{
	private static $database_factory;
	private $db;	
	
	private static $host;
	private static $username; 
	private static $password; 
	private static $database;

	public static function constructDatabaseFactory($host, $username, $password, $database){		
		if (!self::$database_factory){
			
			self::$host = $host;
			self::$username = $username;
			self::$password = $password;
			self::$database = $database;
						
			self::$database_factory = new DatabaseFactory();
		}	
		return self::$database_factory;
	}
	
	public static function getDatabaseFactory()
    {
        if (!self::$database_factory)
            self::$database_factory = new $database_factory();
        return self::$database_factory;
    }

	public function getConnection(){
		if (!$this->db){
			$this->db = new Database(self::$host, self::$username, self::$password, self::$database);
		}
		return $this->db;
	}
		
	// Disable since it is a static class
	protected function __construct(){}	
	private function __clone(){}	
	private function __wakeup(){}
}

// Class to interact with databases
class Database {

	private $db_conn;
	private $db;

	public function __construct($host, $username, $password, $database) {
		$this->db_conn = new \mysqli($host, $username, $password, $database);
		$this->db = $database;		
	}
	
	public function getDB() {
		return $this->db;
	}

	public function getRow($sql_statement) {
		return $this->executeQuery($sql_statement, 'single');
	}

	public function getArray($sql_statement) {
		return $this->executeQuery($sql_statement, 'multi');
	}

	public function runQuery($sql_statement) {
		return $this->executeQuery($sql_statement, 'none');
	}

	public function getCharSet() {
		return $this->db_conn->character_set_name();
	}

	public function setCharSet($passed_string) {
		return $this->db_conn->set_charset($passed_string);
	}

	public function escapeString($passed_string) {
		return $this->db_conn->escape_string($passed_string);
	}

	public function lastInsertId() {
		return $this->db_conn->insert_id;
	}

	private function executeQuery($sql_statement, $return_type) {
		if ($this->db_conn->multi_query($sql_statement)) {
			if ($return_type == 'single') {
				$row = array();
				if ($result = $this->db_conn->store_result()) {
					$row = $result->fetch_assoc();
					$result->free();
				}

				while ($this->db_conn->more_results() && $this->db_conn->next_result()) {
					$extraResult = $this->db_conn->use_result();
					if ($extraResult instanceof mysqli_result) {
						$extraResult->free();
					}
				}

				return $row;
			} elseif ($return_type == 'multi') {
				$result_arr = array();
				if ($result = $this->db_conn->store_result()) {
					while ($row = $result->fetch_assoc()) {
						$result_arr[] = $row;
					}
					$result->free();
				}

				while ($this->db_conn->more_results() && $this->db_conn->next_result()) {
					$extraResult = $this->db_conn->use_result();
					if ($extraResult instanceof mysqli_result) {
						$extraResult->free();
					}
				}

				return $result_arr;
			} elseif ($return_type == 'none') {
				return true;
			}
		} else {
			error_log("SQL ERROR:" . $this->db_conn->error, 0);
			error_log("SQL QUERY:" . $sql_statement, 0);
			return false;
		}
	}
	
	public function closeConnection() {
		return $this->db_conn->close();
	}
}