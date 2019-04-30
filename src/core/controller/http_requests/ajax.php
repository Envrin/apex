<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\DB;
use apex\registry;
use apex\auth;
use apex\core\components;

class ajax extends \apex\core\controller\http_requests
{

/**
* Processes the AJAX request, handles all HTTP requests sent to /ajax/ URI
* Performs necessary back-end operations, then utilizes the /lib/ajax.php class to 
( change DOM elements as necessary.
*/
public function process() 
{

    // Set response content-type to text/json, 
    // so in case of error, a JSON error will be returned.
    registry::set_content_type('application/json');

    // Ensure a proper alias and/or package is defined
    if (!isset(registry::$uri[0])) { trigger_error("Invalid request 123445", E_USER_ERROR); }
    if (isset(registry::$uri[1]) && registry::$uri[1] != '') { 
        $ajax_alias = registry::$uri[0] . ':' . registry::$uri[1];
    } else { 
        $ajax_alias = registry::$uri[0];
    }

    // Check if component exists
    if (!list($package, $Parent, $alias) = components::check('ajax', $ajax_alias)) { 
        trigger_error("AJAX function does not exist '$alias' within the package ''", E_USER_ERROR);
    }

    // Load the AJAX function class
    if (!$client = components::load('ajax', $alias, $package)) { 
        trigger_error("Unable to load the AJAX function '$alias' within the package '$package'", E_USER_ERROR);
    }

    // Check auth
    $auth_type = registry::post('auth_type') ?? 'user';
    if ($auth_type == 'admin') { registry::$panel = 'admin'; }
    auth::set_auth_type($auth_type);
    auth::check_login(false);

    // Process the AJAX function
    $client->process();

    // Display response
    $response = array(
        'status' => 'ok', 
        'actions' => $client->results
    );

    // Set the response
    registry::set_response(json_encode($response));

}

}

