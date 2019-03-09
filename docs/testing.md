
# Testing via phpUnit

Apex fully integrates with the popular phpUnit to allow for unit tests.  If you are not currently 
familiar with phpUnit, please take a look at the below two links to help familiarize yourself with it.

* [Getting Started with phpUnit](https://phpunit.de/getting-started/phpunit-7.html)
* [phpUnit Assertions](https://phpunit.readthedocs.io/en/8.0/assertions.html)

**NOTE:** For examples of the methods and custom assertions described below, look at the unit tests located 
within the directory /src/core/test/.


### Creating / Executing Test Classes

Create a new test class by simply opening up terminal, change to the installation directory, and type:
`php apex.php create test PACKAGE:ALIAS`

This will create a file at /src/PACKAGE/test/ALIAS.php which you may modify as needed, and add your test methods which will be 
executed by phpUnit.  You can then automatically run the unit tests with:

`php apex.php test PACKAGE [ALIAS]`

The above will run either, the specific test class specified as `ALIAS`, or if not specified will run all unit test classes within the package one at a time.



### `string registry::test_request($uri, [$method = 'GET'], [array $post = array()], [array $get = array()], [array $cookie = array()])`

**Description:** A very useful method you will probably find yourself using often while writing your unit tests.  This will emulate a HTTP request to any page 
within the software, and return the response.  Useful to allow the unit tests to emulate a human being going through the online operation.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$uri` | string | The URI which to request (eg. /register, /admin/settings/mypackage, etc.)
`$method` | string | Should always be either GET or POST, and is the request method of the request.  Defaults to GET.
`$post` | array | Array containing any variables you would like POSTed to the request.
`$get` | array | Array of any GET values you would like included within the request (ie. the query string)
`$cookie` | array | Optional array of any cookie key-value pairs you would like included within the request.


**Example**

~~~php
namespace apex;

use apex\registry;

// Set post varaibles
$request = array(
    'username' => 'myuser', 
    'password' => 'mypass', 
    'submit' => 'login'
);

// Send request
$response = registry::test_request('/login', 'POST', $request);
$this->assertpagetitle("Welcome to the member's area");
~~~

The above example will send a POST request to /login on the system, emulating a user submitting the public login form.


### Custom Assertions

On top of all the standard assertions provided by phpUnit, Apex also offers various additional assertions to help aid 
in the writing of unit tests.  These can all be executed exactly as phpUnit assertions, such as for example within one of your test classes you 
could use:

~~~php
function test_sometest()
{

    // Send request
    $response = registry::test_request('/admin/casinos/games');

    // Check the page title
$this->assertpagetitle('Manage Casino Games');

// Check for a user message
$this->asserthasusermessage('success', "Successfully created new casino game");
~~~

The below table lists all custom assertions available to your unit tests within Apex.


Method | Description
------------- |------------- 
`assertpagetitle($title)` | Checks the page title of the last test request sent to see if it matches `$title`.  The inverse is `assertnotpagetitle`.
`assertpagetitlecontains($text)` | Checks if the page title contains the specified `$text`.  Inverse is `assertpagetitlenotcontains`.
`assertpagecontains($text)` | Checks if the page contents anywhere contains `$text`.  Inverse is `assertpagenotcontains`.
`asserthasusermessage($type, $message)` | Checks if the most recent page requested contains a user message / callout with the type of `$type~ (success, error, or info) and contains the text in `$message`.  The inverse is `assertnothasusermessage`
`asserthasfirnerrir*$tyoem $field_name)` | Checks if the page has a form validation error given by the `forms::validate_form()` method of the specified type (blank, email, alphanum) on the specified form field.  Inverse of this method is `assertnothasformerror`.
`asserthassubmit($value, $label)` | Checks if the last requested page contains a submit button with the specified value and label.  The inverse of this method is `assertnothassubmit`.
`asserthastable($table_alias)` | Checks if the page contains a HTML table component with the alias in Apex format (ie. PACKAGE:ALIAS) that is displayed via the `<e:function>` tag.  Inverse of this method is `assertnothastable`.
`asserthastablefield($table_alias, $col_num, $value)` | Checks if the specified HTML tab has a row containing the specified value in the specified column number.  Inverse of this method is `assertnothastablefield`.
`asserthasdbfield($sql, $column, $value)` | Retrives one row from the database with the specified SQL statement, and checks if the specified column name matches the value.  The inverse of this method is `aasertnothasdbfield`.
`asserthasformfield($name)` | Checkes if the last page requested contains a form field with the specified name.  The inverse of this method is `assertnothasformfield`
`assertstringcontains($string, $text)` | Checks if the provided string contains the specified text.  Inverse of this method is `assertstringnotcontains`.
`assertfilecontains($filename, $text)` | Checks if the specified file contains the specified text within its contents.  Inverse of this method is `assertfilenotcontains`.





