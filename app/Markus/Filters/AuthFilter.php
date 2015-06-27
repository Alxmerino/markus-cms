<?php namespace Markus\Filters;

/**
* Auth Filter
*/
class AuthFilter
{
	
	function __construct($router)
	{
		$router->filter('auth', function() {
			if (!isset($_SESSION['user'])) {
				header('Location: /login');

				return false;
			}
		});

		return $router;
	}
}