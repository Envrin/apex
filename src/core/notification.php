<?php
declare(strict_types = 1);

namespace apex\core;

use apex\DB;
use apex\registry;
use apex\core\components;
use apex\core\forms;

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

    // Set profile fields
    $profile_fields = array(
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

    // Start HTML
    $html = "<option value=\"\" style=\"font-weight: bold;\">User Profile</option>\n";
    foreach ($profile_fields as $key => $value) { 
        $html .= "\t      <option value=\"$key\">     $value</option>\n";
    }

    // Load controller
    if (!$client = components::load('controller', $controller, 'core', 'notifications')) { 
        trigger_error("Unable to load the notification controller, $controller", E_USER_ERROR);
    }

    // Get fields
    $fields = $client->get_merge_fields();

    // GO through fields
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
* Get a sender / recipient name and e-mail address.
*     @param string $sender The sender string (eg. admin:1, user, etc.)
*/
public function get_recipient(string $recipient, int $userid = 0)
{

    // Initialize
    $name = ''; $email = '';

    // Check for admin
    if (preg_match("/^admin:(\d+)$/", $recipient, $match) && $row = registry::$redis->hgetall($recipient)) { 
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
* is executed when creating a notification within the admin panel.
*/
public function create(array $data = array()) 
{ 

    // Perform checks
    if (!isset($data['controller'])) { trigger_error("No 'controller' variable defined when creating e-mail notification.", E_USER_ERROR); } 
    elseif (!isset($data['sender'])) { trigger_error("No 'sender' variable defined while trying to create e-mail notification.", E_USER_ERROR); }
    elseif (!isset($data['recipient'])) { trigger_error("No 'recipient' variable defined while creating e-mail notification.", E_USER_ERROR); }

    // Load controller
    if (!$client = components::load('controller', $data['controller'], 'core', 'notifications')) {
        trigger_error("Notification controller '$data[controller]' does not exist.", E_USER_ERROR);
    }

    // Set variables
    $sender = $data['sender'];
    $recipient = $data['recipient'];
    $content_type = isset($data['content_type']) ? $data['content_type'] : 'text/plain';

    // Get condition
    $condition = array();
    foreach ($client ->fields as $field_name => $vars) { 
        $condition[$field_name] = registry::post('cond_' . $field_name);
    }

    // Add to DB
    DB::insert('notifications', array(
        'controller' => $data['controller'], 
        'sender' => $sender, 
        'recipient' => $recipient, 
        'content_type' => $content_type, 
        'subject' => $data['subject'], 
        'contents' => base64_encode($data['contents']), 
        'condition_vars' => base64_encode(json_encode($condition)))
    );
    $notification_id = DB::insert_id();

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
        trigger_error("Notification does not exist in database, ID# $notification_id", E_USER_ERROR);
    }

    // Load controller
    if (!$client = components::load('controller', $row['controller'], 'core', 'notifications')) {
        trigger_error("Unable to load the notification controller, $row[controller]", E_USER_ERROR);
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
        'content_type' => registry::post('content_type'), 
        'subject' => registry::post('subject'), 
        'contents' => base64_encode(registry::post('contents')), 
        'condition_vars' => base64_encode(json_encode($condition))), 
    "id = %i", $notification_id);

}

/**
* Delete notification
*/
public function delete($notification_id) 
{

    DB::query("DELETE FROM notifications WHERE id = %i", $notification_id);
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
    $rows = DB::query("SELECT id,controller,subject FROM notifications GROUP BY controller ORDER BY subject");
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


