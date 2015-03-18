<?php

# This file is if you need the WAID framework for just the database. For instance Cron jobs.

/*******************************
 * Include the necessary files
 ******************************/
 
// Autoloader
include WAID_ROOT.DIRECTORY_SEPARATOR.'autoload.php';

/*******************************
 * 			Database
 ******************************/

// Database Config
include APP_ROOT.DIRECTORY_SEPARATOR.'Configs'.DIRECTORY_SEPARATOR.'database.php';

// Initialize the Database Factory
include WAID_ROOT.DIRECTORY_SEPARATOR.'Core/Database.php';
WAID\Core\DatabaseFactory::constructDatabaseFactory($database[ENVIRONMENT]['host'], $database[ENVIRONMENT]['username'], $database[ENVIRONMENT]['password'], $database[ENVIRONMENT]['database']);
