<?php
declare(strict_types = 1);

namespace apex;

use apex\core\forms;


// Update general settings
if (registry::$action == 'update_general') { 

    // Set vars
    $vars = array(
        'domain_name', 
        'date_format', 
        'nexmo_api_key', 
        'nexmo_api_secret', 
        'recaptcha_site_key', 
        'recaptcha_secret_key', 
        'openexchange_app_id', 
        'default_language', 
        'default_timezone', 
        'mode', 
        'log_level', 
        'debug_level', 
    );

    // Update config vars
    foreach ($vars as $var) { 
        registry::update_config_var('core:' . $var, registry::post($var));
    }

    // User message
    template::add_message("Successfully updated general settings");

// SIte info settings
} elseif (registry::$action == 'site_info') { 

    // Set vars
    $vars = array(
        'site_name', 
        'site_address', 
        'site_address2', 
        'site_email', 
        'site_phone', 
        'site_tagline', 
        'site_facebook', 
        'site_twitter', 
        'site_linkedin', 
        'site_instagram' 
    );

    // Update config avrs
    foreach ($vars as $var) { 
        registry::update_config_var('core:' . $var, registry::post($var));
    }

    // User message
    template::add_message("Successfully updated site info settings");

// Security settings
} elseif (registry::$action == 'security') { 

    // Set vars
    $vars = array(
        'session_expire_mins', 
        'password_retries_allowed', 
        'require_2fa',  
        'num_security_questions'
    );

    // Update config vars
    foreach ($vars as $var) { 
        registry::update_config_var('core:' . $var, registry::post($var));
    }

    // Update date intervals
    registry::update_config_var('core:session_retain_logs', forms::get_date_interval('session_retain_logs'));
    registry::update_config_var('core:force_password_reset_time', forms::get_date_interval('force_password_reset_time'));

    // User message
    template::add_message("Successfully updated admin panel security settings");

// Add database
} elseif (registry::$action == 'add_database') { 

    // Set vars
    $vars = array(
        'dbname' => registry::post('dbname'), 
        'dbuser' => registry::post('dbuser'), 
        'dbpass' => registry::post('dbpass'), 
        'dbhost' => registry::post('dbhost'), 
        'dbport' => registry::post('dbport')
    );
    registry::$redis->rpush('config:db_slaves', json_encode($vars));

    // User message
    template::add_message("Successfully added new database server");

// Update database
} elseif (registry::$action == 'update_database') { 

    // Set variables
    $server_id = (int) registry::post('server_id');

    // Set vars
    $vars = array(
        'dbname' => registry::post('dbname'), 
        'dbuser' => registry::post('dbuser'), 
        'dbpass' => registry::post('dbpass'), 
        'dbhost' => registry::post('dbhost'), 
        'dbport' => registry::post('dbport')
    );

    // Save to redis
    registry::$redis->lset('config:db_slaves', $server_id, json_encode($vars));

    // User message
    template::add_message("Successfully updated database server");

// Delete databases
} elseif (registry::$action == 'delete_database') { 

    // Get IDs
    $ids = forms::get_chk('db_server_id');
    $slaves = registry::$redis->lrange('config:db_slaves', 0, -1);
    $new_slaves = array();
    // Delete databases
    $num=0;
    foreach ($slaves as $data) { 
        if (!in_array($num, $ids)) { $new_slaves[] = $data; }
        $num++;
    }

    // Reset slave servers
    registry::$redis->del('config:db_slaves');
    foreach ($new_slaves as $data) { 
        registry::$redis->rpush('config:db_slaves', $data);
    }

    // User message
    template::add_message("Successfully deleted checked database servers");

// Add SMTP e-mail server
} elseif (registry::$action == 'add_email') { 

    // Set vars
    $vars = array(
        'is_ssl' => registry::post('email_is_ssl'), 
        'host' => registry::post('email_host'), 
        'username' => registry::post('email_user'), 
        'password' => registry::post('email_pass'), 
        'port' => registry::post('email_port')
    );

    // Add to redis
    registry::$redis->rpush('config:email_servers', json_encode($vars));

    // Add message
    template::add_message("Successfully added new SMTP e-mail server");

// Update e-mail SMTP server
} elseif (registry::$action == 'update_email') { 

    // Set vars
    $vars = array(
        'is_ssl' => registry::post('email_is_ssl'), 
        'host' => registry::post('email_ost'), 
        'username' => registry::post('email_user'), 
        'password' => registry::post('email_pass'), 
        'port' => registry::post('email_port')
    );

    // Update redis database
    registry::$redis->lset('config:email_servers', registry::post('server_id'), json_encode($vars));

    // User message
    template::add_message("Successfully updated e-mail SMTP server");

// Delete e-mail SMTP servers
} elseif (registry::$action == 'delete_email') {

    // Get IDs
    $ids = forms::get_chk('email_server_id');
    $servers = registry::$redis->lrange('config:email_servers', 0, -1);
    $new_servers = array();

    // Delete e-mail servers
    $num=0;
    foreach ($servers as $data) { 
        if (!in_array($num, $ids)) { $new_servers[] = $data; }
        $num++;
    }

    // Reset e-mail servers
    registry::$redis->del('config:email_servers');
    foreach ($new_servers as $data) { 
        registry::$redis->rpush('config:email_servers', $data);
    }

    // User message
    template::add_message("Successfully deleted all checked e-mail SMTP servers");

// Update RabbitMQ info
} elseif (registry::$action == 'update_rabbitmq') { 

    // Set vars
    $vars = array(
        'host' => registry::post('rabbitmq_host'), 
        'port' => registry::post('rabbitmq_port'), 
        'user' => registry::post('rabbitmq_user'), 
        'pass' => registry::post('rabbitmq_pass')
    );

    // Update redis
    registry::$redis->hmset('config:rabbitmq', $vars);

    // User message
    template::add_message("Successuflly updated RabbitMQ connection info");

// Reset redis
} elseif (registry::$action == 'reset_redis') { 

    // Check
    if (strtolower(registry::post('redis_reset')) != 'reset') { 
        template::add_message("You did not enter RESET in the provided text box", 'error');
    } else { 

        // Go through packages
        $packages = DB::get_column("SELECT alias FROM internal_packages");
        foreach ($packages as $alias) { 
            $client = new Package($alias);
            $pkg = $client->load();

            if (!method_exists($pkg, 'redis_reset')) { continue; }
            $pkg->redis_reset();
        }

        // User message
        template::add_message("Successfully reset the redis database");
    }

}


// Get RabbitMQ info
if (!$rabbitmq_vars = registry::$redis->hgetall('config:rabbitmq')) { 
    $rabbitmq_vars = array(
        'host' => 'localhost', 
        'port' => '5672', 
        'user' => 'guest', 
        'pass' => 'guest'
    );
}

// Template variables
template::assign('rabbitmq', $rabbitmq_vars);


