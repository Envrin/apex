<?php
declare(strict_types = 1);

namespace apex;

use apex\core\notification;

// Get notification
if (!$row = DB::get_idrow('notifications', registry::get('notification_id'))) { 
trigger_error(tr("Notification does not exist in database, ID# %s", reigstry::get('notification_id')), E_USER_ERROR);
}

// Set variables
$row['contents'] = base64_decode($row['contents']);

// Get merge fields
$client = new notification();
$merge_vars = $client->get_merge_fields($row['controller']);

// Template variables
template::assign('notification_id', $row['id']);
template::assign('notify', $row);
template::assign('merge_variable_options', $merge_vars);
