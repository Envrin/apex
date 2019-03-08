<?php
declare(strict_types = 1);

namespace apex;

use apex\apex_cli;

// Load
require("./src/load.php");

// Check for CLI
if (php_sapi_name() != "cli") {
    die("You can only execute this script via CLI.  Please login to your server via SSH, and run commands from prompt.");
}

// Initialize
$client = new apex_cli();
$action = strtolower($argv[1]);

// Execute action
array_splice($argv, 0, 2);
$client->process($action, $argv);

// Echo response
echo registry::get_response();

// Exit
exit(0);

