
# Request Handling

There is an *apex\registry* class available, which handles all data pertaining to 
HTTP requests and responses, and is automatically populated with every request to Apex.


### `post(), get(), cookie(), server()` Methods

For security purposes, never use the super globals such as `$_POST` and `$_GET`, and instead only use these methods to retrieve 
input data, as these variables are properly sanitized.  For example:

**NOTE:** These methods will return a `NULL` if the key does not exist, allowing for the use of the `??` operator.

~~~php
namespace apex;

use apex\registry;

$category_id = registry::get)'category_id') ?? 0;
$name = registry::post('full_name');
~~~


### `has_post(), has_get(), has_cookie, has_server()` Methods

These methods should always be used in place of PHP's built-in `isset()` function, and they simply 
return a boolean whether or not the requested variable exists.  For example:

~~~php
namespace apex;

use apex\registry;

if (!registry::has_post('note')) {
    echo "No 'note' variable was defined!";
}
~~~

### `clear_post() / clear_get()` Methods

This allows you to clear the POST and GET input arrays,  Useful for example, after a form has been submitted and action completed successfully, 
you may want to clear the POST array so the form displayed on the template again is not pre-filed in with the previously submitted data.

### URI / Routing 

Several properties are available pertaining to the URI and HTTP request itself.  These are:

Variable | Type | Description
------------- |------------- |-------------
`$request_method` | string | GET, POST, HEAD, PUT, etc.  The `$_SERVER['REQUEST_METHOD']` variable
`$http_controller` | string | The *http_requests* controller that is handling the request (ie. the first segment of the URI).
`$route` | string | the URI of the request, minus the first segment if the first segment exists as a http_requests controller.
`$uri` | array | Array consisting of the `$route` variable, split by / characters.
`$ip_address` | string | The IP address of the user.
`$user_agent` | string | The user-agent of the user, if exists.

For example:

~~~php
namespace apex;

use apex\registry;

// Example URI:  /blog/post/5832
if (registry::$uri[0] == 'post') { 

    // Show post ID# from registry::$uri[1] (5832
    $post_id = registry::$uri[1];

}
~~~


### `config()` Method

The `registry::config()` method is also available, and allows you to easily retrieve any configuration variables 
for the system.  These variables are defined within the `$this->config` array of the [package.php __construct() Function](packages_construct.md) file.  All variables include 
their package alias, and are named `PACKAGE:ALIAS`.  For example:  

~~~php

$sname = registry::config('core:site_name');

$title = registry::config('myblog:title');
~~~

The above will return the `site_name` config variable as defined within the `core` package, and the `title` configuration as 
defined by the `myblog` package within the /etc/myblog/package.php file. 


### User

There are also several user-based properties available, as explained below.

