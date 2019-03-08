<?php
declare(strict_types = 1);

namespace apex\core\controller\notifications;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;
use apex\core\components;

class system 
{

    // Properties
    public $display_name = 'System Notifications';

    // Set fields
    public $fields = array(
        'action' => array('field' => 'select', 'data_source' => 'hash:core:notify_system_actions', 'label' => 'Action')
    );

    // Senders
    public $senders = array(
        'admin' => 'Administrator' 
    );

    // Recipients
    public $recipients = array(
        'user' => 'User'		
    );

/**
* Get available merge fields.  Used when creating notification via admin panel.
*/
public function get_merge_fields():array 
{

    // Set fields
    $fields = array(
        'Profile' => array(
            'username' => 'Username', 
            'full_name' => 'Full Name', 
            'email' => 'E-Mail'
        ), 
        '2FA Variablies' => array(
            '2fa-url' => 'URL', 
            '2fa-ip_address' => 'IP Address', 
            '2fa-user_agent' => 'User Agent'
        )
    );

    // Return
    return $fields;
}

/** Get merge variables
*/
public function get_merge_vars(int $userid, array $data):array
{

    // Load user
    $user = new user($userid);
    $profile = $user->load(true, true);

    // Return
    return $profile;

}


}


