<?php

namespace apex;

use apex\DB;
use apex\registry;
use apex\core\io;

// Add repo
if (registry::$action == 'add_repo') { 

    // Get repo info
    $url = trim(registry::post('repo_url'), '/') . '/repo/get_info';
    $response = io::send_http_request($url);

    // Try to decide response
    if (!$vars = json_decode($response, true)) { 
        template::add_message("Test connection to repository failed.  Please double check the URL, and try again.", 'error');
    }

    // Check for name
    if (!isset($vars['name'])) { 
        template::add_message("Test connection to repository failed.  Please double check the URL, and try again.", 'error');
    }

    // Check for template errors
    if (template::$has_errors === false) { 

        // Add to DB
        DB::insert('internal_repos', array(
            'username' => registry::post('repo_username'), 
            'password' => registry::post('repo_password'), 
            'url' => trim(registry::post('repo_url'), '/'), 
            'display_name' => $vars['name'])
        );

        // User message
        template::add_message(tr("Successfully added new repository, %s", $vars['name']));
    }

// Update repo
} elseif (registry::$action == 'update_repo') { 



}

