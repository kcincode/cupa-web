<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


define('DEBUG', (isset($argv[1]) and $argv[1] == '--debug') ? true : false);

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap('db');
/*
echo "Import data from where?\n";

echo "  Server [cincyultimate.org]: ";
$host = trim(fgets(STDIN));
if(empty($host)) {
	$host = 'cincyultimate.org';
}

echo "Database Name [cincyu6_cupa]: ";
$db = trim(fgets(STDIN));
if(empty($db)) {
	$db = 'cincyu6_cupa';
}

echo "          Username [cincyu6]: ";
$username = trim(fgets(STDIN));
if(empty($username)) {
	$username = 'cincyu6';
}

echo "                    Password: ";
system('stty -echo');
$password =  trim(fgets(STDIN));
system('stty echo');

// original db connection string
try {
	$origDb = new PDO('mysql:dbname=' . $db . ';host=' . $host, $username, $password);
} catch(Exception $e) {
	echo 'Could not conned to source DB.  ' . $e->getMessage();
    exit();
}
*/
require_once('ProgressBar.php');
echo "\n\n";