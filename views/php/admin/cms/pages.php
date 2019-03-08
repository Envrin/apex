<?php
declare(strict_types = 1);

namespace apex;

use apex\core\io;


// Update pages
if (registry::$action == 'update_public' || registry::$action == 'update_members') { 

    // Get area
    $area = registry::$action == 'update_members' ? 'members' : 'public';

    // Delete existing
    DB::query("DELETE FROM cms_pages WHERE area = %s", $area);

    // Delete from redis cms:titles
$keys = registry::$redis->hkeys('cms:titles');
    foreach ($keys as $key) { 
        if (!preg_match("/^$area\//", $key)) { continue; }
        registry::$redis->hgel('cms:titles', $key);
    }

    // Delete from redis cms:layouts
    $keys = registry::$redis->hkeys('cms:layouts');
    foreach ($keys as $key) { 
        if (!preg_match("/^$area\//", $key)) { continue; }
        registry::$redis->hdel('cms:layouts', $key);
    }

    // Go through all files
    $files = io::parse_dir(SITE_PATH . '/views/tpl/' . $area);
    foreach ($files as $file) { 
        $file = preg_replace("/\.tpl$/", "", $file);
        $layout = registry::post('layout_' . $area . '_' . $file);
        $title = registry::post('title_' . $area . '_' . $file);
        if ($layout == 'default' && $title == '') { continue; }

        // Add to database
        DB::insert('cms_pages', array(
            'area' => $area, 
            'layout' => $layout, 
            'title' => $title, 
            'filename' => $file)
        );

        // Update redis
        $key = $area . '/' . $file;
        registry::$redis->hset('cms:titles', $key, $title);
        registry::$redis->hset('cms:layouts', $key, $layout);

    }

    // User message
    template::add_message("Successfully updated the title / layout of all necessary pages.");

}


