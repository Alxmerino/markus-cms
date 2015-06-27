<?php namespace Markus\Routes;

/**
* Handle Route Filters
*/
class Filters
{
	
	function __construct($router)
	{
		$router->filter('auth', function() {
			// 
		});

		return $router;
	}
}