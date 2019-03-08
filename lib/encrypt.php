<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\debug;


/**
* Handles all encryption within Apex including user based two-way AES256 encryption 
* to multiple recipients, basic insecure encryption, RSA key-pair generation, etc.
*/
class encrypt 
{

/**
* Generate new RSA key-pair
*     @param int $userid The ID# of the user / admin to create a keypair for.
*      @param string $type The type of user, defaults to 'user'
*      @param string $password The encryption password for the private key.  Generally should be the user's login password.
*     @return int The ID# of the encryption key
*/
public static function generate_rsa_keypair(int $userid, string $type = 'user', string $password = ''):int
{

    // Debug
    debug::add(2, fmsg("Start generating RSA key-pair for user ID# {1}, user type: {2}", $userid, $type), __FILE__, __LINE__, 'info');

    // Set config args
    $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    // Generate private key
    if (!$res = openssl_pkey_new($config)) { 
        throw new EncryptException('unable_generate_rsa', $userid, $type);
    }

    // Export private key
        openssl_pkey_export($res, $privkey);

    // Export public key
    $pubkey = openssl_pkey_get_details($res);

    // Encrypt private key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $privkey = openssl_encrypt($privkey, ENCRYPT_CIPHER, md5($password), 0, $iv);

    // Add to database
    DB::insert('encrypt_keys', array(
        'type' => $type, 
        'userid' => $userid, 
        'iv' => base64_encode($iv), 
        'public_key' => $pubkey['key'], 
        'private_key' => $privkey)
    );
    $key_id = DB::insert_id();

    // Debug
    debug::add(1, fmsg("Generated RSA key-pair for user ID# {1}, user type: {2}", $userid, $type), __FILE__, __LINE__, 'info');

    // Return
    return (int) $key_id;
}

/**
* Change user's RSA password.  This will decrypt the user's 
* current RSA private key, and encrypt it again with the 
* new password.
*     @param int $userid The ID# of the user / admin
*     @param string $type The type of user, either 'user' or 'admin'
*     @param string $old_password The old / current password
*      @param string $password The new password
*/
public static function change_rsa_password(int $userid, string $type, string $old_password, string $password)
{

    // Get key
    if (!list($key_id, $privkey) = self::get_key($userid, $type, 'private', $old_password)) { 
        throw new EncryptException('no_private_key', $userid, $type);
    }

    // Encrypt private key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $privkey = openssl_encrypt($privkey, ENCRYPT_CIPHER, md5($password), 0, $iv);

    // Update database
    DB::update('encrypt_keys', array(
        'iv' => base64_encode($iv), 
        'private_key' => $privkey), 
    "id = %i", $key_id);

    // Debug
    debug::add(1, fmsg("Updated password on RSA key-pair for user ID# {1}, user type: {2}", $userid, $type), __FILE__, __LINE__, 'info');

    // Return
    return true;

}

/**
* Get public / private key from the database
*     @param int $userid The ID# of the user / admin
*     @param string $type The type of user, either 'user' or ad'min, defults to 'user'
*     @param $key_type The type of key to retrieve, either 'public' or 'private', defaults to 'public'
*     @param string $password Only required if $key_type is 'private', and is the password the key is encrypted with
*/
public static function get_key(int $userid, string $type = 'user', string $key_type = 'public', string $password = '')
{

    // Get row
    if (!$row = DB::get_row("SELECT * FROM encrypt_keys WHERE type = %s AND userid = %i", $type, $userid)) { 
        throw new EncryptException('no_private_key', $userid, $type);
    }

    // Get key
    $key = $row[$key_type . '_key'];
    if ($key_type == 'private') { 
        if (!$key = openssl_decrypt($key, ENCRYPT_CIPHER, $password, 0, base64_decode($row['iv']))) { 
            throw new EncryptException('no_private_key', $userid, $type);
        }
    }

    // Debug
    debug::add(5, fmsg("Retrieved RSA encryption key, userid: {1}, user type: {2}, key type: {3}", $userid, $type, $key_type), __FILE__, __LINE__);

    // Return
    return array($row['id'], $key);

}
/**
* Encrypt text to one or more users
*     @param string $data The data to encrypt
*      @param array $recipients An array of recipients to encrypt the data to (eg. admin:1, user:46, etc.)
*     @return int The ID# of the encrypted data
*/
public static function encrypt_user(string $data, array $recipients):int
{

    // Generate key / IV
    $data_key = openssl_random_pseudo_bytes(32);
    $data_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

    // Encrypt the data
    $encrypted = openssl_encrypt($data, ENCRYPT_CIPHER, $data_key, 0, $data_iv);

    // Add to database
    DB::insert('encrypt_data', array(
        'data' => $encrypted)
    );
    $data_id = DB::insert_id();

    // Add administrators to recipients
    $admin_ids = DB::get_column("SELECT id FROM admin");
    foreach ($admin_ids as $admin_id) { 
        $var = 'admin:' . $admin_id;
        if (in_array($var, $recipients)) { continue; }
        $recipients[] = $var;
    }

    // Go through recipients
    foreach ($recipients as $recipient) { 

        // Get key
        if (preg_match("/^(user|admin):(\d+)/", $recipient, $match)) { 
            list($key_id, $public_key) = self::get_key((int) $match[2], $match[1]);
        }
        $pubkey = openssl_pkey_get_public($public_key);

        // Encrypt
        $keydata = base64_encode($data_key) . '::' . base64_encode($data_iv);
        if (!openssl_public_encrypt($keydata, $enc_key, $pubkey, OPENSSL_PKCS1_OAEP_PADDING)) {
            throw new EncryptException('no_encrypt_user', 0, '', $recipient);
        }

        // Add to database
        DB::insert('encrypt_data_keys', array(
            'data_id' => $data_id, 
            'key_id' => $key_id, 
            'keydata' => base64_encode($enc_key))
        );

        // Debug
        debug::add(5, fmsg("Encrypted data via AES256 to recipient: {1}", $recipient), __FILE__, __LINE__);

    }

    // Debug
    debug::add(5, "Finished encrypting AES256 data to all listed recipients", __FILE__, __LINE__);

    // Return
return $data_id;

}

/**
* Decrypt data using ID# of data and key, plus encryption password
     @param int $data_id The ID# of the data to decrypt
*     @param int $userid The ID# of the user to decrypt for
*      @param string $type The type of user, 'user or 'admin'
*     @param string $password The decryption password, generally the user's password.
*     @return string The decrypted data, or false if not successful.
*/
public static function decrypt_user(int $data_id, string $password = ''):string
{

    // Get password, if needed
    if ($password == '' && !$password = auth::get_encpass()) { 
        throw new EncryptException('no_session_password');
    }

    // Get key
    $user_type = registry::$panel == 'admin' ? 'admin' : 'user';
    if (!list($key_id, $privkey) = self::get_key(registry::$userid, $user_type, 'private', $password)) { 
        throw new EncryptException('no_private_key', registry::$userid, $user_type);
    }

    // Get keydata
    if (!$keydata = DB::get_field("SELECT keydata FROM encrypt_data_keys WHERE data_id = %i AND key_id = %i", $data_id, $key_id)) { 
        return false;
    }

    // Get data
    if (!$data = DB::get_field("SELECT data FROM encrypt_data WHERE id = %s", $data_id)) { 
        throw new EncryptException('no_data', 0, '', '', $data_id);
    }

    // Decrypt keydata
    $privkey = openssl_pkey_get_private($privkey);
    openssl_private_decrypt(base64_decode($keydata), $decrypted, $privkey, OPENSSL_PKCS1_OAEP_PADDING);

    // Parse key data
    list($key, $iv) = explode("::", $decrypted, 2);
    $key = base64_decode($key);
    $iv = base64_decode($iv);

    // Decrypt data
    $text = openssl_decrypt($data, ENCRYPT_CIPHER, $key, 0, $iv);

    // Return
    return $text;

}

/**
* Very basic encryption.
* NOTE:  Very insecure, and please treat virtually the same as plain text, albiet a tiny fraction better.
*/
public static function encrypt_basic(string $data, string $password = ''):string
{

    // Debug
    debug::add(4, "Basic encrypt of data", __FILE__, __LINE__);

    if ($password == '') { $password = ENCRYPT_PASS; } 
    $encrypted = openssl_encrypt($data, ENCRYPT_CIPHER, $password, 0, ENCRYPT_IV);
    return $encrypted;
}

/**
* Decrypts data that was encrypted via the encrypt_basic() function
*/
public static function decrypt_basic(string $data, string $password = '')
{

    // Debug
    debug::add(4, "Basic decrypt of data", __FILE__, __LINE__);

    // Decrypt
    if ($password == '') { $password = ENCRYPT_PASS; } 
    $text = openssl_decrypt($data, ENCRYPT_CIPHER, $password, 0, ENCRYPT_IV);
    return $text;
}

/**
* Import public PGP key
*     @param string $type The user type (user / admin)
*     @param int $Userid The ID# of the user
*     @param string $public_key The public PGP key
*     @return mixed If unsuccessful, return false.  Otherwise, returns the fingerprint of the PGP key.
*/
public static function import_pgp_key(string $type, int $userid, string $public_key)
{

    // Initialize
    if (!function_exists('gnupg_init')) { 
        throw new EncryptException('no_gnupg');
    }
    $pgp = gnupg_init();

    // Import key
    if (!$vars = gnupg_import($pgp, $public_key)) { 
        throw new EncryptException('invalid_pgp_key');
    }
    if (!isset($vars['fingerprint'])) { 
        throw new EncryptException('invalid_pgp_key');
    }

    // Update database, if user already exists
    if ($key_id = DB::get_field("SELECT id FROM encrypt_pgp_keys WHERE type = %s AND userid = %i", $type, $userid)) { 
        DB::update('encrypt_pgp_keys', array(
            'fingerprint' => $vars['fingerprint'], 
        'pgp_key' => $public_key), 
        "id = %i", $key_id);

    /// Add key to database
    } else { 
        DB::insert('encrypt_pgp_keys', array(
            'type' => $type, 
            'userid' => $userid, 
            'fingerprint' => $vars['fingerprint'], 
            'pgp_key' => $public_key)
        );
        $key_id = DB::insert_id();
    }

    // Return
    return $vars['fingerprint'];

}

/**
* Encrypt a PGP message to one or more recipients
*     @param string $message The plain text message to encrypt.
*     @param array $recipients The recipients to engry message to, formatted in standard Apex format (eg. user:54, admin:1, etc.)
*     @return string The encrypted PGP message
*/
public static function encrypt_pgp(string $message, $recipients)
{

    // Initialize
    if (!function_exists('gnupg_init')) { 
        throw new EncryptException('no_gnupg');
    }
    $pgp = gnupg_init();
    $recipients = is_array($recipients) ? $recipients : array($recipients);

    // Go through recipients
    foreach ($recipients as $recipient) {

        // Get key 
        list($type, $userid) = explode(":", $recipient, 2);
        if (!$fingerprint = self::get_pgp_key($type, $userid)) {
            continue;
        }

        // Add fingerprint
        gnupg_addencryptkey($pgp, $fingerprint);
    }

    // Encrypt message
    $encrypted = gnupg_encrypt($pgp, $message);

    // Return
    return $encrypted;

}

/**
* Get a PGP key from the database
*     @param string $type The type of user (user / admin)
*      @param int $userid The ID# of the user
*     @param string $key_type The type of key to return (fingerprint or public_key)
*      @return string The fingerprint or full PGP key of the user
*/
public static function get_pgp_key(string $type, int $userid, string $key_type = 'fingerprint')
{

    // Get from database
    if (!$row = DB::get_row("SELECT * FROM encrypt_pgp_keys WHERE type = %s AND userid = %i")) { 
        return false;
    }
    $key = $key_type == 'fingerprint' ? $row['fingerprint'] : $row['pgp_key'];

    // Return
    return $key;

}

/**
* Reimport all PGP keys from the database into gnupg on the server.  This is used when 
* transferring the system to a new server, as all PGP keys in the database must be imported 
* into gnupg to encrypt messages to them.
*/
public static function reimport_all_pgp_keys()
{

    // Go through keys
    $rows = DB::query("SELECT * FROM encrypt_pgp_keys ORDER BY id");
    foreach ($rows as $row) { 
        self::import_pgp_key($row['type'], (int) $row['userid'], $row['pgp_key']);
    }

    // Return
    return true;

}

}



