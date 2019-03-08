<?php
declare(strict_types = 1);

namespace apex\core\ajax;

use apex\DB;
use apex\registry;

class search_table Extends \apex\jax 
{

/**
* Searches a table for given terms, removes all existing table 
* rows, and replaces them with table rows that match the 
* search.  This is the 'quick search' functionality of the data tables.
*/
public function process() extends ajax 
{

    // Set variables
    $package = $_POST['package'] ?? '';
    $search_text = $_POST['search_' . $_POST['id']] ?? '';
    if ($search_text == '') { 
        $this->alert(tr('You did not specify any text to search for.'));
        return;
    }

    // Load table
    $table = load_component('table', $_POST['table'], $package, '', $_POST);
    // Get table details
    $details = get_table_details($table, $_POST['id']);

    // Clear table rows
    $this->clear_table($_POST['id']);

    // Add new rows
    $this->add_data_rows($_POST['id'], $_POST['table'], $package, $details['rows'], $_POST);

    // Set pagination
    $this->set_pagination($_POST['id'], $details);

}

}

