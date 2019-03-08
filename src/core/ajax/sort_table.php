<?php
declare(strict_types = 1);

namespace apex\core\ajax;

use apex\DB;
use apex\registry;

class sort_table extends \apex\ajax 
{

/**
* Sorts a table.  Removes all existing table rows, retrives the 
* new rows from the 'table' component, and displays them in the browser. 
* Used when clicking the up/down sort arrows in a data table column header.
*/
public function process() extends ajax
{

    // Set variables
    $package = $_POST['package'] ?? '';

    // Load table
    $table = load_component('table', $_POST['table'], $package, '', $_POST);

    // Get table details
    $details = get_table_details($table, $_POST['id']);

    // Clear table
    $this->clear_table($_POST['id']);

    // Add new rows
    $this->add_data_rows($_POST['id'], $_POST['table'], $package, $details['rows'], $_POST);

    // Set pagination
    $this->set_pagination($_POST['id'], $details);

}

}

