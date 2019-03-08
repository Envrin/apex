<?php
declare(strict_types = 1);

namespace apex;

use apex\DB;
use apex\registry;
use apex\message;
use apex\log;
use apex\debug;
use apex\email;
use apex\core\io;

class 2fa
{

/**
* Authenticate user / admin via e-mail
*     @param string $type Must be either 'user' or 'admin', and defines the type of user being authenticated.
*/
public function authenticate(string $type = 'user')
{

    // Create 2FA hash
    $auth_hash = io::generate_random_string(36);
    $auth_hash_enc = hash('sha512', $hash_2fa);

    // Add to database
    DB::insert('auth_2fa', array(

}

}
