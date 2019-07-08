<?php
declare(strict_types = 1);

use apex\app;
use apex\services\db;
use apex\services\debug;
use apex\services\log;
use apex\services\template;
use apex\app\exceptions\ApexException;


/**
 * Handle all exceptions
 */
function handle_exception($e)
{



    // ApexException
    if ($e instanceof ApexException) { 
        $e->process();

    // RPC timeout
    } elseif ($e instanceof PhpAmqpLib\Exception\AMQPTimeoutException || $e instanceof WebSocket\ConnectionException) { 

        // Log error
        debug::add(1, "The internal RPC / web socket server is down.  Please start by using the init script at /bootstrap/apex.", __FILE__, __LINE__, 'emergency');
        debug::finish_session();

        // Display template
        app::set_res_http_status(504);
        app::set_uri('504', true);
        echo template::parse();

    // Give standard error
    } else { 
        error(0, $e->getMessage(), $e->getFile(), $e->getLine());
    }

    // Exit
    exit(0);

}

/**
 * Error handler
 */
function error(int $errno, string $message, string $file, int $line) 
{
    if (preg_match("/fsockopen/", $message) && preg_match("/8194/", $message)) { return; }
    $file = trim(str_replace(SITE_PATH, '', $file), '/');

    // Get level of log message
    if (in_array($errno, array(2, 32, 512))) { $level = 'warning'; }
    elseif (in_array($errno, array(8, 1024))) { $level = 'notice'; }
    elseif (in_array($errno, array(64, 128, 256, 4096))) { $level = 'error'; }
    elseif (in_array($errno, array(2048, 8192, 16384))) { $level = 'info'; }
    elseif (in_array($errno, array(1, 4, 16))) { $level = 'critical'; }
    else { $level = 'alert'; }

    // Add log
    log::add_system_log($level, 1, $file, $line, $message);

    // Finish session
    debug::finish_session();

// Check for command line usage
    if (php_sapi_name() == "cli") { 
        echo "ERROR: $message\n\nFile: $file\nLine: $line\n\n";
        exit(0);

    // JSON error
    } elseif (preg_match("/\/json$/", app::get_res_content_type())) { 
        $response = array(
            'status' => 'error', 
            'errmsg' => $message, 
            'file' => $file, 
            'line' => $line
        );
        echo json_encode($response);
        exit(0);
    }

    // Check if .tpl file exists
    $tpl_file = app::_config('core:mode') == 'devel' ? '500' : '500_generic'; 
    if (!file_exists(SITE_PATH . '/views/tpl/' . app::get_area() . '/' . $tpl_file . '.tpl')) {
        echo "<b>ERROR!</b>  We're sorry, but an unexpected error occurred preventing this software system from running.  Additionally, a 500.tpl template was not found.<br /><blockquote>\n";
        if (app::_config('core:mode') == 'devel') { 
            echo "<b>Message:</b> $message<br /><br />\n<b>File:</b> $file<br />\n<b>Line:</b> $line<br />\n";
        }
        exit(0);
    }

// Set registry variables
    app::set_res_http_status(500);
    app::set_uri($tpl_file, true);

    // Template variables
    template::assign('err_message', $message);
    template::assign('err_file', $file);
    template::assign('err_line', $line);

    // Parse template
    app::set_res_body(template::parse());
    app::Echo_response();

    // Exit
    exit(0);

}


/**
 * Translate text to user's language 
 *
 * Translates a string of text into the necessary language, depending on the 
 * user's profile preferences.  Also supports placeholders (ie. %s), with the 
 * variables being passed as additional parameters in sequential order. 
 */
function tr(...$args):string
{ 

    // Check for text
    if (!$text = array_shift($args)) { return 'null'; }

    // Get the correct text
    $lang = app::get_language();
    if ($lang != 'en' && $row = db::get_row("SELECT * FROM internal_translations WHERE language = %s AND md5hash = %s", $lang, md5($text))) { 
        if ($row['contents'] != '') { $text = base64_decode($row['contents']); }
    }

    // Go through args
    foreach ($args as $value) { 
        $text = preg_replace("/\%\w/", $value, $text, 1);
    }

    // Return
    return $text;

}

/**
 * Format with PSR placeholders 
 *
 * Formats a message with PSR3 supported placeholders such as {1}, {2}, {3}. 
 * First parameter is the message with placeholders, and the rest of the 
 * parameters are the values of the placeholders in sequential order. 
 */
function fmsg(string $msg, ...$vars)
{ 

    // Initializ
    $replace = array();
    if (isset($vars[0]) && is_array($vars[0])) { $vars = $vars[0]; }

    // Create replace array
    $x=1;
    foreach ($vars as $key => $var) { 
        if (is_string($key)) { $replace['{' . $key . '}'] = filter_var($var, FILTER_SANITIZE_STRING); }
        $replace['{' . $x . '}'] = filter_var($var, FILTER_SANITIZE_STRING);
    $x++; }

    // Return
    return strtr($msg, $replace);

}

