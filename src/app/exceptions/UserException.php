<?php
declare(strict_types = 1);

namespace apex\app\exceptions;

use apex\app;
use apex\app\exceptions\ApexException;


/**
 * Handles all user exceptions, such as user does not exist, unable to create 
 * / update / delete user, etc. 
 */
class Userxception   extends ApexException
{



    // Properties
    private $error_codes = array(
    'not_exists' => "No user exists within the system with the ID# {id}"
    );
/**
 * Construct 
 */
public function __construct($message, $userid = 0)
{ 

    // Set variables
    $vars = array(
        'id' => $userid
    );

    // Get message
    $this->log_level = 'error';
    $this->message = $this->error_codes[$message] ?? $message;
    $this->message = fnames($this->message, $vars);

}


}

