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
 
/**
 * The router
 * @var Phroute
 */
$router = new Phroute\RouteCollector();

/**
 * Filters
 */
$router->filter('auth', function() {
	// if (!isset($_SESSION['user'])) {
	// 	header('Location: /login');

	// 	return false;
	// }
});

/**
 * Define routes
 */
$router->group(array('before' => 'auth'), function($router) {
	$router->get('/', function() {
		// Add this to controller
		return 'Main admin page';
	});
});

// Login route
$router->get('/login', function() {
	// Add this to controller
	return 'Auth the user here';
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