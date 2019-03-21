
# Apex Training - Members Area - Request New Loan

Let's jump in, and create the "Request New Loan" feature within the member's area.  First, login to your administration panel and create a user via the Users->Create New User menu, then login 
with that account at http://localhost/login.  You will see the three menus that we defined within the package.php file, but let's just concentrate on the 
Request New Loan menu.


### Create Template

First, naturally we need a template for our menu.  In terminal, type:

`php apex.php create template members/loans/request_loan lending`

If you remember in our package.php configuration file, the parent of this menu was "loans", and the menu itself was "request_loan", hence the URI above.  We must also define "lending" as the package that 
owns the template, so it is included when we compile and publish the package to a repository.  This will have created a new .tpl file at */views/tpl/members/loans/request_loan.tpl*, so open 
it and enter the following contents:

~~~
<h1>Request New Loan</h1>

<e:form action="members/loans/request_loan2">

<e:box>
    <e:box_header title="Loan Details">
        <p>You may request a new loan by completing the below form with the necessary information.</p>
    </e:box_header>

    <e:form_table>
        <e:ft_amount name="amount" required="1">
        <e:ft_textbox name="title" label="One Line Description" required="1">
        <e:ft_textarea name="description" required="1">
        <e:ft_submit value="request" label="Request New Loan">
    </e:form_table>

</e:box>
~~~

That's it.  Now go ahead and visit the Loans->Request New Loan menu within your web browser, and you will see our new template and HTML form.  


### Create Loans Worker

To help ensure security and scalability, we will want all back-end processes to be executed by [Worker Components](../components/worker.md).  The worker components receive messages from RabbitMQ, and should 
always be used for all heavy lifting of the software, or in other words, any processes that may potentially take one second or longer.  This allows (if desired) the client to easily move 
the main load of the software off the front-end web server(s), and on to back-end application servers.  If the volume ever gets too high for one server, this also allows the client to easily 
setup multiple back-end application servers, and have the load evenly distributed among them.

Now that you know why we're using a worker component, let's go ahead and create one.  In terminal, type:

`php apex.php create worker lending:loans lending.loans`

This will create a new PHP class at */src/lending/worker/loans.php*, and will begin listening to all messages from RabbiqMQ that are routed through the "lending.loans.*" routing key.  Open the new PHP 
class, and enter the following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex\lending\worker;

use apex\DB;
use apex\registry;
use apex\debug;


/**
* Handles the back-end functions for all loans.
*/
class loans
{


/**
* Request a new loan
*/
public function request($data)
{

    // Decode JSON
    $vars = json_decode($data, true);

}

}

~~~

We will get back to this class in a minute.


### Tie it All Together

Now let's tie this all together.  In our first "request_loan.tpl" template, we pointed the form to the action "request_loan2", so we 
can provide a confirmation page instead of just a callout on the same page.  So, create a new template again with:

`php apex.php create template members/loans/request_loan2 lending`

This time we need to modify the PHP code for this template, so open the new file located at */views/php/members/loans/request_loan2*, and enter the following contents:

~~~php
<?php
declare(strict_types = 1);

namespace apex;


// Make sure we've come through the right form
if (registry::$action != 'request') { 
    throw new ApexException('error', "You did not come through the right way!");
}

// Set variables
$vars = array(
    'amount' => registry::post('amount'), 
    'title' => registry::post('title'), 
    'description' => registry::post('description')
);

// Send via RPC
$rpc = new rpc();
$rpc->send('lending.loans.request', json_encode($vars));

~~~

See how that works?  Upon submitting the form to request a loan, the necessary variables are gathered into a JSON encoded string, and sent via RPC to RabbitMQ, which then 
routes the message to the worker PHP class we previously created.  This allows the back-end processes to be easily separated onto a separate server, and / or multiple 
back-end application servers setup to handle large volume operations.  It is highly recommended that all resource intensive operations within your software are 
routed to worker components in this manner when developing with Apex.



