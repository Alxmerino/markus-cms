<?php 

namespace Markus\Routes;
use Markus\Filters\AuthFilter;
use Phroute\RouteCollector as Phrouter;
use Phroute\Dispatcher as Dispatcher;

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
	public $filters;
	
	function __construct()
	{
		$this->router = $this->routes(new Phrouter());
		$this->filters = new AuthFilter($this->router);
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

		$router->get('/login', function() {
			return "You must login to proceed";
		});

		$router->group(array('before' => 'auth'), function($router) {

			$router->get('/', function() {
				return 'Hello Admin';
			});
			// $router->controller('/', 'FileController');
		});

		// Return the router
		return $router;
	}

	function serveResponse($dispatcher)
	{
		$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		echo $response;
	}
}