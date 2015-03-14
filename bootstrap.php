<?php

/**
 * Include the necessary files
 */
 
// Autoloader
include WAID_ROOT.DIRECTORY_SEPARATOR.'autoload.php';

/**
 * Database
 */

// Database Config
include APP_ROOT.DIRECTORY_SEPARATOR.'Configs'.DIRECTORY_SEPARATOR.'database.php';

// Initialize the Database Factory
include WAID_ROOT.DIRECTORY_SEPARATOR.'Core/Database.php';
WAID\Core\DatabaseFactory::constructDatabaseFactory($database[ENVIRONMENT]['host'], $database[ENVIRONMENT]['username'], $database[ENVIRONMENT]['password'], $database[ENVIRONMENT]['database']);


/**
 * Router
 */

// Create Router Object
$router = new WAID\Core\Router();

// Gather all Routes
if (is_dir(APP_ROOT.DIRECTORY_SEPARATOR.'Routes')) {
    $dir = APP_ROOT.DIRECTORY_SEPARATOR.'Routes';
}
foreach (glob($dir.DIRECTORY_SEPARATOR.'*.php') as $filename) {
    include $filename;
}

// Match request against router mappings
$match = $router->match();


/**
 * Request
 */

// Initialize the request class, which parses the incoming call.
$request_obj = new WAID\Core\Request($router, $match);

// Process the Request
$request_obj->processRequest();

// Get the status and response arr from the application. Use these to form a response back to the sender.
$status_arr = $request_obj->getStatus();
$response = $request_obj->getResponse();


/**
 * Response
 */
 
// Initialize the response class, which return data to the sender.
$response_obj = new WAID\Core\Response($status_arr, $response);

// Send Response
$response_obj->sendResponse();