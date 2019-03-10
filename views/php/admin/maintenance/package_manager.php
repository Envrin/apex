<?php

namespace apex;

use apex\DB;
use apex\registry;
use apex\core\io;

// Add repo
if (registry::$action == 'add_repo') { 

    // Get repo info
    $url = trim(registry::post('repo_url'), '/') . '/repo/get_info';
    if (!$response = io::send_http_request($url)) { 
        template::add_message("Test connection to repository failed.  Please double check the URL, and try again.", 'error');

    // Decode JSON response
    } elseif (!$vars = json_decode($response, true)) { 
        template::add_message("Test connection to repository failed.  Please double check the URL, and try again.", 'error');
    } elseif (!isset($vars['is_apex_repo'])) { 
        template::add_message("Test connection to repository failed.  Please double check the URL, and try again.", 'error');
    } elseif ($vars['is_apex_repo'] != 1) { 
        template::add_message("Test connection to repository failed.  Please double check the URL, and try again.", 'error');
    }

    // Check for template errors
    if (template::$has_errors === false) { 

        // Add to DB
        DB::insert('internal_repos', array(
            'username' => encrypt::encrypt_basic(registry::post('repo_username')), 
            'password' => encrypt::encrypt_basic(registry::post('repo_password')), 
            'url' => trim(registry::post('repo_url'), '/'), 
            'display_name' => $vars['name'], 
            'description' => '')
        );

        // User message
        template::add_message(tr("Successfully added new repository, %s", $vars['name']));
    }

// Update repo
} elseif (registry::$action == 'update_repo') { 

    // Update database
    DB::update('internal_repos', array(
        'username' => encrypt::encrypt_basic(registry::post('repo_username')), 
        'password' => encrypt::encrypt_basic(registry::post('repo_password'))), 
    "id = %i", registry::post('repo_id'));

    // User message
    template::add_message("Successfully updated repository login details");

}

