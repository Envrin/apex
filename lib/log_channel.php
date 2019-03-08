<?php
declare(strict_types = 1);

namespace apex;

use apex\log;

class log_channel extends log
{

    public static $channel;

/**
* Creates a new log channel, allowing for easy filtering 
( of logs specifically for your code / application.
* 
*     @param string $channel_name The alpha-numeric channel name to create.
*/
public function __construct(string $channel_name) 
{
    self::$channel = $channel_name;
}

/**
* The below methods mimic the methods within the log.php class for 
* the various log levels, but temporarily 
* change the channel name white adding the log entry.
*/
public static function debug(string $message, ...$vars) { 
    parent::$channel = self::$channel;
    parent::debug($message, $vars);
    parent::$channel = 'apex';
}

public static function info(string $message, ...$vars) { 
    parent::$channel = self::$channel;
    parent::info($message, $vars);
    parent::$channel = 'apex';
}

public static function warning(string $message, ...$vars) { 
    parent::$channel = self::$channel;
    parent::warning($message, $vars);
    parent::$channel = 'apex';
}

public static function notice(string $message, ...$vars) { 
    parent::$channel = self::$channel;
    parent::notice($message, $vars);
    parent::$channel = 'apex';
}

public static function error(string $message, ...$vars) { 
    parent::$channel = self::$channel;
    parent::error($message, $vars);
    parent::$channel = 'apex';
}

public static function critical(string $message, ...$vars) { 
    parent::$channel = self::$channel;
    parent::critical($message, $vars);
    parent::$channel = 'apex';
}

public static function alert(string $message, ...$vars) { 
    parent::$channel = self::$channel;
    parent::alert($message, $vars);
    parent::$channel = 'apex';
}

public static function emergency(string $message, ...$vars) { 
    parent::$channel = self::$channel;
    parent::emergency($message, $vars);
    parent::$channel = 'apex';
}
}

