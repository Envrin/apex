
# Database - redis

Apex uses redis, which is a very quick in-memory storage engine allowing for fast page load times, and horizontal 
scaling.  The php-redis extension is used, which provides a very easy and efficient API to communicate with redis.

The redis connection is created within the `registry` class as a variable, and can be accessed with for example:

~~~php
namespace apex;

use apex\registry;

registry::$redis->hset('some_hash', 'var_name', 'value');
~~~

For more information on redis, the data types and functions available, and php-redis, please visit the below links.

> [redis Data Types](https://redis.io/topics/data-types)
> [php-redis](https://redislabs.com/lp/php-redis/)


### System Keys

The below table lists the system keys stored within the redis database for your reference.

Key | Type | Description
------------- |------------- |------------- 
auth | hash | Contains information on all the authenticated sessions for both, users and administrators.
auth:last_seen | hash | Contains the time the user / administrator was last seen.
hash | hash | Contains all hashes of all packages, the keys being *PACKAGE:ALIAS8, and the values being a JSON encoded string of the hash key-value pairs.
counters | hash | Small hash that simply contains sequential numbers, and is used when you need an incrementing number for any reson.  Uses the global `get_counter($key)` function.
config | hash | Contains all configuration variables of all packages, the keys being *PACKAGE:ALIAS*, and the value being the value of the configuration variable.
config:db_master | hash | Contains connection details for the master mySQL database.
config:db_slaves | list | Contains connection information for all slave mySQL database servers, the keys being the ID# of the slave, and the value being a JSON encoded string of connection information.
config:email_servers | list | List of all SMTP servers configured on the server, each being encoded via JSON with connection information.
config:languages | list | List of all active language packs installed.
config:components | list | List of all components installed, for easy look up within the software to check whether or not a component exists (form, table, etc.)
config:component_packages | hash | Contains all components installed on the system with the values being their respective package, allowing for quick look up when no package is specifically defined within the templates / code.
std:timezone | hash | Contains details on all timezones, keys being the abbreciation (eg. EST), and values being the offset and whether DST exists within that timezone.
std:currency | hash | Contains details on all world currencies.
std:language | hash | Contains the ISO code and name of all major world languages.
| std:country | hash | Contains details on all countries including country name, language, timezone, calling code, etc.
users_groups | hash | Contains the ID# and name of all user groups.
users_profile_fields | hash | Contains details on all additional profile fields aside from the standard fields.  These are profile fields defined by the administrator within the admin panel.
users:XX | hash | Where XX is the ID# of the user, and contains basic profile information on every user such as username, full name, e-mail address, country, timezone and language for faster lookup.
admin:XX | hash | Where XX is the ID# of the administrator, and contains basic information on each administrator including username, language, timezone, etc.
admin_usernames | hash | Contains all administrator usernames as the keys, with the values being the ID# of the administrator
usernames | hash | Contains all usernames in the database as keys, with the value being the ID# of the user.
cms:menus | hash | Contains all menus for all areas of the software such as admin panel, member's area, and public site.  Helps ensure the software doesn't need to connect to the mySQL database during page loads.


