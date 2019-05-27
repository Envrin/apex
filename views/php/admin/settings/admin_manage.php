<?php

namespace apex;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use apex\core\lib\template;


// Template variables
template::assign('admin_id', registry::get('admin_id'));

