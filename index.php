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
 * The file system
 */
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;

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
 * Files controller
 */
class FileController {

	protected $fs;

	public function __construct() {
		$this->fs = new Filesystem(new Adapter(__DIR__));
	}

    public function anyIndex()
    {
    	$this->fs->write('index.txt', 'contents');
        // return 'This is the default page and will respond to /controller and /controller/index';
    }

    /**
    * One required paramter and one optional parameter
    */
    public function anyFileController($param, $param2 = 'default')
    {
        return 'This will respond to /controller/test/{param}/{param2}? with any method';
    }

    public function getFileController()
    {
        return 'This will respond to /controller/test with only a GET method';
    }

    public function postFileController()
    {
        return 'This will respond to /controller/test with only a POST method';
    }

    public function putFileController()
    {
        return 'This will respond to /controller/test with only a PUT method';
    }

    public function deleteFileController()
    {
        return 'This will respond to /controller/test with only a DELETE method';
    }
}

/**
 * Define routes
 */
$router->group(array('before' => 'auth'), function($router) {

	$router->controller('/', 'FileController');
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