
# Communication -- Web Sockets

Apex also contains an internal web sockets server which facilitates real-time between clients viewing the web site and between clients and the server, allowing elments within the 
DOM to be modified in real-time with virtually no lag.  This is useful for things such as real-time notifications, chat bots, updates rates / scores, and so on.  Elements within a 
web page can be easily modified without any Javascript by utilizing the [AJAX Library](core/ajax.md) contained within Apex.

Every page load anywhere within the online operations will automatically initialize a connection to the web socket server.  From within the software, you can easily send a message to everyone connected to the 
site, or filter the message and only send to users who are viewing a certain area (eg. administration panel, member area, 
public site), a specific page / URI, or to individual users who are logged in.


### `message::send_ws(Ajax $ajax, array $recipients = array(), string $area = '', string $route = '')`

**Description:** The web socket server / client integrates with the existing Apex [AJAX Library](core/ajax.md) allowing you to easily change DOM elements within the 
web browser.  Anywhere within the code you can create a new AJAX object, execute the desired functions against it, and pass the AJax object to the `message::send_ws()` function, which 
will instantly output it to all web browsers connected to the web site at that moment.

You can easily filter outgoing messages to be sent to everyone connected to the site, only people connected to a certain area (eg. public site, administration panel, .  The `message::send_ws()` function takes in various paramters, as explained below.


**Parameters**

Variable | Type | Description
-------------member's area), only people on a specific page, or speicifc individuals that are logged in or on the public site and not authenticated. |-------------member's area), only people on a specific page, or speicifc individuals that are logged in or on the public site and not authenticated. |-------------member's area), only people on a specific page, or speicifc individuals that are logged in or on the public site and not authenticated. 
`$ajax` | Ajax Object | Object from the `apex\ajax` class, located at `/lib/ajax.php`.
`$recipients` | array | An array of specific individuals to send the message to, formatted in either `user:XX` or `admin:XX`, where `XX` is the ID# of the user or administrator.  For example, `user:841` will send the message to user ID# 841, assuming they are logged in and active on the site.
`area` | string | If defined, will only send messages to users currently viewing the specified area (admin, members, or public).
`$route` | string | Id defined, will only send the message to users with the speicifc page opened, relative to the area (eg. `area` = admin, and `$route` = users/create will only sent to people viewing the Users->Create New User menu of the administration panel)


**Example:**

~~~php
namespace apex;

use apex\ajax;
use apex\message;

// Set AJAX
$ajax = new Ajax();
$ajax->alert("Got the message!");
$ajax->clear_table('tbl_orders');

// Send to everyone viewing admin panel
message::send_ws($ajax, array(), 'admin');

// Send specifically to user ID# 581
$recipient = 'user:581';
message::send_ws($ajax, array($recipient));
~~~

For full information on all actions available to modify DOM elements, please visit the [AJAX Library](core/ajax.md) page of this manual.  The above example will immediately clear all 
table rows from the table "tbl_orders, and display a small dialog box with an alert.



### `message::add_dropdown_alert(string $recipient, string $message, string $url)`

**Description:**Both the administration panel and member's area contain a drop-down list available via an icon in the top right corner of the 
screen.  This function allows you to add an additional alert into that drop-down list in real-time.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$recipient` | string | The user to add the alert to, formatted in either `user:XX` or `admin:XX` where `XX` is the ID# of the user / administrator.
`message` | string | The message to add into the dropdown list item.
`$url` | string | The URL (relative or absolute) to link to dropdown list item to.

**Example**

~~~php
namespace apex;

use apex\message;

// Add alert to user IDID# 53
message::add_dropdown_alert('user:53', "An import alert just came in for you...", "members/some_menu?action=592831");
~~~



### `message::add_dropdown_message(string $message, string $from, string $message, string $url)`

**Description:** Similar to adding a new dropdown alert as explained above, but adds the dropdown item to a different list that is available within the 
administration panel and member's area just to the right of the alerts dropdown list.  This is meant for actual messages, e-mails, private messages, etc.

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$recipient` | string | The user to add the message to, formatted in either `user:XX` or `admin:XX` where `XX` is the ID# of the user / administrator.
`$from` | string | Who the message is from, can be any string / name you wish.
`message` | string | The message to add into the dropdown list item.
`$url` | string | The URL (relative or absolute) to link to dropdown list item to.

**Example**

~~~php
namespace apex;

use apex\message;


// Add message to administrator with ID# 1
message:add_dropdown_message('admin:1', "John Smith (jsmith)", "Could use some help with getting this to work...", "admin/support/view_ticket?id=4239");
~~~



