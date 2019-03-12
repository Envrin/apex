<?php

namespace apex;

use apex\pkg\package;

class pkg_core
{

// Set package variables
public $version = '1.0.6';
public $access = 'public';
public $name = 'Core Framework';
public $description = 'The core package of the framework, and is required for all installations of the software.';

// Define configuration
public function __construct() { 

// Config variables
$this->config = array(
    'cron_pid' => 0,
    'worker_pid' => 0, 
    'rpc_pid' => 0, 
    'websocket_pid' => 0, 
    'mode' => 'devel', 
    'debug' => 0, 
    'date_format' => 'F j, Y', 
    'start_year' => date('Y'),
    'db_driver' => 'mysql',  
    'server_type' => '', 
    'theme_admin' => 'limitless', 
    'theme_public' => 'public_coco', 
    'site_name' => 'My Company Name', 
    'site_address' => '', 
    'site_address2' => '', 
    'site_email' => '', 
    'site_phone' => '',
    'site_tagline' => '', 
    'site_facebook' => '', 
    'site_twitter' => '', 
    'site_linkedin' => '', 
    'site_instagram' => '', 
    'domain_name' => '', 
    'session_expire_mins' => 60,  
    'session_retain_logs' => 'W2', 
    'password_retries_allowed' => 5, 
    'require_2fa' => 0, 
    'num_security_questions' => 3, 
    'force_password_reset_time' => '', 
    'nexmo_api_key' => '', 
    'nexmo_api_secret' => '', 
    'recaptcha_site_key' => '', 
    'recaptcha_secret_key' => '', 
    'openexchange_app_id' => '', 
    'default_timezone' => 'PST', 
    'default_language' => 'en', 
    'log_level' => 'notice,error,critical,alert,emergency', 
    'debug_level' => 0, 
    'cookie_name' => 'K9dAmgkd4Uaf', 
    'backups_enable' => 1, 
    'backups_save_locally' => 1, 
    'backups_db_interval' => 'H3', 
    'backups_full_interval' => 'D1', 
    'backups_retain_length' => 'W1', 
    'backups_remote_service' => 'none', 
    'backups_aws_access_key' => '', 
    'backups_aws_access_secret' => '', 
    'backups_dropbox_client_id' => '', 
    'backups_dropbox_client_secret' => '', 
    'backups_dropbox_access_token' => '', 
    'backups_gdrive_client_id' => '', 
    'backups_gdrive_client_secret' => '', 
    'backups_gdrive_refresh_token' => '', 
    'backups_next_db' => 0, 
    'backups_next_full' => 0
);

// Hashes
$this->hash = $this->define_hashes();

// Public menus
$this->menus = array();
$this->menus[] = array(
    'area' => 'public', 
    'position' => 'top', 
    'menus' => array(
        'index'=> 'Home', 
        'about' => 'About Us', 
        'register' => 'Sign Up', 
        'login' => 'Login', 
        'contact' => 'Contact Us'
    )
);

// Admin menu -- Setup header
$this->menus[] = array(
    'area' => 'admin', 
    'position' => 'top', 
    'type' => 'header', 
    'alias' => 'hdr_setup', 
    'name' => 'Setup'
);

// Admin menus -- Setup
$this->menus[] = array(
    'area' => 'admin', 
    'position' => 'after hdr_setup', 
    'type' => 'parent', 
    'icon' => 'fa fa-fw fa-cog', 
    'alias' => 'settings', 
    'name' => 'Settings', 
    'menus' => array(
        'general' => 'General', 
        'admin' => 'Administrators', 
        'notifications' => 'Notifications'
    )
);

// Admin - Maintenance menu
$this->menus[] = array(
    'area' => 'admin', 
    'position' => 'after settings', 
    'type' => 'parent', 
    'icon' => 'fa fa-fw fa-wrench', 
    'alias' => 'maintenance', 
    'name' => 'Maintenance', 
    'menus' => array(
        'package_manager' => 'Package Manager', 
        'theme_manager' => 'Theme Manager', 
        'backup_manager' => 'Backup Manager', 
        'cron_manager' => 'Cron Manager' 
    )
);

// Menu -- header -- Site
$this->menus[] = array(
    'area' => 'admin', 
    'position' => 'after hdr_setup', 
    'type' => 'header', 
    'alias' => 'hdr_site', 
    'name' => 'Site'
);

// Menus -- CMS
$this->menus[] = array(
    'area' => 'admin', 
    'position' => 'after hdr_site', 
    'icon' => 'fa fa-fw fa-pagelines', 
    'type' => 'parent', 
    'alias' => 'cms', 
    'name' => 'CMS', 
    'menus' => array(
        'menus' => 'Menus', 
        'pages' => 'Titles / Layouts', 
    'placeholders' => 'Placeholders'
    )
);

// Menus - Reports
$this->menus[] = array(
    'area' => 'admin', 
    'position' => 'after cms', 
    'type' => 'parent', 
    'icon' => 'fa fa-fw fa-bar-chart', 
    'alias' => 'reports', 
    'name' => 'Reports', 
    'menus' => array()
); 




    // External files
$this->ext_files = array(		
    'apex.php',
    'composer.json',  
    'License.txt', 
    'Readme.md', 
    'data/index.html', 
    'data/backups/index.html', 
    'lib/ajax.php',
    'lib/apex_cli.php',  
    'lib/auth.php', 
    'lib/debug.php', 
    'lib/encrypt.php', 
    'lib/functions.php', 
    'lib/html_tags.php', 
    'lib/installer.php', 
    'lib/log.php', 
    'lib/log_channel.php', 
    'lib/message.php', 
    'lib/network.php', 
    'lib/registry.php', 
    'lib/rpc.php', 
    'lib/template.php', 
    'lib/test.php', 
    'lib/wsbot.php', 
    'lib/abstracts/autosuggest.php', 
    'lib/abstracts/cron.php', 
    'lib/abstracts/form.php', 
    'lib/abstracts/htmlfunc.php', 
    'lib/abstracts/modal.php', 
    'lib/abstracts/tabcontrol.php', 
    'lib/abstracts/table.php', 
    'lib/abstracts/tabpage.php', 
    'lib/db/mysql.php',
    'lib/exceptions/ApexException.php',
    'lib/exceptions/CommException.php', 
    'lib/exceptions/ComponentException.php', 
    'lib/exceptions/DBException.php', 
    'lib/exceptions/EncryptException.php', 
    'lib/exceptions/FormException.php', 
    'lib/exceptions/IOException.php', 
    'lib/exceptions/MiscException.php', 
    'lib/exceptions/PackageException.php', 
    'lib/exceptions/RepoException.php', 
    'lib/exceptions/ThemeException.php', 
    'lib/exceptions/UpgradeException.php', 
    'lib/exceptions/UserException.php', 

    'lib/pkg/package.php', 
    'lib/pkg/package_config.php', 
    'lib/pkg/pkg_component.php', 
    'lib/pkg/theme.php', 
    'lib/pkg/upgrade.php',  
    'lib/third_party/maxmind/*', 
    'lib/third_party/SqlParser.php', 
    'public/plugins/flags/*', 
    'public/plugins/parsley.js/*', 
    'public/plugins/sounds/notify.wav', 
    'public/index.php', 
    'src/apex',
    'src/apex_worker',  
    'src/autoload.php', 
    'src/cron.php', 
    'src/load.php', 
    'src/rpc_server.php', 
    'src/worker.php', 
    'src/ws_server.php'
);



}


////////////////////////////////////////////////////////////
// Define hashes
////////////////////////////////////////////////////////////

public function define_hashes() { 

    $vars = array();

    // Backup remote services
    $vars['backups_remote_services'] = array(
        'none' => 'None / Do Not Backup Remotely', 
        'aws' => 'Amazon Web Services', 
        'dropbox' => 'Dropbox', 
        'google_drive' => 'Google Drive', 
        'tarsnap' => 'Tarsnap'
    );


    // Server mode
    $vars['server_mode'] = array(
        'devel' => 'Development', 
        'prod' => 'Production'
    );

    // CMS - menu areas
    $vars['cms_menus_area'] = array(
        'public' => 'Public Site', 
        'members' => 'Member Area'
    );

    // CMS menu types
    $vars['cms_menus_types'] = array(
        'internal' => 'Internal Page', 
        'external' => 'External URL', 
        'parent' => 'Parent Menu', 
        'header' => 'Header / Seperator'
    );

    $vars['log_levels'] = array(
        'info,warning,notice, error,critical,alert,emergency' => 'All Levels', 
        'notice, error,critical,alert,emergency' => 'All Levels, except INFO and NOTICE',
        'error,critical,alert,emergency' => 'Only Errors', 
        'none' => 'No Logging'
    );

    $vars['debug_levels'] = array(
        0 => '0 - No Debugging', 
        1 => '1 - Very Limited', 
        2 => '2 - Limited', 
        3 => '3 - Medium', 
        4 => '4 - Extensive', 
        5 => '5 - Very Extensive'
    );


    // Boolean
    $vars['boolean'] = array(
        '1' => 'Yes',
        '0' => 'No'
    );

    // Time intervals
    $vars['time_intervals'] = array(
        'I' => 'Minute', 
        'H' => 'Hour', 
        'D' => 'Day', 
        'W' => 'Week', 
        'M' => 'Month', 
        'Y' => 'Year'
    );

    // Date formats
    $vars['date_formats'] = array(
        'F j, Y' => 'March 21, 2019', 
        'M-d-Y' => 'Mar-21-209', 
        'm/d/Y' => '3/21/2019', 
        'd/m/Y' => '21/3/2019', 
        'd-M-Y' => '21-Mar-2019' 
    );

    // Form fields
    $vars['form_fields'] = array(
        'textbix' => 'Textbox', 
        'textarea' => 'Textarea', 
        'select' => 'Select List', 
        'radio' => 'Radio List', 
        'checkbox' => 'Checkbox List', 
        'boolean' => 'Boolean (yes/no)'
    );

    // Secondary secure questions
    $vars['secondary_security_questions'] = ARRAy(
        'q1' => "What was your childhood nickname?", 
        'q2' => "In what city did you meet your spouse/significant other?", 
        'q3' => "What is the name of your favorite childhood friend?", 
        'q4' => "What street did you live on in third grade?", 
        'q5' => "What is your oldest sibling?s birthday month and year? (e.g., January 1900)", 
        'q6' => "What is the middle name of your oldest child?", 
        'q7' => "What is your oldest siblings middle name?", 
        'q8' => "What school did you attend for sixth grade?", 
        'q9' => "What was your childhood phone number including area code? (e.g., 000-000-0000)", 
        'q10' => "What is your oldest cousins first and last name?", 
        'q11' => "What was the name of your first stuffed animal?", 
        'q12' => "In what city or town did your mother and father meet?", 
        'q13' => "Where were you when you had your first kiss?", 
        'q14' => "What is the first name of the boy or girl that you first kissed?", 
        'q15' => "What was the last name of your third grade teacher?", 
        'q16' => "In what city does your nearest sibling live?", 
        'q17' => "What is your oldest brothers birthday month and year? (e.g., January 1900)", 
        'q18' => "What is your maternal grandmothers maiden name?", 
        'q19' => "In what city or town was your first job?", 
        'q20' => "What is the name of the place your wedding reception was held?", 
        'q21' => "What is the name of a college you applied to but didnt attend?" 
    );

    // System notification actions
    $vars['notify_system_actions'] = array(
        '2fa' => 'Two Factor Authentication (2FA)'
    );

    // Notification content type
    $vars['notification_content_type'] = array(
        'text/plain' => 'Plain Text', 
        'text/html' => 'HTML'
    );

    // Base currencies
    $vars['base_currencies'] = array(
        'AUD' => 'Australian Dollar (AUD)', 
        'BRL' => 'Brazilian Real (BRL)', 
        'GBP' => 'British Pound (GBP)', 
        'CAD' => 'Canadian Dollar (CAD)', 
        'CLP' => 'Chilean Peso (CLP)', 
        'CNY' => 'Chinese Yuan (CNY)', 
        'CZK' => 'Czech Koruna (CZK)', 
        'DKK' => 'Danish Krone (DKK)', 
        'EUR' => 'Euro (EUR)', 
        'HKD' => 'Hong Kong Dollar (HKD)', 
        'HUF' => 'Hungarian Forint (HUF)', 
        'INR' => 'Indian Rupee (INR)', 
        'IDR' => 'Indonesian Rupiah (IDR)', 
        'ILS' => 'Israeli New Shekel (ILS)', 
        'JPY' => 'Japanese Yen (JPY)', 
        'MYR' => 'Malaysian Ringgit (MYR)', 
        'MXN' => 'Mexican Peso (MXN)', 
        'NZD' => 'New Zealand Dollar (NZD)', 
        'NOK' => 'Norwegian Krone (NOK)', 
        'PKR' => 'Pakistani Rupee (PKR)', 
        'PHP' => 'Philippine Peso (PHP)', 
        'PLN' => 'Polish Zloty (PLN)', 
        'RUB' => 'Russian Ruble (RUB)', 
        'SGD' => 'Singapore Dollar (SGD)', 
        'ZAR' => 'South African Rand (ZAR)', 
        'KRW' => 'South Korean Won (KRW)', 
        'SEK' => 'Swedish Krona (SEK)', 
        'CHF' => 'Swiss Franc (CHF)', 
        'TWD' => 'Taiwan Dollar (TWD)', 
        'THB' => 'Thailand Baht (THB)', 
        'TRY' => 'Turkish Lira (TYR)', 
    );

    // Return
    return $vars;


}


////////////////////////////////////////////////////////////
// Install After
////////////////////////////////////////////////////////////

public function install_after() 
{

    // Delete keys from redis
    registry::$redis->del('std:language');
    registry::$redis->del('std:currency');
    registry::$redis->del('std:timezone');
    registry::$redis->del('std:country');

    $lines = file(SITE_PATH . '/etc/core/stdlists');
    foreach ($lines as $line) { 
        $vars = explode("::", base64_decode(trim($line)));

        if ($vars[0] == 'currency') { 
            $line = implode("::", array($vars[2], $vars[4], $vars[5]));
        registry::$redis->hset('std:currency', $vars[1], $line); 

        } elseif ($vars[0] == 'timezone') {  
        $line = implode("::", array($vars[2], $vars[3], $vars[4]));
            registry::$redis->hset('std:timezone', $vars[1], $line); 

        } elseif ($vars[0] == 'country') { 
            $line = implode("::", array($vars[2], $vars[3], $vars[4], $vars[5], $vars[6], $vars[7]));
            registry::$redis->hset('std:country', $vars[1], $line); 

        } elseif ($vars[0] == 'language') { 
            registry::$redis->hset('std:language', $vars[1], $vars[2]); 
        }

    }

    // Active languages
    registry::$redis->lpush('config:language', 'en');

}



////////////////////////////////////////////////////////////
// Reset
////////////////////////////////////////////////////////////

public function reset() { } 


////////////////////////////////////////////////////////////
// Remove
////////////////////////////////////////////////////////////

public function remove() { }

/**
* Reset the redis database
*/
public function reset_redis()
{

    // Delete needed keys
    registry::$redis->del('config:components');
    registry::$redis->del('config:components_package');
    registry::$redis->del('hash');
    registry::$redis->del('cms:titles');
    registry::$redis->del('cms:layouts');
    registry::$redis->del('cms:placeholders');

    // Go through all components
    $rows = DB::query("SELECT * FROM internal_components");
    foreach ($rows as $row) {

        // Add to components
        $line = implode(":", array($row['type'], $row['package'], $row['parent'], $row['alias']));
        registry::$redis->sadd('config:components', $line);

        // Add to components_package
        $chk = $row['type'] . ':' . $row['alias'];
        if ($value = registry::$redis->hget('config:components_package', $chk)) { 
            registry::$redis->hset('config:components_package', $chk, 2);
        } else { 
            registry::$redis->hset('config:components_package', $chk, $row['package']);
        }

        // Process hash, if needed
        if ($row['type'] == 'hash') {
            $hash_alias = $row['package'] . ':' . $row['alias']; 
            $vars = DB::get_hash("SELECT alias,value FROM internal_components WHERE type = 'hash_var' AND parent = %s AND package = %s", $row['alias'], $row['package']);
            registry::$redis->hset('hash', $hash_alias, json_encode($vars));
        }
    }

    // GO through CMS pages
    $rows = DB::query("SELECT * FROM cms_pages");
    foreach ($rows as $row) { 
        $key = $row['area'] . '/' . $row['filename'];
        registry::$redis->hset('cms:titles', $key, $row['title']);
        registry::$redis->hset('cms:layouts', $key, $row['layout']);
    }

    // CMS placeholders
    $rows = DB::query("SELECT * FROM cms_placeholders WHERE contents != ''");
    foreach ($rows as $row) { 
        $key = $row['uri'] . ':' . $row['alias'];
        registry::$redis->hset('cms_placeholders', $key, $row['contents']);
    }

    // CMS menus
    $pkg = new package('core');
    $pkg->update_redis_menus();

    // Setup stdlists again
    $this->install_after();


}
}

