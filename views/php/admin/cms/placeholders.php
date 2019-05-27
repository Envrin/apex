<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use apex\core\lib\template;



// Update
if (registry::$action == 'update') { 

    // GO through placeholders
    $rows = DB::query("SELECT * FROM cms_placeholders WHERE uri = %s", registry::post('uri'));
    foreach ($rows as $row) { 

        // Get contents
        $contents= registry::post('contents_' . $row['alias']);
        DB::update('cms_placeholders', array(
            'contents' => $contents), 
        "id = %i", $row['id']);
        // Update redis
        $key = $row['uri'] . ':' . $row['alias'];
        registry::$redis->hset('cms:placeholders', $key, $contents);
    }

    // User message
    template::add_message("Successfully updated necessary placeholders.");

}

