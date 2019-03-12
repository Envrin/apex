<?php

namespace apex;

use apex\core\forms;


// Update
if (registry::$action == 'update') { 

    // Set vars
    $vars = array(
        'backups_enable', 
        'backups_save_locally', 
        'backups_remote_service', 
        'backups_aws_access_key', 
        'backups_aws_access_secret', 
        'backups_dropbox_client_id', 
        'backups_dropbox_client_secret', 
        'backups_dropbox_access_token', 
        'backups_gdrive_client_id', 
        'backups_gdrive_client_secret', 
        'backups_gdrive_refresh_token'
    );

    // Update vars
    foreach ($vars as $var) { 
        registry::update_config_var('core:' . $var, registry::post($var));
    }

    // Update date intervals

    registry::update_config_var('core:backups_db_interval', forms::get_date_interval('backups_db_interval'));
    registry::update_config_var('core:backups_full_interval', forms::get_date_interval('backups_full_interval'));
    registry::update_config_var('core:backups_retain_length', forms::get_date_interval('backups_retain_length'));

    // User message
    template::add_message("Successfully updated backup settings");

}


