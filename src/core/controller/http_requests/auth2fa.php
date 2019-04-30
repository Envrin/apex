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
    $hash = (string) trim(registry::$uri[0]) ?? '';
    $redis_key = '2fa:email:' . hash('sha512', trim($hash));

    // Check for key
    if ($data = registry::$redis->get($redis_key)) { 
        $vars = json_decode($data, true);
    } else { 
        registry::echo_template('2fa_nohash');
    }

    // Delete from redis
    registry::$redis->del($redis_key);

    // Update redis session, if needed
    if ($vars['is_login'] == 1) { 
        $redis_key = 'auth:' . $vars['auth_hash'];
        registry::$redis->hset($redis_key, '2fa_status', 1);
        $vars['route'] = 'index';
    }

    // Verify the request
    registry::verify_2fa($vars);

}

}

