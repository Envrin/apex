<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;
use apex\core\hashes;

class date
{

    // Properties
    public static $secs_hash = array(
        'I' => 60, 
        'H' => 3600, 
        'D' => 86400, 
        'W' => 604800  , 
        'M' => 2592000 , 
        'Y' => 31536000 
    );


/**
** Add interval to date.
*      @param string $interval The time interval to add formateed in standard Apex format (eg. M1 = 1 month, I30 = 30 minutes, 6H = 6 hours)
*     @param string $from_date The date to add the interval to, defaults to now().  Can be either time in seconds since epoch, or standard timestamp format (YYYY-MM-DD HH:II:SS)
*     @param bool $return_datestamp If set to false, returns the seconds from epoch, and otherwise returns timestamp (YYYY-MM-DD HH:II:SS)
*     @return string The new date.
*/
public static function add_interval(string $interval, $from_date = 0, $return_datestamp = true)
{

    // Parse interval
    if (!preg_match("/^(\w)(\d+)$/", $interval, $match)) { 
        throw new ApexException('error', "Invalid date interval specified, {1}", $interval);
    }

    // Get start date / time
    if (preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/", (string) $from_date, $d)) { 
        $from_date = mktime((int) $d[4], (int) $d[5], (int) $d[6], (int) $d[2], (int) $d[3], (int) $d[1]);
    } elseif ($from_date == 0) { 
        $from_date = time();
    }

    // Modify date as needed
    $secs = (self::$secs_hash[$match[1]] * $match[2]);
    $from_date += $secs;

    // Return
    if ($return_datestamp === true) { $from_date = date('Y-m-d H:i:s', $from_date); }
    return $from_date;

}

/**
* Subtract interval from date.
*      @param string $interval The time interval to add formateed in standard Apex format (eg. M1 = 1 month, I30 = 30 minutes, 6H = 6 hours)
*     @param string $from_date The date to add the interval to, defaults to now().  Can be either time in seconds since epoch, or standard timestamp format (YYYY-MM-DD HH:II:SS)
*     @param bool $return_datestamp If set to false, returns the seconds from epoch, and otherwise returns timestamp (YYYY-MM-DD HH:II:SS)
*     @return string The new date.
*/
public static function subtract_interval(string $interval, $from_date = 0, $return_datestamp = true)
{

    // Parse interval
    if (!preg_match("/^(\w)(\d+)$/", $interval, $match)) { 
        throw new ApexException('error', "Invalid date interval specified, {1}", $interval);
    }

    // Get start date / time
    if (preg_match("/^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/", (string) $from_date, $d)) { 
        $from_date = mktime((int) $d[4], (int) $d[5], (int) $d[6], (int) $d[2], (int) $d[3], (int) $d[1]);
    } elseif ($from_date == 0) { 
        $from_date = time();
    }

    // Modify date as needed
    $secs = (self::$secs_hash[$match[1]] * $match[2]);
    $from_date -= $secs;

    // Return
    if ($return_datestamp === true) { $from_date = date('Y-m-d H:i:s', $from_date); }
    return $from_date;

}


/**
* Get last seen display time.
*      @param int $secs The number of seconds, epoch UNIX time from the PHP time() stamp.
*/
public static function last_seen($secs)
{

    // Initialize
    $seen = 'Unknown';
    $orig_secs = $secs;
    $secs = (time() - $secs);

    // Check last seen
    if ($secs < 20) { $seen = 'Just Now'; }
    elseif ($secs < 60) { $seen = $secs . ' secs ago'; }
    elseif ($secs < 3600) { 
        $mins = floor($secs / 60);
        $secs -= ($mins * 60);
        $seen = $mins . ' mins ' . $secs . ' secs ago';
    } elseif ($secs < 86400) { 
        $hours = floor($secs / 3600); 
        $secs -= ($hours * 3600);
        $mins = floor($secs / 60);
        $seen = $hours . ' hours ' . $mins . ' mins ago';
    } else { 
        $seen = date('D M dS H:i', $orig_secs);
    }

    // Return
    return $seen;

}
/**
* Get the 'last seen' time.  Accepts the number of seconds, and 
* returns how long ago that time was from the current time
*     @param int $secs The seconds to get the 'lst seen' since time for
*     @return string The 'last seen' time
*/
public static function get_time_since($seconds) { 
    $seconds = (time() - $seconds);

    // Set variables
    $days = 0;
    $hours = 0;
    $minutes = 0;

    // Get times
    while ($seconds >= 86400) { $days++; $seconds -= 86400; }
	while ($seconds >= 3600) { $hours++; $seconds -= 3600; }
    while ($seconds >= 60) { $minutes++; $seconds -= 60; }
    if ($days == 0 && $hours == 0 && $minutes == 0) { return 'Just Now'; }

    // Format last seen
    if ($days > 0) { $last_seen = $days . ' days'; }
    elseif ($hours > 0) { $last_seen = $hours . ' hours'; }
    elseif ($minutes > 0) { $last_seen = $minutes . ' minutes'; }
    else { $last_seen = $seconds . ' seconds'; }

    // Return
    return $last_seen;

}

}

