<?php
declare(strict_types = 1);

namespace apex\core\lib\exceptions;

/**
* Handles various miscellaneous errors, such as administrator 
* does not exist, etc.
*/
class MiscException extends \apex\core\lib\exceptions\ApexException
{
    // Properties
    private $error_codes = array(
        'no_admin' => "No administrator exists within the database with ID# {id}"
    );

/**
* Construct
*/
public function __construct($message, $id = 0)
{

    // Set variables
    $vars = array(
        'id' => $id
    );

    // Get message
    $this->log_level = 'error';
    $this->message = $this->error_codes[$message] ?? $message;
    $this->message = fnames($this->message, $vars);

}

}

