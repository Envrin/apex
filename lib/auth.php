<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\message;
use apex\core\io;
use apex\core\admin;
use apex\core\hashes;
use apex\user\user;

class auth 
{

    // Set variables
    protected static $auth_type = 'user';
    protected static $users_table = 'users';
    protected static $cookie_name = COOKIE_NAME . '_user_auth_hash';
    protected static $expire_mins = 0;
    protected static $password_retries_allowed = 0;
    protected static $force_password_reset_time = ''; 
    protected static $require_2fa = 0;

/**
* Sets the 'auth_type' and 'users_table' variables within this class.
* Must be either 'admin' or 'user', and should generally never 
* be executed by another package.
*
*     @param string $auth_type Type of user authenticating.  Must be either 'admin' or 'user'.
*     @return bool Whether or not the operation was successful.
*/
public static function set_auth_type(string $auth_type = 'user'):bool
{

    if ($auth_type != 'admin' && $auth_type != 'user') { return false; }
    self::$auth_type = $auth_type;
    self::$users_table = $auth_type == 'admin' ? 'admin' : 'users';
    self::$cookie_name = COOKIE_NAME . '_' . $auth_type . '_auth_hash';
    return true;
}

/**
* Initialize
*/
private static function initialize()
{

    // Initialize
    $package = self::$auth_type == 'admin' ? 'core' : 'users';
    self::$expire_mins = (registry::config($package . ':session_expire_mins') * 60);
    self::$password_retries_allowed = registry::config($package . ':password_retries_allowed'); 
    self::$force_password_reset_time = registry::config($package . ':force_password_reset_time');
    self::$require_2fa = registry::config($package . ':require_2fa');


}


/**
* Authenticates a current session
*     @param bool $require_login Whther or not authentication is required.  For example, false 
*         user is visiting public web site, and different menus are displayed if authenticated.
*/
public static function check_login(bool $require_login = false) 
{ 

    // Debug
    debug::add(3, fmsg("Starting authentication, auth_type: {1}, require_login: {2}.", self::$auth_type, ($require_login === true ? 'true' : 'false')), __FILE__, __LINE__);

    // Logout, if needed
    if (preg_match("/logout$/", registry::$route)) { 
        self::logout();
    }

    // Set variables
    self::initialize();
    if (registry::has_cookie(self::$cookie_name)) { 
        $chk_auth_hash = 'auth:' . hash('sha512', registry::cookie(self::$cookie_name));
        registry::$auth_hash = registry::cookie(self::$cookie_name);
    } else { 
        $chk_auth_hash = '';
        registry::$auth_hash = '';
    }

    // Login, if needed
    if (registry::$action == 'login') { 
        return self::login();
    }

    // Check for session
    if ($chk_auth_hash != '' && registry::has_cookie(self::$cookie_name) && $row = registry::$redis->hgetall($chk_auth_hash)) {

        // Set variables
        $redis_user_key = self::$users_table . ':' . $row['userid'];

        // Debug
        debug::add(3, fmsg("Found existing auth session, auth_type: {1}, userid: {2}, auth_hash: {3}", self::$auth_type, $row['userid'], $chk_auth_hash), __FILE__, __LINE__);

        // Check for 2FA
        if ($row['2fa_status'] == 0 && registry::$panel != 'public') {
            debug::add(3, "Auth session still requires 2FA authorization", __FILE__, __LINE__);
            registry::echo_template('2fa');
        }

        // Check IP address
        if (registry::$ip_address != $row['ip_address']) { 

            // Debug and log
            debug::add(2, fmsg("Authentication error.  Session and current user IP addresses do not match.  Session IP: {1}, Current IP: {2}", $row['ip_address'], registry::$ip_address), __FILE__, __LINE__, 'warning');

            // Invalid login
            self::invalid_login('invalid');
            return false;
        }

        // Check user agent
        if (registry::$user_agent != $row['user_agent']) { 

            // Debug
            debug::add(2, fmsg("Authentication error.  Session and current user agents do not match.  Session UA: {1}, Current UA: {2}", $row['user_agent'], registry::$user_agent), __FILE__, __LINE__, 'warning');

            // Invalid login
            self::invalid_login('invalid');
            return false;
        }

        // Update session
        $redis_user_key = self::$auth_type . ':' . $row['userid'];
        registry::$redis->hset('auth:last_seen', $redis_user_key, time());

        // Update expiration
        $expire_secs = isset($row['remember_me']) && $row['remember_me'] == 1 ? 2592000 : self::$expire_mins;
        registry::$redis->expire($chk_auth_hash, $expire_secs);
        if (self::$auth_type == 'user') { 
            registry::$redis->expire($redis_user_key, 259200);
        }

        // Load client
        if (self::$auth_type == 'admin') { $client = new \apex\core\admin((int) $row['userid']); }
        else { $client = new \apex\users\user((int) $row['userid']); }

        // Get language / timezone
        if (!$urow = $client->load()) { 
            throw new UserException('not_exists', $row['userid']);
        }
        if ($urow['status'] != 'active') { 
            self::invalid_login($row['status']);
            return false;
        }

        registry::$timezone = $urow['timezone'];
        registry::$language = $urow['language'];
        registry::$currency = $urow['currency'] ?? registry::config('transaction:base_currency');

        // Set userid
        registry::set_userid((int) $row['userid']);

        // Add page history
        self::add_page_history($row['history_id']);

        // Debug and log
        debug::add(2, fmsg("Successfully authenticated usesr, auth_type: {1}, userid: {2}, username: {3}", self::$auth_type, $row['userid'], $urow['username']), __FILE__, __LINE__, 'info');

        // Return
        return true;

    // Require login, if needed
    } elseif ($require_login === true) { 

        // Debug
        debug::add(2, fmsg("User not authenticated, login required.  Displaying login screen."), __FILE__, __LINE__);

        // Display login screen
        self::invalid_login();
        return false;

    }

    // Return
    return true;

}

/**
* Add page history
*/
private static function add_page_history($history_id)
{

    // Get post vars
    $post_vars = registry::getall_post() ?? array();
    foreach ($post_vars as $key => $value) { 
        if (preg_match("/password/", $key)) { $post_vars[$key] = "*****"; }
    }

    // Set vars
    $vars = array(
        'history_id' => $history_id, 
        'request_method' => registry::$request_method, 
        'panel' => registry::$panel, 
        'route' => registry::$route, 
        'get_vars' => base64_encode(json_encode(registry::getall_get())), 
        'post_vars' => base64_encode(json_encode($post_vars)) 
    );

    // Send message to RabbitMQ
    message::send('core.logs.add_auth_pageview', json_encode($vars));


}

/**
* Logs in a user, checking their user/pass from POSTed data
*     @param bool $auto_login If true, will automatically login the user without passowrd check.  Used mainly when administrator remotely logs into user account.
*/
public static function login(bool $auto_login = false) 
{

    // Debug / log
    debug::add(2, fmsg("Initiating login process, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__, 'info');

    // Initialize
    self::initialize();

    // Get							 admin / user info
    if ($auto_login === true) { 
        $user_row = array('id' => registry::$userid, 'status' => 'active');
        $userid = registry::$userid;

    } elseif (self::$auth_type == 'user' && !$userid = registry::$redis->hget('usernames', registry::post('username'))) { 
        debug::add(2, fmsg("Login failed, username does not exist, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__);
        self::invalid_login('invalid');
        return false;

    } elseif (self::$auth_type == 'admin' && !$userid = DB::get_field("SELECT id FROM admin WHERE username = %s", registry::post('username'))) { 
        debug::add(2, fmsg("Login failed, username does not exist, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__);
        self::invalid_login('invalid');
        return false;

    }
    $userid = (int) $userid;

    // Load client
    if (self::$auth_type == 'admin') { $client = new \apex\core\admin($userid); }
    else { $client = new \apex\users\user($userid); }

    // Load user profile
    if (!$user_row = $client->load()) { 
        debug::add(2, fmsg("Login failed, username does not exist, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__);
        self::invalid_login('invalid');
        return false;
    }

    // Check status
    if ($user_row['status'] != 'active') { 
        self::invalid_login($user_row['status']);
        return false;
    }

    // Check password
    if ($auto_login === false) { 

        if (!password_verify(registry::post('password'), base64_decode($user_row['password']))) {

            // Debug
            debug::add(2, fmsg("Authentication error, password is incorrrect, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__); 

            // Check # of retries
            if (self::$password_retries_allowed > 0 && $user_row['invalid_logins'] >= self::$password_retries_allowed) {

                // Debug / log
                debug::add(2, fmsg("Authentication error, invalid password and user exceeded max retries allowed, deactivating account.  auth_type: {1}, username: {2}, failed_logins: {3}", self::$auth_type, registry::post('username'), $user_row['invalid_logins']), __FILE__, __LINE__, 'warning');

                // Deactivate user
                if (self::$auth_type == 'admin') { $client = new \apex\core\admin((int) $user_row['id']); }
                else { $client = new \apex\users\user((int) $user_row['id']); } 
                $client->update_status('inactive');
            }
            registry::$redis->HINCRBy(self::$users_table . ':' . $userid, 'invalid_logins', 1);

            // Invalid login
            self::invalid_login('invalid');
            return false;
        }	

        // Check if new device
        $cookie = COOKIE_NAME . '_' . self::$auth_type . '_auth_sechash';
        if (registry::has_cookie($cookie) && hash('sha512', registry::cookie($cookie)) == $user_row['sec_hash']) {
            $new_device = false;
        } else { $new_device = true; }

        // Check if 2FA required
        list($require_2fa, $require_2fa_phone) = array(0, 0);
        if (self::$auth_type == 'admin' || registry::config('users:require_2fa') == 'optional') { 
            $require_2fa = $user_row['require_2fa'];
            $require_2fa_phone = $user_row['require_2fa_phone'];
        } elseif (registry::config('users:require_2fa') == 'session') { 
            list($require_2fa, $require_2fa_phone) = array(1, 1);
        } elseif (registry::config('users:require_2fa') == 'new_device' && $new_device === true) { 
            list($require_2fa, $require_2fa_phone) = array(1, 1);
        }

        // Check if phone or e-mail 2FA required
        if ($require_2fa_phone == 1 && $user_row['phone'] != '') { 
            $require_2fa = 0;
        } else { $require_2fa_phone = 0; }

        // Check security question
        self::check_security_question(intval($user_row['id']), $user_row['sec_hash']);

        // Check IP address
        self::check_ip_restrictions(intval($user_row['id']));

    } else {
        list($require_2fa, $require_2fa_phone) = array(0, 0);
    }

    // Generate session ID
    do {
        $session_id = io::generate_random_string(60);
        $exists = registry::$redis->exists('auth:' . hash('sha512', $session_id));
    } while ($exists > 0);

    // Debug / log
    debug::add(1, fmsg("Authentication successful, session ID generated, auth_type: {1}, username: {2}, session_id: {3}", self::$auth_type, registry::post('username'), $session_id), __FILE__, __LINE__);

    // Add session to DB
    $remember_me = registry::post('remember_me') ?? 0;
    $vars = array(
        'type' => self::$auth_type, 
        'userid' => $user_row['id'], 
        'enc_pass' => md5(registry::post('password')), 
        '2fa_status' => ($require_2fa == 1 ? 0 : 1),
        '2fa_phone_status' => ($require_2fa_phone == 1 ? 0 : 1), 
        'remember_me' => $remember_me, 
        'ip_address' => registry::$ip_address, 
        'user_agent' => registry::$user_agent
    );

    // Add login history
    $rpc = new rpc();
    $vars['history_id'] = $rpc->send('core.logs.add_auth_login', json_encode($vars))['core'];

    // Add session to redis
    $expire_secs = $remember_me == 1 ? 2592000  : self::$expire_mins;
    $redis_key = 'auth:' . hash('sha512', $session_id);
    registry::$redis->hmset($redis_key, $vars);
    registry::$redis->expire($redis_key, $expire_secs);
    registry::$redis->hset('auth:last_seen', self::$users_table . ':' . $user_row['id'], time());

    // Set cookie
    if (php_sapi_name() != "cli") {
        $expire = $remember_me == 1 ? (time() + $expire_secs) : 0;
        unset($_COOKIE[self::$cookie_name]);
        if (!setcookie(self::$cookie_name, $session_id, $expire, '/')) { 
            throw new ApexException('alert', "Unable to set login cookie.  Customer support has been notified, and will resolve the issue shortly.  Please try again later.");
        }
    }

    // Set auth ash and user ID
    registry::$auth_hash = $session_id;
    registry::set_userid((int) $user_row['id']);

    // Initiate 2FA, if needed
    if ($require_2fa_phone == 1) { self::authenticate_2fa_sms(1); }
    elseif ($require_2fa == 1) { self::authenticate_2fa_email(1); }

    // Debug
    debug::add(1, fmsg("Completed successful login, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__, 'info');

    // Change panel, if needed
    if (self::$auth_type == 'user') { 
        $uri = registry::config('users:login_method') == 'index' ? '/index' : '/members/index';
        header("Location: $uri");
        exit(0);
    }

    // Parse template
    registry::set_route('index');
    registry::set_response(template::parse());

    // Return
    return true;

}

/**
* Checks user for secondary question.  If the system can not recognize 
* the user has previously logged in from this browser / computer, 
* will prompt the user to answer a pre-defined security question.
*/
protected static function check_security_question(int $userid, string $chk_sec_hash):bool 
{

    // Check for cookie
    $cookie = COOKIE_NAME . '_' . self::$auth_type . '_auth_sechash';
    if (registry::has_cookie($cookie) && hash('sha512', registry::cookie($cookie)) == $chk_sec_hash) {
        debug::add(4, fmsg("Authentication, user is already validated via security question from previous session, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__); 
        return true; 
    }

    // Check if user has questions
    if (!$value = registry::$redis->hget('auth:security_questions', self::$users_table . ':' . $userid)) { 
        return true;
    }
    $questions = json_decode($value, true);

    // Check answer, if needed
    $ask_question = true; 
    $invalid_answer = false;
    if (registry::has_post('answer') && registry::has_post('question_id')) { 

        // Check answer
        $question_id = registry::post('question_id');
        if (isset($questions[$question_id]) && password_verify(registry::post('answer'), base64_decode($questions[$question_id])) === true) { 
            debug(2, fmsg("Successfully answered secondary security question, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__, 'info');
            $ask_question = false;
        } else { $invalid_answer = true; }
    }

    // Ask question, if needed 
    if ($ask_question === true) { 

        // Debug
        debug::add(3, fmsg("Authentication, secondary security question required.  Displaying form, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__, 'info');

        // Get random question
    $question_id = array_rand($questions);

        // Start template
        registry::set_route('security_question');
        if ($invalid_answer === true) { 
            template::add_message(tr("We're sorry, but your answer to the security question was incorrect.  Please try again."), 'error');
        }
        template::assign('username', registry::post('username'));
        template::assign('password', registry::post('password'));
        template::assign('question_id', $question_id);
        template::assign('question', hashes::get_hashvar('core:secondary_security_questions', $question_id));

        // Parse template
        registry::set_response(template::parse());
        return false;
    }

    // Set cookie
    $sec_hash = io::generate_random_string(50);
    setcookie($cookie, $sec_hash, (time() + 2592000));

    // Update secondary hash indb
    $class_name = self::$users_table == 'admin' ? 'admin' : 'user';
    $client = new $class_name($userid);
    $client->update_sec_auth_hash($sec_hash);

    // Return
    return true;

}

/**
* Checks the user's IP address against any 
* IP restrictions that have been pre-defined and are 
* are in the database.
*/
protected static function check_ip_restrictions(int $userid):bool 
{

    // Check if IP records exist
    if (!$value = registry::$redis->hget('auth:ip_restrictions', self::$users_table . ':' . $userid)) { 
        return true;
    }
    $rows = json_decode($value, true);

    // Debug
    debug::add(4, fmsg("Authentication, checking IP address restrictions, auth_type: {1}, username: {2}", self::$auth_type, registry::post('username')), __FILE__, __LINE__);

    // Check if IP allowed
    if (!in_array(registry::$ip_address, $rows)) { 
        debug::add(3, fmsg("Authentication error, IP address not allowed, auth_type: {1}, username: {2}, ip_address: {3}", self::$auth_type, registry::post('username'), registry::$ip_address), __FILE__, __LINE__, 'warning');
        self::invalid_login();
    }

    // Return
    return true;

}

/**
* Processes an invalid login, and outputs the login.tpl template 
* with any necessary user message (eg. invalid user / pass submitted).
*/
public static function invalid_login(string $type = 'none') 
{

    // Debug
    debug::add(2, fmsg("Authentication, invalid login, auth_type: {1}, type: {2}", self::$auth_type, $type), __FILE__, __LINE__, 'info'); 

    // Logout
    self::logout();

    // Start template
    registry::set_route('login');

    // Add template message
    if ($type == 'invalid') { template::add_message(tr("Invalid username or password.  Please double check your login credentials and try again."), 'error'); }
    elseif ($type == 'expired') { template::add_message(tr("Your session has expired due to inactivity.  Please login again."), 'error'); }
    elseif ($type == 'inactive') { template::add_message(tr("Your account is currently inactive, and not allowed to login.  Please contact customer support for further information."), 'error'); }
    elseif ($type == 'pending') { template::add_message(tr("Your account is currently pending, and must first be approved by customer support.  You will receive an e-mail once your account has been activated.", 'error')); }

    // Set template response
    registry::set_route('login');
    registry::set_response(template::parse());

    // Return
    return false;

}

/**
* Logs out a user
*/
public static function logout():bool 
{

    // Ensure user is logged in
    if (!registry::has_cookie(self::$cookie_name)) { return true; }

    // Delete session
    registry::$redis->del('auth:' . hash('sha512', registry::cookie(self::$cookie_name)));
    unset($_COOKIE[self::$cookie_name]);
    registry::set_userid(0);

    // Return
    return true;

}

/**
* Checks a username / password if it's valid, and nothing more.  
* Used for APIs, such as the /repo/ JSON API.
*
*     @param string $username The username to check.
*     @param string $password The password to check.
*     @return bool Whther or not the username / password is valid.
*/
public static function check_password(string$username, string$password)
{

    // Debug
    debug::add(2, fmsg("Authentication, raw user / pass check, auth_type: {1}, username: {2}", self::$auth_type, $username), __FILE__, __LINE__);

    // Get user row
    if (!$user_row = DB::get_row("SELECT * FROM " . self::$users_table . " WHERE username = %s", $username)) { 
        return false;
    }
    if ($user_row['status'] != 'active') { return false; }

    // Check password
    if (!password_verify($password, base64_decode($user_row['password']))) { 
        return false;
    }

    // Return
    registry::set_userid((int) $user_row['id']);
    return (int) $user_row['id'];

}

/**
* Authenticate via 2FA.  General 2FA function that checks the user / admin profile, and 
* configuration settings to see what level of authentication is required
*/
public function authenticate_2fa()
{


}

/**
* Conduct 2FA authentication via e-mail.
*/
public static function authenticate_2fa_email(int $is_login = 0)
{

    // Check if authenticated
    if (registry::$verified_2fa === true) { return true; }

    // Generate hash
    $hash_2fa = strtolower(io::generate_random_string(32));
            $hash_2fa_enc = hash('sha512', $hash_2fa);

    // Set vars
    $vars = array(
        'is_login' => $is_login, 
        'auth_hash' => hash('sha512', registry::$auth_hash), 
        'userid' => registry::$userid, 
        'http_controller' => registry::$http_controller, 
        'panel' => registry::$panel, 
        'theme' => registry::$theme, 
        'route' => registry::$route, 
        'request_method' => registry::$request_method, 
        'get' => registry::getall_get(), 
        'post' => registry::getall_post()
    );

    // Set 2FA session
    $key = '2fa:email:' . $hash_2fa_enc;
    registry::$redis->set($key, json_encode($vars));
    registry::$redis->expire($key, 1200);

    debug::add(1, fmsg("2FA authentication required.  Exiting, and forcing display of 2fa.tpl template"), __FILE__, __LINE__);

    // Send e-mails
    message::process_emails('system', 0, array('action' => '2fa'), array('2fa_hash' => $hash_2fa));

    // Parse template
    registry::echo_template('2fa');

    // Return
    return false;

}

/**
* Conduct 2FA authentication via SMS
*/
public function authenticate_2fa_sms()
{
}

/**
* Get encryption password
*/
public static function get_encpass()
{


{

    // Initial checks
    if (!registry::has_cookie(self::$cookie_name)) { return false; }
    $auth_hash = 'auth:' . hash('sha512', registry::cookie(self::$cookie_name));

    // Get password
    if (!$password = registry::$redis->hget($auth_hash, 'enc_pass')) { 
        return false;
    }

    // Return
    return $password;

}
}

/**
* Authenticate the Google reCaptcha
*     @return bool Whether or not the authentication was successful
*/
public static function recaptcha()
{

    // Check if enabled
    if (registry::config('core:recaptcha_site_key') == '') { return true; }

    // Set request
    $request = array(
        'secret' => registry::config('recaptcha_secret_key'), 
        'response' => registry::post('g-recaptcha-response'), 
        'remoteip' => registry::$ip_address
    );

    // Send request
    $response = io::send_http_request('https://www.google.com/recaptcha/api/siteverify', 'POST', $request);

    // Decode JSON
    if (!$vars = json_decode($response, true)) {
    return false;
    }

    // Check response
    if (isset($vars['success']) && $vars['success'] == true) { $ok = true; }
    else { $ok = false; }

    // Return
    return $ok;

}

}

