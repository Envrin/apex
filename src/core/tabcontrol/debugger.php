<?php
declare(strict_types = 1);

namespace apex\core\tabcontrol;

use apex\DB;
use apex\template;
use apex\registry;
use apex\log;
use apex\debug;
use apex\core\hashes;

class debugger extends \apex\abstracts\tabcontrol
{

    // Define tab pages
    public $tabpages = array(
        'general' => 'General', 
    'trace' => 'Trace', 
        'line_items' => 'Line Items', 
    'input' => 'Input Arrays', 
    'server' => 'Server',
    'sql' => 'SQL Queries'
    );

/**
* Is executed every time the tab control is displayed, 
* is used to perform any actions submitted within forms 
* of the tab control, and mainly to retrieve and assign variables 
* to the template engine.
*
*     @param array $data The attributes contained within the <e:function> tag that called the tab control.
*/

public function process(array $data) 
{

    // Get data
    if (isset($data['from_redis']) && $data['from_redis'] == 1) { 
        $data = json_decode(registry::$redis->get('config:debug_log'), true);
    } else { 
        $data = debug::$data;
    }

    // Get URI
    $uri = $data['registry']['http_controller'] == 'public' ? 'public' : $data['registry']['http_controller'];
    $uri = '/' . $uri . '/' . $data['registry']['route'];

    // Get authenticated user
    if ($data['registry']['userid'] > 0) { 
        $table = $data['registry']['panel'] == 'admin' ? 'admin' : 'users';
        $auth_user = DB::get_field("SELECT username FROM $table WHERE id = %i", $data['registry']['userid']);
        $auth_user .= ' (ID# ' . $data['registry']['userid'] . ')';
    } else { $auth_user = 'Not Logged In'; }

    // Set request info
    $req = array(
        'request_method' => $data['registry']['request_method'], 
        'uri' => $uri, 
        'date_added' => fdate($data['date'], true), 
        'exec_time' => ($data['end_time'] - $data['start_time']), 
        'ip_address' => $data['registry']['ip_address'], 
        'user_agent' => $data['registry']['user_agent'], 
        'panel' => $data['registry']['panel'], 
        'theme' => $data['registry']['theme'], 
        'language' => hashes::get_stdvar('language', $data['registry']['language']), 
        'timezone' => hashes::get_stdvar('timezone', $data['registry']['timezone']), 
        'auth_user' => $auth_user, 
        'action' => $data['registry']['action']
    );

    // Input arrays
    list($post, $get, $cookie, $server, $sql) = array(array(), array(), array(), array(), array());
    foreach ($data['post'] as $key => $value) { array_push($post, array('key' => $key, 'value' => $value)); }
    foreach ($data['get'] as $key => $value) { array_push($get, array('key' => $key, 'value' => $value)); }
    foreach ($data['cookie'] as $key => $value) { array_push($cookie, array('key' => $key, 'value' => $value)); }
    foreach ($data['server'] as $key => $value) { array_push($server, array('key' => $key, 'value' => $value)); }
    foreach ($data['sql'] as $query) { array_push($sql, array('query' => $query)); }

    // Template variables
    template::assign('req', $req);
    template::assign('trace', $data['backtrace']);
    template::assign('notes', $data['notes']);
    template::assign('post', $post);
    template::assign('get', $get);
    template::assign('cookie', $cookie);
    template::assign('server', $server);
    template::assign('sql', $sql);

}

}

