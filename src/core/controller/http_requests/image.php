<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\DB;
use apex\core\lib\registry;
use apex\core\images;
use apex\core\lib\rpc;

class image extends \apex\core\controller\http_requests
{

/**
* Pulls specified image from the 'images' database table, 
* and properly displays it to the browser.
* Meant to allow easy image management and storage via the /lib/image.php class.
*/
public function process()
{

    // Get size
    $size = registry::$uri[2] ?? 'full';
    $size = preg_replace("/\..+$/", "", strtolower($size));

    // Display image
    images::display(registry::$uri[0], registry::$uri[1], $size);

    // Echo response
    registry::echo_response();

}

}

