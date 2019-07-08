
# Global Functions

There are a handful of global functions available, which help facilitate development within Apex.  All
functions are available within the */src/app/sys/functions.php* library, and are explained below.


### `string tr(string $text, ...$args)`

**Description:** Used to format messages to be sent for output to the web browser.  This does both, replaces
placeholders as needed, and if necessary translates the message into another language.  Supports both,
sequential and name based place holders, and for example:

~~~php
$text = tr("The user's full name is {1}, and their group ID# is {2}.", $full_name, $group_id);

$vars = array(
    'username' => 'john',
    'amount' => '25.00
);
template::add_callout("Successfully added transaction to user {username} of amount {amount}", $vars);

throw new ApexException('error', tr("You did not submit a valid amount, {1}", $amount));
~~~


### `string fdate(string $date, bool $add_time = false)`

**Description:** Formats a date into the proper, readable format for the web browser, plus also changes it to
the correct timezone.  All dates within Apex are stored in UTC, this function will check the authenticated
user's preferred timezone, and update the time as necessary to display the time and date in the correct
timezone.  Should always be used when displaying any date / time within the web browser.

**Parameters**

Variable | Type | Description ------------- |------------- |------------- `$date~ | string | The date to
format, formatted in YYYY-MM_DD HH:II:SS `$add_time` | bool | WHether or not to add time on to the end of the
date.


### `string fmoney(float $amount, string $currency = '', bool $include_abbr = true)`

**Description:** Formats a decimal into an amount using the proper currency symbol and decimals.  Always use
this function when displaying an amount.


### `float exchange_money(float $amount, string $from_currency, string $to_currency)`

**Description:** Exchanges the amount specified from the one currency, to the specified currency using the
latest exchange rates in the database.

### `bool check_package(string $alias)`

**Description:** Checks whether or not a package alias is installed on the system, and returns a boolean.
Useful when developing packages that act / display things differently depending on whether or not a certain
package is installed.




