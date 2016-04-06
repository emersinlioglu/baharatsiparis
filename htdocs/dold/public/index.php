<?php

use Ffb\Common\Config\Loader;

//This makes our life easier when dealing with paths. Everything is relative to the application root now.
chdir(dirname(__DIR__));

//Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

/**
 * Do not edit this value!
 *
 * If you want to set a different environment value please define it in your .htaccess file
 * or in the server configuration.
 *
 * SetEnv APPLICATION_ENV development
 */
if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', (getenv('APPLICATION_ENV') != '') ? getenv('APPLICATION_ENV') : 'production');
}

/**
 * Set APPLICATION_CLIENT
 */
if (!defined('APPLICATION_CLIENT')) {
    define('APPLICATION_CLIENT', (getenv('APPLICATION_CLIENT') != '') ? getenv('APPLICATION_CLIENT') : null);
}

// Check current APPLICATION_ENV
switch (APPLICATION_ENV) {
    case 'development':
        // Display errors
        ini_set('display_errors', true);
        // Log errors to a file
        ini_set('log_errors', true);
        // Report all errors
        error_reporting(E_ALL);
        break;
    case 'testing':
        // Don't display errors
        ini_set('display_errors', false);
        // Log errors to a file
        ini_set('log_errors', true);
        // Report all errors
        error_reporting(E_ALL);
        break;
    case 'staging':
        // Don't display errors
        ini_set('display_errors', false);
        // Log errors to a file
        ini_set('log_errors', true);
        // Report all errors except notices
        error_reporting(E_ALL ^ E_NOTICE);
        break;
    case 'production':
        // Don't display errors
        ini_set('display_errors', false);
        // Log errors to a file
        ini_set('log_errors', true);
        // Report all errors except notices
        error_reporting(E_ALL ^ E_NOTICE);
        break;
}

//Setup autoloading
//error_log('===========');
//if (file_exists('init_autoloader.php')) {
//    error_log('yes : init_autoloader.php');
//    require 'init_autoloader.php';
//} else {
//    error_log('no : init_autoloader.php');
//}
//
//if (file_exists('../init_autoloader.php')) {
//    error_log('yes : ../init_autoloader.php');
//    require '../init_autoloader.php';
//} else {
//    error_log('no : ../init_autoloader.php');
//}

require __DIR__ . '/../init_autoloader.php';
//if (file_exists(__DIR__ . '/../init_autoloader.php')) {
//    error_log('yes : 3');
//} else {
//    error_log('no : 3');
//}



// Run the application! plese disable modules in application.ini files, not here
$whiteModulesList = array(
    'DoctrineModule',
    'DoctrineORMModule',
    'ZendDeveloperTools'
);

if (substr($_SERVER['REQUEST_URI'], 0, '4') === '/api') {
    $whiteModulesList[] = 'Ffb\Api';
} else {
    $whiteModulesList[] = 'Ffb\Backend';
    $whiteModulesList[] = 'SmartyModule';
}

Zend\Mvc\Application::init(
    Loader::getApplicationConfig(
        dirname(__DIR__),
        $whiteModulesList
    )
)->run();