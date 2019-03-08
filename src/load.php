<?php
declare(strict_types = 1);

namespace apex;

use DB;

// Define component types
define('COMPONENT_TYPES', array(
    'ajax', 
    'autosuggest', 
    'controller', 
    'cron', 
    'form', 
    'htmlfunc', 
    'lib', 
    'modal', 
    'tabcontrol',
    'tabpage',  
    'table', 
    'template', 
    'test', 
    'worker')
);

// Get site path
$site_path = preg_replace("/\/src$/", "", realpath(dirname(__FILE__)));
define ('SITE_PATH', $site_path);

// Load autoloaders
require_once(SITE_PATH . '/src/autoload.php');
require_once(SITE_PATH . '/vendor/autoload.php');

// Load files
require_once(SITE_PATH . '/etc/config.php');
require_once(SITE_PATH . '/lib/functions.php');

// Start registry
registry::create();

// Load database driver
if (!defined('REDIS_HOST')) { 
    $db_driver = 'mysql';
} else { 
    if (!$db_driver = registry::$redis->hget('config', 'db_driver')) { $db_driver = 'mysql'; }
}
require_once(SITE_PATH . '/lib/db/' . $db_driver .'.php');

// Set error reporting
error_reporting(E_ALL);
set_exception_handler('handle_exception');
//set_error_handler('\error');

// Start session
session_start();

// Set time zone
date_default_timezone_set('UTC');

// Set INI variables
ini_set('pcre.backtrack_limit', '4M');
ini_set('zlib.output_compression_level', '2');

// Check if installed
if (!defined('REDIS_HOST')) {
    $installer = new installer();
    $installer->run_wizard();                             
}

// Set RabbitMQ constants
if (registry::$redis->exists('config:rabbitmq')) { 
    $vars = registry::$redis->hgetall('config:rabbitmq');
    define('RABBITMQ_HOST', $vars['host']);
    define('RABBITMQ_PORT', $vars['port']);
    define('RABBITMQ_USER', $vars['user']);
    define('RABBITMQ_PASS', $vars['pass']);
}
if (!defined('RABBITMQ_HOST')) { define('RABBITMQ_HOST', 'localhost'); }


