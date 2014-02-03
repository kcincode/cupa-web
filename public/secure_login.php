<?php
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

if(APPLICATION_ENV == 'production') {
    // Define path to application directory
    defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../cupaweb/application'));

    define('APPLICATION_WEBROOT', realpath(dirname(__FILE__)));
} else {
    // Define path to application directory
    defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

    define('APPLICATION_WEBROOT', APPLICATION_PATH . '/../public');
}
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';


// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

//Zend_Session::setId($_POST['session']);

$application->bootstrap('session')
            ->bootstrap();

$front = Zend_Controller_Front::getInstance();
$request = new Zend_Controller_Request_Apache404();
$response = new Zend_Controller_Response_Http();
$front->setRequest($request->setRequestUri('login'), $response)
      ->dispatch();

//$response->setHeader('Access-Control-Allow-Origin', '*', true);
$response->sendResponse();
