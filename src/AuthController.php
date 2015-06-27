<?php 
/**
 * Files controller
 */
/**
 * The file system
 */
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as Adapter;

class AuthController {

	protected $fs;

	public function __construct() {
		$this->fs = new Filesystem(new Adapter(__DIR__));
	}

    public function anyIndex()
    {
    	// $contents = $this->fs->read('users.json');
        /**
         * TODO: This could probably go in a view
         */
        return '<form method="" action="/login">
                    <h2>Login</h2>
                    <fieldset>
                        <input name="text" type="text">
                        <input name="password" type="password">
                        <input type="submit" value="Login">
                    </fieldset>
                </form>';
        // return 'This is the default page and will respond to /controller and /controller/index';
    }


    public function getLoginController()
    {
        return 'This will respond to /controller/test with only a GET method';
    }

    public function postLoginController()
    {
        return 'This will respond to /controller/test with only a POST method';
    }
}
