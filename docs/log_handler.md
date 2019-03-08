
# Log Handler

Logging within Apex is both, quite simplistic and extensive.  Efforts have been made to support PSR3 specifications, including 
multiple log channels and the eight log levels (`debug, info, warning, notice, error, alert, critical, emergency`).  


### The /log/ directory

All logs are stored within the /log/ directory, which will contain the following files:

File | Description
------------- |------------- 
access.log | One line for every request to the system, simliar to a standard Nginx / Apache access log.
messages.log | The main log file which contains entries of all entries of all log levels.
system.log | Messages given off by the PHP parser itself, and not within the Apex software (eg. undefined index, etc.)
sql_error.log | Contains all SQL statements that resulted in an error from mySQL
LEVEL.log | One file for each of the eight supported PSR3 log levels (eg. info.log, alert.log, etc.)



### `log::LEVEL(string $message, [...$vars])`

These methods are available anywhere within Apex, and allow you to easily add a log under any of the eight supported PSR3 defined log 
levels.  Simply replace the method name `LEVEL` with one of the eight levels (`debug, info, warning, notice, error, alert, critical, emergency`), and the appropriate 
log entry will be added.

**Example**

~~~php
namespace apex;

use apex\log;

log::info("Here is some info about what's happening");

log::error("Houston, we have a problem!");

log::warning("Not sure, but might want to take a look at this.  Better log it just in case");

~~~


#### Placeholders

Full support for placeholders is also available as defined by PSR3.  Placeholders are simply numbers surrounded by left and right braces, such as `{1}` and `{2}`', and are easy to use.  For example:

~~~php
$name = 'John Doe';
$amount = 53.11;

log::warning("Payment from {1} was {2} lower than expected.", $name, $amount);
~~~

Above would add a log message that says *"Payment from John Doe was 53.11 lower than expected."*.


### Channels

Every log entry contains the channel name, which by default is "apex".  Support for multiple channels is available though, which allows you to easily filter the log file for only entries that are specific to your application / code.  Here's an example of how to create a new log channel.
~~~php
$logger = new log_channel('my_package');

$logger::error("Something bad happend!");
~~~

That's it.  Now every entry added via `$logger` will include the channel name "my_package" 
instead of "apex".  You can then manually filter them, or through the Maintenance-&gt;Log Manager menu of the 
administration panel you can also easily filter by channel name.


