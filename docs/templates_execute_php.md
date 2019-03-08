
# Execute PHP on Exiting Template

All templates have a PHP file located within the */views/php/* directory, relative to the URI being viewed, which is executed every time the template is displayed.  However, there will be times 
when you are developing a new package, and want additional PHP code executed on an already existing template that you can't modify since it belongs to a different package.  For example, you may want additional code 
executed on the home page of the public web site.

This is actually quite simplistic in Apex, as an RPC call is made every time a template is displayed.  To execute additional PHP code on an existing template, simply create a worker with 
the routing key `core.template`.  For example, if developing a package named "casino", you would run something like:

`php apex.php create worker casino:parse_template core.template`

This will create a new file at */src/casino/worker/parse_template.php*.  Open this file up, and create a `parse($data)` method within it, which will be executed every time a template is displayed within the software.  The `$data` 
variable passed is a JSON encoded string of all request data including the URI being viewd, all POST / GET data, and more.  This method should return an associative array, and all key-value paris within the returned array will be 
assigned to the template.


**Example**

~~~php
<?php
declade(strict_types = 1);

namespace apex\casino\worker;

use apex\DB;
use apex\registry;
use apex\log;
use apex\debug;


class parse_template
{

/**
* Execute this method every time a template is parsed.
*/
public function parse(string $data)
{

    // Decode JSON
    $vars = json_decode($data, true);

    // Make sure we're on the home page, otherwise return nothing
    if ($vars['panel'] != 'public' || $vars['route'] != 'index') { 
        return array();
    }

    // Do some stuff
    $bets = array();
    $bets[] = array('id' => 45, 'winner' => 'mike', 'amount' => 54.11), 
        array('id' => 88, 'winner' => 'joan', 'amount' => 284.11)
    );
    $total_won = 853.33;
    $total_bets = 48;

    // Set response
    $response = array(
        'bets' => $bets, 
        'total_won' => $total_won, 
        'total_bets' => $total_bets
    );

    // Return
    return $response;

}

}
~~~

That's it~  As you can see, the function checks to ensure we're on the home page of the public site, and if not, returns a blank array and stops executing.  Otherwise, it will grab some 
variables, and create a single `$response` associative array which it returns.  In turn, we can now use the `~total_won~` and `~total_bets~` merge variables within the 
home page template, plus create an `<e:section name="bets"> ... </e:section>` block on the home page which will loop through that `$bets` array we 
defined.





        




