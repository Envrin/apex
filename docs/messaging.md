
# Messaging (RabbitMQ)

Apex fully implements the messaging engine RabbitMQ allowing for easy and efficient horizontal scaling, ensuring your operations can easily scale to any volume.  If you are 
unfamiliar with RabbitMQ, please view this [RabbitMQ PHP Tutorial](https://www.rabbitmq.com/tutorials/tutorial-one-php.html).  Simply put, it provides messaging 
functionality to the software, and evenly distributes the messages to the back-end application servers to handle the heavy lifting of the software.  This way, if and when the volume gets too high, 
you can easily add a new server into the cluster to help handle the load.


### Horizontal Scaling Overview

The setup is quite simplistic:

* Front-end load balancer(s) running haproxy that evenly distribute incoming HTTP requests to the web servers.
* The web servers handle all incoming HTTP requests, and have direct access to the redis database which is a very quick in-memory database engine.
* Behind the web servers is a messaging server that hosts RabbitMQ, and accepts messages from the web servers.
* Behind the messaging server are multiple back-end application servers that handle the heavy loading of the software (user registration, transaction processing, etc.).  RabbitMQ will evenly distribute messages to these application servers, so as volume increases, you can easily just drop another application server in place to help handle the load.
* The back-end application servers are the only servers that every communicate directly with the mySQL database, which is also running database replication.

There are two types of messages available -- one-way messages where the web servers only require an acknowledgement that the message has been accepted, and 
two-way RPC commands where the web server has to wait for a response from RabbitMQ / back-end application servers before providing output to the web browser.  Developers should aim 
that 90% of all requests only use one-way messages, and only use two-way RPC commands if necessary as two-way RPC commands take longer to process, hence may slow down the overall operation and cause bottlenecks.

For example, when a user registers, the web servers only communicate with the redis database, send one message to RabbitMQ containing the registration information, 
then outputs the "thank you" page to the web browser, and closes the connection.  RabbitMQ then picks up the message, and relays it to one of the back-end application servers, which then performs the actual registration and commits it to the 
mySQL database.

However, since redis is an in-memory database, it does come with storage limitations, meaning sometimes information from the mySQL database is needed requiring a 
two-way RPC call.  For example, when a user is viewing their transaction history, due to storage limitations that data can not be stored in redis.  Instead, the web server will 
make a two-way RPC call to RabbitMQ, which then relays the message to one of the back-end application servers, which then gathers the necessary information from 
the mySQL database, returns it to RabbitMQ, which then relays it back to the web server.  This process can take several seconds, so best to avoid two-way RPC calls unless required.

### More Information

For further information on how messaging works within Apex, please use the below links.

1. [Workers and Routing Keys](components/worker.md)
2. [Sending One-Way Messages and Two-Way RPC Calls](messaging_send.md)
3. [DigitalOcean API](digitalocean.md)
4. [Core Message Queues](messaging_queues.md)
    1. [Core Apex Framework](core/messages.md)
    2. [User Management](core/users.md)
    3. [Transactions and Payments](transaction/messages.md)


