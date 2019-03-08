<?php

namespace apex;

use apex\core\components;
use apex\core\notification;

// Create notification
if (registry::$action == 'create') {

    // Create
    $client = new Notification();
    $client->create(registry::getall_post());

    // Add message
    template::add_message("Successfully added new e-mail notification, %s", 'success', $_POST['subject']);

// Edit notification
} elseif (registry::$action == 'edit') { 

    // Update notification
    $client = new notification();
    $client->edit(registry::post('notification_id'));

    // User message
    template::add_message(tr("Successfully updated the e-mail notification, %s", registry::post('subject')));

}



// Go through controllers
$controllers = array();
$controller_options = '<option value="">--------------------</option>';
$aliases = DB::get_column("SELECT alias FROM internal_components WHERE type = 'controller' AND package = 'core' AND parent = 'notifications' ORDER BY alias");
foreach ($aliases as $alias) { 
    $client = components::load('controller', $alias, 'core', 'notifications');

    // Add to options
    $name = isset($client->display_name) ? $client->display_name : $alias;
    $controller_options .= "<option value=\"$alias\">$name</option>";
}

// Template variables
template::assign('controller_options', $controller_options);

?>

?>
