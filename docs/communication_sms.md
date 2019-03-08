
# Communication -- SMS Messages

Apex fully supports sending SMS messages via the [Nexmo API](https://nexmo.com/), allowing you to easily send SMS messages to 
users around the world.  Below explains the few functions available to send SMS messages.


### `message::send_sms(string $phone, string $message)`

This simply sends a SMS message to the provided phone number.  Pretty straight forward.

**Example:**

~~~php
namespace apex;

use apex\message;

message::send('+16045551234', 'Hellow from Apex');
~~~


### `$user->send_sms($message)`

Easily send a SMS message to any user within the database with the following example code:

```php
namespace apex;

use apex\users\user

$user = new user(registry::$userid);
$user->send_sms('Hellow from Aplex');
~~~


#### `$admin->send_sms($message)`

Send a SMS e-mail message to any administrator with the following example code:

~~~php
namespace apex;

use apex\core\admin;

$admin_id = 1;
$admin = new admin($admin_id);
$admin->send_sms('Hellow from Apex');
~~~


