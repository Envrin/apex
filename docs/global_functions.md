
# Global Functions

There are a handful of global functions available, which help facilitate development within Apex.  All 
functions are available within the */lib/functions.php* library, and are explained below.


### `string tr(string $text, ...$args)`

**Description:** Used to automatically translate text into the authenticated user's language, and should be used 
when adding user messages via `template::add_message()`, triggering errors via `trigger_error()`, and any other English based text within your code that is outputted to the browser.

Fully supports placeholders, which should always be used to help facilitate translation into multiple languages.  For a few examples:

~~~php
$text = tr("The user's full name is %s, and their group ID# is %s.", $full_name, $group_id);

template::add_message(tr("Successfully added %s to the user's account, %s.", $amount, $username), 'success');

trigger_error(tr("The records ID# %s does not exist>', $record), E_USER_ERROR);
~~~

As you can see from the above examples, use placeholders such as **%s**, and pass the parameters to the function in sequential order.


### `string fmsg(string $msg, ...$args)`

**Description:** Formats a message with placeholders based on PSR3 standards, and is used within the *apex\log* and *apex\debug* classes.  These 
placeholders look like `{1}`, `{2}`, `[3]~, and so on.  The first parameter to this function is the message itself with placeholders, 
and the rest of the parameters are the values to replace the placeholders with in sequential order.  For example:

~~~php

$msg = fmsg("The user {1} completed verification step {2}, and is now active", $username, $level);
~~~


### `string fnames(string $message, array $vars)`

**Description:** Formats a message using named placeholders, instead of sequential numbers like `fmsg()` does.  The `$vars` array is an associatve array that replaces the keys with their respective values within the message.

** Example**

~~~php
$message = "The name of the dog is {name}, and he is {age} years old";

$vars = array(
    'name' => 'Boxer', 
    'age' => 6
);

$message = fnames($message, $vars);
~~~


### `string fdate(string $date, bool $add_time = false)`

**Description:** Formats a date into the proper, readable format for the web browser, plus also 
changes it to the correct timezone.  All dates within Apex are stored in UTC, this function will check the authenticated user's preferred timezone, and update 
the time as necessary to display the time and date in the correct timezone.  Should always be used when displaying any date / time 
within the web browser.

**Parameters**

Variable | Type | Description
------------- |------------- |-------------
`$date~ | string | The date to format, formatted in YYYY-MM_DD HH:II:SS
`$add_time` | bool | WHether or not to add time on to the end of the date.


### `string fmoney(float $amount, string $currency = '', bool $include_abbr = true)`

**Description:** Formats a decimal into an amount using the proper currency symbol and decimals.  Always 
use this function when displaying an amount.


### `float exchange_money(float $amount, string $from_currency, string $to_currency)`

**Description:** Exchanges the amount specified from the one currency, to the specified currency using the latest exchange rates in the database.

### `bool check_package(string $alias)`

**Description:** Checks whether or not a package alias is installed on the system, and returns a boolean.  Useful 
when developing packages that act / display things differently depending on whether or not a certain package is installed.
 



