<?php
declare(strict_types = 1);

namespace apex\core\lib;

use apex\DB;
use apex\core\lib\registry;
use apex\core\lib\message;
use apex\core\components;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/*** Handles all two-way RPC calls via RabbitMQ, including both 
* sending and receiving of messages.
*/
class rpc
{

    // Set properties
    private $connection = '';
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;
    private $workers = array();

/**
* Initialize.  Will start a new connection to RabbitMQ if one 
* does not already exist.
*/
public function initialize()
{

    // Debug
    debug::add(4, "Initializing connection to RabbitMQ for RPC calls", __FILE__, __LINE__);

    // Start connection
    $this->connection = new AMQPStreamConnection(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASS);
    $this->channel = $this->connection->channel();
    list($this->callback_queue, ,) = $this->channel->queue_declare('', false, false, true, false);

    // Consume
    $this->channel->basic_consume($this->callback_queue, '', false, false, false, false, array($this, 'onresponse'));

}

/**
* Process response.  Executed when RabbitMQ responds to 
* a previously sent RPC call, and.
*/
public function onresponse($response)
{

    // Check correlation ID
    if ($response->get('correlation_id') == $this->corr_id) {
        $this->response = $response->body;
    }

}

/**
* Send RPC command
*     @param string $action The action / routing key to send the RPC call to, formatted in standard Apex format (eg. users.profile.load).  See documentation for further details.
*      @param string $data The contents of the message to send via RPC.  Many times a JSON encoded string, but not always.
*     @return array Response from all packages that contain a worker for the action called.  Keys are the package alias, and value is the response from the worker method.
*/
public function send(string $action, $data)
{

    // Debug
    debug::add(4, fmsg("Sending RPC command to {1}", $action), __FILE__, __LINE__);

    // Check for all-in-one server
    if (registry::config('core:server_type') == 'all' || registry::config('core:server_type') == 'app') { 
        return message::call_worker($action, $data);
    }

    // Initialize
    if (!$this->connection) { $this->initialize(); }

    // Set variables
    $this->response = null;
    $this->corr_id = uniqid();
    $data = $action . "\n" . $data;

    // Define message
    $msg = new AMQPMessage(
        $data, 
        array(
            'correlation_id' => $this->corr_id,
            'reply_to' => $this->callback_queue
        )
    );

    // Publish message
    $this->channel->basic_publish($msg, '', 'rpc_queue');
    // Wait for response
    $this->channel->wait(false, false, 5);

    // Return
    return json_decode($this->response, true);
}

/**
* Listen for RPC messages.  This is executed by the /src/rpc_server.php script.
*/
public function receive()
{

    // Debug
    debug::add(1, "Beginning to listen for RPC calls", __FILE__, __LINE__);

    // Initialize
    if (!$this->connection) { $this->initialize(); }

    // Go through workers
    $rows = DB::query("SELECT package,alias,value FROM internal_components WHERE type = 'worker'");
    foreach ($rows as $row) { 

        // Load worker
        if (!$worker = components::load('worker', $row['alias'], $row['package'], '')) { 
            continue;
        }

        // Add to workers
        $msg_alias = $row['value'];
        if (!isset($this->workers[$msg_alias])) { $this->workers[$msg_alias] = array(); }
        array_push($this->workers[$msg_alias], array($row['package'], $worker));
    }

    // Connect to RabbitMQ
    $connection = new AMQPStreamConnection(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASS);
    $channel = $connection->channel();

    // Define exchange and queue
    $channel->queue_declare('rpc_queue', false, false, false, false);

    // Define callback function
    $callback = function ($request) {

    // Check
        $body = $request->body;

        // Parse message body
        list($action, $data) = explode("\n", $body, 2);
        if (!preg_match("/^(.+?)\.(.+?)\.(.+)$/", $action, $match)) { 
            debug::add(1, fmsg("Invalid RPC call made to arouting key: {1}", $action), __FILE__, __LINE__, 'alert'); 
        }

        // Set variables
        $msg_alias = $match[1] . '.' . $match[2];
        $func_name = $match[3];
        if (!isset($this->workers[$msg_alias])) { 
            debug::add(1, fmsg("No workers exist for RPC call to the routing key: {1}", $action), __FILE__, __LINE__, 'warning');
            $tmp_workers = array();
        } else { 
            $tmp_workers = $this->workers[$msg_alias];
        }

        // Go through workers
        $response = array();
        foreach ($tmp_workers as $vars) { 

            // Set variables
            $package = $vars[0];
            $worker = $vars[1];

            // Execute RPC call, if method exists
            if (method_exists($worker, $func_name)) { 
                debug::add(5, fmsg("Executing single RPC call to routing key: {1} for the package: {2}", $action, $package), __FILE__, __LINE__);
                $response[$package] = $worker->$func_name($data);
                debug::add(5, fmsg("Completed execution of single RPC call to routing key: {1} for the package: {2}", $action, $package), __FILE__, __LINE__);
            }
        }

        // Define response message
        $msg = new AMQPMessage(json_encode($response), array('correlation_id' => $request->get('correlation_id')));

        // Send response
        $request->delivery_info['channel']->basic_publish($msg, '', $request->get('reply_to'));
        $request->delivery_info['channel']->basic_ack($request->delivery_info['delivery_tag']);
    };

    // Start listening for messages
    $channel->basic_qos(null, 1, null);
    $channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);
    while (count($channel->callbacks)) {
        $channel->wait();
    }

    // Close channel
    $channel->close();
    $connection->close();


}

}

