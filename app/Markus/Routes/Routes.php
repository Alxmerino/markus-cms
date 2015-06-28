<?php 

namespace Markus\Routes;
use Phroute\RouteCollector as Phrouter;
use Phroute\Dispatcher as Dispatcher;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;

/**
* Handle Routes
*/
class Routes
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

		// Main route
		$router->group(array('before' => 'auth'), function($router) {

			$router->get('/', function() {
				
				/* Get config data */
				$this->config = $this->getConfig();
				$contents = $this->filesystem->listContents($this->config->app_path);
				echo '<pre>';
	print_r($contents);
echo '</pre>';
			});
			// $router->controller('/', 'FileController');
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