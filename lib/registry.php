<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\log;
use apex\debug;
use apex\core\components;
use redis;

/**
* Handles all information on the overall request itself, including santizing all input arrays, 
* the URI being displayed, the response status and contents, language and timezone to display everything in, 
* and so on.  Please see developer documentation for more details.
*/
class registry
{

    // Routing variables
    public static $route = '';
    public static $http_controller = 'http';
    public static $uri = array();
    public static $auth_hash = '';
    public static $verified_2fa = false;

    // Request variables
    public static $service = 'http';
    public static $request_method;
    public static $ip_address = '';
    public static $user_agent = '';

    // Input arrays
    protected static $post = array();
    protected static $get = array();
    protected static $cookie = array();
    protected static $server = array();
    protected static $config = array();

    // Front-end variables
    public static $userid = 0;
    public static $panel = 'public';
    public static $theme = 'public_coco';
    public static $action;

    // Localization
    public static $timezone = 'PST';
    public static $language = 'en';
    public static $currency = 'USD';
    private static $smtp_connections = array();

    // Response variables
    protected static $res_status = 200;
    protected static $res_content_type = 'text/html';
    protected static $response = '';

    // Redis
    public static $redis;

/**
* Creates a new registry session, sanitizes input arrays, 
* obtains user info, parses the URI, and more.  This class is heavily 
* used throughout the rest of the system to handle request / response data.
*/
public static function create()
{ 

    // Sanitize inputs
    if (self::$service != 'test') { 
        self::$get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
        self::$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        self::$cookie = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_STRING);
        self::$server = filter_input_array(INPUT_SERVER, FILTER_SANITIZE_STRING);
    }

    // Set other variables
    self::$request_method = self::$server['REQUEST_METHOD'] ?? 'GET';
    self::$action = self::$post['submit'] ?? '';

    // Get IP address
    if (isset(self::$server['HTTP_X_FORWARDED_FOR'])) { self::$ip_address = self::$server['HTTP_X_FORWARDED_FOR']; }
    elseif (isset(self::$server['REMOTE_ADDR'])) { self::$ip_address = self::$server['REMOTE_ADDR']; }

    // Validiate IP address
    if (self::$ip_address != '' && !filter_var(self::$ip_address, FILTER_VALIDATE_IP)) { 
        throw new ApexException('alert', "Invalid request, malformed IP address: {1}", self::$ip_address);
    }

    // Get user agent
    $ua = self::$server['HTTP_USER_AGENT'] ?? '';
    self::$user_agent = filter_var($ua, FILTER_SANITIZE_STRING);

    // Get route
    $route = self::$get['route'] ?? 'index';
    $route = strtolower($route);

    // Get http controller
        self::$uri = explode("/", trim($route, '/'));
    if (file_exists(SITE_PATH . '/src/core/controller/http_requests/' . self::$uri[0] . '.php')) { 
        self::$http_controller = array_shift(self::$uri);
        if (!isset(self::$uri[0])) { self::$uri[0] = 'index'; }
        $route = implode('/', self::$uri);
    }
    self::set_route($route);

    // Connect to redis
    self::connect_redis();

    // Start log handler
    log::start();

    // Debug
    debug::add(1, fmsg("Register loaded with HTTP controller '{1}', panel '{2}', and route '{3}'", self::$http_controller, self::$panel, self::$route), __FILE__, __LINE__);

}

