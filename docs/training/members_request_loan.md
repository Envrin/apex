
# Apex Training - Members Area - Request New Loan

Let's jump in, and create the "Request New Loan" feature within the member's area.  First, login to your administration panel and create a user via the Users->Create New User menu, then login 
with that account at http://localhost/login.  You will see the menus that we defined within the package.php file, but let's just concentrate on the 
Request New Loan menu.


### Create Template

First, naturally we need a template for our menu.  In terminal, type:

`php apex.php create template members/loans/request lending`

If you remember in our package.php configuration file, the parent of this menu was "loans", and the menu itself was "request", hence the URI above.  We must also define "lending" as the package that 
owns the template, so it is included when we compile and publish the package to a repository.  Templates are just one component that is supported by Apex, 
and for details on all components and creating them, please visit the following links:

* [Components Overview](../components.md)
* [CLI Component Commands](../cli_component.md)


The above command will have created a new tpl file at */views/tpl/members/loans/request*.tpl*, so open 
it and enter the following contents:

~~~
<h1>Request New Loan</h1>

<e:form action="members/loans/request2">

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

That's it.  Now go ahead and visit the Loans->Request New Loan menu within your web browser, and you will see our new template and HTML form.  For details on the structure of templates 
including the handful of special HTML tags available, and the HTML forms, please visit [Template Structure / Engine](../templates.md).

There are a few points regarding the above .tpl file:

* All .tpl files should begin with a set of `<h1> ... </h1>` tags, as the template engine removes this, and places it as the page title where necessary depending on the theme being used.
* You will notice the form fields are prefixed with `ft_`, which means they will result in a two column table row, the left column being the textual name, and right column being the form field.  You may remove the `ft_` prefix from any form fields, and it will be replaced with only the form field itself without the table row.
* The `<e:form>` tag by default points to the template being displayed, and an `action=""` attribute is only required if the form needs to point to a different template.
 

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

    // Add to database
    DB::insert('loans', array(
        'userid' => $vars['userid'], 
        'amount' => $vars['amount'], 
        'title' => $vars['title'], 
        'description' => $vars['description'])
    );
    $loan_id = DB::insert_id();

    // Return
    return $loan_id;

}

}

~~~

The above function simply decodes a string of JSON that is passed to it, then inserts one row into the "loans" table of the 
database, plus adds a line to the debugger.  For more information on database and debug functions, please see the below links:

* [mySQL / Back-End](../database_mysql.md)
* [Debugger](../debugging.md)


### Create Library

Considering the size of this project, it would be suitable to simply place this code directly within the templates, but for training purposes, we will create a separate library.  In terminal, type:

`php apex.php create lib lending:loan`

This will create a blank PHP class located at */src/lending/loan.php*, which can be used for anything and everything.  Open up this class, and define the following contents:

~~~php
<?php
define(strict_types = 1);

namespace apex\lending;

use apex\DB;
use apex\registry;
use apex\debug;
use apex\message;
use apex\template;
use apex\user\users;
use apex\transaction\transaction;


/**
* This class handles all loan functionality, including requesting 
* a new loan, approving / declining loans, adding new payments, etc.
*/
class loan
{

/**
* Construct
*/
public function __construct(int $loan_id)
{
    $this->loan_id = $loan_id;
}

/**
/**
* Request a new loan
*     @param int $userid The ID# of the user requesting the loan
*     @param float $amount The amount being requested
*     @param string $title The one-line title of the loan
*     @param string $description The full description of the loan
*     @param mixed False is unsuccessful, otherise the unique ID# of the new loan
*/
public function request_loan(int $userid, float $amount, string $title, string $description)
{

    // Perform checks
    if ($amount < registry::config('lending:min_amount') && registry::config('lending:min_amount') > 0) { 
        template::add_message(fmsg("You must request a loan with a minimum amount of {1}", fmoney(registry::config('lending:min_amount'))), 'error');
    } elseif ($amount > registry::config('lending:max_amount') && registry::config('lending:max_amount') > 0) { 
        template::add_message(fmsg("You can not request a loan for more than the maixmum allowed amount of {1}", fmoney(registry::config('lending:max_amount'))), 'error');
    } elseif ($tital == '') { 
        template::add_message("You did not specify a one-line title for the loan", 'error');
    } elseif ($description == '') { 
        template::add_message("You did not specify a description for the loan", 'error');
    }

    // Check for errors
    if (template::$has_errors === true) { return false; }

    // Gather request
    $vars = array(
        'userid' => $userid, 
        'amount' => $amount, 
        'title' => $title, 
        'description' => $description
    );
    $request = json_encode($vars);

    // Send RPC command
    $loan_id = message::rpc('lending.loans.request', $request, 'lending');

    // Return
    return $loan_id;

}

}

~~~

It's just a simple PHP class with one method to request a loan.  This method performs some initial checks to ensure the loan amount requested is within acceptable rangae 
for the settings, then sends a RPC aommand to the "lending.loans.request" routing key to execute the action.  






### Tie it All Together

Now let's tie this all together.  In our first "request.tpl" template, we pointed the form to the action "request2", so we 
can provide a confirmation page instead of just a callout on the same page.  So, create a new template again with:

`php apex.php create template members/loans/request2 lending`

This time we need to modify the PHP code for this template, so open the new file located at */views/php/members/loans/request2.php*, and enter the following contents:

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
    'userid' => registry::$userid, 
    'amount' => registry::post('amount'), 
    'title' => registry::post('title'), 
    'description' => registry::post('description')
);

// Send via RPC
$loan_id = message::rpc('lending.loans.request', json_encode($vars), 'lending');

// Set template variables

~~~

See how that works?  It sends a JSON encoded string via RPC to the routing key "lending.loans.request", and upon creating the worker component we specified 
the routing key "lending.loans.*", which means this RPC call is going to get routed to that worker, and will execute the "request" method within the PHP class.  This setup 
allows the software to be easily separated onto multiple servers for scalability.


### Conclusion

We've went through quite a few aspects of Apex in this page while creating two templates, and a worker component.  Use the below 
links to read full documentation on the various aspects covered.


[Debugger](debugging.md)

* [Template Structure / Engine](../templates.md) -- All about templates, the directory structure, the handful of special HTML tags available, the few PHP functions available, and more.
* [Request Handling (registry class)](../request_handling.md) -- The registry class which handles all overall aspects of requests including GET / POST data, URI being displayed, response contents, etc.
* [mySQL / Back-End](../database_mysql.md) and [redis](../database_redis.md) -- The two databases used within Apex, the main mySQL database, and redis for quick access to small / recent data.
* [Workers and Routing Keys](../components/worker.md) and [Sending One-Way Messages and Two-Way RPC Calls](../messaging_send.md) -- How Apex implements horizontal scaling via RabbitMQ, worker components, and RPC calls.

Once you've digested that information, move on to the next page in this training gude, [Admin Panel - Pending Loans](admin_pending_loans.md)


