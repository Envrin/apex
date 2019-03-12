<?php
declare(strict_types = 1);

namespace apex\core\controller\http_requests;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;

class auth2fa extends \apex\core\controller\http_requests
{

/**
* Process a 2FA request
*/
public function process()
{

    // Check for hash
    $hash = registry::$uri[0] ?? '';
    $redis_key = '2fa:email:' . hash('sha512', $hash);
header("Content-type: text/plain");
echo "KEY: $redis_key\n"; 
    // Check for key
    if ($data = registry::$redis->get($redis_key)) { 
        $vars = json_decode($data);
        header("Content-type: text/plain"); print_r($vars); exit;
    } else { 
        echo "NO hash";
    }

}

}