/**
* Connect to redis server, and initiate as necessary.
*/
private static function connect_redis()
{


    // Return if not defined
    if (!defined('REDIS_HOST')) { 
        self::set_default_constants();
        return;
    }

    // Connect
    self::$redis = new redis();
    if (!self::$redis->connect(REDIS_HOST, REDIS_PORT, 2)) { 
        throw new ApexException('emergency', "Unable to connect to redis database.  We're down!");
    }

    // Authenticate redis, if needed
    if (REDIS_PASS != '' && !self::$redis->auth(REDIS_PASS)) { 
        throw new ApexException('emergency', "Unable to authenticate redis connection.  We're down!");
    }

    // Select redis db, if needed
    if (REDIS_DBINDEX > 0) { self::$redis->select(REDIS_DBINDEX); }

    // Get config variables
    $rows = self::$redis->hgetall('config');
    foreach ($rows as $key => $value) {
        self::$config[$key] = $value;
    }

    // Check for encryption info
    if ((!defined('ENCRYPT_CIPHER')) && $vars = self::$redis->hgetall('config:encinfo')) { 
        define('ENCRYPT_CIPHER', $vars['cipher']);
        define('ENCRYPT_PASS', base64_decode($vars['pass']));
        define('ENCRYPT_IV', base64_decode($vars['iv']));
    }

    // Check if config exists
    if (!self::$redis->hexists('config', 'core:default_timezone')) { 
        self::set_default_constants();
        return;
    }

    // Set config constants
    if (!defined('DEFAULT_TIMEZONE')) { 
        define('DEFAULT_TIMEZONE', self::config('core:default_timezone'));
        define('DEFAULT_LANGUAGE', self::config('core:default_language'));
        define('LOG_LEVEL', explode(",", self::config('core:log_level')));
        define('DEBUG_LEVEL', self::config('core:debug_level'));
        define('COOKIE_NAME', self::config('core:cookie_name'));
    }

    // Debug
    //debug::add(4, "Connected to redis, loaded config", __FILE__, __LINE__);

}

/**
* Define default constants.  Used to ensure errors aren't thrown during installation.
*/
private static function set_default_constants()
{

    if (!defined('DEFAULT_TIMEZONE')) { define('DEFAULT_TIMEZONE', 'PST'); }
    if (!defined('DEFAULT_LANGUAGE')) { define('DEFAULT_LANGUAGE', 'en'); }
    if (!defined('LOG_LEVEL')) { define('LOG_LEVEL', array('notice', 'error', 'critical', 'alert', 'emergency')); }
    if (!defined('DEBUG_LEVEL')) { define('DEBUG_LEVEL', 0); }
    if (!defined('COOKIE_NAME')) { define('COOKIE_NAME', 'K9dAmgkd4Uaf'); }

}

/**
* Handles the request, and response is placed in 
* self::$response, which can be outputted via self::echo_response()
*/
public static function handle_request() 
{

    // Load http controller
    if (!$http = components::load('controller', self::$http_controller, 'core', 'http_requests')) { 
        throw new ApexException('alert', "Invalid request, as unable to load HTTP controller: {1}", self::$http_controller);
    }

    // Debug
    debug::add(2,fmsg( "Loaded HTTP controller, '{1}", self::$http_controller), __FILE__, __LINE__);

    // Process request
    $http->process();

    // Finish logging
    log::finish();

    // Finish debugging
    debug::finish_session();

}

/**
* Handle test request.  Used within the unit tests to emulate a HTTP request to the 
* system, and obtain the response to check assertions.
*     @param string $route The URI to send a request to
*     @param string $method The request method (POST or GET), defaults to GET
*     @param array $post Variables that should be POSTed to the URI
*      @param array $get Variables that should be included in GET / query string of URI
*     @param array $cookie Any cookie variables to include in test request (eg. auth hash if logged in)
*/
public static function test_request(string $route, string $method = 'GET', array $post = array(), array $get = array(), array $cookie = array())
{

    // Set variables
    self::$service = 'test';
    template::$has_errors = false;
    template::$user_messages = array();


    // Get POST / GET variables
    self::$get['route'] = $route;
    self::$post['submit'] = $post['submit'] ?? '';
    self::$server['REQUEST_METHOD'] = $method;

    // Create registry
    self::create();

    // Set input arrays
    self::$post = $post;
    self::$get = $get;
    self::$cookie = $cookie;

    // Handle request
    self::handle_request();

    // Return response
    return self::$response;

}

/**
* Verify a 2FA request*/
public static function verify_2fa(array $vars)
{

    // Set variables
    self::$userid = (int) $vars['userid'];
    self::$http_controller = $vars['http_controller'];
    self::$panel = $vars['panel'];
    self::$theme = $vars['theme'];
    self::$route = $vars['route'];
    self::$request_method = $vars['request_method'];
    self::$get = $vars['get'];
    self::$post = $vars['post'];
    self::$verified_2fa = true;

    // Handle request
    self::handle_request();

    // Echo results
    self::echo_response();

}

/**
( Below functions are the equivalents of the get / has methods within the PSR-11 standards, 
* and allow you to easily check if a variable exists, and 
* also retrieve said variable
*/

