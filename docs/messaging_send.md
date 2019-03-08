
# Sending Messages

You can send either, one-way or two-way RPC messages with Apex.  If you haven't already, please read the [Workers and Routing Keys](components/workers.md) page, as it will explain how messages are routed and received.

**NOTE:** Upon installation you will be prompted for which type of server is being configured.  If you select the default "All-in-One" option, instead of sending these messages via RabbitMQ, 
Apex will instantly execute the appropriate method within the PHP worker class, since everything is on one server.


## One-Way Messages `message::send(string $routing_key, string $data)`

**Description:** Sends a message to RabbitMQ, which then queues the message and evenly distributes it to the back-end application servers.  One-way messages are very quick and efficient, as the front-end web server only gets an acknowledgement that the message was received, but no actual response from the back-end application servers.  This is useful for any processes that may take a little time (eg. user registration, transaction processing, etc.) as if volume increases, you can easily add more back-end application servers to handle the volume.

**NOTE:** within the worker PHP classes that process the incoming messages, they need to return a boolean of true or false.  An acknowledgement that the message has been successfully received will only be sent to RabbitMQ if a true is returned by the method within the worker PHP class.

**Parameters**

Variable | Type | Description
------------- |------------- |-------------
`$routing_key` | string | The routing key for RabbitMQ, formatted in *string.string.string* (eg. users.profile.create).  This will route the message to the appropriate workers and methods within them.
`$data` | string | Any string of data, many times will be JSON encoded.  This is the data received by the worker PHP class to perform the actual process.  For example, upon user registration, this contains a JSON encoded array of all profile fields.  The contents of this variable are up to you / the developer who added the message, so if needed refer to the developer documentation of the package you're integrating with to know what this variable should contain.

**Example:**

~~~php
namespace apex;

use apex\message;

$data = array(); // Some array of data
message::send('loans.applications.add', json_encode($data));
~~~

The above will look for any workers with the routing key alias of *loans.applications*, load the necessary PHP class, and execute the `add()` method within the class.


### Two-Way RPC Calls

These are used for any intensive operations that may take a couple seconds to complete with a large database and high volume, as they provide the front-end web servers a response from the back-end application servers, whereas one-way 
messages only provide an acknowledgement the message was received.  For example, when viewing a user's previous transaction history a RPC call is used, as it could take a couple seconds to retrieve the data from the database, hence with 
high volume you can distribute the load to multiple back-end application servers.

### `$response = $rpc_client->send($routing_key, $data);`

**Description:** Uses the *apex\rpc* library to send RPC calls via RabbitMQ, and await for a response.  Upon sending a RPC call, the software will halt and wait for a response from the back-end application server.

Variable | Type | Description
------------- |------------- |-------------
`$routing_key` | string | The routing key for RabbitMQ, formatted in *string.string.string* (eg. users.profile.create).  This will route the message to the appropriate workers and methods within them.
`$data` | string | Any string of data, many times will be JSON encoded.  This is the data received by the worker PHP class to perform the actual process.  For example, upon user registration, this contains a JSON encoded array of all profile fields.  The contents of this variable are up to you / the developer who added the message, so if needed refer to the developer documentation of the package you're integrating with to know what this variable should contain.


**Return Value:**

Variable | Type | Description
------------- |------------- |-------------
`$response` | array | An array containing the respons(es) from the back-end application server.  The keys of the array are the package alias that generated the response, and the values of the array are the actual response.  Generally will always be formatted in JSON, but not always.  Consult the developer documentation of the package you're using for exactly how this variable is formatted.


**Example:**

~~~php
namespace apex;

use apex\rpc;

$rpc_client = new rpc();
$data = '...' // Any string of data
$response = $rpc_client->send('loans.applications.load', $data);

echo "Response from back-end application server of 'loans' package is: " . $response['loans'] . "\n";
~~~

In the above example, Apex will look for all workers created with the routing key *loans.applications*, load the necessary PHP class, 
execute the `load()` method, and return the results.  

