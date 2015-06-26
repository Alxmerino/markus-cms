<?php

/**
 * Vars
 */
$debug = 1;

/**
 * Turn debugging on
 */
if ($debug) {
	// error reporting
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}
/**
 * Require autoload
 */
require 'vendor/autoload.php';
 
$router = new Phroute\RouteCollector();

/**
 * Define main route
 */
$router->get('/', function() {
	// Add this to class
	return 'Main admin page';
});


/**
 * Dispatch router
 */
$dispatcher = new Phroute\Dispatcher($router);


/**
 * Print our response from dispatcher
 */
$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
echo $response;