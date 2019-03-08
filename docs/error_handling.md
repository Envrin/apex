
# Error Handling

All errors within Apex are given off by throwing exceptions, mainly the `ApexException` class.  There are multiple exception classes available, which you 
can view within the /lib/exceptions/ directory of the software.


### `throw new ApexException(string $level, string $message, [...$vars])`

**Description:** This allows you to throw a general error anywhere within Apex, which will be logged and rendered 
as necessary.  If viewing from the web browser, the appropriate error .tpl template will be displayed, if via JSON (ie. the response content-type is set to "text/json") it will return a JSON formatted error, and if executing via CLI / terminal, it will simply render a plain text error within the terminal.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$level` | string | One of the eight supported PSR3 log levels, and if ensure, simply use "error".  Can be: `debug, info, warning, notice, error, alert, critical, emergency`
`$message` | string | The error message, which is processes through the global `fmsg()` function to support placeholders (eg. {1}, {2}, etc.)
`vars` | array | Optional array of variables, which placeholders within the message are replaced with.

**Example**

~~~php
namespace apex;

use apex\ApexException;

$x = 5;
if ($x < 9)
    throw new APexException('error', "Uh oh, x is not supposed to be less than 9, but is {1}", $x);
}
~~~


### Error Templates

There are several error templates available within the /views/tpl/ directory, and there are separate error templates 
for each area (admin, members, public).  These error templates are:

Filename | Description
----------- |----------- 
404.tpl | Custom File Not Found template, and is displayed when viewing a URI for which no .tpl template exists.  
500.tpl | Standard error template, and provides contents of the error message.  If system is in development mode, also displays full information on the error including a tab control with all debugger information.
500_generic.tpl | Displayed during select errors such as SQL errors, and instead of providing any details on the error, simply logs the error and says an error occurred.  Only displayed when system is in production mode, and only for select errors as to not provide too much information to attackers.
504.tpl | Custom timeout template, and displayed if the system can not connect to either the internal RPC or WebSocket servers.


### Adding Custom Exception Class

You can easily add a custom exception class if desired by simply adding the desired PHP class into the 
/lib/exceptions/ directory.  Look at the other exception classes other than `ApexException.php` to get an idea of how they are used.

Upon adding a new exception, please remember to add it into the `$this->ext_files` array of 
the [package.php __construct() Function](packages_construct.md) within the /etc/PACKAGE?package.php file to ensure it gets included when 
publishing / installing the package.


