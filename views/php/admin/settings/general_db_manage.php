<?php
declare(strict_types = 1);

namespace apex\views;

use apex\app;
use apex\services\template;

/**
 * All code below this line is automatically executed when this template is viewed, 
 * and used to perform any necessary template specific actions.
 */


// Template variables
template::assign('server_id', app::_get('server_id'));

