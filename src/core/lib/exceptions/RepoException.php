<?php
declare(strict_types = 1);

namespace apex\core\lib\exceptions;

/**
* Handles all repository based errors, such as 
* repo does not exists, unable to connect, invalid access, etc.
*/
class RepoException extends \apex\core\lib\exceptions\ApexException
{
    // Properties
    private $error_codes = array(
        'not_exists' => "No repository exists within the database with ID# {id}", 
        'invalid_repo' => "No valid repository exists at the URL, {url}", 
        'no_repos_exist' => "There are no repositories currently listed in the database.  Please add at least one repository (eg. php apex.php add_repo URL) before continuing.", 
        'host_not_exists' => "No repository exists in this system with the host {url}",  
        'remote_error' => "Repository returned error: {error}"
    );
/**
* Construct
*/
public function __construct($message, $repo_id = 0, $url = '', $error_message = '')
{

    // Set variables
    $vars = array(
        'id' => $repo_id, 
        'url' => $url, 
        'error' => $error_message
    );

    // Get message
    $this->log_level = 'error';
    $this->message = $this->error_codes[$message] ?? $message;
    $this->message = fnames($this->message, $vars);

}

}

