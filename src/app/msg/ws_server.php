<?php
declare(strict_types = 1);

namespace apex\app\msg;

use apex\app;
use apex\services\redis;
use apex\app\web\ajax;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


/**
 * Handles the internet web socket server, and listens for messages.  For more 
 * information, refer to the developer documenation. 
 */
class ws_server implements MessageComponentInterface
{




    // Set properties
    protected $clients;

/**
 * Construct 
 */
public function __construct()
{ 
    $this->clients = new \SplObjectStorage;
}

/**
 * Queue a new connection to WS server 
 */
public function onOpen(ConnectionInterface $conn)
{ 
    $this->clients->attach($conn);
}

/**
 * When a message is received from the web browser to the web socket server 
 */
public function onMessage(ConnectionInterface $from, $msg)
{ 

    // Check for authentication
    if (preg_match("/^ApexAuth: (.+)/", $msg, $match)) { 
        $this->authenticate($match[1], $from);
        return true;

    // Check for online chat
    } elseif (preg_match("/^chat--/i", $msg)) { 
        $this->add_chat($msg);
    return true;
    }

    // Decode JSON
    $vars = json_decode($msg, true);

    // Relay message to all clients, if needed
    $this->clients->rewind();
    while ($this->clients->valid()) { 

        // Get client info
        $client = $this->clients->current();
        $user = $this->clients->getinfo();

        // Check if authenticated user
        $ok = true;
        if ($client == $from) { $ok = false; }
        if (!is_array($user)) { $ok = false; }
        if (!isset($user['area'])) { $ok = false; }
        if (!isset($user['userid'])) { $ok = false; }
        if (!isset($user['type'])) { $ok = false; }

        // Skip, if needed
        $chk_recipient = $user['type'] . ':' . $user['userid'];
        if ($vars['area'] != '' && $vars['area'] != $user['area']) { $ok = false; }
        if ($vars['route'] != '' && $vars['route'] != $user['route']) { $ok = false; }
        if (count($vars['recipients']) > 0 && !in_array($chk_recipient, $vars['recipients'])) { $ok = false; }

        // Send message
        if ($ok === true) { 
            $client->send($msg);
        }

        // Next client
        $this->clients->next();
    }


}

/**
 * Add chat 
 */
protected function add_chat($line)
{ 

    // Parse line
    $vars = explode("--", $line, 4);

    // AJAX
    $ajax = new ajax();
    $ajax->alert("<b>$vars[2]</b> $vars[3]");

    // Set response
    $response = array(
        'status' => 'ok',
        'actions' => $ajax->results
    );
    $msg = json_encode($response);

    // Relay message to all clients, if needed
    $this->clients->rewind();
    while ($this->clients->valid()) { 

        // Get client info
        $client = $this->clients->current();
        $user = $this->clients->getinfo();

        // Check if authenticated user
        $ok = true;
        if ($client == $from) { $ok = false; }
        if (!is_array($user)) { $ok = false; }
        if (!isset($user['userid'])) { $ok = false; }
        if (!isset($user['type'])) { $ok = false; }

        // Skip, if needed
        $chk_recipient = $user['type'] . ':' . $user['userid'];
        if ($chk != 'admin:1') { $ok = false; }

        // Send message
        if ($ok === true) { 
            $client->send($msg);
        }

        // Next client
        $this->clients->next();
    }

}

/**
 * Authenticate the user 
 */
protected function authenticate(string $auth_string, ConnectionInterface $from)
{ 

    // Explode auth string
    list($area, $route, $auth_hash) = explode(":", $auth_string, 3);
    $user = array(
        'area' => $area,
        'route' => $route
    );

    // Check redis database for auth session
    if ($vars = redis::hgetall($auth_hash)) { 
        $user['type'] = $vars['type'];
        $user['userid'] = $vars['userid'];
    } elseif (preg_match("/^public:(.+)$/", $auth_hash, $match)) { 
        $user['type'] = 'public';
        $user['userid'] = $match[1];
    }

    // Set user info
    $this->clients->rewind();
    while ($this->clients->valid()) { 
        $conn = $this->clients->current();
        if ($conn == $from) { 
            $this->clients->setinfo($user);
            break;
        }
        $this->clients->next();
    }

    // Return
    return true;

}

/**
 * on close
 */
public function onClose(ConnectionInterface $conn) {
    $this->clients->detach($conn);
}
/**
 * onerror
 */
public function onError(ConnectionInterface $conn, \Exception $e) {
    echo "An error has occurred: {$e->getMessage()}\n";
    $conn->close();

}

}

