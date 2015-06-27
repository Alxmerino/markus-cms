<?php 

namespace Markus\Routes;
use Phroute\RouteCollector as Phrouter;
use Phroute\Dispatcher as Dispatcher;

/**
* Handle Routes
*/
class Routes
{
	// Vars
	public $router;
	public $dispatcher;
	
	function __construct()
	{
		$this->router = $this->routes(new Phrouter());
		$this->dispatcher = new Dispatcher($this->router);
		$this->serveResponse($this->dispatcher);
	}

	function routes($router)
	{
		$router->get('/', function() {
			return 'Hello World';
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