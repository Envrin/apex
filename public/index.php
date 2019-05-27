<?php
declare(strict_types = 1);

namespace apex;

use apex\core\lib\registry;


// Load
require_once('../src/load.php');

// Handle request
registry::handle_request();

// Echo response
registry::echo_response();

// Exit
exit(0);

