<?php
declare(strict_types = 1);

namespace apex\core\worker;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;

class logs
{

/**
* Add login history
*/
public function add_auth_login(string $data) { 

    // Decode JSON
    $vars = json_decode($data, true);

    // Add to DB
    DB::insert('auth_history', array(
        'type' => $vars['type'], 
        'userid' => $vars['userid'], 
        'ip_address' => $vars['ip_address'], 
        'user_agent' => $vars['user_agent'], 
        'logout_date' => date('Y-m-d H:i:s'))
    );
    $history_id = DB::insert_id();

    // Return
    return $history_id;

}

/**
* Add auth history page
*/
public function add_auth_pageview(string $data)
{

    // Decode JSON
    $vars = json_decode($data, true);

    // Set variables
    $uri = $vars['panel'] == 'public' ? $vars['route'] : $vars['panel'] . '/' . $vars['route'];
    // Add to database
    DB::insert('auth_history_pages', array(
        'history_id' => $vars['history_id'],
        'request_method' => $vars['request_method'],
        'uri' => $uri, 
        'get_vars' => $vars['get_vars'], 
        'post_vars' => $vars['post_vars'])
    );

}


}

