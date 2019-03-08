
# Error Handling, Logging, and Debugging

Apex allows for very straight forward and simple error handling, logging, and debugging functionality.  The eight 
different log levels are supported as defined in PSR3, which are: `debug, info, warning, notice, error, alert, critical emergency`.  For details 
on this functionality, please ensure to read the below linked pages as this page only gives very brief details.

* [Error Handling](error_handling.md)
* [Logging](log_handler.md)
* [Debugger](debugger.md)

### Server Mode (development / production)

You can easily switch the system between development and production modes, which modifies how errors are handled.  When in 
production mode no details on errors are displayed, and either only the error message itself is displayed, or for more internal errors (eg. SQL error) a generic error template is displayed that gives no error 
message at all.  However, when in development mode, full details on errors are displayed including a tab control providing all debugger information on the 
request.

You can easily switch between development / production modes via the Settings->General menu of the administration panel, or via 
the terminal by running:

`php apex.php mode [devel|prod] [DEBUG_LEVEL]`.  

The DEBUG_LEVEL can be 0 - 5 and defines how extensive of logging you would like to view within the debugger.  Generally, a debug level of 3 should be sufficient to pinpoint any errors / bugs.  

**Example:** `php apex.php mode devel 3`


### Errors

All errors are given by throwing exceptions, the main / general one being `ApexException``, an example of which is:

`throw new ApexException('error', "Houston, we have a problem!");`

The first parameter is one of the eight PSR3 log levels, and if unsure, simply use "error".  The second parameter is the 
actual error message to give off.  For full details on error handling including how to add your own custom exception classes, please 
refer to the [Error Handling](error_handling.md) page of this manual.


### Debugging

Assuming the system is set to development mode, the debugger is always on, and upon getting an error within the web browser a 
tab control will be displayed showing full details on the request and error.  The only function you need is `debug::add` as it combines both, 
debugging and logging functionality, and it can be called anywhere within the software with:

`debug::add(int $level, string $message, __FILE__, __LINE__ [string $log_level])`

The `$level` is 1 - 5, and defines the minimum debug level that must be set in order to log the item into the debugger.  The 
optional `$log_level` variable is one of the eight supported PSR3 levels, and if defined, will also add the entry to the appropriate log file on top of the 
debug session.  For example:

`debug::3, "We're doing some cool action", __FILE__, __LINE__, 'info');`

The above will be added to the current debug session if the debug level in the system is set to 3 or higher, and it will also be recorded as an 
INFO entry into the log handler.  For full information on the debugger, please visit the [Debugger](debugger.md) page of this manual.


