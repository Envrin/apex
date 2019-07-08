
# The `app` Class

The *apex\app* class is the main / central class for all Apex applications, and is used in every request.  It
handles the HTTP request and response, dependency injection container, services, stores all information
regarding the request such as inputted data via POST / GET methods, and more.

This class is a singleton, meaning only one instance is ever created per-request, allowing it to also be
accessed statically for better accessibility.  It is always loaded in virtually every PHP file by simply
loading the `apex\app` namespace such as:

~~~php
namespace apex\mypackage;

use apex\app;

... rest of the code ...
~~~


1. <a href="#input_arrays">Input Arrays (POST, GET, COOKIE, etc.)</a>
2. <a href="#config">Configuration Variables</a>
3. <a href="#apex_request">Apex Request</a>
4. <a href="#http_request">HTTP Request</a>
5. <a href="#http_response">HTTP Response</a>
6. <a href="#additional">Additional</a>
7. <a href="dependency_injection">Dependency Injection</a>



<a name="input_arrays">
## Input Arrays

#### <api:app>_post()</api>, <api:app>_get()</api>, <api:app>_cookie()</api>, <api:app>_server()</api> Methods

For security purposes, never use the super globals such as `$_POST` and `$_GET`, and instead only use these
methods to retrieve input data, as these variables are properly sanitized.  For example:

~~~php
namespace apex;

use apex\app;

$category_id = app::_get('category_id') ?? 0;
$name = app::_post('full_name');
~~~


#### `has_post(), has_get(), has_cookie, has_server()` Methods

These methods should always be used in place of PHP's built-in `isset()` function, and they simply return a
boolean whether or not the requested variable exists.  For example:

~~~php
namespace apex;

use apex\app;

if (!app::has_post('note')) {
    echo "No 'note' variable was defined!";
}
~~~


#### <api:app>getall_post()</api>, <api:app>getall_get()</api>, <api:app>getall_cookie()</api>, <api:app>getall_server()</api> Methods

These return an array consisting of all key-value pairs of the corresponding array.  For example:

~~~php
namespace apex;

use apex\app;

$post_vars = app::getall_post();
~~~


#### <api:app>clear_post()</api> / <api:app>clear_get()</api> Methods

This allows you to clear the POST and GET input arrays,  Useful for example, after a form has been submitted
and action completed successfully, you may want to clear the POST array so the form displayed on the template
again is not pre-filled in with the previously submitted data.



<a name="config">
## Configuration Variables

All global configuration variables as defined by the installed packages can be retrived with the following
methods:

Method | Description ------------- |-------------
<api:app>_config()</api> | Get a single configuration variable (eg. *core:domain_name*, *transaction:base_currency*, etc.)
<api:app>has_cookie()</api> | Returns a boolean whether or not the configuration variable exists.
<api:app>getall_config()</api> | Returns an array consisting of all configuration variables.


<a name="apex_request">
## Apex Request

There are various methods available to obtain and set information regarding the request that is specific to
Apex, and are explained in the below table.