public static function post(string $var) { return self::$post[$var] ?? null; }
public static function get(string $var) { return self::$get[$var] ?? null; }
public static function cookie(string $var) { return self::$cookie[$var] ?? null; }
public static function server(string $var) { return self::$server[$var] ?? null; }
public static function config(string $var) { return self::$config[$var] ?? null; }

public static function has_post(string $var) { return isset(self::$post[$var]) ? true : false; }
public static function has_get(string $var) { return isset(self::$get[$var]) ? true : false; }
public static function has_cookie(string $var) { return isset(self::$cookie[$var]) ? true : false; }
public static function has_server(string $var) { return isset(self::$server[$var]) ? true : false; }
public static function has_config(string $var) { return isset(self::$config[$var]) ? true : false; }

public static function getall_post() { return is_array(self::$post) ? self::$post : array(); }
public static function getall_get() { return is_array(self::$get) ? self::$get : array(); }
public static function getall_cookie() { return is_array(self::$cookie) ? self::$cookie : array(); }
public static function getall_server() { return is_array(self::$server) ? self::$server : array(); }
public static function getall_config() { return self::$config; }

public static function clear_post() { self::$post = array(); }
public function clear_get() { self::$get = array(); }

/**
* Sets the route, which is also used by the template engine to 
* display the correct template.  Only use this is you need to change the URI 
* for some reason.
* 
*     @param string $route The route / URI to set in registry
*     return bool Whether the route was successfully set.
*/
public static function set_route(string $route)
{

    // Format
    $route = str_replace(" ", "+", $route);

    // Validate
    if (!filter_var('http://domain.com/' . trim($route, '/'), FILTER_VALIDATE_URL)) { 
        throw new ApexException('error', "Invalid URI speicifed as route, {1}", $route);
    }

    // Sanitize route 
    self::$route = strtolower(filter_var(trim($route, '/'), FILTER_SANITIZE_URL));

    // Debug
    debug::add(3, fmsg("Changed route / URI to {1}", self::$route), __FILE__, __LINE__);

}

/**
* Sets the ID# of the user who has been authenticated.  You should 
* never have to use this method.
*
*     @param int $userid The ID# of the user authenticated.  Can be either admin or user.
*/
public static function set_userid(int $userid)
{

    self::$userid = $userid;

    // Debug
    debug::add(4, fmsg("Set authenticated user ID# to {1}", self::$userid), __FILE__, __LINE__);

}

/**
* Gets timezone offset and is_dst.
* Defaults to self::$timezone if no timezone specified.
*     @param string $timezone The timezone to retrieve details from.
*/
public static function get_timezone(string $timezone = '') 
{

    // Check for no redis
    if (!defined('DEFAULT_TIMEZONE')) { return array(0, 0); }
    if (!defined('REDIS_HOST')) { return array(0, 0); }


    // Default timezone, if needed
    if ($timezone == '') { $timezone = self::$timezone; }

    // Get timezone from db
    if (!$value = self::$redis->hget('std:timezone', $timezone)) { 
        return array(0, 0);
    }
    $vars = explode("::", $value);

    // Debug
    debug::add(5, fmsg("Obtained info for timezone {1}, offset {2}, DST {3}", $timezone, $vars[1], $vars[2]), __FILE__, __LINE__);

    // Return
    return array($vars[1], $vars[2]);
    
}

/**
* Gets formatting info on a given currency, such as 
* the currency sign, number of decimal points, etc.
* 
*     @param string $currency The 3 character ISO code of the currency to retrieve.
*/
public static function get_currency(string $currency):array
{

    // Get currency data
    if (!$data = self::$redis->hget('std:currency', $currency)) { 

        // Check for crypto
        if (!registry::$redis->sismember('config:crypto_currency', $currency)) { 
            throw new ApexException('critical', "Currency does not exist in database, {1}", $currency);
        }

        // Return
        $vars = array(
            'symbol' => '', 
            'decimals' => 8, 
            'is_crypto' => 1
        );
        return $vars;
    }
    $line = explode("::", $data);

    // Set vars
    $vars = array(
        'symbol' => $line[1], 
        'decimals' => $line[2], 
        'is_crypto' => 0
    );

    // Return
    return $vars;

}

