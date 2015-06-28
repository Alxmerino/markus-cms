<?php

use Phroute\RouteCollector as Phrouter;
use Phroute\Dispatcher as Dispatcher;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;

/**
 * Vars
 */
$debug = 1;

/**
 * Turn debugging on
 * error reporting
 */
if ($debug) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}

/**
 * Require autoload and dependencies
 */
require_once __DIR__ .'/../vendor/autoload.php';

class MarkusCMS
{
	// @var Phroute
	private $router;
	// @var Dispatcher
	private $dispatcher;
	// @var Filters
	private $filesystem;
	// @array CMS config
	private $config;
	// Twig instance
	private $twig;
	
	function __construct()
	{
		$this->filesystem = new Filesystem(new Adapter($_SERVER['DOCUMENT_ROOT']));
		$this->config = $this->getConfig();
		
		Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem($_SERVER['DOCUMENT_ROOT'] . '/app/views');
		$this->twig = new Twig_Environment($loader, array(
			'debug' => true
		));

		$this->router = $this->routes(new Phrouter());
		$this->filters($this->router);
		$this->dispatcher = new Dispatcher($this->router);
		$this->serveResponse($this->dispatcher);
	}

	/**
	 * Define all routes
	 * @param  @var $router Phrouter instance
	 * @return @var return all routes
	 */
	function routes($router)
	{
		/**
		 * Login route
		 */
		$router->get('/login', function() {
			return "You must login to proceed";
		});

		// Main routes group
		$router->group(array('before' => 'auth'), function($router) {

			// Files list
			$router->get('/', function() {

				$data = array();
				$data['data'] = $this->filesystem->listContents($this->config->app_path);
				$data['markus'] = $this->objToArray($this->config->settings);

				return $this->twig->render('dash.html', $data);

			});

			$router->get('/edit/{filename:c}', function($filename) {
				$path = $this->config->app_path;
				$fileContents = $this->filesystem->read($path . '/' . $filename);
				
				return $fileContents;
			});
		});

		// Return the router
		return $router;
	}

	/**
	 * Get config data
	 * @return stdClass
	 */
	function getConfig()
	{
		return json_decode($this->filesystem->read('.config'));
	}

	function filters($router)
	{
		$router->filter('auth', function() {
			// Set login session here
		});
	}

	function serveResponse($dispatcher)
	{
		$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		echo $response;
	}

	function objToArray($obj) {
		if (is_object($obj)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$obj = get_object_vars($obj);
		}

		if (is_array($obj)) {
			/*
			* Return array converted to object
			* Using __METHOD__ (Magic constant)
			* for recursive call
			*/
			return array_map(__METHOD__, $obj);
		}
		else {
			// Return array
			return $obj;
		}
	}

	private function arrayToObject($array) {
		if (is_array($array)) {
			/*
			* Return array converted to object
			* Using __METHOD__ (Magic constant)
			* for recursive call
			*/
			return (object) array_map(__METHOD__, $array);
		} else {
			// Return object
			return $array;
		}
	}
}