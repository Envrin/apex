<?php
declare(strict_types = 1);

namespace apex\core\test;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\template;
use apex\test;
use apex\core\forms;

class test_admin_panel extends \apex\test
{

/**
* setUp
*/
public                      function setUp()
{
    $this->admin_username = 'envrin';
    $this->admin_password = 'white4882';
    $this->cookie = array();

    $cookie_name = COOKIE_NAME . '_admin_auth_hash';
    if (registry::$auth_hash != '') { 
        $this->cookie[$cookie_name] = registry::$auth_hash;
    }

    // Get db / email slave servers
    $this->dbslaves = registry::$redis->lrange('config:db_slaves', 0, -1);
    $this->email_servers = registry::$redis->lrange('config:email_servers', 0, -1);
    $this->rabbitmq = registry::$redis->hgetall('config:rabbitmq');




}

/*
* tearDown
*/
public function tearDown()
{

    // Reset db slave servers
    registry::$redis->del('config:db_slaves');
    foreach ($this->dbslaves as $row) { 
        registry::$redis->rpush('config:db_slaves', $row); 
    }

    // Reset e-mail servers
    registry::$redis->del('config:email_servers');
    foreach ($this->email_servers as $data) { 
        registry::$redis->rpush('config:email_servers', $data);
    }

    // RabbitMQ
    registry::$redis->hmset('config:rabbitmq', $this->rabbitmq);




}

/** 
* Login form
*/
public function test_login()
{

    // Get login form
    $html = registry::test_request('/admin');
    $this->assertpagetitle('Login Now');

    // Login
    $vars = array(
        'username' => $this->admin_username, 
        'password' => $this->admin_password, 
        'submit' => 'login'
    );
    $html = registry::test_request('/admin/login', 'POST', $vars);
    $this->assertpagetitlecontains("Welcome");

    // Ensure we logged in
    if (registry::$auth_hash == '') { 
        trigger_error("Unable to login to administration panel, further tests aborted.", E_USER_ERROR);
    }

}

/**
* Settings->General Settings page
*/
public function test_page_settings_general()
{

    // Check if page loads
    $html = registry::test_request('/admin/settings/general', 'GET', array(), array(), $this->cookie);
    $this->assertpagetitle('General Settings');

    // Get current config vars
    $orig_config = registry::getall_config();

    // Set general settings vars
    $general_vars = array(
        'domain_name' => 'unit-test.com', 
        'date_format' => 'Y-m-d H:i:s',  
        'nexmo_api_key' => 'utest_nexmo_api_key',  
        'nexmo_api_secret' => 'utest_nexmo_secret', 
        'recaptcha_site_key' => 'utest_recaptcha_set_key',  
        'recaptcha_secret_key' => 'utest_recaptcha_secret',  
        'openexchange_app_id' => 'test_openexchange_api_key',  
        'default_language' => 'es',  
        'default_timezone' => 'MST', 
        'log_level' => 'utest,info,degub',  
        'debug_level' => 3,
        'mode' => 'prod', 
        'submit' => 'update_general' 
    );

    // Update general settings
    $html = registry::test_request('/admin/settings/general', 'POST', $general_vars, array(), $this->cookie);
    $this->assertpagecontains('Successfully updated general settings');
    $this->asserthasusermessage('success', "updated general settings");

    // Check config vars
    foreach ($general_vars as $key => $value) {
        if ($key == 'submit') { continue; }
 
        $chk = registry::$redis->hget('config', 'core:' . $key);
        $this->assertequals($chk, $value, tr("General Settings error.  Unable to update convig variable '%s' to '%s'", $key, $value));
        registry::update_config_var('core:' . $key, (string) $orig_config['core:' . $key]);
    }


    // Set site info vars
    $siteinfo_vars = array(
        'site_name' => 'Apex Unit Test', 
        'site_address' => '555 Burrard Street',  
        'site_address2' => 'Chicago, IL 930256',  
        'site_email' => 'support@unit-test.com',  
        'site_phone' => '582-666-3251',  
        'site_tagline' => 'Unit tests are here to stay',  
        'site_facebook' => 'unit_fb',  
        'site_twitter' => 'unit_twit',  
        'site_linkedin' => 'unit_linked',  
        'site_instagram' =>'unit_instagram', 
        'submit' => 'site_info' 
    );

    // Update site info vars
    $html = registry::test_request('/admin/settings/general', 'POST', $siteinfo_vars, array(), $this->cookie);
    $this->assertpagecontains('Successfully updated site info settings');
    $this->asserthasusermessage('success', "updated site info settings");

    // Check config vars
    foreach ($siteinfo_vars as $key => $value) {
        if ($key == 'submit') { continue; }
        $chk = registry::$redis->hget('config', 'core:' . $key);
        $this->assertequals($chk, $value, tr("General Settings error.  Unable to update convig variable '%s' to '%s'", $key, $value));
        registry::update_config_var('core:' . $key, (string) $orig_config['core:' . $key]);
    }

    // Set security vars
    $security_vars = array(
        'session_expire_mins' => 45,  
        'password_retries_allowed' => 8, 
        'require_2fa' => 2,   
        'num_security_questions' => 9, 
        'session_retain_logs_period' => 'W', 
        'session_retain_logs_num' => '1', 
        'force_password_reset_time_period' => 'D', 
        'force_password_reset_time_num' => '90',  
        'submit' => 'security'
    );

    // Update security vars
    $html = registry::test_request('/admin/settings/general', 'POST', $security_vars, array(), $this->cookie);
    $this->assertpagecontains('Successfully updated admin panel security settings');
    $this->asserthasusermessage('success', "updated admin panel security");

    // Modify vars as needed
    unset($security_vars['session_retain_logs_period']);
    unset($security_vars['session_retain_logs_num']);
    unset($security_vars['force_password_reset_time_period']);
    unset($security_vars['force_password_reset_time_num']);
    $security_vars['session_retain_logs'] = 'W1';
    $security_vars['force_password_reset_time'] = 'D90';

    // Check config vars
    foreach ($security_vars as $key => $value) {
        if ($key == 'submit') { continue; }
        $chk = registry::$redis->hget('config', 'core:' . $key);
        $this->assertequals($chk, $value, tr("General Settings error.  Unable to update convig variable '%s' to '%s'", $key, $value));
        registry::update_config_var('core:' . $key, (string) $orig_config['core:' . $key]);
    }

    // Get existingt database info
    $db = registry::$redis->hgetall('config:db_master');
    registry::$redis->del('config:db_slaves');

    // Set add db server vars
    $vars = array(
        'dbname' => $db['dbname'], 
        'dbuser' => $db['dbuser'], 
        'dbpass' => $db['dbpass'], 
        'dbhost' => $db['dbhost'], 
        'dbport' => $db['dbport'], 
        'submit' => 'add_database'
    );

    // Add database servers
    for ($x = 1; $x <= 3; $x++) { 
        $vars['dbuser'] = 'slave' . $x;
        $html = registry::test_request('/admin/settings/general', 'POST', $vars, array(), $this->cookie);
        $this->assertpagecontains('Successfully added new database server');
        $this->asserthasusermessage('success', 'added new database server');
    }

    // Check count of slave servers
    $count = registry::$redis->llen('config:db_slaves');
    $this->assertequals(3, $count, "Did not create 3 slave db servers");

    // Get rotation of db servers
    registry::$redis->hset('counters', 'db_server', 2);
    for ($x=1; $x <= 6; $x++) { 
        $chk_x = $x > 3 ? ($x - 3) : $x;
        $vars = registry::get_db_server();
        $this->assertequals('slave' . $chk_x, $vars['dbuser'], "Rotating slave servers did not work on loop $x");
    }

    // Get write connection
    $vars = registry::get_db_server('write');
    $this->assertequals($vars['dbuser'], $db['dbuser'], "Unable to retrieve master db info");

    // Get update db server page
    $html = registry::test_request('/admin/settings/general_db_manage', 'GET', array(), array('server_id' => 1), $this->cookie);
    $this->assertpagetitle('Manage Database Server');

    // Set vars to update database server
    $vars = json_decode(registry::$redis->lindex('config:db_slaves', 1), true);
    $vars['dbuser'] = 'unit_test';
    $vars['submit'] = 'update_database';
    $vars['server_id'] = 1;

    // Update database
    $html = registry::test_request('/admin/settings/general', 'POST', $vars, array(), $this->cookie);
    $this->assertpagecontains('Successfully updated database server');
    $this->asserthasusermessage('success', 'Successfully updated database server');

    // Verify database server was updated
    $vars = json_decode(registry::$redis->lindex('config:db_slaves', 1), true);
    $this->assertequals($vars['dbuser'], 'unit_test', "Unable to update slave db server");

    // Delete DB serversw
    $vars = array(
        'db_server_id' => array(0, 2), 
        'submit' => 'delete_database'
    );
    $html = registry::test_request('/admin/settings/general', 'POST', $vars, array(), $this->cookie);
    $this->assertpagecontains('Successfully deleted checked database servers');
    $this->asserthasusermessage('success', 'Successfully deleted checked database servers');

    // Check remaining slave server
    $vars = json_decode(registry::$redis->lindex('config:db_slaves', 0), true);
    $this->assertequals($vars['dbuser'], 'unit_test', "Deleting slave servers didn't work");

    // Set SMTP server vars
    $vars = array(
        'email_is_ssl' => 1, 
        'email_host' => 'mail.envrin.com', 
        'email_user' => 'email1', 
        'email_pass' => 'mypassword', 
        'email_port' => 25, 
        'submit' => 'add_email'
    );
    registry::$redis->del('config:email_servers');
    for ($x=1; $x <= 3; $x++) { 
        $vars['email_user'] = 'email' . $x;
        $html = registry::test_request('/admin/settings/general', 'POST', $vars, array(), $this->cookie);
        $this->assertpagecontains('Successfully added new SMTP e-mail server');
        $this->asserthasusermessage('success', 'Successfully added new SMTP e-mail server');
    }

    // Check number of e-mail servers
    $count = registry::$redis->llen('config:email_servers');
    $this->assertequals($count, 3, "Unable to create 3 e-mail servers");

    // Delete e-mail servers
    $vars = array(
        'email_server_id' => array(0, 2), 
        'submit' => 'delete_email'
    );
    $html = registry::test_request('/admin/settings/general', 'POST', $vars, array(), $this->cookie);
    $this->assertpagecontains('Successfully deleted all checked e-mail SMTP servers');
    $this->asserthasusermessage('success', 'Successfully deleted all checked e-mail SMTP servers');

    // Ensure e-mail servers deleted correctly
    $vars = json_decode(registry::$redis->lindex('config:email_servers', 0), true);
    $this->assertequals($vars['username'], 'email2', "Unable to correctly delete SMTP e-mail servers");

    // Update RabbitMQ info
    $vars = array(
        'rabbitmq_host' => '10.0.1.6', 
        'rabbitmq_port' => '8311', 
        'rabbitmq_user' => 'unit_test', 
        'rabbitmq_pass' => 'mypassword', 
        'submit' => 'update_rabbitmq'
    );
    $html = registry::test_request('/admin/settings/general', 'POST', $vars, array(), $this->cookie);
    $this->assertpagecontains('Successuflly updated RabbitMQ connection info');
    $this->asserthasusermessage('success', 'Successuflly updated RabbitMQ connection info');

    // Verify RabbitMQ data updates
    $user = registry::$redis->hget('config:rabbitmq', 'user');
    $this->assertequals($user, 'unit_test', "Unable to update RabbitMQ info");

    // Reset redis -- validation error
    $vars = array(
        'redis_reset' => 'no', 
        'submit' => 'reset_redis'
    );
    $html = registry::test_request('/admin/settings/general', 'POST', $vars, array(), $this->cookie);
    $this->assertpagecontains('You did not enter RESET in the provided text box');
    $this->asserthasusermessage('error', 'You did not enter RESET in the provided text box');

    // Reset redis
    $vars['redis_reset'] = 'reset';
    $html = registry::test_request('/admin/settings/general', 'POST', $vars, array(), $this->cookie);
    $this->assertpagecontains('Successfully reset the redis database');
    $this->asserthasusermessage('success', 'Successfully reset the redis database');

}

/**
* Create administrators
*     @dataProvider provider_create_admin
*/
public function test_create_admin(array $vars, string $error_type = '', string $field_name = '')
{

    // Send request
    $html = registry::test_request('/admin/settings/admin', 'POST', $vars, array(), $this->cookie);
if ($error_type == 'alphanum') { file_put_contents(SITE_PATH . '/public/error.html', $html); }
    if ($error_type != '') { 
        $this->asserthasformerror($error_type, $field_name);
    } else { 
        $this->asserthasusermessage('success', 'Successfully created new administrator, unit_test');
        $this->asserthasdbfield("SELECT * FROM admin WHERE username = 'unit_test'", 'email', 'unit@test.com');
    }

}

/**
* Provider -- Create administrator
*/
public function provider_create_admin()
{

    // Set legitimate vars
    $vars = array(
        'username' => 'unit_test', 
        'password' => 'mypassword123', 
        'confirm-password' => 'mypassword123', 
        'full_name' => 'Unit Test', 
        'email' => 'unit@test.com', 
        'phone_country' => '1', 
        'phone' => '5551234567', 
    'require_2fa' => '0', 
        'language' => 'en', 
        'timezone' => 'PST',
        'question1' => 'q1', 
        'question2' => 'q2', 
        'question3' => 'q3', 
        'answer1' => 'unit1', 
        'answer2' => 'unit2', 
        'answer3' => 'unit3', 
        'submit' => 'create'
    );

    // Set requests
    $results = array(
        array($vars, 'blank', 'username'), 
        array($vars, 'blank', 'password'), 
        array($vars, 'blank', 'full_name'), 
        array($vars, 'blank', 'email'), 
        array($vars, 'blank', 'language'), 
        array($vars, 'blank', 'timezone'), 
        array($vars, 'blank', 'question1'), 
        array($vars, 'blank', 'answer1'), 
        array($vars, 'alphanum', 'username'), 
        array($vars, 'email', 'email'), 
        array($vars, '', '') 
    );

    // Add bogus variables
    $results[0][0]['username'] = '';
    $results[1][0]['password'] = '';
    $results[2][0]['full_name'] = '';
    $results[3][0]['email'] = '';
    $results[4][0]['language'] = '';
    $results[5][0]['timezone'] = '';
    $results[6][0]['question1'] = '';
    $results[7][0]['answer1'] = '';
    $results[8][0]['username'] = 'dkg$*d Agiu4%g';
    $results[9][0]['email'] = 'testing_email';

    // Return
    return $results;

}

/**
* Settings->Administrators page
*/
public function test_page_settings_admin()
{

    // Ensure page loads
    $html = registry::test_request('admin/settings/admin', 'GET', array(), array(), $this->cookie);
    $this->assertpagetitle('Administrators');
    $this->asserthastable('core:admin');
    $this->asserthassubmit('create', 'Create New Administrator');
    $this->asserthastablefield('core:admin', 2, 'unit_test');

    // Get admin ID
    $admin_id = DB::get_field("SELECT id FROM admin WHERE username = 'unit_test'");

    // Check manage admin page
    $html = registry::test_request('/admin/settings/admin_manage', 'GET', array(), array('admin_id' => $admin_id), $this->cookie);
    $this->assertpagetitle('Manage Administrator');
    $this->asserthassubmit('update', 'Update Administrator');

    // Set vars for update
    $vars = array(
        'username' => 'unit_test', 
        'password' => '', 
        'confirm-password' => '', 
        'full_name' => 'Unit Test', 
        'email' => 'update@test.com', 
        'phone_country' => '1', 
        'phone' => '5551234567', 
        'require_2fa' => '0', 
        'language' => 'en', 
        'timezone' => 'PST', 
        'submit' => 'update', 
        'admin_id' => $admin_id
    );

    // Update administrator
    $html = registry::test_request('/admin/settings/admin', 'POST', $vars, array(), $this->cookie);
    $this->asserthasusermessage('success', 'Successfully updated administrator details');
    $this->asserthasdbfield("SELECT * FROM admin WHERE username = 'unit_test'", 'email', 'update@test.com');

    // Set delete vars
    $vars = array(
        'table' => 'core:admin', 
        'id' => 'tbl_core_admin', 
        'admin_id' => array($admin_id)
    );

    // Send delete request
    $html = registry::test_request('/ajax/core/delete_rows', 'POST', $vars, array(), $this->cookie);
    $html = registry::test_request('/admin/settings/admin', 'GET', array(), array(), $this->cookie);
    $this->assertnothastablefield('core:admin', 1, 'unit_test');
    $this->assertnothasdbrow("SELECT * FROM admin WHERE username = 'unit_test'");

}

/**
* Notification menu
*/
public function test_page_admin_settings_notifications()
{

    // Ensure page loads
    $html = registry::test_request('/admin/settings/notifications', 'GET', array(), array(), $this->cookie);
    $this->assertpagetitle('Notifications');
    $this->asserthastable('core:notifications');
    $this->asserthassubmit('create', 'Create E-Mail Notification');

    // Send request to create e-mail notification
    $vars = array(
        'controller' => 'users', 
        'submit' => 'create'
    );
    $html = registry::test_request('/admin/settings/notifications_create', 'POST', $vars, array(), $this->cookie);
    $this->assertpagetitle('Create Notification');
    $this->asserthasformfield('subject');
    $this->asserthasformfield('recipient');
    $this->asserthasformfield('cond_action');

    // Get administrator
    $admin_id = DB::get_field("SELECT id FROM admin ORDER BY id LIMIT 0,1");

    // Set variables to create notification
    $vars = array(
        'controller' => 'users', 
        'sender' => 'admin:' . $admin_id, 
        'recipient' => 'user', 
        'cond_action' => 'create', 
        'cond_status' => 'active', 
        'cond_group_id' => '', 
        'content_type' => 'text/plain', 
        'subject' => 'Test Subject 123', 
        'contents' => 'This is a test message.', 
        'submit' => 'create'
    );

    // Create notification
    $html = registry::test_request('/admin/settings/notifications', 'POST', $vars, array(), $this->cookie);
    $this->asserthasusermessage('success', 'Successfully added new e-mail notification');
    $this->asserthastablefield('core:notifications', 4, 'Test Subject 123');

    // Get notification ID
    $notification_id = DB::get_field("SELECT id FROM notifications WHERE subject = 'Test Subject 123'");
    $this->assertnotfalse($notification_id, "Unable to find e-mail notification in database");

    // Get edit notification page
    $html = registry::test_request('/admin/settings/notifications_edit', 'GET', array(), array('notification_id' => $notification_id), $this->cookie);
    $this->assertpagetitle('Edit Notification');
    $this->asserthasformfield('subject');
    $this->asserthasformfield('cond_action');
    $this->asserthasformfield('recipient');
    $this->asserthassubmit('edit', 'Edit E-Mail Notification');

    // Set update vars
    $vars['subject'] = 'Update Test';
    $vars['submit'] = 'edit';
    $vars['notification_id'] = $notification_id;

    // Edit notification
    $html = registry::test_request('/admin/settings/notifications', 'POST', $vars, array(), $this->cookie);
    $this->assertpagetitle('Notifications');
    $this->asserthasusermessage('success', 'Successfully updated the e-mail notification');
    $this->asserthastablefield('core:notifications', 4, 'Update Test');
    $this->asserthasdbfield("SELECT * FROM notifications WHERE id = $notification_id", 'subject', 'Update Test');

    // Set vars to delete notification
    $vars = array(
    'table' => 'core:notifications', 
    'id' => 'tbl_core_notifications', 
    'notification_id' => array($notification_id)
    );

    // Delete notification
    $html = registry::test_request('/ajax/core/delete_rows', 'POST', $vars, array(), $this->cookie);
    $html = registry::test_request('/admin/settings/notifications', 'GET', array(), array(), $this->cookie);
    $this->assertpagetitle('Notifications');
    $this->asserthastable('core:notifications');
    $this->assertnothastablefield('core:notifications', 4, 'Update Test');

    // Ensure notification is deleted
    $row = DB::get_row("SELECT * FROM notifications WHERE id = %i", $notification_id);
    $this->assertfalse($row, "Unable to delete the e-mail notification");

}




}