/**
* Sets the response HTTP status code
*     @param int $code The HTTP status code to give as a response
*     @return bool Whether or not the operation was successful.
*/
public static function set_http_status(int $code)
{

    self::$res_status = $code;

    // Debug
    debug::add(1, fmsg("Changed HTTP response status to {1}", $code), __FILE__, __LINE__);

}

/**
* Sets the content type of the response that will be given.
* Defaults to 'text/html'.
* 
*     @param string $type the content type to set the response to.
*     @return bool Whether or not the operation was successful.
*/
public static function set_content_type(string $type)
{

    self::$res_content_type = $type;

    // Debug
    debug::add(1, fmsg("Set response content-type to {1}", $type), __FILE__, __LINE__);

}

/**
* Returns the current content type of the response.
*     @return string Content type of response
*/
public static function get_content_type() { 
    return self::$res_content_type;
}

/**
* Set the contents of the response that will be given.  Should be 
* used with every request.
* 
*     @param string $content The content of the response that will be given.
*/
public static function set_response(string $content = '') 
{

    // Set response content
    self::$response = (string) $content;

    // Debug
    debug::add(2, "Set response contents", __FILE__, __LINE__);

}

/**
* Returns the current response contents.
*/
public static function get_response() 
{

    // Return
    return self::$response;

}

/**
* Outputs the response contents to the user, generally the web browser
*/
public static function echo_response() 
{

    // Debug
    debug::add(2, "Outputting response to web browser", __FILE__, __LINE__);

    // Set HTTP status code
    http_response_code(self::$res_status);

    // Content type
    header("Content-type: " . self::$res_content_type);

    // Echo response
    echo (string) self::$response;

}

/**
* Echos a template.  Useful if you want to break execution of a template PHP code mid-way, and 
* display the previous template with user errors.
*     @param string $uri The template to display, relative to the current area / panel the user is in (admin, members, public, etc.)
*/
public static function echo_template(string $uri)
{

    // Debug
    debug::add(1, fmsg("Forcing non-standard output of template: {1}", $uri), __FILE__, __LINE__);
 
    // Set route
    self::set_route($uri);
    if (self::$theme == '') { 
        self::$panel = 'public';
        self::$theme = self::config('core:theme_public');
    }

    // Echo template
    self::set_response(template::parse());
    self::echo_response();

    // Finish session
    log::finish();
    debug::finish_session();

    // Exit
    exit(0);

}

/**
* Get date for log files.  This ensures the date is formatted to DEFAULT_TIMEZONE, 
* instead of UTC or the authenticated user's timezone.
*/
public static function get_logdate() 
{

    // Get timezone data
    list($offset, $dst) = self::get_timezone(DEFAULT_TIMEZONE);
    $offset *= 60;

    // Get log date
    $secs = $offset < 0 ? (time() - $offset) : (time() + $offset);
    $logdate = date('Y-m-d H:i:s', $secs);

    // Return
    return $logdate;

}

/**
* Update a configuration variable
* 
*      @param string $var The variable name within self::$config to update.
*     @param string $value THe value to update the configuration variable to.
*/
public static function update_config_var(string $var, $value) 
{

    // Debug
    debug::add(5, fmsg("Updating configuration variable {1} to value: {2}", $var, $value), __FILE__, __LINE__);

    self::$redis->hset('config', $var, $value);
    self::$config[$var] = $value;

    // Update mySQL
    list($package, $alias) = explode(":", $var, 2);
    DB::query("UPDATE internal_components SET value = %s WHERE package = %s AND alias = %s", $value, $package, $alias);

}

