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
	public $router;
	// @var Dispatcher
	public $dispatcher;
	// @var Filters
	public $filesystem;
	// @array CMS config
	private $config = array();
	
	function __construct()
	{
		$this->filesystem = new Filesystem(new Adapter($_SERVER['DOCUMENT_ROOT']));
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

				$outputHTML = '<ul>';
				
				/* Get config data */
				$this->config = $this->getConfig();
				$appContents = $this->filesystem->listContents($this->config->app_path);

				foreach ($appContents as $key => $value) {
					$outputHTML .= '<li>';
					$outputHTML .= $value['basename'];
					$outputHTML .= ' <a href="edit/';
					$outputHTML .= $value['basename'];
					$outputHTML .= '">Edit</a></li>';
				}

				$outputHTML .= '</ul>';

				return $outputHTML;

			});

			$router->get('/edit/{filename:c}', function($filename) {
				$path = $this->getConfig()->app_path;
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
}