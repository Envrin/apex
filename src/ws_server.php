<?php

namespace apex;

use apex\DB;
use apex\core\lib\message;
use apex\core\lib\wsbot;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require(dirname(realpath(__FILE__)) . "/load.php");

$http = new HttpServer(new WsServer(new wsbot()));
$server = IoServer::factory($http, 8194);

// Update PID
registry::update_config_var('core:websocket_pid', getmypid());

// Run server
$server->run();


