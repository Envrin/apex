
# Debugging

TO take advantage of debugging functionality within Apex, please ensure both, the system is in development 
mode, and you have the Development Toolkit package installed.  You may do both from your terminal by running:

~~~
php apex.php mode devel 3
php apex.php install devkit
~~~

The first command above will switch the system into development mode with a debug level of 3, although the debug level can be 0 - 5 depending on 
how extensive of debugging information you would like.  Once in development mode, the debugger is always on, and upon an error being thrown, 
a tab control will be displayed within the error template displaying full details on the request.


### Saving Debug Sessions

Sometimes you will want to save debugging information on a request to review later.  To do this, 
within terminal change to the installation directory and run:

`php apex.php debug [MODE]`

Where MODE can be either:

* 0 - No debugging
* 1 - Save next request, then turn off debugging
* 2 - Save every request, do not turn debugging off.

Apex only retains one debugging session at a time, so for example, `php apex.php debug 1` will save the next 
request to the software, then stop overwriting the session.  Once saved, you can view full details on the request via the Devel Kit->Debugger menu 
of the administration panel.


### `debug:add(int $level, string $message, __FILE__, __LINE__, [string $log_level = 'debug'])`

**Description:** Can be called anywhere within the software, and adds a new entry into the current debug session, plus can 
optionally add the entry into the log handler as well.  This is the only call you need for both, debugging and logging within 
APex.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$level` | int | The minimum debug level, and must be between 1 - 5.  The entry will only be added to the debug session if the system debug level is set to this number or higher.
`$message` | string | The contents of the debug / log message.  Generally, this is surrounded by the global `fmsg()` function to allow for usage of placeholders.
`__FILE__` | string | Always simply use the `__FILE__` constant as to pass the filename of where the call is being made.
`__LINE__` | int | Always use the `__LINE__` constant as to pass the line number from where the call is being made.
`$log_level` | string | Optional, and used to also add the entry into the appropriate log file via the log handler.  Can be any of the eight supported PSR3 log levels, which are: `debug, info, warning, notice, error, alert, critical, emergency`



