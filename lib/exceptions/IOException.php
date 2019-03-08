<?php
declare(strict_types = 1);

namespace apex;

/**
* Handles all file I/O errors, such as file does not exist, directory 
* does not exists, unable to create or unpack zip archive, etc.
*/
class IOxception extends ApexException
{
    // Properties
    private $error_codes = array(
        'zip_not_exists' => "Zip archive file does not exist at {file}", 
        'zip_invalid' => "Not a valid zip archive at {file}"
    );
/**
* Construct
*/
public function __construct($message, $file = '')
{

    // Set variables
    $vars = array(
        'file' => $file
    );

    // Get message
    $this->log_level = 'error';
    $this->message = $this->error_codes[$message] ?? $message;
    $this->message = fnames($this->message, $vars);

}

}

