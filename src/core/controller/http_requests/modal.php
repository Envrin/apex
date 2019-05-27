<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\template;
use apex\core\components;

class modal extends \apex\core\controller\http_requests
{

/**
* Processes all modals that are opened via the open_modal() Javascript function.
*/
public function process()
{

    // Set response content-type to text/json, so 
    // in case of error, a JSON response is returned.
    registry::set_content_type('application/json');

    // Ensure a proper modal was defined in URI
    if (!isset(registry::$uri[0])) { trigger_error("Invalid request", E_USER_ERROR); }

    // Get package / alias
    if (!list($package, $parent, $alias) = components::check('modal', registry::$uri[0])) { 
        throw new ComponentException('not_exists', 'modal', registry::$uri[0]);
    }

    // Get TPL code
    $tpl_file = SITE_PATH . '/views/modal/' . $package . '/' . $alias . '.tpl';
    if (!file_exists($tpl_file)) { trigger_error("The TPL file does not exist for the modal, $parts[0]", E_USER_ERROR); }
    $tpl_code = file_get_contents($tpl_file);

    // Get title
if (preg_match("/<h1>(.+?)<\/h1>/i", $tpl_code, $match)) { 
        $title = $match[1];
        $tpl_code = str_replace($match[0], "", $tpl_code);
    } else { $title = tr('Dialog'); }

    // Load component
    if (!$client = components::load('modal', $alias, $package)) { 
        trigger_error("Unable to load modal with alias '$alias' within the package '$package'", E_USER_ERROR);
    }

    // Execute show() method, if exists
    if (method_exists($client, 'show')) { $client->show(); }

    // Parse HTML
    template::initialize();
    template::load_base_variables();
    $html = template::parse_html($tpl_code);

    // Set results array
    $results = array(
        'title' => template::parse_html($title), 
        'body' => $html
    );

    // Set response
    registry::set_response(json_encode($results));
}
}

