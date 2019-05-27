<?php
declare(strict_types = 1);

namespace apex\core\controller\notifications;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\log;
use apex\core\lib\debug;
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
        'user' => 'User', 
        'admin' => 'Administrator'		
    );

/**
* Get available merge fields.  Used when creating notification via admin panel.
*/
public function get_merge_fields():array 
{

    // Set fields
    $fields = array();
    $fields['2FA Variablies'] = array(
        '2fa-url' => 'Authentication URL'
    );

    // Return
    return $fields;
}

/** 
* Get merge variables
*/
public function get_merge_vars(int $userid, array $data):array
{

    // Initialize
    $vars = array();

    // Get 2FA hash, if needed
    if (isset($data['2fa_hash'])) { 
        $vars['2fa-url'] = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $vars['2fa-url'] .= registry::config('core:domain_name') . '/auth2fa/' . $data['2fa_hash'];
    }

    // Return
    return $vars;

}

}

