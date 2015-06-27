<?php

use Markus\Routes\Routes;

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
	
	function __construct()
	{
		// TODO: Call Classes here
		new Routes();
	}

}