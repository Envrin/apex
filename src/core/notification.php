<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\registry;
use apex\debug;
use apex\message;
use apex\ApexException;
use apex\CommException;
use apex\ComponentException;
use apex\core\components;
use apex\core\forms;
use apex\users\user;

/**
* Handles creating and managing the e-mail notifications 
* within the system, including obtaining the lists of senders / recipients / 
* available merge fields, and so on.
*/
class Notification 
{

/**
* Get available merge fields from a certain notification controller.
*      @param string $controller The notification controller to obtain merge fields of.
*     @return array All availalbe merge fields.
*     @return string The HTML options of all available merge fields
*/
public function get_merge_fields(string $controller):string
{

    // Debug
    debug::add(5, fmsg("Obtaining merge fields for notification controller, {1}", $controller), __FILE__, __LINE__);

    // Set systems fields
    $fields = array();
    $fields['System'] = array(
        'site_name' => 'Site Name', 
        'domain_name' => 'Domain Name', 
        'install_url' => 'Install URL', 
        'current_date' => 'Current Date', 
        'current_time' => 'Current Time', 
        'ip_address' => 'IP Address', 
        'user_agent' => 'User Agent'
    );

    // Set profile fields
    $fields['User Profile'] = array(
        'id' => 'ID', 
        'username' => '$username', 
        'first_name' => 'First Name', 
        'last_name' => 'Last Name', 
        'full_name' => 'Full Name', 
        'email' => 'E-Mail Address', 
        'phone' => 'Phone Number', 
        'group' => 'User Group', 
        'status' => 'Status', 
        'language' => 'Language', 
        'timezone' => 'Timezone', 
        'country' => 'Country', 
        'reg_ip' => 'Registration IP', 
        'date_created' => 'Date Created'
    );

    // Load controller
    if (!$client = components::load('controller', $controller, 'core', 'notifications')) { 
        throw new ComponentException('no_load', 'controlelr', '', $controller, 'core', 'notifications');
    }

    // Get fields
    if (method_exists($client, 'get_merge_fields')) { 
        $tmp_fields = $client->get_merge_fields();
        $fields = array_merge($fields, $tmp_fields);
    }

    // GO through fields
    $html = '';
    foreach ($fields as $name => $vars) { 
        $html .= "<option value=\"\" style=\"font-weight: bold;\">$name</option>\n";

        // Go through variables
        foreach ($vars as $key => $value) { 
            $html .= "\t      <Option value=\"$key\">$value</option>\n";
        }
    }

    // Return
    return $html;

}

/**
* Get mrege variables for an e-mail notification
*     @param string $controller The e-mail notification controller
*     @param int $userid The ID# of the user, if appropriate
*      @param array $data Any additional data passed when processing the e-mail notifications
*      @return array Returns a key-value pair of all merge variables
*/
public function get_merge_vars(string $controller, int $userid = 0, array $data = array()):array
{

    // Debug
    debug::add(5, fmsg("Obtaining merge ariables for e-mail notification controller {1}, userid: {2}", $controller, $userid), __FILE__, __LINE__);

    // Get install URL
    $url = isset($_SERVER['_HTTPS']) ? 'https://' : 'http://';
    $url .= registry::config('core:domain_name');

    // Set system variables
    $date = date('Y-m-d H:i:s');
    $vars = array(
        'site_name' => registry::config('core:site_name'), 
        'domain_name' => registry::config('core:domain_name'), 
        'install_url' => $url, 
        'current_date' => fdate($date), 
        'current_time' => fdate($date, true), 
        'ip_address' => registry::$ip_address, 
        'user_agent' => registry::$user_agent
    );

    // Get user profile, fi needed
    if ($userid > 0) { 
        $user = new user($userid);
        $profile = $user->load(false, true);

        foreach ($profile as $key => $value) { 
            $vars[$key] = $value;
        }
    }

    // Load controller
    if (!$client = components::load('controller', $controller, 'core', 'notifications')) { 
        throw new ComponentException('no_load', 'controller', '', $controller, 'core', 'notifications');
    }

    // Get vars from controller, if available
    if (method_exists($client, 'get_merge_vars')) { 
        $tmp_vars = $client->get_merge_vars($userid, $data);
        $vars = array_merge($vars, $tmp_vars);
    }

    // Return
    return $vars;

}

/**
* Get a sender / recipient name and e-mail address.
*     @param string $sender The sender string (eg. admin:1, user, etc.)
*/
public function get_recipient(string $recipient, int $userid = 0)
{

    // Debug
    debug::add(5, fmsg("Getting e-mail recipient / sender, {1}, userid: {2}", $recipient, $userid), __FILE__, __LINE__);

    // Initialize
    $name = ''; $email = '';

    // Check for admin
    if (preg_match("/^admin:(\d+)$/", $recipient, $match) && $row = DB::get_idrow('admin', $match[1])) { 
        $name = $row['full_name'];
        $email = $row['email'];

    // Check for user
    } elseif ($recipient == 'user') { 

        $user = new user($userid);
        $profile = $user->load(false, true);

        $name = $profile['full_name'];
        $email = $profile['email'];

    }

    // Check if no user found
    if ($email == '') { return false; }

    // Return
    return array($email, $name);

}

/**
* Send a single notification
*     @param int $userid The ID# of the user the e-mail is being sent against
*     @param int $notification The ID# of the notification to send
*     @param array $data The $data array passed to the message::process_emails() function
*/
public function send($userid, $notification_id, $data)
{

    // Get notification
    if (!$row = DB::get_idrow('notifications', $notification_id)) { 
        throw new CommException('not_exists', '', '', '', $notification_id);
    }
    $condition = json_decode(base64_decode($row['condition_vars']), true);

    // Load controller
    $controller = components::load('controller', $row['controller'], 'core', 'notifications');

    // Get sender info
    if ((!method_exists($controller, 'get_recipient')) || (!list($from_email, $from_name) = $controller->get_recipient($row['sender'], $userid, $data))) { 
        if (!list($from_email, $from_name) = $this->get_recipient($row['sender'], $userid)) { 
            throw new CommException('no_sender', '', '', $row['sender']);
        }
    }

    // Change recipient for 2FA
    if ($row['controller'] == 'system' && isset($condition['action']) && $condition['action'] == '2fa') { 
        $row['recipient'] = 'admin:1';
    }

    // Get recipient info
    if ((!method_exists($controller, 'get_recipient')) || (!list($to_email, $to_name) = $controller->get_recipient($row['recipient'], $userid, $data))) { 
        if (!list($to_email, $to_name) = $this->get_recipient($row['recipient'], $userid)) { 
            throw new CommException('no_recipient', '', '', $row['recipient']);
        }
    }

    // Set variables
    $reply_to = $controller->reply_to ?? $row['reply_to'];
    $cc = $controller->cc ?? $row['cc'];
        $bcc = $controller->bcc ?? $row['bcc'];

    // Get merge variables
    $merge_vars = $this->get_merge_vars($row['controller'], $userid, $data);

    // Format message
    $subject = $row['subject']; $message = base64_decode($row['contents']);
    foreach ($merge_vars as $key => $value) { 
        if (is_array($value)) { continue; }
        $subject = str_replace("~$key~", $value, $subject);
        $message = str_replace("~$key~", $value, $message);
    }

    // Send e-mail
    message::send_email($to_email, $to_name, $from_email, $from_name, $subject, $message, $row['content_type'], $reply_to, $cc, $bcc);

    // Return
    return true;

}

/**
* is executed when creating a notification within the admin panel.
*/
public function create(array $data = array()) 
{ 

    // Perform checks
    if (!isset($data['controller'])) { 
        throw new ApexException('error', "No notification controller was defined upon creating e-mail notification");
    } elseif (!isset($data['sender'])) { 
        throw new ApexException('error', "No sender variable defined when trying to create e-mail notification");
    } elseif (!isset($data['recipient'])) { 
        throw new ApexException('error', "No recipient variable defined when creating e-mail notification");
    }

    // Load controller
    if (!$client = components::load('controller', $data['controller'], 'core', 'notifications')) {
        throw new ComponentException('no_load', 'controller', '', $data['controller'], 'core', 'notifications');
    }

    // Get condition
    $condition = array();
    foreach ($client ->fields as $field_name => $vars) { 
        $condition[$field_name] = $data['cond_' . $field_name];
    }

    // Add to DB
    DB::insert('notifications', array(
        'controller' => $data['controller'], 
        'sender' => $data['sender'], 
        'recipient' => $data['recipient'],
        'reply_to' => ($data['reply_to'] ?? ''),  
        'cc' => ($data['cc'] ?? ''), 
        'bcc' => ($data['bcc'] ?? ''), 
        'content_type' => $data['content_type'], 
        'subject' => $data['subject'], 
        'contents' => base64_encode($data['contents']), 
        'condition_vars' => base64_encode(json_encode($condition)))
    );
    $notification_id = DB::insert_id();

    // Debug
    debug::add(1, fmsg("Created new e-mail notification within settings, subject: {1}", $data['subject']), __FILE__, __LINE__, 'info');

    // Add attachments as needed
    $x=1;
    while (1) { 
        if (!list($filename, $mime_type, $contents) = forms::get_uploaded_file('attachment' . $x)) { break; }

        // Add to DB
        DB::insert('notifications_attachments', array(
            'notification_id' => $notification_id, 
        'mime_type' => $mime_type, 
            'filename' => $filename, 
            'contents' => base64_encode($contents))
        );

    $x++; }

    // Debug
    debug::add(1, fmsg("Created new e-mail notification with subject, {1}", $data['subject']), __FILE__, __LINE__);

    // Return
    return $notification_id;

}

/**
* Edit notification
*     @param int $notification_id The ID# of the notification to edit
*/
public function edit($notification_id)
{

    // Get row
    if (!$row = DB::get_idrow('notifications', $notification_id)) { 
        throw new CommException('not_exists', '', '', $notification_id);
    }

    // Load controller
    if (!$client = components::load('controller', $row['controller'], 'core', 'notifications')) {
        throw new ComponentException('no_load', 'controller', '', $row['controller'], 'core', 'notifications');
    }

    // Get condition
    $condition = array();
    foreach ($client ->fields as $field_name => $vars) { 
        $condition[$field_name] = registry::post('cond_' . $field_name);
    }

    // Updatte database
    DB::update('notifications', array(
        'sender' => registry::post('sender'), 
        'recipient' => registry::post('recipient'),
        'reply_to' => registry::post('reply_to'), 
        'cc' => registry::post('cc'), 
        'bcc' => registry::post('bcc'), 
        'content_type' => registry::post('content_type'), 
        'subject' => registry::post('subject'), 
        'contents' => base64_encode(registry::post('contents')), 
        'condition_vars' => base64_encode(json_encode($condition))), 
    "id = %i", $notification_id);

    // Debug
    debug::add(1, fmsg("Updated e-mail notification with subject, {1}", registry::post('subject')), __FILE__, __LINE__);

}

/**
* Delete notification
*/
public function delete($notification_id) 
{

    DB::query("DELETE FROM notifications WHERE id = %i", $notification_id);
    debug::add(1, fmsg("Deleted e-mail notification, ID: {1}", $notification_id), __FILE__, __LINE__);
    return true;

}

/**
* Create select list options of all e-mail notifications in the database.
*     @param string $selected The selected notification
*     @return string The HTML code of all options
*/
public function create_options($selected = ''):string
{

    // Start options
    $options = '<option value="custom">Send Custom Message</option>';

    // Go through notifications
    $last_controller = '';
    $rows = DB::query("SELECT id,controller,subject FROM notifications ORDER BY controller,subject");
    foreach ($rows as $row) {

        // Load controller, if needed
        if ($last_controller != $row['controller']) { 
            $controller = components::load('controller', $row['controller'], 'core', 'notifications');
            $name = $controller->display_name ?? ucwords($row['controller']);

            if ($last_controller != '') { $options .= "</optgroup>"; }
            $options .= "<optgroup name=\"$name\">";
            $last_controller = $row['controller'];
        }

    // Add to options
        $chk = $selected == $row['id'] ? 'selected="selected"' : '';
        $options .= "<option value=\"$row[id]\">ID# $row[id] - $row[subject]</option>";
    }

    // Return
    return $options;

}

/**
* Add a mass e-mailing to the queue
*     @param string $type The type of notification.  Must be either 'email' or 'sms'
*     @param string $controller The controller to user to gather the recipients.  Defaults to 'users'
*     @param string $message The contents of the message to send
*     @param string $subject The subject of the e-mail message
*     @param string $from_name The sender name of the e-mail message
*     @param string $from_email The sender e-mail address of the e-mail message
*     @param string $reply_to The reply-to e-mail address of the e-mail message
*     @param array $condition An array containing the filter criteria defining which users to broadcast to.
*/
public function add_mass_queue(string $type, string $controller, string $message, string $subject = '', string $from_name = '', string $from_email = '', string $reply_to = '', array $condition = array())
{

    // Add to database
    DB::insert('notifications_mass_queue', array(
        'type' => $type, 
        'controller' => $controller, 
        'from_name' => $from_name, 
        'from_email' => $from_email, 
        'reply_to' => $reply_to, 
        'subject' => $subject, 
        'message' => $message, 
        'condition_vars' => json_encode($condition))
    );

    // Return
    return true;

}


}


