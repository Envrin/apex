<?php
declare(strict_types = 1);

namespace apex\app\msg;

use apex\app;
use apex\services\debug;
use apex\services\msg;
use apex\app\msg\objects\event_message;
use apex\app\msg\objects\websocket_message;
use apex\app\interfaces\msg\WebSocketMessageInterface;


/**
 * Handles all web socket functionality, such as dispatching messages to 
 * connected browsers. 
 */
class websocket
{


/**
 * Send message via Web Socket. 
 *
 * Send message to internal Web Socket server, which is passed to necessary 
 * user's web browsers, and allows DOM elements within the page to be updated 
 * in real-time (eg. new notification, etc.) 
 *
 * @param ajax $ajax Object of the \apex\ajax class, containing the actions to execute within the web browser
 * @param array $recipients List of individual recipients to relay message to.  See documentation for details.
 * @param string $area Optional, and the area to broadcast message to (eg. admin, members, etc.)
 * @param string $uri Optional, and the exact URI to relay message to
 */
public function dispatch(WebSocketMessageInterface $msg)
{ 

    // Debug
    debug::add(3, fmsg("Sending message to Web Socket server, area {1}, uri: {2}", $msg->get_area(), $msg->get_uri()), __FILE__, __LINE__);

    // Send WS message
    $msg = new event_message('core.notify.send_ws', $msg->get_json());
    $msg->set_type('direct');
    msg::dispatch($msg);

}


}

