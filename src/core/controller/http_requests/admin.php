<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\DB;
use apex\core\lib\template;
use apex\core\lib\registry;
use apex\core\lib\auth;

class admin extends \apex\core\controller\http_requests
{

/**
* Processes all HTTP requests send to http://domain.com/admin/
* Simply checks authentication, then passes the request off to the template engine.
* Also checks if an administrator exists, and if not, prompts to create the first administrator.
*/
public function process()
{

    // Check if admin enabled
    if (ENABLE_ADMIN == 0) { 
        registry::set_http_status(404);
        registry::echo_template('404');
    }

    // Set registry variables
    registry::$panel = 'admin';
    registry::$theme = registry::config('core:theme_admin');
    auth::set_auth_type('admin');

    //  Check if admin exists
    $count = DB::get_field("SELECT count(*) FROM admin");
    if (registry::$action == 'create' && $count == 0) {

        // Create first admin 
        $client = new \apex\core\admin();
        if (!$userid = $client->create()) { 
            registry::set_route('create_first_admin');
            registry::set_response(template::parse());
            return;
        }

        // Set userid
        registry::set_userid((int) $userid);

        auth::login(true);
        return;

    // Display form to create first admin
    } elseif ($count == 0) { 
        registry::set_route('create_first_admin');
        registry::set_response(template::parse());
        return;
    }

    // Check auth
    if (!auth::check_login(true)) { 
        return;
    }

    // Parse template
    registry::set_response(template::parse());

}

}