/**
 * Format a message with named placeholders 
 *
 * @param string $message The message with placeholders in it
 * @param array $vars Associative array of keys being the placeholder names, with their respective values to replace with
 *
 * @return string The newly formatted message
 */
function fnames(string $message, array $vars):string
{ 

    // Translate message
    $message = tr($message);

    // Go through placeholders
    foreach ($vars as $key => $value) { 
        if (is_array($value)) { continue; }

        $key = '{' . $key . '}';
        $message = str_replace($key, $value, $message);
    }

    // Return
    return $message;

}

/**
 * Formats a date in proper readable format, and also converts timezone 
 * automatically as necessary. 
 *
 * @param string $date The date to format, in YYYY-MM-DD HH:II:SS
 * @param bool $add_time WHther or not to add the time to the outputed date.
 *
 * @return string The resulting formatted date.
 */
function fdate(string $date, bool $add_time = false):string
{ 

$d = $date == '2019-03-02 06:55:02' ? 1 : 0;

    // Get timezone data
    list($offset, $dst) = app::get_tzdata();

    // Convert date to correct timezone
    $date_func = $offset < 0 ? 'date_sub' : 'date_add';
    $date = db::get_field("SELECT " . $date_func . "('$date', interval " . abs($offset) . " minute)");

    // Split date, if needed
    if (preg_match("/^(.+?)\s(.+)/", trim($date), $match)) { $date = $match[1]; }

    // Format the date
    list($year, $month,$day) = explode("-", $date);
    $new_date = date(app::_config('core:date_format'), mktime(0, 0, 0, (int) $month, (int) $day, (int) $year));

    // Add time, if needed
    if ($add_time === true && preg_match("/^(.+)\:.+/", $match[2], $time_match)) { 
        $new_date .= ' at ' . $time_match[1];
    }

    // Return
    return $new_date;

}

/**
 * Format decimal into amount with correct currency 
 *
 * @param float $amount The decimal to format.
 * @param string $currency The 3 character ISO currency to format to.
 *
 * @return string The formatted amount.
 */
function fmoney(float $amount, string $currency = '', bool $include_abbr = true):string
{ 

    // Use default currency, if none specified
    if ($currency == '') { $currency = app::_config('transaction:base_currency'); }

    // Get currency
    $format = app::get_currency_data($currency);

    // Format crypto currency
    if ($format['is_crypto'] == 1) { 
 
        // Format decimal points
        $amount = preg_replace("/0+$/", "", sprintf("%.8f", $amount));
        $length = strlen(substr(strrchr($amount, "."), 1));
        if ($length < 4) { 
            $amount = sprintf("%.4f", $amount);
            $length = 4;
        }

        // Format amount
        $name = number_format((float) $amount, (int) $length);
        if ($include_abbr === true) { $name .= ' ' . $currency; }

    // Format standard currency
    } else { 
        $name = $format['symbol'] . number_format((float) $amount, (int) $format['decimals']);
        if ($include_abbr === true) { $name .= ' ' . $currency; }
    }

    // Return
    return $name;



}

/**
 * Exchange funds into another currency, and return the formatted value of 
 * resulting amount. 
 *
 * @param float $amount The amount to exchange
 * @param string $currency_from The currency the amount is currently in
 * @param string $currency_to The currency to exchange the funds into
 *
 * @return string The resulting amount after exchange
 */
function fexchange(float $amount, string $from_currency, string $to_currency)
{ 

    // Exchange money
    $amount = exchange_money($amount, $from_currency, $to_currency);

    // Format and return
    return fmoney((float) $amount, $to_currency);

}

/**
 * Exchange funds into another currency. 
 *
 * @param float $amount The amount to exchange
 * @param string $currency_from The currency the amount is currently in
 * @param string $currency_to The currency to exchange the funds into
 *
 * @return float The resulting amount after exchange
 */
function exchange_money(float $amount, string $from_currency, string $to_currency)
{ 

    // Echange to base currency, if needed
    if ($from_currency != app::_config('transaction:base_currency')) { 
        $rate = db::get_field("SELECT current_rate FROM transaction_currencies WHERE abbr = %s", $from_currency);
        $amount *= $rate;
    }

    // Convert to currency
    $rate = db::get_field("SELECT current_rate FROM transaction_currencies WHERE abbr = %s", $to_currency);
    if ($rate == 0.00) { return 0; }
    return($amount / $rate);

}

/**
 * Checks whether or not a package is installed. 
 *
 * @param string $alias The alias of the package to check.
 *
 * @return bool WHether or not the package is installed.
 */
function check_package($alias) { 
    $pkg_file = SITE_PATH . '/etc/' . $alias . '/package.php';
    return file_exists($pkg_file) ? true : false;
}

