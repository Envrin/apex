<?php
declare(strict_types = 1);

namespace apex;

use DB;

class log
{

    // Properties
    public static $channel = 'apex';
    protected static $log_file;
    protected static $log_line;
    protected static $is_system = 0;

/**
* Initiates anew class, allowing for channel creation
*/
public function __construct(string $channel_name = 'apex') { 
    self::$channel = $channel_name;
}

/**
* Starts the session, and sets variables such as the log date.
*/
public static function start() 
{

    // Get URI
    $uri = registry::$http_controller == 'http' ? 'public' : registry::$http_controller;
    $uri = ' /' . $uri . '/' . registry::$route;

    // Add request log line
    $line = '[' . registry::get_logdate() . '] (' . registry::$ip_address . ') ' . registry::$request_method . $uri . "\n";
    file_put_contents(SITE_PATH . '/log/access.log', $line, FILE_APPEND);

}

/**
* DEBUG message.
*/
public static function debug(string $msg, ...$vars)
{

    // Add log
    self::add_log('debug', $msg, $vars);

}

/**
* INFO message.
*/
public static function info(string $msg, ...$vars)
{

    // Add log
    self::add_log('info', $msg, $vars);

}

/**
* WARNING message.
*/
public static function warning(string $msg, ...$vars)
{

    // Add log
    self::add_log('warning', $msg, $vars);

}

/**
* NOTICE message.
*/
public static function notice(string $msg, ...$vars)
{

    // Add log
    self::add_log('notice', $msg, $vars);

}

/**
* ERROR message.
*/
public static function error(string $msg, ...$vars)
{

    // Add log
    self::add_log('error', $msg, $vars);

}

/**
* CRITICAL message.
*/
public static function critical(string $msg, ...$vars)
{

    // Add log
    self::add_log('critical', $msg, $vars);

}

/**
* ALERT message
*/
public static function alert(string $msg, ...$vars)
{

    // Add log
    self::add_log('alert', $msg, $vars);

}

/**
* EMERGENCY message.
*/
public static function emergency(string $msg, ...$vars)
{

    // Add log
    self::add_log('emergency', $msg, $vars);

}

/**
* Adds new line to log file
*/
protected static function add_log(string $type, string $msg, ...$vars) 
{ 

    // Check log level
    if ($type != 'debug' && !in_array($type, LOG_LEVEL)) { 
        return;
    }

    // Define log line
    $file_var = self::$log_file != '' ? '[' . self::$log_file . ':' . self::$log_line . '] ' : '';
    $line = '(' . self::$channel . ") " . $file_var . fmsg($msg, $vars) . "\n";
    $msg_line ='[' . strtoupper($type) . '] ' . $line;
    $type_line = '[' . registry::get_logdate() . '] ' . $line;

    // Add lines to log files
    file_put_contents(SITE_PATH . '/log/' . $type . '.log', $type_line, FILE_APPEND);
    file_put_contents(SITE_PATH . '/log/messages.log', $msg_line, FILE_APPEND);
    if (self::$is_system == 1) { 
        file_put_contents(SITE_PATH . '/log/system.log', $msg_line, FILE_APPEND);
    }

    // Blank variables
    self::$log_file = '';
    self::$log_line = '';
    self::$is_system = 0;

}
/**
* Adds a system log, and should never be executed manually.  This 
* is used when PHP throws an error or the trigger_error() function is used, plus when 
* a DEBUG level message comes in.  This is due to the fact file and line 
* numbers are included, plus for filtering reasons.
* 
*     @param string $type The type / level of log message
*     @param int $is_debug Whether this message is from the debugger.  If 0, then it's a PHP / trigger_error() log.
*     @param $file The __FILE__ variable captured.
*     @param int $line The __LINE__ variable captured.
*     @param string $msg The log message.
*     @param array $vars Values to replace placeholders in the message with.
*/
public static function add_system_log(string $type, int $is_system = 0, string $file, int $line, string $msg, ...$vars)
{

    // Set variables
    self::$log_file = trim(str_replace(SITE_PATH, "", $file), '/');
    self::$log_line = $line;
    self::$is_system = $is_system;

    // Add log
    self::$type($msg, $vars);

}

/**
* Finish, and save logs to file
*/
public static function finish() 
{}

}
