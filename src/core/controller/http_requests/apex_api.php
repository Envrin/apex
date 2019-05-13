<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;

class apex_api extends \apex\core\controller\http_requests
{

/**
* Blank PHP class for the controller.  For the 
* correct methods and properties for this class, please 
* review the abstract class located at:
*     /src/core/controller/core.php
*
*/
public function process()
{

    // Set content type
    registry::set_content_type('text/json');

    // Server check
    if (registry::$uri[0] == 'server_check') { 
        registry::set_response(registry::config('core:server_status'));

    }




}

}
