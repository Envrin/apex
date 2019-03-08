
# Encryption

Upon a user or administrator being created, a 4096 bit RSA key-pair is automatically generated for them.  The private key is stored within the database, 
encrypted via AES256 with the user's password.  Upon logging in, the private key is decrypted and stored within the session 
for the duration of the session until logged out / destroyed.

Data can be easily encrypted to multiple people.  Upon encrypting a piece of data, Apex will encrypt it via AES256 using a random password and IV.  Then Apex 
will go through each defined user, and encrypt the random password / IV key used with their RSA public key.  When the user needs the data decrypted, 
Apex simply decrypts the encryption key / IV using the user's private RSA key, then decrypts the actual data.


### `int encrypt::encrypt_user(string $data, array $recipients)`

**Description:** Allows you to securely encrypt any data to one or more recipients.  The recipients are formatted in either `admin:XX` or `user:XX` where `XX` is the ID# 
of the administrator / user.  This returns the ID# of the encrypted data, which you will need to store in order to retrieve the decrypted 
version of the data in the future.

**Paramters**

Variable | Type | Description
-------------  |-------------  |-------------  
`$data` | string | The data to encrypt.
`$recipients` | array | List of people to encrypt the data to, formatted with either `admin:XX` or `user:XX` where `XX` is the ID# of the administrator / user.



### `string encrypt::decrypt_user(int $data_id, string $password = '')`

**Description:** Decrypts data that was previously encrypted with the `encrypt_user()`.  Takes in the `$data_id`, which was returned by the encrypt 
function, plus the optional encryption password in MD5 hash.  You only need to supply the password if it's a back-end application server doing the decryption, but if it's a front-end web server 
doing the decryption, then the password is not required, as it's simply pulled from the user's authentication session.  


### `string auth::get_encpass()`

**Description:** Useful when sending one-way messages or RPC calls via RabbitMQ, and the back-end application server needs to decrypt some data that is encrypted to the user.  This will return the MD5 hash of the encryption 
password from the user's current authenticated session, which you need to them pass on to RabbitMQ.  Specify this password in the `decrypt_user()` function to properly decrypt the data.


**Paramters**
------------- |------------- |------------- 
`$data_id` | int | The ID# of the data, returned by the `encrypt_user()` function.
`$userid` | int | The ID# of the user / administrator that is decrypting the data.
`$user_type` | string | Either "user" or "admin", defining which type of user it's being decrypted for.
`$password` | string | The plain text account password of the user / administrator.


**Example**

~~~php
namespace apex;

use apex\encrypt;
$text = "My test... 1234...";
$data_id = encrypt::encrypt_data($text, array('user:46', 'admin:1'));

$decrypted = encrypt::decrypt_data($data_id, 46, 'user', 'some_password');
~~~


### `string encrypt::encrypt_basic(string $data)`

**Description:** Does a simple AES256 encryption against the provided data, and returns the results.  Please note, this is HIGHLY insecure, 
and should for all intents be treated as plain text.  Both, the encryption password and IV are stored within the redis database in plain text, as there is no way to keep them offline.  It's a little 
better than plain text, but not much, and never store anything truly sensitive with this function.


### `string encrypt::decrypt_basic(string $encrypted)`

**Description:** Decrypts data encrypted via the `encrypt_basic()` function, and returns the results.



# PGP Ecnrypptionn

Apex also allows for PGP encryption and key management with the below static function.  Please note, in order to use 
PGP encryption you must have the pgp-gnupg extension installed on your server, and the `$HOME/.gnupg` directory must be writable by whatever user / group the http server 
is running / listening on.


### `string encrypt::import_pgp_key(string $type, int $userid, string $public_pgp_key)`

**Description:** Validates and imports a public PGP key into gnupg on the system.  All PGP keys must be imported via this method before you can encrypt messages to them.  This will return the fingerprint of the PGP key if successful, and false otherwise.

**Example**

~~~php
namespace apex;

use apex\encrypt;

$mykey = 'SOME_PUBLIC_PGP_KEY_ASCII_FORMAT';
$fingerprint = encrypt::import_pgp_key('user', registry::$userid, $mykey);

echo "Imported PGP key with fingerprint: $fingerprint\n";
~~~


### `string encrypt::encrypt_pgp(string $message, array $recipients)`

**Description:** Encrypts a new PGP message to one or more recipients.  Simply pass the plain text message, and an array of recipients to encrypt the message to 
in standard Apex format.  For example, `array('user:63', 'admin:2')` will encrypt the message to user ID# 63 and administrator ID# 2.

**Example**

~~~php
namespace apex;

use apex\encrypt;

$plain_text = "The brown fox ran away...";
$encrypted = encrypt::encrypt_pgp($plain_text, array('user:85'));

echo "Encrypted PGP:\n\n" . $encrypted;
~~~


### `string encrypt::get_pgp_key(string $type, int $userid, [string $key_type = 'fingerprint'])`

**Description:** Retrieves a PGP fingerprint of full public key for a user, and returns it..  The `$type` is the tyep of user (eg. user / admin), then the ID# of the user, and the 
`$key_type` to return which must be either "fingerprint" or "pgp_key".


**Example**

~~~php
namespace apex;

use apex\encrypt;

$pgp_key = encrypt::get_pgp_key('user', 381, 'pgp_key');

echo "Your PGP key is: $pgp_key\n\n";
~~~



### `encrypt::reimport_all_pgp_keys()`

**Description:** This is only needed when transferring to a new server.  It eill go through all PGP keys that were previously imported and are currently stroed in the database, and 
will import them into gnupg on the server.  All PGP keys must be imported into gnupg in order to encrypt PGP messages to them.


