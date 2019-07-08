<?php
declare(strict_types = 1);

namespace apex\core\tabcontrol;

use apex\app;
use apex\services\db;
use apex\services\debug;
use apex\services\template;
use apex\services\redis;
use apex\app\utils\hashes;
use apex\app\interfaces\components\tabcontrol;


class debugger implements tabcontrol
{




    private $app;
    private $hashes;

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
 * Constructor.  Grab some injected dependencies we will need. 
 */
public function __construct(app $app, hashes $hashes)
{ 
    $this->app = $app;
    $this->hashes = $hashes;
}

/**
 * Process tab control. 
 *
 * This method is executed every time the tab control is displayed, and allows 
 * you to execute any necessary actions, assign various template variables as 
 * necessary, and so on. 
 *
 * @param array $data The attributes contained within the <e:function> tag that called the tab control.
 */
public function process(array $data)
{ 

    // Get data
    if (isset($data['from_redis']) && $data['from_redis'] == 1) { 
        $data = json_decode(redis::get('config:debug_log'), true);
    } else { 
        $data = debug::get_data();
    }

    // Get URI
    $uri = $data['registry']['http_controller'] == 'public' ? 'public' : $data['registry']['http_controller'];
    $uri = '/' . $uri . '/' . $data['registry']['uri'];

    // Get authenticated user
    if ($data['registry']['userid'] > 0) { 
        $table = $data['registry']['area'] == 'admin' ? 'admin' : 'users';
        $auth_user = db::get_field("SELECT username FROM $table WHERE id = %i", $data['registry']['userid']);
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
        'panel' => $data['registry']['area'],
        'theme' => 'N/A', 
        'language' => $this->hashes->get_stdvar('language', $data['registry']['language']),
        'timezone' => $this->hashes->get_stdvar('timezone', $data['registry']['timezone']),
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

