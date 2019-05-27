<?php
declare(strict_types = 1);

namespace apex\core\ajax;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;

class clear_dropdown extends \apex\core/lib/Ajax 
{

/**
* Processes the AJAX function, and uses the 
* moethds within the 'apex\ajax' class to modify the 
* DOM elements within the web browser.  See 
* documentation for durther details.
*/

public function process()
{

    // Set variables
    $badge_id = 'badge_unread_' . registry::post('dropdown');
    $list_id = 'dropdown_' . registry::post('dropdown');
    $recipient = (registry::$panel == 'admin' ? 'admin:' : 'user:') . registry::$userid;
    $clearall = registry::post('clearall') ?? 0;

    // Reset redis
    $redis_key = 'unread:' . registry::post('dropdown');
    registry::$redis->hset($redis_key, $recipient, 0);

    // Perform necessary actions
    $this->set_text($badge_id, 0);
    $this->set_display($badge_id, 'none');

    // Clear list, if needed
    if ($clearall == 1) {
        $redis_key = registry::post('dropdown') . ':' . $recipient;
        registry::$redis->del($redis_key); 
        $this->clear_list($list_id);
    }

}

}

