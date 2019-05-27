<?php
declare(strict_types = 1);

namespace apex\core\lib;


/**
* Handles logging all debugger line items, and displaying the 
( debug results within the browser when necessary.
*/
class debug 
{

    // Properties
    protected static $start_time = 0;
    protected static $notes = array();
    protected static $trace = array();
    protected static $sql = array();
    public static $data = array();


/**
* Add entry to debug session
*     @param int $level Number beterrn 1 - 3 defining the level of entry.
*     @param string $message The message to add
*     @param string $file File number (__FILE__)
*     @param int $line_number The line number of call (__LINE__)
*     @param string $log_level Optional, and will add appropriate log item via logger if not debug.
*/
public static function add(int $level, string $message, string $file = '', int $line_number = 0, $log_level = 'debug', $is_system = 0)
{

    // Check if DEBUG_LEVEL defined
    if (!defined('DEBUG_LEVEL')) { return; }

    // Add log
    if ($log_level != 'debug') { 
        log::add_system_log($log_level, $is_system, $file, $line_number, $message);
    }
    if (self::$start_time == 0) { self::$start_time = time(); }

    // Check debug level
    if ($level > DEBUG_LEVEL) { return false; }
    if (registry::config('core:mode') != 'devel') { return false; }
    if (registry::$route == '500') { return false; }

    // Add entry to notes array
    $vars = array(
        'level' => $log_level, 
        'note' => $message,
        'file' => trim(str_replace(SITE_PATH, '', $file), '/'), 
        'line' => $line_number, 
        'time' => time()
    );
    array_unshift(self::$notes, $vars);

    // Add log
    if ($log_level == 'debug') { 
        log::add_system_log($log_level, 0, $file, $line_number, $message);
    }

}

/**
* Add SQAL query
*      @param string $sql_query The SQL query that was executed
*/
public static function add_sql(string $sql_query)
{
    array_unshift(self::$sql, $sql_query);
} 

/**
* Finish the session, compileall notes and data gatherered during request, 
* and put them into redis for later display.  This is executed by the registry 
* class at the end of each request.
*/
public static function finish_session() 
{

    // Check if we're debugging
    if (registry::config('core:debug') < 1 && registry::config('core:mode') != 'devel') { 
        return; 
    }
    if (registry::$http_controller == 'admin' && registry::$route == 'devkit/debugger') { return; }

    // Set data array
    $data = array(
        'date' => registry::get_logdate(), 
        'start_time' => self::$start_time, 
        'end_time' => time(), 
        'registry' => array(
            'request_method' => registry::$request_method, 
            'service' => registry::$service, 
            'http_controller' => registry::$http_controller, 
            'route' => registry::$route, 
            'ip_address' => registry::$ip_address, 
            'user_agent' => registry::$user_agent, 
            'userid' => registry::$userid, 
            'timezone' => registry::$timezone, 
            'language' => registry::$language, 
            'panel' => registry::$panel, 
            'theme' => registry::$theme, 
            'action' => registry::$action
        ), 
        'post' => registry::getall_post(), 
        'get' => registry::getall_get(), 
        'cookie' => registry::getall_cookie(), 
        'server' => registry::getall_server(), 
        'sql' => self::$sql, 
        'backtrace' => self::get_backtrace(), 
        'notes' => self::$notes
    );
    self::$data = $data;

    // Return if we're not saving
    if (registry::config('core:debug') < 1) { return; }

    // Save json to redis
    registry::$redis->set('config:debug_log', json_encode($data));
    registry::$redis->expire('config:debug_log', 10800);

    // Save response output
    file_put_contents(SITE_PATH . '/log/response.txt', registry::get_response());

    // Update config, as needed
    if (registry::config('core:debug') != 2) { 
        registry::update_config_var('core:debug', '0');
    }

}

/**
* Go through and format the backtrace as necessary 
* for the debug session.
*/
public static function get_backtrace(array $stack = array()):array
{

    // Get back trace
    if (count(self::$trace) > 0) { 
        return self::$trace;

    } elseif (count($stack) == 0) { 
        $stack = debug_backtrace(0);
        array_splice($stack, 0, 2);
    }

    // Go through stack
    $trace = array();
    foreach ($stack as $vars) {
        $vars['file'] = isset($vars['file']) ? trim(str_replace(SITE_PATH, '', $vars['file']), '/') : '';
        if (!isset($vars['line'])) { $vars['line'] = ''; }
        if (isset($vars['args'])) { unset($vars['args']); }
        if (!isset($vars['class'])) { $vars['class'] = ''; }
        array_push($trace, $vars);
    }
    self::$trace = $trace;

    // Return
    return $trace;

}


}