Method | Description ------------- |-------------
<api:app>get_action()</api> | Returns the value of any "submit" form field previously posted, blank otherwise.
<api:app>get_uri()</api> / <api:app>set_uri($uri)</api> | Gets or sets the URI that is being accessed, and is used to determine which .tpl template file from within the */views/* directory is displayed.
<api:app>get_userid()</api> / <api:app>set_userid($userid)</api> | Gets or sets the ID# of the authenticated user.  Generally, you should never have to set the userid as the authentication engine does that automatically, but useful to obtain the ID# of the user.
<api:app>get_area()</api> / <api:app>set_area($area)</api> | Gets or sets the current active area.  Generally, this will always be either "admin", "members" or "public", is automatically determined based on URI, and should never have to be set.
<api:app>get_theme()</api> / <api:app>set_theme($theme)</api> | Gets or sets the theme being displayed.  Generally, you will never need either of thise, as they are used by the template engine only.
<api:app>get_http_controller()</api> | Gets the HTTP controller that will be handling the request, resides in the */src/core/controller/http_requests/* directory.


**Examples**

~~~php

namespace apex;

use apex\app;


// Submit button from previous form was "create"
if (app::get_action() == 'create') {

    // Display the /public/winner template instead if first 500 members.
    if (app::get_userid() <= 500) {
        app::set_uri('winner');
    }

}
~~~



<a name="http_request">
## HTTP Request

The below methods allow you to retrive various information about the HTTP request itself. Please note, this
information is read-only and can not be set.

Method | Description ------------- |-------------
<api:app>get_ip()</api> | IP address of the client connecting.
<api:app>get_user_agent()</api> | The user agent of the client connecting.
<api:app>get_host()</api> | The hostname being connected to (ie. your domain name)
<api:app>get_port()</api> | The port being connected to, generally either 80 or 443 (SSL).
<api:app>get_protocol()</api> | The HTTP protocol version being used, generally 1.1.
<api:app>get_method()</api> | The request method of the request (ie. GET, POST, DELETE, etc.)
<api:app>get_content_type()</api> | The content type passed by the client.
<api:app>get_uri_segments()</api> | An array of the URI split by the / character, with the first sgement being left off in case it's an HTTP controller (eg. admin, members, repo, etc.)
<api:app>get_request_body()</api> | Get the full raw contents of the request body.  Useful if example, a chunk of JSON was POSTed to the software.
<api:app>_header($name)</api> | Array of all instances of the `$name` within the HTTP header sent by the client.
<api:app>_header_line($name)</api> | A single comma delimited line of all instances of the `$name` HTTP header passed by the client.



<a name="http_response">
## HTTP Response

The below methods allow you to control the response that is given back to the client.

Method | Description ------------- |-------------
<api:app>set_res_http_status($code)</api> | Set the HTTP status code of the response (eg. 404, 500, 403, etc..  Defaults to 200 OK.
<api:app>set_res_content_type($type)</api> | Set the content type of the HTTP response.  Defaults to "text/html".
<api:app>set_res_header($name, $value)</api> | Set a custom HTTP header within the response.
<api:app>set_res_body($contents)</api> | Set the actual contents of the response.
<api:app>echo_response()</api> | Outputs the response including HTTP status and headers to the browser.
<api:app>echo_template($uri)</api> | Halts execution, and displays the template at `$uri` then exists.  Useful if example, you need to display a 2FA pending template and halt further execution.



<a name="additional">
## Additional

There's various additional methods available that will be useful at times, and are explaind in the below
table.

Method | Description ------------- |-------------
<api:app>get_instance()</api> | Returns the single instance of the app class.  Used when you need to call non-static methods on it for dependency injection.
<api:app>update_config_var($var, $value)</api> | Updates a configuration variable.
<api:app>get_counter($name)</api> | Increments the specified counter by 1, and returns the new integer.  Useful when you need incrementing numbers outside of the database.
<api:app>get_timezone()</api> | Returns the timezone of the current session.
<api:app>get_language()</api> | Returns the language of the current session.
<api:app>get_currency()</api> | Returns the base currency of the current session.



<a name="dependency_injection">
## Dependency Injection

The app class also acts as the container for dependency injection.  This is explained in more detail elsewhere
in the documentation, but the methods available are explained below. Please note, these methods can not be
accessed statically, so you must obtain the actual instance of `$app` to call them.

method | Description ------------- |-------------
<api:app>get($key)</api> | Get the value of the key from the container.
<api:app>has($key)</api> | Returns a boolean as to whether or not the container contains the specified key.
<api:app>set($key, $value)</api> | Set a new value within the container.
<api:app>make($class_name, array $params = [])</api> | Creates and returns an instance of the specified class.  This instance is not saved within the container, and is only created with dependency injection, then returned.
<api:app>call([$class_name, $method], array $params = [])</api> | Call a specific method within a class while performing dependency injection directly on the method.  This does not save anything within the container, and only calls the method, but does allow for method / setter injection.
<api:app>injectOn($object)</api> | Only useful if using annotation injection, and allows you to inject dependencies after an instance of the class has been created.  Never really needed for Apex.



