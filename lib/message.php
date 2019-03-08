<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\registry;
use apex\email;
use apex\core\components;
use apex\core\notification;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
* Handles all one-way messaging between the software and RabbitMQ.  This is for one-way messages
* only, and not RPC calls.  Handles both, sending and receiving of messages
*/
class message 
{

    public static $workers = array();

/**
* Sends a new one-way message to RabbitMQ
*     @param string $routing_key The routing key to send the message to, used to specify which PHP class and method(s) should be executed (eg. users.profile.create).  See developer documentation for further details.
*     @param string $data The data to send, generally a JSON encoded string, but not required.
*/

public static function send(string $routing_key, string $data)
{

    // Debug
    debug::add(5, fmsg("Sending one-time message to RabbitMQ, routing key: {1}", $routing_key), __FILE__, __LINE__);

    // Check for all-in-one server
    if (registry::config('core:server_type') == 'all' || registry::config('core:server_type') == 'app') { 
        return self::call_worker($routing_key, $data);
    }

    $connection = new AMQPStreamConnection(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASS);
    $channel = $connection->channel();

    $channel->exchange_declare('apex', 'topic', false, false, false);

    $msg = new AMQPMessage(
        $data 
        //array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    );
    $channel->basic_publish($msg, 'apex', $routing_key);

    $channel->close();
    $connection->close();

    // Debug
    debug::add(3, fmsg("Sent one-time message to RabbitMQ, routing key: {1}", $routing_key), __FILE__, __LINE__);

}
/**
* Receive messages from RabbitMQ.  This is executed by the /src/worker.php script, 
* begins listening to incoming messages from RabbitMQ, and executes the necessary 
* PHP code as messages come in.
*/
public static function receive() 
{

    // Debug
    debug::add(1, "Initializing start up of receiving one-way messages from RabbitMQ", __FILE__, __LINE__);

    // Go through workers
    $rows = DB::query("SELECT package,alias,value FROM internal_components WHERE type = 'worker'");
    foreach ($rows as $row) { 

        // Load worker
        if (!$worker = components::load('worker', $row['alias'], $row['package'], '')) { 
            continue;
        }

        // Add to workers
        $msg_alias = $row['value'];
        if (!isset(self::$workers[$msg_alias])) { self::$workers[$msg_alias] = array(); }
        array_push(self::$workers[$msg_alias], $worker);
    }

    // Debug
    debug::add(2, "Finished loading all workers to receive one-way messages from RabbitMQ", __FILE__, __LINE__);

    // Connect to RabbitMQ
    $connection = new AMQPStreamConnection(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASS);
    $channel = $connection->channel();

    // Debug
    debug::add(4, "Successfully connected to RabbitMQ", __FILE__, __LINE__);

    // Define exchange and queue
    $channel->exchange_declare('apex', 'topic', false, false, false);
    list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

    // Bind to keys
    foreach (array_keys(self::$workers) as $binding_key) {
        $binding_key .= '.*';
        echo "Blinding to: $binding_key\n";
        $channel->queue_bind($queue_name, 'apex', $binding_key);

        // Debug
        debug::add(3, fmsg("Binded to key {1} for incoming RabbitMQ messages", $binding_key), __FILE__, __LINE__);
    }

    // Define callback message
    $callback = function ($msg) {

        // Debug
        debug::add(4, fmsg("Received one-way message from RabbitMQ, routing key: {1}", $msg->delivery_info['routing_key']), __FILE__, __LINE__);

        // Get routing key and worker
        list($package, $type, $action) = explode(".", $msg->delivery_info['routing_key']);
        $msg_type = $package . '.' . $type;

        // Go through workers
        $response = true;
        foreach (self::$workers[$msg_type] as $worker) { 

            // Check method
            if (method_exists($worker, $action)) { 
                $response = $worker->$action($msg->body);
            } elseif (method_exists($worker, 'receive')) { 
                $response = $worker->receive($msg->body);
            }
        }

        // Achknowledge message
        if ($response === true) { 
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            debug::add(4, fmsg("Executed and acknowledged one-way message from RabbitMQ, routing key: {1}", $msg->delivery_info['routing_key']), __FILE__, __LINE__);
        }
    };

    // Debug
    debug::add(1, "Starting to listen for one-way messages from RabbitMQ", __FILE__, __LINE__); 

    // Listen for messages
    $channel->basic_qos(null, 1, null);
    $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

    while (count($channel->callbacks)) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();

}

/**
* Processes any necessary notifications.  Takes in the type of 
* notifications, and additional variables.  Checks each notification
* against the condition, and sends any that match.
*     @param string $controller The notification controller / type alias of which to check.
*     @param int $userid The user ID# for which notifications are being processed against.
*     @param array $conditiom Associative array containing details on the current request, and is checked against the condition notifications were created with.
*     @param array $data Associatve array that is passed to the notification controller, and contains any additional information to retrieve merge variables (eg. transaction ID#, support ticket ID#, etc.)
*/
public static function process_emails(string $controller, int $userid = 0, array $condition = array(), array $data = array()) 
{

    // Debug
    debug::add(3, fmsg("Checking e-mail notifications for controller {1}, user ID# {2}", $controller, $userid), __FILE__, __LINE__);

    // Check for notifications
    $rows = DB::query("SELECT * FROM notifications WHERE controller = %s ORDER BY id", $controller);
    foreach ($rows as $row) { 

        // Get conditions
        $ok = true;
        $chk_condition = unserialize(base64_decode($row['condition_vars'])); 
        foreach ($chk_condition as $key => $value) { 
            if (!isset($condition[$key])) { continue; }
            if ($condition[$key] == '') { continue; }
            if ($value != $condition[$key]) { $ok = false; break; }
        }
        if ($ok === false) { continue; }

        // Load controller
        $controller = components::load('controller', $controller, 'core', 'notifications');
        $client = new notification();

        // Get sender info
        if (!list($from_email, $from_name) = $client->get_recipient($row['sender'], $userid)) { 
            if (!list($from_email, $from_name) = $controller->get_sender($row['sender'], $userid)) { 
                trigger_error(tr("Unable to determine sender e-mail information for sender, %s", $row['sender']), E_USER_ERROR);
            }
        }

        // Get recipient info
        if (!list($to_email, $to_name) = $client->get_recipient($row['recipient'], $userid)) { 
            if (!list($to_email, $to_name) = $controller->get_recipient($row['recipient'], $userid)) { 
                trigger_error(tr("Unable to determine recipient e-mail information for recipient, %s", $row['recipient']), E_USER_ERROR);
            }
        }

        // Set variables
        $reply_to = $controller->reply_to ?? '';
        $cc = $controller->cc ?? '';
        $bcc = $controller->bcc ?? '';

        // Get merge variables
        $merge_vars = $controller->get_merge_vars($userid, $data);

        // Format message
        $subject = $row['subject']; $message = $row['contents'];
        foreach ($merge_vars as $key => $value) { 
            $subject = str_replace("~$key~", $value, $subject);
            $message = str_replace("~$key~", $value, $message);
        }

    // Send e-mail
    self::send_email($to_email, $to_name, $from_email, $from_name, $subject, $message, $row['content_type'], $reply_to, $cc, $bcc);

    }

}

/**
* Send a single e-mail message via rotating SMTP servers.
*     @param string $to_email E-mail address of the recipient
*      @param string $to_name Full name of the recipient
*      @param string $from_email E-mail address of the sender
*     @param string $from_name Full name of the sender
*     @param string $subject The subject of the e-mail message
*     @param string $message The contents of the e-mail message to send
*     @param string $content_type Optional, and the content type of the e-mail message.  Defaults to "text/plain"
*     @param string $reply_to Optional, the the Reply-To e-mail address
*     @param string $cc Optional, and the e-mail address to CC the message to
*     @param string $bcc Optional, and the e-mail address to BCC the message to
*     @param array $attachments Optional, and associative array of file attachments to include, keys are the filename and value is contents of file
*/
public static function send_email(string $to_email, string $to_name, string $from_email, string $from_name, string $subject, string $message, string $content_type = 'text/plain', string $reply_to = '', string $cc = '', string $bcc = '', array $attachments = array())
{

    // Debug
    debug::add(4, fmsg("Sending e-mail message to {1} from {2} with subject: {3}", $to_email, $from_email, $subject), __FILE__, __LINE__);

    // Set vars
    $vars = array(
        'to_email' => $to_email, 
        'to_name' => $to_name, 
        'from_email' => $from_email, 
        'from_name' => $from_name, 
        'subject' => $subject, 
        'message' => $message, 
        'content_type' => $content_type
    );

    // Add reply-to, cc, bcc
    if ($reply_to != '') { $vars['reply_to'] = $reply_ti; }
    if ($cc != '') { $vars['cc'] = $cc; }
    if ($bcc != '') { $vars['bcc'] = $bcc; }

    // Add attachments
    foreach ($attachments as $filename => $contents) { 
        if (!isset($vars['attachments'])) { $vars['attachments'] = array(); }
        $vars['attachments'][$filename] = $contents;
    }

    // Send RabbitMQ message
    self::send('core.notify.send_email', json_encode($vars));

}

/** 
* Send SMS message via Nexmo
*     @param string $phone The number number to send to including country code
*     @param string $message The SMS message to send
*/
public static function send_sms(string $phone, string $message)
{

    // Debug
    debug::add(3, fmsg("Sending SMS message to phone number {1}", $phone), __FILE__, __LINE__);

    // Set request
    $vars = array(
        'phone' => $phone, 
        'message' => $message
    );

    // Send message to Rabbit MQ
    self::send('core.notify.send_sms', json_encode($vars));

}

/**
* Send message to internal Web Socket server, which is passed to necessary user's web 
* browsers, and allows DOM elements within the page to be updated in real-time (eg. new notification, etc.)
*     @param ajax $ajax Object of the \apex\ajax class, containing the actions to execute within the web browser
*     @param array $recipients List of individual recipients to relay message to.  See documentation for details.
*     @param string $area Optional, and the area to broadcast message to (eg. admin, members, etc.)
*     @param string $route Optional, and the exact URI to relay message to 
*/
public static function send_ws($ajax, array $recipients = array(), string $area = '', string $route = '')
{

    // Debug
    debug::add(3, fmsg("Sending message to Web Socket server, area {1}, route: {2}", $area, $route), __FILE__, __LINE__);

    // Set data
    $data = array(
        'status' => 'ok', 
        'actions' => $ajax->results, 
        'recipients' => $recipients, 
        'area' => $area, 
        'route' => trim($route, '/')
    );

    // Send WS message
    self::send('core.notify.send_ws', json_encode($data));

}

/**
* Add new notification alert
*      @param string $recipient The recipient of the alert in standard format (eg. user:915, admin:3, etc.)
*     @param string $message The body of the alert message
*     @param string $url The URL to link the drop-down notification to
*/
public static function add_dropdown_alert(string $recipient, string $message, string $url)
{

    // Debug
    debug::add(3, fmsg("Adding notification / alert via Web Socket to recipient: {1}", $recipient), __FILE__, __LINE__);

    // Set vars
    $vars = array(
        'message' => $message, 
        'url' => $url, 
        'time' => time()
    );

    // Get redis key
    $redis_key = 'alerts:' . $recipient;
    registry::$redis->lpush($redis_key, json_encode($vars));
    registry::$redis->ltrim($redis_key, 0, 9);
    $unread_alerts = registry::$redis->hincrby('unread:alerts', $recipient, 1);

    // Get HTML of dropdown item
    $comp_file = SITE_PATH . '/themes/' . registry::$theme . '/components/dropdown_alert.tpl';
    if (file_exists($comp_file)) { 
        $html = file_get_contents($comp_file);
    } else { $html = '<li><a href="~url~"><p>~message~<br /><i style="font-size: small">~time~</i><br /></p></a></li>'; }

    // Merge variables
    $html = str_replace("~url~", $url, $html);
    $html = str_replace("~message~", $message, $html);
    $html = str_replace("~time~", 'Just Now', $html);

    // AJAX
    $ajax = new ajax();
    $ajax->prepend('dropdown_alerts', $html);
    $ajax->set_text('badge_unread_alerts', $unread_alerts);
    $ajax->set_display('badge_unread_alerts', 'block');
    $ajax->play_sound('notify.wav');

    // Send WS message
    self::send_ws($ajax, array($recipient));

}

/**
* Add new dropdown notification message
*      @param string $recipient The recipient of the alert in standard format (eg. user:915, admin:3, etc.)
*     @param string $message The body of the alert message
*     @param string $url The URL to link the drop-down notification to
*/
public static function add_dropdown_message(string $recipient, string $from, string $message, string $url)
{

    // Debug
    debug::add(3, fmsg("Adding dropdown notification via Web Socket to recipient: {1}", $recipient), __FILE__, __LINE__);

    // Set vars
    $vars = array(
        'from' => $from, 
        'message' => $message, 
        'url' => $url, 
        'time' => time()
    );

    // Get recipient
    $recipients = array();
    if ($recipient == 'admin') { 
        $admin_ids = DB::get_column("SELECT id FROM admin");
        foreach ($admin_ids as $admin_id) { $recipients[] = 'admin:' . $admin_id; }
    } else { $recipients[] = $recipient; }

    // Get redis key
    foreach ($recipients as $recipient) { 
        $redis_key = 'messages:' . $recipient;
        registry::$redis->lpush($redis_key, json_encode($vars));
        registry::$redis->ltrim($redis_key, 0, 9);
        $unread_messages = registry::$redis->hincrby('unread:messages', $recipient, 1);
    }

    // Get HTML of dropdown item
    $comp_file = SITE_PATH . '/themes/' . registry::$theme . '/components/dropdown_message.tpl';
    if (file_exists($comp_file)) { 
        $html = file_get_contents($comp_file);
    } else { $html = '<li><a href="~url~"><p><b>~from~</b><br />~message~<br /><i style="font-size: small">~time~</i><br /></p></a></li>'; }

    // Merge variables
    $html = str_replace("~from~", $from, $html);
    $html = str_replace("~url~", $url, $html);
    $html = str_replace("~message~", $message, $html);
    $html = str_replace("~time~", 'Just Now', $html);

    // AJAX
    $ajax = new ajax();
    $ajax->prepend('dropdown_messages', $html);
    $ajax->set_text('badge_unread_messages', $unread_messages);
    $ajax->set_display('badge_unread_messages', 'block');
    $ajax->play_sound('notify.wav');

    // Send WS message
    self::send_ws($ajax, $recipients);

}

/**
* Call a worker.  Used for server types of 'all-in-one', as instead of routing the message 
* via RabbitMQ, the appropriate method within the worker PHP class is 
* immediately executed.
*      @param string $routing_key The routing key of the message
*     @param string $data The message contents
*/
public static function call_worker(string $routing_key, $data)
{

    // Debug
    debug::add(5, fmsg("Executing one-way message to RabbitMQ for all-in-one server instead of relaying to RabbitMQ, routing key: {1}", $routing_key), __FILE__, __LINE__);

    // Init
    $response = array();

    // Parse key
    $key = explode(".", $routing_key);
    $chk_key = $key[0] . '.' . $key[1];

    // Go through workers
    $rows = DB::query("SELECT * FROM internal_components WHERE type = 'worker' AND value = %s", $chk_key);
    foreach ($rows as $row) { 

        // Load components
        if (!$worker = components::load('worker', $row['alias'], $row['package'])) { continue; }
        if (!method_exists($worker, $key[2])) { continue; }

        // Execute method
        $func_name = $key[2];
        $response[$row['package']] = $worker->$func_name($data);
    }

    // Return
    return $response;

}

}