/**
* GeoIP an address, and return the country, state / province, 
* and city.  Uses MaxMin free goecitylite database.
*
*     @param string $ipaddr IP address to look up.  Defaults to self::$ip_address.
*/
public static function geoip(string $ipaddr = ''):array 
{

    // Load library file
    require_once(SITE_PATH . '/lib/third_party/maxmind/autoload.php');

    // Get IP address
    if ($ipaddr == '') { $ipaddr = self::$ip_address; }

    // Debug
    debug::add(2, fmsg("Performing GeoIP lookup of IP address: {1}", $ipaddr), __FILE__, __LINE__);


    // Load reader
    $reader = new \MaxMind\Db\Reader(SITE_PATH . '/lib/third_party/maxmind/GeoLite2-City.mmdb');
    $vars = $reader->get($ipaddr);

    // Set results
    $results = array(
        'city' => $vars['city']['names']['en'], 
        'country' => $vars['country']['iso_code'], 
        'country_name' => $vars['country']['names']['en']
    );

    // Get postal code
    if (isset($vars['postal']) && is_array($vars['postal']) && isset($vars['postal']['code'])) { $results['postal'] = $vars['postal']['code']; }
    else { $results['postal'] = ''; }

    if (isset($vars['subdivisions']) && is_array($vars['subdivisions'])) { $results['province'] = $vars['subdivisions'][0]['names']['en']; }
    else { $results['province'] = ''; }

    // Close reader
    $reader->close();
    // Return
    return $results;

}

/**
* Get a database server credentials.
*     @param string $type The type of connection.  Must be 'read' or 'write'
*/
public static function get_db_server(string $type = 'read')
{

    // Set variables
    $total_slaves = self::$redis->llen('config:db_slaves') ?? 0;

    // Get master server, if needed
    if ($type == 'write' || $total_slaves == 0) { 
        $vars = self::$redis->hgetall('config:db_master');

        if ($type == 'read' && $vars['dbuser_readonly'] != '') { 
            $vars['dbuser'] = $vars['dbuser_readonly'];
            $vars['dbpass'] = $vars['dbpass_readonly'];
        }

        // Return
        return $vars;
    }

    // Get slave server
    $num = get_counter('db_server');
    if (!$vars = self::$redis->lindex('config:db_slaves', $num)) { 
        self::$redis->hset('counters', 'db_server', 0);
        $vars = self::$redis->lindex('config:db_slaves', 0);
    }

    // Return
    return json_decode($vars, true);

}

/**
* Get the next SMTP server to send from
*/
public static function get_smtp_server()
{

    // Set variables
    $found = false;
    $retries = 0;
    $vars = array();
    $total_servers = self::$redis->llen('config:email_servers');
    if ($total_servers < 1) { return false; }

    // Get active SMTP server
    do {
        $num = get_counter('email_servers');
        if (!$value = self::$redis->lindex('config:email_servers', $num)) { 
            self::$redis->hset('counters', 'email_servers', -1);
            continue;
        }

        // Check number of retries
        if ($retries >= $total_servers) { 
            return false;
        }

        // Decode JSON, and check status
        $vars = json_decode($value, true);
        //if ($vars['is_active'] != 1) { 
            //$retries++;
            //continue;
        //}

        // Check for existing connection
        if (isset(self::$smtp_connections[$vars['host']])) { 
            fwrite(self::$smtp_connections[$vars['host']]['connection'], "RSET\r\n");
            $response = fread(self::$smtp_connections[$vars['host']]['connection'], 1024);
            return self::$smtp_connections[$vars['host']];
        }

        // Connect to host
        $host = $vars['is_ssl'] == 1 ? 'ssl://' . $vars['host'] : $vars['host'];
        if (!$connection = fsockopen($host, (int) $vars['port'], $errno, $errstr, 5)) { 
            $vars['is_active'] = 0;
            self::$redis->lset('config:email_servers', $num, json_encode($vars));
            $retries++;
            continue;
        }
        $response = fread($connection, 1024);

        // HELO
        fwrite($connection, "EHLO " . self::config('core:domain_name') . "\r\n");
        $response = fread($connection, 1024);

        // Authenticate
        if ($vars['username'] != '' && $vars['password'] != '') { 

            // Auth
            fwrite($connection, "AUTH LOGIN\r\n");
            $response = fread($connection, 1024);

            // Username
            fwrite($connection, base64_encode($vars['username']) . "\r\n");
            $response = fread($connection, 1024);

            // Password
            fwrite($connection, base64_encode($vars['password']) . "\r\n");
            $response = fread($connection, 1024);
        }

        // Finish up
        self::$smtp_connections[$vars['host']] = array(
            'connection' => $connection, 
            'server_num' => $num, 
            'host' => $vars['host']
        );
        return self::$smtp_connections[$vars['host']];

    } while ($found === false);

    // Return
    return false;

}

}

