<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use apex\core\lib\template;
use apex\core\notification;

// Template variables
template::assign('notification_id', registry::get('notification_id'));


