<?php

namespace apex;

use apex\core\components;
use apex\core\notification;


// Load controller
if (!$client = components::load('controller', registry::post('controller'), 'core', 'notifications')) {
    trigger_error(tr("Unable to load notification controller, %s", registry::post('controller')), E_USER_ERROR);
}

// Get condition
$condition = array();
foreach ($client->fields as $alias => $vars) { 
    $condition[$alias] = $_POST['cond_' . $_POST['controller'] . '_' . $alias];
}

// Get merge fields
$client = new notification();
$merge_vars = $client->get_merge_fields(registry::post('controller'));

// Template variables
template::assign('controller', registry::post('controller'));
template::assign('merge_variable_options', $merge_vars);


