<?php

namespace apex;

use apex\DB;
use apex\message;
use apex\core\components;

require(dirname(realpath(__FILE__)) . "/load.php");

// Update PID
registry::update_config_var('core:rpc_pid', getmypid());

// Listen for messages
$rpc_server = new rpc();
$rpc_server->receive();


