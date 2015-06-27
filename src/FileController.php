<?php 
/**
 * Files controller
 */

/**
 * The file system
 */
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;

class FileController {

	protected $fs;

	public function __construct() {
		$this->fs = new Filesystem(new Adapter(__DIR__));
	}

    public function anyIndex()
    {
    	// $this->fs->write('index.txt', 'contents');
        return 'This is the default page and will respond to /controller and /controller/index';
    }

    /**
    * One required paramter and one optional parameter
    */
    public function anyFileController($param, $param2 = 'default')
    {
        return 'This will respond to /controller/test/{param}/{param2}? with any method';
    }

    public function getFileController()
    {
        return 'This will respond to /controller/test with only a GET method';
    }

    public function postFileController()
    {
        return 'This will respond to /controller/test with only a POST method';
    }

    public function putFileController()
    {
        return 'This will respond to /controller/test with only a PUT method';
    }

    public function deleteFileController()
    {
        return 'This will respond to /controller/test with only a DELETE method';
    }
}
