<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\registry;
use apex\template;

class http extends \apex\core\controller\http_requests
{

/**
* Used as a catch-all, and handles all HTTP requests to any URI 
* for which their is not a specific http_requests controller defined.
* Treats all requests as a page on the public web site.
*/
public function process()
{
    // Set theme
    registry::$theme = registry::config('core:theme_public');

    // Parse template
    registry::set_response(template::parse());

}

}

