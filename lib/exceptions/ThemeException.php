<?php
declare(strict_types = 1);

namespace apex;

/**
* Handles all theme based errors such as theme does not exist, 
* unable to download theme, unable to install theme, etc.
*/
class ThemeException extends ApexException
{
    // Properties
    private $error_codes = array(
        'not_exists' => "No theme exists within the system with the alias, {alias}", 
        'invalid_alias' => "Unable to create new theme as you specified an invalid alias, {alias}", 
        'theme_exists' => "The theme already exists in this system with the alias, {alias}", 
        'not_exists_repo' => "The theme does not exist in any repositories listed within the system, {alias}" 
    );
/**
* Construct
*/
public function __construct($message, $alias = '')
{

    // Set variables
    $vars = array(
        'alias' => $alias
    );

    // Get message
    $this->log_level = 'error';
    $this->message = $this->error_codes[$message] ?? $message;
    $this->message = fnames($this->message, $vars);

}

}

