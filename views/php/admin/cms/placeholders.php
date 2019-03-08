<?php
declare(strict_types = 1);

namespace apex;

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

