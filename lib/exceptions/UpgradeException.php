<?php
declare(strict_types = 1);

namespace apex;

/**
* Handles all upgrade errors such as upgrade does not exist, 
* unable to download, unable to load upgrade.php class file, etc.
*/
class UpgradeException extends ApexException
{
    // Properties
    private $error_codes = array(
        'not_exists' => "No upgrade exists within the database with the ID# {id}", 
        'invalid_version' => "Unable to create upgrade as version is invalid.  Must be in format x.x.x, in all digigs", 
        'not_open' => "This upgrade is not open, hence can not be compiled or published, ID# {id}", 
        'no_rollback' => "No rollback information exists for the package {package}, version {version}" 
    );
/**
* Construct
*/
public function __construct($message, $upgrade_id = 0, $package = '', $version = '')
{

    // Set variables
    $vars = array(
        'id' => $upgrade_id, 
        'package' => $package, 
        'version' => $version
    );

    // Get message
    $this->log_level = 'error';
    $this->message = $this->error_codes[$message] ?? $message;
    $this->message = fnames($this->message, $vars);

}

}

