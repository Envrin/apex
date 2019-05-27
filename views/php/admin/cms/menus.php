<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use apex\core\lib\template;
use apex\core\forms;


// Update menus
if (registry::$action == 'update_public' || registry::$action == 'update_members') { 

    // Delete neded menus
    $deletes = forms::get_chk('delete');
    foreach ($deletes as $menu_id) { 
        if (!$menu_id > 0) { continue; }
        DB::query("DELETE FROM cms_menus WHERE id = %i", $menu_id);
    }

    // Update 'is_active'
    $is_active = forms::get_chk('is_active');
    DB::query("UPDATE cms_menus SET is_active = 0");
    foreach ($is_active as $menu_id) { 
        if (!$menu_id > 0) { continue; }
        DB::query("UPDATE cms_menus SET is_active = 1 WHERE id = %s", $menu_id);
    }

    // Update ordering, as neede
    $area = registry::$action == 'update_public' ? 'public' : 'members';
    $rows = DB::query("SELECT * FROM cms_menus WHERE area = %s", $area);
    foreach ($rows as $row) { 
        $order_num = registry::post('order_' . $row['id']);
        DB::query("UPDATE cms_menus SET order_num = %i WHERE id = %i", $order_num, $row['id']);
    }

    // Update redis
    $pkg = new package('core');
    $pkg->update_redis_menus();

    // User message
    template::add_message("Successfully updated menus as needed");

// Add new menu
} elseif (registry::$action == 'add_menu') { 

    // Set variables
    $require_login = registry::post('require_login') ?? 0;
    $require_nologin = registry::post('require_nologin') ?? 0;

    // Add to DB
    DB::insert('cms_menus', array(
        'package' => '', 
        'area' => registry::post('area'), 
        'is_system' => 0, 
        'require_login' => $require_login, 
        'require_nologin' => $require_nologin, 
        'order_num' => registry::post('order_num'), 
        'link_type' => registry::post('link_type'), 
        'icon' => registry::post('icon'), 
        'parent' => registry::post('parent'), 
        'alias' => registry::post('alias'), 
        'display_name' => registry::post('display_name'), 
        'url' => registry::post('url'))
    );

    // Update redis
    $pkg = new package('core');
    $pkg->update_redis_menus();

    // User message
    template::add_message(tr("Successfully added new menu, %s", registry::post('display_name')));


// Update menu
} elseif (registry::$action == 'update_menu') { 

// Set variables
    $require_login = registry::post('require_login') ?? 0;
    $require_nologin = registry::post('require_nologin') ?? 0;

// Update database
    DB::update('cms_menus', array(
        'area' => registry::post('area'), 
        'require_login' => $require_login, 
        'require_nologin' => $require_nologin, 
        'order_num' => registry::post('order_num'), 
        'link_type' => registry::post('link_type'), 
        'icon' => registry::post('icon'), 
        'parent' => registry::post('parent'), 
        'alias' => registry::post('alias'), 
        'display_name' => registry::post('display_name'), 
        'url' => registry::post('url')), 
    "id = %i", registry::post('menu_id'));

    // Update redis
    $pkg = new package('core');
    $pkg->update_redis_menus();

    // User message
    template::add_message(tr("Successfully updated menu, %s", registry::post('display_name')));

}