Variable | Type | Description
------------- |------------- |-------------
`$panel` | string | The panel / area that is being accessed.  Generally, will always be "public", "admin" or "members", although can be expanded by developers.
`$theme~ | string | The theme that is being displayed, generally taken from the`$config` array.  This is the sub-directory name within the /themes/ directory of which theme to display.
`userid` | int | The ID# of the user / administrator that is logged in, if authenticated.
`$action` | string | Simply the value of any `post['submit']` variable, if one exists.  Only for standardization, and to avoid typing `$action = registry::$post['submit'] ?? ''` in every template.
`$language` | string | The two character language code of the authenticated user's language preference.  If not authenticated, defaults to the `DEFAULT_LANGUAGE` constant within the /etc/config.php file.
`$timezone` | string \ The 3 or 4 character timezone (eg. PST, EST, etc.) of the authenticated user's timezone preference.  If not authenticated, defaults to the `DEFAULT_TIMEZONE` constant found within the /etc/config.php file.


### Response variables

Several properties are available pertaining to the response given to the user.  These should never be manually modified, as there are methods within the class to do so (see below).

Variable | Type | Description
------------- |------------- |------------- 
`$res_status` | int | The HTTP status code of the response.  Defaults to 200 OK, but can be changed via the `registry::set_http_status($code)` method.
`$res_content_type` | string | The content type given within the response.  Defaults to "text/html", but can be changed via the `registry::set_content_type($type)` method.  Note, if developing a JSON API or similar, ensure to set the content type to "text/json", as by doing so any errors that occur will be outputted in JSON format instead of a HTML template.
`$response` | string | The actual contents of the response.  Set using the `registry::set_response($content)` method.


## Methods Available

Below describes all the methods available within the *apex\registry* class.


#### `void create()`

**Description:** Is automatically executed for every request to Apex, and populates the class properties.  There is never any reason to run this method manually.


#### `void handle_request()`

**Description** Handles the request.  Passes the request off to the appropriate *http_requests* controller, obtains the response, and closes the debug and logging 
sessions.  Does not output the response, so unit tests can obtain and evaluate the response.


#### `bool set_route(string $route)`

**Description** Changes the `registry::$route` property, which defines which template the template engine will display.  This generally isn't 
needed, but can sometimes be useful when for example, a form points to a different page, but due to submission errors you want to 
return to the previous page with the form pre-filled in and user errors displayed above.

**Parameters**

Variable | Type | Description
------------- |------------- |-------------
`route` | string | The new route to set within the registry (eg. admin/mypackage/some_template).


#### `bool set_userid(int $userid)`

**Description:** Should never have to be run manually, and is only used by the authentication library.  This sets the `registry::$userid` variable, 
defining the ID# of the user / administrator that is logged in.


#### `bool set_http_status(int $code)`

**Description:** Set the HTTP status code of the response.  This defaults to 200, but can be changed via this method if needed.

**Parameters:**

variable | type | Description
------------- |------------- |------------- 
`$code` | int | The HTTP status code to change the response to.


#### `set_content_type(string $content_type)`

**Description:** Sets the content type of the response.  Defaults to "text/html", so generally you will never 
need this method.  However, if for example you're developing a JSON API, ensure to set the content type to "text/json" as the error handler will notice the content type, 
and output any error messages in their proper format (eg. JSON).

**Parameters:**

Variable | Type | Description
------------- |------------- |------------- 
`$content-type` | string | The content type to set the response to.


#### `bool set_response(string $content)`

**Description:** Sets the contents of the response.  This is the contents that is outputted to the user / browser.


#### `string get_response()`

**Description:** Returns the contents of the response, and is used mainly with unit tests to evaluate the response instead of outputting it to the browser.


#### `echo_response()`

**Description:** Outputs the response to the user / browser.  Generally never needs to be 
manually executed.



#### `echo_template(string $uri)`

**Description:** Allows you to quickly display any template, close the session, and exit with one line.  Tih si useful when 
you want to break execution of the template PHP code mid-way, and display the previous template again with user submission errors.

#### `update_config_var(string $var, string $value)`

**Description** Updates a variable within the `self::$config` array, and is generally used when updating settings within the administration panel.  This updates both, 
the database as necessary, plus the ~self::$config` array so the new value is in place for the rest of the request.


#### `array geoip([string $ipaddr])`

**Description:** Uses the popular MaxMind library and database to perform a geo lookup on a IP addresses.  Returns an array containing the country 2 character code, country full 
name, state / province, and city name.  If no IP addresses is passed, defaults to the `self::$ip_address` variable, which is the current user's IP address.


#### `string get_logdate()`

**Description:** Used by the log handler and debugger, and gets the date to use within the log files.  All times within Apex are UTC, so this simply 
formats the current time to the DEFAULT_TIMEZONE constant within the /etc/config.php file, and returns in *YYYY-MM-DD HH:II:SS* format.


#### `array get_timezone([string $timezone = ''])` 

**Description:** You should never have to manuyll call this method, and it's generally only used by the global 
`fdate()` function to obtain the appropriate timezone data (offset, and dst) for the user's timezone preference.


#### `array get_currency(string $currency)`

**Description:** Another method you should never have to call, and is generally only used by the global 
`fmoney()` function.  Used to gather appropriate information on a currency, namely the currency symbol and number of decimals to format to.



