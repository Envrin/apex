<?php
declare(strict_types = 1);

namespace apex\app\sys;

use apex\app;
use apex\services\db;
use apex\services\debug;
use apex\services\msg;
use apex\services\template;
use apex\services\redis;
use apex\app\interfaces\AuthInterface;
use apex\app\io\io;
use apex\app\utils\hashes;
use apex\app\msg\objects\event_message;
use apex\core\admin;
use apex\users\user;


/**
 * Authentication Library
 *
 * Service: apex\utils\auth
 *
 * Handles all authentication functionality including checking for a valid 
 * session and whether or not a user is authenticated, 2FA requests, invalid / 
 * expired logins, etc.
 *
 * This class is available within the services container, meaning its methods can be accessed statically via the 
 * service singleton as shown below.
 *
 * PHP Example
 * --------------------------------------------------
 * 
 * </php
 * 
 * namespace apex;
 * 
 * use apex\app;
 * use apex\services\auth;
 *
 * // Auto login a user
 * $userid = 582;
 * auth::auto_login($userid);
 *
 */
class auth implements AuthInterface
{


    // Injected properties
    private $app;
    private $io;
    private $hashes;

    // User type properties
    private $user_class;
    private $recipient;
    private $users_table;
    private $cookie_name;
    private $user_type;
    private $is_invalid = false;

