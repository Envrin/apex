<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use apex\core\lib\template;


// Template variables
template::assign('server_id', registry::get('server_id'));

