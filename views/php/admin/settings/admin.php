<?php

namespace apex;

use apex\core\admin;


// Create administrator
if (registry::$action == 'create') { 

    $admin = new admin();
    if ($admin->create() > 0) { 
        template::add_message(tr("Successfully created new administrator, %s", registry::post('username')));
    }

// Update administrator
} elseif (registry::$action == 'update') { 

    // Update admin
    $admin = new admin((int) registry::post('admin_id'));
    $admin->update();

    // User message
    template::add_message("Successfully updated administrator details");

}


