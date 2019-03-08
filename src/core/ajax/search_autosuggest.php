<?php
declare(strict_types = 1);

namespace apex\core\ajax;

use apex\DB;
use apex\registry;
use apex\core\components;

class search_autosuggest Extends \apex\ajax 
{

/**
* Loads the appropriate autosuggest component, performs a 
* search, and displays the results to the browser.
**********/ 

public function process() 
{

    // Set variables
    list($package, $parent, $alias) = components::check('autosuggest', registry::get('autosuggest'));

    // Load autosuggest
    $autosuggest = components::load('autosuggest', $alias, $package, '', registry::getall_get());

    // Get options
    $options = $autosuggest->search($_GET['term']);

    // Format options
    $results = array();
    foreach ($options as $id => $label) { 
        array_push($results, array('label' => $label, 'data' => $id));
    }

    // Return
    header("Content-type: text/json");
    echo json_encode($results);
    exit(0);


}

}

