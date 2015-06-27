<?php

use Markus\Routes\Routes;
use Markus\Routes\Filters;

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
 * Require autoload and dependencies
 */
require_once __DIR__ .'/../vendor/autoload.php';

class MarkusCMS
{
	
	function __construct()
	{
		// TODO: Call Classes here
		new Routes();
		new Filters();
	}

}