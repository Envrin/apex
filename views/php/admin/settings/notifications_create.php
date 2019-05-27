<?php

namespace apex;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\debug;
use apex\core\lib\template;
use apex\core\components;
use apex\core\notification;

// Template variables
template::assign('controller', registry::post('controller'));