    // Configuration properties
    private $expire_secs;
    private $password_retries_allowed;
    private $force_password_reset_time;
    private $require_2fa;


/**
 * Constructor.  Grab some injected dependencies, and set a few basic 
 * variables. 
 */
public function __construct(app $app, io $io, hashes $hashes)
{ 

    // Injected properties
    $this->app = $app;
    $this->io = $io;
    $this->hashes = $hashes;

    // user type variables
    if (app::get_area() == 'admin') { 
        $package = 'core';
        $this->user_class = admin::class;
        $this->users_table = 'admin';
        $this->user_type = 'admin';
        $this->cookie_name = app::_config('core:cookie_name') . '_admin_auth_hash';
    } else { 
        $package = 'users';
        $this->user_class = user::class;
        $this->users_table = 'users';
        $this->user_type = 'user';
        $this->cookie_name = app::_config('core:cookie_name') . '_user_auth_hash';
    }

    // Configuration variables
    $this->expire_secs = (app::_config($package . ':session_expire_mins') * 60);
    $this->password_retries_allowed = app::_config($package . ':password_retries_allowed');
    $this->force_password_reset_time = app::_config($package . ':force_password_reset_time');
    $this->require_2fa = app::_config($package . ':require_2fa');



}

/**
 * Check whether or not a user is authenticated. 
 *
 * @param bool $require_login Whether or not authentication is required for this request.  if true, and not authenticated, the login page will be displayed.
 */
public function check_login(bool $require_login = false)
{ 

    // Logout / login, if necessary
    if (preg_match("/logout$/", app::get_uri())) { 
        return $this->logout();
    } elseif (app::get_action() == 'login' && $this->is_invalid === false) { 
        return $this->login();
    }

    // Check for session
    if (!list($redis_key, $row) = $this->check_for_session()) { 
        if ($require_login === true) { $this->invalid_login(); }
        return false;
    }

    // Update last seen
    $recipient = (app::get_area() == 'admin' ? 'admin:' : 'user:') . $row['userid'];
    redis::hset('auth:last_seen', $recipient, time());

    // Update session expiry
    $seconds = isset($row['remember_me']) && $row['remember_me'] == 1 ? 2592000 : $this->expire_secs;
        redis::expire($redis_key, $seconds);
    if (app::get_uri() != 'admin') { 
        redis::expire($recipient, 259200);
    }

        // Load user profile
    if (!$profile = $this->app->make($this->user_class, ['id' => (int) $row['userid']])->load()) { 
        throw new UserException('not_exists', $row['userid']);
    }
    if ($profile['status'] != 'active') { $this->invalid_login($profile['status']); }

    // Set localization (timezone, language, currency)
    app::set_userid((int) $row['userid']);
    app::set_timezone($profile['timezone']);
    app::set_language($profile['language']);
    if (isset($profile['currency'])) { app::set_currency($profile['currency']); }

        // Add page history
        self::add_page_history($row['history_id']);

        // Debug and log
        debug::add(2, fmsg("Successfully authenticated usesr, area: {1}, userid: {2}, username: {3}", app::get_area(), $row['userid'], $profile['username']), __FILE__, __LINE__, 'info');

        // Return
        return true;


}

/**
 * Check for a session 
 */
private function check_for_session()
{ 
    // Check for cookie
    if (!app::has_cookie($this->cookie_name)) { 
        return false;
    }

    // Check redis for session
    $chk_hash = 'auth:' . hash('sha512', app::_cookie($this->cookie_name));
    if (!$row = redis::hgetall($chk_hash)) { 
        return false;
    }

    // Debug
    debug::add(3, fmsg("Found authenticated session, userid: {1}, uri: {2}", $row['userid'], app::get_uri()), __FILE__, __LINE__);

    // Check for pending 2FA request
    if ($row['2fa_status'] == 0 && app::get_area() != 'public') { 
        debug::add(3, "Auth session still requires 2FA authorization", __FILE__, __LINE__);
        app::echo_template('2fa', true);
    }

    // Check IP address
    if (app::get_ip() != $row['ip_address']) { 
        debug::add(2, fmsg("Authentication error.  Session and current user IP addresses do not match.  Session IP: {1}, Current IP: {2}", $row['ip_address'], app::get_ip()), __FILE__, __LINE__, 'warning');
        $this->invalid_login('invalid');
        return false;
    }

    // Check user agent
    if (app::get_user_agent() != $row['user_agent']) { 
        debug::add(2, fmsg("Authentication error.  Session and current user agents do not match.  Session UA: {1}, Current UA: {2}", $row['user_agent'], app::get_user_agent()), __FILE__, __LINE__, 'warning');
        $this->invalid_login('invalid');
        return false;
    }

    // Return
    return array($chk_hash, $row);

}

/**
 * Login a user. 
 */
public function login()
{ 

    // Debug / log
    debug::add(2, fmsg("Initiating login process, area: {1}, username: {2}", app::get_area(), app::_post('username')), __FILE__, __LINE__, 'info');

    // Get user ID
    if (app::get_area() == 'admin') { 
        $userid = db::get_field("SELECT id FROM admin WHERE username = %s", app::_post('username'));
    } else { 
        $userid = redis::hget('usernames', app::_post('username'));
    }

    // Check username exists
    if (!$userid) { 
        debug::add(2, fmsg("Login failed, username does not exist, area: {1}, username: {2}", app::get_area(), app::_post('username')), __FILE__, __LINE__);
        $this->invalid_login('invalid');
        return false;
    }
    $userid = (int) $userid;

    // Load profile
    $user = $this->app->make($this->user_class, ['id' => $userid]);
    if (!$profile = $user->load()) { 
        debug::add(2, fmsg("Invalid login.  Unable to load user profile, area: {1}, username: {2}", app::get_area(), app::_post('username')), __FILE__, __LINE__);
        $this->invalid_login('invalid');
        return false;
    }

    // Check status
    if ($profile['status'] != 'active') { 
        $this->invalid_login($profile['status']);
        return false;
    }

    // Check password
    if (!password_verify(app::_post('password'), base64_decode($profile['password']))) { 
        debug::add(2, fmsg("Invalid password during login, area: {1}, username: {2}", app::get_area(), app::_post('username')), __FILE__, __LINE__);

        // Check # of retries
        if ($this->password_retries_allowed > 0 && $profile['invalid_logins'] >= $this->password_retries_allowed) { 
            debug::add(2, fmsg("Authentication error, invalid password and user exceeded max retries allowed, deactivating account.  area: {1}, username: {2}, failed_logins: {3}", app::get_area(), app::_post('username'), $profile['invalid_logins']), __FILE__, __LINE__, 'warning');
            $user->update_status('inactive');
        }

        // Update invalid logins
        if (app::get_area() != 'admin') { 
            redis::HINCRBy('user:' . $userid, 'invalid_logins', 1);
        }
        db::query("UPDATE $this->users_table SET invalid_logins = invalid_logins + 1 WHERE id = %i", $userid);

        // Invalid login
        $this->invalid_login('invalid');
        return false;
    }

    // Check if new device
    $cookie = app::_config('core:cookie_name') . '_auth_sechash';
    $new_device = app::has_cookie($cookie) && hash('sha512', app::_cookie($cookie)) == $profile['sec_hash'] ? false : true;

    // Check if 2FA required
    list($require_2fa, $require_2fa_phone) = array(0, 0);
    if (app::get_area() == 'admin' || $this->require_2fa == 'optional') { 
        $require_2fa = $profile['require_2fa'];
        $require_2fa_phone = $profile['require_2fa_phone'];
    } elseif ($this->require_2fa == 'session') { 
        list($require_2fa, $require_2fa_phone) = array(1, 1);
    } elseif ($this->require_2fa == 'new_device' && $new_device === true) { 
        list($require_2fa, $require_2fa_phone) = array(1, 1);
    }

    // Check if phone or e-mail 2FA required
    if ($require_2fa_phone == 1 && $user_row['phone'] != '') { 
        $require_2fa = 0;
    } else { $require_2fa_phone = 0; }

    // Check security question
    $this->check_security_question($userid, $profile['sec_hash']);

    // Check IP address
    $this->check_ip_restrictions($userid);

    // Create login session
    $session_id = $this->create_session($userid);

    // Return
    return true;


}

/**
 * Auto login a user.  Generally used by administrator to remotely login to a 
 * user account, and for unit tests. 
 *
 * @param int $userid The ID# of the user to login as.
 */
public function auto_login(int $userid)
{ 

    // Create session
    //app::set_area('members');
    return $this->create_session($userid);

}

/**
 * Create new login session 
 *
 * @param int $userid The ID# of the user to login
 * @param int $require_2fa A 1/0 whether or not 2FA via e-mail is required
 * @param int $require_2fa_phone A 1/0 whether or not 2FA via phone is required
 */
private function create_session(int $userid, int $require_2fa = 0, int $require_2fa_phone = 0)
{ 

    // Generate session ID
    do { 
        $session_id = $this->io->generate_random_string(60);
        $exists = redis::exists('auth:' . hash('sha512', $session_id));
    } while ($exists > 0);

    // Debug / log
    debug::add(1, fmsg("Authentication successful, session ID generated, area: {1}, username: {2}, session_id: {3}", app::get_area(), app::_post('username'), $session_id), __FILE__, __LINE__);

    // Add session to DB
    $remember_me = app::_post('remember_me') ?? 0;
    $vars = array(
        'type' => (app::get_area() == 'admin' ? 'admin' : 'user'),
        'userid' => $userid,
        'enc_pass' => app::has_post('password') ? md5(app::_post('password')) : '',
        '2fa_status' => ($require_2fa == 1 ? 0 : 1),
        '2fa_phone_status' => ($require_2fa_phone == 1 ? 0 : 1),
        'remember_me' => $remember_me,
        'ip_address' => app::get_ip(),
        'user_agent' => app::get_user_agent()
    );

    // Add login history
    $msg = new event_message('core.logs.add_auth_login', $vars);
    $vars['history_id'] = msg::dispatch($msg)->get_response('core');

    // Set redis variables
    $seconds = $remember_me == 1 ? 2592000  : $this->expire_secs;
    $redis_key = 'auth:' . hash('sha512', $session_id);
    $this->recipient = (app::get_area() == 'admin' ? 'admin:' : 'user:') . $userid;

    // Add session to redis
    redis::hmset($redis_key, $vars);
    redis::expire($redis_key, $seconds);
    redis::hset('auth:last_seen', $this->recipient, time());

    // Set cookie
    $expire = $remember_me == 1 ? (time() + $seconds) : 0;
    if (!app::set_cookie($this->cookie_name, $session_id, $expire, '/')) { 
        throw new ApexException('alert', "Unable to set login cookie.  Customer support has been notified, and will resolve the issue shortly.  Please try again later.");
    }

    // Set user ID
    app::set_userid($userid);

    // Initiate 2FA, if needed
    if ($require_2fa_phone == 1) { $this->authenticate_2fa_sms(1); }
    elseif ($require_2fa == 1) { $this->authenticate_2fa_email(1); }

    // Debug
    debug::add(1, fmsg("Completed successful login, area: {1}, username: {2}", app::get_area(), app::_post('username')), __FILE__, __LINE__, 'info');

    // Change panel, if needed
    if (app::get_area() != 'admin') { 
        $uri = app::_config('users:login_method') == 'index' ? '/index' : '/members/index';
        header("Location: $uri");
        exit(0);
    }

    // Parse template
    app::set_uri('index', true);
    app::set_res_body(template::parse());

    // Return
    return true;

}

/**
 * Logs out a user 
 */
public function logout():bool
{ 

    // Ensure user is logged in
    if (!app::has_cookie($this->cookie_name)) { return true; }

    // Delete session
    redis::del('auth:' . hash('sha512', app::_cookie($this->cookie_name)));
    unset($_COOKIE[$this->cookie_name]);
    app::set_userid(0);

    // Return
    return true;

}

/**
 * Check secondary security question 
 *
 * Checks user for secondary question.  If the system can not recognize the 
 * user has previously logged in from this browser / computer, will prompt the 
 * user to answer a pre-defined security question. 
 */
protected function check_security_question(int $userid, string $chk_sec_hash):bool
{ 

    // Check for cookie
    $cookie = app::_config('core:cookie_name') . '_' . (app::get_area() == 'admin' ? 'admin' : 'user') . '_auth_sechash';
    if (app::has_cookie($cookie) && hash('sha512', app::_cookie($cookie)) == $chk_sec_hash) { 
        debug::add(4, fmsg("Authentication, user is already validated via security question from previous session, area: {1}, username: {2}", app::get_area(), app::_post('username')), __FILE__, __LINE__);
        return true;
    }

    // Check answer, if needed
    $ask_question = true;
    $invalid_answer = false;
    if (app::has_post('answer') && app::has_post('question_id')) { 

        // Check answer
        $answer = db::get_field("SELECT answer FROM auth_security_questions WHERE type = %s AND userid = %i AND question = %s", $this->user_type, $userid, app::_post('question_id'));
        if (password_verify(app::_post('answer'), base64_decode($answer)) === true) { 
            debug::add(2, fmsg("Successfully answered secondary security question, area: {1}, username: {2}", app::get_area(), app::_post('username')), __FILE__, __LINE__, 'info');
            $ask_question = false;
        } else { 
            $invalid_answer = true;
        }
    }

    // Ask question, if needed
    if ($ask_question === true) { 

        // Debug
        debug::add(3, fmsg("Authentication, secondary security question required.  Displaying form, area: {1}, username: {2}", app::get_area(), app::_post('username')), __FILE__, __LINE__, 'info');

        // Get random question
        if (!$row = db::get_row("SELECT * FROM auth_security_questions WHERE type = %s AND userid = %i ORDER BY RAND() LIMIT 0,1", $this->user_type, $userid)) { 
            return true;
        }

        // Start template
        app::set_uri('security_question', true);
        if ($invalid_answer === true) { 
            template::add_callout(tr("We're sorry, but your answer to the security question was incorrect.  Please try again."), 'error');
        }

        // Assign template variables
        template::assign('username', app::_post('username'));
        template::assign('password', app::_post('password'));
        template::assign('question_id', $row['question']);
        template::assign('question', $this->hashes->get_hash_var('core:secondary_security_questions', $row['question']));

        // Parse template
        app::set_res_body(template::parse());
        return false;
    }

    // Set cookie
    $sec_hash = $this->io->generate_random_string(50);
    setcookie($cookie, $sec_hash, (time() + 2592000));

    // Update secondary hash indb
    $this->app->make($this->user_class, ['id' => $userid])->update_sec_auth_hash($sec_hash);

    // Return
    return true;

}

/**
 * Checks the user's IP address against any IP restrictions that have been 
 * pre-defined and are are in the database. 
 *
 * @param int $userid The ID# of the user to check IP restrictions for
 *
 * @return bool Whther or not the check was successful
 */
protected function check_ip_restrictions(int $userid):bool
{ 

    // Check if IP records exist
    $user_type = app::get_area() == 'admin' ? 'admin' : 'user';
    $ips = db::get_column("SELECT ip_address FROM auth_allowips WHERE type = %s AND userid = %i", $user_type, $userid);
    if (count($ips) == 0) { 
        return true;
    }

    // Debug
    debug::add(4, fmsg("Authentication, checking IP address restrictions, area: {1}, username: {2}", app::get_area(), app::_post('username')), __FILE__, __LINE__);

    // Check if IP allowed
    if (!in_array(app::get_ip(), $ips)) { 
        debug::add(3, fmsg("Authentication error, IP address not allowed, area: {1}, username: {2}, ip_address: {3}", app::get_area(), app::_post('username'), app::get_ip()), __FILE__, __LINE__, 'warning');
        $this->invalid_login();
    }

    // Return
    return true;

}

/**
 * Process invalid login 
 *
 * Processes an invalid login, and outputs the login.tpl template with any 
 * necessary user message (eg. invalid user / pass submitted). ( 
 *
 * @param string $type The type / reason for the invalid login (eg. expired, invalid, etc.)
 */
public function invalid_login(string $type = 'none')
{ 

    // Debug
    debug::add(2, fmsg("Authentication, invalid login, area: {1}, type: {2}", app::get_area(), $type), __FILE__, __LINE__, 'info');

    // Logout
    $this->logout();
    $this->is_invalid = true;

    // Add template message
    if ($type == 'invalid') { template::add_callout(tr("Invalid username or password.  Please double check your login credentials and try again."), 'error'); }
    elseif ($type == 'expired') { template::add_callout(tr("Your session has expired due to inactivity.  Please login again."), 'error'); }
    elseif ($type == 'inactive') { template::add_callout(tr("Your account is currently inactive, and not allowed to login.  Please contact customer support for further information."), 'error'); }
    elseif ($type == 'pending') { template::add_callout(tr("Your account is currently pending, and must first be approved by customer support.  You will receive an e-mail once your account has been activated.", 'error')); }

    // Set template response
    app::set_uri('login', true);
    app::set_res_body(template::parse());

    // Return
    return false;

}

/**
 * Checks a username / password if it's valid, and nothing more. Used for 
 * APIs, such as the /repo/ JSON API. 
 *
 * @param string $username The username to check.
 * @param string $password The password to check.
 *
 * @return bool Whther or not the username / password is valid.
 */
public function check_password(string$username, string$password)
{ 

    // Debug
    debug::add(2, fmsg("Authentication, raw user / pass check, area: {1}, username: {2}", app::get_area(), $username), __FILE__, __LINE__);

    // Get user row
    if (!$profile = db::get_row("SELECT * FROM $this->users_table WHERE username = %s", $username)) { 
        return false;
    }
    if ($profile['status'] != 'active') { return false; }

    // Check password
    if (!password_verify($password, base64_decode($profile['password']))) { 
        return false;
    }

    // Return
    app::set_userid((int) $profile['id']);
    return (int) $profile['id'];

}

/**
 * Conduct 2FA authentication via e-mail. 
 *
 * @param int $is_login A 1/0 defining whether or not the 2FA is for a user login.
 */
public function authenticate_2fa_email(int $is_login = 0)
{ 

    // Check if authenticated
    //if (app::$verified_2fa === true) { return true; }

    // Generate hash
    $hash_2fa = strtolower($this->io->generate_random_string(32));
            $hash_2fa_enc = hash('sha512', $hash_2fa);

    // Set vars
    $vars = array(
        'is_login' => $is_login,
        'auth_hash' => hash('sha512', app::$auth_hash),
        'userid' => app::get_userid(),
        'http_controller' => app::get_http_controller(),
        'area' => app::get_area(),
        'theme' => app::get_theme(),
        'uri' => app::get_uri(),
        'request_method' => app::get_method(),
        'get' => app::getall_get(),
        'post' => app::getall_post()
    );

    // Set 2FA session
    $key = '2fa:email:' . $hash_2fa_enc;
    redis::set($key, json_encode($vars));
    redis::expire($key, 1200);

    debug::add(1, fmsg("2FA authentication required.  Exiting, and forcing display of 2fa.tpl template"), __FILE__, __LINE__);

    // Send e-mails
    //message::process_emails('system', 0, array('action' => '2fa'), array('2fa_hash' => $hash_2fa));

    // Parse template
    app::echo_template('2fa', true);

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
 * Get encryption password frp, the aith sessopm/ 
 */
public function get_encpass()
{ 

    // Initial checks
    if (!app::has_cookie($this->cookie_name)) { return false; }
    $auth_hash = 'auth:' . hash('sha512', app::_cookie($this->cookie_name));

    // Get password
    if (!$password = redis::hget($auth_hash, 'enc_pass')) { 
        return false;
    }

    // Return
    return $password;

}

/**
 * Authenticate the Google reCaptcha 
 *
 * @return bool Whether or not the authentication was successful
 */
public function recaptcha()
{ 

    // Check if enabled
    if (app::_config('core:recaptcha_site_key') == '') { return true; }

    // Set request
    $request = array(
        'secret' => app::_config('recaptcha_secret_key'),
        'response' => app::_post('g-recaptcha-response'),
        'remoteip' => app::get_ip()
    );

    // Send request
    //$response = io::send_http_request('https://www.google.com/recaptcha/api/siteverify', 'POST', $request);

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

/**
 * Add page history 
 */
private function add_page_history($history_id)
{ 

    // Get post vars
    $post_vars = app::getall_post() ?? array();
    foreach ($post_vars as $key => $value) { 
        if (preg_match("/password/", $key)) { $post_vars[$key] = "*****"; }
    }

    // Set vars
    $vars = array(
        'history_id' => $history_id,
        'request_method' => app::get_method(),
        'area' => app::get_area(),
        'uri' => app::get_uri(),
        'get_vars' => base64_encode(json_encode(app::getall_get())),
        'post_vars' => base64_encode(json_encode($post_vars))
    );

    // Send message to RabbitMQ
    $msg = new event_message('core.logs.add_auth_pageview', $vars);
    $msg->set_type('direct');
    msg::dispatch($msg);

}


}

