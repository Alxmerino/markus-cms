<?php

use Phroute\RouteCollector as Phrouter;
use Phroute\Dispatcher as Dispatcher;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Dumper;

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
require_once __DIR__ .'/helper-classes/Spyc.php';

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
	// @var Twig instance
	private $twig;
	// @var YAML Parser
	private $yml;
	// @var YAML Dumper
	private $dumper;
	
	function __construct()
	{
		/**
		 * File System and config
		 */
		$this->filesystem = new Filesystem(new Adapter($_SERVER['DOCUMENT_ROOT']));
		$this->config = $this->getConfig();

		/**
		 * YAML Parser/Dumper
		 */
		$this->yml = new Parser();
		$this->dumper = new Dumper();
		
		/**
		 * Templating Engine
		 */
		Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem($_SERVER['DOCUMENT_ROOT'] . '/app/views');
		$this->twig = new Twig_Environment($loader, array(
			'debug' => true
		));

		/**
		 * Router
		 */
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
				// Yaml Editing
				$yamlMode = (isset($_GET['mode']) && $_GET['mode'] === 'yaml') ? true : false;
				
				// File Data
				$data = array();
				$data['error'] = '';

				// Valid extensions
				$markdownExtensions = array("mark", "markdown", "md", "mdml", "mdown", "mdtext", "mdtxt", "mdwn", "mkd", "mkdn");
				$yamlExtensions = array("yaml", "yml");

				// File contents
				$fileContents = $this->filesystem->read($this->config->app_path . '/' . $filename);
				$data['filename'] = $filename;

				// Get file info
				$fileInfo = pathinfo($this->config->app_path . '/' . $filename);
	
				if ($yamlMode) {
					/* Let's see if the file is a 
					   valid extension and YAML mode */
					if ( in_array($fileInfo['extension'], $markdownExtensions) !== FALSE
						|| in_array($fileInfo['extension'], $yamlExtensions) !== FALSE ) {

						// Lets try regular yaml
						try {

							$data['fields'] = array();
							$fields = $this->yml->parse($fileContents);

							foreach ($fields as $key => $value) {
								$data['fields'][$key] = $value;
							}

						} catch (ParseException $e) {
							$data['jekyll'] = true;

							// Let's do some jekyll like here
							$fileDataArray = explode('---', $fileContents);

							// Filter contents
							$fileDataArray = array_filter($fileDataArray);
							$fields = $this->yml->parse($fileDataArray[1]);

							foreach ($fields as $key => $value) {
								$data['fields'][$key] = $value;
							}

							$data['content'] = $fileDataArray[2];

						}
					} else {
						// @TODO Message: Yaml mode but file not supported
						$data['content'] = $fileContents;
					}
				} else {
					$data['content'] = $fileContents;
				}

				$data['markus'] = $this->objToArray($this->config->settings);
					
				return $this->twig->render('edit.html', $data);
			});

			$router->post('/edit', function() {
				$messages = array();
				$jekyllData = array();
				$path = $this->config->app_path . '/';
				$old_file = $_POST['old_file'];
				$filename = $_POST['filename'];
				$jekyll = $_POST['jekyll'];
				$contents = (isset($_POST['contents'])) ? $_POST['contents'] : '';
				$fields = (isset($_POST['fields'])) ? $_POST['fields'] : '';

				// Rename file if it doesnt exist
				if ( $old_file != $filename ) {
					$renamed = $this->filesystem->rename($path . $old_file, $path . $filename);

					// Add message
					if ($renamed) {
						$messages['renamed'] = 'File name change succesfully';
					}
				}

				// Save fields if any
				if (!empty($fields)) {
					$ymlStr = $this->dumper->dump($fields, 2);

					// Jekyll mode
					if ($jekyll == true) {
						$jekyllData[] = $ymlStr;
					} else {
						// Update the file
						$updated = $this->filesystem->update($path . $filename, $ymlStr);
						// Add message
						if ($updated) {
							$messages['fields'] = 'All fields updated successfully';
						}
					}

				}

				// Save contents if any
				if (!empty($contents)) {
					// Jekyll mode
					if ($jekyll == true) {
						$jekyllData[] = $contents;
					} else {
						$this->filesystem->update($path . $filename, $contents);
						$messages['contents'] = 'File contents updated succesfully';
					}
				}

				if (!empty($jekyllData)) {
					// Glue the data
					$jekyllData = implode("---", $jekyllData);
					$this->filesystem->update($path . $filename, $jekyllData);
					$messages['contents'] = 'Fields and content updated succesfully';
				}

				echo json_encode($messages);

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

	function objToArray($obj)
	{
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
		} else {
			// Return array
			return $obj;
		}
	}

	private function arrayToObject($array)
	{
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